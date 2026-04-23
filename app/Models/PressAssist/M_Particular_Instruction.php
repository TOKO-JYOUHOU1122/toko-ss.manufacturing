<?php

namespace App\Models\PressAssist;

use Illuminate\Database\Eloquent\Model;

class M_Particular_Instruction extends Model
{
    protected $table = 'M_プレスアシスト特殊指示';
    protected $connection = 'sqlsrv_seisanhojyo';
    protected $primaryKey = 'ID';
    protected $guarded = ['ID'];

    protected $fillable = [
        '管理番号',
        '指示名',
        '段位置',
        'モニタ番号',
        '段位置',
        '条件',
        'プレスアシスト加工手順_ID',
        '入力ピン番号',
        '出力ピン番号',
        '置換フラグ',
        '金型番号',
        '表示1',
        '表示2',
    ];


    public $timestamps = false;

    public function scopeWhereId($query, $id) {
        return $query->where('ID', $id);
    }

    public function scopeWhereEquipmentNumber($query, $equipment_number) {
        return $query->where('管理番号', $equipment_number);
    }

    public function scopeWhereDivision($query, $division) {
        return $query->where('指示名', 'like', '%' . $division . '%');
    }

    public function scopeWherePosition($query, $position_number) {
        return $query->where('位置番号', 'like', '%' . $position_number . '%');
    }

    public function scopeWhereProcedure_id($query, $procedure_id) {
        return $query->where('プレスアシスト加工手順_ID', $procedure_id);
    }
}
