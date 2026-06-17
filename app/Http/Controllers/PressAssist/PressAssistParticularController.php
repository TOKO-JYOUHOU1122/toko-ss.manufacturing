<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PressAssist\M_Position;
use App\Models\PressAssist\M_Particular_Info;
use App\Exports\PressAssist\ParticularExport;
use App\Imports\PressAssist\ParticularImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PressAssistParticularController extends Controller
{
    public function particular(Request $request)
    {
        $particulars = self::fetchParticular($request)->toArray();
        $position_numbers = M_Position::select([
                '管理番号',
                DB::raw("LEFT(位置番号, PATINDEX('%[0-9]%', 位置番号)) AS 段位置")
            ])
            ->groupBy(['管理番号', DB::raw("LEFT(位置番号, PATINDEX('%[0-9]%', 位置番号))")])
            ->orderBy('管理番号', 'asc')
            ->orderByRaw("LEFT(位置番号, PATINDEX('%[0-9]%', 位置番号)) ASC")
            ->get()->toArray();

        return Inertia::render(
            'PressAssist/PressAssistMasterParticular',
            [
                'particulars' => $particulars,
                'position_numbers' => $position_numbers
            ]
        );
    }

    public function fetchParticular(Request $request)
    {
        $particulars = M_Particular_Info::WhereEquipmentNumber($request->equipment_number)
            ->WhereCategory($request->category)
            ->WherePosition($request->position)
            ->orderBy('管理番号', 'asc')
            ->orderBy('指示区分', 'asc')
            ->orderBy('登録コード', 'asc')
            ->get();

        return $particulars;
    }

    public function registParticular(Request $request)
    {
        $errMessage = '';
        $editedItem = $request->editedItem;
        try {
            \DB::beginTransaction();

            $particular = M_Particular_Info::WhereID($editedItem['ID'])->first();
            if (!$editedItem['ID'] || !$particular) $particular = new M_Particular_Info();

            $particular->fill([
                '管理番号' => $editedItem['管理番号'],
                '登録コード' => $editedItem['登録コード'],
                '指示区分' => $editedItem['指示区分'],
                '段位置' => $editedItem['段位置'],
                'モニタ番号' => $editedItem['モニタ番号'],
                '入力ピン番号' => $editedItem['入力ピン番号'],
                '出力ピン番号' => $editedItem['出力ピン番号'],
                '条件' => $editedItem['条件'],
                '置換フラグ' => $editedItem['置換フラグ'],
                '表示1' => $editedItem['表示1'],
                '表示2' => $editedItem['表示2'],
            ]);
            $particular->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function deleteParticular(Request $request)
    {
        $errMessage = '';

        try {
            M_Particular_Info::WhereID($request->ID)->delete();
        } catch (\Exception $e) {
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function exportParticular(Request $request)
    {
        return Excel::download((new ParticularExport)->setRequest($request), 'プレスアシスト特殊指示情報.csv');
    }

    public function importParticular(Request $request)
    {
        $errMessage = '';
        try {
            \DB::beginTransaction();
            $file = $request->file('file');
            Excel::import(new ParticularImport, $file);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }
        return ['errMessage' => $errMessage ?? ''];
    }
}
