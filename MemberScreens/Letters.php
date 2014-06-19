<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/DisplayFunctions.inc";
	$LettersList = GetLettersList();
	$AccountNo = $_GET['AccountNo'];
	$MemberNo = $_GET['MemberNo'];

	$Title = "Letters";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> <?php echo $Title; ?> </TITLE>
<META NAME="Generator" CONTENT="EditPlus">

<META NAME="Author" CONTENT="">
<script>
	function ReturnReturned( letter )
	{
		rv = new Array( letter );
		window.returnValue=rv;
		window.close();
	}

	function ReturnChoice()
	{
		rv = new Array( code.value );
		window.returnValue=rv;
		window.close();
	}

</script>
<base target='_self'>
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR>
	<select id="code" >
	<option></option>
	<?php
		DisplaySelectOptions( $LettersList );
	?>
	</select>
	<BR>
	<BR><BUTTON onclick="window.close()">Cancel</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="ReturnChoice()">OK</BUTTON>
	<BR>
	<BR>
	<BR>
</CENTER><font size=2>

<?
$sql = "SELECT FraudStatus,  
ConfirmSpend1SentDate,  
ConfirmSpend1ReturnedDate,
ConfirmSpend2SentDate,      
ConfirmSpend2ReturnedDate, 
ProofOfReceiptsSentDate,      
ProofOfReceiptsReturnedDate, 
AccountClosedDate, 
AccountClearedDate FROM AccountStatus WHERE AccountNo = $AccountNo";
$results = DBQueryExitOnFailure( $sql );
$row = mysql_fetch_array( $results );

if( $row[ConfirmSpend1SentDate] != NULL OR  $row[ConfirmSpend2SentDate] != NULL OR $row[ProofOfReceiptsSentDate] != NULL )
{
	echo "<table border=0 cellspacing=0 cellpadding=3 width=533 align=center><tbody><tr>";
	echo "<td width=25%>Letter</td><td width=22%>Sent Date </td><td width=23%>Returned Date</td><td width=30%></td></tr>";
	if( $row[ConfirmSpend1SentDate] != NULL )
	{
		echo "<form method=post name=cs1date action=InsertReturnDate.php?Form=cs1&AccountNo=$AccountNo&MemberNo=$MemberNo>";
		echo "<tr><td width=25%>Confirm Spend 1</td>";
		echo "<td width=22%>$row[ConfirmSpend1SentDate]</td>";
		echo "<td width=23%>$row[ConfirmSpend1ReturnedDate]</td>";
		echo "<td width=30%>";
		if( $row[ConfirmSpend1ReturnedDate] == NULL )
		{
			echo "<BUTTON onclick=\"ReturnReturned('cs1')\">Returned</BUTTON>";
		}
		echo "</td></tr></form>"; 
	}
	if( $row[ConfirmSpend2SentDate] != NULL )
	{
		echo "<form method=post name=cs2date action=InsertReturnDate.php?Form=cs2&AccountNo=$AccountNo&MemberNo=$MemberNo>";
		echo "<tr><td width=25%>Confirm Spend 2</td>";
		echo "<td width=22%>$row[ConfirmSpend2SentDate]</td>";
		echo "<td width=23%>$row[ConfirmSpend2ReturnedDate]</td>";
		echo "<td width=30%>";
		if( $row[ConfirmSpend2ReturnedDate] == NULL )
		{
			echo "<BUTTON onclick=\"ReturnReturned('cs2')\">Returned</BUTTON>";
		}
		echo "</td></tr></form>"; 
	}
	if( $row[ProofOfReceiptsSentDate] != NULL )
	{
		echo "<form method=post name=pordate action=InsertReturnDate.php?Form=por&AccountNo=$AccountNo&MemberNo=$MemberNo>";
		echo "<tr><td width=25%>Proof of Receipts</td>";
		echo "<td width=22%>$row[ProofOfReceiptsSentDate]</td>";
		echo "<td width=23%>$row[ProofOfReceiptsReturnedDate]</td>";
		echo "<td width=30%>";
		if( $row[ProofOfReceiptsReturnedDate] == NULL )
		{
			echo "<BUTTON onclick=\"ReturnReturned('por')\">Returned</BUTTON>";
		}
		echo "</td></tr></form>"; 
	}
	echo "</tbody></table></font>";
}
?>
</BODY>
</HTML>