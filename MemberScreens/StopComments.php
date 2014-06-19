<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

	$StopsOptions = GetStopsOptions();


	$Title = "Tracking";

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
		rv = new Array( document.getElementById("cat").value,  document.getElementById("Notes").value);
		window.returnValue=rv;
		window.close();
	}

	function EnableBox()
	{
		if( document.getElementById("Notes").value != "" )
		{
			document.getElementById("done").disabled = false;
		}
		else
		{
			document.getElementById("done").disabled = true;
		}
	}

</script>
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR>
	<FORM>
	<BR>
	Category:
	<select style="width:200px" id="cat" onchange="EnableBox()">
	<option></option>
	<?php
		DisplaySelectOptions( $StopsOptions );
	?>
	</select>&nbsp;&nbsp;&nbsp;
	<BR>
	Add tracking comments
	<TEXTAREA id="Notes" name="Notes" cols=50 rows=4 onkeypress="EnableBox()"></TEXTAREA>
	<BR>
	<BR><BUTTON id="done" onclick="ReturnChoice()" disabled>OK</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="window.close()">Cancel</BUTTON>
	</form>
<CENTER>

</BODY>
</HTML>
