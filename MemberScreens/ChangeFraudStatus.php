<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/FraudInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Change Fraud Investigation Status";

	
	$MemberNo = $_GET['MemberNo'];
	$AccountNo = $_GET["AccountNo"];
	$MemberNo = $_GET['MemberNo'];
	$CurrentFraudStatus   = $_GET["FraudStatus"];
	$NewFraudStatus   = $_GET["NewStatus"];
	$FraudOptions = GetFraudOptions($CurrentFraudStatus);
	$disabled = "";

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> <?php echo $Title; ?> </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<script>
	function ReturnChoice()
	{
		rv = new Array( document.getElementById("fraudstatus").value, document.getElementById("Notes").value);
		window.returnValue=rv;
		window.close();
	}

	function EnableBox()
	{
		if( document.getElementById("fraudstatus").selectedIndex !=  "P")
		{
			document.getElementById("done").disabled = false;
			document.getElementById("Notes").disabled = false;
		}
		else
		{
			if( document.getElementById("Notes").value.length > 3 )
			{
				document.getElementById("done").disabled = false;
			}
			else
			{
				document.getElementById("done").disabled = true;
			}
			document.getElementById("Notes").disabled = true;
		}
	}

</script>
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR>
	<FORM>
	Change Fraud Status to:
	<select id="fraudstatus" name="fraudstatus" onchange="EnableBox()">
	<?php
	if ($NewFraudStatus == "undefined" )
	{
		$disabled = "disabled";
		echo "<option value=\"P\"  Selected> Please Select </option>";
		DisplayFraudSelectOptions( $FraudOptions, 99 );
	}
	else
	{
		DisplayFraudSelectOptions( $FraudOptions, $NewFraudStatus );
	
	}
	?>
	</select>
	<BR>
	<BR>
	Additional Notes:
	<TEXTAREA id="Notes" name="Notes" cols=50 rows=4 onkeypress="EnableBox()"></TEXTAREA>
	<BR>
	<BR><BUTTON id="done" onclick="ReturnChoice()" <?php echo $disabled; ?>>OK</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="window.close()">Cancel</BUTTON>
	</form>
<CENTER>

</BODY>
</HTML>
