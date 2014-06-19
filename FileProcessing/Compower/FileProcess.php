	<?php
error_reporting( E_ALL );



	/********************************************************************
	**
	**  Processing File
	**	 GRANT insert on texaco.NewErrorLog to CompowerProcess@localhost
	********************************************************************/
	


	//******************************************************************
	//
	// /FileProcessing/Compower/FileProcess.php
	// Reads in a file from compower and inserts the data into the database
	//
	// Requires the code in WriteAlgo.php to have been run first which
	// creates the file Calculate.inc.  This contains the code to calculate
	// bonus points from rules in the database.
	//
	//  	MRM 07/05/2008 - Spelling mistakes corrected
	//  	MRM 29/05/2008 - Messages made more meaningful
	//      MRM 16/10/2008 - More message changes
	//
	//******************************************************************

	$IIN = "707655";
	$db_host = "weoudb";
	$db_name = "texaco";
	$db_user = "CompowerProcess";
	$db_pass = "ComPassword";

	require "../../include/Locations.php";
	require "../../include/DB.inc";
	require "../General/classes.php";
	require "../General/BonusFuncs.php";
	require "../General/ProductAlocation.php";
	require "../General/DatabaseUpdate.php";
	require "../../DBInterface/FileProcessRecord.php";
	require "../../DBInterface/ExposureInterface.php";
	require "../../DBInterface/CardInterface.php";
	require "../General/Calculate.php";

	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 06/05/2008
	//*  echo date("Y-m-d H:i:s").' '.$_SERVER['PHP_SELF']." started \r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";


	global $fileToProcess, $lineNo, $errorCount, $ProcessName;

	$filePath =  LocationCompowerDirectory;
	$fileMove =  LocationFileProcessing."Processed/Compower/";
	$filePattern = "GCC*.DAT";
	$fileToProcess = "";
	$ProcessName   = "COMPOWER";

	function processHeaderLine( $line )
	{
		global $records, $totalValue, $fileDate, $expectedLen;
		$len = strlen( $line );
		if( $len < 54 or $len > 56  )
		{
			echo "\r\n";
			echo "************************************************************\r\n";
			echo "***POSSIBLE ERROR !! ***  Header Line Format Issue\r\n";
			echo "************************************************************\r\n";
			echo "\r\n";
			LogError( "Header line format issue - line length not in range.  Received Length = " .  strlen( $line ) ) ;
			
			// email to Atos ... .added by MRM 06/05/08 - should make an included function after testing 
			require("../../mailsender/class.phpmailer.php");
			$mail = new phpmailer();
			$mail->FromName	= 'RSM 2000 Ltd'; // text for "From" shown to recipient e.g. RSM Admin
			$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
			$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
			$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail

			$mail->AddAddress('Richard.Bundonis@atosorigin.com', 'Richard Bundonis'); // mail recipient address and name, repeat for each recipent

			$mail->Subject = 'Corrupted ATOS Transactions File - Please re-send'; // set mail subject

			$mail->WordWrap = 70; // set word wrap
			$mail->IsHTML(true); // set mail as html

			// HTML Message Body
			$mail->Body =
			'<font size=2>Richard<p>The ATOS Transaction File '. $fileToProcess . '  cannot be read by our Star Rewards Batch process. Could you '."\n" 
			.'please re-send the file at your earliest convenience.</p></font>'."\n"
			.'<p><font size=2><font face=Verdana></p>Regards<BR><BR></font><font face=Verdana><font size=4><font face="Vladimir Script" '."\n"
			.'color=#0000ff size=6>Mike MacKechnie</font><BR></font>Web Developer<BR>RSM2000 Ltd<BR><BR>'."\n"
			.'T. +44 (0)1525 862555<BR></font></font><font size=2><font face=Verdana>F. +44 (0)1525 862500<BR><BR>'."\n"
			.'</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n"
			.'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n"
			.'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n"
			.'error please notify the sender by email and delete this message and any '."\n"
			.'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n"
			.'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n"
			.'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n"
			.'Registration Number: 3703548</EM></font></font></p>';

			// Text Message Body
			$mail->AltBody = "Richard: ".$fileToProcess."\n\n"
				." cannot be read by our Star Rewards Batch process. Could you please re-send the file at your earliest convenience";
			// send the email and check on its success
			if (!$mail->Send()) {
			$dbMailSent = "Mail Send to Atos - fail";
			} else {
			$dbMailSent = "Mail Send to Atos - pass";
			}
			return false;
		}
		else
		{
			$expectedLen = $len;

			$records = substr( $line, 0, 7 );
			$totalValue = substr( $line, 7, 9 );
			$fileDate = substr( $line, 16, 8 );
			echo "Header Data\n";
			echo " Records = $records \n";
			echo " Value   = $totalValue \n";
			echo " Date    = $fileDate \n";
			// Don't bother checking the fillers are all zero.
			return true;
		}
	}

	function ConvertToTable( $transDate )
	{
		global $LastMonth;
		$Month = substr( $transDate, 0 , 4 ) . substr( $transDate, 5, 2 );
		return $Month;
	}

	function ConvertDateToMysql( $transDate )
	{
		return "20". substr( $transDate, 0 , 2 ). '-'. substr( $transDate, 3, 2 ). '-'.  substr( $transDate, 6, 2 );
	}

	function TransactionInsert( $gUserData, $gSiteData, $fileNameOnly, $ProcessName )
	{
		global $gStatsData, $gTransactionData;
		$results = InsertTransaction( "Compower", $fileNameOnly );

	// Store for latter use
		if( $results )
		{
			$gTransactionData->starValueCurrency = $gTransactionData->transValue; 
			$gStatsData->valueProcessed += $gTransactionData->transValue;
			$gStatsData->transactionsProcessed++;
			return true;
		}
		else
		{
			return false;
		}
	}

	function processTransaction( $line )
	{
		global $gStatsData, $expectedLen;
		global $fileToProcess, $ProcessName, $IIN;
		global $gTransactionData, $gUserData, $gSiteData;

		if( strlen( $line ) != $expectedLen )
		{
			LogError( "Transaction Record format issue - line not $expectedLen characters.  Received Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			// Reset the transaction information

			$gTransactionData->transactionNo = 0;
			$gTransactionData->productCount = 0;
			$gTransactionData->starValueCurrency = 0;
			$gTransactionData->bonusPoints = 0;
			$gTransactionData->bonusSeq = 0;

			$cardNumber = $IIN .$custNumber = substr( $line , 0 , 13 );
			$siteCode   = substr( $line , 13, 6  );
			$gTransactionData->transDate  = ConvertDateToMysql(substr( $line , 19, 8 ));
			$compInd					 = substr( $line , 27, 1 );
			$gTransactionData->transTime	 = substr( $line , 28, 5 );
			$gTransactionData->transValue = substr( $line , 33, 7 );
			$filler						 = substr( $line , 40, 3 );
			$gTransactionData->EFTTransNo = substr( $line , 43, 6 );
			$gTransactionData->cardCode	= substr( $line , 49, 3 );
			$gTransactionData->flag		= substr( $line , 52, 1 );
			$gTransactionData->PANKey	= substr( $line , 53, 1 );


	//		echo "Transaction Data \n";
	//		echo " cardNumber = $cardNumber\n";
	//		echo " mercNumber = $siteCode\n";
	//		echo " transDate  = $gTransactionData->transDate  \n";
	//		echo " compInd    = $compInd	\n";
	//		echo " transTime  = $gTransactionData->transTime	\n";
	//		echo " transValue = $gTransactionData->transValue \n";
	//		echo " filler     = $filler		\n";
	//		echo " EFTTransNo = $gTransactionData->EFTTransNo \n";
	//		echo " CardCode   = $gTransactionData->cardCode	\n";
	//		echo " Flag       = $gTransactionData->flag			\n";
	//		echo " PANKey     = $gTransactionData->PANKey		\n";

			$gTransactionData->Month = ConvertToTable( $gTransactionData->transDate );
			// Check if the transaction already exists
			if( checkForDuplicate( $cardNumber, $siteCode, "COMPOWER") )
			{
				// Check SiteNo

				CheckSiteNumber($siteCode, "SiteCode");

				if( checkCardNumber( $cardNumber, true ) )
				{
					$fileNameOnly = basename($fileToProcess);

					// need to calculate points but this needs the products details

					TransactionInsert( $gUserData, $gSiteData, $fileNameOnly, $ProcessName );

					return true;
				}
			}
		}
		return false;
	}


	function processProduct( $line )
	{
		global $gStatsData, $gProductData;
		global $gTransactionData, $gUserData, $expectedLen;
		if( strlen( $line ) != $expectedLen  )
		{
			LogError( "Product Record format issue - line not $expectedLen.  Received Length = " .  strlen( $line )) ;
			return false;
		}
		else
		{
			$seqNum				  = substr( $line, 1, 2 );
			$gProductData->code   = substr( $line, 3, 2 );
			$gProductData->value  = substr( $line, 5, 7 );
			$Space				  = substr( $line, 12, 1 );
			$gProductData->volume = substr( $line, 13, 7 );
			$Filler				  = substr( $line, 20, 23 );
			$EFTransNo			  = substr( $line, 43, 6 );

			$gStatsData->productsProcessed++;
			$gTransactionData->productCount++;
			if( $gTransactionData->productCount != $seqNum )
			{
				$gStatsData->warnings++;
				LogError( "Product sequence number out of sequence\r\n" );
				return false;
			}

			$PointsAwarded = CalculateProductVolumeBonus( );
			$PointsAwarded += CalculateProductValueBonus( );
			ProductAllocate(  );

			ProductInsertPurchase( $gTransactionData, $gProductData );
			return true;
		}
	}




	// Main function
	//  We use globals for all the data because of the split in the code to 
	// Auto generated code

//	echo "$filePath$filePattern\n";
	connectToDB( MasterServer, TexacoDB );

	GetThisMonth();

	global $LastMonth;
	global $FirstMonth;

	$LastMonth = date( "Ym" );
	$FirstMonth = "200401";

	$fileList = glob( $filePath . $filePattern );
	if( !$fileList )
	{
		echo "\r\n";
		echo "************************************************************\r\n";
		echo "***POSSIBLE ERROR !! ***  No Compower Files found to process\r\n";
		echo "************************************************************\r\n";
		echo "\r\n";
		exit();
	}
	else
	{
		foreach ( $fileList as $fileToProcess )
		{
			$gTransactionData = new TransactionClass();
			$gUserData = new UserDataClass();
			$gProductData = new ProductClass();
			$gDeptmentData = new DeptClass();
			$gSiteData = new SiteClass();
			$gStatsData = new StatsClass();
			$errorCount = 0;

			echo "$fileToProcess\r\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.\r\n";
			} 
			else 
			{
				//Next bit added MRM 30/5/08 for Mantis 457
				$goodSiteNumbers = true;
				while(! feof($fr))
				{
					$line = fgets($fr);
					if( substr( $line, 0, 2 ) == "02" )
					{
						$siteCode   = substr( $line , 13, 6  );
						if(CheckGoodSiteNumber( $siteCode ) == false)
						{
							$goodSiteNumbers = false;
						}
					}
				}
				if ($goodSiteNumbers)
				{
					fseek($fr, 0);
					echo "All Site Codes good.\r\n";
					// .................................................................................end of MRM's bit
					$line = fgets( $fr );
					if( processHeaderLine( $line ) )
					{
						echo "Creating Exposure point\r\n";
						$InitialBalance = CreateExposurePoint( "Prior to compower file load" .basename($fileToProcess) );

						$fileRec = createFileProcessRecord($fileToProcess);
						if( $fileRec )
						{
							// $fr now can be used to represent the opened file
							$success = false;
							$lineNo = 1;

							while( $line = fgets( $fr ) )
							{
								$lineNo++;
								//echo $line;
								if( substr( $line, 0, 1 ) == "P" )
								{
									if( $success )
									{
										$success = processProduct( $line );
									}
								}
								else
								{
									if( $success )
									{
										UpdatePoints( false  );
									}
									//		echo "l";
									$success = processTransaction( $line );
								}
								if( $lineNo % 1000 == 0 )
								{
									$t = $gStatsData->productsProcessed + $gStatsData->transactionsProcessed;
									echo "After $lineNo lines, Product Lines Processed = $gStatsData->productsProcessed, ";
									echo "Transactions Processed $gStatsData->transactionsProcessed"."\r\n";
								}
								//							if( $lineNo != $gStatsData->productsProcessed + $gStatsData->transactionsProcessed + 1 )
								//							{
								//								echo "$lineNo, $line, $gStatsData->productsProcessed, $gStatsData->transactionsProcessed\n";
								//							}
								//							if( $lineNo > 1000 )
								//							{
								//								break;
								//							}
							}
							if( $success )
							{
								UpdatePoints(  false );
							}
							echo "Summary data\r\n";
							echo " productsProcessed = $gStatsData->productsProcessed"."\r\n";
							echo " transactionsProcessed = $gStatsData->transactionsProcessed"."\r\n";
							echo " valueProcessed = $gStatsData->valueProcessed "."\r\n";
							echo " Records = $records "."\r\n";
							echo " Value   = $totalValue "."\r\n";
							echo " Lines   = $lineNo"."\r\n";
						}
						echo "Creating Exposure point\r\n";
						$FinalBalance = CreateExposurePoint( 'After compower file load' .basename($fileToProcess) );

						$Movement = $FinalBalance - $InitialBalance;

						echo "The effect on the balance was $Movement\r\n";
						UpdateRecordsProcessed( $fileRec, $gStatsData );
						//	    function UpdateRecordsProcessed( $recNum, $Successfull, $Duplicates, $Bad, $Warnings )
						fclose($fr);
						rename( $fileToProcess, $fileMove . basename($fileToProcess) );
					}
					else
					{
						fclose($fr);
					}
				}
				else
				{
					echo " Bad Site Codes found; File ". $fileToProcess . " not processed.\n";
				}
			}
		}
	}
		//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 06/05/2008
	//* echo date("Y-m-d H:i:s").' '.$_SERVER['PHP_SELF']." completed \r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
	
	?>