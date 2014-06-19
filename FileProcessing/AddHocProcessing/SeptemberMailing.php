<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";
	//$db_pass = "";


	require "../../include/Locations.php";
	require "../General/misc.php";
	require "../../DBInterface/FileProcessRecord.php";


	$filePath =  LocationFileProcessing;
	//$filePath =  "C:/Projects/SampleData/";

	$fileMove =  LocationFileProcessing."Processed/Compower/";
	$filePattern = "September Statement Mailing Lapsed_Dormant.txt";


	function processHeaderLine( $fields )
	{
		global $cardNumberCol;
		$cardNumberCol = 0;

		return true;
	}

	function processCard( $fields )
	{
		global $gStatsData;
		global $fileToProcess, $ProcessName;
		global $cardNumberCol, $StartDateCol, $EndDateCol;


		$cardNumber = $fields[$cardNumberCol];

		$sql = "Select Members.AccountNo, Cards.MemberNo from Cards left join Members Using(MemberNo) left join Accounts Using(AccountNo) where Cards.CardNo = '$cardNumber'"; 

		$resultscard = mysql_query( $sql ) or die( mysql_error() . $sql );

		if( mysql_num_rows( $resultscard ) > 0 )
		{
			$cardData = mysql_fetch_assoc( $resultscard );
			if( !isset( $cardData["MemberNo"] ) or $cardData["MemberNo"] == ""  or $cardData["MemberNo"] == 0 )
			{
				logError( "Member not found $cardNumber" );
				return;
			}
			else
			{
				$MemberNo = $cardData["MemberNo"];
	//			echo " MemberNo $MemberNo "; 
	
				$sql = "Insert into CampaignHistory ( MemberNo, AccountNo, CampaignType, CampaignCode, ListCode, CreationDate, CreatedBy ) values ( $MemberNo, $cardData[AccountNo], 'STATEMENT', 'SEPT04', 'LAPSED', '2004-09-01', 'Statement' )";

				$results = mysql_query( $sql ) or die( mysql_error() . $sql );

				if( !$results )
				{	
					echo mysql_error() . $sql;
				}
			}
		}
		else					
		{
			logError( "Failed to find card $cardNumber" );
		}

	}





	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

//	echo "$filePath$filePattern\n";
	echo "Version 1.7\n";
	connectToDB();

	$fileList = glob( $filePath . $filePattern );
	if( !$fileList )
	{
		echo "No files to process\r\n";
	}
	else
	{
		foreach ( $fileList as $fileToProcess )
		{

			echo "$fileToProcess\r\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.\r\n";
			} 
			else 
			{
				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					// $fr now can be used to represent the opened file
					$line = fgetcsv( $fr, 2048);
					if( processHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgetcsv( $fr, 2048) )
						{
							$lineNo++;
							//echo $line;
							$success = processCard( $line );
							if( $lineNo % 1000 == 0 )
							{
								echo "$lineNo\n";
							}
						}
					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			}
		}
	}
?>