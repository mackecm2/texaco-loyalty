<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	
	if( isset($_GET["Type"] ) )
	{
		$Type = $_GET["Type"];
	}
	else if( isset( $_SESSION["ReportType"] ) )
	{
		$Type = $_SESSION["ReportType"] ;
	}
	else
	{
		$Type = 1;
	}

	$_SESSION["ReportType"] = $Type;

	$sql = "Select * from ReportTypes where ReportTypeId = $Type";

	$Results = DBQueryExitOnFailure( $sql );

	$Trow = mysql_fetch_assoc( $Results );

	$TableRoot = $Trow["TableRoot"];
	$_SESSION["DrillOrder"] = $Trow["DrillPath"];
		$_SESSION["Fields"] = $Trow["SumFields"];
		$_SESSION["AdditionalFields"] = $Trow["AdditionalFields"];

	unset($_SESSION["NextIndex"]);
	unset($_SESSION["OrderBy"]);
	unset($_SESSION["WhereStack"]);
	unset($_SESSION["MonthIndex"]);

	if( $Trow["TableExt"] == 'N' )
	{
		header("Location: ReportPage.php?Table=$TableRoot");
	}
	else
	{
		$_SESSION["MonthIndex"] = 'M';
	}
	$sql = "Select * from ReportMonths where TableRoot = '$TableRoot'";

	$Results = DBQueryExitOnFailure( $sql );

	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Report Index </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">

<script>
	function BackToApp()
	{
		window.location = "../MemberScreens/SelectMember.php";
	}

</script>

</HEAD>



<BODY>


	<a style="Color:blue" href="ReportTypeIndex.php">Index</a> - 
	&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Close Window" onClick="window.close()">
	
<?php 
	while( $row = mysql_fetch_assoc( $Results )  )
	{
		echo "<BR><a href=\"ReportPage.php?Table=$TableRoot$row[Month]\">$row[Month]</a>";
	}
?>
</BODY>
</HTML>
