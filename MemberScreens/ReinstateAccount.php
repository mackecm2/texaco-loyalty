<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

	$Title = "Reinstate Account";
	
	$TrackingOptions = GetTrackingOptionsAccountReinstate();
	
	$AccountNo = $_GET["AccountNo"];
	$MemberNo = $_GET['MemberNo'];
	$Balance = $_GET['Balance'];
	$StoppedPoints   = $_GET['StoppedPoints'];
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
		rv = new Array( document.getElementById("code").value, document.getElementById("Notes").value);
		window.returnValue=rv;
		window.close();
	}

	function EnableBox()
	{
		if( document.getElementById("code").selectedIndex !=  0)
		{
			document.getElementById("done").disabled = false;
//			document.getElementById("Notes").disabled = false;
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
//			document.getElementById("Notes").disabled = true;
		}
	}

</script>
</HEAD>

<BODY valign="middle">

<CENTER>Are you sure you want to Reinstate this account?
	<BR>
	<FORM>
	Reason:
	<select id="code" name="code" onchange="EnableBox()">
	<option>Please Select</option>
	<?php
		DisplaySelectOptions( $TrackingOptions, "" );
	?>
	</select>
	<BR>
	<BR>
	Additional Notes:
	<TEXTAREA id="Notes" name="Notes" cols=50 rows=4 onkeypress="EnableBox()"></TEXTAREA>
	<BR>
	<BR><BUTTON id="done" onclick="ReturnChoice()" disabled>Reinstate Account</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="window.close()">Cancel</BUTTON>
	</form>
<CENTER>

</BODY>
</HTML>
