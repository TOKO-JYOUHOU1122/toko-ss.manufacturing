<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use App\Models\PressAssist\M_Position;
use App\Models\PressAssist\M_Particular_Info;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PressAssist\M_Procedure;
use App\Models\PressAssist\M_Particular_Instruction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PressAssistProcedureController extends Controller
{
    public function procedure(Request $request)
    {
        $work_numbers = M_Procedure::select('作業番号')
            ->groupBy('作業番号')
            ->OrderByWorkNumberNumeric()
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
        $procedures = M_Procedure::from('M_プレスアシスト加工手順 as a')
            ->select('a.*')
            ->selectSub(function ($query) {
                $query->from('M_プレスアシスト特殊指示 as b')
                    ->selectRaw("STRING_AGG(CAST(b.ID as varchar(500)), ',')")
                    ->whereColumn('a.作業番号', 'b.作業番号')
                    ->whereColumn('a.作業順', 'b.作業順');
            }, '特殊指示ID')
            ->where('a.作業番号', $request->work_number)
            ->orderBy('a.ID', 'asc')
            ->get();

        $procedures->transform(function ($item) {
            $item->特殊指示ID = $item->特殊指示ID ? array_map('intval', explode(',', $item->特殊指示ID)) : [];
            return $item;
        });

        return $procedures;
    }

    public function registProcedures(Request $request)
    {
        $errMessage = '';
        $work_number = $request->work_number;
        $editable_items = $request->editable_items;

        try {
            \DB::beginTransaction();

            foreach ($editable_items as $editedItem) {
                if ($editedItem['削除区分']) {
                    if ($editedItem['ID']) M_Procedure::WhereID($editedItem['ID'])->delete();
                    continue;
                }

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

            DB::connection('sqlsrv_seisanhojyo')->table('M_プレスアシスト加工手順 as a')
                ->join('M_プレスアシスト位置番号 as b', function ($join) {
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

    public function previewProcedures(Request $request)
    {
        $procedures = M_Procedure::WhereWorkNumber($request->work_number)
            ->orderBy('作業順', 'asc')
            ->get();

        // $proceduresを別の配列に入れる
        $mergedProcedures = [];
        $particular_instructions = [];
        $arrayFileds = ['ID', '段位置', '入力_検知ピン番号', '入力_フットスイッチピン番号', '入力_プレス完了ピン番号', '出力_プレスピン番号', '出力_ライトピン番号'];

        foreach ($procedures as $procedure) {
            $lastIndex = count($mergedProcedures) - 1;
            //同作業順の場合同一のものがなければ追加
            if ($lastIndex >= 0 && $mergedProcedures[$lastIndex]['作業順'] === $procedure['作業順']) {
                foreach ($arrayFileds as $field) {
                    if (!is_array($mergedProcedures[$lastIndex][$field])) {
                        $mergedProcedures[$lastIndex][$field] = [$mergedProcedures[$lastIndex][$field]];
                    }
                }
            } else {
                $mergedProcedures[] = $procedure->toArray();
                $currentIndex = count($mergedProcedures) - 1;
                foreach ($arrayFileds as $field) {
                    $mergedProcedures[$currentIndex][$field] = [$mergedProcedures[$currentIndex][$field]];
                }
                $mergedProcedures[$currentIndex]['型図画像'] = self::getImage($procedure['型図パス'])->getData()->image;
            }

            $procedure_id = $procedure['ID'];
            $particular_instructions[$procedure_id][] = self::getParticularInstructions($procedure_id)->toArray();
        }

        return Inertia::render(
            'PressAssist/PressAssistMasterProcedurePreview',
            [
                'procedures' => $mergedProcedures,
                'particular_instructions' => $particular_instructions,
            ]
        );
    }

    private function getParticularInstructions(int $procedure_id)
    {
        $particular_instructions = M_Particular_Instruction::WhereProcedure_id($procedure_id)
            ->orderBy('ID', 'asc')
            ->get();

        return $particular_instructions;
    }

    public function fetchParticularInstructions(Request $request)
    {
        $work_number = $request->work_number;
        $work_order = $request->work_order;

        $instructions = M_Particular_Instruction::where('作業番号', $work_number)
            ->where('作業順', $work_order)
            ->orderBy('ID', 'asc')
            ->get();

        return $instructions;
    }

    public function registParticularInstruction(Request $request)
    {
        $errMessage = '';
        $editedItems = $request->editedItems;

        try {
            \DB::beginTransaction();

            foreach ($editedItems as $editedItem) {
                $instruction = $editedItem['ID'] ? M_Particular_Instruction::WhereId($editedItem['ID'])->first() : null;

                if (isset($editedItem['削除区分']) && $editedItem['削除区分'] === true) {
                    if ($instruction) $instruction->delete();

                    continue;
                }

                if (!$instruction) {
                    $instruction = new M_Particular_Instruction();
                }

                $particularInfo = M_Particular_Info::where('管理番号', $editedItem['管理番号'])
                    ->where('指示区分', $editedItem['指示名'])
                    ->where('登録コード', $editedItem['表示1'])
                    ->first();
                if (!$particularInfo) {
                    throw new \Exception('対応する特殊情報が見つかりません。管理番号: ' . $editedItem['管理番号'] . ' 登録コード: ' . $editedItem['表示1']);
                }

                $instruction->fill([
                    '作業番号' => $request->work_number,
                    '作業順' => $request->work_order,
                    '管理番号' => $editedItem['管理番号'],
                    '置換フラグ' => $editedItem['置換フラグ'],
                    '条件' => $editedItem['条件'],
                    '指示名' => $particularInfo['指示区分'],
                    '表示1' => $particularInfo['登録コード'],
                    '表示2' => $particularInfo['表示文字列'],
                    '段位置' => $particularInfo['段位置'],
                    'モニタ番号' => $particularInfo['モニタ番号'],
                    '入力ピン番号' => $particularInfo['入力ピン番号'],
                    '出力ピン番号' => $particularInfo['出力ピン番号'],
                ]);

                $instruction->save();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function deleteParticularInstruction(Request $request)
    {
        $errMessage = '';

        try {
            M_Particular_Instruction::WhereId($request->ID)->delete();
        } catch (\Exception $e) {
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function fetchParticularOptions(Request $request)
    {
        $equipment_numbers = M_Position::select('管理番号')
            ->groupBy('管理番号')
            ->orderBy('管理番号')
            ->pluck('管理番号')
            ->toArray();

        $unique_info = M_Particular_Info::select('管理番号', '指示区分', '登録コード')
            ->groupBy('管理番号', '指示区分', '登録コード')
            ->orderBy('管理番号')
            ->orderBy('指示区分')
            ->orderBy('登録コード')
            ->get()
            ->toArray();

        return [
            'equipment_numbers' => $equipment_numbers,
            'unique_info' => $unique_info,
        ];
    }

    public function getImage(string $path)
    {
        if (!file_exists($path)) {
            return response()->json(['image' => null]);
        }
        $image = base64_encode(file_get_contents($path));
        $mime = mime_content_type($path);

        return response()->json([
            'image' => "data:{$mime};base64,{$image}"
        ]);
    }
}
