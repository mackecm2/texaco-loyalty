	<?php

	//******************************************************************
	//
	// TrackingFileProcess.php
	//
	//
	//
	//******************************************************************


	function TrackingProcessLine( $line )
	{


		$CardNo 	= $line[0];
		$TrackingCode 	= $line[1];
		$TrackingComment= $line[2];


		// See if card to link to field is set and if so if it exists in DB

		$sql = "Select Cards.MemberNo, AccountNo from Cards left Join Members using( MemberNo ) where CardNo = '$CardNo'";
		# echo "<br>sql is $sql";
		$results = DBQueryLogOnFailure( $sql );

		if( mysql_num_rows($results) == 0 )
		{
			LogError( "Card $CardNo not found" );
		}
		else
		{
			$row = mysql_fetch_assoc( $results );
			if( $row["MemberNo"] != "" )
			{
				$AccountNo = $row["AccountNo"];
				$MemberNo = $row["MemberNo"];
			}

		}


		// See if the tracking code exisits in the db

		$sql = "Select TrackingCode from TrackingCodes where TrackingCode = '$TrackingCode'";

		$results = DBQueryLogOnFailure( $sql );

		if( mysql_num_rows($results) == 0 )
		{
			LogError( "TrackingCode - $TrackingCode not found" );
			return;
		}
		else
		{

			$sql = "insert into Tracking set MemberNo = $MemberNo, AccountNo = $AccountNo, TrackingCode = $TrackingCode,Notes = '$TrackingComment',CreationDate = now(),CreatedBy='BatchLoad'";
			# echo "<br>sql is $sql";
			$results = DBQueryLogOnFailure( $sql );

		}

	}



	// Main function

	function TrackingProcessFile($file)
	{
		global 	$ProcessName;
		global  $uname;
		global $lineNo;
		global $fileToProcess;

		$fileToProcess = $file;

		$fileMove =  LocationFileProcessing. "Processed/BatchFile/";

		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR> Processing Tracking Load Data.\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>*****************************************************\n";

		$ProcessName = "GenericLoad";
		$uname = $ProcessName;

		$fr = fopen ("$fileToProcess","r");
		$lineNo = 0;

		if(!$fr)
		{
			echo "Error! Couldn't open the file.";
		}
		else
		{

			$fileRec = createFileProcessRecord($fileToProcess);

			while ($line = fgetcsv ($fr, 1000, ","))
			{

				$lineNo += 1;
				if( $fileRec )
				{
					# echo "<br>Processing Line $lineNo";
					TrackingProcessLine( $line );
				}


			}
			UpdateFileProcessRecord( $fileRec );
			fclose($fr);
			#rename( $fileToProcess, $fileMove . basename($fileToProcess) );
			echo "Finished</BODY></HTML>";
		}
	}




	?>