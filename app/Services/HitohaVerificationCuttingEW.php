<?php

namespace App\Services;

use App\Contracts\Services\HitohaVerificationInterface;
use Illuminate\Support\Facades\DB;

class HitohaVerificationCuttingEW implements HitohaVerificationInterface
{
    public function getItemInformation(...$id)
    {
        $orderSQL = DB::table('T_工程保証_EW')
            ->select('受注ID', '明細ID', '生産ID', '梱包ID', '部材ID')
            ->where('管理ID', '=', $id);

        if (!$orderSQL->exists()) return ['data' => null];

        $orderData = $orderSQL->first();

        $data = DB::table('D_部材ﾘｽﾄ_EW as a')
            ->leftjoin('生産計画F_EW as b', function ($join) {
                $join->on('a.受注№', 'b.受注№')->on('a.受注明細№', 'b.受注明細№')
                    ->on('a.生産枝№', 'b.生産枝№')->on('a.分割№', 'b.分割№');
            })
            ->leftjoin('M_商品 as c', 'a.生産CD', '=', 'c.商品CD')
            ->select(
                DB::raw("CONCAT(a.[受注№], '-', a.[受注明細№], '-', a.[生産枝№], '-', a.[分割№]) AS 受注CD"),
                DB::raw("CONCAT(CONVERT(NVARCHAR,  b.生産日, 111), '-', b.ライン記号, '-', b.生産連番) AS 組立番号"),
                'a.商品CD',
                'a.取付位置',
                'a.切断寸法',
                'a.品名',
                'a.構成数',
                'a.色種CD',
                'a.型番',
                DB::raw("c.商品名 + '' + ISNULL(a.仕様表示1, '') + ' ' + ISNULL(a.仕様表示2, '') + ' ' + ISNULL(a.仕様表示3, '') AS 商品名"),
                'a.W寸法',
                'a.H寸法',
                'a.枚数',
                'a.勝手',
                'a.製品色',
                'a.外観色',
                'a.内観色'
            )
            ->where('a.受注№', '=', $orderData->受注ID)
            ->where('a.受注明細№', '=', str_pad($orderData->明細ID, 2, '0', STR_PAD_LEFT))
            ->where('a.生産枝№', '=', $orderData->生産ID)
            ->where('a.分割№', '=', $orderData->梱包ID)
            ->where('a.№', '=', $orderData->部材ID)
            ->where(function ($query) {
                $query->where('a.製作指示書出力区分', '>', 0)
                    ->orwhere('a.加工指示区分', '>', 0);
            })->first();

        $query = DB::table('D_部材ﾘｽﾄ_EW as a')
            ->select(
                DB::raw(
                    "
					case a.加工指示区分
						when 1 then '長尺プレス'
						when 2 then '多段プレス'
						when 3 then 'エアープレス'
						when 4 then '手加工'
						when 5 then 'NC'
						else '-'
					end
					as 加工指示区分名"
                ),
                'a.加工指示区分',
                'a.釘穴加工穴数 as 加工内容',
                DB::raw(
                    "
					IIf(a.釘穴加工ピッチ='-','',a.釘穴加工ピッチ) +
					IIf(a.アタッチ取付穴数='-','',a.アタッチ取付穴数) +
					IIf(a.アタッチ取付ピッチ='-','',a.アタッチ取付ピッチ)
					as 加工設備"
                ),
                DB::raw(
                    "
					IIf(a.アタッチ取付中央='-','',a.アタッチ取付中央) +
					IIf(a.アタッチ取付半ピッチ='-','',a.アタッチ取付半ピッチ) +
					IIf(a.躯体取付穴数='-','',a.躯体取付穴数) +
					IIf(a.躯体取付ピッチ='-','',a.躯体取付ピッチ) +
					IIf(a.躯体取付中央='-','',a.躯体取付中央) +
					IIf(a.躯体取付半ピッチ='-','',a.躯体取付半ピッチ)
					as 加工情報"
                )
            )
            ->where('a.受注№', '=', $orderData->受注ID)
            ->where('a.受注明細№', '=', str_pad($orderData->明細ID, 2, '0', STR_PAD_LEFT))
            ->where('a.生産枝№', '=', $orderData->生産ID)
            ->where('a.分割№', '=', $orderData->梱包ID)
            ->where('a.取付位置', '=', $data->取付位置)
            ->where('a.切断寸法', '=', $data->切断寸法)
            ->where('a.加工指示区分', '>', 0)
            ->where('a.加工指示区分', '<', 90);

        return ['data' => $data, 'process' => $query->get()];
    }

    public function save($items)
    {
    }
}
