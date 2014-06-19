	<?php
//* MRM 27/06/2008 - if( $numrows ==0 ) changed to if( $numrows >0 ) since we may have more than one Site Code if one has a status of "Closing"
function CheckGoodSiteNumber( $siteNo )
{
	$sql = "Select * from sitedata where SiteCode = $siteNo";

	$results = DBQueryExitOnFailure( $sql );
	$numrows = mysql_num_rows($results);
	if( $numrows >0 )
	{
		return true;
	}
	else
	{	
		//* this section added for unknown codes from Sites that are undergoing change of ownership MRM 8/12/08 
		//
		$sql = "Select SiteNo as SiteCode, RegionCode, AreaCode from sites where SiteNo = '$siteNo'";
		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows >0 )
		{
			$row = mysql_fetch_assoc( $results );
			$gSiteData->siteCode = $row["SiteCode"];
			$gSiteData->regionID = $row["RegionCode"];
			$gSiteData->areaID = $row["AreaCode"];
			LogError( "** Site Code $siteNo found in sites but not in sitedata.") ;
			return true;
		}
		else       // end of additional bit
		{
			LogError( "********* Site Code $siteNo not found.") ;
			return false;
		}
	}
}

	function GetThisMonth()
	{
		global $ThisMonth;
		$sql = "select date_format( now(), '%Y%m' )";
		$ThisMonth = DBSingleStatQuery( $sql );
		return $ThisMonth;
	}


	function InsertTransaction( $Type, $fileNameOnly )
	{
 		global $gStatsData, $gTransactionData,  $gUserData, $gSiteData, $ProcessName;

		$tab = $gTransactionData->tableNo;

		if( $Type == "Compower" or $Type == "FIS" )
		{
		// Compower

 			$sql = "Insert into Transactions$tab ( CardNo, AccountNo, SiteCode, TransTime, TransValue, Flag, PanInd, PayMethod, InputFile, EFTTransNo, CreatedBy, CreationDate ) values ( '$gUserData->cardNo', $gUserData->accountNo, $gSiteData->siteCode, '$gTransactionData->transDate $gTransactionData->transTime', $gTransactionData->transValue/100, '$gTransactionData->flag', $gTransactionData->PANKey, $gTransactionData->cardCode, '$fileNameOnly', $gTransactionData->EFTTransNo, '$ProcessName', now() )";
		}
		else if( $Type == "UKFuels" )
		{
			$sql = "Insert into Transactions$tab ( CardNo, AccountNo, SiteCode, TransTime, PayMethod, InputFile, EFTTransNo, CreatedBy, CreationDate ) values ( $gUserData->cardNo, $gUserData->accountNo, $gSiteData->siteCode, '$gTransactionData->transDate $gTransactionData->transTime', 'U', '$fileNameOnly', $gTransactionData->EFTTransNo, '$ProcessName', now() )";
		}
		else if( $Type = "MTV" )
		{
			$sql = "Insert into Transactions$tab ( CardNo, AccountNo, SiteCode, TransTime, TransValue, InputFile, EFTTransNo, CreatedBy, CreationDate ) values ( '$gUserData->cardNo', $gUserData->accountNo, $gSiteData->siteCode, '$gTransactionData->transDate', $gTransactionData->transValue/100, '$fileNameOnly', $gTransactionData->EFTTransNo, '$ProcessName', now() )";
		}
		$results = DBQueryExitOnFailure( $sql );
		if( $results )
		{
			$gTransactionData->transactionNo = mysql_insert_id();
		}
		return $results;
	}


	function checkForDuplicate( $cardNumber, $siteCode, $type  )
	{
		global $fileToProcess, $gTransactionData, $gStatsData, $ThisMonth ;

		if( $gTransactionData->Month < 200410 or $gTransactionData->Month > $ThisMonth )
		{
			$gTransactionData->tableNo = "BadDates";
		}
		else
		{
			$gTransactionData->tableNo = $gTransactionData->Month;
		}

		$tab = $gTransactionData->tableNo;

		$ExtraConstraint = "";
		if( isset( $gTransactionData->transTime))
		{
			$ExtraConstraint .= " and TransTime = '$gTransactionData->transDate $gTransactionData->transTime'";
			$ExtraConstraint .= " and EFTTransNo = $gTransactionData->EFTTransNo";
		}
		else
		{
			$ExtraConstraint .= " and Date_Format( TransTime, '%Y-%m-%d') = '$gTransactionData->transDate'";
		}

		if( isset($gTransactionData->transValue) and $gTransactionData->transValue > 0 )
		{
			 $ExtraConstraint .= " and cast(cast( TransValue * 100 as char) as signed) = $gTransactionData->transValue"; 
		}
		$sql = "Select * from Transactions$tab where CardNo='$cardNumber' and SiteCode=$siteCode $ExtraConstraint";
		$results = mysql_query( $sql ) or die( mysql_error() . $sql);
		$numrows = mysql_num_rows($results);

		if( $numrows == 0 )
		{
			return true;
		}
		else
		{
			$gStatsData->duplicates++;
			//echo "Duplicate record \n";
			// compare the existing transaction and where it came from.
			$row = mysql_fetch_assoc( $results );
			$fileNameOnly = basename($fileToProcess);
			if( $row['InputFile'] == $fileNameOnly )
			{
				echo "File already processed!\n";
			}
			else if( $row['InputFile'] != 'null' )
			{
				LogWarning( "Transaction already presented in file $row[InputFile].\r\n" );
			}
			else
			{
				// We should compare the fields except there are hardly any that overlap that
				// haven't already been compared to make the link.

				$sql = "Update Transactions$tab set InputFile=$fileNameOnly where TransactionNo = $row[TransactionNo]";
				$results = mysql_query( $sql );
			}
			return false;
		}
	}

