<?php  
	error_reporting( E_ALL );
	include "../include/Session.inc";
	include "../DBInterface/LettersInterface.php";
	header("Content-Type: text/xml");
	echo "<?xml version='1.0' encoding='windows-1250'?>\n";
?>
<?php
	echo "<letters>\n";

	if( isset( $_GET["Repeat"] ) )
	{
		$TimeStamp = $_GET["Repeat"];
	}
	else
	{
		$TimeStamp = GetLettersSQLTime();
		MakeUpLetterBatch( $TimeStamp );
	}

	$results = GetRequestedLetters( $TimeStamp );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		if( strlen( $row["Forename"] ) > 0 )
		{
			$I = $row["Forename"];
		}
		else
		{
			$I = $row["Initials"];
		}

		$wholeAddress = "";
		echo "<record template=\"".htmlspecialchars( $row["Template"] )."\">\n";
		echo "<bName>".htmlspecialchars( trim( $row["Title"] . " ". $I) . " ". $row["Surname"])."</bName>\n";
		if( isset($row["Address1"]) && $row["Address1"] != "" )
		{
			echo "<bAddress1>".htmlspecialchars($row["Address1"])."</bAddress1>\n";
			$wholeAddress .= htmlspecialchars($row["Address1"]) . "\n";
		}
		if( isset($row["Address2"]) && $row["Address2"] != "" )
		{
			echo "<bAddress2>".htmlspecialchars($row["Address2"])."</bAddress2>\n";
			$wholeAddress .= htmlspecialchars($row["Address2"]) . "\n";
		}
		if( isset($row["Address3"]) && $row["Address3"] != "" )
		{
			echo "<bAddress3>".htmlspecialchars($row["Address3"])."</bAddress3>\n";
			$wholeAddress .= htmlspecialchars($row["Address3"]) . "\n";
		}
		if( isset($row["Address4"]) && $row["Address4"] != "" )
		{
			echo "<bAddress4>".htmlspecialchars($row["Address4"])."</bAddress4>\n";
			$wholeAddress .= htmlspecialchars($row["Address4"]) . "\n";
		}
		if( isset($row["Address5"]) && $row["Address5"] != "" )
		{
			echo "<bAddress5>".htmlspecialchars($row["Address5"])."</bAddress5>\n";
			$wholeAddress .= htmlspecialchars($row["Address5"]) . "\n";
		}
		if( isset($row["PostCode"]) && $row["PostCode"] != "" )
		{
			echo "<bPostCode>".htmlspecialchars($row["PostCode"])."</bPostCode>\n";
			$wholeAddress .= htmlspecialchars($row["PostCode"]) . "\n";
		}
		echo "<bWholeAddress>$wholeAddress</bWholeAddress>\n";
		echo "<bSalute>".htmlspecialchars($row["Title"]." ". $row["Surname"])."</bSalute>\n";
		echo "<bSystemdate>".htmlspecialchars($row["SystemDate"])."</bSystemdate>\n";
		echo "<bCardno>".htmlspecialchars($row["PrimaryCard"])."</bCardno>\n";
		echo "<bBalance>".htmlspecialchars($row["Balance"])."</bBalance>\n";
		echo "<bBalDate>".htmlspecialchars($row["BalDate"])."</bBalDate>\n"; 
		echo "<bLetterNo>".htmlspecialchars($row["RequestNo"])."</bLetterNo>\n";
		echo "<bStaffID>".htmlspecialchars($row["StaffID"])."</bStaffID>\n";
		echo "</record>\n";
	}

	echo "</letters>\n";
?>
