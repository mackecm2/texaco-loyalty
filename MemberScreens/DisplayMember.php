<?php

/***************************************************************************
// This page can be called to 
// 1) Update a current Member
// 2) Creating a whole new account/member (without card)
// 3) Creating a new member on an account with coping (without card) 
// 4) Creating a new member on an account without coping (without card)
// 5) Creating a whole new account/member (with card)
//
// This used to be done in a different way for 3) but with the addition of
// the group loyalty scheme and the requirments specified with that it became to
// convluted code and was based re-worked to handle it here.
//
// It also made the functionality more consitent because the member used to be
// created prior to this page for 3) where as they were created afterwards
// for 5)
// 
//    29/04/08 MRM Made "No Files to process" Error Message a bit more eye-catching
//    14/01/09 MRM changes to VerifyDOB( y ), UpdateDOB(), and html for Mantis 705
//    03/08/09 MRM Group Balance displayed if part of Group Loyalty Scheme
//    23/02/10 MRM Redemption Stop Date displayed
***************************************************************************/


	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../include/Locations.php";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/UserInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/AccountCardsInterface.php";


	if( isset( $_GET["Action"] ) )
	{
		$Action = $_GET["Action"];
	}
	else
	{
		$Action = "";
	}

	if( isset( $_GET["Staff"] ) )
	{
		$Staff = $_GET["Staff"];
	}
	else
	{
		$Staff = "";
	}
	
	if( isset( $_GET["Group"] ) )
	{
		$Group = $_GET["Group"];
	}
	else
	{
		$Group = "";
	}
	
	$AccountCards = true;
	if( isset( $_REQUEST["AccountNo"] ) )
	{
		$AccountNo = $_REQUEST["AccountNo"];
	}
	else
	{
		$AccountNo = "";
	}

	if( isset( $_GET["MemberNo"] ) )
	{
		$MemberNo = $_GET["MemberNo"];
	}
	else
	{
		$MemberNo = "";
	}

	if( isset( $_GET["CardNo"] ) )
	{
		$CardNo = $_GET["CardNo"];
	}
	else
	{
		$CardNo = "";
	}


	if( $AccountNo == "" && $CardNo == "" && $Action != "NewAccount"  )
	{
		header("Location: ../MemberScreens/SelectMember.php");
		exit();
	}

	function HighlightCellOn( $pre, $Name, $cond )
	{
		if( $cond )
		{
			echo "<td $pre id=\"$Name\" style=\"color:red; text-decoration:blink\">";
		}
		else
		{
			echo "<td $pre id=\"$Name\">";
		}
	}

	// Get the relevant results set according to what we are doing

	if( $Action == "NewAccount" )
	{
		if( $CardNo != "" )
		{
			// Linking an existing Card
			$results = GetCardOnly( $CardNo );
		}
		else
		{
			// Creating a new member without a card
			$results = GetBlankFields();
		}
	}
	else if( $Action == "NewMemberCopy" )
	{
		// Adding a new member to an account and copying some details
		$results = GetMemberCopy( $MemberNo );
	}
	else if( $Action == "NewMemberNoCopy" )
	{
		// Adding a completely seperate member to the account
		$results = GetMemberNoCopy( $MemberNo );
	}
	else if( $Action == "NewGroupMember" && $Group == "yes")
	{
		// Adding a new member to a Group account
		$results = GetBlankGroupFields($AccountNo);
	}
	else
	{	
		// Displaying the details of the account
		$results = GetAllCardsByAccount( $AccountNo );
	}

	$AssocMembers = array();
	$AssocCards = array();

	$prevMember = "";
	$Details = array();
	$bestMatch = array();

	$TotalStopped = 0;
	$TotalCards = 0;
	$tMatch = false;
	// start match search

	if( mysql_num_rows( $results ) == 1 )
	{
		$Details = mysql_fetch_assoc( $results );
		$AccountNo = $Details["AccountNo"];
		$CardNo = $Details["CardNo"];
		$MemberNo = $Details["MemberNo"];
		$fraudstatus = $Details["FraudStatus"];
		$AccountStatus = $Details["Status"];
		$tMatch = true;
		$AssocCards = array();
		$AssocCards[$Details["CardNo"]] = $Details["CardNo"];
		$TotalStopped += $Details["StoppedPoints"];
		if( $CardNo != "" )
		{
			$TotalCards++;
		}
	}
	else
	{
		while( $row = mysql_fetch_assoc( $results ) )
		{

			if( $prevMember != $row["MemberNo"])
			{
				$prevMember = $row["MemberNo"];
				if( $row["PrimaryMember"] == 'Y' )
				{
					$AssocMembers[$row["MemberNo"]] = "(P) $row[Title] $row[Forename] $row[Surname]";
				}
				else
				{
					$AssocMembers[$row["MemberNo"]] = "$row[Title] $row[Forename] $row[Surname]";
				}
				if( $MemberNo == "" )
				{
					$AssocCards = array();
				}
			}

			$match = false;
			if( $CardNo != "" and $CardNo === $row["CardNo"] )
			{
				$match = true;
			}
			else if( $CardNo == "" )
			{
				if( $MemberNo == $row["MemberNo"] )
				{
					if( $row["PrimaryCard"] == $row["CardNo"] OR $row["PrimaryCard"] == "" )
					{
						$match = true;
					}
					else
					{
						$bestMatch = $row;
					}
				}
				else if( $MemberNo == "" )
				{
					if( $row["PrimaryMember"]=='Y')
					{
						$match = true;
					}
					else
					{
						$bestMatch = $row;
					}
				}
			}
			else if( $row["CardNo"] != "" && !isset( $bestMatch["CardNo"] ))
			{
				$bestMatch = $row;
			}

			if( $match )
			{
				$AccountNo = $row["AccountNo"];
				$CardNo = $row["CardNo"];
				$Details = $row;
				$MemberNo = $prevMember;
				$tMatch = true;
				$bestMatch = $row;
			}

			if( $row["CardNo"] != "" )
			{
				$TotalCards++;
			}

			if( ($prevMember == $MemberNo ||  $MemberNo == "") && $row["CardNo"] != "")
			{
				$AssocCards[$row["CardNo"]] = $row["CardNo"];
			}
			
			$TotalStopped += $row["StoppedPoints"];
		}
		if( !$tMatch )
		{
			$Details = $bestMatch;
			$CardNo = $Details["CardNo"];
		}
	}
	// end of match search

	$fraudstatus = $Details["FraudStatus"];
	$AccountStatus = $Details["Status"];
	$AccountType = $Details["AccountType"];

	$AccountTypes = GetAccountTypeList();
	$Titles = GetMemberTitles();
