<?php

namespace App\Models\PressAssist;

use Illuminate\Database\Eloquent\Model;

class M_Item extends Model
{
    protected $table = 'M_プレスアシスト品目';
    protected $connection = 'sqlsrv_seisanhojyo';
    protected $primaryKey = 'ID';

    protected $fillable = [
        '機種',
        '作業番号',
        '品名',
        '表示順',
        '同時加工数',
        '条件'
    ];

    public $incrementing = true;
    public $timestamps = false;

    public function scopeWhereId($query, $id)
    {
        if ($id == null) return $query;

        return $query->where('ID', $id);
    }

    public function scopeWhereDivision($query, $division)
    {
        if ($division == null) return $query;

        return $query->where('機種', $division);
    }

    public function scopeWhereWorkNumber($query, $work_number)
    {
        if ($work_number == null) return $query;

        return $query->where('作業番号', $work_number);
    }

    public function scopeWhereItemName($query, $item_name)
    {
        if ($item_name == null) return $query;

        return $query->where('品名', 'like', '%' . $item_name . '%');
    }

    public function scopeWhereCondition($query, $condition)
    {
        if ($condition == null) return $query;

        return $query->where('条件', 'like', '%' . $condition . '%');
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

    public static function getHeadings()
    {
        return [
            '機種',
            '作業番号',
            '品名',
            '表示順',
            '同時加工数',
            '条件'
        ];
    }
}
