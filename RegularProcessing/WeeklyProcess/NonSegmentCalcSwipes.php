<?php 
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

	$db_user = "pma001";
	$db_pass = "amping";
	
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  echo "Start Write Back";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";


	$slave = connectToDB( ReportServer, ReportDB );
	
	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "select * from NonSegmentedMembers where 1";

	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated<br>";
	$c = 0;
	$u = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$c++;
		
		#	Now we have the Account, go get the most recent transactions
		
		$sql = "select sum(Swipes) as NumSwipes,sum(SpendVal) as TotalSpend from texaco.AccountMonthly2000 where AccountNo = '$row[AccountNo]' group by AccountNo";
		$result = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		while( $totals = mysql_fetch_assoc( $result ) )
		{
			$sql = "Update Analysis.NonSegmentedMembers set 
				NumSwipes = (NumSwipes + $totals[NumSwipes]),
				TotalSpend = (TotalSpend + $totals[TotalSpend])
				where AccountNo = $row[AccountNo]";
			#echo "$sql<br>";
			mysql_query( $sql, $slave )  or die( mysql_error($slave) );
			$u++;
		
		}
				
		#echo "<br>$sql<br";

		$sql = "select sum(Swipes) as NumSwipes,sum(SpendVal) as TotalSpend from texaco.AccountMonthly1990 where AccountNo = '$row[AccountNo]' group by AccountNo";
		$result = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		while( $totals = mysql_fetch_assoc( $result ) )
		{
			$sql = "Update Analysis.NonSegmentedMembers set 
				NumSwipes = (NumSwipes + $totals[NumSwipes]),
				TotalSpend = (TotalSpend + $totals[TotalSpend])
				where AccountNo = $row[AccountNo]";
			#echo "$sql<br>";
			mysql_query( $sql, $slave )  or die( mysql_error($slave) );
			$u++;		
		}		
		
		
		#echo "<br>$sql<br";
		
		if( ($c % 10000) == 0 )
		{
			echo date("H:i:s");
			echo "Read $c Updated $u\n";
		}
	}
  	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
	//*  	echo date("H:i:s");
	//*		echo "Finished\n";
	echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>