//	$AutoOptions = GetAutoOptionsList();
	$MontlySpends = GetMonthlySpendList();
	$TimeStamp = GetSQLTime1();

	$GroupMemberTypes = array();
	$GroupMemberTypes[''] = '';
	$GroupMemberTypes['T'] = 'Treasurer';
	$GroupMemberTypes['S'] = 'Secretary';
	$GroupMemberTypes['M'] = 'Member';

	if( $MemberNo != "" )
	{
		$RequestedCards = GetOutstandingCardRequestForMember( $MemberNo );
		$i = 0;
		while( $i < $RequestedCards )
		{
			$AssocCards[$i] = "Card Requested";
			$i++;
		}
	}
	if( $Details["PrimaryMember"] != 'N' )
	{
		$PrimaryCard = true;
		$PrimaryOnly = "";
		$MemberLabel = "Primary Cardholder";
		$AssociatedLabel = "Group Members";
		$DisplayTotal = $Details["Balance"];
		$statePref = "";
	}
	else
	{
		$PrimaryCard = false;
		$PrimaryOnly = "disabled";
		$MemberLabel = "Secondary Cardholder";
		$AssociatedLabel = "Group Members";
		$DisplayTotal = 0;
		$statePref = " disabled ";
		
		$sql = "SELECT AccountNo FROM CardRanges JOIN Accounts USING ( AccountNo ) WHERE AccountType = 'G'";
		$results = DBQueryLogOnFailure( $sql );
		while( $row = mysql_fetch_assoc( $results )  )
		{
			if ($AccountNo == $row['AccountNo'])
			{
				$DisplayTotal = $Details["MemberBalance"];
				$GroupBalanceTotal = $Details["Balance"];
			}
		}
	}

	if( $Details["Title"] != "" )
	{
		$LTitle = "Other";
		$OTitle = "value=\"$Details[Title]\"";
		foreach( $Titles as $Val )
		{
			if( $Details["Title"] == $Val )
			{
				$LTitle = $Val;
				$OTitle = "style=\"display:none\"";
			}
		}
	}
	else
	{
		$LTitle = "";
		$OTitle = "style=\"display:none\"";
	}

	AddUserHistory( $MemberNo );

	if( $AccountNo != "" )
	{
		$TrackingHistory = GetRecentTrackingHistory( $AccountNo, 8 );
	}

	if( $AccountNo == "" or $MemberNo == ""  )
	{
		$cButton = "disabled";
	}
	else
	{
		$cButton = "";
	}

	$ctr = "";
	if( isset( $_GET["ActionToDo"] ) )
	{
		$ctr = "onload=\"$_GET[ActionToDo]()\"";
	}
	else if( isset( $_GET["Questions"] ) && $_GET["Questions"] != "false" && CheckPermisions( PermissionsQuestionUser ) && $PrimaryCard && $AccountType != "D" && $AccountStatus != "Closed")
	{
		$ctr = "onload=\"ShowQuestionaire()\"";
	}

	$UKFuelsAccountNo = "";
	if( $AccountNo != "" )
	{
		#	Lets find out if this Account has an associated UKFuels Account Number

		$ukfuelsresult = GetAccountCards( $AccountNo, "" );

		$UKFuelsAccountNo = "";
		$c = "";
		While($row = mysql_fetch_array($ukfuelsresult))
		{
			$UKFuelsAccountNo .= $c. $row['GAccountNo'];
			$c = ",";
			#echo "UKF is $UKFuelsAccountNo";
		}

	}


	$Title = "Card Holder";
	$currentPage = "Card Holder";
	$bodyControl = "onbeforeunload=\"LeavePage()\" $ctr";
	include "../MasterViewHead.inc";
	include "CardHolderButtons.inc";

?>


<script>
	dirty = false;
<?php
	if( $Details["PrimaryMember"] == 'Y' )
	{
		echo "primaryMember = true;\n";
	}
	else
	{
		echo "primaryMember = false;\n";
	}

