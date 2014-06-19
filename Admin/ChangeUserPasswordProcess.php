<?php

	require '../include/Session.inc';
	include "../DBInterface/UserInterface.php";
	include "../DBInterface/PasswordInterface.php";


	if( !isset( $_POST["username"])  || !isset( $_POST["psswrd1"]) || !isset( $_POST["psswrd2"])) 
	{
		header("Location: ChangeUserPassword.php");
		exit();	
	}

	if( $_POST["psswrd1"] !=  $_POST["psswrd2"] )
	{
		header("Location: ChangeUserPassword.php");
		exit();	
	}

	$user = $_POST["username"];
	$newpass = $_POST["psswrd1"];

	$oldGrp = CheckControl( $db_user, $user ) ; 

	if( !$oldGrp )
	{
		$errorStr = "You do not have permission to control user";
		include "NoPermission.php";
		exit();
	}

	if( !isset( $_POST["usertype"] ) )
	{
		$newGrp = $oldGrp;
	}
	else
	{
		$newGrp = $_POST["usertype"];
	}

	UpdateUserGrpAndPassword( $db_user, $db_pass, $user, $newGrp, $oldGrp, $newpass ); 

	if( $newGrp != $oldGrp )
	{
		$Title = "User Group Changed";
	}
	else
	{
		$Title = "User Password Changed";	
	}
	$currentPage = "Manager";
	include "../MasterViewHead.inc";
?>
	<TR>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
<?php
	if( $newGrp != $oldGrp )
	{
		echo "User Group was changed successfully.";
	}
	else
	{
		echo "Password was changed successfully.";	
	}
?>
	<BR>
	<BR>
	<button onclick="window.location='ManageUsers.php'">OK</button>

	</center>
<?PHP
	echo "</table></center>";
	include "../MasterViewTail.inc";
?>
