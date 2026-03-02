<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductCompletionReport extends Model
{
  protected $table = 'T_生産完了報告';
  protected $primaryKey = ['ライン記号', '受注NO', '受注明細NO', '生産枝NO', '分割NO', '生産日'];
  public $incrementing = false;
  public $timestamps = false;

  public function scopeLineSign($query, $value)
  {
    return $query->where('ライン記号', '=', $value);
  }
  public function scopeProductDate($query, $value)
  {
    return $query->where('生産日', '=', $value);
  }

  public function scopeSerial($query, $value)
  {
    return $query->where('生産連番', '=', $value);
  }
  public function scopeNoCancel($query)
  {
    return $query->where('キャンセル区分', '=', "0");
  }
  public function scopePreviousDate($query, $isProductToday, $productDate){
    if (!$isProductToday) {
      return $query->where(DB::raw("CONVERT(NVARCHAR, [実生産日時], 111)"), "=", $productDate);
    }else{
      return $query;
    }
  }

}
