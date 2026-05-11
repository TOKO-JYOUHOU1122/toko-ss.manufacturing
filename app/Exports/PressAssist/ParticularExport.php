<?php

namespace App\Exports\PressAssist;

use App\Http\Controllers\PressAssist\PressAssistParticularController;
use App\Models\PressAssist\M_Particular_Info;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings};

class ParticularExport implements FromCollection, WithMapping, WithHeadings, WithCustomCsvSettings
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
            $item->指示区分,
            $item->登録コード,
            $item->段位置,
            $item->モニタ番号,
            $item->条件,
            $item->入力ピン番号,
            $item->出力ピン番号,
            $item->置換フラグ,
            $item->表示文字列,
        ];
    }

    public function headings(): array
    {
        return M_Particular_Info::getHeadings();
    }

    public function collection()
    {
        $controller = new PressAssistParticularController();
        $particulars = $controller->fetchParticular($this->request);
        return $particulars;
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
