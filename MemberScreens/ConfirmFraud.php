<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Confirm Fraud";

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

<body valign="middle">
<form method="post" 
action="LettersProcess.php?AccountNo=<?php echo $AccountNo; ?>&amp;MemberNo=<?php echo $MemberNo; ?>&amp;CardNo=<?php echo $CardNo; ?>&amp;Code=1207&amp;Refer=1">
<center><br><strong><font size="4">Confirm Fraud Status for Card 
No</font></strong> <?php echo $CardNo;?>
<table border="0" cellspacing="0" cellpadding="3" align="center">
  <tbody>
  <tr>
    <td bgcolor="lavender">
      <p><button onclick="done()">Confirm</button>&nbsp;&nbsp;<font size="2" 
      face=Arial> Click this button to<br>- to Change status and send Account closed letter</font></p></td></tr>
    </tbody></table></center></form></body></html>