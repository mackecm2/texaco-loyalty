<?php 
// MRM 22/09/08 - Status = A : - so that only Approved records get included
//
	$db_user = "CompowerProcess";
	$db_pass = "ComPassword";

	include "misc.php";
	require "../../include/Locations.php";
	include "../../include/DB.inc";
	include "../../DBInterface/FileProcessRecord.php";

	function WriteComments( &$Wiz )
	{
		
		fputs( $Wiz, "// Automatically generated script file that produces the code to calculate bonuses\n");
		fputs( $Wiz, "// Script run ". date( "r" )."\n\n\n" );
		fputs( $Wiz, "error_reporting( E_ALL );\n\n\n" );
		
	}


	function TestForPersonalBonus( $promo )
	{
		$sql = "Select * from BonusCriteria where PromotionCode='$promo' and FieldName = PromotionCode";
		$results = mysql_query( $sql ) or die( mysql_error());
		
		return mysql_num_rows($results) > 0;
	}

	function WriteTestLine( $promo, $indent )
	{
		$res = "";
		$inBracket = false;
		$sql = "Select FieldTest, Boolean, ComparisionCrteria from FieldComparisions JOIN BonusCriteria using (FieldName, ComparisionType )  where PromotionCode='$promo' order by CriteriaNo";
		$results = mysql_query( $sql ) or die( mysql_error());
		while( $row = mysql_fetch_assoc( $results ))
		{
			if( $row["Boolean"] == "OR" && $inBracket == false )
			{
				$res .=" ( ";	
				$inBracket = true;
			}
			$FieldTest = $row["FieldTest"];
			$FieldTest = str_replace( "%exp", "'$row[ComparisionCrteria]'", $FieldTest );
			$res.= $FieldTest;
			if( $row["Boolean"] == "AND" && $inBracket == true )
			{
				$res .=" ) ";	
				$inBracket = false;
			}
			if( $row["Boolean"] != "" )
			{
				$res .= "\n".$indent.$indent;
			}
			else
			{
				if( $inBracket )
				{
					$res .= ")";
				}
			}
			$res.=  $row["Boolean"] . " ";
		}
		if(	$res != "" )
		{
			$res = "($res)";
		}
		return $res;
	}
	
//***********************************************************************************
//
// This function writes the lines that test if we have a promotion hit function
// 
// Its important to make sure that personal campaign promotions have a criteria in
// them of promotion code
//
//***********************************************************************************

	function WriteCalculateAlgo( &$Wiz, $criteria, $funcName, $sectionValue )
	{
		// Extract the promotion code data.

		$sql = "Select PromotionCode, StartDate, EndDate, 	BonusPoints, PerQuantity, if( Exclude=1,'true','false') as Exclude, AppliesTo, Threshold, ThresholdPts, MaximumHits from BonusPoints where $criteria and Active = 'Y' and Status = 'A' order by Priority";
		$results = mysql_query( $sql ) or die( mysql_error());
		
		$numberEntries = mysql_num_rows( $results );
		$count = 0;
		$indent = "    ";
		$finalReturn = true;
		
		// write the generic part of the function

		fputs( $Wiz, "function $funcName\n{\n");

		fputs( $Wiz, $indent."global \$gTransactionData, \$gUserData, \$gSiteData, \$gProductData, \$gDeptData;\n");
		fputs( $Wiz, $indent."\$rBonus = 0;\n");
		fputs( $Wiz, $indent.'$SectionValue'. " = $sectionValue;\n");

		// Build up the promotion tests.

		while( $row = mysql_fetch_assoc( $results ))
		{
			$count++;

			// If the promotion has a start or end date then add evaluation code

			$condStr = '$SectionValue != 0 ';
			if( $row["StartDate"] != "" or $row["EndDate"] != "" )
			{
				$condStr .= "AND DateRange( '$row[StartDate]', '$row[EndDate]')\n$indent";
			}

			// If the promotion is a personal promotion then see if it has a limit count on it.

			$PersonalBonus = TestForPersonalBonus( $row["PromotionCode"] ); 

			$HitCount = "false";
			if( $row["MaximumHits"] != -1 )
			{
				$condStr .= ' AND $gUserData->PromoHitsLeft > 0 ';
				$HitCount = "true";
			}

			// Builf up the Cretieria string

			$TestLine = WriteTestLine( $row["PromotionCode"], $indent );
			if( $TestLine != "" )
			{
				$condStr .= " AND $TestLine";
			}
			
			// Call the Bonus Calculation function

			// BonusPoints PerQuantity ( Basic bonus calculation )
			// PromotionCode
			// AppliesTo 
			// Exclude If this bonus is successfull then do we use this value in other calculations
			// Threshold ThresholdPts used if there is a threshold
			// SectionValue (passed by reference) 
			// HitCount true if there is a hit limit

			$BonusCalc = '$rBonus = Bonuses('. " $row[BonusPoints], $row[PerQuantity], '$row[PromotionCode]', '$row[AppliesTo]', $row[Exclude], $row[Threshold], $row[ThresholdPts],". '$SectionValue'. ",$HitCount);";

			fputs( $Wiz, $indent."if( $condStr )\n".$indent. "{\n".$indent.$indent.$BonusCalc."\n".$indent."}\n" );
		}

		// close the function

		fputs( $Wiz, $indent.'return $rBonus'.";\n}\n" );
	}
		//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 02/07/2008
	//*  echo "/FileProcessing/General/WriteAlgo.php - Writing New algorithm ".date("Y-m-d H:i:s"). "\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	connectToDB( MasterServer, TexacoDB );
	$fill = LocationFileProcessing."General/Calculate.php";
	echo "$fill\r\n";

	$rec = CreateProcessStartRecord( "BonusWriter" );

	$Wiz = fopen( $fill, "w" );
	if( $Wiz )
	{
		fputs( $Wiz, "<?php\n\n");
		WriteComments( $Wiz );
		WriteCalculateAlgo( $Wiz, "AppliesTo='Quantity'", "CalculateProductVolumeBonus()", '$gProductData->volume');
		WriteCalculateAlgo( $Wiz, "AppliesTo='Product'", "CalculateProductValueBonus()", '$gProductData->value');
		WriteCalculateAlgo( $Wiz, "AppliesTo='Total'", "CalculateTotalBonus()", '$gTransactionData->starValueCurrency' );
		WriteCalculateAlgo( $Wiz, "AppliesTo='Visit'", "CalculateVisitBonus()", "1");
		WriteCalculateAlgo( $Wiz, "AppliesTo='Dept'", "CalculateDeptBonus()", "0" );
		WriteCalculateAlgo( $Wiz, "AppliesTo='PeriodSpend'", "CalculatePeriodBonus()", '$gUserData->periodSpend' );

		fputs( $Wiz, "\n\n?>\n" ); 

		fclose( $Wiz );
	}
	else
	{
		LogError( "Failed to open bonus algorithum file" );
	}
	//*
	//* next line exchanged for the one below it for greater clarity in logs - MRM 02/07/2008
	//* echo "/FileProcessing/General/WriteAlgo.php - Completed ". date("Y-m-d H:i:s")."\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
	
	

	CompleteProcessRecord( $rec );
?>