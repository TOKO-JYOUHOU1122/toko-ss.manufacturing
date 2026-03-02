<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\ProductCompletionReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionProgressController extends Controller
{
	public function GetProgressStatus(Request $request)
	{
		$lineSign = $request->lineSign;
		$nowDate = date("Y/m/d");
		$minProductDate =
			ProductCompletionReport::lineSign($lineSign)
			->NoCancel()
			->whereNull("完了日時1")
			->where(function ($query) use ($lineSign, $nowDate) {
				if ($lineSign == 'ET') {
					$query->ProductDate($nowDate);
				}
			})
			->min("生産日");

		if (empty($minProductDate)) {
			return [
				'productDate' => null,
				'dailyQuantity' => null,
				'precedenceOrder' => null,
				'scheduledQuantity' => null,
				'actualQuantity' => null,
				'finishTime' => null,
				'progress' => null
			];
		}
		$formattedProductDate = date('Y/m/d', strtotime($minProductDate));
		$isProductToday = ($formattedProductDate === $nowDate);         //true:当日生産分  false:前日生産分

		//計画生産数
		$dailyQuantity = ProductCompletionReport::ProductDate($formattedProductDate)
			->lineSign($lineSign)
			->NoCancel()
			->count();

		//先行組立順
		$precedenceOrder = $this->getPrecedenceOrder($formattedProductDate, $nowDate, $lineSign);
		Log::info(3);
		//予定完了日時
		$finishTime = ProductCompletionReport::ProductDate($formattedProductDate)
			->lineSign($lineSign)
			->NoCancel()
			->where(function ($query) use ($isProductToday, $nowDate, $precedenceOrder) {
				if ($isProductToday) {
					$query->where(DB::raw("CONVERT(NVARCHAR, 実生産日時, 111)"), "=", $nowDate);
				} else {
					$query->where("組立順1", "<=", $precedenceOrder);
				}
			})
			->max("生産完了時刻");

		$finishTime = date('H:i', strtotime($finishTime));

		//予定生産数
		$scheduledQuantity = ProductCompletionReport::ProductDate($formattedProductDate)
			->lineSign($lineSign)
			->NoCancel()
			->where("実生産日時", "<=", date("Y/m/d H:i:s"))
			->count();

		//実績生産数
		$actualQuantity = ProductCompletionReport::ProductDate($formattedProductDate)
			->lineSign($lineSign)
			->whereNotNull("完了日時1")
			->count();
		//進捗度
		$progress = $this->getProgress($formattedProductDate, $nowDate, $lineSign);
		return [
			'productDate' => date('m/d', strtotime($minProductDate)),
			'dailyQuantity' => $dailyQuantity,
			'precedenceOrder' => $precedenceOrder,
			'scheduledQuantity' => $scheduledQuantity,
			'actualQuantity' => $actualQuantity,
			'finishTime' => $finishTime,
			'progress' => $progress
		];
	}

	private function getPrecedenceOrder($productDate, $nowDate, $lineSign)
	{
		$isProductToday = ($productDate === $nowDate);

		//先行組立NO
		$precedenceNo = DB::table('T_生産完了管理')
			->select("先行組立NO")
			->where("項目", "=", "先行組立NO")
			->where("ライン記号", "=", $lineSign)
			->value("先行組立NO");

		if (empty($precedenceNo)) {
			$precedenceNo = ProductCompletionReport::ProductDate($productDate)
				->lineSign($lineSign)
				->NoCancel()
				->PreviousDate($isProductToday, $nowDate)
				->orderBy("組立順1", "desc")
				->value("生産連番");

			$precedenceOrder =  ProductCompletionReport::ProductDate($productDate)
				->lineSign($lineSign)
				->Serial($precedenceNo)
				->NoCancel()
				->PreviousDate($isProductToday, $nowDate)
				->max("組立順1");

			if (empty($precedenceOrder)) {
				$precedenceOrder = ProductCompletionReport::ProductDate($productDate)
					->lineSign($lineSign)
					->NoCancel()
					->PreviousDate($isProductToday, $nowDate)
					->max("組立順1");
			}
		} else {
			$precedenceOrder = $precedenceNo;
		}
		return $precedenceOrder;
	}

	private function getProgress($productDate, $nowDate, $lineSign)
	{
		$expected = ProductCompletionReport::ProductDate($productDate)
			->lineSign($lineSign)
			->NoCancel()
			->where("実生産日時", "<=", date("Y/m/d H:i:s"));


		$expectedQuantity = $expected->count("受注NO");
		$expectedOrder = $expectedQuantity == 0 ? 0 : $expected->max("組立順1");

		$finishQuantity = ProductCompletionReport::ProductDate($productDate)
			->lineSign($lineSign)
			->NoCancel()
			->whereNotNull("完了日時1")
			->count();

		$differenceQuantity = $expectedQuantity - $finishQuantity;
		if ($differenceQuantity == 0) {
			$delayTime = 0;
			$progress = 0;
		} else {
			$result = ProductCompletionReport::select(DB::raw('Sum(ISNULL(生産タクト, 0)) AS "delayTime"'))
				->ProductDate($productDate)
				->lineSign($lineSign)
				->NoCancel()
				->where(function ($query) use ($differenceQuantity, $expectedOrder) {
					if ($differenceQuantity > 0) {
						$query->where("組立順1", "<=", $expectedOrder)
							  ->whereNull("完了日時1");
					} else {
						$query->where("組立順1", ">", $expectedOrder)
							  ->whereNotNull("完了日時1");
					}
				})->get();
			$delayTime = $result[0]->delayTime;

			$progress = $differenceQuantity > 0 ? 0 - $delayTime : $delayTime;
		}

		return $progress;
	}
}
