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

/*
Create Table PhoneStats
(
  CardNo char(20),
  HomePhone char(20),
  MobilePhone char(20),
  WorkPhone char(20),
  WorkPhone2 char(20)
);
*/ 


 include "../../include/DB.inc";
 include "../../include/Locations.php";

	$db_host = "localhost";
	$db_name = "Analysis";
	$db_user = "root";
	$db_pass = "trave1";																		   
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
		$fieldArray[$fieldCounter++] = new Field( "DOB",		"text", 4, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address1",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address2",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address3",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address4",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Address5",	"text", 40, "" );
		$fieldArray[$fieldCounter++] = new Field( "Postcode",	"text", 8,  "" );
		$fieldArray[$fieldCounter++] = new Field( "Email",		"text", 80, "" );
		$fieldArray[$fieldCounter++] = new Field( "EmailOpt",	"Y/N",  1,  "" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone",	"text", 20, "WorkPhone" );
		$fieldArray[$fieldCounter++] = new Field( "MobilePhone",	"text", 20, "MobilePhone" );
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
		$fieldArray[$fieldCounter++] = new Field( "OptOutTex",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OptOutColt",		"Y/N", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "HomePhone",		"text", 20, "HomePhone" );
		$fieldArray[$fieldCounter++] = new Field( "WorkPhone2",		"text", 20, "WorkPhone2" );
		$fieldArray[$fieldCounter++] = new Field( "CollectFor",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Mileage",		"number", 6, "" );
		$fieldArray[$fieldCounter++] = new Field( "Visits",			"number", 2 , "");
		$fieldArray[$fieldCounter++] = new Field( "Company_pay",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Cars",			"text", 1 , "");
		$fieldArray[$fieldCounter++] = new Field( "Fleet",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "ThirdParty",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "PAF",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Colt",			"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Source",			"text", 8, "Source" );
		$fieldArray[$fieldCounter++] = new Field( "Statements",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "SiteCode",		"number", 10, "" );
		$fieldArray[$fieldCounter++] = new Field( "FillTotal",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "OutOf10",		"text", 2, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWFuel",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "REWCarWash",		"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "Prefs",			"text", 3, "" );
		$fieldArray[$fieldCounter++] = new Field( "AutoVoucher",	"text", 1, "" );
		$fieldArray[$fieldCounter++] = new Field( "PartnerOptin",	"text", 1, "" );
	}


	function CreateRecords( $fields )
	{
		global $fieldArray, $ProcessName;

		$fieldsToFill = "";
		$values = "";
		$s = "";
		foreach( $fieldArray as $fielddd )
		{
			if( $fielddd->memberCol != "" && $fields[$fielddd->name] != "" )
			{
				$fieldsToFill .= $s. $fielddd->memberCol;
				$values .= $s. "'".mysql_escape_string($fields[$fielddd->name])."'";
				$s = ",";
			}
		}
		$sql = "Insert into PhoneStats( $fieldsToFill ) values ( $values )";
		$results = DBQueryExitOnFailure( $sql );
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
			$value = rtrim( substr( $line, $field->offset, $field->size ) ); 
			$FieldLookup[ $field->name ] = $value;
		}

		if( luhnCheck( 	$FieldLookup["CardNo"] ) )
		{
			CreateRecords( $FieldLookup );
		}
	}


	// Main function

		global 	$ProcessName;
		global  $uname;
		global $lineNo;


		$fileMove =  LocationFileProcessing. "Processed/BatchFile/";

		$ProcessName = "CardRequest";
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
			}
		}
		echo "Finished 	$lineNo</BODY></HTML>";
	?>