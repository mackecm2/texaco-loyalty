<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/MemberInterface.php";

	$MemberNo = $_GET["MemberNo"];
	$results = GetMemberDetails( $MemberNo );
	$Member = mysql_fetch_assoc( $results );
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Additional Card/Member </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<script>
	function ReturnChoice()
	{
		var t = number.value;
		if( grp[1].checked )
		{
			rv = new Array( "Card", t);
			window.returnValue= rv;
		}
		else
		{
			rv = new Array( "Member", t);
			window.returnValue= rv;
		}
		window.close();
	}

	function Show()
	{
		d1 = document.getElementById("NewCards");
		d2 = document.getElementById("NewMember");
		if( grp[1].checked )
		{
			fred.style.display = "";
			d1.style.display = "";
			d2.style.display = "none";
		}
		else
		{
			fred.style.display = "none";
			d1.style.display = "none";
			d2.style.display = "";
		}
	}
</script>
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR>
	<TABLE>
	<TR><TD>New Member<TD><input type="radio" id="choice2" name="grp" onclick="Show()">
	<TR><TD>Additional Card(s)<TD><input type="radio" id="choice1" name="grp" checked onclick="Show()">
	<TR id=fred><TD style="text-align: right">No Cards<TD><input id="number" size=2 maxlength=2 value=1>
	</TABLE>

	<DIV id="NewCards">
		<BR>Are you sure you wish to request an additional cards for this member?
	<BR>
<?php
	echo "<BR>$Member[Title] $Member[Forename] $Member[Surname]\n";
	if( $Member["Address1"] <> "" ) echo "<BR>$Member[Address1]\n"; 
	if( $Member["Address2"] <> "" ) echo "<BR>$Member[Address2]\n";
	if( $Member["Address3"] <> "" ) echo "<BR>$Member[Address3]\n";
	if( $Member["Address4"] <> "" ) echo "<BR>$Member[Address4]\n";
	if( $Member["Address5"] <> "" ) echo "<BR>$Member[Address5]\n";
	echo "<BR>$Member[PostCode]\n";

?>
	</DIV>
	<DIV id="NewMember" style="display:none">
		<BR>Are you sure you wish to create a new member?
		<BR>A new card will be automatically sent to the 
		<BR>address entered on the next screen.
	</DIV>
	
	<BR><BUTTON onclick="window.close()">Cancel</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="ReturnChoice()">OK</BUTTON>
<CENTER>

</BODY>
</HTML>
