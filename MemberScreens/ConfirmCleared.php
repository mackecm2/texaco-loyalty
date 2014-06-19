<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Confirm Cleared";

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
		window.returnValue=1;
		window.close();
	}

	function cancel()
	{
		window.returnValue=2;
		window.close();		
	}

</script>
<base target='_self'>
</HEAD>

<BODY valign="middle">
	<form method="POST" action="LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=<?php echo $MemberNo; ?>&CardNo=<?php echo $CardNo; ?>&Code=1209&Refer=1">
<table border="0" cellspacing="0" cellpadding="3" align="center">
  <tbody>
  <tr>
    <td bgcolor="lavender"><button onclick="done()">Change Status &amp; send Account Cleared letter</button><font 
      size=2 face=Arial>&nbsp;<br>Click this button to
<br>- remove Redemption Stop
<br>- set account status to Cleared
<br>- create Account Cleared letter
      </font></td></tr>
  <tr>
    <td><button onclick="cancel()">Change Status</button><font 
      size=2 face=Arial><br>Click this button to
<br>- remove Redemption Stop
<br>- set account status to Cleared</font></td></tr>
</tbody></table>
</form>
</BODY>
</HTML>
