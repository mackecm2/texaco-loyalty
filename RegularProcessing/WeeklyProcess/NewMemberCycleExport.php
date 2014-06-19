<?php
#   GRANT USAGE ON `texaco`.* TO 'MemberCycle'@'localhost';
#	GRANT USAGE ON *.* TO 'MemberCycle'@'localhost';
#	set PASSWORD for MemberCycle@localhost = OLD_PASSWORD( 'MemberCycle' );
#	GRANT SELECT on texaco.Members to MemberCycle@localhost;
#	GRANT SELECT, UPDATE on texaco.CampaignHistory to 'CompowerProcess'@'localhost';
#
	require "../../include/DB.inc";
	require "../../include/Locations.php";
	require "../../DBInterface/GeneralInterface.php";
	require "../../DBInterface/WelcomePackInterface.php";

	#$db_user = "root";
	#$db_pass = "trave1";
	
	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";	

	connectToDB( MasterServer, TexacoDB );

	// Create phase two file

	$sql = "select date_add( date_sub( curdate(), interval DayOfWeek( curdate()) day), interval 4 day)";

	$ThisWednessday = DBSingleStatQuery( $sql );

	$StartDate = DBSingleStatQuery("select Date_Sub( '$ThisWednessday', interval 20 day )" );
	$EndDate   = DBSingleStatQuery("select Date_Sub( '$ThisWednessday', interval 13 day )" );

 	$sql = "Select CreationDate from CampaignHistory where CampaignType = 'WELCOME' and ListCode in (1, 5, 7) and CreationDate between '$StartDate' and '$EndDate' group by CreationDate";

	echo "Extract Phase 1 for '$StartDate' to '$EndDate'\n";


	$OldType = "Email";
	$oldlistcode = 1;
	$newlistcode = 5;

	$NewTime = date( "Y-m-d H:i:s" );

	$results = DBQueryExitOnFailure( $sql );

	$num_rows = mysql_num_rows($results);
	echo "Number of Rows - $num_rows\n";

//	$friendly = LocationNMCEmailFiles."WelcomeFileEmailPhase2_".GetBatchFilename( $NewTime );
	$friendly = LocationNMCEmailFiles."nmc_week1.csv";

	$out = fopen( $friendly, "w" );

	fwrite( $out, "email,namedattr.primary_card,namedattr.title,firstname,lastname\n" );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$timestamp = $row["CreationDate"];
		echo "$timestamp\n";

		CopyNMCContactBack( $timestamp , $OldType, $NewTime, $oldlistcode, $newlistcode  );

		$results2 = GetWelcomePackEmailBatchData( $timestamp, "EmailPhase2" );
		OutputCSVFile( $out, $results2 );
	}


	// Create Phase 2 file

	$StartDate = DBSingleStatQuery("select Date_Sub( '$ThisWednessday', interval 48 day )" );
	$EndDate   = DBSingleStatQuery("select Date_Sub( '$ThisWednessday', interval 41 day )" );

 	$sql = "Select CreationDate from CampaignHistory where CampaignType = 'WELCOME' and  ListCode in (1, 5, 7) and CreationDate between '$StartDate' and '$EndDate' group by CreationDate";

	echo "Extract Phase 2 for '$StartDate' to '$EndDate'\n";


	$OldType = "EmailPhase2";
	$oldlistcode = 5;
	$newlistcode = 7;

//	$friendly = LocationNMCEmailFiles."WelcomeFileEmailPhase3_".GetBatchFilename( $NewTime );
 	$friendly = LocationNMCEmailFiles."nmc_week2.csv";

	$out = fopen( $friendly, "w" );

	fwrite( $out, "email,namedattr.primary_card,namedattr.title,firstname,lastname\n" );

	$results = DBQueryExitOnFailure( $sql );
	$num_rows = mysql_num_rows($results);
	echo "Number of Rows - $num_rows\n";

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$timestamp = $row["CreationDate"];

		CopyNMCContactBack( $timestamp , $OldType, $NewTime, $oldlistcode, $newlistcode  );

		$results2 = GetWelcomePackEmailBatchData( $timestamp, "EmailPhase3" );
		OutputCSVFile( $out, $results2 );
	}

	function OutputCSVFile( $file,  $results )
	{
		$delimiter = ",";
		while( $row = mysql_fetch_assoc( $results )	 )
		{
			$d = '';
			$output = "";
			foreach( $row as $fieldname => $value )
			{
				$value = str_replace('"', '', $value);
 				if (strpos($value, $delimiter))
				{
					$output .= $d.'"'.$value.'"';
				}
				else
				{
					$output .= $d.$value;
				}
				$d = $delimiter;
			}
			$output .= "\n";
			fwrite( $file, 	$output );

		}
	}



?>