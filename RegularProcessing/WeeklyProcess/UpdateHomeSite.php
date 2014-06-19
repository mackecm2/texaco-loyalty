<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

	$db_user = "pma001";
	$db_pass = "amping";
	
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  echo "Start Write Back";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";


	$slave = connectToDB( ReportServer, TexacoDB );
	
	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "select Members.PrimaryCard, Accounts.AccountNo from Accounts join Members using(AccountNo) where Accounts.SegmentCode is not NULL and Accounts.HomeSite is NULL";

	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated<br>";
	$c = 0;
	$u = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
		
		#	Now we have the PrimaryCard, go get the most recent transaction
		
		$sql = "select SiteCode from Transactions where CardNo = '$row[PrimaryCard]' order by TransTime DESC limit 1";
		
		#echo "<br>$sql<br";
		
		$TransResult = mysql_query( $sql, $master )  or die( mysql_error($master) );
		while( $Transrow = mysql_fetch_assoc( $TransResult ) )
		{
			$sql = "Update Accounts set HomeSite = '$Transrow[SiteCode]' where AccountNo = $row[AccountNo]";
			mysql_query( $sql, $master )  or die( mysql_error($master) );
			$u++;
		
		}
		
		if( ($c % 10000) == 0 )
		{
			echo date("H:i:s");
			echo "Read $c Updated $u\n";
		}
	}
  	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo date("H:i:s");
	//*     echo "Finished\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>