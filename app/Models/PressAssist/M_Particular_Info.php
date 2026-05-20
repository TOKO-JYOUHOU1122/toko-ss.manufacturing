<?php

namespace App\Models\PressAssist;

use Illuminate\Database\Eloquent\Model;

class M_Particular_Info extends Model
{
    protected $table = 'M_プレスアシスト特殊指示情報';
    protected $connection = 'sqlsrv_seisanhojyo';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];

    protected $fillable = [
        '管理番号',
        '指示区分',
        '登録コード',
        '段位置',
        'モニタ番号',
        '入力ピン番号',
        '出力ピン番号',
        '表示文字列',
    ];

    public $incrementing = true;
    public $timestamps = false;

    public function scopeWhereId($query, $id) {
        return $query->where('ID', $id);
    }

    public function scopeWhereEquipmentNumber($query, $equipment_number) {
        if (!$equipment_number) return $query;
        return $query->where('管理番号', $equipment_number);
    }

    public function scopeWhereCategory($query, $category) {
        if (!$category) return $query;
        return $query->where('指示区分', 'like', '%' . $category . '%');
    }

    public function scopeWherePosition($query, $position) {
        if (!$position) return $query;
        return $query->where('段位置', 'like', '%' . $position . '%');
    }

    public static function getHeadings()
    {
        return [
            '管理番号',
            '指示区分',
            '登録コード',
            '段位置',
            'モニタ番号',
            '条件',
            '入力ピン番号',
            '出力ピン番号',
            '置換フラグ',
            '表示文字列',
        ];
    }
}
