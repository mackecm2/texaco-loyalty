	<?php
	//**********************************************************************************************
	//*                                                                                            *
	//*           Program Name     :  ProduceFile.php                                              *
    //*           Path             :  /data/www/websites/texaco/FileProcessing/Compower            *
    //*           Author / Date    :  Unknown / Unknown                                            *   
    //*           Function         :  creates the daily HomeSite File                              * 
    //*           Revision History :                                                               *
    //*                                                                                            *
    //*           MRM 11/03/08 - "if exists" added to line 132 (Mantis 381)                        *
    //*           MRM 31/03/08 - line 137: 	                                                       *	
    //* $sql = "....,SiteCode,AccountNo, max(CreationDate) changed to                              *
    //* $sql = "....,SiteCode,max(AccountNo) as AccountNo, max(CreationDate)                       *
    //*           lines 79 - 89 commented out......... Mantis 387                                  *
    //*                                                                                            *
    //*           MRM 28/04/09 - Mantis 856 Points Update Suppression on Unregistered Accounts     *
	//*  OLD HOME SITE DELETION                                                                    *
	//*           MRM 14 AUG 2009 -                                                                *
	//*  send out type 02 delete records for any sites where the card hasn't swiped for 6months    *
	//*           MRM 10/11/09 - Using HomeSiteDeletes table for type 02 records                   *                                                                *
    //*                                                                                            *
    //*                                                                                            *
    //*                                                                                            *
    //**********************************************************************************************
	require "../../include/DB.inc";
	require "../../include/Locations.php";
	#$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "HomeExport";
	$db_pass = "FLOWER";
	
	$ProcessName   = "HomeSites";

	echo date("Y-m-d H:i:s").' '.__FILE__." Version 2.0 started \r\n";
	

	function MainFunction( $delta )
	{

		connectToDB( MasterServer, TexacoDB );

		$filename = "DLXCLMSTARGCC.DAT";
		
		// Date for top of file
		$sql = "Select Date_Format( now(), '%Y%m%d%H%i' ) as p, now() as RunAt";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$tim = $row[0];

		$rdate = substr( $tim, 0, 8 );
		$runAt = $row[1];


		// Get Last run data
		
		$sql = "Select max(RunAt) from OutputFiles where Type = 'HOMESITE' group by Type ";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$lastDate = $row[0];
		
		if( $delta and $lastDate != "" )
		{
			echo "Deltas only\r\n";
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
		
		$Wiz = fopen( LocationHomeSitesDirectory.$filename , "w" );
		fputs( $Wiz, "00".$FileHeader."MILLSTAR".$tim );
		fputs( $Wiz, str_pad( " ", 57 )."\r\n");

		$count = 2;

		$srch = array( "\r", "\n", "/'" );
		$rep  = array( " ", " ", "'" );
		
		#
		
		//  Now we need to take care of the new extract type
		$sql = "SELECT DATE_ADD(now(), INTERVAL -6 MONTH)";
		
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$StartDate = $row[0];

		echo "New format - starting from $StartDate \r\n";

		// First create the working table	
		// code change MRM 11/03/08 - "if exists" added
		
		$sql = "drop table if exists HomeSiteFileData";
		echo "Creating HomeSiteFileData Table \r\n";
		
		$results = DBQueryExitOnFailure( $sql );		
	
		$sql = "create table HomeSiteFileData SELECT distinct(CardNo),SiteCode,max(AccountNo) as AccountNo, max(CreationDate)
		FROM Transactions join sitedata using (SiteCode) 
		WHERE   Transactions.TransTime > '$StartDate' 
		AND sitedata.HomeSiteFormat = 'NEW' 
		AND (AccountNo IS NOT NULL OR SiteCode NOT IN (SELECT SiteCode FROM HomeFileExemptions))
		group by CONCAT(CardNo,SiteCode)";
		
		// code change MRM 28/04/09 - "OR SiteCode NOT IN (SELECT SiteCode FROM HomeFileExemptions))" added
				
		$results = DBQueryExitOnFailure( $sql );
		
		echo "HomeSiteFileData Created \r\n";
		
		$sql = "select CardNo,SiteCode,AccountNo 
			FROM HomeSiteFileData";
		
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
				#echo "$sql \r\n";
				
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
				#echo "$sql \r\n";
					
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
				fputs( $Wiz, str_pad( $row["SiteCode"], 6, "0", STR_PAD_LEFT));
				fputs( $Wiz, "A" );
				fputs( $Wiz, substr( str_pad( str_replace( $srch, $rep ,$Title. " " . $Surname), 20 ), 0, 20 ));
				fputs( $Wiz, str_pad( $Balance, 6, "0", STR_PAD_LEFT )); 
				fputs( $Wiz, $rdate );
				fputs( $Wiz, str_pad( "N00", 18 ));
				fputs( $Wiz, "\r\n" );	
				
				$count++;

				if( $count % 10000 == 0 )
				{
					echo date("H:i:s")." $count type 01 lines output\n";
				}
			}
			
			unset($CardData);
			unset($AccData);
			
		}
	
		
		echo date("H:i:s")." $count type 01 lines output\n";
		
		//  OLD HOME SITE DELETION
		//  MRM 14 AUG 2009 - send out type 02 delete records for any sites where the card hasn't swiped for 6months

		
		$type2count = 0;
		
		$sql = "DROP TABLE IF EXISTS MostRecentTransactions";
		$results = DBQueryExitOnFailure( $sql );
		$sql = "CREATE TABLE MostRecentTransactions SELECT CardNo, SiteCode, MAX(TransTime) AS TransTime FROM Transactions GROUP BY CONCAT(CardNo, SiteCode)";
		$results = DBQueryExitOnFailure( $sql );
		$sql = "ALTER TABLE MostRecentTransactions ADD PRIMARY KEY (CardNo, SiteCode)";
		$results = DBQueryExitOnFailure( $sql );
		$sql = "SELECT M.CardNo, M.SiteCode, DeleteSent
					FROM MostRecentTransactions AS M
					LEFT JOIN HomeSiteDeletes AS H
					ON M.CardNo = H.CardNo
					AND M.SiteCode = H.SiteCode
					WHERE TransTime > '2007-07-07' AND TransTime < DATE_ADD( NOW( ) , INTERVAL -6
					MONTH ) AND 
					(DeleteSent = 'N'
					OR DeleteSent IS NULL ) 
					AND M.SiteCode NOT IN (SELECT SiteCode FROM HomeFileExemptions) 
					LIMIT 99999";
		$results = DBQueryExitOnFailure( $sql );
		while( $row = mysql_fetch_assoc( $results ) )
		{
				$type2count++;
				fputs( $Wiz, "02" );
				fputs( $Wiz, $row["CardNo"] );
				fputs( $Wiz, str_pad( $row["SiteCode"], 6, "0", STR_PAD_LEFT));
				fputs( $Wiz, str_repeat(" ", 53));
				fputs( $Wiz, "\r\n" );	
				$sql2 = "INSERT INTO HomeSiteDeletes (CardNo, SiteCode, DeleteSent) VALUES ($row[CardNo],$row[SiteCode],'Y')
					  ON DUPLICATE KEY UPDATE DeleteSent='Y'";
				$results2 = DBQueryExitOnFailure( $sql2 );
				if( $type2count % 10000 == 0 )
			{
				echo date("H:i:s")." $type2count type 02 lines output\n";
			}
		}
		
		
		$count = $count + $type2count;
		echo date("H:i:s")." $type2count type 02 lines output\n";
		fputs( $Wiz, "99".$FileHeader."MILLSTAR".$tim );

		fputs( $Wiz, str_pad( $count, 11, "0", STR_PAD_LEFT ));
		fputs( $Wiz, str_pad( " ", 18 ) . "\r\n");  

		$count -= 2;
		$sql = "Insert into OutputFiles ( Type, RunAt, Filename, NoRecords ) values ( 'HOMESITE', '$runAt', '$filename', $count )";
		
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
			echo "Delta file\n";
			MainFunction( false );			
		}
		else
		{
			echo "Full file\n";
			MainFunction( true );
		}
	}


		#	Write the end date/time into the log file
			//* next line exchanged for the one below it for greater clarity in logs - MRM 06/05/2008
	//* echo  date("Y-m-d H:i:s").' '.$_SERVER['PHP_SELF']." completed \r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
	
	
?>	