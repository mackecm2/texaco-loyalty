<?php 
/******************************************************************************
// Returns a csv file of a welcome pack batch
// 
//
****************************************************************************/

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../include/CSVFile.php";

	if( isset( $_GET["Type"] ))
	{
		$Type = $_GET["Type"];
	}
	else
	{
		$Type = "All";
	}

	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilenameDateOnly( $timestamp );
	}
	else
	{
//		$timestamp = GetSQLTime();
//		$friendly = GetBatchFilename( $timestamp );
//		$timestamp = CreateWelcomePackBatch( $timestamp, "WEL01" );
	}

	$fill = "WelcomeFile". $Type. $friendly .".csv";

	if( $Type == "CLOSE" )
	{
		$results = GetSiteCloseBatchData( $timestamp, $Type );
	}
	else
	{
		$results = GetWelcomePackBatchData( $timestamp, $Type );
	}
	$noRecords = mysql_num_rows( $results );

	OutputCSV( $fill, $results );

	//WebRegistrationsUpdateNo( $timestamp, $noRecords );

?>