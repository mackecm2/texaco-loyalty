<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Confirm Redemption Stop";

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

	function setstatusonly()
	{
		window.returnValue=2;
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
	<form method="POST" action="LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=<?php echo $MemberNo; ?>&CardNo=<?php echo $CardNo; ?>&Code=1212&Refer=1">
<center><br><strong><font size="4">Confirm Redemption Stop for Card No </font></strong><?php echo $CardNo;?><br>
<table border="0" cellspacing="0" cellpadding="3" align="center">
  <tbody>
  <tr>
    <td bgcolor="lavender"><button onclick="done()">Change Status &amp; send Confim Spend</button><font 
      size=2 face=Arial>&nbsp;<br>&nbsp; Click this button to
<br>- set Redemption Stop
<br>- set account status to Under Investigation
<br>- create Confirm Spend letter
      </font></td></tr>
  <tr>
    <td><button onclick="setstatusonly()">Change Status &amp; set to Under Investigation</button><font 
      size=2 face=Arial>&nbsp;<br>Click this button to
<br>- set Redemption Stop
<br>- set account status to Under Investigation</font></td></tr>
  <tr>
    <td bgcolor="lavender"><button onclick="cancel()">Change Status only</button><font size="2" 
      face=Arial><br>Click this button to
<br>-  set Redemption Stop
</font></td></tr></tbody></table></center></form></body></html>