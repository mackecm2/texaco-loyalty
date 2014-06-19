<?php 

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../include/CSVFile.php";


	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilename( $timestamp );
	}
	else
	{
		$timestamp = CreateWebRegBatch();
		$friendly = GetBatchFilename( $timestamp );
	}

	$fill = "RequestFile". $friendly .".csv";

	$results = GetWebRequestBatchData( $timestamp );
	
	$noRecords = mysql_num_rows( $results );

	OutputCSV( $fill, $results );

	WebRegistrationsUpdateNo( $timestamp, $noRecords );

?>