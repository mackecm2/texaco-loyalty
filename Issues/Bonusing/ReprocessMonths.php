<?php

include "../../include/DB.inc";
include "../../FileProcessing/General/classes.php";
include "../../FileProcessing/General/Calculate.php";
include "../../FileProcessing/General/BonusFuncs.php";
include "../../DBInterface/ExposureInterface.php";

	global $gTransactionData, $gUserData, $gSiteData;

	global $LastMonth;
	global $FirstMonth;
	global $addBonusHit;
	global $master;



	$LastMonth = date( "Ym" );
	$FirstMonth = "200401";

	$db_user = "ReadOnly";
	$db_pass = "ORANGE";

	$slave = connectToDB( ReplicationServer, TexacoDB );


 	GetThisMonth();

	$sql = "select * from Transactions where CreationDate > '2005-04-01'";

	$transactions = mysql_query( $sql, $slave ) or die( mysql_error( $slave ) );

	$db_user = "root";
	$db_pass = "trave1";

	$master =  connectToDB( MasterServer, TexacoDB );

	CreateExposurePoint( "Before Bonus Correction" );


	$gTransactionData = new TransactionClass();
	$gUserDate = new UserDataClass();
	$gProductData = new ProductClass();
	$gDeptmentData = new DeptClass();
	$gSiteData = new SiteClass();
	$gStatsData = new StatsClass();
	$errorCount = 0;

	while( $transaction = mysql_fetch_assoc( $transactions ) )
	{
		ResetUser();
		$gTransactionData->transDate = substr( $transaction["TransTime"], 0 , 10);
		$gTransactionData->transTime = substr( $transaction["TransTime"], 11, 8 );
		$gTransactionData->transValue = $transaction["TransValue"] * 100;
		$gSiteData->siteCode = $transaction[ "SiteCode"]; 
		$gTransactionData->transactionNo = $transaction["TransactionNo"];

		$gUserDate->cardNo = $transaction["CardNo"];
		$gUserDate->accountNo = $transaction["AccountNo"];

		if( $gUserDate->accountNo == "" )
		{
			$gUserData->cardStopped = true;
			$gUserDate->memberNo = "";
		}
		else
		{
			checkCardNumber( $gUserDate->cardNo, false );
		}

		$gTransactionData->Month = substr( $gTransactionData->transDate , 0 , 4 ) . substr( $gTransactionData->transDate , 5, 2 );
		
		if( $gTransactionData->Month < 200410 or $gTransactionData->Month > $ThisMonth )
		{
			$gTransactionData->tableNo = "BadDates";
		}
		else
		{
			$gTransactionData->tableNo = $gTransactionData->Month;
		}

		$addBonusHit = false;
		$gTransactionData->productCount = 0;
		$gTransactionData->starValueCurrency  = $gTransactionData->transValue;
		$gTransactionData->bonusPoints = 0;
		$gTransactionData->bonusSeq = 0;
		CalculateTotalBonus();
		$ShouldHavePoints =	$gTransactionData->bonusPoints;

		// Because of the nature of limited hit personal bonuses 
		// we can get a lower result when re-calculating

		if( $ShouldHavePoints > $transaction["PointsAwarded"] )
		{
			echo "Oh dear $gUserDate->cardNo Should have $ShouldHavePoints, had $transaction[PointsAwarded] ($transaction[CreationDate]) \n";
			$addBonusHit = true;
			$gTransactionData->productCount = 0;
			$gTransactionData->starValueCurrency  = $gTransactionData->transValue;
			$gTransactionData->bonusPoints = 0;
			$gTransactionData->bonusSeq = 0;
			CalculateTotalBonus();
			UpdatePoints( $ShouldHavePoints - $transaction["PointsAwarded"]   );
		}
	}

	CreateExposurePoint( "After Bonus Correction" );

