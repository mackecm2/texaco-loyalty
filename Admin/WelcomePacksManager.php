<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
//	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../DBInterface/BonusInterface.php";

//	$results = GetUnsatisifiedCardRequestBatches( 7 );

	$PromotionCodes = GetBonusList();

	$PromotionCodes[""] = "";

	$Title = "Welcome Pack Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="WelcomePacks";
	$HelpPage = "WelcomePacks";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";

$startDate = "";

#	This script can take a while to process
set_time_limit(0);

?>
<script language="JavaScript" src="overlib_mini.js"></script>
<script language="JavaScript" src="DatePicker.js"></script>

<script>

	function WebRequestsToClient( batch, type )
	{
		if( batch == "" )
		{
			window.location="WelcomePackFile.php";
		}
		else
		{
			window.location="WelcomePackFile.php?Repeat="+ batch+"&Type="+type;
		}
	}

</script>

<center>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<TABLE>
<TR valign=top><TD>
<table width="491px">	<fieldset>
	<legend>Create New Welcome Packs</legend>
<TR><TH colspan = 2></TH><tr>
<tr><th style="text-align: left;">Batch Time<th style="text-align: left;">Registered</th></tr>
		<form name="BonusForm" action="WelcomePackCreateBatch.php" method="POST"> 
		<input type=submit  value="Create New Batch">
		</form>
<?php

	$results3 = UnwelcomedCount();
	$results2 = GetWelcomePackBatches( );

	$RadioGrp = "";

	if( mysql_num_rows( $results3 ) == 0 ) 
	{
 		echo "<tr><td>";
		echo "New Batch</td><td>0</td></tr>\n";
	}
	else
	{
		$checked = " checked";
		while( $row = mysql_fetch_assoc( $results3 ) )
		{
			echo "<tr><td>";
			echo $row["BatchGroup"] ;
			echo "</td><td>$row[NoRecords]</td>";
			echo "</tr>\n";
			$RadioGrp .= "<input type=\"radio\" name=SpecialCriteria value=\"$row[BatchGroup]\" $checked>$row[BatchGroup]";
			$checked = "";
		}
	}

	while( $row = mysql_fetch_assoc( $results2 ) )
	{

	//	echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]')\"><td>";
		echo "<tr><td>";
		echo $row["BatchTime"];
		if($row["NoRecords"] > 0 and $row["NMC"] == 0 and $row["Standard"] == 0 and $row["NoBonus"] == 0 )
		{
			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'All')\">$row[NoRecords]";
		}
		else
		{

			echo "<td>$row[NoRecords]";

			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'NMC')\">$row[NMC]";
			
			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'Standard')\">$row[Standard]";

			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'NoBonus')\">$row[NoBonus]";

		}
		echo "</tr>\n";
	}
	echo "</fieldset></table>\n";
?>
	<td>
	
	<button onclick="window.location='WelcomeBatchBonusManager.php'">New Batch Bonus Schedule</button>
<?php
	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

