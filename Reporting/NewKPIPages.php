<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";


	$DrillOrder = $_SESSION['DrillOrder'];
	$Order = explode( ",", $DrillOrder);

	$NextIndex = "+KPIPages.php";
	$Key = array_search( $NextIndex, $Order );

	if( isset( $_GET["WhereClause"] ) )
	{
		$WhereClause = $_GET["WhereClause"];
	}	
	else if( isset( $WhereStack[$Key] ) )
	{
		$WhereClause = $WhereStack[$Key] ;
	}
	else
	{
		$WhereClause = "";
	}
	$WhereClause = stripslashes( $WhereClause );

	$pos = strpos( $WhereClause, "MonthYear" );

	$Month = substr( $WhereClause, $pos + 11, 6 );

	$SecondWhere = substr( $WhereClause, 0, $pos ). " MonthYear=Period_add( $Month, -1 ) ".substr( $WhereClause, $pos + 19 );

//	echo $SecondWhere;
//	$sql = "Select ColumnName, TotalMembers as 'Number Of Members', TotalSpend/TotalMembers as 'Avg. Spend per Member', TotalSpend/Swipes as 'Avg Spend per Swipe', Format((TotalSpend - FuelSpend - ShopSpend)/Swipes, 2) as 'Avg. Unknown Spend per Swipe', Format(FuelSpend/Swipes, 2 ) as 'Avg. Fuel Spend per Swipe', Format(ShopSpend/Swipes, 2) as 'Avg. Shop Spend per Swipe', Format(FuelSpend/ShopSpend, 2) as 'Avg. Fuel to Shop spend ratio', Relationship as 'Avg. Relationship', Format(AvgSwipes, 2 ) as 'Avg. Swipes per Member', PointsEarned/Swipes as 'Avg. Points per Swipe', null as 'No. Points before first redemp', MembersRedeemed/TotalMembers * 100 as '% Members who have redeemed' from KPIReport $WhereClause";

function getTable( $WhereClause )
{
	global $q, $c;
	$sql = "Select ColumnName, 
					TotalMembers as 'Number Of Members',
					CurrentMonthMembers as 'Members active this month',	
					Format( TotalSpend/TotalMembers, 2 ) as 'Avg. Spend per Member', 
					Format( TotalSpend/TotalSwipes, 2 ) as 'Avg Spend per Swipe',
					Format( CurrentMonthSpend/CurrentMonthSwipes, 2 ) as 'Avg Spend per Swipe current month',
					Format(TotalFuelSpend/TotalShopSpend, 2) as 'Avg. Fuel to Shop spend ratio',
					AvgRelationship as 'Avg. Relationship',
					Format( TotalSwipes/TotalMembers , 2 ) as 'Avg Swipes per Member',
					Format( CurrentMonthSwipes/CurrentMonthMembers, 2 ) as 'Avg Swipes per Member current month',
					Format( PointsEarned/TotalSwipes, 2)  as 'Avg Points per swipe',
					CurrentMonthSwipes as 'Current Month Swipes',
					Format(( TotalSpend - TotalFuelSpend - TotalShopSpend)/TotalSwipes, 2) as 'Avg. Unknown Spend per Swipe',
					Format(TotalFuelSpend/TotalSwipes, 2 ) as 'Avg. Fuel Spend per Swipe',
					Format(TotalShopSpend/TotalSwipes, 2) as 'Avg. Shop Spend per Swipe', 
					Format(AvgSwipes, 2 ) as 'Avg. Swipes per Member', 
					null as 'No. Points before first redemp', 
					Format( MembersRedeemed/TotalMembers * 100, 2 ) as '% Members who have redeemed',
					Format( RetainedCount/LastYearCount * 100, 2 ) as '% Members retained from last year'
					from NewKPIReport $WhereClause";


	$Results = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $Results )  > 0 )
	{
		$Columns = array();
		$Names = array();
		$q = 0;
		while( $info = mysql_fetch_field($Results)  )
		{
			 $Names[$q] = $info->name;
			 $q++;
		}
		$Columns[0] = $Names;
		$c = 1;
		while( $Col = mysql_fetch_row( $Results ) )
		{
			 $Columns[$c] = $Col;
			 $c++	 ;
		}
	}
	return $Columns;
}