//***********************************************************************************
//
// The data we need has to come from
//
//	Accounts
//	Members
//  Cards
//	Personal Campaigns
//
//
//
//***********************************************************************************

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
		$sql = "Select Accounts.AccountType, Members.AccountNo, Cards.MemberNo, Cards.TotalSwipes, isnull(AwardStopDate) as CardStopped, '$TransTime' > Cards.LastSwipeDate or IsNull(LastSwipeDate) as LatestSwipe, IsNull(FirstSwipeDate) as FirstSwipe from Cards left join Members Using(MemberNo) left join Accounts Using(AccountNo) where Cards.CardNo = '$cardNumber'";

		ResetUser();
		$results = mysql_query( $sql ) or die( mysql_error());
		$numrows = mysql_num_rows($results);
		$gUserData->deductHit = false;
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
				$gUserData->accountType = $row["AccountType"];
				$gUserData->cardStopped = ( $row["CardStopped"] == 0 );
			}
			$gUserData->memberNo  = $row["MemberNo"];
			$gUserData->totalSwipes  = $row["TotalSwipes"];
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
			//next line added by MRM 02/09/08 for Triple Points Bonus
			$gUserData->totalSwipes = 0;
			if( $bAddCards )
			{
				if( luhnCheck( $cardNumber ) ) 
				{
					$sql = "insert into Cards (CardNo, CreatedBy, CreationDate ) values ( '$cardNumber', '$ProcessName', now() )";
					$results = mysql_query( $sql );
					if( !$results )
					{												   
						LogError( "Failed to insert.\r\n" .mysql_error());
					}
				}
				else
				{
					LogError( "$CardNo failed luhnCheck\r\n" );
				}
			}
			else
			{
				$gStatsData->warnings++;
				LogError( "AccountCard $cardNumber not in database.\r\n" );
				return false;
			}
		}
		else
		{
			LogError( "Database integrity is suspect '$sql' produced $numrows results\r\n");
			return false;
		}
		return true;
	}

