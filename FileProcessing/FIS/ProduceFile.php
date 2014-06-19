	<?php
	//**********************************************************************************************
	//*                                                                                            *
	//*           Program Name     :  ProduceFile.php                                              *
    //*           Path             :  /data/www/websites/texaco/FileProcessing/FIS                 *
    //*           Author / Date    :  Mike M    based on Compower/ProduceFile.php                  *   
    //*           Function         :  creates the daily HomeSite File                              * 
    //*           Revision History :                                                               *
    //*                                                                                            *
    //*                                                                                            *
    //*                                                                                            *
    //*                                                                                            *
    //**********************************************************************************************
	require "../../include/DB.inc";
	require "../../include/Locations.php";

	$db_name = "texaco";
	$db_user = "FISExport";
	$db_pass = "FLOWER";
	
	$ProcessName   = "HomeSites";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	

	function MainFunction( $delta )
	{

		connectToDB( MasterServer, TexacoDB );

		$yyyymmdd = date("Ymd");
		$filename = "STARREWARDS_BALS_".$yyyymmdd;
		
		// Date for top of file
		$sql = "Select Date_Format( now(), '%Y%m%d%H%i' ) as p, now() as RunAt";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$tim = $row[0];

		$rdate = substr( $tim, 0, 8 );
		$runAt = $row[1];


		// Get Last run data
		
		$sql = "Select max(RunAt) from OutputFiles where Type = 'FISHOMESITE' group by Type ";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$lastDate = $row[0];
		
		if( $delta and $lastDate != "" )
		{
			echo "Deltas only - last run date is $lastDate \r\n";
			$FileHeader = 'U';
			$partial = " and Accounts.RevisedDate > '$lastDate'";
			$partialunregistered = " and Cards.LastSwipeDate > '$lastDate'";
		}
		else
		{
			echo "Full Data files\r\n";
			$FileHeader = 'R';
			$partial = "";
			$partialunregistered = "";
		}

		$Wiz = fopen( LocationFISBalanceFile.$filename , "w" );
		fputs( $Wiz, "00".$FileHeader."MILLSTAR".$tim );
		fputs( $Wiz, str_pad( " ", 57 )."\r\n");

		$count = 2;
		$type1count = 0;

		$srch = array( "\r", "\n", "/'" );
		$rep  = array( " ", " ", "'" );
		
		#
		
		//  Now we need to take care of the new extract type
		$sql = "SELECT DATE_FORMAT(DATE_ADD(now(), INTERVAL -6 MONTH), '%Y-%m-%d')";
		
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$StartDate = $row[0];

		echo "New format - starting from $StartDate \r\n";

		// First create the working table	
		// code change MRM 11/03/08 - "if exists" added
		
		$sql = "drop table if exists FISHomeSiteFileData";
		echo "Creating FISHomeSiteFileData Table \r\n";
		
		$results = DBQueryExitOnFailure( $sql );		
		
		$sql = "create table FISHomeSiteFileData SELECT distinct(CardNo),max(AccountNo) as AccountNo, max(CreationDate)
		FROM Transactions WHERE Transactions.TransTime > '$StartDate' 
		group by CardNo";

		$results = DBQueryExitOnFailure( $sql );
		
		echo "FISHomeSiteFileData Created \r\n";
		
		$sql = "select CardNo,AccountNo 
			FROM FISHomeSiteFileData";
		
		$results = DBQueryExitOnFailure( $sql );
		while( $row = mysql_fetch_assoc( $results ) )
		{	

			$Title = "";
			$Surname = "";
			$Balance = "";	
			$outputline = "";
			
			if($row['AccountNo'] <> '')
			{
			
				//	This card is registered so grab the balance etc
				//	Only output if Accounts.RevisedDate >= $lastDate
				
				$sql = "select Title, Forename, Surname, Balance from Cards join Members using (MemberNo) join Accounts using (AccountNo) where Cards.CardNo = '$row[CardNo]' $partial";

				$AccQueryResult = DBQueryExitOnFailure( $sql );
				$AccData = mysql_fetch_assoc( $AccQueryResult );
			
				if($AccData['Balance'] > 0)
				{
					$outputline = "YES";
					$Title = $AccData['Title'];
					$Surname = $AccData['Surname'];
					$Balance = $AccData['Balance'];
				}
			}
			else
			{
				// this card is not registered so the balance comes from the card record

				$sql = "select StoppedPoints as Balance from Cards where CardNo = '$row[CardNo]' $partialunregistered";
					
				$CardQueryResult = DBQueryExitOnFailure( $sql );
				$CardData = mysql_fetch_assoc( $CardQueryResult );
				
				if($CardData['Balance'] > 0)
				{
					$outputline = "YES";
					$Title = "";
					$Surname = "Unregistered";
					$Balance = $CardData['Balance'];			
				}
			}
			
			
			//  Now write the data out
			
			if($outputline == "YES")
			{
				fputs( $Wiz, "01" );
				fputs( $Wiz, $row["CardNo"] );
//				fputs( $Wiz, str_pad( $row["SiteCode"], 6, "0", STR_PAD_LEFT));
				fputs( $Wiz, "A" );
				fputs( $Wiz, substr( str_pad( str_replace( $srch, $rep ,$Title. " " . $Surname), 20 ), 0, 20 ));
				fputs( $Wiz, str_pad( $Balance, 6, "0", STR_PAD_LEFT )); 
				fputs( $Wiz, $rdate );
				fputs( $Wiz, str_pad( "N00", 24 ));
				fputs( $Wiz, "\r\n" );	
				
				$type1count++;

				if( $type1count % 10000 == 0 )
				{
					echo date("H:i:s")." $type1count type 01 lines output\n";
				}
			}
			
			unset($CardData);
			unset($AccData);
			
		}
	
		
		echo date("H:i:s")." $type1count type 01 lines output\n";
		$count = $count + $type1count;
		//  OLD HOME SITE DELETION
		//  MRM 14 AUG 2009 - send out type 02 delete records for any sites where the card hasn't swiped for 6months

		
		$type2count = 0;
		
		if ($delta)   // MRM 23 DEC 2010 Only write type 02 records if it's a delta file
		{
			$sql = "DROP TABLE IF EXISTS MostRecentFISTransactions";
			$results = DBQueryExitOnFailure( $sql );
			$sql = "CREATE TABLE MostRecentFISTransactions SELECT CardNo, MAX(TransTime) AS TransTime FROM Transactions GROUP BY CardNo";
			$results = DBQueryExitOnFailure( $sql );
			$sql = "ALTER TABLE MostRecentFISTransactions ADD PRIMARY KEY ( CardNo )";
			$results = DBQueryExitOnFailure( $sql );
			$sql = "SELECT M.CardNo, DeleteSent
						FROM MostRecentFISTransactions AS M
						LEFT JOIN FISHomeSiteDeletes AS H
						ON M.CardNo = H.CardNo
						WHERE TransTime > '2007-07-07' AND TransTime < '$StartDate' AND 
						(DeleteSent = 'N'
						OR DeleteSent IS NULL ) 
						LIMIT 99999";
			$results = DBQueryExitOnFailure( $sql );
			while( $row = mysql_fetch_assoc( $results ) )
			{
					$type2count++;
					fputs( $Wiz, "02" );
					fputs( $Wiz, $row["CardNo"] );
					fputs( $Wiz, str_repeat(" ", 59));
					fputs( $Wiz, "\r\n" );	
					$sql2 = "INSERT INTO FISHomeSiteDeletes (CardNo, DeleteSent) VALUES ($row[CardNo],'Y')
						  ON DUPLICATE KEY UPDATE DeleteSent='Y'";
					$results2 = DBQueryExitOnFailure( $sql2 );
					if( $type2count % 10000 == 0 )
				{
					echo date("H:i:s")." $type2count type 02 lines output\n";
				}
			}
		}
		
		$count = $count + $type2count;
		echo date("H:i:s")." $type2count type 02 lines output\n";
		fputs( $Wiz, "99".$FileHeader."MILLSTAR".$tim );

		fputs( $Wiz, str_pad( $count, 11, "0", STR_PAD_LEFT ));
		fputs( $Wiz, str_pad( " ", 46 ) . "\r\n");  

		$count -= 2;
		$sql = "Insert into OutputFiles ( Type, RunAt, Filename, NoRecords ) values ( 'FISHOMESITE', '$runAt', '$filename', $count )";
		
		$results = DBQueryExitOnFailure( $sql );
	}


	if ($argc <1 || $argc > 2 || ($argc == 2 && $argv[1] != '-f') ) 
	{
		echo $argc ;
?>

		This is a command line PHP script with one option.

		  Usage:
			 <?php echo $argv[0]; ?> <-d>

		 <-f> full refresh.
<?php
	} 
	else 
	{
		if( $argc == 2 )
		{
			echo "Full file\n";
			MainFunction( false );			
		}
		else
		{
			echo "Delta file\n";
			MainFunction( true );
		}
	}
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>	