?>
	function SetLabelRed( label )
	{
		var sel = document.getElementById(label);
		sel.style.color = "red";
	}

	function ClearLabel( label )
	{
		var sel = document.getElementById(label);
		sel.style.color = "black";
	}

	function VerifyEmail( str )
	{
	//	var r1 = new RegExp("(@.*@)|(\\.\\.)|(@\\.)|(^\\.)");
	//	var r2 = new RegExp("^.+\\@(\\[?)[a-zA-Z0-9\\-\\.]+\\.([a-zA-Z]{2,3}|[0-9]{1,3})(\\]?)$");
	//	return (!r1.test(str) && r2.test(str));
	return true;
	}

	function UpdateField( label )
	{
		ClearLabel( label );
		SetDirty();
	}

	function VerifyPostcode( str )
	{
		var r1 = new RegExp("^[a-zA-Z]{1,2}\d{1,2}\s\d[a-zA-Z]{1,2}");
		return (!r1.test(str));
	}

	function VerifyDOB( y )
	{
		var thisdate = new Date();
		return ( y > 1900 && thisdate.getYear() > y );
	}

	function VerifyStatementPref()
	{

	}

	function SetDirty()
	{
		dirty = true;
		document.getElementById("update").disabled = false;
		//document.getElementsById("create").disabled = true;
	}

	function DisableSubmit()
	{
		document.getElementById("update").disabled = true;
	}

	function LeavePage()
	{
<?php
		if	($AccountStatus == "Closed")
		{
?>
			if( dirty )
			{
				event.returnValue = "********** THIS ACCOUNT IS CLOSED! **********\n\nRE-OPEN THE ACCOUNT BEFORE MAKING ANY CHANGES.\n\n.... .... .... .... PRESS OK TO PROCEED .... .... .... ....\n********************************************";
			}
<?php
		}
		else
		{
?>
			if( dirty )
			{
				event.returnValue = "You have not saved your changes to the database!\n Press OK to lose your changes.";
			}
<?php
		}
?>
	}

	function RemoveDirty()
	{
		dirty = false;
	}

	function DisplayAssociated()
	{
		var sel = document.getElementById("members");
		var mem = sel.options[sel.selectedIndex].value;
		window.location = "DisplayMember.php?AccountNo=<?php echo $AccountNo; ?>&MemberNo=" + mem;
	}

	function DisplayCards()
	{
		var sel = document.getElementById("cards");
		var mem = sel.options[sel.selectedIndex].value;
		if( mem.length > 10 )
		{
			window.location = "DisplayMember.php?AccountNo=<?php echo $AccountNo; ?>&CardNo=" + mem;
		}
	}

	function EmailDirty()
	{
		var sel = document.getElementById("EmailButton");
		var optin = document.getElementById("OKEMail");
		if( VerifyEmail( document.getElementById("Email").value ) )
		{
			if( optin.checked )
			{
				if( primaryMember )
				{						   
					sel.disabled = false;					 
				}
			}
			else
			{
					sel.disabled = true;
			}
			optin.disabled = false;
			SetDirty();
		}
		else
		{
			SetLabelRed( "EmailLabel" );
			//DisableSubmit();
			sel.disabled = true;
			optin.disabled = true;
		}

	}

	function PhoneDirty( label )
	{
		var p = document.getElementById("WorkPhone").value;
		var s = document.getElementById("HomePhone").value;
		var sel = document.getElementById("SMSButton");
		var optin = document.getElementById("OKSMS");
		if( p.substring(0,2 ) == '07' || s.substring(0,2 ) == '07'  )
		{
			if( primaryMember )
			{
				sel.disabled = false;
			}
			optin.disabled = false;
		}
		else
		{
			sel.disabled = true;
			optin.disabled = true;
		}
		UpdateField( label );
	}

	function SMSDirty()
	{
		var sel = document.getElementById("SMSButton");
		if( document.getElementById("OKSMS").checked )
		{
			if( primaryMember )
			{
				sel.disabled = false;
			}
		}
		else
		{
			sel.disabled = true;
		}
		SetDirty();
	}

	function Virgin600()
	{
		if( !dirty )
		{
			virginNo = document.getElementById("VirginNo").value;
			if( virginNo.length == 11 )
			{
				window.location = "Virgin600.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&VirginNo="+virginNo;
			}
			else
			{
				alert( "Invalid Virgin No" );
			}
		}
		else
		{
			alert( "You need to save the page first" );
		}
	}

	function UpdateDOB()
	{
		if( document.getElementsByName("DOB_y")[0].value != "" )
		{
		document.getElementsByName("DOB")[0].value =
		document.getElementsByName("DOB_y")[0].value;

		if( VerifyDOB( document.getElementsByName("DOB_y")[0].value ) )
		{
			SetDirty();
			ClearLabel( "DOBLabel" );
		}
		else
		{
			DisableSubmit();
			SetLabelRed( "DOBlabel" );

			alert( "Invalid Date"  );
		}
		}
	}
	
	function ShowOther()
	{
		var sel = document.getElementById("LTitle");
		var mem = sel.options[sel.selectedIndex].value;
		var other = document.getElementById("Other");
		var title = document.getElementById("Title");

		if( mem == "Other" )
		{
			other.disabled = false;
			title.value = other.value;
			other.style.display = 'inline';
		}
		else
		{
			other.value = "";
			other.disabled = true;
			other.style.display = 'none';
			title.value = mem;
		}
		ClearLabel( "TitleLabel" );
		SetDirty();
	}

	function SetDateField( cur, lable )
	{
		var sel = document.getElementById(lable);
		if( cur.checked )
		{
			sel.value = "<?php echo $TimeStamp; ?>";
		}
		else
		{
			sel.value = "";
		}
		SetDirty();
	}

	function RedempStopClick( but, lable )
	{
		if( but.checked )
		{
			document.getElementById("RedemptionStop").checked = true;
		}
		else
		{
			document.getElementById("RedemptionStop").checked = false;
		}
		SetDirty();
		SetDateField( but, lable );
		GetTrackingNotes();
	}

	function AwardStopClick( but, lable )
	{
		if( but.checked )
		{
			document.getElementById("AwardStop").checked = true;
			document.getElementById("RedemptionStop").checked = true;
		}
		else
		{
			document.getElementById("AwardStop").checked = false;
		}
	//	SetDirty();
		SetDateField( but, lable );
		GetTrackingNotes();
	}

	function AccountTypeChange()
	{
		SetDirty();
		if( document.getElementById("AccountType").value == 'G' )
		{
			document.getElementById("RedemptionStop").checked = true;
			document.getElementById("SwapableFields1").style.display="none";
			document.getElementById("SwapableFields2").style.display="";
		}
		else
		{
			if( document.getElementById("AccountType").value == 'D' )
			{
				event.returnValue = "You should enter Staff member's Site Code in the Home Site field.\n Press OK to save your changes.";
			}
			else
			{
			}
			document.getElementById("SwapableFields1").style.display="";
			document.getElementById("SwapableFields2").style.display="none";			
		}
	}

	function ShowQuestionaire()
	{
<?php
		echo "rval = window.showModalDialog( 'Questionaire.php?MemberNo=$MemberNo', 0, 'center:yes;resizable:no;status:no;dialogHeight:250px');";
?>
	}

	function GetTrackingNotes()
	{
		if( document.getElementById("AwardStop").checked )
		{
			<?php echo "rval = window.showModalDialog('ConfirmFraud.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1207', 0, 'center:yes;resizable:no;dialogHeight:250px');"; ?>
			if( rval )
			{
				RemoveDirty();
				<?php echo "window.location=\"LettersProcess.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1207&Refer=\"";?>+rval;
			}

		}
		else 
		{
			if( document.getElementById("RedemptionStop").checked )
			{

				<?php echo "rval = window.showModalDialog('ConfirmRedemptionStop.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1212', 0, 'center:yes;resizable:no;dialogHeight:300px');"; ?>
				if( rval )
				{
					RemoveDirty();
					<?php echo "window.location=\"LettersProcess.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1212&Refer=\"";?>+rval;
				}
			}
			else
			{
				<?php echo "rval = window.showModalDialog('ConfirmCleared.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1209', 0, 'center:yes;resizable:no;dialogHeight:250px');"; ?>
				if( rval )
				{
					RemoveDirty();
					<?php echo "window.location=\"LettersProcess.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&Code=1209&Refer=\"";?>+rval;
				}
			}
		}

		if( rval )
		{
			document.getElementById( "spareCode" ).value = rval[0];
			document.getElementById( "spareNotes" ).value = rval[1];
			document.getElementById("BigForm").submit();
		}
		return false;
	}

