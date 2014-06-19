<?php


include "../../include/DB.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
//	$db_pass = "";

	$update = true;

	connectToDB();

	if( $update )
	{
		echo "Real Version\n";
	}
	else
	{
		echo "Test Version\n";
	}				
	$PromoCode = "DoublePts";
	$TotalBonus = 0;
	$AccountsBonused = 0;
	$SeriousErrors = 0;
	$sql = "select TransactionNo, TransValue, CardNo from Transactions200503 where TransTime > '2005-03-02' and SiteCode = 886640";

	$transactions = DBQueryExitOnFailure( $sql );

	while( $transaction = mysql_fetch_assoc( $transactions ) )
	{

		$TransId = $transaction["TransactionNo"];
		$CardNo =  $transaction["CardNo"];
		$transValue = $transaction["TransValue"];

		$sql = "select Accounts.AccountNo, AwardStopDate is null as AwardStop from Cards join Members using(MemberNo) left join Accounts using (AccountNo) where CardNo = '$CardNo'";
		$accounts = DBQueryExitOnFailure( $sql );
  		if( $account = mysql_fetch_assoc( $accounts ) )
		{
			$AccountNo = $account["AccountNo"];
			echo "AwardStop = ".$account["AwardStop"];
			$addToAccount = $account["AwardStop"];
		}
		else
		{
			$addToAccount = false;
		}
			
		$ThisBonus = IntVal( $transValue );
		$Month = "200503";

		// check this transaction doesn't have this bonus

		$sql = "Select * from BonusHit$Month where TransactionNo = $TransId and PromotionCode = '$PromoCode'";
		$bonusHits = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $bonusHits ) != 0 ) 
		{
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
		}
		if( $addToAccount )
		{
			$sql = "Update Accounts set Balance = Balance + $ThisBonus where AccountNo = $AccountNo";

			if( $update )
			{
				echo "A ";
				 DBQueryExitOnFailure( $sql );
			}
			else
			{
				
				echo "$sql\n";
			}
			$TotalBonus += $ThisBonus;
			$AccountsBonused++;
		}
		else
		{
			$sql = "Update Cards set StoppedPoints = StoppedPoints + $ThisBonus where CardNo = '$CardNo'";

			if( $update )
			{
				echo "C ";
				 DBQueryExitOnFailure( $sql );
			}
			else
			{
				
				echo "$sql\n";
			}
			$TotalBonus += $ThisBonus;
			$AccountsBonused++;
		}
	}  // next transaction


echo "$TotalBonus, $AccountsBonused, $SeriousErrors\n";

?>