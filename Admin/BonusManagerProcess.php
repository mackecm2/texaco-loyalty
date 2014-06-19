<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/BonusInterface.php";

	$a = $_POST["Promos"];

	$c = 0;

	$results = GetAllBonuses();

	$oldEntries = array();

	while( $row = mysql_fetch_assoc($results) )
	{
		$oldEntries[$row["PromotionCode"]] = $row["PromotionCode"];
	}

	foreach( $a as $value )
	{
		SetPromotionPriority( $value, $c );
		unset( $oldEntries[$value] );
		$c++;
	}
	
	foreach( $oldEntries as $value )
	{
		DeletePromotionCode( $value );
	}

	header("Location: BonusManagerAll.php");


?>
