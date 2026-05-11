<?php

namespace App\Http\Controllers\PressAssist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PressAssist\M_Item;
use App\Exports\PressAssist\ItemExport;
use App\Imports\PressAssist\ItemImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PressAssistItemController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('PressAssist/PressAssistMasterTop', []);
    }

    public function item(Request $request)
    {
        $items = self::fetchItem($request)->toArray();
        return Inertia::render('PressAssist/PressAssistMasterItem',
            [
                'items' => $items
            ]);
    }

    public function fetchItem(Request $request)
    {
        $items = M_Item::WhereDivision($request->division)
            ->WhereItemName($request->item_name)
            ->WhereWorkNumber($request->work_number)
            ->OrderByWorkNumberNumeric()
            ->orderBy('品名', 'asc')
            ->get();

        return $items;
    }

    public function registItem(Request $request)
    {
        $errMessage = '';
        $editedItem = $request->editedItem;
        try {
            \DB::beginTransaction();

            $item = M_Item::WhereID($editedItem['ID'])->first();
            if (!$editedItem['ID'] || !$item) $item = new M_Item();

            $item->fill([
                '機種' => $editedItem['機種'],
                '作業番号' => $editedItem['作業番号'],
                '品名' => $editedItem['品名'],
                '表示順' => $editedItem['表示順'],
                '同時加工数' => $editedItem['同時加工数'],
                '条件' => $editedItem['条件'],
            ]);
            $item->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function deleteItem(Request $request)
    {
        $errMessage = '';

        try {
            M_Item::WhereID($request->ID)->delete();
        } catch (\Exception $e) {
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }

        return ['errMessage' => $errMessage];
    }

    public function exportItem(Request $request) {
        return Excel::download((new ItemExport)->setRequest($request), 'プレスアシスト品目.csv');
    }

    public function importItem(Request $request)
    {
        $errMessage = '';
        try {
            \DB::beginTransaction();
            $file = $request->file('file');
            Excel::import(new ItemImport, $file);
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error($e);
            $errMessage = 'エラーが発生しました。' . $e->getMessage();
        }
        return ['errMessage' => $errMessage ?? ''];
    }
}
