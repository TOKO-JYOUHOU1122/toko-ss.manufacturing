<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class IssueRequestController extends Controller
{
    const department = ['EC' => 'イスター', 'EW' => 'ウインド', 'EX' => 'エクステリア'];
    const issueLineSign = 'FR';

    public function __construct() {}

    private static function nvl($value, $replace)
    {
        return (is_null($value) || $value == 'null') ? $replace : $value;
    }

    public function home(Request $request)
    {
        $lines = DB::connection('sqlsrv_seisanhojyo')->table('M_値リスト')
            ->select(
                '設定値2 AS departmentCode',
                '項目名 AS lineSign',
                '設定値1 AS lineName'
            )
            ->where('区分名', '=', '工場棚入庫設定')
            ->where('項目名', '<>', 'BM')
            ->where(function ($query) use ($request) {
                if ($request->departmentCode) {
                    $query->where('設定値2', '=', $request->departmentCode);
                }
            })
            ->orderBy('表示順', 'asc')
            ->get();

        $departmentCodes = $lines->pluck('departmentCode')->unique()->values();

        return Inertia::render('IssueRequestHome', [
            'departmentCodes' =>  $departmentCodes,
            'lines' => $lines
        ]);
    }

    public function verification(Request $request)
    {
        return Inertia::render('IssueRequestVerification', [
            'targetLine' =>  $request->targetLine,
        ]);
    }

    public function request(Request $request)
    {
        $targetLine = (object) $request->targetLine;
        $staging_list = DB::connection('sqlsrv_seisanhojyo')->table('T_出庫依頼')
            ->select(
                'ID as id',
                '品目CD as itemCode',
                '品目名称 as itemName',
                '依頼数 as requestCount',
                '棚番 as shelfNumber',
                'まるめ数 as roundingCount'
            )
            ->where('ライン', '=', $targetLine->lineSign)
            ->whereNull('更新日時')
            ->orderBy('品目CD', 'asc')
            ->get();

        return Inertia::render('IssueRequestStagingList', [
            'targetLine' =>  $targetLine,
            'stagingList' => $staging_list,
        ]);
    }

    public function history(Request $request)
    {
        $targetLine = (object) $request->targetLine;
        $history = DB::connection('sqlsrv_seisanhojyo')->table('D_部品出庫')
            ->select(
                'D_部品出庫.出庫番号 as shippingNumber',
                DB::raw('FORMAT(D_部品出庫進捗.作成日時, \'yyyy-MM-dd\') as requestDate'),
                '品目CD as itemCode',
                '品名 as itemName',
                DB::raw('sum(ISNULL(指示数量, 0)) as requestCount')
            )
            ->join('D_部品出庫進捗', function ($join) {
                $join->on('D_部品出庫.出庫番号', '=', 'D_部品出庫進捗.出庫番号');
            })
            ->where('D_部品出庫進捗.出庫ライン記号', '=', self::issueLineSign)
            ->where('D_部品出庫進捗.生産ライン記号', '=', $targetLine->lineSign)
            ->where(
                function ($query) use ($targetLine) {
                    switch ($targetLine->departmentCode) {
                        case 'EX':
                            $query->whereNull('D_部品出庫.工場棚入庫日時');
                            break;
                        case 'EW':
                            $query->whereNull('D_部品出庫.出庫日時');
                    }
                }
            )
            ->groupBy('D_部品出庫.出庫番号')
            ->groupBy(DB::raw('FORMAT(D_部品出庫進捗.作成日時, \'yyyy-MM-dd\')'))
            ->groupBy('品目CD')
            ->groupBy('品名')
            ->orderBy('D_部品出庫.品目CD', 'asc')
            ->orderBy(DB::raw('FORMAT(D_部品出庫進捗.作成日時, \'yyyy-MM-dd\')'), 'asc')
            ->get();

        return Inertia::render('IssueRequestHistory', [
            'targetLine' =>  $targetLine,
            'history' => $history,
        ]);
    }

    public function getItem(Request $request)
    {
        $targetLine = (object) $request->targetLine;
        $item = DB::table('M_品目')
            ->select(
                'M_品目.品目名称 as itemName',
                'D_工場在庫.出庫依頼まるめ as roundingCount',
                'M_品目.色種 as color',
                'M_品目.棚番 as shelfNumber',
                'M_品目.棚枝番 as shelfBranchNumber'
            )
            ->join('D_工場在庫', function ($join) {
                $join->on('M_品目.品目CD', '=', 'D_工場在庫.品目CD');
            })
            ->where('M_品目.品目CD', '=', $request->itemCode)
            ->where('D_工場在庫.棚設置区分', '=', $targetLine->lineSign)
            ->where('D_工場在庫.部門', '=', self::department[$targetLine->departmentCode])
            ->get();

        //出庫残数取得
        $remainingCount = DB::connection('sqlsrv_seisanhojyo')->table('D_部品出庫')
            ->select(
                DB::raw('sum(ISNULL(指示数量,0)) as remainingCount')
            )
            ->join('D_部品出庫進捗', function ($join) {
                $join->on('D_部品出庫.出庫番号', '=', 'D_部品出庫進捗.出庫番号');
            })
            ->where('D_部品出庫.品目CD', '=', $request->itemCode)
            ->where('D_部品出庫進捗.出庫ライン記号', '=', self::issueLineSign)
            ->where('D_部品出庫進捗.生産ライン記号', '=', $targetLine->lineSign)
            ->where(
                function ($query) use ($targetLine) {
                    switch ($targetLine->departmentCode) {
                        case 'EX':
                            $query->whereNull('D_部品出庫.工場棚入庫日時');
                            break;
                        case 'EW':
                            $query->whereNull('D_部品出庫.出庫日時');
                    }
                }
            )
            ->value('remainingCount');

        return ['item' => $item, 'remainingCount' => $remainingCount];
    }

    public function update(Request $request)
    {
        $affected = DB::connection('sqlsrv_seisanhojyo')->table('T_出庫依頼')
            ->where('id', '=', $request->id)
            ->update(['依頼数' => $request->requestCount]);
        return ['errMessage' => $affected == 0 ? '更新に失敗しました。再度実行してください。' : ''];
    }

    public function delete(Request $request)
    {
        $affected = DB::connection('sqlsrv_seisanhojyo')
            ->delete('delete from T_出庫依頼 where id = ?', [$request->id]);

        return ['errMessage' => $affected == 0 ? '削除に失敗しました。再度実行してください。' : ''];
    }

    public function registToStaging(Request $request)
    {
        $now = date("Y/m/d H:i:s");
        $affected = DB::connection('sqlsrv_seisanhojyo')
            ->insert(
                'insert into T_出庫依頼 (ライン,品目CD,品目名称,色種,棚番,棚枝番,依頼数,依頼残数,まるめ数,依頼日時) values (?,?,?,?,?,?,?,?,?,?)',
                [
                    $this->nvl($request->lineName, ''),
                    $this->nvl($request->itemCode, ''),
                    $this->nvl($request->itemName, ''),
                    $this->nvl($request->color, ''),
                    $this->nvl($request->shelfNumber, ''),
                    $this->nvl($request->shelfBranchNumber, ''),
                    $this->nvl($request->requestCount, ''),
                    $this->nvl($request->remainingCount, ''),
                    $this->nvl($request->roundingCount, ''),
                    $now
                ]
            );

        return ['errMessage' => $affected == 0 ? '登録に失敗しました。再度実行してください。' : ''];
    }

    private function getSerial()
    {
        $query = DB::select("select 連番 from M_採番 where 項目 = '依頼部品出庫連番'");
        if (count($query) == 0) throw new Exception("依頼部品出庫連番の取得に失敗しました。システム担当者に連絡してください。");

        $affected = DB::update("update M_採番 set 連番 = 連番 + 1 where 項目 = '依頼部品出庫連番'");
        if ($affected == 0) throw new Exception("依頼部品出庫連番の更新に失敗しました。システム担当者に連絡してください。");

        return $query[0]->連番;;
    }

    private function setLock()
    {
       $affected = DB::update("update M_採番 set ロック区分 = 1 where 項目 = '依頼部品出庫連番' and ロック区分 = 0");
       return $affected > 0;
    }
    private function setUnLock()
    {
        $affected = DB::update("update M_採番 set ロック区分 = 0 where 項目 = '依頼部品出庫連番'");
        return $affected > 0;
    }

    public function registToRequest(Request $request)
    {
        $targetLine = (object) $request->targetLine;
        DB::beginTransaction();

        try {
            if (!$this->setLock()) throw new Exception("他のユーザーが処理を行っています。しばらくしてから再度実行してください。");

            $serial = $this->getSerial();

            //D_部品出庫進捗にデータ追加
            DB::connection('sqlsrv_seisanhojyo')
                ->insert(
                    'insert into D_部品出庫進捗 (出庫番号,出庫ライン記号,生産ライン記号,生産日,生産連番,事業部) values (?,?,?,?,?,?)',
                    [
                        $serial,
                        $this->nvl(self::issueLineSign, ''),
                        $this->nvl($targetLine->lineSign, ''),
                        date("Y/m/d"),
                        0,
                        self::department[$targetLine->departmentCode]
                    ]
                );

            //T_出庫依頼からD_部品出庫にデータ追加
            $list = DB::connection('sqlsrv_seisanhojyo')->table('T_出庫依頼')
                ->select(
                    'ID as id',
                    '品目CD as itemCode',
                    '品目名称 as itemName',
                    '依頼数 as requestCount',
                    '棚番 as shelfNumber',
                    '色種 as color',
                    'まるめ数 as roundingCount'
                )
                ->where('ライン', '=', $targetLine->lineSign)
                ->whereNull('更新日時')
                ->orderBy('棚番', 'asc')
                ->orderBy('棚枝番', 'asc')
                ->orderBy('品目CD', 'asc')
                ->get();

            //EXの場合はまるめ数ごとにD_部品出庫を作成 その他の場合は依頼数をそのまま登録する
            foreach ($list as $value) {
                if ($targetLine->departmentCode == 'EX') {
                    $shippingCount = $value->roundingCount;
                    $rows = $value->requestCount / $shippingCount;
                } else {
                    $shippingCount = $value->requestCount;
                    $rows = 1;
                }

                for ($count = 0; $count < $rows; $count++) {
                    DB::connection('sqlsrv_seisanhojyo')
                        ->insert(
                            'insert into D_部品出庫 (出庫番号,品目CD,品名,色種,棚番,取付位置,指示数量,部材番号,指示数量無効区分) values (?,?,?,?,?,?,?,?,?)',
                            [
                                $serial,
                                $this->nvl($value->itemCode, ''),
                                $this->nvl($value->itemName, ''),
                                $this->nvl($value->color, ''),
                                $this->nvl($value->shelfNumber, ''),
                                '-',
                                $this->nvl($shippingCount, ''),
                                0,
                                1
                            ]
                        );
                }
            }

            //T_出庫依頼の更新日時を更新
            DB::connection('sqlsrv_seisanhojyo')
                ->update("update T_出庫依頼 set 更新日時 = ? where ライン = ? and 更新日時 is Null", [date("Y/m/d H:i:s"), $targetLine->lineSign]);

            $this->setUnLock();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['errMessage' => $e->getMessage()];
        }

        return;
    }
}
