<?php

# A powerfull little page that handles the navigation of a Database table drilldown
# It takes several very large parameters as Get paarameters it also maintains a stack of
# the drill path and sort orders at each level.
# Because of this several parameters must be set to empty on first entry.

# Table			: The Table (or Tables) to be navigated.
# Fields		: The fields to be shown at each level.
#				  These must be comma seperated agrigates as would apear in SQL
# DrillOrder	: The Fields that will be used to drill into the table
#				  If the field is a string then its field name must be prefixed by !
#				  You can use SQL functions in here with an 'as' clause
#				  but , must be escaped and you have to use " not '   ignore this ->  "
#					e.g. Date_Format( date/, "%Y %m %d" ) as Date.
#				  You can specify a .php page to jump to at any point in the drill path
#				  by prefixing the page with +.
# NextIndex		: Specifies the current level needs to be set to First drill point
#				  else you can get funny behaviour.
# AdditionalFields (SessionVariable) : You can specify Fields that are to be displayed at
#				  the different levels as a $ seperated list of comma seperated fields.
#					 e.g. Level1Field1, Level1Field2 $ L2F1, L2F2, L2F3 $ L3F1
# OrderBy		: Optional The order by clasue gets stored in the stack so if you return
#				  to this level then it is used.
# LimitTo		: Optional
# Reset			: If this parameter is passed in then the stacks are cleared;
#

	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	function ArraySubString( $needle, $haystack )
	{
		foreach( $haystack as $key => $value )
		{
			if( strstr( $value, $needle ) )
			{
				return $key;
			}
		}
		return false;
	}


	if( isset( $_GET["Reset"] ) )
	{
		$Reset = True;
	}
	else
	{
		$Reset = False;
	}

	if( isset( $_GET["Table"] ) )
	{
		$Table = $_GET["Table"];
	}
	else if( isset( $_SESSION["Table"] ) and !$Reset )
	{
		$Table = $_SESSION["Table"];
	}
	else
	{
		$Table = "";
	}
	$_SESSION["Table"] = $Table;

	if( isset( $_GET["Fields"] ) )
	{
		$Fields = $_GET["Fields"];
	}
	else if( isset( $_SESSION['Fields'] ) and !$Reset )
	{
		$Fields = $_SESSION["Fields"];
	}
	else
	{
		$Fields = "";
	}
	$_SESSION['Fields'] = $Fields;


	if( isset( $_GET["DrillOrder"] ) )
	{
		$DrillOrder = $_GET["DrillOrder"];
	}
	else if( isset( $_SESSION['DrillOrder'] ) and !$Reset )
	{
		$DrillOrder = $_SESSION['DrillOrder'];
	}
	else
	{
		$DrillOrder = "";
	}
	$_SESSION['DrillOrder'] = $DrillOrder;


	if( strstr($DrillOrder, "$" ) )
	{
		$Order = explode( "$", $DrillOrder);
	}
	else
	{
		$DrillOrder = stripslashes( $DrillOrder );
		$DrillOrder = str_replace( "/,", "$%^", $DrillOrder );
		$Order = explode( ",", $DrillOrder);
		$Order = str_replace( "$%^",",",  $Order );
	}

	if( isset( $_GET["NextIndex"] ) )
	{
		$NextIndex = stripslashes($_GET["NextIndex"]);
	}
	else if( isset( $_SESSION["NextIndex"] ) and !$Reset )
	{
		$NextIndex = $_SESSION["NextIndex"];
	}
	else
	{
		$NextIndex = $Order[0];
	}
	$_SESSION["NextIndex"] = $NextIndex;

	$Key = ArraySubString( $NextIndex, $Order );
	$CleanNextIndex = trim( $Order[$Key], " !" );

	$NextPage = "ReportPage.php";
	if( isset( $Order[$Key + 1 ] ))
	{
		if( strpos( $Order[$Key + 1 ], "+" ) === 0 )
		{
			$NextPage = trim( $Order[$Key + 1 ], " +" );
		}
	}
	if( isset( $_SESSION["AdditionalFields"] ) and 	$_SESSION["AdditionalFields"] != "" and !$Reset )
	{
		$AdditionalFieldsAll = $_SESSION["AdditionalFields"];
		$AdditionalArray = explode( "$", $AdditionalFieldsAll);
 		$LAdditionalFields = $AdditionalArray[$Key];
	}
	else
	{
		$LAdditionalFields = "";
	}



	if( isset( $_SESSION["WhereStack"] ) and !$Reset)
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
	else if( isset( $_SESSION["LimitTo"] ) and !$Reset)
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

	if( isset( $_SESSION["OrderByStack"] ) and !$Reset)
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
	else if( isset( $_SESSION["OrderBy"] ) and !$Reset)
	{
		$OrderBy = $_SESSION["OrderBy"];
		if( ArraySubString( $OrderBy, $Order ) )
		{
			$OrderBy = $CleanNextIndex;
		}
		echo "$OrderBy";
	}
	else
	{
		$OrderBy = $CleanNextIndex;
	}

	$groupByArray = explode( " as ", $CleanNextIndex );
	$groupBy = $groupByArray[0];

	$t = explode( " as ", $OrderBy );
	$OrderBy = $t[0];
	$OrderByStack[ $Key ] = $OrderBy;
	$_SESSION["OrderByStack"] = $OrderByStack;
	$_SESSION["OrderBy"] = $OrderBy;

	$Fields = trim( $Fields, ',' );
	if( $Fields != "" )
	{
		$Fields = ','.$Fields;
	}
	$LAdditionalFields = trim( $LAdditionalFields, ',' );

	if( $LAdditionalFields != "" )
	{
		$Fields .= ','. $LAdditionalFields;
	}

	$sql = "Select $CleanNextIndex $Fields from $Table $WhereClause group by $groupBy order by $OrderBy $LimitToClause";

