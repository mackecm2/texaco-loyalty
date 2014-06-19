	<?php

/*
Create Table DawleysStats
(
  CardNo char(20),
  OKEmail char(1),
  OptOutTex char(1),
  TOKMail char(1),
  Source  char(8),
  StatementPref char(1),
  Prefs char(3),					
  PartOptin char(1)
);
*/ 

 include "../../include/DB.inc";
 include "../../include/Locations.php";
 include "../../DBInterface/TrackingInterface.php";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
//	$db_pass = "";																		   
	//******************************************************************
	//
	// CardRequestFileProcess.php
	//
	//  
	//
	//******************************************************************



	class Field
	{
		var $name;
		var $number;
		var $size;
		var $offset;
		var $memberCol;

		function Field( $lname, $ltype, $lsize, $lCol )
		{
			global $fieldOffset;

			$this->name = $lname;
			$this->size = $lsize;
			$this->offset = $fieldOffset;
			$this->memberCol = $lCol;
			$fieldOffset += $lsize;
		}
	}

	function BatchInitArray()
	{
		global $fieldArray,  $fieldOffset;
		$fieldCounter = 0;
		$fieldOffset  = 0;

		$fieldArray = array();

		$fieldArray[$fieldCounter++] = new Field( "BatchNo",	"text", 4, "" );
		$fieldArray[$fieldCounter++] = new Field( "Date",		"date", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "CardNo",		"text", 19, "CardNo" );
		$fieldArray[$fieldCounter++] = new Field( "Title",		"text", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "Forename",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Surname",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "DOB",		"date", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address1",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address2",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address3",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address4",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address5",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Postcode",	"text", 8,  "" );
		$fieldArray[$fieldCounter++] = new Field( "Email",		"text", 80, "" );
		$fieldArray[$fieldCounter++] = new Field( "EmailOpt",	"Y/N",  1,  "OKEmail" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone",	"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "MobilePhone",	"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "ExistingCard",	"text", 19, "" );
		$fieldArray[$fieldCounter++] = new Field( "Profession",		"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Children",		"text", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "Income",			"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTex",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillShell",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillBP",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillEsso",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTesco",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillAsda",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillSains",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillOther",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillFree",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillFreq",		"Y/N", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "SpendShop",		"Y/N", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "Private",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Company",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Make",			"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewTex",			"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewMotor",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewTravel",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewSports",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewDining",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewFamily",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "RewShop",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptOutTex",		"Y/N", 1, "OptOutTex" );
		$fieldArray[$fieldCounter++] = new Field( "OptOutColt",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "HomePhone",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone2",		"text", 20, "" );
		$fieldArray[$fieldCounter++] = new Field( "CollectFor",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Mileage",		"number", 6, "" );
		$fieldArray[$fieldCounter++] = new Field( "Visits",			"number", 2 , "");
		$fieldArray[$fieldCounter++] = new Field( "Company_pay",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Cars",			"text", 1 , "");
		$fieldArray[$fieldCounter++] = new Field( "Fleet",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "ThirdParty",		"text", 1, "TOKMail" );
		$fieldArray[$fieldCounter++] = new Field( "PAF",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Colt",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Source",			"text", 8, "Source" );
		$fieldArray[$fieldCounter++] = new Field( "Statements",		"text", 1, "StatementPref" );
		$fieldArray[$fieldCounter++] = new Field( "SiteCode",		"number", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTotal",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OutOf10",		"text", 2, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWFuel",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWCarWash",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Prefs",			"text", 3, "Prefs" );
		$fieldArray[$fieldCounter++] = new Field( "AutoVoucher",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "PartnerOptin",	"text", 1, "PartOptin" );
	}

	function AddIfDifferent( $newVal, $field, $row )
	{
		global $Changes;
		if( $newVal != $row[$field] )
		{
			$Changes .= "$field $row[$field] => $newVal ";
			return "$field = '$newVal',";
		}
		else
		{
			return "";
		}
	}


	function CreateRecords( $fields )
	{
		global $fieldArray, $ProcessName;
		global $Changes, $Updated;

		$Changes = "";

		$sql = "Select AccountNo, Members.MemberNo, OKMail, OKSMS, OKEMail, TOKMail, StatementPref, Source, HomePhone, WorkPhone, Members.CreatedBy, Members.CreationDate, Members.RevisedBy, Members.RevisedDate from Cards join Members using(MemberNo) where CardNo = '$fields[CardNo]'";

		$results = DBQueryExitOnFailure( $sql );

		if( $row = mysql_fetch_assoc( $results ) )
		{
			$update = "";
			// SMS field
			
			if( strstr($fields["Prefs"], "S") and $fields["OptOutTex"] != "Y" )
			{
				$update.= AddIfDifferent( 'Y', 'OKSMS', $row); 		
			}
			else
			{
				$update.= AddIfDifferent( 'N', 'OKSMS', $row ); 		
			}

			// Mail Field
			if( $fields["OptOutTex"] != "Y" )
			{
				$update.= AddIfDifferent( 'Y', 'OKMail', $row ); 		
			}
			else
			{
				$update.= AddIfDifferent( 'N', 'OKMail', $row ); 		
			}

			// Email

			if( $fields["OptOutTex"] == "Y" )
			{
				$update.= AddIfDifferent( 'N', 'OKEMail', $row ); 		
			}
			else
			{

				if( $fields["Source"] == "TEXWEOU1" )
				{
					if( $fields["EmailOpt"] == 'Y' )
					{
						$update.= AddIfDifferent( 'N', 'OKEMail', $row ); 					
					}
					else
					{
						$update.= AddIfDifferent( 'Y', 'OKEMail', $row ); 					
					}
				}
				else
				{
					if( strstr($fields["Prefs"], "E") or $fields["Statements"] == "E" )
					{
						$update.= AddIfDifferent( 'Y', 'OKEMail', $row ); 					
					}
					else
					{
						$update.= AddIfDifferent( 'N', 'OKEMail', $row ); 					
					}
				}

			}


			// Third Party Mail

			if( $fields["Source"] == "TEX3" or $fields["Source"] == "TEX4" )
			{
				if( $fields["ThirdParty"] == 'Y' )
				{
					$update.= AddIfDifferent( 'N', 'TOKMail', $row ); 		
				}
				else
				{
					$update.= AddIfDifferent( 'Y', 'TOKMail', $row ); 		
				}
			}
			else
			{
				if( $fields["PartnerOptin"] == 'Y' )
				{
					$update.= AddIfDifferent( 'Y', 'TOKMail', $row ); 		
				}
				else
				{
					$update.= AddIfDifferent( 'N', 'TOKMail', $row ); 		
				}
			}


			// Statement Pref

			if( $fields["Statements"] != "" )
			{
				$update.= AddIfDifferent( $fields["Statements"], 'StatementPref', $row ); 				
			}
			else if( $fields["Prefs"] != "" )
			{
				if( strstr( $fields["Prefs"], "E" ) )
				{
					$update.= AddIfDifferent( 'E', 'StatementPref', $row );
				}
				else if( strstr( $fields["Prefs"], "S" ) )
				{
					$update.= AddIfDifferent( 'S', 'StatementPref', $row ); 				
				}
				else
				{
					$update.= AddIfDifferent( 'P', 'StatementPref', $row ); 
				}																		
			}
			else
			{
				if( $fields["OptOutTex"] != "Y" )
				{
					$update.= AddIfDifferent( 'P', 'StatementPref', $row ); 				
				}
				else
				{
					$update.= AddIfDifferent( 'N', 'StatementPref', $row ); 				
				}
			}

	 // Phone number processing

	//	WorkPhone, MobilePhone, HomePhone, WorkPhone2	
			
			if( $fields["HomePhone"] != "" )
			{
				$update.= AddIfDifferent( $fields["HomePhone"], 'HomePhone', $row ); 	
			}
			else if( $fields["MobilePhone"] != "" )
			{
				$update.= AddIfDifferent( $fields["MobilePhone"], 'HomePhone', $row ); 	
			}

			if( $fields["WorkPhone"] != "" )
			{
				$update.= AddIfDifferent( $fields["WorkPhone"], 'WorkPhone', $row ); 	
			}
			else if( $fields["WorkPhone2"] != "" )
			{
				$update.= AddIfDifferent( $fields["WorkPhone2"], 'WorkPhone', $row ); 	
			}

			if( $update != "" )
			{
				$Updated++;
				//$update = trim( $update, " ,");
				$sql = "Update Members set $update RevisedBy = '$ProcessName' where MemberNo = $row[MemberNo]";
				$results = DBQueryExitOnFailure( $sql );
				InsertTrackingRecord( TrackingContactChange,  $row["MemberNo"], $row["AccountNo"], $Changes, "" );
			}
		}
	}

  		function luhnCheck( $CardNumber ) 
		{
			$CardNumber = Trim( $CardNumber ); 
			if(!is_numeric($CardNumber)) 
			{
				return false;
			}
			//* MRM next bit inserted for Mantis 09/03/09
			if (substr($CardNumber, 0, 2) == '01')
			{
				return true;
			}
			else
			{
				$no_digit = strlen($CardNumber);
				$oddoeven = (($no_digit % 2) == 1);
				$sum = 0;
	
				for ( $count = 0; $count < $no_digit; $count++) 
				{
					$digit = intval(substr( $CardNumber, $count, 1 ));
					if (!(($count % 2) == 1 xor $oddoeven)) 
					{
						$digit *= 2;
						if ($digit > 9)
						{
							// very cleverly adds in one as well
							$digit -= 9;
						}
					}
					$sum += $digit;
				}
				return ($sum % 10 == 0);
			}
		}

	function BatchProcessLine( $line )
	{
		global $fieldArray;
		$FieldLookup = array();
	//	echo $line;
		foreach( $fieldArray as $field )
		{
			$value = stripslashes( rtrim( substr( $line, $field->offset, $field->size ) )); 
			$FieldLookup[ $field->name ] = $value;
		}
		if( luhnCheck( 	$FieldLookup["CardNo"] ) )
		{
			CreateRecords( $FieldLookup );
		}
		else
		{
			echo "CardNo $FieldLookup[CardNo] failed luhnCheck\n";	
		}
	}


	// Main function

		global 	$ProcessName;
		global  $uname;
		global $lineNo;
		global $Updated;


		$fileMove =  LocationFileProcessing. "Processed/BatchFile/";

		$ProcessName = "BatReproc";
		$uname = $ProcessName;

		BatchInitArray();

		connectToDB();
		$lineNo = 0;


		$fileList = glob( $fileMove . "*" );
		if( !$fileList )
		{
			echo "No files to process\r\n";
		}
		else
		{
			foreach ( $fileList as $fileToProcess )
			{
				$Updated = 0;

				echo "$fileToProcess\n";

				$fr = fopen( $fileToProcess, "r");

				if(!$fr) 
				{
					echo "Error! Couldn't open the file.";
				} 
				else 
				{

					while( $line = fgets( $fr ))
					{
						$lineNo++;
						BatchProcessLine( $line );
					}
					fclose($fr);
				}
				echo "$Updated Updated\n";
			}
		}
		echo "Finished 	$lineNo</BODY></HTML>";
	?>