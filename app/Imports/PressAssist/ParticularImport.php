<?php

namespace App\Imports\PressAssist;

use App\Models\PressAssist\M_Particular_Info;
use Maatwebsite\Excel\Concerns\{ToModel, WithHeadingRow, WithValidation};

class ParticularImport implements ToModel, WithHeadingRow, WithValidation
{
    protected array $seenPositions = [];

    public function model(array $row)
    {
        $item = M_Particular_Info::firstOrNew(['管理番号' => $row['管理番号'], '指示区分' => $row['指示区分'], '登録コード' => $row['登録コード']]);

        $item->fill([
            '管理番号' => $row['管理番号'] ?? null,
            '指示区分' => $row['指示区分'] ?? null,
            '登録コード' => $row['登録コード'] ?? null,
            '段位置' => $row['段位置'] ?? null,
            'モニタ番号' => $row['モニタ番号'] ?? null,
            '条件' => $row['条件'] ?? null,
            '入力ピン番号' => $row['入力ピン番号'] ?? null,
            '出力ピン番号' => $row['出力ピン番号'] ?? null,
            '置換フラグ' => $row['置換フラグ'] ?? null,
            '表示1' => $row['表示1'] ?? null,
            '表示2' => $row['表示2'] ?? null,
        ]);

        return $item;
    }


    public function rules(): array
    {
        return [
            '*.管理番号' => ['required', 'string', 'max:15'],
            '*.指示区分' => ['required', 'string', 'max:15'],
            '*.登録コード'  => ['required', 'string', 'max:15',
                function ($attribute, $value, $fail) {
                    $key = strtolower($value);
                    if (isset($this->seenPositions[$key])) {
                        $fail('同一ファイル内で登録コードが重複しています。');
                    }
                    $this->seenPositions[$key] = true;
                },
            ],
            '*.段位置' => ['required', 'string', 'max:15'],
            '*.モニタ番号' => ['numeric', 'min:1',  'max:10'],
            '*.入力ピン番号' => ['numeric', 'min:0', 'max:127'],
            '*.出力ピン番号' => ['numeric', 'min:0', 'max:127'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.管理番号.required' => '管理番号は必須です。',
            '*.管理番号.max' => '管理番号は15文字以内でなければなりません。',
            '*.指示区分.required' => '指示区分は必須です。',
            '*.指示区分.max' => '指示区分は15文字以内でなければなりません。',
            '*.登録コード.required' => '登録コードは必須です。',
            '*.登録コード.max' => '登録コードは15文字以内でなければなりません。',
            '*.段位置.required' => '段位置は必須です。',
            '*.段位置.max' => '段位置は15文字以内でなければなりません。',
            '*.モニタ番号.numeric' => 'モニタ番号は数値でなければなりません。',
            '*.モニタ番号.min' => 'モニタ番号は1以上でなければなりません。',
            '*.モニタ番号.max' => 'モニタ番号は10以内でなければなりません。',
            '*.入力ピン番号.numeric' => '入力ピン番号は数値でなければなりません。',
            '*.入力ピン番号.min' => '入力ピン番号は0以上でなければなりません。',
            '*.入力ピン番号.max' => '入力ピン番号は127以下でなければなりません。',
            '*.出力ピン番号.numeric' => '出力ピン番号は数値でなければなりません。',
            '*.出力ピン番号.min' => '出力ピン番号は0以上でなければなりません。',
            '*.出力ピン番号.max' => '出力ピン番号は127以下でなければなりません。',
        ];
    }
}
