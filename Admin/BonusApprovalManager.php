<?php

	include_once "../include/Session.inc";
	include_once "../include/CacheCntl.inc";
	include_once "../DBInterface/BonusInterface.php";

	$results = GetApprovalBonuses();

	$Title = "Promotions Requiring Approval";
	$currentPage = "Bonuses";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	include "../MasterViewHead.inc";
?>

<script>
	dirty = false;

	function SetDirty()
	{
		dirty = true;
		document.getElementById("update").disabled = false;
		document.getElementById("create").disabled = true;
	}
	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved your changes to the database!";
		}
	}
	function RemoveDirty()
	{
		dirty = false;
	}

	function CreateEntry()
	{
		location = "BonusEdit.php?add=new";
	}
	function ViewAll()
	{
		location = "BonusManagerAll.php";
	}
		function ViewActive()
	{
		location = "BonusManager.php";
	}
	function ReportActive()
	{
		location = "../Reporting/ActivePromotions.php";
	}
		function ReportRejected()
	{
		location = "../Reporting/RejectedPromotions.php";
	}
		function ReportTransactions()
	{
		location = "../Admin/TransactionReport.php";
	}
		function Edit( row )
	{
		code = row.firstChild.innerText;
		location = "BonusApprover.php?promoCode=" + code;
	}
		function BackToApp()
	{
		window.location = "../Admin/BonusApprovalManager.php";
	}
</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td align=center><Button id="create" OnClick="CreateEntry()">Add Entry</Button> <Button id="ViewActive" OnClick="ViewActive()">View Active</Button> <Button id="ViewAll" OnClick="ViewAll()">View All</Button>
	</td></tr>
	<tr><td><Button id="ReportActive" OnClick="ReportActive()">Report on Currently Active</Button> <Button id="ReportRejected" OnClick="ReportRejected()">Report on Rejected</Button> <Button id="TransactionReport" OnClick="ReportTransactions()">Transactions Report</Button>
	</td></tr>
	</table>
	</center>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	<h2 align=center>Promotions requiring approval</h2><p></p>
	<center><table id="DataArea" width="100%">

<?php

$numrows = mysql_num_rows($results);
if( $numrows >0 )
{
?>
	
		<TR>
			<TH>Promotion</TH>
			<TH>Description</TH>
			<TH>Criteria Summary</TH>
			<TH>Points/Quant</TH>
			<TH>Applies To</TH>
			<TH>Start Date</TH>
			<TH>End Date</TH>
			<TH></TH>
			
		</TR>
<?php
	while( $row = mysql_fetch_array( $results ))
	{
		$activecount = 0;
		$criteria = GetAbriviatedBonusCriteria( $row["PromotionCode"] );
		echo "<TR OnClick=\"Edit(this)\">";
		echo "<TD><input name=\"Promos[]\" type=\"hidden\" value=\"$row[PromotionCode]\">$row[PromotionCode]</TD>";
		echo "<TD>$row[BonusName]</TD>";
		echo "<TD>$criteria</TD>";
		echo "<TD>$row[BonusPoints]/$row[PerQuantity]";
		echo "</TD>";
		echo "<TD>$row[AppliesTo]</TD>";
		echo "<TD>$row[StartDate]</TD>";
		echo "<TD>$row[EndDate]</TD>";
		echo "<td><input type=button value='Action' name='approve' OnClick=\"Edit(this)\"></td>\n";
		echo "</TR>";
	}
}
else 
{
	echo "<td align=center>no bonus schemes requiring approval</td></tr>";
}
echo "</TABLE>";		
	
	include "../MasterViewTail.inc";
?>