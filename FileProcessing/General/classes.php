<?php

	class TransactionClass
	{
			var $transDate;
			var $transTime;
			var $transValue;
			var $EFTTransNo;
			var $flag;
			var $cardCode;
			var $PANKey;

			var $transactionNo = 0;
			var $tableNo = 0;
			var $Month = 0;
			var $productCount = 0;
			var $starValueCurrency = 0;
			var $bonusPoints = 0;
			var $bonusSeq = 0;
	}

	class UserDataClass
	{
		var $periodSpend;
		var $cardNo;
		var $accountNo;
		var $accountType;
		var $memberNo;
		var $totalSwipes;
		var $latestSwipe;
		var $firstSwipe;
		var $promoCode;
		var $periodAdd;
		var $fuelAdd;
		var $shopAdd;
		var $deductHit;
		var $PromoHitsLeft;
	}

	class ProductClass
	{
		var $code;
		var $volume;
		var $value = 0;
	}

	class DeptClass
	{
		var $code;
	}

	class SiteClass
	{
		var $siteCode;
		var $regionID;
		var $areaID;
	}

	class StatsClass
	{
		var $valueProcessed = 0;
		var $transactionsProcessed = 0;
		var $productsProcessed = 0;
		var $duplicates = 0;
		var $bad = 0;
		var $warnings = 0;
	}

	function ResetUser()
	{
		global $gUserData;
		$gUserData->cardNo = "";
		 $gUserData->accountNo = "";
		 $gUserData->accountType = "";
		 $gUserData->memberNo = "";
		 $gUserData->latestSwipe = false;
		 $gUserData->firstSwipe = false;
		 $gUserData->promoCode = "";
		 $gUserData->periodSpend = 0;
		 $gUserData->periodAdd = 0;
		 $gUserData->fuelAdd = 0;
		 $gUserData->shopAdd = 0;
		 $gUserData->deductHit = false;
		 $gUserData->PromoHitsLeft = 0;
	}



?>

