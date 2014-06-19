<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/UserInterface.php";

	if( isset( $_GET["UserName"] ) )
	{
		DeleteUser( $db_user, $_GET["UserName"] );
		header("Location: ManageUsers.php");
	}
?>