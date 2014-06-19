<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/Locations.php";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/TransactionsInterface.php";
	include "../DBInterface/RedemptionInterface.php";
	include "../DBInterface/MonthlyInterface.php";
	include "../DBInterface/StatementInterface.php";
	include "../DBInterface/MemberInterface.php";

	if( isset( $_POST["AccountNo"] ) )
	{
		$AccountNo = $_POST["AccountNo"];
	}
	else
	{

	}

	if( isset( $_POST["MemberNo"] ) )
	{
		$MemberNo = $_POST["MemberNo"];
	}

	if( isset( $_POST["CardNo"] ) )
	{
		$CardNo = $_POST["CardNo"];
	}

	if( $AccountNo == "" )
	{
		header("Location: SelectMember.php");
		exit();
	}
	$results = GetPrimaryMemberDetails( $AccountNo );


	if( $row = mysql_fetch_assoc( $results ) )
	{
		$Name = "$row[Title] $row[Surname]";
		$Address1 = $row["Address1"];
		$Address2 = $row["Address2"];
		$Address3 = $row["Address3"];
		$Address4 = $row["Address4"];
		$Address5 = $row["Address5"];
		$PostCode = $row["PostCode"];
		$Salute = "$row[Title] $row[Surname]";
		$Systemdate =$row["SystemDate"];
		$Cardno = $row["PrimaryCard"];
		$Balance = $row["Balance"];
		$BalDate = $row["BalDate"]; 
	}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> <?php echo "$Name"; ?> History </TITLE>
</HEAD>

<BODY>

<br>
<br>
<br>
<br>
<br><?php echo "$Name"; ?> 
<br><?php echo "$Address1"; ?> 
<br><?php echo "$Address2"; ?> 
<br><?php echo "$Address3"; ?> 
<br><?php echo "$Address4"; ?> 
<br><?php echo "$Address5"; ?> 
<br><?php echo "$PostCode"; ?> 
<br>
<br> Dear <?php echo "$Salute"; ?>
<br>
<br> 
<br><b>Star Rewards Card Number: <?php echo "$CardNo"; ?></b>

<P>In response to your recent enquiry, please find enclosed details of your award history.

<P>Should you require any further assistance, please do not hesitate to
contact us the Star Rewards hotline on 0800 234 6336 or email us at
starrewards@clientmail.eu.com.
<P>
Alternatively, visit our website at www.starrewards.co.uk to find out
more about special offers open to Star Rewards members.

<P>We thank you for your custom and look forward to seeing you at Texaco again soon.

<P>Yours sincerely,
<Br>
<img SRC="bronaghsignature.jpg">
<Br> Bronagh Carron.
<Br> Marketing & Loyalty Manager.
<br> 
<table cellpadding=20>
<?

	if( isset( $_POST["Transactions"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Transactions</I><TD>\n";

		$results = GetTransactionHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 1 );
		}
		else
		{
			echo "No Entries<Br>";
		}

	}

	if( isset( $_POST["Monthlys"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Card Monthly Sum</I><TD>\n";

		$results = GetMonthlyCardHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 1 );
		}
		else
		{
			echo "No Entries<Br>";
		}
	}
	if( isset( $_POST["MonthlyAccount"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Account Monthly Sum</I><TD>\n";
		
		$results = GetMonthlyAccountHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 0 );
		}
		else
		{
			echo "No Entries<Br>";
		}
	}

	if( isset( $_POST["Statements"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Statement</I><TD>\n";

		$results = GetStatementHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 0 );
		}
		else
		{
			echo "No Entries<Br>";
		}
	}

	if( CheckPermisions( PermissionsRestrictedHistoryPrinting ) and isset( $_POST["Tracking"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Tracking</I><TD>\n";

		$results = GetTrackingHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 0 );
		}
		else
		{
			echo "No Entries<Br>";
		}
	}

	if( isset( $_POST["Redemptions"] ) )
	{
		echo "<TR><TD style=\"vertical-align: top\"><I>Redemptions</I><TD>\n";

		$results = GetPrintableRedemptionHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayPrintableTable( $results, 0 );
		}
		else
		{
			echo "No Entries<Br>";
		}
	}
	echo "</TABLE>";
?>



</BODY>
</HTML>
