<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Confirm Account Cleared";

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
		window.close();		
	}

	function cancel()
	{
		window.close();		
	}

</script>
<base target='_self'>
</HEAD>

<BODY valign="middle">
	<form method="POST" action="LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=<?php echo $MemberNo; ?>&CardNo=<?php echo $CardNo; ?>&Code=1209&Refer=1">
<CENTER>
	<BR>Confirm Cleared Account No <?php echo $AccountNo;?> to
	<BR> - Create "Account Cleared" letter
	<BR> - Set account status to Cleared 
	<BR><BR>
	<button onclick="done()">OK</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<button onclick="cancel()">Cancel</button>
</CENTER>
</form>
</BODY>
</HTML>
