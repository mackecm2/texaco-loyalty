	<?php

	//	This script runs on the slave machine only - DRWEOUWeb

	require "./DRDB.inc";
	require "./functions.php";

	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "Trave1";

	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 03/07/2008
	//*  	echo date("Y-m-d H:i:s").' '.$_SERVER['PHP_SELF']." Version 1.6 started \r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";


####   S T A R T   O F   F U N C T I O N    ####

	function MainFunction( $delta )
	{

		$ProcessName   = "OccamExport";
		global $SERVER_NAME_FOR_ALL;

		connectToDB( ReplicationServer, TexacoDB );

		// Setup Dates
		$sql = "Select Date_Format( now(), '%Y%m%d%H%i' ) as p, now() as RunAt";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$tim = $row[0];

		$rdate = substr( $tim, 0, 8 );
		$runAt = $row[1];

		// Get Last run data

		$sql = "Select max(RunAt), DATEDIFF( '$runAt',max(RunAt) ) from OutputFiles where Type = 'OccamExport' group by Type ";
		$results = DBQueryExitOnFailure( $sql );
		$row = mysql_fetch_row( $results );
		$lastDate = $row[0];
		$dayssince = $row[1];

		echo "Last run time $lastDate \r\n";
		echo "Days since last extract run $dayssince \r\n";

		if( $dayssince < 12 ) // Mantis 1796 - Extra Validation
		{
			echo "Days since last extract run is less than 12.\r\n";
			if ( $delta === "Override")
			{
				echo "Override specified.\r\n";
			}
			else
			{
				echo "Processing terminated.\r\n";
				die();
			}

		}

		$output = '';
		if( $SERVER_NAME_FOR_ALL == "TEST" )
		{
			$filepath = "/data/Occam/";
		}
		else
		{
			$filepath = "/data/mysql2/Occam/";
			$sql = "stop slave";
			$results = DBQueryExitOnFailure( $sql );
			echo "$SERVER_NAME_FOR_ALL Checking replication\r\n";

			CheckReplicationStopped();
		}

		/*
					// this only needed to be exported once.
					array('tablename' => 'newpostcodedata',	'type' => 'full', 	'partialfield' => ''),
		*/

		$tablearray = array(  	array('tablename' => 'AccountCards',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'AccountMonthly',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'AccountRedemptions',	'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'AccountTypes',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'Accounts',		'type' => 'inc', 	'partialfield' => 'RevisedDate', 'fields' => '*'),
					array('tablename' => 'AccountStatus',		'type' => 'inc', 	'partialfield' => 'RevisedDate', 'fields' => '*'),
					array('tablename' => 'Answers',			'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'BonusCriteria',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'BonusPoints',		'type' => 'inc', 	'partialfield' => 'RevisionDate', 'fields' => '*'),
					array('tablename' => 'CampaignHistory',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'Cards',			'type' => 'inc', 	'partialfield' => 'LastUpdate', 'fields' => 'CardNo,MemberNo,
					CardType,LastSwipeLoc,LastSwipeDate,FirstSwipeLoc,FirstSwipeDate,TotalSwipes,TotalSpend,FuelSpend,ShopSpend,IssueDate,LostDate,
					StoppedPoints,CreationDate,CreatedBy,SegmentCode'),
					array('tablename' => 'CardMonthly',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'CardRequests',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'HomeSiteChanges',		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'LiabilityReduction',	'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'MonthlySpends', 		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'OrderProducts', 		'type' => 'inc', 	'partialfield' => 'RevisedDate', 'fields' => '*'),
					array('tablename' => 'Orders', 			'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'ProductTypes', 		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'QuestionOptions',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'Questions',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'RedemptionMerchants',	'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'Statement', 		'type' => 'inc', 	'partialfield' => 'StateDate', 'fields' => '*'),
					array('tablename' => 'SupplierCodes',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'Tracking', 		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*'),
					array('tablename' => 'TrackingCodes',		'type' => 'full', 	'partialfield' => '', 		'fields' => '*'),
					array('tablename' => 'Transactions', 		'type' => 'inc', 	'partialfield' => 'CreationDate', 'fields' => '*')
				 );

		// SDT 20/10/2008  The extract has been separated into two extracts because Occam need some of the data in an alternative format.
		// MRM 23/08/2010  Mantis 2515 Blank our Passwrd field in Members table

		$tablearray2 = array(  	array('tablename' => 'Members',			'type' => 'inc', 	'partialfield' => 'RevisedDate', 'fields' => 'MemberNo,
					AccountNo,PrimaryMember,PrimaryCard,Title,Initials,Forename,Surname,Honours,Salutation,GenderCode,DOB,HomePhone,HomeVerified,WorkPhone,WorkVerified,
					Fax,Email,EmailVerified,Company,Address1,Address2,Address3,Address4,Address5,PostCode,ShortPostCode,ScottishPostCode,
					NonUKAddress,NonResidential,AddressVerified,CntryCode,\'\' AS Passwrd,PassPrompt,StatementPref,CanRedeem,OKMail,TOKMail,OKEmail,
					OKSMS,OKHomePhone,OKWorkPhone,GoneAway,Deceased,MemberData,LastLogin,SourceSite,MemberBalance,PromoCode,CreationDate,
					CreatedBy,RevisedDate,RevisedBy,PromoHitsLeft,Source,Organisation,MemberType,StaffID,ExperianHomeSiteAlloc,HomeSiteTravelTime'),
					array('tablename' => 'sitedata',		'type' => 'full', 	'partialfield' => '', 'fields' => '*'),
				  	array('tablename' => 'sites',			'type' => 'full', 	'partialfield' => '', 'fields' => '*'),
				  	// new lines added by MRM 25/09/08
				  	array('tablename' => 'CustomerRegistrations',	'type' => 'full', 	'partialfield' => '', 'fields' => '*'),
				  	array('tablename' => 'StaffMembers',		'type' => 'full', 	'partialfield' => '', 'fields' => '*'),
				  	array('tablename' => 'SiteRegistrations',	'type' => 'full', 	'partialfield' => '', 'fields' => '*')
				   );


		$thismonth =  GetNewMonth();

		for( $c = 0; $c < 6; $c++ )
		{

			array_push($tablearray,array('tablename' => 'BonusHit'.$thismonth,	'type' => 'full', 	'partialfield' => '', 'fields' => '*') );
			array_push($tablearray,array('tablename' => 'ProductsPurchased'.$thismonth,	'type' => 'full', 	'partialfield' => '', 'fields' => '*') );

			$thismonth =  DecrementMonth( $thismonth );

			array_push($tablearray,array('tablename' => 'Reporting.RawKPIData'.$thismonth,	'type' => 'full', 	'partialfield' => '', 'fields' => 'AccountNo,
			CardNo,Recency,Frequency,Value,PointsEarned,TotalSwipes,ActiveSwipes,CurrentMonthSwipes,TotalSpend,ShopSpend,FuelSpend,ActiveSpend,CurrentMonthSpend,
			Relationship,Balance,MembersRedeemed,RedeemedFuel,RedeemedVoucher,Registered,OKMail,OKEmail,OKContact,StatementPref,Source') );

		}

		foreach($tablearray as $table)
		{

			$filename = "$table[tablename]".".csv";

			if( $delta != "Full" and $lastDate != "" and $table['type'] == 'inc' )
			{
				echo "Outputting $filename Deltas only\r\n";
				$partial = " and $table[tablename].$table[partialfield] > '$lastDate'";
			}
			else
			{
				echo "Outputting $filename Full Data files\r\n";
				$partial = "";
			}

			$sql =  "select ".$table['fields']." INTO OUTFILE '$filepath".$table['tablename'].".csv' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n'
				from $table[tablename] where 1 $partial\r\n";

			$results = DBQueryExitOnFailure( $sql );
			#$num_rows = mysql_num_rows($results);
			echo date("Y-m-d H:i:s")." Output $filename completed\r\n";
			echo date("Y-m-d H:i:s")." Records written = ".mysql_affected_rows()." \r\n";

			unset($table);
			unset($ourData);
		}

		foreach($tablearray2 as $table)
		{

			$filename = "$table[tablename]".".csv";

			if( $delta != "Full" and $lastDate != "" and $table['type'] == 'inc' )
			{
				echo "Outputting $filename Deltas only\r\n";
				$partial = " and $table[tablename].$table[partialfield] > '$lastDate'";
			}
			else
			{
				echo "Outputting $filename Full Data files\r\n";
				$partial = "";
			}

			$sql =  "select ".$table['fields']." INTO OUTFILE '$filepath".$table['tablename'].".csv' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\r\n'
				from $table[tablename] where 1 $partial\r\n";

			//echo "$sql\r\n";

			$results = DBQueryExitOnFailure( $sql );
			echo date("Y-m-d H:i:s").' '."Output $filename completed\r\n";
			echo date("Y-m-d H:i:s")." Records written = ".mysql_affected_rows()." \r\n";

			unset($table);
			unset($ourData);
		}

		connectToDB( MasterServer, TexacoDB );
		$sql = "Insert into OutputFiles ( Type, RunAt, Filename ) values ( 'OccamExport', '$runAt', '$filename' )";

		$results = DBQueryExitOnFailure( $sql );
	}

####   E N D   O F   F U N C T I O N    ####


####   M A I N   P R O C E S S   ####


	if( $argc == 1 )
	{
		$extract_type = "Incremental";
	}
	else
	{
		switch ($argv[1])
		{
		case "-f":
        	$extract_type = "Full";
        	break;
        case "-d":
			$extract_type = "Incremental";
        	break;
        case "-o":
        	echo "Incremental ";
        	$extract_type = "Override";
        	break;
	     default:
	     	$extract_type = "None";
        	echo "This is a command line PHP script with one option.\r\n";
			echo "Usage:\r\n";
			echo $argv[0];
			echo " [-d | -f | -o | -h | <null> ]\r\n\r\n";
			echo "-d     =   delta refresh \r\n";
			echo "-o     =   override date checking\r\n";
			echo "-h     =   help\r\n";
			echo "-f	 =   Full refresh \r\n";
			echo "<null> =   Full refresh \r\n\r\n";
        	break;
      	}
	}
	
	if ( $extract_type != "None")
	{
		echo "$extract_type file extract\n";
		MainFunction( $extract_type );
	}
	
	if( $SERVER_NAME_FOR_ALL <> "TEST" )
	{
		connectToDB( ReplicationServer, TexacoDB );
		$sql = "start slave";
		$results = DBQueryExitOnFailure( $sql );
	}

		echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>