</script>

	<TABLE WIDTH=100% STYLE="background-color: BEIGE; font-size: medium"><tr>
<?php
		echo "<td style=\"text-align:right\" >Card No:</td><td style='color=";
		if ($AccountStatus == "Closed")
		{
			echo "gainsboro";
		}
		else 
		{
			echo "red";
		}
		echo "; font-size:small;'>";
		if( count( $AssocCards ) == 0)
		{
			echo "No Card Assigned";
		}
		else
		{
			if ( $CardNo != '' )
			{
				$sql = "SELECT AccountType, Logo, CardType FROM Accounts
				 JOIN Members USING ( AccountNo )
				  JOIN Cards USING ( MemberNo )
				   JOIN CardRanges USING( CardType ) WHERE CardNo = '".$CardNo."' LIMIT 1";
				
				$results = DBQueryExitOnFailure( $sql );

				$row = mysql_fetch_assoc($results);

				if ( $row['AccountType'] == 'G' && ($row['CardType'] == 'WEOU' or $row['CardType'] == 'StarRewards'))
				{
					$sql = "SELECT Logo FROM CardRanges WHERE CardType = 'Group'";
					$logo = DBSingleStatQuery( $sql );
				}
				else 
				{
					$logo = $row['Logo'];
				}
				echo $CardNo;
				if( mysql_num_rows($results) > 0 )
				{
					echo "  <IMG height=18 width=70 src=".$logo.">";
				}
				
			}

		}
		
		echo "<td style=\"text-align:right\"> Stars: </td><td> $DisplayTotal";
		if( $TotalStopped > 0 )
		{
			echo "($TotalStopped)";
		}

 		if ($Details["AccountType"] == "G")
		{
			echo "<span style=\"font-size: xx-small;\"> (Group Balance $GroupBalanceTotal)</span>";
		} 
		if( count( $AssocMembers ) > 1)
		{
			echo "<td style=\"text-align:right\">$AssociatedLabel:</td><td><select id=\"members\" onchange=\"DisplayAssociated()\">";
			DisplaySelectOptions( $AssocMembers, $MemberNo );
			echo "</select></td>";
		}
		else
		{
			echo "<td width=20%></td><td width=10%></td>";
		}

		echo "<td width=10%>";
		if ($AccountStatus == "Closed")
		{
			echo "<p align=center bgcolor=#ffffff><font color=#ff0000>CLOSED</font></p>";
		}
		else 
		{
			echo DisplaySegmentCode($Details["SegmentCode"]);
			echo "</a>";
		}
		echo "</td>";
		
	$emailEnabled = "disabled";
	$emailOptin = "disabled";
	if( $Details["Email"] != "" )
	{
		if( $Details["OKEMail"] == 'Y' )
		{
			$emailEnabled = "";
		}
		$emailOptin = "";
	}

	$smsOptin = "disabled";
	$smsEnabled = "disabled";

	if( (substr($Details["WorkPhone"], 0, 2 ) == "07" or substr($Details["HomePhone"], 0, 2) == "07" ))
	{
		if($Details["OKSMS"] == 'Y' )
		{
			$smsEnabled = "";
		}
		$smsOptin = "";
	}