function TrackBonusHits( $gTransactionData, $PromoCode, $Points )
{
   		$tab = $gTransactionData->tableNo;

		global $gStatsData;
  		global $addBonusHit;
		global $master;

		if( $addBonusHit )
		{
			echo "$PromoCode, $Points"; 
//		$sql = "Insert into BonusHit$tab ( TransactionNo, Month, SequenceNo, PromotionCode, Points ) values ( $gTransactionData->transactionNo, $gTransactionData->Month,  $gTransactionData->bonusSeq, '$PromoCode', $Points )";

			$sql = "Select count(*) from BonusHit$tab where TransactionNo = $gTransactionData->transactionNo and PromotionCode = '$PromoCode' ";

			$results = DBSingleStatQuery( $sql );

			if( $results == 0 )
			{
				$tab = "BadDates";
				$sql = "Select count(*) from BonusHitBadDates where TransactionNo = $gTransactionData->transactionNo and Month = $gTransactionData->Month and PromotionCode = '$PromoCode'";

				$results = DBSingleStatQuery( $sql );
			}

			if( $results == 0 )
			{
				echo "Need to add bonus of $Points for $PromoCode, BonusHit$tab, $gTransactionData->transactionNo ";

		   		$tab = $gTransactionData->tableNo;
				$sql = "select count(*) from BonusHit$tab  where TransactionNo = $gTransactionData->transactionNo and Month = $gTransactionData->Month"; 

				$bonusSeq = DBSingleStatQuery( $sql ) + 1;
				$sql = "Insert into BonusHit$tab ( TransactionNo, Month, SequenceNo, PromotionCode, Points ) values ( $gTransactionData->transactionNo, $gTransactionData->Month,  -$bonusSeq, '$PromoCode', $Points )";

				echo "$sql\n";
				mysql_query( $sql, $master ) or die( mysql_error( $master ) );

			}
			else
			{
				echo "found in $tab\n";
			}
		}
		return true;
		
}


	function checkCardNumber( $cardNumber, $bAddCards )
	{
		global $gTransactionData, $gUserData, $gStatsData;
		global $ProcessName;

		if( isset( $gTransactionData->transTime ) )
		{
			$TransTime = "$gTransactionData->transDate $gTransactionData->transTime";
		}
		else
		{
			$TransTime = $gTransactionData->transDate;
		}
		$sql = "Select Accounts.AccountType, Members.AccountNo, Cards.MemberNo, isnull(AwardStopDate) as CardStopped, '$TransTime' > Cards.LastSwipeDate or IsNull(LastSwipeDate) as LatestSwipe, IsNull(FirstSwipeDate) as FirstSwipe from Cards left join Members Using(MemberNo) left join Accounts Using(AccountNo) where Cards.CardNo = '$cardNumber'";

		ResetUser();
		$results = mysql_query( $sql ) or die( mysql_error());
		$numrows = mysql_num_rows($results);
		$gUersData->deductHit = false;
		if( $numrows == 1 )
		{
			$row = mysql_fetch_assoc( $results );

			$gUserData->cardNo = $cardNumber;
			if( $row["AccountNo"] == "" )
			{
				$gUserData->accountNo = "null";
				$gUserData->cardStopped = true;
			}
			else
			{
				$gUserData->accountNo = $row["AccountNo"];
				$gUserData->cardStopped = ( $row["CardStopped"] == 0 );
			}
			$gUserData->memberNo  = $row["MemberNo"];
			$gUserData->latestSwipe = ( $row["LatestSwipe"] == 1 );
			$gUserData->firstSwipe = ( $row["FirstSwipe"] == 1 );

			if( isset($gUserData->memberNo) and $gUserData->memberNo != 0 )
			{
				$sql = "Select PersonalCampaigns.PeriodSpend, PersonalCampaigns.PromotionCode, PersonalCampaigns.PromoHitsLeft from PersonalCampaigns where MemberNo = $gUserData->memberNo and '$TransTime' between StartDate and EndDate";

				$results = mysql_query( $sql ) or die( mysql_error());
				$numrows = mysql_num_rows($results);
				if( $numrows == 1 )
				{
					$row = mysql_fetch_assoc( $results );
					$gUserData->promoCode = $row["PromotionCode"];
					$gUserData->periodSpend = $row["PeriodSpend"];
					$gUserData->PromoHitsLeft =  $row["PromoHitsLeft"];
				}
			}
		}
		else if( $numrows == 0 )
		{
			$gUserData->cardNo = $cardNumber;
			$gUserData->accountNo = "null";
			$gUserData->memberNo  = "";
			$gUserData->cardStopped = true;
			$gUserData->latestSwipe = true;
			$gUserData->firstSwipe = true;
		}
		else
		{
			LogError( "Database integrity is suspect '$sql' produced $numrows results\r\n");
			return false;
		}
		return true;
	}

	function GetThisMonth()
	{
		global $ThisMonth;
		$sql = "select date_format( now(), '%Y%m' )";
		$ThisMonth = DBSingleStatQuery( $sql );
		return $ThisMonth;
	}

	function UpdatePoints( $totalPoints )
	{
		global $gTransactionData, $gUserData, $gSiteData, $ThisMonth;
  		global $master;


		// Update the transaction in the transaction log

		$tab = $gTransactionData->tableNo;
		
		$sql = "Update Transactions$tab set PointsAwarded = $gTransactionData->bonusPoints where TransactionNo = $gTransactionData->transactionNo";

		$results = DBQueryLogOnFailure( $sql );

		$updateLoc = "";

		if( $gUserData->cardStopped )
		{
			$sql = "Update Cards set StoppedPoints = StoppedPoints + $totalPoints where CardNo = '$gUserData->cardNo'";
			echo "$sql\n";
			mysql_query( $sql, $master ) or die( mysql_error( $master ) );
		}
		else
		{
			$sql = "Update Accounts set Balance = Balance + $totalPoints where AccountNo = $gUserData->accountNo";
			echo "$sql\n";

			mysql_query( $sql, $master ) or die( mysql_error( $master ) );

		}

		$results = DBQueryLogOnFailure( $sql );

		if( $gUserData->promoCode != "" )
		{
			$updateLoc = "";
			$c = "";
			if( $gUserData->periodAdd )
			{
				$updateLoc = "PeriodSpend = PeriodSpend + $gUserData->periodAdd/100";
				$c = ",";
			}

			if( $gUserData->deductHit )
			{
				$updateLoc .= "$c PromoHitsLeft = PromoHitsLeft - 1";
				$c = ",";
			}

			if( $updateLoc != "" )
			{
				$sql = "Update PersonalCampaigns set $updateLoc where MemberNo = $gUserData->memberNo and PromotionCode = '$gUserData->promoCode'";
				echo "$sql\n";
				mysql_query( $sql, $master ) or die( mysql_error( $master ) );
			}
		}
	}

?>