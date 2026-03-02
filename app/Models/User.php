<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class User extends Model
{
    protected $table = 'M_社員';
    protected $primaryKey = '社員番号';
    protected $fillable = [
        '社員番号',
        '社員名'
    ];

    private static $DEPARTMENT = [
        'EC' => 'イスターカーテン',
        'EW' => 'ウインド',
        'EX' => 'エクステリア'
    ];

    public function scopeDepartmentCode($query, $department_code){
        if ($department_code === null) return $query;

        $department = self::$DEPARTMENT[$department_code] ?? null;
        if ($department === null) return $query;

        return $query->where("所属事業部", "=", $department);
    }

    public function scopeLineSign($query, $line_sign){
        if ($line_sign === null) return $query;
        return $query->where(DB::raw($line_sign), "=", 1);
    }
}
