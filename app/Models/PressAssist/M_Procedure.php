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


    public $timestamps = true;

    public function scopeWhereId($query, $id) {
        if($id == null) return $query;

        return $query->where('ID', $id);
    }

    public function scopeWhereWorkNumber($query, $work_number) {
        if($work_number == null) return $query;

        return $query->where('作業番号', $work_number);
    }

    public function scopeWhereEquipmentNumber($query, $equipment_number) {
        if($equipment_number == null) return $query;

        return $query->where('管理番号', $equipment_number);
    }
}
