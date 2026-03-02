<?php

namespace App\Models\PressAssist;

use Illuminate\Database\Eloquent\Model;

class M_Position extends Model
{
    protected $table = 'M_プレスアシスト位置番号';
    protected $connection = 'sqlsrv_seisanhojyo';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];

    protected $fillable = [
        '管理番号',
        '位置番号',
        'モニタ番号',
        '入力_検知ピン番号',
        '入力_フットスイッチピン番号',
        '出力_プレスピン番号',
        '入力_プレス完了ピン番号',
        '出力_ライトピン番号'
    ];

    public function scopeWhereId($query, $id) {
        if($id == null) return $query;

        return $query->where('ID', $id);
    }

    public function scopeWhereEquipmentNumber($query, $equipment_number) {
        if($equipment_number == null) return $query;

        return $query->where('管理番号', $equipment_number);
    }

    public function scopeWherePosition($query, $position_number) {
        if($position_number == null) return $query;

        return $query->where('位置番号', 'like', '%' . $position_number . '%');
    }

    public static function getHeadings()
    {
        return [
            '管理番号',
            '位置番号',
            'モニタ番号',
            '入力_検知ピン番号',
            '入力_フットスイッチピン番号',
            '入力_プレス完了ピン番号',
            '出力_プレスピン番号',
            '出力_ライトピン番号',
        ];
    }
}
