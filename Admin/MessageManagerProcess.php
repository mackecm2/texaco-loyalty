<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/MessagesInterface.php";

	$a = $_POST["MessageNo"];

	$c = 0;

	$results = GetCurrentMessages();

	$oldEntries = array();

	while( $row = mysql_fetch_assoc($results) )
	{
		$oldEntries[$row["MessageNo"]] = $row["MessageNo"];
	}

	foreach( $a as $value )
	{
		SetMessagePriority( $value, $c );
		unset( $oldEntries[$value] );
		$c++;
	}

	foreach( $oldEntries as $value )
	{
		DeletePromotionCode( $value );
	}

	header("Location: MessagesManager.php");


?>
