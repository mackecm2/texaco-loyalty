<?php 

	include "../include/Session.inc";
	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";

	$results = GetUnsatisifiedCardRequestBatches( 7 );

	$Title = "Request File Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="CardRequests";
	$HelpPage = "CardRequests";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";
?>
<script>
	function TransferToClient( batch )
	{
		if( batch == "" )
		{
			window.location="RequestFile.php";
		}
		else
		{
			window.location="RequestFile.php?Repeat="+ batch;
		}
	}

	function WebRequestsToClient( batch )
	{
		if( batch == "" )
		{
			window.location="WelcomePackFile.php";
		}
		else
		{
			window.location="WelcomePackFile.php?Repeat="+ batch;
		}
	}


</script>

<center>
<TABLE>
<TR><TH>Card Requests<TH>Welcome Packs To Send
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

			echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"TransferToClient('$row[BatchTime]')\"><td>";
			if( $row["BatchTime"] == "" )
			{
				echo "New Batch";
			}
			else
			{
				echo $row["BatchTime"] ;
			}
			echo "</td><td>$row[Unsatisfied]</td>";
			echo "</tr>\n";
		}
	}
	ECHO "</table>";
?>
<TD>
<table>
<tr><th>Batch Time<th>Registations</th>
<?php
	$newSize = UnwelcomedCount();
	$results2 = GetWelcomePackBatches( );


 	echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('')\"><td>";
	echo "New Batch</td><td>$newSize</td></tr>\n";

	while( $row = mysql_fetch_assoc( $results2 ) )
	{

		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]')\"><td>";
		if( $row["BatchTime"] == "" )
		{
			echo "New Batch";
		}
		else
		{
			echo $row["BatchTime"] ;
		}
		echo "</td><td>$row[NoRecords]</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

