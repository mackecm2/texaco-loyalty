<?php 
error_reporting(E_ALL);

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/MemberInterface.php";
	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

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
	if( isset( $_REQUEST["Action"] ) )
	{
		$Action = $_REQUEST["Action"];
	}
	else
	{
		$Action = "";
	}
/*	if( isset( $_GET["Action"] ) )
	{
		$Action = $_GET["Action"];
	}
*/
	// Assume sanity checks done before call
//	var_dump($_GET);
//	echo "Action is ".$Action;
	switch( $Action )
	{
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
			else if( $SMemberNo != "" && $CardNo != "" )
			{
				// Begin Transaction
				MergeCardToMember( $CardNo, $SMemberNo, false );
				$AccountNo = $SAccountNo;
				$MemberNo = $SMemberNo;
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
	$StaffCard = false;
	$msg = "";
	if ( substr($CardNo,0,2) == '01')
	{
		$StaffCard = true;
		$msg = "<table style = 'border-style:solid; border-color: blue' width=70%>
	<tr><td colspan=2 style='background-color: blue;color: white; text-align: center'>You cannot merge a Staff Incentive Account</td></tr>
	<tr>
	<td align=center height=100>
      <button onclick='return Cancel()'>Back</button>
	</td>
    </tr>
  </table>";
	}
	$cardNumber = "";

	if( isset( $_POST["cardNumber"] ) && strlen($_POST["cardNumber"]) > 18 )
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
		else if( $num_res >= $limit )
		{
			$showMatches = false;	
			$msg = "<B style=\"color:red\">Too many records match your search please refine your search</B>";
		}
		else if ( $StaffCard )
		{
			$msg = "<p> <p>You cannot merge a Staff Incentive Account</p></p>";
		}
		else 
		{
			$showMatches = true;			
		}
	}	
	$Title = "Merge Cards";
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

		function SelectMember( AccountNo, MemberNo, CardNo )
		{
			document.forms[0].submit();
			rval = window.showModalDialog('ConfirmMerge.php?AccountNo=' + AccountNo + '&MemberNo=' + MemberNo +'&CardNo='+ CardNo, 0, 'center:yes;resizable:no;dialogHeight:450px');
			if( rval )
			{
				document.getElementById( "SAccountNo" ).value = AccountNo;
				document.getElementById( "SMemberNo" ).value = MemberNo;
				document.getElementById( "SCardNo" ).value = CardNo;
				document.getElementById( "Action" ).value = "Second2First";
				document.forms[0].submit();
				return true;
			}
		}

		function Find()
		{
			document.getElementById( "SAccountNo" ).value = "";
			document.getElementById( "SMemberNo" ).value = "";
			document.getElementById( "SCardNo" ).value = "";
			document.forms[0].submit();
		}

		function Cancel( )
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

		function Advanced()
		{
			window.location='AdvancedMergeMembers.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>';
		}

		function GroupLoyalty()
		{
			window.location='MergeMembersIntoGroup.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>';
		}

		
		function luhnCheck( CardNumber ) 
		{
			if (isNaN(CardNumber)) 
			{
				return false;
			}

			var no_digit = CardNumber.length;
			var oddoeven = no_digit & 1;
			var sum = 0;

			for (var count = 0; count < no_digit; count++) 
			{
				var digit = parseInt(CardNumber.charAt(count));
				if (!((count & 1) ^ oddoeven)) 
				{
					digit *= 2;
					if (digit > 9)
					{
						// very cleverly adds in one as well
						digit -= 9;
					}
				}
				sum += digit;
			}
			return (sum % 10 == 0);
		}


		function CreateCard()
		{
			cardNumber = document.getElementsByName( "cardNumber" )[0].value; 
			if( cardNumber.length > 18 )
			{
				if( luhnCheck( cardNumber) )
				{
					document.forms[0].action = "CreateCard.php";
					document.forms[0].submit();
				}
				else
				{
					alert( "Invalid card number" );
				}
			}
			else
			{
				alert( "Invalid Card number length" );
			}
		}

	</script>
	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
<?php
	if ( !$StaffCard )
	{
?>
		<Br>
		<center>
		<form action="MergeGroupMembers.php" method="post">
<?php
		echo "<input type=\"hidden\" id=\"AccountNo\" name=\"AccountNo\" value=\"2325212\">";
		echo "<input type=\"hidden\" id=\"MemberNo\" name=\"MemberNo\" value=\"$MemberNo\">";
		echo "<input type=\"hidden\" id=\"CardNo\" name=\"CardNo\" value=\"$CardNo\">";
		echo "<input type=\"hidden\" id=\"SAccountNo\" name=\"SAccountNo\" value=\"$AccountNo\">";
		echo "<input type=\"hidden\" id=\"SMemberNo\" name=\"SMemberNo\" value=\"$MemberNo\">";
		echo "<input type=\"hidden\" id=\"SCardNo\" name=\"SCardNo\" value=\"$CardNo\">";
	
		echo "<input type=\"hidden\" id=\"Account\" name=\"Account\" >";
		echo "<input type=\"hidden\" id=\"Member\" name=\"Member\" >";
		echo "<input type=\"hidden\" id=\"Card\" name=\"Card\" >";
		echo "<input type=\"hidden\" id=\"Action\" name=\"Action\">";
		

		
?>
	
	<table style = "border-style:solid; border-color: blue" width="70%">
		<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Merge Card into Group Loyalty Account</td></tr>
		<tr>
		<td>
	  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
	    <tr class="bodytext">
	      <td align="right" valign="middle">Account :</td>
	      <td valign="middle">
	      <?php 
	      $sql = "SELECT AccountNo, Comments FROM CardRanges JOIN Accounts USING( AccountNo ) WHERE AccountType = 'G'";
			$results = DBQueryExitOnFailure( $sql );
			echo "<select id='accounts' name=AccountNo>";
			while( $row = mysql_fetch_assoc( $results ) )
			{
				echo "<option value='".$row['AccountNo']."' > ".$row['Comments']."</option>";
			}
			echo "</select>";
	      ?>
		</td>
		<td>
	      <input type="submit" value="Merge Card">
		  &nbsp;<Button onclick="Cancel()">Cancel</button>
		 </td>
	    </tr>
	  </table>
	</form>
	</center>
<?php
	}
	if( $msg != "" )
	{
		echo "<center>$msg</center>";
	}

			
		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	include "../MasterViewTail.inc";
?>
