<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

	$CreditOptions = GetAdjustmentOptions();

	if( CheckPermisions(PermissionsMassiveAdjust) )
	{
		$MaxValue = 10000000;
	}
	elseif( CheckPermisions(PermissionsBigAdjust) )
	{
		$MaxValue = 5000;
	}
	else if( CheckPermisions(PermissionsSmallAdjust) )
	{
		$MaxValue = 150;
	}
	else
	{
		$MaxValue = 0;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<LINK REL=StyleSheet HREF="../Popups.css">
<TITLE> Add Tracking </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">

<script>

	function ReturnChoice( which )
	{
		var adjust = Adjustment.value;
		if( !isNaN(adjust) )
		{
			adjust = Math.abs(parseInt( adjust ) );
			if( which == "Debit" )
			{
				adjust = -adjust;
			}
			rv = new Array( Credit.value, adjust, Notes.value);
			window.returnValue=rv;
			window.close();
		}
	}

	function EnableBox(  )
	{
		var adjust = parseInt(Adjustment.value);
		if( !isNaN(adjust) && ( adjust > 0) )
		{
			if( (Credit.selectedIndex != 0)
			&& ((Credit.options[Credit.selectedIndex].value != "OC") || (Notes.value != "")) )
			{
				if( adjust > <?php echo $MaxValue; ?> )
				{
					CreditButton.disabled = true;
					DebitButton.disabled = true;
					alert( "You do not have permission to enter that amount" );
				}
				else
				{
					CreditButton.disabled = false;
					DebitButton.disabled = false;
				}
			}
			else
			{
				CreditButton.disabled = true;
				DebitButton.disabled = true;
			}
		}
		else
		{
			CreditButton.disabled = true;
			DebitButton.disabled = true;
		}
	}

</script>
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR> Current Balance <?php echo $_GET["Ammount"] ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	Adjustment  <INPUT id=Adjustment onchange="EnableBox()" size=8 maxlength=8 >
	<BR>
	<TABLE>
	<TR>
	<TD>
	Category:
	<select style="width:200px" id="Credit" onchange="EnableBox()">
	<option></option>
	<?php
		DisplaySelectOptions( $CreditOptions );
	?>
	</select>&nbsp;&nbsp;&nbsp;
	<TD>
	</TABLE>
	<DIV>
	Additional Notes:
	<TEXTAREA id="Notes" cols=50 rows=2 onchange="EnableBox()"></TEXTAREA>
	</DIV>
	<BR>
	<BR>
	<BUTTON style="width:50px" id=CreditButton onclick="ReturnChoice('Credit')" disabled>Credit</BUTTON>
	&nbsp;&nbsp;&nbsp;
	<BUTTON onclick="window.close()">Cancel</BUTTON>
	&nbsp;&nbsp;&nbsp;
	<BUTTON style="width:50px" id=DebitButton onclick="ReturnChoice('Debit')" disabled>Debit</BUTTON>
	&nbsp;&nbsp;&nbsp;
<CENTER>

</BODY>
</HTML>
