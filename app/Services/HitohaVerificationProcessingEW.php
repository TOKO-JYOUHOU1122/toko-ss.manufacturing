<?php

namespace App\Services;

use App\Contracts\Services\HitohaVerificationInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HitohaVerificationProcessingEW implements HitohaVerificationInterface
{
    public function getItemInformation(...$id)
    {
        if (count($id) != 1) return ['data' => null, 'err_message' => '管理IDが不正です。'];

        $orderSQL = DB::table('T_工程保証_EW as a')
            ->select('a.受注ID', 'a.明細ID', 'a.生産ID', 'a.梱包ID', 'a.組立区分ID', 'b.組立区分名')
            ->leftjoin('M_組立区分 as b', 'a.組立区分ID', '=', 'b.組立区分ID')
            ->where('a.管理ID', '=', $id);

        if (!$orderSQL->exists()) return ['data' => null, 'err_message' => '対象のデータが存在しません。'];
        $orderData = $orderSQL->first();

        $tables = ['生産計画F_EW', '生産完了F_EW'];
        $serialData = null;
        foreach ($tables as $table) {
            $query = DB::table($table)
            ->select(DB::raw("FORMAT(生産日, 'MM/dd') as 日付"),'ライン記号','生産連番')
            ->where('受注№', '=', $orderData->受注ID)
            ->where('受注明細№', '=', str_pad($orderData->明細ID, 2, '0', STR_PAD_LEFT))
            ->where('生産枝№', '=', $orderData->生産ID)
            ->where('分割№', '=', $orderData->梱包ID);
            if ($query->exists()) {
                $serialData = $query->first();
                break;
            }
        }
        if ($serialData === null) return ['data' => null, 'err_message' => '対象のデータが存在しません。'];
        $serial = ($serialData->日付 ?? '00/00') . '-' . ($serialData->ライン記号 ?? 'XX') . '-' . ($serialData->生産連番 ?? 0);
        $position = $orderData->組立区分名;

        $query = DB::table('T_工程保証_EW as a')
            ->select('a.*', 'a.加工工程 as 照合日時', 'b.照合区分')
            ->leftjoin('D_部材リスト_EW as b', function ($join) {
                $join->on('a.受注ID', 'b.受注№')
                    ->on('a.明細ID', 'b.受注明細№')
                    ->on('a.生産ID', 'b.生産枝№')
                    ->on('a.梱包ID', 'b.分割№')
                    ->on('a.部材ID', 'b.№');
            })
            ->where('a.受注ID', '=', $orderData->受注ID)
            ->where('a.明細ID', '=', $orderData->明細ID)
            ->where('a.生産ID', '=', $orderData->生産ID)
            ->where('a.梱包ID', '=', $orderData->梱包ID)
            ->where('a.組立区分ID', '=', $orderData->組立区分ID)
            ->where('a.管理区分', '=', 1)
            ->where(function ($q) {
                $q->where('a.加工工程', '!=', 0)
                  ->orWhereNull('a.加工工程');
            })
            ->orderBy('a.部材ID', 'asc');

        $results = $query->get();
        $items = [];
        foreach ($results as $row) {
            $items[] = ['id' => $row->管理ID, 'item_name' => $row->品名, 'verify_datetime' => $row->照合日時];
        }

        return ['data' => $items, 'serial' => $serial, 'position' => $position, 'err_message' => null];
    }

    public function save($items)
    {
        try {
            DB::beginTransaction();
            foreach ($items as $item) {
                DB::table('T_工程保証_EW')
                    ->where('管理ID', $item['id'])
                    ->update(['加工工程' => $item['verify_datetime']]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ['err_message' => $e->getMessage()];
        }

        return ['err_message' => null];
    }
}
