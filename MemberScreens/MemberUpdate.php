<?php
/***************************************************************************
// This page can be called to  
// 1) Update a current Member
// 2) Create a whole new account/member (without card)
// 3) Create a new member on an account with coping (without card) 
// 4) Create a new member on an account without coping (without card)
// 5) Create a whole new account/member (with card)
***************************************************************************/

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardRequestInterface.php";
	include "../merchantinterface/interfacesql.php";

//	07/05/09 MRM extractproducts functions copied from commands100.php and customised so that prodcode1 is used

	function extractproducts( $orderno, $merchantid, $prodcode1, $proddesc )
	{
	
		$prodcost = 0;
		$prodqty = 1;
		$prodopt = "" ;
		$prodsupplier = "STAFF";
		$prodpersonal = "";

		$result = neworderline( $orderno, $prodcode1, $merchantid, $merchanttxno, $prodopt, $type, $qty, $prodcost, $prodsupplier, $proddesc, $prodpersonal );

	}

	Function InputCheck( $fieldName )
	{
		global $Details;
		global $fields;
		global $values;
		global $tracking;

		if( isset( $_POST[$fieldName]) && ($_POST[$fieldName] != $Details[$fieldName]) )
		{
			if( $_POST[$fieldName] != '' )
			{
				$fields .= "$fieldName ='".mysql_real_escape_string($_POST[$fieldName])."',";
				if ( $fieldName == "Passwrd" )				//  01 09 10 MRM Mantis 2525 suppress passwords
				{	
					$tracking .= " Password changed ";			
				}
				else 
				{
					$tracking .= " $fieldName ". $Details[$fieldName] . "=>".$_POST[$fieldName];
				}
			}
			else
			{
				$fields .= "$fieldName = null,";
				$tracking .= " $fieldName ". $Details[$fieldName] . "=>null";
			}

			return true;
		}
		return false;
	}
	
	Function FraudInputCheck( $fieldName )
	{
		global $Details;
		global $fields;
		global $values;
		global $tracking;

		if( isset( $_POST[$fieldName] ) )
		{
			switch ($fieldName)
			{
			    case "UnderInvestigation":
			        $fraudstatus = 1;
			        break;
			    case "Cleared":
			         $fraudstatus = 3;
			        break;
			    case "Fraud":
			    	 $fraudstatus = 4;
			        break;
			}
			if( $fraudstatus != $Details[FraudStatus] )
			{
				if( $fraudstatus == '1' && $Details[FraudStatus] == '4' ) // we don't want a fraud account reverting back to under investigation 
				{
					return false;
				}
				else 
				{
				$fields .= "FraudStatus ='".$fraudstatus."',";
				$tracking .= " FraudStatus ". $Details[FraudStatus] . "=>".$fraudstatus;
				return true;
				}
			}
		}
		return false;
	}

	Function InputCheckNumber( $fieldName )
	{
		global $Details;
		global $fields;
		global $values;
 		global $tracking;
 		
		if( isset( $_POST[$fieldName]) && ($_POST[$fieldName] != $Details[$fieldName]) )
		{
			if( $_POST[$fieldName] != '' )
			{
				$fields .= "$fieldName =".mysql_real_escape_string($_POST[$fieldName]).",";
				$tracking .= " $fieldName ". $Details[$fieldName] . "=>". $_POST[$fieldName];
			}
			else
			{
				$fields .= "$fieldName = null,";
				$tracking .= " $fieldName ". $Details[$fieldName] . "=>null";
			}
			return true;
		}
		return false;
	}


	Function CheckCheck( $fieldName, $checked )
	{
		global $Details;
		global $fields;
		global $values;
  		global $tracking;

		if( isset( $_POST[$fieldName] ) != ($Details[$fieldName] == $checked) )
		{
			$fields .= "$fieldName =";
			if( isset( $_POST[$fieldName] ) )
			{
				$fields .= "'$checked',";
				$tracking .= " $fieldName ". $Details[$fieldName] . "=>$checked";
			}
			else
			{
				if($checked == 'Y' )
				{
					$fields .= "'N',";
					$tracking .= " $fieldName ". $Details[$fieldName] . "=>N";
				}
				else
				{
					$fields .= "'Y',";
					$tracking .= " $fieldName ". $Details[$fieldName] . "=>Y";
				}

			}
			return true;
		}
		return false;
	}

	function CheckVerified( $fieldName )
	{
		if( isset( $_POST[$fieldName] ))
		{
			return true;
		}
		return false;
	}

	if( isset( $_POST["Action"] ) )
	{
		$Action = $_POST["Action"];
	}
	else
	{
		$Action = "";
	}

	
	if( isset( $_POST["AccountNo"] ) )
	{
		$AccountNo = $_POST["AccountNo"];
	}
	else
	{
		$AccountNo = "";
	}

	if( isset( $_POST["MemberNo"] ) )
	{
		$MemberNo = $_POST["MemberNo"];
	}
	else
	{
		$MemberNo = "";
	}

	if( isset( $_POST["CardNo"] ) )
	{
		$CardNo = $_POST["CardNo"];
	}
	else
	{
		$CardNo = "";
	}

	if( isset( $_POST["AccountType"] ) )
	{
		$AccountType = $_POST["AccountType"];
	}
	else
	{
		$AccountType = "";
	}

	if( isset( $_POST["MemberType"] ) )
	{
		$MemberType = $_POST["MemberType"];
	}
	else
	{
		$MemberType = "";
	}

