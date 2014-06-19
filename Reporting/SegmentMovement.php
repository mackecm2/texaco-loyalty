<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	$Src = $_GET["Src"];
	$Dest = $_GET["Dest"];
	
	$sql = "Select Source.Recency as SourceRecency, Dest.Recency as DestRecency, count(*) as Total, sum( Source.OKMail = 'Y' ) as OKMail, sum(Source.OKEmail = 'Y') as OKEmail, sum(Source.Registered = 'Y') as Registered from RawKPIData$Src as Source left join RawKPIData$Dest as Dest using(CardNo) group by Source.Recency, Dest.Recency ";
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
	<a style="Color:blue" href="SelectSourceDestMonths.php\">Months</a> - ";
	<br>

<?php
	echo "$Src => $Dest"; 
   	DisplayTable( $Results, 0 );
 ?>

</Table>
 </BODY>
 </HTML>



