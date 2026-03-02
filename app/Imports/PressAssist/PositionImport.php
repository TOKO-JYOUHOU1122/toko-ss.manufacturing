<?php

namespace App\Imports\PressAssist;

use App\Models\PressAssist\M_Position;
use Maatwebsite\Excel\Concerns\{ToModel, WithHeadingRow, WithValidation};

class PositionImport implements ToModel, WithHeadingRow, WithValidation
{
    protected array $seenPositions = [];

    public function model(array $row)
    {
        $item = M_Position::firstOrNew(['管理番号' => $row['管理番号'], '位置番号' => $row['位置番号']]);

        $item->fill([
            '管理番号' => $row['管理番号'] ?? null,
            '位置番号' => $row['位置番号'] ?? null,
            'モニタ番号' => $row['モニタ番号'] ?? null,
            '入力_検知ピン番号' => $row['入力_検知ピン番号'] ?? null,
            '入力_フットスイッチピン番号' => $row['入力_フットスイッチピン番号'] ?? null,
            '入力_プレス完了ピン番号' => $row['入力_プレス完了ピン番号'] ?? null,
            '出力_プレスピン番号' => $row['出力_プレスピン番号'] ?? null,
            '出力_ライトピン番号' => $row['出力_ライトピン番号'] ?? null,
        ]);

        return $item;
    }


    public function rules(): array
    {
        return [
            '*.管理番号' => ['required', 'string', 'max:15'],
            '*.位置番号'  => ['required', 'string', 'max:15',
                function ($attribute, $value, $fail) {
                    $key = strtolower($value);
                    if (isset($this->seenPositions[$key])) {
                        $fail('同一ファイル内で位置番号が重複しています。');
                    }
                    $this->seenPositions[$key] = true;
                },
            ],
            '*.モニタ番号' => ['required', 'numeric', 'min:1',  'max:10'],
            '*.入力_検知ピン番号' => ['required', 'numeric', 'min:0', 'max:127'],
            '*.入力_フットスイッチピン番号' => ['required', 'numeric', 'min:0', 'max:127'],
            '*.入力_プレス完了ピン番号' => ['required', 'numeric', 'min:0', 'max:127'],
            '*.出力_プレスピン番号' => ['required', 'numeric', 'min:0', 'max:127'],
            '*.出力_ライトピン番号' => ['numeric', 'min:0', 'max:127'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.管理番号.required' => '管理番号は必須です。',
            '*.管理番号.max' => '管理番号は15文字以内でなければなりません。',
            '*.位置番号.required' => '位置番号は必須です。',
            '*.位置番号.max' => '位置番号は15文字以内でなければなりません。',
            '*.モニタ番号.required' => 'モニタ番号は必須です。',
            '*.モニタ番号.numeric' => 'モニタ番号は数値でなければなりません。',
            '*.モニタ番号.min' => 'モニタ番号は1以上でなければなりません。',
            '*.モニタ番号.max' => 'モニタ番号は10以内でなければなりません。',
            '*.入力_検知ピン番号.required' => '入力_検知ピン番号は必須です。',
            '*.入力_検知ピン番号.numeric' => '入力_検知ピン番号は数値でなければなりません。',
            '*.入力_検知ピン番号.min' => '入力_検知ピン番号は0以上でなければなりません。',
            '*.入力_検知ピン番号.max' => '入力_検知ピン番号は127以下でなければなりません。',
            '*.入力_フットスイッチピン番号.required' => '入力_フットスイッチピン番号は必須です。',
            '*.入力_フットスイッチピン番号.numeric' => '入力_フットスイッチピン番号は数値でなければなりません。',
            '*.入力_フットスイッチピン番号.min' => '入力_フットスイッチピン番号は0以上でなければなりません。',
            '*.入力_フットスイッチピン番号.max' => '入力_フットスイッチピン番号は127以下でなければなりません。',
            '*.入力_プレス完了ピン番号.required' => '入力_プレス完了ピン番号は必須です。',
            '*.入力_プレス完了ピン番号.numeric' => '入力_プレス完了ピン番号は数値でなければなりません。',
            '*.入力_プレス完了ピン番号.min' => '入力_プレス完了ピン番号は0以上でなければなりません。',
            '*.入力_プレス完了ピン番号.max' => '入力_プレス完了ピン番号は127以下でなければなりません。',
            '*.出力_プレスピン番号.required' => '出力_プレスピン番号は必須です。',
            '*.出力_プレスピン番号.numeric' => '出力_プレスピン番号は数値でなければなりません。',
            '*.出力_プレスピン番号.min' => '出力_プレスピン番号は0以上でなければなりません。',
            '*.出力_プレスピン番号.max' => '出力_プレスピン番号は127以下でなければなりません。',
            '*.出力_ライトピン番号.numeric' => '出力_ライトピン番号は数値でなければなりません。',
            '*.出力_ライトピン番号.min' => '出力_ライトピン番号は0以上でなければなりません。',
            '*.出力_ライトピン番号.max' => '出力_ライトピン番号は127以下でなければなりません。',
        ];
    }
}