//	echo "A $AccountNo M $MemberNo C $CardNo\n";


	if( $CardNo != "" )
	{
	// If we have a card number then we might have a member in the DB already or not
	// we want to find out.
		$results = GetCardOnly( $CardNo );
	}
	else if( $MemberNo != "" )
	{
		$results = GetMemberDetails( $MemberNo );
	}
	else if( $AccountNo != "" )
	{
		// if we don't have a memberno we still might have an accountno 
		//  where we are creating a new member
		$results = GetAccountDetails( $AccountNo );
	}
	else
	{
		$results = GetBlankFields();
	}

	$Details = mysql_fetch_assoc( $results );
	$CardNo = $Details["CardNo"];
	$MemberNo = $Details["MemberNo"];
	//$AccountNo = $Details["AccountNo"];

//	echo "\nA $AccountNo M $MemberNo C $CardNo\n";

	$fields = "";
	$values = "";
	$tracking = "";

	$MemberChange = false;
	$AddressChange = false;
	$AccountChange = false;
	$CardChange = false;
	$StatementChange = false;
	$RedeemStopChange = false;
	$AwardStopChange = false;
	$VerifiedDetails = false;
	$StaffIDChange = false;
	// Member info
	$AddressChange |= InputCheck("Title");
	$AddressChange |= InputCheck("Forename");
	$AddressChange |= InputCheck("Surname");
	$AddressChange |= InputCheck("DOB");
	$AddressChange |= CheckCheck("OKWorkPhone", 'N' );
	$AddressChange |= CheckCheck("OKHomePhone", 'N' );
