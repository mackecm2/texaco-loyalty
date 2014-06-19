<?php

	require '../include/Session.inc';
	include "../include/CacheCntl.inc";
	include "../DBInterface/PasswordInterface.php";

	$showForm = false;
	$msg  = "";
	if( !isset( $_POST["oldpass"])  || !isset( $_POST["psswrd1"]) || !isset( $_POST["psswrd2"])) 
	{
		$showForm = true;
	}
	else if( $_POST["psswrd1"] !=  $_POST["psswrd2"] )
	{
		$showForm = true;
	}

	if( !$showForm )
	{
		$oldword = $_POST["oldpass"];
		$newword = $_POST["psswrd1"];

		$res = ChangePassword( $uname, $oldword, $newword );
		if( $res != 0 )
		{
			$showForm = true;
			if( $res == -1 )
			{
				$msg = "Old password does not match that in the database";
			}
			else if( $res == -2 )
			{
				$msg = "You can't use a password you have already used";
			}
		}
	}

	if( $showForm ) 
	{
		$daysLeft = PasswordDaysLeft( $uname, $db_user );
		if( $daysLeft <= 0 )
		{
			$bodyControl = "onload=\"StartBlinker()\" onbeforeunload=\"ForceChange()\"";
		}
		$Title = "Change Password";
		$currentPage = "Change Password";
		include "../MasterViewHead.inc";

?>
<script>
<?php
	if( $daysLeft <= 0 )
	{
		echo "var Force = true;";
	}
	else
	{
		echo "var Force = false;";
	}
	?>

	function ForceChange()
	{
		if( Force )
		{
			event.returnValue = "You must change your password NOW";
		}
	}

	function FormVerify()
	{
		if( document.getElementById( "psswrd1").value.length < 6 )
		{
			alert( "Password is too short" );
			return false;
		}

		if( (document.getElementById( "psswrd1").value != document.getElementById( "psswrd2").value ) )
		{
			alert( "Passwords do not match" );
			return false;
		}
		Force = false;
		return true;
	}


	function StartBlinker()
	{
		if( document.getElementById( "Blinking" ) )
		{
			timeID = window.setTimeout( OnTime, 1000 );
			displayMode = 1;
		}
	}

	function OnTime()
	{
		if( displayMode == 1 )
		{
			//document.getElementById( "Blinking" ).style.display = "none";
			document.getElementById( "Blinking" ).style.color = 'red';
			displayMode = 0;
		}
		else
		{
			//document.getElementById( "Blinking" ).style.display = "";
			document.getElementById( "Blinking" ).style.color = 'lavender';
			displayMode = 1;
		}
		timeID = window.setTimeout( OnTime, 1000 );
	}

</script>

	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

<form action="ChangePassword.php" method="post" onsubmit="return FormVerify();">


	<center>
	 
<?php 
	if( $daysLeft > 3 ) 
	{
		echo "You have $daysLeft days left with this password.\n";
	}
	else if( $daysLeft > 0  )
	{
		echo "This password is about to expire please change it.\n";
	}
	else
	{
		echo "<span id=Blinking style=\"color:red\" >This password has expired<br><br>You must change it as you will be unable to log in again.</span><br>";
	//	echo "<marquee loop=infinite width=33% scrollamount=15 style=\"color:red; font-size:large\">This password has expired</marquee>\n";
	//	echo "<br><marquee loop=infinite width=33% style=\"color:red; font-size:large\">You must change it as you will be unable to log in again.</marquee>\n";
	}
?> 
	<br><?php echo $msg; ?>
	<table>
	<tr><td> Old Password</td><td><input type="password" name="oldpass" id="oldpass"></td></tr>
	<tr><td> New Password</td><td><input type="password" name="psswrd1" id="psswrd1"></td></tr>
	<tr><td> Repeat</td><td><input type="password" name="psswrd2" id="psswrd2"></td></tr>
	<tr><td></td><td><input type="submit" value="Enter"></td></tr>
	</table>
</form>
	</tr>
<?php
	}
	else
	{

	$Title = "Password Changed";
	$currentPage = "Change Password";
	include "../MasterViewHead.inc";
?>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	<center>
	Password was changed succesfully.
	</center>
	</tr>
<?php
	}
	include "../MasterViewTail.inc";

?>