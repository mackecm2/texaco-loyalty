<?php  
	include "../include/Session.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/TransactionsInterface.php";
	include "../DBInterface/RedemptionInterface.php";
	include "../DBInterface/MonthlyInterface.php";
	include "../DBInterface/StatementInterface.php";
	include "../DBInterface/MemberInterface.php";

	function DisplayRows( $results )
	{
		$fields = mysql_num_fields( $results );
		$pre = "";
		for( $k = 0; $k < $fields; $k++)
		{
			echo $pre . mysql_field_name( $results, $k );
			$pre = ",";
		}
		echo "\n";

		while($row = mysql_fetch_row($results))
		{
			$pre = "";
			for( $k = 0; $k < $fields; $k++)
			{
				echo $pre.$row[ $k ];
				$pre = ",";
			}
			echo "\n";
		}
	}


	echo "<?xml version='1.0' encoding='windows-1250'?>\n";
	echo "<transactionHistory>\n";

	$AccountNo = $_GET["AccountNo"];

	$results = GetPrimaryMemberDetails( $AccountNo );

	if( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<header>\n";
		echo "<bName>$row[Title] $row[Surname]</bName>\n";
		echo "<bAddress1>$row[Address1]</bAddress1>\n";
		echo "<bAddress2>$row[Address2]</bAddress2>\n";
		echo "<bAddress3>$row[Address3]</bAddress3>\n";
		echo "<bAddress4>$row[Address4]</bAddress4>\n";
		echo "<bAddress5>$row[Address5]</bAddress5>\n";
		echo "<bPostcode>$row[PostCode]</bPostcode>\n";
		echo "<bSalute>$row[Title] $row[Surname]</bSalute>\n";
		echo "<bSystemdate>$row[SystemDate]</bSystemdate>\n";
		echo "<bCardno>$row[PrimaryCard]</bCardno>\n";
		echo "<bBalance>$row[Balance]</bBalance>\n";
		echo "<bBalDate>$row[BalDate]</bBalDate>\n"; 
		echo "</header>\n";
	}

	if( isset( $_GET["Transactions"] ) )
	{
		$results = GetPrintTransactionHistory( $AccountNo );

		$CardNo = "";

		echo "<transactions>";
		echo "Card Number, Date, Points Awarded, Site \n";
		
		while( $row = mysql_fetch_assoc( $results ) )
		{
			if( strcmp($row["CardNo"], $CardNo ) != 0 )
			{
				$CardNo = $row["CardNo"];
				echo $CardNo;
			}
			if( $row["Date"] != "" )
			{
				echo ",$row[Date], $row[PointsAwarded], $row[SiteCode]\n";
			}
			else
			{
				echo ",,,\n";
			}
		}
		echo "</transactions>\n";	
	}

	if( isset( $_GET["Monthly"] ) )
	{
		echo "<monthlycard>";

		$results = GetMonthlyCardHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayRows( $results, 0 );
		}
		echo "</monthlycard>\n";

		echo "<monthlymember>";

		$results = GetMonthlyMemberHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayRows( $results, 0 );
		}
		echo "</monthlymember>\n";

	}

	if( isset( $_GET["Statements"] ) )
	{
		echo "<statement>";

		$results = GetStatementHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayRows( $results, 0 );
		}
		echo "</statement>\n";
	}

	if( isset( $_GET["Tracking"] ) )
	{
		echo "<tracking>";

		$results = GetTrackingHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayRows( $results, 0 );
		}
		echo "</tracking>";
	}
	if( isset( $_GET["Redemptions"] ) )
	{
		echo "<redemptions>";

		$results = GetRedemptionHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayRows( $results, 0 );
		}
		echo "</redemptions>";
	}


	echo "</transactionHistory>\n";
?>