//	$MemberChange |= CheckCheck("CanRedeem", 'Y' );
	$MemberChange |= CheckCheck("OKMail", 'N' );
	$MemberChange |= CheckCheck("TOKMail", 'N' );
	$MemberChange |= CheckCheck("Deceased", 'Y' );
	$MemberChange |= CheckCheck("GoneAway", 'Y' );
	$MemberChange |= CheckCheck("OKEMail", 'Y' );
	$MemberChange |= CheckCheck("OKSMS", 'Y' );
	$MemberChange |= InputCheck("Passwrd" );
	$MemberChange |= InputCheck("MemberType" );
	$MemberChange |= InputCheck("StaffID" );
	$AddressChange |= InputCheck("Organisation" );
	$AddressChange |= InputCheck("Address1");
	$AddressChange |= InputCheck("Address2");
	$AddressChange |= InputCheck("Address3");
	$AddressChange |= InputCheck("Address4");
	$AddressChange |= InputCheck("Address5");
	$AddressChange |= InputCheck("PostCode");

	if( $AddressChange  )
	{
		$fields .= "AddressVerified = now(),";
		$MemberChange = true;
	}
	elseif ( CheckVerified( "AddNdsVfy" ) )
	{
		$fields .= "AddressVerified = now(),";
		$VerifiedDetails = true;
	}

	if( InputCheck("WorkPhone") )
	{
		$fields .= "WorkVerified = now(),";
		$MemberChange = true;
	}
	elseif ( CheckVerified( "WorkNdsVfy") )
	{
		$fields .= "WorkVerified = now(),";
		$VerifiedDetails = true;
	}

	if( InputCheck("HomePhone") )
	{
		$fields .= "HomeVerified = now(),";
		$MemberChange = true;
	}
	elseif( CheckVerified( "HomeNdsVfy") )
	{
		$fields .= "HomeVerified = now(),";
		$VerifiedDetails = true;
	}

	if( InputCheck("Email") )
	{
		$fields .= "EmailVerified = now(),";
		$MemberChange = true;
	}
	elseif ( CheckVerified( "EmailNdsVfy") )
	{
		$fields .= "EmailVerified = now(),";
		$VerifiedDetails = true;
	}

	// Account Information

	//$AccountChange |= CheckCheck("StatementStop", 'Y' );

	if( isset($_POST["StatementPreference"]) )
	{
		if( $_POST["StatementPreference"] != $Details["StatementPreference"])
		{
			$fields .= "StatementPref='$_POST[StatementPreference]',";
			$tracking .= " StatementPref $Details[StatementPreference] => $_POST[StatementPreference] ";
			$StatementChange |= true;
		}
	}
	else
	{
		if( $Details["StatementPreference"] == '' or $Details["StatementPreference"] == NULL )
		{
			$fields .= "StatementPref='N',";
			$StatementChange |= true;
		}
	}
	
		if( isset($_POST["FraudStatus"]) )
	{
		if( $_POST["FraudStatus"] != $Details["FraudStatus"])
		{
			$fields .= "FraudStatus='$_POST[FraudStatus]',FraudStatusSetDate=now(),";
		}
	}
	
	$AccountChange |= InputCheck("AccountType");
	$AccountChange |= InputCheck("MonthlySpend");
	$AccountChange |= InputCheck("VirginNo");

	if( InputCheckNumber("HomeSite") )
	{
		$AccountChange |= true;
		$fields .= "HomeSiteDate=now(),";
	}

	if( isset( $_POST["RedemptionStop"] ) != ($Details["RedemptionStopDate"] != "") )
	{
		if ($_POST[AccountStatus] != 'Closed')
		{
			$fields .= "RedemptionStopDate=";
			if( isset( $_POST["RedemptionStop"]) )
			{
				$fields .= "now(),";
			}
			else 
			{
				$fields .= "null,";
			}
			$RedeemStopChange |= true;
		}

	}


	$ReleaseAllPoints = false;
	if( isset( $_POST["AwardStop"] ) != ($Details["AwardStopDate"] != "") )
	{
		if ($_POST[AccountStatus] != 'Closed')
		{
			$fields .= "AwardStopDate=";
			if( isset( $_POST["AwardStop"]) )
			{
				$ReleaseAllPoints = false;
				$fields .= "now(),";
			}
			else
			{
				$fields .= "null,";
				$ReleaseAllPoints = true;
			}
			$AwardStopChange |= true;	
		}

	}


	if( $fields != "" )
	{
		$newAccount = false;
		$newMember = false;
		if( $MemberNo == "" && ( $AccountChange || $MemberChange || $RedeemStopChange || $AwardStopChange ))
		{
			$newMember = true;
			if( $AccountNo == "" )
			{
				$newAccount = true;
			}
			$SiteCode = $_POST["HomeSite"];
			$Organisation = $_POST["Organisation"];
			CreateMember( $CardNo, $AccountNo, $MemberNo, $SiteCode, $AccountType, $Organisation );

			$ReleaseAllPoints = true;
		}
		if( $CardNo != ""  )
		{
			if( $MemberNo != "" )
			{
				$sql = "Update Accounts, AccountStatus, Members, Cards set ".rtrim( $fields , ",")." where Cards.MemberNo = Members.MemberNo and Members.AccountNo = Accounts.AccountNo and Accounts.AccountNo = AccountStatus.AccountNo and CardNo = '$CardNo'";
//				echo $sql;
			}
			else
			{
				//echo "Here";
			}
		}
		else
		{
			$sql = "Update Accounts, Members set ".rtrim( $fields , ",")." where Members.AccountNo = Accounts.AccountNo and Members.MemberNo=$MemberNo";
		}
		//		echo $sql;

		$results = DBQueryExitOnFailure( $sql );
		
		// Mantis 1893 MRM 29 APR 10 Project Leeson
	/*	if( $RedeemStopChange )
		{
			if( isset($_POST["RedemptionStop"]) && $_POST["RedemptionStop"] != "1" )
			{
				header("Location: ../MemberScreens/LettersProcess.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1127");
			}
		}
		*/
		if( $newAccount or $newMember)
		{
		}
		else if( $AccountNo != "" )
		{
			$notes = "";
			$code = "";
			if( $MemberChange )
			{
				$code = TrackingPreferenceChange;
			}
			if( $AccountChange )
			{
				$code = TrackingModifyAccount;
			}
			if( $AddressChange )
			{
				$code = TrackingContactChange;
			}
			if( $StatementChange )
			{
				$code = TrackingStatementPref;
			}
			if( $CardChange )
			{
				$code = TrackingHomeSiteChanged;
			}
			if( $RedeemStopChange )
			{
				$code = TrackingRedeemStopChanged;
				$notes = "Redemption Stop Flag changed";
				InsertTrackingRecord( $code, $MemberNo, $AccountNo, $notes, 0);
				$code = "";
			}
			if  ( $AwardStopChange)
			{
				$code = TrackingAwardStopChanged;
				$notes = "Award Stop Flag changed";
				InsertTrackingRecord( $code, $MemberNo, $AccountNo, $notes, 0);
				$code = "";
			}
			if( $code != "" )
			{
 				InsertTrackingRecord( $code, $MemberNo, $AccountNo, $tracking, 0 );
			}
		}
	}

	if( $ReleaseAllPoints && $AccountNo != "")
	{
		ReleaseStoppedPoints( $AccountNo );
	}

	// If we have created a new member without a card then we need to 
	// request a card for this member, unless he is a Staff Loyalty Scheme member.... MRM 10/7/08

	if( $Action == "NewAccount"  )
	{
		if( $CardNo == "" )
		{
			if( $AccountType == 'G' )
			{
				InsertRequestRecord( $MemberNo, RequestGroupTreasure );
			}
			else
			{
				if( $AccountType != 'D' )
				{	
					InsertRequestRecord( $MemberNo, RequestNewMember );
				}
			}
		}
	}
	if( $Action == "CopyMember" )
	{
		InsertRequestRecord( $MemberNo, RequestNewMember );
	}
	if( $Action == "NewMemberNoCopy" OR $Action == "NewMemberCopy")
	{
		if( $Details["AccountType"] == 'G' )
		{
			
			if( $MemberType == 'T' )
			{
				InsertRequestRecord( $MemberNo, RequestGroupTreasure );
			}
			else if( $MemberType == 'S' )
			{
				InsertRequestRecord( $MemberNo, RequestGroupSecretairy );
			}
			else
			{
				InsertRequestRecord( $MemberNo, RequestGroupMember );
			}
		}
		else
		{
			InsertRequestRecord( $MemberNo, RequestNewMember );
		}
	}

	
	// MRM 12 08 09 - new member processing for group loyalty 
	
	if( $Action == "NewGroupMember")
	{
		InsertRequestRecord( $MemberNo, RequestNewMember );
	}
	// new bit ends
		
	if( isset( $_POST["ActionToDo"] ) && $_POST["ActionToDo"] != "" )
	{
		$ActionToDo = "&ActionToDo=".$_POST["ActionToDo"];
	}
	else
	{
		$ActionToDo = "";
	}

