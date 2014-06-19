<?php 
	
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include_once "../include/DB.inc";
?>
<script>
		function BackToApp()
	{
		window.location = "../Admin/BonusApprovalManager.php";
	}
</script>
<html>
<Head>
<BUTTON onClick="BackToApp()">Back To App</Button>
<Title>Active Promotions Report</Title>
</Head>
<body>
<?php
	connectToDB( MasterServer, TexacoDB );
	$today = date("Y-m-d");  
	$sql = "SELECT StartDate, EndDate, PromotionCode, BonusPoints, PerQuantity, RevisedBy FROM BonusPoints WHERE Status = 'A' AND Active = 'Y' ";
	$res1 = DBQueryExitOnFailure( $sql );
?>
	<h1>Report On Currently Active Promotions </h1>
	<table border=0 cellspacing=5 cellpadding=5>
	  <tr>
	    <td><u>Start Date</u></td>
	    <td><u>End Date</u></td>
	    <td><u>Promotion Code</u></td>
	    <td><u>Bonus Points</u></td>
	    <td><u>Per Quantity</u></td>
	    <td><u>Approved By</u></td>
	    <td colspan=3 align=center><u>Criteria</u></td>
	    <td></td>
	  </tr>
<?php
	$count = 1;
	while( $row = mysql_fetch_assoc( $res1 ) ) 
	{
		if ($row["StartDate"] <= $today AND ($row["EndDate"] >= $today  OR !isset($row["EndDate"])))
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
			echo "<tr bgcolor=$color><td>".$row["StartDate"]."</td>"; 
			echo "<td>".$row["EndDate"]."</td>"; 
			echo "<td>".$row["PromotionCode"]."</td>";
			echo "<td>".$row["BonusPoints"]."</td>";
			echo "<td>".$row["PerQuantity"]."</td>";
			echo "<td>".$row["RevisedBy"]."</td>";
			$sql = "SELECT FieldName, ComparisionType, ComparisionCrteria, Boolean FROM BonusCriteria WHERE PromotionCode = '".$row["PromotionCode"]."' ORDER BY CriteriaNo ASC";
			$res2 = DBQueryExitOnFailure( $sql );
	
			while( $row2 =mysql_fetch_assoc( $res2 ) ) 
			{
				echo "<td>".$row2["FieldName"]."</td>";
				echo "<td>".$row2["ComparisionType"]."</td>"; 
				echo "<td>".$row2["ComparisionCrteria"]."</td>"; 
				echo "<td>".$row2["Boolean"]."</td></tr>";
				if ($row2["Boolean"] == "OR" OR $row2["Boolean"] == "AND")
				{
					echo "<tr bgcolor=$color><td></td><td></td><td></td><td></td><td></td><td></td>";
				}
			}
		}
	}
?>
</table>
<?php
if ($count == 1)
{
    echo "No Active Promotions found";
}
?>
</body>
</html>