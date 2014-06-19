<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Confirm Under Investigation";

	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET['AccountNo'];
	$CardNo = $_GET['CardNo'];
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> <?php echo $Title; ?> </TITLE>
<META NAME="Generator" CONTENT="EditPlus">

<META NAME="Author" CONTENT="">
<script>
	function done()
	{
 		document.forms[0].submit();		
	}

	function setstatusonly()
	{
		document.forms[0].action = 'LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=<?php echo $MemberNo; ?>&CardNo=<?php echo $CardNo; ?>&Code=1212&Refer=0';
		document.forms[0].submit();
	}
	
	function cancel()
	{
		window.close();		
	}

</script>
<base target='_self'>
</HEAD>

<BODY valign="middle">
	<form method="POST" action="LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=<?php echo $MemberNo; ?>&CardNo=<?php echo $CardNo; ?>&Code=1212&Refer=1">
<CENTER>
	<BR>Confirm Under Investigation Status for Card No <?php echo $CardNo;?> to
	<BR> - Create "Confirm Spend" letter
	<BR> - Set account status to Under Investigation 
	<BR> - Set Redemption Stop Flag on
	<BR>
	<BR> - Click "Under Investigation" to set account status without creating the "Confirm Spend" letter
	<BR>
	<button onclick="done()">OK</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<button onclick="setstatusonly()">Under Investigation</button>
	<button onclick="cancel()">Cancel</button>
</CENTER>
</form>
</BODY>
</HTML>
