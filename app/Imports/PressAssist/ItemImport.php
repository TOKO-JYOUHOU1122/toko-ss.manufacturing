<?php

namespace App\Imports\PressAssist;

use App\Models\PressAssist\M_Item;
use Maatwebsite\Excel\Concerns\{ToModel, WithHeadingRow, WithValidation};

class ItemImport implements ToModel, WithHeadingRow, WithValidation
{
    protected array $seenItemNames = [];

    public function model(array $row)
    {
        $item = M_Item::firstOrNew(['機種' => $row['機種'], '品名' => $row['品名']]);

        $item->fill([
            '機種'       => $row['機種'] ?? null,
            '作業番号'     => $row['作業番号'] ?? null,
            '品名'       => $row['品名'] ?? null,
            '表示品名'     => $row['表示品名'] ?? null,
            '表示順'      => $row['表示順'] ?? null,
            '同時加工数'    => $row['同時加工数'] ?? null,
            '条件'       => $row['条件'] ?? null,
        ]);

        return $item;
    }


    public function rules(): array
    {
        return [
            '*.機種' => ['required', 'string', 'max:15'],
            '*.作業番号'  => ['required', 'numeric', 'max:99999'],
            '*.品名' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $key = strtolower($value);
                    if (isset($this->seenItemNames[$key])) {
                        $fail('同一ファイル内で品名が重複しています。');
                    }
                    $this->seenItemNames[$key] = true;
                },

            ],
            '*.表示順' => ['required', 'numeric', 'max:999999'],
            '*.同時加工数' => ['required', 'numeric', 'min:1', 'max:2'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.機種.required' => '機種は必須です。',
            '*.機種.max' => '機種は15文字以内でなければなりません。',
            '*.作業番号.required' => '作業番号は必須です。',
            '*.作業番号.numeric' => '作業番号は数値でなければなりません。',
            '*.作業番号.max' => '作業番号は99999以内でなければなりません。',
            '*.品名.required' => '品名は必須です。',
            '*.品名.max' => '品名は50文字以内でなければなりません。',
            '*.表示順.required' => '表示順は必須です。',
            '*.表示順.numeric' => '表示順は数値でなければなりません。',
            '*.表示順.max' => '表示順は999999以内でなければなりません。',
            '*.同時加工数.required' => '同時加工数は必須です。',
            '*.同時加工数.numeric' => '同時加工数は数値でなければなりません。',
            '*.同時加工数.min' => '同時加工数は1以上でなければなりません。',
            '*.同時加工数.max' => '同時加工数は2以下でなければなりません。',
        ];
    }
}
