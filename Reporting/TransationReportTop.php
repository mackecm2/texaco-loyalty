<?php 
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	if( isset( $_GET["Table"] ) )
	{
		$Table = $_GET["Table"];
	}
	else if( isset( $_SESSION["Table"] ) )
	{
		$Table = $_SESSION["Table"];
	}
	else
	{
		$Table = "NonnormailsedTransactionLog200401";
	}
	$_SESSION["Table"] = $Table;

	if( isset( $_GET["Fields"] ) )
	{
		$Fields = $_GET["Fields"];
	}	
	else if( isset( $_SESSION['Fields'] ) )
	{
		$Fields = $_SESSION["Fields"];
	}
	else
	{
		$Fields = "sum(PointsAwarded) as Points, Sum(TransactionValue) as Value, Count(*) as Swipes";
	}
	$_SESSION['Fields'] = $Fields;


	if( isset( $_GET["DrillOrder"] ) )
	{
		$DrillOrder = $_GET["DrillOrder"];
	}	
	else if( isset( $_SESSION['DrillOrder'] ) )
	{
		$DrillOrder = $_SESSION['DrillOrder'];
	}
	else
	{
		$DrillOrder = "!RegionCode,!AreaCode,SiteCode,!CardNo,TransactionDate,TransactionTime";
	}
	$_SESSION['DrillOrder'] = $DrillOrder;

	$Order = explode( ",", $DrillOrder);

	if( isset( $_GET["NextIndex"] ) )
	{
		$NextIndex = $_GET["NextIndex"];
	}	
	else if( isset( $_SESSION["NextIndex"] ))
	{
		$NextIndex = $_SESSION["NextIndex"];
	}
	else
	{
		$NextIndex = $Order[0];
	}
	
	$_SESSION["NextIndex"] = $NextIndex;

	$Key = array_search( $NextIndex, $Order );

	$CleanNextIndex = trim( $NextIndex, " !" );
	if( strpos( $NextIndex, "!" ) == 0 )
	{
		$NextIsChar = true;
	}
	else
	{
		$NextIsChar = false;
	}

	if( isset( $_SESSION["WhereStack"] ) )
	{
		$WhereStack = $_SESSION["WhereStack"];
	}
	else
	{
		$WhereStack = array();
	}

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

	$WhereStack[$Key] = $WhereClause;
	$_SESSION["WhereStack"] = $WhereStack;

	if( isset( $_GET["LimitTo"] ) )
	{
		$LimitTo = $_GET["LimitTo"];
	}	
	else if( isset( $_SESSION["LimitTo"] ) )
	{
		$LimitTo = $_SESSION["LimitTo"] ;
	}
	else
	{
		$LimitTo = "";
	}

	if( $LimitTo == 'All' )
	{
		$LimitTo = "";
	}
	$_SESSION["LimitTo"] =  $LimitTo;

	if( $LimitTo )
	{
		$LimitToClause = " LIMIT $LimitTo";
	}
	else
	{
		$LimitToClause = "";
	}

	if( isset( $_SESSION["OrderByStack"] ) )
	{
		$OrderByStack = $_SESSION["OrderByStack"];
	}
	else
	{
		$OrderByStack = array();
	}

	if( isset( $_GET["OrderBy"] ) )
	{
		$OrderBy = $_GET["OrderBy"];
	}
	else if( isset( $_GET["Restore"] ) && isset( $OrderByStack[$Key] ) )
	{
		$OrderBy = $OrderByStack[ $Key ];
	}
	else if( isset( $_SESSION["OrderBy"] ) ) 
	{
		$OrderBy = $_SESSION["OrderBy"];
		if( array_search( $OrderBy, $Order ) )
		{
			$OrderBy = $CleanNextIndex;
		}
	}
	else
	{
		$OrderBy = $CleanNextIndex;
	}

	$OrderByStack[ $Key ] = $OrderBy;
	$_SESSION["OrderByStack"] = $OrderByStack;
	$_SESSION["OrderBy"] = $OrderBy;

	$sql = "Select $CleanNextIndex, $Fields from $Table $WhereClause group by $CleanNextIndex order by $OrderBy $LimitToClause";
	
