<?php 
	#	This script can take a while to process
	set_time_limit(0);

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../include/CSVFile.php";

	//echo var_dump($_POST);
	
	$timestamp = GetSQLTime();
	$friendly = GetBatchFilename( $timestamp );

    if( isset( $_POST["SpecialCriteria"] ) )
	{
		$SpecialCriteria = $_POST["SpecialCriteria"];
	}
	else
	{
		$SpecialCriteria = "";
	}
	
	if( isset( $_POST["NMC"] ) )
	{
		CreateWelcomePackBatch( $timestamp, "NMC01", $SpecialCriteria );
		CreateWelcomePackLists( $timestamp );
	}
	else
	{
		CreateWelcomePackBatch( $timestamp, $SpecialCriteria, $SpecialCriteria );
	}

	if( isset( $_POST["Date1"] ) and isset( $_POST["Promo1"] ) and $_POST["Date1"] != "" && $_POST["Promo1"] != "")
	{
		CopyBatchToPersonalCampaign( $timestamp, $_POST["StartDate"], $_POST["Date1"], $_POST["Promo1"], "WELCOME" );
	}
	else 
	{
		CopyBatchToPersonalCampaign( $timestamp, $timestamp, $timestamp, "WELCOME25", "WELCOME" );
		
	}
		
	if( isset( $_POST["Date2"] ) and isset( $_POST["Promo2"] ) and $_POST["Date2"] != "" && $_POST["Promo2"] != "") 
	{
		CopyBatchToPersonalCampaign( $timestamp, $_POST["Date1"], $_POST["Date2"], $_POST["Promo2"], "WELCOME" );
		if( isset( $_POST["Date3"] ) and isset( $_POST["Promo3"] )  and $_POST["Date3"] != "" && $_POST["Promo3"] != "") 
		{
			CopyBatchToPersonalCampaign( $timestamp, $_POST["Date2"], $_POST["Date3"], $_POST["Promo3"], "WELCOME" );
		}
	}



//	$fill = "WelcomeFile". $friendly .".csv";

//	$results = GetWelcomePackBatchData( $timestamp );
	
//	$noRecords = mysql_num_rows( $results );

//	OutputCSV( $fill, $results );

	//WebRegistrationsUpdateNo( $timestamp, $noRecords );

	header("Location: WelcomePacksManager.php" );
	

?>