#	echo $sql;

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
			echo "\t\tvar where = \"".addslashes($WhereClause) ." $Clause ". addslashes($groupBy)."\";\n";
?>
		if( (undefined == index) || ( 'null' == index ) )
		{
			where += " is null";
		}
		else
		{
<?php
			$p = "'\\'' + index + '\\''";
			echo "\t\t\twhere += \"=\" +  $p ;\n";
?>
		}
<?php
			$NextKey =  addslashes($Order[$Key + 1 ]) ;

			echo "\t\tvar page = 'NextIndex=$NextKey&WhereClause=' + escape( where );\n";
			echo "window.location = '$NextPage?' + page;";
		}
?>
	}

	function RestoreKey( KeyName )
	{
		window.location = 'ReportPage.php?Restore=true&NextIndex=' + escape( KeyName );
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
		echo "\t\tvar page = 'NextIndex=".addslashes($NextIndex)."&OrderBy=' +colName;\n";
?>
		window.location = 'ReportPage.php?' + page;

	}

	function BackUp( key )
	{
		window.location = 'ReportPage.php?Restore=true&NextIndex=' +key;
	}

	function ChangeLimit( limit )
	{
		window.location = 'ReportPage.php?LimitTo=' +limit;
	}

	function RequestCSV( )
	{
		window.location = "RequestCSVFile.php?SQL=<?php echo addslashes($sql); ?>";
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
	&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Close Window" onClick="window.close()">
<?php
	Echo "<BR>";
	echo "<a style=\"Color:blue\" href=\"ReportTypeIndex.php\">Index</a> - \n";
	if( isset( $_SESSION["MonthIndex"] ) )
	{
		echo "<a style=\"Color:blue\" href=\"ReportMonthIndex.php\">Month</a> - \n";
	}

	for( $k = 0; $k < $Key; $k++ )
	{
		$KeyName = $Order[$k ];
		$KeyDisplay = trim( $KeyName , " !" );
		$KeyArray = explode( " as ", $KeyDisplay );
		$KeyDisplay = $KeyArray[count($KeyArray)- 1 ];
		echo "<a style=\"Color:blue\" href=\"ReportPage.php?Restore=true&NextIndex=$KeyDisplay\")>$KeyDisplay</a> - \n";
		//echo "<a style=\"Color:blue\" OnClick=\"RestoreKey('$KeyName')\">$KeyDisplay</a> - \n";
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
		$header .= "<th onClick='SortBy(\"$colName\")' title='Click here to sort by $colName'>$colName</th>\n";
	}

	$num_results = mysql_num_rows( $Results );

	if( $num_results < 15 or $fields > 6 )
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
		else
		{
			echo "<TR bgcolor=$color onclick=\"SubQuery('$row[0]')\">";
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