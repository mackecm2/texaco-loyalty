<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

	Function DisplayBlock( $AN, $MN, $CN, $Caption, $Advanced )
	{
		echo "<td width = 45%>\n";
		echo "<center>$Caption</center>\n";
		$sql = "";
		$AwardStop = 1;
		$RedeemStop = 'N';


		if( $AN != "" )
		{
			$DragDropFunction = "ondrop=\"dropMember($AN)\"";
			$sql = "select Title, Forename, Surname, DOB, Address1, PostCode, Members.AccountNo, Members.MemberNo, CardNo, (isnull(AwardStopDate) or AwardStopDate = '0000-00-00') as AwardStop, AwardStopDate, (isnull(RedemptionStopDate) or RedemptionStopDate = '0000-00-00')  as RedemptionStop, RedemptionStopDate, PrimaryMember  from Accounts Join Members using(AccountNo) left Join Cards using(MemberNo) where Members.AccountNo=$AN order by Members.MemberNo, Cards.CardNo";
		}
		else if( $CN != "" )
		{
			$DragDropFunction = "";
			$sql = "select '' as MemberNo, '' as AccountNo, 1 as AwardStop, 1 as RedemptionStop, 'N' as PrimaryMember, CardNo from Cards where CardNo = '$CN'";
		}
		else
		{
			$DragDropFunction = "ondrop=\"UnmergeMember()\"";
		}

		echo "<div ondragenter=\"enterAccount()\" $DragDropFunction ondragover=\"overDragAccount()\" style=\"width:100%; height:150; border-style:inset; background-color: white; overflow:auto\">";

		if( $sql != "" )
		{
			$results = mysql_query( $sql );
			if( !$results )
			{
				$errorStr = mysql_error();
				include "../include/NoPermission.php";
				exit();
			}

			
			echo "<table>\n";
			$CurrentMember = "";
			$MembersCount = 0;
			while( $row = mysql_fetch_assoc( $results ) )
			{
				if( $CurrentMember != $row["MemberNo"] ) 
				{
					$MembersCount++;
					$CurrentMember = $row["MemberNo"];
					if( $row["PrimaryMember"] == 'Y' )
					{
						$moveable = "false";
						$color = "style='color:black'";
					}
					else
					{
						$moveable = "true";
						$color = "style='color:red'";
					}
					echo "<tr>";
					echo "<td $color colspan=2 ondragstart=\"startMemberDrag( $row[AccountNo], $CurrentMember, '$row[CardNo]', $moveable)\" ondragenter=\"enterMember()\" ondragover=\"overDragMember()\" ondrop=\"dropCard($CurrentMember)\">";
					echo "$row[Title] $row[Forename] $row[Surname] <td>$row[Address1] $row[PostCode]\n" ;
				}
				if( $row["CardNo"] != "" )
				{
					echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;";
					echo "<td ondragstart=\"startCardDrag($CurrentMember, '$row[CardNo]')\">\n" ;
					echo "$row[CardNo]\n";
				}
				else
				{
					echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<td>No Card Assigned\n" ;
				}
				$AwardStop = $row["AwardStop"];
				$RedeemStop = $row["RedemptionStop"];
			}
			echo "<input type=hidden id=\"MemberCount\" value=$MembersCount>\n";
			echo "</table>\n";
		}
		echo "</div>\n";

		echo "<table style=\"width: 100%; background-color: orange; color: white;\"><TR>";
		echo "<td style=\"width:30%; text-align: left\">Stops";
		echo "<td style=\"width:30%; text-align: left\">Redeem";
			DisplayCheckBox( "r1", $RedeemStop == 'Y', "disabled" );
		echo "<td style=\" text-align: left\">Awards";
			DisplayCheckBox( "a1", $AwardStop == 0, "disabled" );
		echo "</table>";
	}

	$Advanced = true;

	if( isset( $_GET["AccountNo"] ) )
	{
		$AccountNo = $_GET["AccountNo"];
	}
	else if( isset( $_POST["AccountNo"] ))
	{
		$AccountNo = $_POST["AccountNo"];
	}
	else
	{
		$AccountNo = "";
	}

	if( isset( $_GET["MemberNo"] ) )
	{
		$MemberNo = $_GET["MemberNo"];
	}
	else if( isset( $_POST["MemberNo"] ))
	{
		$MemberNo = $_POST["MemberNo"];
	}
	else
	{
		$MemberNo = "";
	}

	if( isset( $_GET["CardNo"] ) )
	{
		$CardNo = $_GET["CardNo"];
	}
	else if( isset( $_POST["CardNo"] ))
	{
		$CardNo = $_POST["CardNo"];
	}
	else
	{
		$CardNo = "";
	}

	if( isset( $_GET["SAccountNo"] ) )
	{
		$SAccountNo = $_GET["SAccountNo"];
	}
	else if( isset( $_POST["SAccountNo"] ))
	{
		$SAccountNo = $_POST["SAccountNo"];
	}
	else
	{
		$SAccountNo = "";
	}

	if( isset( $_GET["SMemberNo"] ) )
	{
		$SMemberNo = $_GET["SMemberNo"];
	}
	else if( isset( $_POST["SMemberNo"] ))
	{
		$SMemberNo = $_POST["SMemberNo"];
	}
	else
	{
		$SMemberNo = "";
	}

	if( isset( $_GET["SCardNo"] ) )
	{
		$SCardNo = $_GET["SCardNo"];
	}
	else if( isset( $_POST["SCardNo"] ))
	{
		$SCardNo = $_POST["SCardNo"];
	}
	else
	{
		$SCardNo = "";
	}

	if( isset( $_POST["Action"] ) )
	{
		$Action = $_POST["Action"];
	}
	else
	{
		$Action = "";
	}

	// Assume sanity checks done before call
	switch( $Action )
	{
		case "AddCardToMember":
			MergeCardToMember( $_POST["Card"], $_POST["Member"], true );
			break;
		case "UnmergeMember":
			$SAccountNo = UnmergeMember( $_POST["Member"], $_POST["Account"]  );
			$SMemberNo = $_POST["Member"];
			break;
		case "MergeMember":
			MoveMember( $_POST["Account"], $_POST["Member"] );
			break;
		case "DeleteMember":
			DeleteMemberFromAccount( $_POST["Member"] );
			break;
		case "First2Second":
			$MemberNo = $_POST['SMemberNo'];
			$AccountNo = $_POST['SAccountNo'];
			$CardNo = $_POST['SCardNo'];
			$SMemberNo = $_POST['MemberNo'];
			$SAccountNo = $_POST['AccountNo'];
			$SCardNo = $_POST['CardNo'];
			// This should fall though
		case "Second2First":
			if( $AccountNo != "" && $SAccountNo != "" )
			{
				// Begin Transaction
				MergeAccounts( $AccountNo, $SAccountNo ); 		
				// End Transaction
			}
			else if( $MemberNo != "" && $SCardNo != "" )
			{
				// Begin Transaction
				MergeCardToMember( $SCardNo, $MemberNo, false );		
				// End Transaction
			}
			$SMemberNo = "";
			$SAccountNo = "";
			$SCardNo = "";
		break;
	}
 
	$cardNo = "7076550";
	$postCode = "";
	$surname = "";
	$showMatches = false;
	$msg = "";
	$cardNumber = "";

	if( isset( $_POST["cardNumber"] ) && strlen($_POST["cardNumber"]) == 19 )
	{
		$cardNumber = $_POST["cardNumber"];
	}
	
	if( isset( $_POST["postCode"] ) && $_POST["postCode"] != "" )
	{
		$postCode = $_POST["postCode"];
	}

	if( isset( $_POST["surname"] ) && $_POST["surname"] != "" )
	{
			$surname = $_POST["surname"];
	}

	$limit = 1000;

	$resultsM = GetSearchResults( $cardNumber, $postCode, $surname, "", $limit );	

	if( $resultsM )
	{
		$num_res = mysql_num_rows( $resultsM );
		if( $num_res == 0 )
		{
			$SAccountNo="";
			$SMemberNo="";

			$msg = "No Matching Records";
		}
		else if( $num_res == 1 )
		{
			$row = mysql_fetch_assoc( $resultsM );
			$SAccountNo=$row["AccountNo"];
			$SMemberNo=$row["MemberNo"];
			if($cardNumber != "" )
			{
				$SCardNo=$cardNo;
			}
		//	header("Location: DisplayMember.php?$params");
		//	exit();
		}
		else if( $num_res >= 1000 )
		{
			$showMatches = true;		
			$msg = "Too many records match your search please refine your search";
		}
		else
		{
			$showMatches = true;			
		}
	}	
	$currentPage = "Card Holder";
	include "../MasterViewHead.inc";
