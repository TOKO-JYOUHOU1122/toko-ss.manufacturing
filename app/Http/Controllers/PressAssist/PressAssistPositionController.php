<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PressAssist\M_Position;
use App\Exports\PressAssist\PositionExport;
use App\Imports\PressAssist\PositionImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PressAssistPositionController extends Controller
{
    public function position(Request $request)
    {
        $positions = self::fetchPosition($request)->toArray();
        return Inertia::render('PressAssist/PressAssistMasterPosition',
            [
                'positions' => $positions
            ]);
    }

    public function fetchPosition(Request $request)
    {
        $positions = M_Position::WhereEquipmentNumber($request->equipment_number)
            ->WherePosition($request->position_number)
            ->orderBy('管理番号', 'asc')
            ->orderBy('位置番号', 'asc')
            ->get();

        return $positions;
    }

    public function registPosition(Request $request)
    {
        $errMessage = '';
        $editedItem = $request->editedItem;
        try {
            \DB::beginTransaction();

            $position = M_Position::WhereID($editedItem['ID'])->first();
            if (!$editedItem['ID'] || !$position) $position = new M_Position();

            $position->fill([
                '管理番号' => $editedItem['管理番号'],
                '位置番号' => $editedItem['位置番号'],
                'モニタ番号' => $editedItem['モニタ番号'],
                '入力_検知ピン番号' => $editedItem['入力_検知ピン番号'],
                '入力_フットスイッチピン番号' => $editedItem['入力_フットスイッチピン番号'],
                '入力_プレス完了ピン番号' => $editedItem['入力_プレス完了ピン番号'],
                '出力_プレスピン番号' => $editedItem['出力_プレスピン番号'],
                '出力_ライトピン番号' => $editedItem['出力_ライトピン番号'],
            ]);
            $position->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function deletePosition(Request $request)
    {
        $errMessage = '';

        try {
            M_Position::WhereID($request->ID)->delete();
        } catch (\Exception $e) {
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function exportPosition(Request $request) {
        return Excel::download((new PositionExport)->setRequest($request), 'プレスアシスト位置番号.csv');
    }

    public function importPosition(Request $request)
    {
        $errMessage = '';
        try {
            \DB::beginTransaction();
            $file = $request->file('file');
            Excel::import(new PositionImport, $file);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }
        return ['errMessage' => $errMessage ?? ''];
    }
}
