	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Staff Incentive Scheme - Check for new sites  
	**   Written by MRM 30/09/2008
	********************************************************************/



	//******************************************************************
	//
	// /RegularProcessing/StaffIncentive/SitesCheck.php
	// Looks at the sitedata table
	// checks out each Site Code with a status of "Live"
	// looks for a corresponding record in the SiteRegistrations table
	// if there is no match, a record is created
	//
	//******************************************************************

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

	function createFileProcessRecord($sitecode)
	{
		$sql = "Insert into SiteRegistrations( SiteCode, StaffRegistrations, DateAdded ) values ( $sitecode, 0, now() )";
		$results = DBQueryExitOnFailure( $sql );
	}
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	$newcount = 0;
	$sql = "SELECT SiteCode FROM sitedata WHERE Status = 'Live' ";
	$results = DBQueryExitOnFailure( $sql );
	while( $row = mysql_fetch_array( $results ) )
	{
		$siteCode = $row['SiteCode'];
		$sql = "SELECT SiteCode FROM SiteRegistrations WHERE SiteCode = $siteCode";
		$site = DBSingleStatQueryNoError( $sql );
		if ($site==NULL OR $site=="" )
		{
			echo "creating site record for ".$siteCode."\r\n";
			createFileProcessRecord($siteCode);
			$newcount++;		
		}
	}
	echo date("Y-m-d H:i:s").' '.__FILE__." completed - $newcount new sites created \r\n";

?>