?>

	</tr></table>


<?php
?>
	<form id="bigForm" action="MemberUpdate.php" method="POST">
	<input style="HEIGHT: 1px" name="CardNo" type="hidden" value="<?php echo $Details["CardNo"]; ?>">
	<input style="HEIGHT: 1px" name="MemberNo" type="hidden" value="<?php echo $Details["MemberNo"]; ?>">
	<input style="HEIGHT: 1px" name="AccountNo" type="hidden" value="<?php echo $Details["AccountNo"]; ?>">
	<table style="width=100%; text-align: top;">
	<tr style="vertical-align:top;">
	<td width=33%>
	<fieldset>
	<legend><?php echo $MemberLabel;?></legend>
		<table>
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "TitleLabel", $Details["Title"]=="") ?>Title<td>
			<select name=LTitle  onchange="ShowOther()" tabindex=1 >
<?php	DisplaySelectOptions( $Titles, $LTitle );	?>
			</select>
			<input name="Other" onchange="ShowOther()" size = 9 <?php echo $OTitle; ?> tabindex=1>
			<input name="Title" type=hidden value="<?php echo $Details["Title"]; ?>">
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "ForenameLabel", $Details["Forename"]=="") ?>Forename<td><input name="Forename"  tabindex=2 onchange='UpdateField("ForenameLabel")' value="<?php echo $Details["Forename"]; ?>">
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "SurnameLabel", $Details["Surname"]=="") ?>			Surname<td><input tabindex=3 name="Surname" onchange='UpdateField("SurnameLabel")' value="<?php echo $Details["Surname"]; ?>">
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "DOBLabel", $Details["DOB"]=="") ?>
			Year of Birth<td>
			<input tabindex=4 name="DOB_y"  onchange="UpdateDOB()" value="<?php echo $Details["DOB"]?>" maxlength=4 size=4><input type=hidden name="DOB" value="<?php echo $Details["DOB"]; ?>">
<tr><td class='FieldLabel'>Organisation<td>
<?php 

if ( $Action == "NewGroupMember" && $Group == "yes")
{
	$sql = "SELECT AccountNo, Comments FROM CardRanges JOIN Accounts USING( AccountNo ) WHERE AccountType = 'G'";
	$results = DBQueryExitOnFailure( $sql );
	echo "<select name=\"Organisation\" id='accounts'>";
	while( $row = mysql_fetch_assoc( $results ) )
	{
		if ( $row['AccountNo'] == $AccountNo )
		{
			echo "<option selected value='".$row['Comments']."'>".$row['Comments']."</option>";
		}
		else 
		{
			echo "<option value='".$row['Comments']."'>".$row['Comments']."</option>";
		}
	}
	echo "</select>";
}
else 
{
	echo "<input tabindex=5 name=\"Organisation\" onchange=\"SetDirty()\" value='". $Details['Organisation']."'>"; 	
}

?>



<tr>
<?php HighlightCellOn( "class='FieldLabel'", "AddressLabel", ($Details["Address1"]=="" && $Details["Address2"]=="" && $Details["Address3"]=="" && $Details["Address4"]=="" && $Details["Address5"]=="" ) || $Details["AddNdsVfy"] ) ?>
			Address<td><input  tabindex=6 name="Address1" size=25 onchange='UpdateField("AddressLabel")' value="<?php echo ereg_replace('\"','\'',$Details["Address1"]); ?>">
			<tr><td>
<?php
	if( $Details["AddNdsVfy"] == 1 )
	{
		echo "<span style=\"font-size: xx-small\">Verified</span>";
		DisplayCheckBox( "AddNdsVfy", false, "  tabindex=7 onclick='UpdateField(\"AddressLabel\")'" );
	}
