<?php

include "../../include/DB.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
//	$db_pass = "";

	$update = true;

	connectToDB();


	echo "Test Version\n";
					
$PromoCode = "Q8Welcome";
$TotalBonus = 0;
$AccountsBonused = 0;
$SeriousErrors = 0;
$sql = "Select AccountNo, PersonalCampaigns.MemberNo, StartDate, EndDate, PersonalCampaigns.PromoHitsLeft from PersonalCampaigns join Members using(MemberNo) where PromotionCode = '$PromoCode'";

$personalCampaigns = DBQueryExitOnFailure( $sql );

while( $personalCampaign = mysql_fetch_assoc( $personalCampaigns ) )
{
//	print_r( $personalCampaign );
	$AccountNo = $personalCampaign["AccountNo"];
	$MemberNo =	$personalCampaign["MemberNo"];								
	$StartDate = $personalCampaign["StartDate"];
	$EndDate = $personalCampaign["EndDate"];
	$HitLimit =	$personalCampaign["PromoHitsLeft"];
	$OHitLimit = $HitLimit;
	$AddToAccount = 0;

	echo "Processing Member $MemberNo($HitLimit)";

	$SMonth = substr( $StartDate, 0, 4 ) . substr( $StartDate, 5, 2 );
	$EMonth = substr( $EndDate, 0, 4 ) . substr( $EndDate, 5, 2 );

	$numHits = 0;
	$Month = $SMonth;
//	echo "$StartDate, $EndDate, $SMonth, $EMonth\n";

	// double check if they have already been bonused.
																	  
	while( $Month <= $EMonth and $Month < 200505 )
	{
	//	echo "$Month\n";
		$sql = "select * from Cards join Transactions$Month using(CardNo) Join BonusHit$Month using(TransactionNo) where MemberNo = $MemberNo and PromotionCode = '$PromoCode'";
	//	echo "$sql\n";

		$existingHits = DBQueryExitOnFailure( $sql );
		$numHits += mysql_num_rows( $existingHits );

		if( $Month % 100 == 12 )
		{
			$Month += 100 - 11;
		}
		else
		{
			$Month++;
		}
	}

	echo "(B $numHits)";

	if( $numHits > 4 )
	{
		$sql = "Insert into OverBonused values( '$PromoCode', $MemberNo, $numHits )";
		DBQueryExitOnFailure( $sql );
		$HitLimit = 0;
		echo "OverBonused";
	}
	else if( $numHits + $HitLimit > 4 )
	{
		echo "Under Recorded";
		$HitLimit = 4 - $numHits;
	}

	$Month = $SMonth;
	while( $HitLimit > 0 and $Month <= $EMonth and $Month < 200505  )
	{
		$sql = "Select t.TransactionNo, t.TransValue, t.Month from Cards  join Transactions$Month as t using( CardNo )  left Join BonusHit$Month as b on( t.TransactionNo = b.TransactionNo and b.PromotionCode = '$PromoCode' ) where MemberNo = $MemberNo and TransTime between '$StartDate' and '$EndDate' and TransValue > 25 and b.TransactionNo is null order by TransTime limit $HitLimit";

		$toBonus = DBQueryExitOnFailure( $sql );
	
   		echo "($Month=". mysql_num_rows( $toBonus) .")";
		while( $transaction = mysql_fetch_assoc( $toBonus ) )
		{
			$transValue = $transaction["TransValue"];
			$TransId = $transaction["TransactionNo"];
			$ThisBonus = IntVal( $transValue );
			$Month = $transaction["Month"];

			// check this transaction doesn't have this bonus

			$sql = "Select * from BonusHit$Month where TransactionNo = $TransId and PromotionCode = '$PromoCode'";
			$bonusHits = DBQueryExitOnFailure( $sql );

			if( mysql_num_rows( $bonusHits ) != 0 ) 
			{
				print_r( $personalCampaign );
				print_r( $transaction );
				echo "\nThis transaction already has this bonus we shold never get here ever ever ever\n";
				$SeriousErrors++;
				//exit();
			}
			else
			{
				// get the sequence number
				$sql = "Select Max( SequenceNo) from BonusHit$Month where TransactionNo = $TransId";
				$seqNum = DBSingleStatQuery( $sql );

				$seqNum++; 

				echo "(Sequence=$seqNum, $ThisBonus)";
				$sql = "insert into BonusHit$Month values( $Month, $TransId, $seqNum, '$PromoCode', $ThisBonus  )";

				if( $update )
				{
					echo "BH ";
					 DBQueryExitOnFailure( $sql );
				}
				else
				{
					echo "$sql\n";
				}

				$sql = "Update Transactions$Month set PointsAwarded = PointsAwarded + $ThisBonus where TransactionNo = $TransId";

				if( $update )
				{
					echo "T ";
					DBQueryExitOnFailure( $sql );
				}
				else
				{
					echo "$sql\n";
				}
				$AddToAccount += $ThisBonus;
				$HitLimit--;
			}
		}  // next transaction

		
		if( $Month % 100 == 12 )
		{
			$Month += 100 - 11;
		}
		else
		{
			$Month++;
		}

	} // next month

	if( $AddToAccount > 0  )
	{
		$sql = "Update Accounts set Balance = Balance + $AddToAccount where AccountNo = $AccountNo";

		if( $update )
		{
			echo "A ";
			 DBQueryExitOnFailure( $sql );
		}
		else
		{
			
			echo "$sql\n";
		}
		$TotalBonus += $AddToAccount;
		$AccountsBonused++;
	}
	echo "Add=$AddToAccount,$HitLimit\n"; 
	if( $OHitLimit != $HitLimit )
	{
		$sql = "Update PersonalCampaigns set PromoHitsLeft = $HitLimit where MemberNo = $MemberNo and PromotionCode = '$PromoCode'";
		if( $update )
		{
			echo "PC ";
			DBQueryExitOnFailure( $sql );
		}
		else
		{
			echo "$sql\n";
		}
	}
} // next member

echo "$TotalBonus, $AccountsBonused, $SeriousErrors\n";

?>