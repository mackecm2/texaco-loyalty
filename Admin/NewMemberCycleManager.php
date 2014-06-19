<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
//	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";

	$Title = "New Member Cycle Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="NMC";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";

?>

<script>

	function WebRequestsToClient( batch, type, records )
	{
		if( records == 0 )
		{
			window.location="NMCCreatePhase.php?Repeat="+ batch+"&Type="+type;
		}
		else
		{
			window.location="WelcomePackFile.php?Repeat="+ batch+"&Type="+type;
		}
	}


</script>

<center>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<table>
<tr><th><th>Welcomed<th colspan=2>Phase 2<th colspan=2>Phase 3
<tr><th>Batch Time<th>Records<th>Email<th>Mail<th>Email<th>Mail
<?php
	$results = GetNMCBatches( );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<tr><td> $row[BatchTime]";
		echo "<td>$row[NoRecords]";
		echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'EmailPhase2', $row[EmailPhase2])\">$row[EmailPhase2]";
		echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'MailPhase2', $row[MailPhase2])\">$row[MailPhase2]";
		echo "</tr>\n";
		echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'EmailPhase3', $row[EmailPhase3])\">$row[EmailPhase3]";
		echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'MailPhase3', $row[MailPhase3])\">$row[MailPhase3]";
		echo "</tr>\n";
	}
	echo "</table>\n";
?>
	
	</table>

<?php
	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