?>

			<td><input tabindex=6 name="Address2"  size=30 onchange='UpdateField("AddressLabel")' value="<?php echo ereg_replace('\"','\'',$Details["Address2"]); ?>">
			<tr><td><td><input  tabindex=6  name="Address3"  size=30 onchange='UpdateField("AddressLabel")' value="<?php echo ereg_replace('\"','\'',$Details["Address3"]); ?>">
			<tr><td><td><input  tabindex=6  name="Address4"  size=30 onchange='UpdateField("AddressLabel")' value="<?php echo ereg_replace('\"','\'',$Details["Address4"]); ?>">
			<tr><td><td><input  tabindex=6  name="Address5"  size=30 onchange='UpdateField("AddressLabel")' value="<?php echo ereg_replace('\"','\'',$Details["Address5"]); ?>">
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "PostCodeLabel", $Details["PostCode"]=="") ?>
			Postcode<td><input  tabindex=6 name="PostCode"  onchange='UpdateField("PostCodeLabel")' maxlength=10 size=10 value="<?php echo $Details["PostCode"]; ?>">
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "WorkPhoneLabel", $Details["WorkNdsVfy"]==1) ?>
			Primary Phone<td  class="FieldLabel"><input   tabindex=7 name="WorkPhone"  onkeyup='PhoneDirty("WorkPhoneLabel")' onChange='PhoneDirty("WorkPhoneLabel")' size=20 value="<?php echo $Details["WorkPhone"];?>"><br>
			<?php DisplayCheckBox( "OKWorkPhone", $Details["OKWorkPhone"] == 'N', " tabindex=7 onclick='UpdateField(\"WorkPhoneLabel\")' ");?> No Calls
<?php
	if( $Details["WorkNdsVfy"] == 1 )
	{
		echo "<span style=\"font-size: xx-small; align:right;\">Verified</span>";
		DisplayCheckBox( "WorkNdsVfy", false, "  tabindex=7 onclick='UpdateField(\"WorkPhoneLabel\")'" );
	}
?>
			<tr>
<?php HighlightCellOn( "class='FieldLabel'", "HomePhoneLabel",  $Details["HomeNdsVfy"]==1 ) ?>
			Secondary Phone<td class="FieldLabel"><input  tabindex=8 name="HomePhone"  onkeyup='PhoneDirty("HomePhoneLabel")' onchange='PhoneDirty("HomePhoneLabel")' size=20 value="<?php echo $Details["HomePhone"]; ?>"><br>
			<?php DisplayCheckBox( "OKHomePhone", $Details["OKHomePhone"] == 'N', " tabindex=8 onclick='UpdateField(\"HomePhoneLabel\")' ");?> No Calls
<?php
	if( $Details["HomeNdsVfy"] == 1 )
	{
		echo "<span style=\"font-size: xx-small\">Verified</span>";
		DisplayCheckBox( "HomeNdsVfy", false, " tabindex=8 onclick='UpdateField(\"HomePhoneLabel\")'" );
	}
?>
	</table>
	</fieldset>
	
	
	<fieldset>
	<?php
	if ($Details["FraudStatus"] != "0" && $Details["FraudStatus"] != NULL )
	{
		?>	
			<legend>Fraud Investigation</legend><div align="center">
			<font size="2">
			<NOBR class="FieldLabel">No Action <?php DisplayRadioButton( "NoAction", "0", $Details["FraudStatus"], " tabindex=9 onclick=\"fraudstatus(0)\" $fraudstatus")?>
		
			</NOBR>&nbsp;
			<NOBR class="FieldLabel">Under Investigation <?php DisplayRadioButton( "UnderInvestigation", "1", $Details["FraudStatus"], " tabindex=9 onclick=\"fraudstatus(1)\" $fraudstatus")?>
		
			</NOBR><br>
			<NOBR class="FieldLabel">Previously Investigated <?php DisplayRadioButton( "PreviouslyInvestigated", "2", $Details["FraudStatus"], " tabindex=9 onclick=\"fraudstatus(2)\" $fraudstatus")?>
		
			</NOBR>&nbsp;
			<NOBR class="FieldLabel">Cleared <?php DisplayRadioButton( "Cleared", "3", $Details["FraudStatus"], " tabindex=9 onclick=\"fraudstatus(3)\" $fraudstatus")?>
		
			</NOBR>&nbsp;
			<NOBR class="FieldLabel">Fraud <?php DisplayRadioButton( "Fraud", "4", $Details["FraudStatus"], " tabindex=9 onclick=\"fraudstatus(4)\" $fraudstatus")?>
			</NOBR>
			</font>
		<?php
	}
	if ($AccountStatus == "Closed")
	{
		echo "<div align=\"center\"><nobr class=\"FieldLabel\"><button onclick=\"ReinstateAccount()\">Reinstate Account</div></button></nobr></div>";	
	}
	else 
	{
		echo "<div align=\"center\"><nobr class=\"FieldLabel\"><button onclick=\"CloseAccount()\">Close Account</div></button></nobr></div>";
	}
	?>
		
	</fieldset>
    </td>
	<td width=33%>
		<fieldset>
		<legend>Member Preferences</legend>
		<table style="width:100%">
			<tr><td class="FieldLabel">No Mail<td><?php DisplayCheckBox( "OKMail", $Details["OKMail"] == 'N', " tabindex=10 onclick=\"SetDirty()\" ");?>
			<td class="FieldLabel">Deceased<td><?php DisplayCheckBox( "Deceased", $Details["Deceased"] == 'Y', " tabindex=10 onclick=\"SetDirty()\"");?>
			<tr><td class="FieldLabel">No 3rd Party Mail<td><?php DisplayCheckBox( "TOKMail", $Details["TOKMail"] == 'N', " tabindex=10 onclick=\"SetDirty()\"");?>
			<td class="FieldLabel">Gone Away<td><?php DisplayCheckBox( "GoneAway", $Details["GoneAway"] == 'Y', " tabindex=10 onclick=\"SetDirty()\"");?>
			<tr><td class="FieldLabel">Email Opt In<td><?php DisplayCheckBox( "OKEMail", $Details["OKEMail"] == 'Y', " tabindex=10 onclick=\"EmailDirty()\" $emailOptin ");?>
			<td class="FieldLabel">SMS Opt In<td><?php DisplayCheckBox( "OKSMS", $Details["OKSMS"] == 'Y', " tabindex=10 onclick=\"SMSDirty()\" $smsOptin");?>
			<tr>
			<td colspan=5><Table><tr><td>
