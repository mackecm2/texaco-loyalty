<?php 

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../include/CSVFile.php";

	if( isset( $_GET["Type"] ))
	{
		$Type = $_GET["Type"];
	}

	switch( $Type )
	{
		case "EmailPhase2":
			$OldType = "Email";
			$oldlistcode = 1;
			$newlistcode = 5;
		break;
		case "MailPhase2":
			$OldType = "Mail";
			$oldlistcode = 1;
			$newlistcode = 6;
		break;
		case "EmailPhase3":
			$OldType = "EmailPhase2";
			$oldlistcode = 5;
			$newlistcode = 7;
		break;
		case "MailPhase3":
			$OldType = "MailPhase2";
			$oldlistcode = 6;
			$newlistcode = 8;
		break;
	}

	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilename( $timestamp );
	}
	else
	{
		exit();
	}

	$NewTime = date( "%Y-%m-%d %H:%i:%s" );

	CopyNMCContactBack( $timestamp, $OldType, $NewTime, $oldlistcode, $newlistcode  );

	$fill = "WelcomeFile". $Type. $friendly .".csv";

	$results = GetWelcomePackBatchData( $timestamp, $Type );
	
	OutputCSV( $fill, $results );

	//WebRegistrationsUpdateNo( $timestamp, $noRecords );

?>