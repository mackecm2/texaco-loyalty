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
		<form action="MergeMembers.php" method="post">
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
		<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Search for account to merge into Account <?php echo $AccountNo;?></td></tr>
		<tr>
		<td>
	  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
	    <tr class="bodytext">
	      <td align="right" valign="middle">Card Number :</td>
	      <td valign="middle"><input name="cardNumber" maxlength="20" size=21 value="<?php echo $cardNo;?>"></td>
	    </tr>
	    <tr class="bodytext">
	      <td align="right" valign="middle">Surname :</td>
	      <td valign="middle"><input name="surname" maxlength="20"  size=21  value="<?php echo $surname;?>"></td>
	    </tr>
	    <tr class="bodytext">
	      <td align="right" valign="middle">Postcode :</td>
	      <td valign="middle"><input name="postCode" maxlength="10"  size=21  value="<?php echo $postCode;?>"></td>
	    </tr>
		</table>
		<td>
	      <input type="submit" value="Find..." onclick="Find()">
		  &nbsp;<Button onclick="ResetForm()">Reset</Button>
		  &nbsp;<Button onclick="CreateCard()">Add Card</button>
		  &nbsp;<Button onclick="Cancel()">Cancel</button>
		 </td>
	    </tr>
	  </table>
	</form>
	<Button onclick="Advanced()">Advanced</button>
	<Button onclick="GroupLoyalty()">Group Loyalty</button>
<?php
	}
	if( $msg != "" )
	{
		echo "<center>$msg</center>";
	}

	if( $showMatches )
	{
		echo "<table width=95%><tr><th width=25%>Card No.</th><th width=25%>Name</th><th width=30%>Address</th><th width=10%>Postcode</th></tr>\n";
		echo "</table>\n";
		echo "<div style=\"width:95%; height:200; border-style:inset; background-color: white; overflow:auto\">";
		echo "<table width=100%>\n";
		
		//$fields = "MemberNo, AccountNo, DOB, Initials, GenderCode, Surname, Address1, Address2, 

		while( $row = mysql_fetch_assoc( $resultsM ) )
		{
			if( $row["AccountNo"] == $AccountNo )
			{
				echo "<tr style=\"color: red ;  font-size:smaller;\">";
				$prefix = '&nbsp;';
			}
			else if( $row[Status] == "Closed" )
			{
				echo "<tr style=\"color: purple;  background-color: orange; font-size:smaller;\">";
				$prefix = '&nbsp;';
			}
			else 
			{
				if( $row["PrimaryMember"] == 'Y' )
					{
						$c= " style=' font-size:smaller;' ";
						$prefix = 'P';
					}
					else
					{
						$c = " style='color: red; font-size:smaller;' "; 
						$prefix = 'S';
					}
	
				echo "<tr $c onmouseover=\"this.style.backgroundColor='lavender'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"SelectMember( $row[AccountNo], $row[MemberNo], $row[CardNo])\">\n";
			}
			echo "<td  width=25%>$prefix $row[CardNo]</td><td width=25%>$row[Title] $row[Initials] $row[Surname] </td><td width=30%> $row[Address1] </td> <td  width=10%>$row[PostCode]</td>";
			echo "</tr>";
		}

			
		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	}
	include "../MasterViewTail.inc";
?>
