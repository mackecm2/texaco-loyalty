<?php 

	include "../include/Session.inc";
	include "../DBInterface/ReportRequestInterface.php";

	$results = GetReportStatus();

	$Title = "Report Request Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but = "Reports";
	$HelpPage = "Reports";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";
?>
<script>
	function TransferToClient( $filename )
	{
		window.location="DownloadCSV.php?ID="+ $filename;
	}

	function Delete( $ID )
	{
		window.location="DeleteReport.php?ID="+$ID;
		event.cancelBubble = true;
	}

</script>

<center>

<form enctype="multipart/form-data" action="RequestReport.php" method="post">
	Fraud<input type="radio" name="ReportType" value="Fraud">
	<input type="submit" value="Request Report">
</form>


<table>

<tr><th>Report Time</th><th>Description</th><th>Status</th></tr>

<?php
	if( mysql_num_rows( $results ) == 0 )
	{
		echo "<tr><td colspan=3>No Reports</tr>";
	}
	else
	{
		while( $row = mysql_fetch_assoc( $results ) )
		{
			switch( $row["Status"] )
			{
			case 'S':
				echo "<tr style=\"color:green\" onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"TransferToClient('$row[ID]')\"><td>";
				echo "$row[Created]<td>$row[Description]<td>Success";
			break;
			case 'F':
				$errstr = $row["ErrorStr"];
				
				echo "<tr title=\"$errstr\" style=\"color:red\" ><td>";
				echo "$row[Created]<td>$row[Description]<td>Failed";
			break;
			case 'P':
				echo "<tr style=\"color:purple\" ><td>";
				echo "$row[Created]<td>$row[Description]<td>Pending";
			break;
			case 'R':
				echo "<tr style=\"color:black\" ><td>";
				echo "$row[Created]<td>$row[Description]<td>Running";
			break;
			}
			echo "<td><img OnClick=\"Delete($row[ID])\" src=\"trash.gif\">";
			echo "</tr>\n";
		}
	}
	echo "</table></center>";
	include "../MasterViewTail.inc";
?>