<?php HighlightCellOn( "class=\"FieldLabel\"", "PasswordLabel", $Details["PassNdsSet"] == 'Y' ) ?>
			Password <td width=100%><input type=password tabindex=11  name="Passwrd" id="Passwrd" onkeyPress="SetDirty()" value="<?php echo $Details["Passwrd"]; ?>" maxlength=30 style="width:100%">
			<tr><td>
<?php HighlightCellOn( "class=\"FieldLabel\"", "EmailLabel", $Details["EmailNdsVfy"] == 1 ) ?>
			Email <td><input tabindex=11  name="Email" id="Email" onkeyPress="EmailDirty()" value="<?php echo $Details["Email"]; ?>" maxlength=70 style="width:100%">
<?php
	if( $Details["EmailNdsVfy"] == 1 )
	{
		echo "<span style=\"font-size: xx-small\">Verified</span>";
		DisplayCheckBox( "EmailNdsVfy", false, " tabindex=11 onclick=\"SetDirty()\"" );
	}

?>
			</Table>
		</table>
		</fieldset>
		<fieldset>
		<legend>Account Information</legend>
		<TABLE>
			<tr><td class="FieldLabel">Home Site<td><input  tabindex=12  name="HomeSite" maxlength=6  id="HomeSite" <?php echo "value=\"$Details[HomeSite]\" $PrimaryOnly"; ?>  onchange="SetDirty()">
			<tr><td class="FieldLabel">Virgin No.<td><input  tabindex=13  name="VirginNo" id="VirginNo" <?php echo "value=\"$Details[VirginNo]\" $PrimaryOnly"; ?> size=12 onchange="SetDirty()">
			<tr><td colspan=2 class="FieldLabel"><SPAN style="width:40%">Redemption Stop</SPAN>
			<?php 
			
			if( $AccountStatus == "Closed" )
			{
				$CBData = "disabled";	
			}
			else 
			{
				$CBData = $PrimaryOnly;
			}
			DisplayCheckBox( "RedemptionStop", $Details["RedemptionStopDate"] != '', " tabindex=14  onclick=\"RedempStopClick( this, 'RdmStopDate')\" $CBData");
			echo "&nbsp;&nbsp;&nbsp;<input id=RdmStopDate value='$Details[RedemptionStopDate]' readonly style=width:40%>";
			echo "<tr><td colspan=2 class=FieldLabel><SPAN style=width:40%>Awards Stop</SPAN>";
			DisplayCheckBox( "AwardStop", $Details["AwardStopDate"] != "", "onclick=\"AwardStopClick( this, 'AwdStopDate')\" $CBData tabindex=14 ");
			echo "&nbsp;&nbsp;&nbsp;<input id=AwdStopDate value='$Details[AwardStopDate]' readonly style=width:40%>";
			?>

			<tr><td class="FieldLabel">UKFuels Card<td>
			<input tabindex=14 name="AccountCard" readonly maxlength=11 size=11 value=<?php echo "\"$UKFuelsAccountNo\" $PrimaryOnly"; ?>>
			<?php if($PrimaryCard){echo "<Button onclick=AddAccountCard()>Update</button>"; }?></td></tr>
			<tr><td class="FieldLabel">Account Type<td>
				<select name="AccountType" id="AccountType"  tabindex=14  onchange="AccountTypeChange()" >
				<?php 
				if ( $Staff == "yes" )
				{
					echo "<option selected value='D'>Staff Loyalty</option>" ;
				}
				else 
				if ( $Group == "yes" )
				{
					echo "<option selected value='G'>Group Loyalty</option>" ;
				}
				{
					echo $PrimaryOnly;
				}
				?>
<?php	DisplaySelectOptions( $AccountTypes, $Details["AccountType"] );	?>
				</select>
<?php
			if( $Details["AccountType"] == "G" )
			{
				$SwapableFields1 = 'style="display:none"';
				$SwapableFields2 = "";
			}
			else
			{
				$SwapableFields1 = "";
				$SwapableFields2 = 'style="display:none"';
			}
			echo "<tr id=SwapableFields1 $SwapableFields1>";
?>
			<td class="FieldLabel">Monthly Spend<td>
				<select name="MonthlySpend"  tabindex=14  onchange="SetDirty()" <?php echo $PrimaryOnly;?>>
<?php	DisplaySelectOptions( $MontlySpends, $Details["MonthlySpend"] );	?>
				</select>
<?php
#			<tr><td class="FieldLabel">Virgin No<td>
#			<input name="VirginNo" id="VirginNo" onchange="SetDirty()"
#			value=" echo $Details["VirginNo"]; " maxlength=10 size=10>

// alter table Members add column MemberType char;
			echo "<tr id=SwapableFields2 $SwapableFields2>";
?>
			<td class="FieldLabel">Member Type<td>
				<select name="MemberType"  tabindex=14  onchange="SetDirty()">
