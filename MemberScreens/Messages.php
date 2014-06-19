<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/MessagesInterface.php";

	$MemberNo = $_GET["MemberNo"];

	#	Set this so there is no limit to the number of messages shown to the user
	#	This may be changed at a later date
	$limit  = '0';
	$i = 1;

	$Messages = GetCurrentWebActiveMessages();
	$MessageOutput =  "";

	while( $row = mysql_fetch_assoc( $Messages ) )
	{
		if(($limit == '0' ) OR ($i <=  $limit) )
		{

			#	We need to check that the message relates to this member.

			$result = checkmembermessage($MemberNo,$row);
			#echo"result is $result<br>";
			if( $result )
			{
				$MessageOutput .=  "Message No $i<br>";
				$MessageOutput .= "$row[MessageText]<br><br>";
				$ShowMessages = 'True';
				$i++;
			}


		}
		else
		{
			break;
		}

	}


	if( $ShowMessages != 'True' )
	{
?>
<html>
<head>
<title>

</title>
<script>
	function clo()
	{
		window.close();
	}
</script>

</head>
<body onload="clo();">
</body>
<?php
	}
	else
	{
?>

<html>
<head>
<title>

</title>
</head>
<body>
<script>
	function done()
	{
		window.close();
	}

</script>

	<form target="_blank" method="POST" action="Messages.php">
	<table style="width:80%; align:center">
<?php
	#	echo "<input type=\"hidden\" name=\"MemberNo\" value=\"$MemberNo\">\n";

		echo "<tr><td style=\"text-align:center\" > <font face=\"Arial, Helvetica, sans-serif\" size=\"-1\"></font>$MessageOutput<td></tr>&nbsp;<br>";



?>
	</table>
	<center>
	<button onclick="done()">OK</button>&nbsp;&nbsp;&nbsp;&nbsp;
	</center>
	</form>
</body>
<?php

	}
?>