<?php
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	$sql = "select * from ReportTypes";
	
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
<script>

	function ClickRow( id )
	{
		window.location = "EditSingleReport.php?Id="+id;
	}

</script>
</HEAD>

<Body>
	<br><button onclick="window.location='../Misc/ManagementPage.html'">Management Page</button>
	<button  onclick="window.location='../Reporting/ReportTypeIndex.php'">Reports</button>
	<button  onclick="window.location='../MemberScreens/SelectMember.php'">Return To App</button>
<?	
	DisplayTableWithClick( $Results, 0, "ClickRow", 0 );

?>
<button onclick="ClickRow( 'New' )"> Create New Report</button>
</Body>