//***********************************************************************************
//
// Need to update
//
// Cards (if the account is stopped or the card is unlinked)
// Personal Campaigns (if one is active)
// Accounts
//
//***********************************************************************************


	function UpdatePoints( $bTransValue )
	{
		global $gTransactionData, $gUserData, $gSiteData, $ThisMonth;

		// Calculate the total points
		CalculatePeriodBonus();
		CalculateTotalBonus();
		CalculateVisitBonus();

		$totalPoints = $gTransactionData->bonusPoints;

		// Update the transaction in the transaction log
		$updateLoc = "";

		if( $bTransValue )
		{
			$updateLoc .= ",TransValue=$gTransactionData->transValue/100 ";
		}

		$tab = $gTransactionData->tableNo;
		
		//Mantis 1970 MRM 30 APR 10 - if account is on stop, then transaction has no points value
		//Mantis 2296 MRM 13 JUL 10 - suppress these messages
		if( $gUserData->accountNo != "null" && $gUserData->cardStopped == true ) 
		{
//			echo "$gTransactionData->transactionNo yields zero points since account no $gUserData->accountNo is on stop.\r\n";	
		}
		else 
		{
			$sql = "Update Transactions$tab set PointsAwarded = $totalPoints $updateLoc  where TransactionNo = $gTransactionData->transactionNo";

			$results = DBQueryLogOnFailure( $sql );
		}

		$updateLoc = "";

		// Update the Cards details for swipes
		if( $gUserData->latestSwipe )
		{
			$updateLoc .= ", LastSwipeLoc = $gSiteData->siteCode, LastSwipeDate = '$gTransactionData->transDate $gTransactionData->transTime'";
		}

		if(  $gUserData->firstSwipe )
		{
			$updateLoc .= ",FirstSwipeDate='$gTransactionData->transDate $gTransactionData->transTime', FirstSwipeLoc = $gSiteData->siteCode";
		}

		if( $gUserData->cardStopped && $gUserData->accountNo == "null" )
		{
			$updateLoc .= ",StoppedPoints = StoppedPoints + $totalPoints";
		}

		if( $gUserData->fuelAdd )
		{
			$updateLoc .= ", FuelSpend = FuelSpend + $gUserData->fuelAdd/100";
		}

		if( $gUserData->shopAdd )
		{
			$updateLoc .= ", ShopSpend = ShopSpend + $gUserData->shopAdd/100";
		}

		$sql = "Update Cards set TotalSwipes = TotalSwipes + 1, TotalSpend = TotalSpend + $gTransactionData->transValue/100 $updateLoc where CardNo = '$gUserData->cardNo'";

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

				$results = mysql_query( $sql );
				if( !$results )
				{
					logError( "Failed to update Personal Campaigns \r\n$sql\r\n".mysql_error() );
				}
			}
		}

		if( !$gUserData->cardStopped )
		{
			// Update the associated account with the data.
			$sql = "Update Accounts set Balance = Balance + $totalPoints where AccountNo = $gUserData->accountNo";
			$results = DBQueryLogOnFailure( $sql );
			if ($gUserData->accountType  == 'G')
			{
				$sql = "Update Members set MemberBalance = MemberBalance + $totalPoints where MemberNo = $gUserData->memberNo";
				$results = DBQueryLogOnFailure( $sql );
			}
			// Mantis 1194
			
		}
	}

	function CheckSiteNumber( $siteNo, $field )
	//* MRM 30/06/2008 if( $numrows ==0 ) changed to if( $numrows >0 )
	{
		global $gSiteData, $gStatsData;
		$sql = "Select * from sitedata where $field = $siteNo";

		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows >0 )
		{
			$row = mysql_fetch_assoc( $results );
			$gSiteData->siteCode = $row["SiteCode"];
			$gSiteData->regionID = $row["RegionCode"];
			$gSiteData->areaID = $row["AreaCode"];
			return true;
		}
		else
		{	
			//* this section added for unknown codes from Sites that are undergoing change of ownership MRM 8/12/08 
			//* table changed from closed_sites to sites 
			//
			$sql = "Select SiteNo AS SiteCode, RegionCode, AreaCode from sites where SiteNo = '$siteNo'";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if( $numrows >0 )
			{
				$row = mysql_fetch_assoc( $results );
				$gSiteData->siteCode = $row["SiteCode"];
				$gSiteData->regionID = $row["RegionCode"];
				$gSiteData->areaID = $row["AreaCode"];
				return true;
			}
			else       // end of additional bit
			{
	
				$gSiteData->siteCode = $siteNo;
				$gSiteData->regionID = "-1";
				$gSiteData->areaID = "-1";
				if( $siteNo == 444444 )
				{
					return true;
				}
				else
				{
					$gStatsData->warnings++;
					LogError( "$field $siteNo not recognised\r\n" );
					return false;
				}
			}
		}
	}

	function ProductInsertPurchase( $gTransactionData, $gProductData )
	{
		global $gStatsData;
		global $ThisMonth;

		$tab = $gTransactionData->tableNo;

		$sql = "Insert into ProductsPurchased$tab( TransactionNo, Month, SequenceNo, DepartmentCode, ProductCode, PointsAwarded, Quantity, Value ) values ( $gTransactionData->transactionNo, $gTransactionData->Month, $gTransactionData->productCount, 0, $gProductData->code, 0, $gProductData->volume, $gProductData->value/100)";

		$results = DBQueryLogOnFailure( $sql );
		return true;
	}

	function TrackBonusHits( $gTransactionData, $PromoCode, $Points )
	{
   		$tab = $gTransactionData->tableNo;

		global $gStatsData;

		$sql = "Insert into BonusHit$tab ( TransactionNo, Month, SequenceNo, PromotionCode, Points ) values ( $gTransactionData->transactionNo, $gTransactionData->Month,  $gTransactionData->bonusSeq, '$PromoCode', $Points )";
		
		$results = DBQueryLogOnFailure( $sql );
		return true;

	}

	function DisplayStats( $StatsData )
	{
		echo "<br>valueProcessed = $StatsData->valueProcessed\n";
		echo "<br>transactionsProcessed = $StatsData->transactionsProcessed\n";
		echo "<br>productsProcessed = $StatsData->productsProcessed\n";
		echo "<br>duplicates = $StatsData->duplicates\n";
		echo "<br>bad = $StatsData->bad\n";
		echo "<br>warnings = $StatsData->warnings\n";
	}
?>