<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> No Permissions </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
</HEAD>

<BODY>
	<div style="vertical-align:middle; text-align:center">
	<?php 
		global $uname;
		echo "Current user $uname.\n";
		echo "<Br>\n";
		echo "You do not have permission to perform the requested operation.\n";
		echo "<Br>\n";
		if( isset( $errorStr ) )
		{
			echo $errorStr;
		}
	?>
	</div>
</BODY>
</HTML>
