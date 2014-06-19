<?php 

	include "../include/Session.inc";
	include "../DBInterface/OrdersInterface.php";



	$types = GetOrderFileTypes();

	$Title = "Orders File Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="Orders";
#	$HelpPage = "OrdersRequests";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";
?>
<script>
	function TransferToClient( type, batch )
	{
		if( batch == "" )
		{
			window.location="OrdersFile.php?Type=" + type;
		}
		else
		{
			window.location="OrdersFile.php?Type="+type+"&Repeat=" + escape( batch );
		}
	}

	function ConfirmBatch( type, tim )
	{
		window.location = "ConfirmOrders.php?Type="+type+"&Timestamp="+ tim;
		event.cancelBubble = true;
	}

</script>

<table>


<?php 
	
	echo "<td><TABLE  style=\"font-size:xx-small;\" ><TR  valign=top >\n";

//	$Lump = array();
	
//	$count = 0;
//	foreach ($types as $key => $code )
//	{
//		$Lump[$count++] = $key;
//		echo "$count $code $key :)";
//	}
//	$Total = $count ;
	$key ="";
	$results = GetUnsatisifiedOrdersBatches( $key,  7 );

	if( mysql_num_rows( $results ) == 0 )
	{
		echo "<tr><td>No new orders</tr>";
	}
	else
	{
		$count = 0;
		$current = -1;
		$subclose = "";
		while( $row = mysql_fetch_assoc( $results ) )
		{
			if( $current != $row['FileGroup'])
			{
				echo $subclose;
				$count++;
				if( $count == 4 )
				{
					echo "</TR><TR  valign=top>";
				}
					$current = $row['FileGroup'];
					if( isset($types[$current])  )
					{
						$name = $types[$current];
						$FileGroup = $current;
					}
					else
					{
						$name = "Unknown";
						$FileGroup = 0;
					}

				// start  a new sub table
					echo "<TD><table  style=\"font-size:xx-small\" >\n";
					if ($name == "Staff Incentive Scheme")
					{
						echo "<TR><TH  colspan=3 style=\"width=200; background-color: #66FFFF\">$name</TH>\n";
					}
					else 
					{
						echo "<TR><TH  colspan=3 style=\"width=200; background-color: red\">$name</TH>\n";
					}
					$subclose = "</table>"; 
			}
			echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"TransferToClient($FileGroup, '$row[BatchTime]')\">\n     <td>";
			if( $row["BatchTime"] == "" )
			{
				echo "New Batch";
				echo "</td>\n     <td style=\"text-align: center\">$row[Unsatisfied]</td>\n";
			}
			else
			{
				echo $row["BatchTime"] ;
				echo "</td>\n    <td style=\"text-align: center\">$row[Unsatisfied]</td>\n";
				echo "    <td><Button onclick=\"ConfirmBatch($FileGroup, '$row[BatchTime]')\"> Confirm</Button></td>\n";
			}
			echo "</tr>\n";
		}

	}
	echo $subclose;

	echo "</TABLE></table>\n";
	include "../MasterViewTail.inc";
?>

