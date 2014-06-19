<?php  
	include "../include/Session.inc";
	include "../DBInterface/LettersInterface.php";

	if( isset( $_GET["Timestamp"] ) )
	{
		$TimeStamp = $_GET["Timestamp"];
		ConfirmLetterBatch( $TimeStamp );
	}

	header("Location:MailMerge.php");
?>