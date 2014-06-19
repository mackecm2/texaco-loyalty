<?php 
	
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include_once  "../include/DB.inc";
?>
<html>
<Head>
<script>
		function BackToApp()
	{
		window.location = "../Admin/BonusApprovalManager.php";
	}
</script>
<Title>Transactions Report</Title>
</Head>
<body>
<BUTTON onClick="BackToApp()">Back To App</Button>
<?php
	connectToDB( MasterServer, TexacoDB );
	$startdate = $_POST['StartDate'];
	$enddate = $_POST['EndDate'];
	$sitecode = $_POST['SiteCode'];
	$sql = "SELECT SiteName FROM sitedata WHERE SiteCode =$sitecode ";
	$sitename = DBSingleStatQueryNoError( $sql );
	$startmonth = substr($startdate,0,4).substr($startdate,5,2);
	$endmonth = substr($enddate,0,4).substr($enddate,5,2);
	$sql = "SELECT TransactionNo, CardNo, AccountNo, TransTime, TransValue, B.SequenceNo, B.PromotionCode, B.Points  FROM Transactions".$startmonth." AS T";
	$sql .= " JOIN BonusHit".$startmonth." AS B USING (TransactionNo) WHERE T.TransTime > '".$startdate."' AND T.TransTime <  DATE_ADD('".$enddate."', INTERVAL 1 DAY) AND T.SiteCode = $sitecode";
	

	if ($endmonth > $startmonth)
	{
	$sql .= " UNION SELECT TransactionNo, CardNo, AccountNo, TransTime, TransValue, B.SequenceNo, B.PromotionCode, B.Points  FROM Transactions".$endmonth." AS T";
	$sql .= " JOIN BonusHit".$endmonth." AS B USING (TransactionNo) WHERE T.TransTime > '".$startdate."' AND T.TransTime < DATE_ADD('".$enddate."', INTERVAL 1 DAY) AND T.SiteCode = $sitecode ORDER BY TransTime, TransactionNo, SequenceNo ASC ";
	}
	else 
	{
	$sql .= " ORDER BY TransTime, TransactionNo, SequenceNo ASC ";
	}
	
	$res1 = DBQueryExitOnFailure( $sql );
	
?>
	<h1>Transactions Report For <?echo $sitecode." - ".$sitename." ".$startdate." to ".$enddate ?></h1>
	<table border=0 cellspacing=5 cellpadding=5>
	  <tr>
	    <td><u>Transaction No</u></td>
	    <td><u>Card No</u></td>
	    <td><u>Account No</u></td>
	    <td><u>Trans Time</u></td>
	    <td><u>Trans Value</u></td>
	    <td><u>Sequence No</u></td>
	    <td><u>Promotion Code</u></td>
	    <td><u>Points</u></td>
	    <td></td>
	  </tr>
<?php
	$count = 0;
	while( $row = mysql_fetch_assoc( $res1 ) ) 
	{
		$count++;
		if ($count & 1)
		{
			$color = "#99CCFF";
		}
		else
		{
			$color = "#ccffff";
		}
		echo "<tr bgcolor=$color><td>".$row["TransactionNo"]."</td>"; 
		echo "<td>".$row["CardNo"]."</td>"; 
		echo "<td>".$row["AccountNo"]."</td>"; 
		echo "<td>".$row["TransTime"]."</td>"; 
		echo "<td>".$row["TransValue"]."</td>"; 
		echo "<td>".$row["SequenceNo"]."</td>"; 
		echo "<td>".$row["PromotionCode"]."</td>"; 
		echo "<td>".$row["Points"]."</td>"; 
	}
?>
</table>
<?php
if ($count == 1)
{
    echo "No Transactions found";
}
else 
{
	echo "End of Report - ".$count." transactions found";	 
}

?>
</body>
</html>