$thisMonth = getTable( $WhereClause );
$lastMonth = getTable( $SecondWhere );


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Report Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>


<BODY>
	<a style="Color:blue" href="ReportTypeIndex.php">Index</a> - 
<?php
	echo "<a style=\"Color:blue\" href=\"ReportMonthIndex.php\">Month</a> - \n";


	for( $k = 0; $k < $Key; $k++ )
	{
		$KeyName = $Order[$k ];
		$KeyDisplay = trim( $KeyName , " !" );
		echo "<a style=\"Color:blue\" href=\"ReportPage.php?Restore=true&NextIndex=$KeyName\")>$KeyDisplay</a> - \n";
	}
 ?>
<H2><?php Echo $WhereClause; ?></H2>
<Table>
<?php
	for( $b = 0; $b < $q; $b++ )
	{
		echo "<tr>\n";
		for( $d = 0; $d < $c; $d++ )
		{
			$t = $thisMonth[$d];
			if( $b == 0 )
			{
				echo  "<td style='writing-mode : tb-rl'>$t[$b]";
			}
			else
			{
				echo "<td>$t[$b]\n";
			}
		}
	}
?>
</Table>

<H2>Change from Last Month</H2>

<Table>
<?php
	for( $b = 0; $b < $q; $b++ )
	{
		echo "<tr>\n";
		for( $d = 0; $d < $c; $d++ )
		{
			$t = $thisMonth[$d];
			$l = $lastMonth[$d];
			if( $b == 0 )
			{
 				echo  "<td style='writing-mode : tb-rl'>$t[$b]";
			}
			else if( $d == 0 )
			{
				echo "<td>$t[$b]\n";
			}
			else if( $l[$b] == 0 )
			{
				echo "<td> --\n";
			}
			else
			{
				$r = ($t[$b] - $l [$b])/$l[$b] * 100.0;
				printf(  "<td><nobr>%2.2f %%<nobr>\n", $r);
			}
		}
	}

	$LastMonthTopRow = $lastMonth[7];
	$LastMonthCount = $LastMonthTopRow[1];
	
?>
</Table>


<?php
/*	$sql = "select * from RetentionReport $WhereClause";
	$RetResults = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $RetResults )  > 0 )
	{
		$retRow = mysql_fetch_assoc( $RetResults );
		$AnRet = number_format($retRow["Retained"]/$retRow["MembersLastYear"] * 100, 2 );
		$OKRet = number_format($retRow["RetainedMail"]/$retRow["OKMailLastYear"] * 100, 2 );
		$NoRet = number_format($retRow["RetainedNotMail"]/$retRow["NotMailLastYear"] * 100, 2);
		$Month12Change = $retRow["MembersLastYear"] - $retRow["CurrentCount"];
		$PerMonth12Change = number_format($Month12Change/$retRow["MembersLastYear"]* 100, 2 );

		$LastMonthChange  = $LastMonthCount - $retRow["CurrentCount"];
		$PerLastMonth  =  number_format($LastMonthChange / $LastMonthCount * 100, 2);

		echo "<table cellspacing = 10>";
		echo "<TR><TD>Number of Members<TD>". number_format($retRow["CurrentCount"])."<TD><TD>Annual Retention Rate<TD>$AnRet%";
		echo "<TR><TD>Change Since Last Report<TD>" . number_format($LastMonthChange)."<TD>$PerLastMonth<TD>Annual Retention Rate - OK Mail<TD>$OKRet%";
		echo "<TR><TD>12 Month Change<TD>". number_format( $Month12Change ) . "<TD>$PerMonth12Change%<TD>Annual Retention Rate - Not OK Mail<TD>$NoRet%";


		echo "</TABLE>"; 
//		print_r( $retRow );
	}

*/
?>

 </BODY>
 </HTML>
