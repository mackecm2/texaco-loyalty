<?php

	#	This page is called from DisplayMember.php and allows the user to enter/edit
	#	a UKFuels Account Card Number.
	#	Once the page is submitted the function AddAccountCardProcess is called to execute.

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/CardInterface.php";

	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<LINK REL=StyleSheet HREF="../Popups.css">
<TITLE> Add/Update Account Card </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<script>
	function ReturnChoice()
	{
			var str = "";
			var newukfaccount = document.getElementsByName("accountcard");
			var c = "";
			for( i = 0; i < newukfaccount.length; i++ )
			{
				if( !isNaN(parseInt(newukfaccount[i].value)))
				{
					str += c + parseInt(newukfaccount[i].value); 
					c = ',';
				}
			}

//			alert( str );
			rv = new Array( str );
			window.returnValue= rv;

			window.close();
	}

	function AddCell( box )
	{
		var ts = box.parentNode.parentNode;
		if( ts.nextSibling == null )
		{
			newNode = ts.cloneNode( true);
			ts.parentNode.insertBefore( newNode );
		}
	}

</SCRIPT>
</HEAD>

<BODY valign="middle">
<CENTER>
	<Table><TR><TD> Account Cards &nbsp;&nbsp;&nbsp;
<?php
	$ANumbers = explode( ',', $_GET['UKFAccount'] );

	foreach( $ANumbers as $Number )
	{
		echo "<TR><TD><input name='accountcard' value='$Number'>";
	}

?>
	<TR><TD><input name="accountcard" onkeypress="AddCell(this)">
	</TABLE>

	<BR><BUTTON onclick="window.close()">Cancel</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="ReturnChoice()">OK</BUTTON>


</CENTER>

</BODY>
</HTML>



