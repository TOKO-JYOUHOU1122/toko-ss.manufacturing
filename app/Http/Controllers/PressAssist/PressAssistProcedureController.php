<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use App\Models\PressAssist\M_Position;
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

            DB::table('M_プレスアシスト加工手順 as a')
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
            if ($lastIndex >= 0 && $mergedProcedures[$lastIndex]['作業順'] === $procedure['作業順']) {
                //画像位置はORで結合、その他は同一のものがなければ追加
                $imageBit = $procedure['画像位置'];
                $existingImageBit = $mergedProcedures[$lastIndex]['画像位置'];
                $maxLen = max(strlen($existingImageBit), strlen($imageBit));
                $merged = '';

                for ($i = 0; $i < $maxLen; $i++) {
                    $bit1 = $existingImageBit[$i] ?? '0';
                    $bit2 = $imageBit[$i] ?? '0';
                    $merged .= ($bit1 === '1' || $bit2 === '1') ? '1' : '0';
                }
                $mergedProcedures[$lastIndex]['画像位置'] = $merged;

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

    private function getParticularInstructions($procedure_id)
    {
        $particular_instructions = M_Particular_Instruction::WhereProcedure_id($procedure_id)
            ->orderBy('ID', 'asc')
            ->get();

        return $particular_instructions;
    }

    public function getImage($path)
    {
        if (!file_exists($path)) {
            return response()->json(['image' => null]);
        }
        $image = base64_encode(file_get_contents($path));
        $mime = mime_content_type($path);
        Log::info("画像取得: {$path}, MIMEタイプ: {$mime}");

        return response()->json([
            'image' => "data:{$mime};base64,{$image}"
        ]);
    }
}
