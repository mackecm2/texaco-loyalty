<?php 
/****************************************************************************************
//	Displays a list of the last few batches and the number of outstanding request
//  so that dawleys can download the csv files associated with them again.
//
//
****************************************************************************************/

	include "../include/Session.inc";
	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";

	$results = GetUnsatisifiedCardRequestBatches( 1000 );
	
	$Title = "Request File Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="CardRequests";
	$HelpPage = "CardRequests";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";
?>
<script>
	function TransferToClient( batch, code )
	{
		if( batch == "New Batch" )
		{
			if( code )
			{
				window.location="RequestFile.php?Group="+code;
			}
			else
			{	
				window.location="RequestFile.php";
			}	
		}
		else
		{
			window.location="RequestFile.php?Repeat="+ batch;
		}
	}


</script>

<center>
<TABLE>
<TR><TH>Card Requests
<TR valign=top><TD>
<table>


<tr><th>Batch Time</th><th>Unsatisfied</th></tr>

<?php
	if( mysql_num_rows( $results ) == 0 )
	{
		echo "<tr><td>No new requests</tr>";
	}
	else
	{
		while( $row = mysql_fetch_assoc( $results ) )
		{

			echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" ";
			
			
			if( $row["Organisation1"] && $row["BatchTime"] == "New Batch" ) 
			{
				echo "onclick=\"TransferToClient('$row[BatchTime]','$row[Organisation1]')\"><td>".$row["Organisation1"];
			}
			else
			{
				echo "onclick=\"TransferToClient('$row[BatchTime]')\"><td>".$row["BatchTime"];
			}
			echo "</td><td>$row[Unsatisfied]</td>";
			echo "</tr>\n";
		}
	}
	ECHO "</table>";

	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

