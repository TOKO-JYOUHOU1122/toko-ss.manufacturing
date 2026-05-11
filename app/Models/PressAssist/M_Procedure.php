<?php

namespace App\Models\PressAssist;

use Illuminate\Database\Eloquent\Model;

class M_Procedure extends Model
{
    protected $table = 'M_プレスアシスト加工手順';
    protected $connection = 'sqlsrv_seisanhojyo';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];

    protected $fillable = [
        '作業番号',
        '管理番号',
        '作業順',
        '型図パス',
        '段位置',
        'モニタ番号',
        '画像位置',
        '反転フラグ',
        '入力_検知ピン番号',
        '入力_フットスイッチピン番号',
        '入力_プレス完了ピン番号',
        '出力_プレスピン番号',
        '出力_ライトピン番号',
    ];

    public $incrementing = true;
    public $timestamps = false;

    public function scopeWhereId($query, $id) {
        return $query->where('ID', $id);
    }

    public function scopeWhereWorkNumber($query, $work_number) {
        return $query->where('作業番号', $work_number);
    }

    public function scopeWhereEquipmentNumber($query, $equipment_number) {
        return $query->where('管理番号', $equipment_number);
    }

    public function scopeOrderByWorkNumberNumeric($query, $direction = 'asc')
    {
        return $query->orderByRaw("
            CASE
                WHEN TRY_CAST(作業番号 AS INT) IS NOT NULL THEN 0
                ELSE 1
            END,
            TRY_CAST(作業番号 AS INT) {$direction},
            作業番号 {$direction}
        ");
    }
}