//	echo $sql;

	$Results = DBQueryExitOnFailure( $sql );

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
<script>
	function SubQuery( index )
	{
<?php
		if( isset(	$Order[$Key + 1 ] ) )
		{

		if( $WhereClause == "" )
		{
			$Clause = "Where ";
		}
		else
		{	
			$Clause = " and ";
		}
		echo "\t\tvar where = \"$WhereClause $Clause $CleanNextIndex\";\n";	
?>
		if( (undefined == index) || ( 'null' == index ) )
		{
			where += " is null";
		}
		else
		{
<?php	
		if( $NextIsChar )
		{
			$p = "'\\'' + index + '\\''";
		}
		else
		{
			$p = "index";
		}
		echo "\t\t\twhere += \"=\" +  $p ;\n";
?>
		}
<?php
		$NextKey = $Order[$Key + 1 ];
		echo "\t\tvar page = 'NextIndex=$NextKey&WhereClause=' + escape( where );\n";
?>
		window.location = 'TransationReportTop.php?' + page;
<?php
		} 
?>
	}
	
	function SortBy( colName )
	{
<?php
		echo "\t\tvar curr = '$OrderBy';\n";
?>
		if( colName == curr )
		{
			colName += ' DESC';
		}
<?php
		echo "\t\tvar page = 'NextIndex=$NextIndex&OrderBy=' +colName;\n";
?>
		window.location = 'TransationReportTop.php?' + page;

	}

	function BackUp( key )
	{
		window.location = 'TransationReportTop.php?Restore=true&NextIndex=' +key;
	}

	function ChangeLimit( limit )
	{
		window.location = 'TransationReportTop.php?LimitTo=' +limit;
	}

	function RequestCSV( )
	{
		window.location = "RequestCSVFile.php?SQL=<?php echo $sql; ?>";
	}

	function BackToApp()
	{
		window.location = "../MemberScreens/SelectMember.php";
	}

</script>



<?php 
	Echo "<BR>50";
	DisplayRadioButton( "Limits", 50, $LimitTo, "onClick=ChangeLimit(50)" ); 
	Echo "200";
	DisplayRadioButton( "Limits", 200, $LimitTo, "onClick=ChangeLimit(200)" ); 
	Echo "All";
	DisplayRadioButton( "Limits", "", $LimitTo, "onClick=ChangeLimit('All')" ); 
?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<BUTTON onClick="RequestCSV()">Request CSV Version</BUTTON>
	&nbsp;&nbsp;&nbsp;&nbsp;<BUTTON onClick="BackToApp()">Back To App</Button>
<?php
	Echo "<BR>";
	echo "<a style=\"Color:blue\" href=\"ReportTypeIndex.php\">Index</a> - \n";
	echo "<a style=\"Color:blue\" href=\"ReportMonthIndex.php\">Month</a> - \n";


	for( $k = 0; $k < $Key; $k++ )
	{
		$KeyName = $Order[$k ];
		$KeyDisplay = trim( $KeyName , " !" );
		echo "<a style=\"Color:blue\" href=\"TransationReportTop.php?Restore=true&NextIndex=$KeyName\")>$KeyDisplay</a> - \n";
	}
	echo "<br>$WhereClause\n";
?>


	<BR> Click on Column Head to sort by Column
	<BR>
<table cellpadding="300" cellspacing="100">
<THEAD>
<TR>
<?php

	$header = "";
	$fields = mysql_num_fields( $Results );
	for( $k = 0; $k < $fields; $k++)
	{
		$colName = mysql_field_name( $Results, $k );
		$header .= "<th onClick='SortBy(\"$colName\")'>$colName</th>\n";
	}

	$num_results = mysql_num_rows( $Results );

	if( $num_results < 15 )
	{
		$num_cols = 1;
	}
	else if( $num_results < 30 )
	{
		$num_cols = 2;
	}
	else
	{
		$num_cols = 3;
	}

	$num_rows = $num_results/$num_cols;
	echo "<TABLE><TR>";
	$i = -1;
	$col = 0;
	while( $row = mysql_fetch_row( $Results ) )
	{
		if( ($i > $num_rows) || ($i == -1) )
		{
			$col++;
			$colour = $col % 2;
			if( $i != -1 )
			{
				echo "</TBODY></TABLE>\n";
			}
			$i = 0;
			echo "<TD style=\"vertical-align: top\"><TABLE><THEAD>$header</THEAD>\n";
			echo "<TBODY style=\"font-size: x-small\">\n";
		}
		$i++;
		$colour++;
		if ($colour & 1)
		{
			$color = "#99CCFF";
			$font = "#004080";
		}
		else
		{
			$color = "#ccffff";
			$font = "#004080";
		}
		if( is_null( $row[0] ) )
		{
			echo "<TR bgcolor=$color onclick=\"SubQuery('null')\">";
		}
		else if( $NextIsChar )
		{
			echo "<TR bgcolor=$color onclick=\"SubQuery('$row[0]')\">";
		}
		else
		{
			echo "<TR bgcolor=$color onclick=\"SubQuery($row[0])\">";
		}
		for( $f = 0; $f< $fields; $f++ )
		{
			echo "<TD>$row[$f]</TD>";
		}
		echo "\n";
	}

?>
</TABLE>
</BODY>
</HTML>