<?php	DisplaySelectOptions( $GroupMemberTypes, $Details["MemberType"] );	?>
				</select>
		</table>
		</fieldset>
<?php
	if( $Details["PrimaryMember"] == 'Y' )
	{
		echo "<div align=center style= \"filter: progid:DXImageTransform.Microsoft.Alpha(opacity=50);\">";
	}
	else
	{
		echo "<div align=center >";
	}
	if ( $Action == "NewAccount" && $Staff == "yes" )
	{
	?>
		<fieldset>
		<legend id=StatementLabel>Polo Shirt Size</legend>
		<NOBR class="FieldLabel">No <?php DisplayRadioButton( "PoloShirtSize", "N", 'N', " tabindex=15 onchange=\"SetDirty()\" $PoloShrtSize")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Small <?php DisplayRadioButton( "PoloShirtSize", "S", '', " tabindex=16 onchange=\"SetDirty()\" $PoloShrtSize")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Medium <?php DisplayRadioButton( "PoloShirtSize", "M", '', " tabindex=17 onchange=\"SetDirty()\" $PoloShrtSize")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Large <?php DisplayRadioButton( "PoloShirtSize", "L", '', " tabindex=18 onchange=\"SetDirty()\" $PoloShrtSize")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Extra Large <?php DisplayRadioButton( "PoloShirtSize", "XL", '', " tabindex=19 onchange=\"SetDirty()\" $PoloShrtSize")?>
		</NOBR>
		</fieldset>
	<?php 	
	}
	else 
	{
	?>
		<fieldset>
		<legend id=StatementLabel>Statement Preference</legend>
		<NOBR class="FieldLabel">None <?php DisplayRadioButton( "StatementPreference", "N", $Details["StatementPreference"], " tabindex=15 onchange=\"SetDirty()\" $statePref")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Post <?php DisplayRadioButton( "StatementPreference", "P", $Details["StatementPreference"], " tabindex=16 onchange=\"SetDirty()\" $statePref")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">Email <?php DisplayRadioButton( "StatementPreference", "E", $Details["StatementPreference"], " tabindex=17 id=\"EmailButton\" onchange=\"SetDirty()\" $emailEnabled $statePref")?>
		</NOBR>&nbsp;&nbsp;&nbsp;
		<NOBR class="FieldLabel">SMS <?php DisplayRadioButton( "StatementPreference", "S", $Details["StatementPreference"], " tabindex=18 id=\"SMSButton\" onchange=\"SetDirty()\" $smsEnabled $statePref")?>
		</NOBR>
		</fieldset>
	<?php 
	}
	?>
	</td>
	<td  width=33%>
	<fieldset>
	<legend>Card Information</legend>
		<table>
			<tr><td class="FieldLabel">Member's cards<td>
<?php
		if( count( $AssocCards ) > 0)
		{
			echo "<select id=\"cards\" onchange=\"DisplayCards()\">";
			DisplaySelectOptions( $AssocCards, $CardNo );
			echo "</select>";
		}
		else
		{
			echo "None";
		}
?>

			<tr><td class="FieldLabel">Last Swipe Location<td><input value="<?php echo $Details["LastSwipeLoc"]; ?>" readonly>
			<tr><td class="FieldLabel" align=right>Date<td><input value="<?php echo $Details["LastSwipeDate"]; ?>" readonly>
			<tr><td class="FieldLabel">Card Issue Date<td><input value="<?php echo $Details["IssueDate"]; ?>" readonly>
			<tr><td class="FieldLabel">Total cards on account<td><input value= "<?php echo $TotalCards; ?>" readonly></td>

		</TABLE>
	</fieldset>
	<fieldset>
	<legend>Last Actions</legend>
		<table style="font-size:xx-small"  height=220>
			<tr><th style="text-align: left">Date<th style="text-align: left">Description</tr>
<?php
	$c = 0;
	if( $AccountNo != "" )
	{
		while( $row = mysql_fetch_row( $TrackingHistory ) )
		{
			$c++;
			echo "<tr title=\"".ereg_replace('\"','\'',$row[4]).", $row[5]\"><td>$row[0]<td>$row[2] $row[3]\n";
//			echo "<tr title=\"".ereg_replace('\"','\'',$row[3]).", $row[4]\"><td>$row[0]<td>$row[1] $row[2]\n";
		}
	}
	while( $c < 10 )
	{
		$c++;
		echo "<tr><td>&nbsp;\n";
	}

?>
		</table>
	</fieldset>		

    <table align="center"><tbody>
      <tr><td>
          <p align="center">
<?php 
   if ($AccountStatus != "Closed")   // MRM 13 06 10 Disable button if account is closed 
	{
		echo "<input id=update type=Submit OnClick=\"RemoveDirty()\" value=\"Save Details\" disabled>";
	}
          
?>          
          </p></td></tr>
      <tr>
        <td><input id="ActionToDo" name="ActionToDo" type="hidden" ></td></tr></tbody></table>

	</table>

	<?php

	if( isset( $_GET["Action"] ))
	{
		echo "<input name=Action type=hidden value='$_GET[Action]' >";
	}
	if( isset( $_GET["Group"] ))
	{
		echo "<input name=Group type=hidden value='$_GET[Group]' >";
	}
	
?>

	</form>
<?php
	include "../MasterViewTail.inc";
?>