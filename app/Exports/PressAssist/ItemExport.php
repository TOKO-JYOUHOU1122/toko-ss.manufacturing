<?php

namespace App\Exports\PressAssist;

use App\Http\Controllers\PressAssist\PressAssistItemController;
use App\Models\PressAssist\M_Item;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings};

class ItemExport implements FromCollection, WithMapping, WithHeadings, WithCustomCsvSettings
{

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function map($item): array
    {
        return [
            $item->機種,
            $item->作業番号,
            $item->品名,
            $item->表示順,
            $item->同時加工数,
            $item->条件
        ];
    }

    public function headings(): array
    {
        return M_Item::getHeadings();
    }

    public function collection()
    {
        $controller = new PressAssistItemController();
        $items = $controller->fetchItem($this->request);
        return $items;
    }


    public function getCsvSettings(): array
    {
        return [
            'delimiter'      => ',',
            'enclosure'      => '"',
            'line_ending'    => "\r\n",
            'use_bom'        => true,
        ];
    }
}
