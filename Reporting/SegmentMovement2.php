<?php 
error_reporting('E_NONE');
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	set_time_limit(0);
	$Headings = array( "New Members", 
					    "Active High", "Active Medium High", "Active Medium", "Active Low",
						"Lapsed High", "Lapsed Medium High", "Lapsed Medium", "Lapsed Low",
						"Dormant High", "Dormant Medium High", "Dormant Medium", "Dormant Low",
						"X Dormant High", "X Dormant Medium High", "X Dormant Medium", "X Dormant Low", 
						"Null" );

	$MaxSize = 18;

	function GetRowCol( $Recency, $value )
	{
		$Base = 0;
		switch( $Recency )
		{
			case "":
				return 17;
			case "N":
				return 0;
			case "A":
				$Base = 1;
			break;
			case "L":
				$Base = 5;
			break;
			case "D":
				$Base = 9;
			break;
			case "X":
				$Base = 13;
			break;
		}
		switch( $value )
		{
			case "H":
				return $Base;
			case "MH":
				return $Base+1;
			case "M":
				return $Base+2;
			case "L":
				return $Base+3;
		}
	}

	$r = array();	
	$c= array();
	for( $i = 0; $i< $MaxSize; $i++ )
	{
		$r[$i] = 0;
		$c[$i] = "";
	}

	$V = array();
	for( $i = 0; $i< $MaxSize; $i++ )
	{
		$V[$i] = $r;
		$C[$i] = $c;
	}


	$Src = $_GET["Src"];
	$Dest = $_GET["Dest"];
	$type = $_GET["Type"];
	
	if($type == 'registered')
	{
		$typequery = " Source.AccountNo <> '' ";
	}
	else
	{
		$typequery = '1';
	}
	
	$sql = "Select substring( Source.Recency, 1, 1) as SourceRecency, Source.Value as SourceValue, substring( Dest.Recency, 1, 1 ) as DestRecency, Dest.Value as DestValue, count(*) as Total from RawKPIData$Src as Source left join RawKPIData$Dest as Dest using(CardNo) where $typequery group by substring(Source.Recency, 1, 1), Source.Value, substring( Dest.Recency, 1, 1), DestValue";
	echo "<br>$sql<br>";
	$Results = DBQueryExitOnFailure( $sql );

	$BaseQuery = "Select Source.CardNo from RawKPIData$Src as Source left join RawKPIData$Dest as Dest using(CardNo) where $typequery ";

	$limit = " limit 200";

	while( $row = mysql_fetch_assoc( $Results ) )
	{
		$RowNo = GetRowCol( $row["SourceRecency"], $row["SourceValue"] );
		$ColNo = GetRowCol( $row["DestRecency"], $row["DestValue"] );

		$r = &$V[$RowNo];
		$r[$ColNo] += $row["Total"];

		$c = &$C[$RowNo];
		$c[$ColNo] = "substring( Source.Recency, 1, 1) = \\'$row[SourceRecency]\\' and Source.Value = \\'$row[SourceValue]\\' and substring( Dest.Recency, 1, 1 ) = \\'$row[DestRecency]\\' and Dest.Value = \\'$row[DestValue]\\'"; 

	}

	

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Report Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">

<Script>
	function RawSQLPage( sql )
	{
		window.location = 'RawSQLPage.php?SQL='+sql;
	}
</script>
</HEAD>


<BODY>
	<a style="Color:blue" href="ReportTypeIndex.php">Index</a> - 
	<a style="Color:blue" href="SelectSourceDestMonths.php\">Months</a> - ";
	<br>

<?php
	echo "$Src => $Dest"; 
	$rowspan = 	$MaxSize + 1;

	echo "<Table><TR><TD><TD><TD colspan=$rowspan style='text-align:center'>$Dest<TR><TD><TD>";

	for( $i = 0; $i< $MaxSize; $i++ )
	{
		echo "<TD>$Headings[$i]";
	}
	echo "<TR><TD rowspan=$rowspan style='writing-mode  : tb-rl; text-align: center'> $Src";
	for( $i = 0; $i< $MaxSize; $i++ )
	{
		$r = &$V[$i];
		$c = &$C[$i];

		echo "<TR><TD>$Headings[$i]";
		for( $j = 0; $j< $MaxSize; $j++ )
		{
			echo "<TD onclick=\"RawSQLPage('$BaseQuery $c[$j] $limit')\">$r[$j]";
		}
	}
 //  	DisplayTable( $Results, 0 );
 ?>

</Table>
 </BODY>
 </HTML>



