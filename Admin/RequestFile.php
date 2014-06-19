<?php

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardRequestInterface.php";
	include "../include/CSVFile.php";
	
	if( isset( $_GET["Group"] ))
	{
		$group = $_GET["Group"];
	}

	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilenameDateOnly( $timestamp );
	}
	else
	{
		$timestamp = GetSQLTime();
		$friendly = GetBatchFilenameDateOnly( $timestamp );

		MakeUpBatch( $timestamp, $group );
	}

	$fill = "RequestFile". $friendly .".csv";

	$results = GetBatchData( $timestamp );

	OutputCSV( $fill, $results );

?>