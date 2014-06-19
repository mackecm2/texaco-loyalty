	<?php

	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************

	require "../../include/DB.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";

	function ExtractMatchString( $Postcode )
	{
		$SpacePos = strpos( $Postcode, " " );
		if( $SpacePos )
		{
			if( substr( $Postcode, $SpacePos + 1, 1) == " " )
			{
				return substr( $Postcode, 0, $SpacePos + 1 ) . substr( $Postcode, $SpacePos + 2	, 1 );
			}
			else
			{
				return substr( $Postcode, 0, $SpacePos + 2 );
			}
		}
		else
		{
			return false;
		}
	}


	function ProcessLine( $line )
	{
		$AccountNo = $line[0];
		$OHomePost = $line[2];
		$SiteCode = $line[9];


		if( trim($SiteCode) != ""  )
		{
		$sql = "Select Postcode from sitedata where SiteCode = $SiteCode";

		$OSitePost = DBSingleStatQueryNoError( $sql );
	
		$HomePost = ExtractMatchString( $OHomePost );

		$SitePost = ExtractMatchString( $OSitePost );

		if( $SitePost and $HomePost )
		{
			$sql = "Select Miles from  postcodedata where Source = '$HomePost' and Target = '$SitePost'";
			$Miles = DBSingleStatQueryNoError( $sql );
			if( $Miles )
			{
				echo "$AccountNo,$Miles\n";
			}
			else
			{
				//echo "$OHomePost, $HomePost, $OSitePost, $SitePost";
			}
		}
		}
	}


//	echo ExtractMatchString( "CB1 3BZ" );
//	echo ExtractMatchString( "C1 3BZ" );
//	echo ExtractMatchString( "C1  3BZ" );

	// Main function

		global 	$ProcessName;
		global  $uname;
		global $lineNo;

//		$fileToProcess =  "c:\projects\sampledata\UKCustomerDetails.csv";
		$fileToProcess =  "/data/temp/UKCustomerDetails.csv";

		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>*****************************************************\n";
		$ProcessName = "CardRequest";
		$uname = $ProcessName;

			connectToDB(AnalysisServer, TexacoDB);

			echo "$fileToProcess\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.";
			} 
			else 
			{
				$line = fgetcsv( $fr, 1024 );
				$lineNo = 0;
				while( $line = fgetcsv( $fr, 1024 ))
				{
					$lineNo++;
					ProcessLine( $line );
				}
				fclose($fr);
			}
	//	echo "Finished</BODY></HTML>";
	?>