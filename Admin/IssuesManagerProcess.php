<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";

	$a = $_POST["Issues"];

	$c = 1;


	foreach( $a as $value )
	{
		$sql = "Update Issues set Priority = $c where IssueNo = $value"; 
		$c++;
		DBQueryExitOnFailure( $sql );
	}

	header("Location: IssueManager.php");
?>