//		echo $sql;

	if( $Action == "NewAccount"  )
	{
		if( $AccountType != 'D' )
		{
			header("Location: DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Questions=true");
		}
		else 
		{
		//MRM 06/05/09 - kick off an order for a polo shirt if the Shirt Size has been specified
			if( isset($_POST["PoloShirtSize"]) )
			{
				if( $_POST["PoloShirtSize"] != 'N')
				{
					switch ($_POST["PoloShirtSize"])
					{
						    case "S":
						        $prodcode1 = 1556;
						        $proddesc = "Polo Shirt -  Small";
						        break;
						    case "M":
						        $prodcode1 = 1557;
						        $proddesc = "Polo Shirt - Medium";
						        break;
						    case "L":
						        $prodcode1 = 1558;
						        $proddesc = "Polo Shirt - Large";
						        break;
						    case "XL":
						        $prodcode1 = 1559;
						        $proddesc = "Polo Shirt - XLarge";
						        break;    
					}
					$title = $_POST["Title"];
					$forename = $_POST["Forename"];
					$name = $_POST["Surname"];
					$address1 = $_POST["Address1"];
					$address2 = $_POST["Address2"];
					$address3 = $_POST["Address3"];
					$address4 = $_POST["Address4"];
					$address5 = $_POST["Address5"];
					$postcode = $_POST["PostCode"];
					$datetime = date("Y-m-d H:i:s");
					$userid = $uname;
					$balance = 0;
					$merchantid = 1;
					$orderno = createorder($AccountNo,$MemberNo, $title, $forename, $name, $address1,$address2,$address3,$address4,$address5,$postcode,$datetime, $userid, $balance);
					extractproducts( $orderno, $merchantid, $prodcode1, $proddesc );
					
				}
			}
			header("Location: DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Questions=false");
		}
	}
	else
	{
	header("Location: DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo$ActionToDo");
	}
	//echo 	"Location: DisplayMember.php?AccountNo=$AccountNo&MemberNo=&MemberNo&CardNo=$CardNo";

?>