?>
	<script>
		function ResetForm()
		{
			document.getElementsByName( "cardNumber" )[0].value = "7076550";
			document.getElementsByName( "surname" )[0].value = "";
			document.getElementsByName( "postCode" )[0].value = "";
		}

		function SelectMember( AccountNo, MemberNo )
		{
			document.getElementById( "SAccountNo" ).value = AccountNo;
			document.getElementById( "SMemberNo" ).value = MemberNo;
			document.forms[0].submit();
		}

		function Find()
		{
			document.getElementById( "SAccountNo" ).value = "";
			document.getElementById( "SMemberNo" ).value = "";
			document.getElementById( "SCardNo" ).value = "";
			document.forms[0].submit();
		}

		function Merge( direction )
		{
			document.getElementById( "Action" ).value = direction;
			document.forms[0].submit();
		}

		function CreateCard()
		{
			document.forms[0].action = "CreateCard.php";
			document.forms[0].submit();
		}

		var srcObj;
		var dragType = 0;
		var srcAccount;
		var srcMember;
		var srcCard;

		var deleteable = false;
		function enterMember()
		{
			if( dragType == 2 )
			{
				window.event.dataTransfer.dropEffect  = "move";

			}
		}

		function enterAccount()
		{	
			if( dragType == 1 )
			{
				window.event.dataTransfer.dropEffect  = "move";
			}
		}

		function enterBin()
		{
			if( deleteable )
			{
				window.event.dataTransfer.dropEffect  = "move";
			}
		}

		function startMemberDrag( accountno, memberno, cardno, dragable )
		{
			if( dragable )
			{
				dragType = 1;
				srcAccount = accountno;
				srcMember = memberno;
				if( cardno != '' )
				{
					deleteable = false;
				}
				else
				{
					deleteable = true;
				}
				srcObj = window.event.srcElement;
				window.event.dataTransfer.effectAllowed = "move";
			}
		}

		function startCardDrag( memberno, cardno )
		{
			srcMember = memberno;
			srcCard = cardno;
			deleteable = false;
			dragType = 2;
			srcObj = window.event.srcElement;
			window.event.dataTransfer.effectAllowed = "move";
		}

		function dropMember( accountno )
		{
			if( dragType == 1 && accountno != srcAccount)
			{
				window.event.returnValue = false;
				document.getElementById( "Action" ).value = "MergeMember";
				document.getElementById( "Account" ).value = accountno;
				document.getElementById( "Member" ).value = srcMember;
				document.forms[0].submit();
			}
		}

		function UnmergeMember( )
		{
			if( dragType == 1 && document.getElementById( "MemberCount" ).value > 1 )
			{
				window.event.returnValue = false;
				document.getElementById( "Action" ).value = "UnmergeMember";
				document.getElementById( "Account" ).value = srcAccount;
				document.getElementById( "Member" ).value = srcMember;
				document.forms[0].submit();
			}
			else
			{
				alert( "Not allowed to unmerge when single member in account" );
			}
		}


		function dropCard( memberno )
		{
			if( dragType == 2 && srcMember != memberno )
			{
				window.event.returnValue = false;
				document.getElementById( "Action" ).value = "AddCardToMember";
				document.getElementById( "Card" ).value = srcCard;
				document.getElementById( "Member" ).value = memberno;
				document.forms[0].submit();
			}
		}

		function dropBin()
		{
			if( deleteable )
			{
				window.event.returnValue = false;
				document.getElementById( "Action" ).value = "DeleteMember";
				document.getElementById( "Account" ).value = srcAccount;
				document.getElementById( "Member" ).value = srcMember;
				document.forms[0].submit();				
			}
		}

		function overDragAccount() 
		{
			// tell onOverDrag handler not to do anything:
			if( dragType == 1 )
			{
				window.event.returnValue = false;
			}
		}

		function overDragMember()
		{
			if( dragType == 2 )
			{
				window.event.returnValue = false;
			}
		}

		function overBin()
		{
			if( deleteable )
			{
				window.event.returnValue = false;
			}
		}

		function back()
		{
<?php
		if( $AccountNo != "" )
		{
			echo "window.location = 'DisplayMember.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo';";
		}
		else
		{
			echo "window.location = 'SelectMember.php';";
		}
?>

		}
	
	</script>
	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
