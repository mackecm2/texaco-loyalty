<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";


	$sql = "Show Tables from Reporting like 'RawKPIData%'";

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
	<a style="Color:blue" href="ReportTypeIndex.php">Index</a> - 
	<form Action="SegmentMovement2.php">
	<input type=submit>
	<table>
	<TR><TD><input type=Radio name=Type value=registered>Registered Only</td></tr>
	<TR><TH> Source Month <TH>Destination Month
<?php
	while( $row = mysql_fetch_row( $Results ) )
	{
		$Month = substr( $row[0], 10 );  
		echo "<tr>";
		echo "<td><input type=Radio name=Src value=$Month>$Month";
		echo "<td><input type=Radio name=Dest value=$Month>$Month";
 	}
?>
	</Table>
 </BODY>
 </HTML>
