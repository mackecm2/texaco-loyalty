<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/BonusFunctions.inc";
	require("../mailsender/class.phpmailer.php");

//	$promo = $_POST["promoCode"];
	$promo = str_replace(" ", "", $_POST["promoCode"]);
	if ($promo == '' or !isset($_POST["promoCode"]))
	{
		 header("Location: BonusEdit.php");
		 exit();
	}

	$NewPromo = $_POST["newpromo"];
	$fieldNames = $_POST["FieldName"];
	$Comparisions = $_POST["Comparision"];
	$Booleans = $_POST["Boolean"];
	$Modes = $_POST["Mode"];

	//$sql = "Delete from BonusCriteria where PromotionCode = '$promo'"; replaced with what's below MRM 07/11/2008 
	$OldPromo = 'N';
	$sql = "SELECT Status, Active FROM BonusPoints WHERE PromotionCode = '$promo'";
//	echo $sql;
//	exit;
	$results = mysql_query( $sql );
	if ( $NewPromo == "new" && mysql_num_rows($results))
	{
		echo "You have specified a duplicate Promotion Code. $promo is already in use.\r\n";
		echo "Use the 'Back' button to return to the screen.";	
		exit;
	}	
	while( $row = mysql_fetch_array( $results ) )
	{
		$Status = $row['Status'];
		$Active = $row['Active'];
		if ($Status == 'R' or $Active == 'N')
		{
			$OldPromo = 'Y';
			$sql1 = "UPDATE BonusCriteria SET PromotionCode = CONCAT('*',PromotionCode) WHERE PromotionCode = '$promo'";
 			$results1 = mysql_query( $sql1 );	
		}
	}
	
	$count = 0;
	foreach( $fieldNames as $value )
	{
		if( $fieldNames[$count] != "" )
		{
			if( $Modes[$count] == "Text" )
			{
				$crit = $_POST["FreeText"][$count];
			}
			else if( $Modes[$count] == "List" )
			{
				$crit = $_POST["Single"][$count];
			}
			else if( $Modes[$count] == "Range" )
			{
				$crit = $_POST["Range"][$count];
			}
			
			// we want to ensure we always have a site id - the user has left a blank field so lets insert a dummy site id.
			
			if($crit == '')
			{
				$crit = '111111';
				
			}				
		
			$sql = "Insert into BonusCriteria (	PromotionCode, CriteriaNo, FieldName, ComparisionType, Boolean, ComparisionCrteria ) values ( '$promo', $count, '$fieldNames[$count]', '$Comparisions[$count]', '$Booleans[$count]', '$crit')";
			$results = mysql_query( $sql )or die (mysql_error());
			$count++;
		}
	}

	//.$sql = "Delete from BonusPoints where PromotionCode = '$promo'";
	if ($OldPromo == 'Y')
	{
		$sql = "UPDATE BonusPoints SET PromotionCode = CONCAT('*',PromotionCode), RevisionDate = NOW( ) WHERE PromotionCode = '$promo'";
			$results = mysql_query( $sql ) or die (mysql_error());
	}
	
	if( isset( $_POST["Exclude"] ) )
	{
		$Exclude = 1;
	}
	else
	{
		$Exclude = 0;
	}

	$sqlFields = "PromotionCode, BonusName, BonusPoints, AppliesTo, Exclude, CreationDate,	CreatedBy, Status";
	$sqlValues = " '$promo', '$_POST[promoName]', $_POST[Pts], '$_POST[AppliesTo]', $Exclude, now(), '$uname', 'P'";

	if( $_POST["StartDate"] != "" )
	{
		$sqlFields .= ",StartDate";
		$sqlValues .= ",'$_POST[StartDate]'";
	}

	if( $_POST["EndDate"] != "" )
	{
		$sqlFields .= ",EndDate";
		$sqlValues .= ",'$_POST[EndDate]'";
	}

	if( $_POST["PerQuantity"] != "" )
	{
		$sqlFields .= ",PerQuantity";
		$sqlValues .= ",'$_POST[PerQuantity]'";
	}

	if( isset( $_POST["Priority"] ) )
	{
		$sqlFields .= ",Priority";
		$sqlValues .= ",$_POST[Priority]";
	}

	if( isset( $_POST["MaximumHits"] ) && $_POST["MaximumHits"] != "" )
	{
		$sqlFields .=  ",MaximumHits";
		$sqlValues .= ", $_POST[MaximumHits]"; 
	}


	if( isset( $_POST["Threshold"] ) && $_POST["Threshold"] != "" )
	{
		$sqlFields .=  ",Threshold";
		$sqlValues .= ", $_POST[Threshold]"; 
	}

	if( isset( $_POST["ThresholdPts"] ) && $_POST["ThresholdPts"] != "" )
	{
		$sqlFields .=  ",ThresholdPts";
		$sqlValues .= ", $_POST[ThresholdPts]"; 
	}

	$sql = "Insert into BonusPoints ( $sqlFields   ) values ( $sqlValues  )";

//	print $sql;

	$results = mysql_query( $sql ) or die (mysql_error());
	$urgent = 1;

   	sendemail($promo, $uname, $urgent);

//	print_r( $_POST );

	header("Location: BonusManager.php");
?>