<?php
	echo "<Table width=100%><tr>\n";
	DisplayBlock( $AccountNo, $MemberNo, $CardNo, "First Account", $Advanced  );
	echo "<td width=10%><center>\n";

	echo "<button onclick='return back()'>Back</button>";
	echo "<br><br><Button onclick=\"Merge('Second2First')\" title=\"Add second account member(s) to first account\"";
	if( $AccountNo == "" )
	{
		echo "disabled";
	}
	echo ">&lt;&lt;</Button>\n";
	echo "<BR><BR><Button onclick=\"Merge('First2Second')\" title=\"Add first account member(s) to second account\"";
	if( $SAccountNo == "" )
	{
		echo "disabled";
	}
	echo ">&gt;&gt;</Button>\n";
	if( $Advanced )
	{
		echo "<Br><br><img src=\"trash.gif\" ondragover=\"overBin()\" ondrop=\"dropBin()\"></center>";
	}
	DisplayBlock( $SAccountNo, $SMemberNo, $SCardNo, "Second Account", $Advanced   );
	echo "</table>";
?>
	<center>
	<form action="AdvancedMergeMembers.php" method="post">
<?php
	echo "<input type=\"hidden\" id=\"AccountNo\" name=\"AccountNo\" value=\"$AccountNo\">";
	echo "<input type=\"hidden\" id=\"MemberNo\" name=\"MemberNo\" value=\"$MemberNo\">";
	echo "<input type=\"hidden\" id=\"CardNo\" name=\"CardNo\" value=\"$CardNo\">";
	echo "<input type=\"hidden\" id=\"SAccountNo\" name=\"SAccountNo\" value=\"$SAccountNo\">";
	echo "<input type=\"hidden\" id=\"SMemberNo\" name=\"SMemberNo\" value=\"$SMemberNo\">";
	echo "<input type=\"hidden\" id=\"SCardNo\" name=\"SCardNo\" value=\"$SCardNo\">";

	echo "<input type=\"hidden\" id=\"Account\" name=\"Account\" >";
	echo "<input type=\"hidden\" id=\"Member\" name=\"Member\" >";
	echo "<input type=\"hidden\" id=\"Card\" name=\"Card\" >";
	echo "<input type=\"hidden\" id=\"Action\" name=\"Action\">";
