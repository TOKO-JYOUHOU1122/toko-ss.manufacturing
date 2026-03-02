<?php

namespace App\Exports\PressAssist;

use App\Http\Controllers\PressAssist\PressAssistPositionController;
use App\Models\PressAssist\M_Position;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings};

class PositionExport implements FromCollection, WithMapping, WithHeadings, WithCustomCsvSettings
{

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function map($item): array
    {
        return [
            $item->管理番号,
            $item->位置番号,
            $item->モニタ番号,
            $item->入力_検知ピン番号,
            $item->入力_フットスイッチピン番号,
            $item->入力_プレス完了ピン番号,
            $item->出力_プレスピン番号,
            $item->出力_ライトピン番号,
        ];
    }

    public function headings(): array
    {
        return M_Position::getHeadings();
    }

    public function collection()
    {
        $controller = new PressAssistPositionController();
        $positions = $controller->fetchPosition($this->request);
        return $positions;
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
