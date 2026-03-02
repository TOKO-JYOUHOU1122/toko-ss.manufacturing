<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use App\Models\PressAssist\M_Position;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PressAssist\M_Procedure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PressAssistProcedureController extends Controller
{
    public function procedure(Request $request)
    {
        $work_numbers = M_Procedure::select('作業番号')
            ->distinct()
            ->orderBy('作業番号', 'asc')
            ->pluck('作業番号')
            ->toArray();
        $positions = M_Position::select('管理番号', '位置番号 as 段位置')
            ->orderBy('管理番号', 'asc')
            ->orderBy('位置番号', 'asc')
            ->get()
            ->toArray();
        $procedures = $request->work_number ? self::fetchProcedures($request)->toArray() : [];

        return Inertia::render(
            'PressAssist/PressAssistMasterProcedure',
            [
                'work_numbers' => $work_numbers,
                'positions' => $positions,
                'procedures' => $procedures,
            ]
        );
    }

    public function fetchProcedures(Request $request)
    {
        $procedures = M_Procedure::WhereWorkNumber($request->work_number)
            ->orderBy('作業順', 'asc')
            ->get();

        return $procedures;
    }

    public function registProcedures(Request $request)
    {
        $errMessage = '';
        $work_number = $request->work_number;
        $editedItems = $request->editedItems;

        try {
            \DB::beginTransaction();

            foreach ($editedItems as $editedItem) {
                $item = M_Procedure::WhereID($editedItem['ID'])->first();
                if (!$editedItem['ID'] || !$item) $item = new M_Procedure();

                $item->fill([
                    '作業番号' => $work_number,
                    '管理番号' => $editedItem['管理番号'],
                    '作業順' => $editedItem['作業順'],
                    '型図パス' => $editedItem['型図パス'],
                    '段位置' => $editedItem['段位置'],
                    '画像位置' => $editedItem['画像位置'],
                    '反転フラグ' => $editedItem['反転フラグ'],
                ]);
                $item->save();
            }

            DB::table('M_プレスアシスト加工手順 as a')
                ->join('M_プレスアシスト位置番号 as b', function($join) {
                    $join->on('a.管理番号', '=', 'b.管理番号')
                         ->on('a.段位置', '=', 'b.位置番号');
                })
                ->where('a.作業番号', $work_number)
                ->update([
                    'a.モニタ番号' => DB::raw('b.モニタ番号'),
                    'a.入力_検知ピン番号' => DB::raw('b.入力_検知ピン番号'),
                    'a.入力_フットスイッチピン番号' => DB::raw('b.入力_フットスイッチピン番号'),
                    'a.入力_プレス完了ピン番号' => DB::raw('b.入力_プレス完了ピン番号'),
                    'a.出力_プレスピン番号' => DB::raw('b.出力_プレスピン番号'),
                    'a.出力_ライトピン番号' => DB::raw('b.出力_ライトピン番号'),
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function deleteProcedures(Request $request)
    {
        $errMessage = '';

        try {
            if ($request->work_number) {
                M_Procedure::WhereWorkNumber($request->work_number)->delete();
            } else if ($request->ID) {
                M_Procedure::WhereID($request->ID)->delete();
            }
        } catch (\Exception $e) {
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }
}