?>

<table style = "border-style:solid; border-color: blue" width="70%">
	<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Search for second account to merge</td></tr>
	<tr>
	<td>
  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td align="right" valign="middle">Card Number :</td>
      <td valign="middle"><input name="cardNumber" maxlength="20" value="<?php echo $cardNo;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Surname :</td>
      <td valign="middle"><input name="surname" maxlength="20"  value="<?php echo $surname;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Postcode :</td>
      <td valign="middle"><input name="postCode" maxlength="10"  value="<?php echo $postCode;?>"></td>
    </tr>
	</table>
	<td>
      <Button onclick="Find()">Find...</button>
	  &nbsp;<Button onclick="ResetForm()">Reset</Button>
	  &nbsp;<Button onclick="CreateCard()">Create</button>
	 </td>
    </tr>
  </table>
</form>

<?php
	if( $msg != "" )
	{
		echo "<center>$msg</center>";
	}

	if( $showMatches )
	{
		echo "<div style=\"width:95%; height:200; border-style:inset; background-color: white; overflow:auto\">";
		echo "<table width=100%><tr><th width=25%>Name</th><th  width=10%>Postcode</th><th  width=21%>Card No.</th><th  width=40%>Address</th></tr>\n";
		
		//$fields = "MemberNo, AccountNo, DOB, Initials, GenderCode, Surname, Address1, Address2, 

		while( $row = mysql_fetch_assoc( $resultsM ) )
		{
			if( $row["AccountNo"] == $AccountNo )
			{
				echo "<tr style=\"color: red ;\">";
				echo "<td>$row[Title] $row[Forename] $row[Surname] </td><td>$row[PostCode]</td><td>$row[CardNo]</td><td>$row[Address1] $row[Address2]</td>";
				echo "</tr>";
			}
			
			else if( $row[Status] == "Closed" )
			{
				echo "<tr style=\"color: purple;  background-color: orange;\">";
				echo "<td>$row[Title] $row[Forename] $row[Surname] </td><td>$row[PostCode]</td><td>$row[CardNo]</td><td>$row[Address1] $row[Address2]</td>";
			}
			else
			{
				echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"SelectMember($row[AccountNo],$row[MemberNo])\">";
				echo "<td>$row[Title] $row[Forename] $row[Surname] </td><td>$row[PostCode]</td><td>$row[CardNo]</td><td>$row[Address1] $row[Address2]</td>";
				echo "</tr>";
			}
		}

		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	}
	include "../MasterViewTail.inc";
?>
