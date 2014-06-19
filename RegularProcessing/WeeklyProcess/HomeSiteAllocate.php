<?php
// MRM 02/06/08 added (SegmentCode LIKE ‘A%’ OR SegmentCode LIKE ‘N%’) to sql on line 45
// MRM 09/06  sql on line 115 commented out

	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  echo Date("Y-m-d h:i:s");
	//*  Echo " Start HomeSiteAllocate\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
    $uname = "HomeSiteAllocate.php";

	/*
	Create Table HomeSiteChanges
	(
		AccountNo Bigint,
		MemberNo  Bigint,
		OldHomeSite int,
		NewHomeSite int,
		CreationDate  timestamp 
	)

	Grant Usage on texaco.* to HomeSiteProcess@localhost identified by 'non-secure';

	set PASSWORD for 'HomeSiteProcess'@'weoudb' = OLD_PASSWORD( 'non-secure' );


	Grant insert on texaco.HomeSiteChanges to HomeSiteProcess@localhost;
	Grant update( HomeSite, HomeSiteDate, RevisedDate ), Select( AccountNo ) on texaco.Accounts to HomeSiteProcess@localhost;
	Grant select, insert, Update on texaco.FilesProcessed to HomeSiteProcess@localhost;

	Grant select on texaco.Members to HomeSiteProcess@texaco.rsmsecure.com;
	Grant select on texaco.Transactions to HomeSiteProcess@texaco.rsmsecure.com;
	*/
	require "../../include/DB.inc";
	require "../../DBInterface/FileProcessRecord.php";

	$db_user = "ReadOnly";
	//$db_pass = "Orange";
	$db_pass = "ORANGE";

	$slave = connectToDB( ReportServer, TexacoDB );

	$db_user = "HomeSiteProcess";
	$db_pass = "non-secure";

	$master = connectToDB( MasterServer, TexacoDB );

	$spr = CreateProcessStartRecord( "HomeSiteAllocate" );

	$sql = "select Accounts.AccountNo, MemberNo, PrimaryCard, HomeSite from Accounts join Members using( AccountNo ) where PrimaryMember = 'Y' and (Accounts.RevisedDate > HomeSiteDate or HomeSiteDate is null) and (SegmentCode LIKE 'A%' OR SegmentCode LIKE 'N%')";

	$members = DBQueryExitOnFailure($sql, $slave);
	$nummembers = mysql_num_rows( $members );
	echo date("H:i:s");
	echo " $nummembers to check\r\n";
	

	$recordsChanged = 0;
	$recordsRead = 0;

	while( $member = mysql_fetch_assoc( $members ) )
	{
		$recordsRead++;
		$pCard = $member["PrimaryCard"];
		$account = $member["AccountNo"];
		$sql = "select SiteCode from Transactions where CardNo = '$pCard' order by TransTime Desc limit 7";

		$transactions = DBQueryExitOnFailure($sql, $slave);
		$sites = array();
		$max = 0;
		$maxSite = "";
		if( mysql_num_rows( $transactions ) > 3 )
		{
			
			#echo "Got > 3 transactions\r\n";
			
			
			if( isset($member["HomeSite"]) )
			{
				$homesite = $member["HomeSite"];
			}
			else
			{
				$homesite = 0;
			}
			$sites[$homesite] = 0;
			while( $transaction = mysql_fetch_assoc( $transactions ) )
			{	
				$siteCode = $transaction["SiteCode"]; 
				if( isset( $sites[$siteCode] ) )
				{
					$sites[$siteCode]++;
				}
				else
				{
					$sites[$siteCode] = 1;
				}
				if( $sites[$siteCode] > $max )
				{
					$max = $sites[$siteCode];
					$maxSite = $siteCode;
				}
			}
			if( $maxSite != $homesite and $max > $sites[$homesite] )
			{
				$sql = "Update Accounts Set HomeSite = '$maxSite', HomeSiteDate = now() where AccountNo = $member[AccountNo]";
				if (!mysql_ping ($master)) 
				{
   				//here is the major trick, you have to close the connection (even though its not currently working) for it to recreate properly.
   					mysql_close($master);
   					$master = connectToDB( MasterServer, TexacoDB );
				}
				//echo "$sql\r\n";
				DBQueryExitOnFailure($sql, $master);
				$recordsChanged++;
				if( $member["HomeSite"] != "" )
				{
					$sql = "Insert into HomeSiteChanges values (  $member[AccountNo], $member[MemberNo], $member[HomeSite], $maxSite, now() )";
				}
				else
				{
					$sql = "Insert into HomeSiteChanges (AccountNo, MemberNo, NewHomeSite, CreationDate ) values (  $member[AccountNo], $member[MemberNo], $maxSite, now() )";
				}
				if (!mysql_ping ($master)) 
				{
   				//same as above.
   					mysql_close($master);
   					$master = connectToDB( MasterServer, TexacoDB );
				}
				DBQueryExitOnFailure($sql, $master);
				//echo "$sql\r\n";
			}
			else
			{
				#echo "HomeSite matches - Account $account\r\n";
			}
		}
		
		if( $recordsRead % 10000 == 0 )
		{
			echo date("H:i:s")." ".$recordsRead." processed\r\n";
		}
		
		
	}
	CompleteProcessRecordStats( $spr, 0, $recordsChanged );

    echo date("H:i:s");
	echo " $recordsChanged HomeSite records updated\n";
		//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo Date("Y-m-d h:i:s");
	//*	   Echo " Finish HomeSiteAllocate\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>
