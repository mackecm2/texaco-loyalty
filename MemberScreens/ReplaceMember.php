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
<TITLE> New Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
</HEAD>

<BODY valign="middle">

<CENTER>
	<BR>Are you sure you wish to request a replacment card for this member?
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
	<BR>
	<BR><BUTTON onclick="window.close()">Cancel</BUTTON>
	&nbsp;&nbsp;&nbsp;<BUTTON onclick="window.returnValue=true; window.close();">OK</BUTTON>
<CENTER>

</BODY>
</HTML>
