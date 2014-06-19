<?php 

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../include/CSVFile.php";


	$timestamp = GetSQLTime();
	$friendly = GetBatchFilename( $timestamp );

    if( isset( $_POST["SiteNo"] ) )
	{
		$SiteNo = $_POST["SiteNo"];
	}
	else
	{
		header("Location: SiteClosureManager.php" );
	}
	
	CreateSiteClosureBatch( $timestamp, $SiteNo );

	CopyBatchToPersonalCampaign( $timestamp, $_POST["StartDate"], $_POST["Date1"], $_POST["Promo1"], "SITECLOSE" );

	if( isset( $_POST["Date2"] ) and isset( $_POST["Promo2"] ) and $_POST["Date2"] != "" && $_POST["Promo2"] != "") 
	{
		CopyBatchToPersonalCampaign( $timestamp, $_POST["Date1"], $_POST["Date2"], $_POST["Promo2"], "SITECLOSE" );
		if( isset( $_POST["Date3"] ) and isset( $_POST["Promo3"] )  and $_POST["Date3"] != "" && $_POST["Promo3"] != "") 
		{
			CopyBatchToPersonalCampaign( $timestamp, $_POST["Date2"], $_POST["Date3"], $_POST["Promo3"], "SITECLOSE" );
		}
	}

	header("Location: SiteClosureManager.php" );
?>