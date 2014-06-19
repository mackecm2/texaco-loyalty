<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	$sql = "";
	
// leading zero knocked off CardNo below - MRM 09/07

	$cardNo = "7076550200";
	$postCode = "";
	$surname = "";
	$Email = "";
	$showMatches = false;
	$msg = "";
	$cardNumber = "";
	if( isset( $_POST["cardNumber"] ) && strlen($_POST["cardNumber"]) == 19 )
	{
		$cardNumber = $_POST["cardNumber"];
		$cardNo = $cardNumber;
	}
	
	if( isset( $_POST["postCode"] ) && $_POST["postCode"] != "" )
	{
		$postCode =  mysql_real_escape_string($_POST["postCode"])	;
	}

	if( isset( $_POST["surname"] ) && $_POST["surname"] != "" )
	{
		$surname = mysql_real_escape_string($_POST["surname"]);
	}

	if( isset( $_POST["Email"] ) && $_POST["Email"] != "" )
	{
		$Email =  mysql_real_escape_string($_POST["Email"]);
	}


	$alt = false;
	if( isset( $_POST["AccountNo"]) &&  $_POST["AccountNo"] != "" )
	{
		$alt = true;
		$sql = "Select AccountNo, MemberNo from Members where AccountNo = $_POST[AccountNo] order by PrimaryMember ASC limit 1";
	}

	if( isset( $_POST["MemberNo"]) &&  $_POST["MemberNo"] != "" )
	{
		$alt = true;
		$sql = "Select AccountNo, MemberNo from Members where MemberNo = $_POST[MemberNo]";
	}
	if( isset( $_POST["StaffID"]) &&  $_POST["StaffID"] != "" )
	{
		$alt = true;
		$sql = "Select AccountNo, MemberNo from Members where StaffID = $_POST[StaffID] AND PrimaryCard LIKE '01%'";
	}

	$limit = 1000;

	if( $alt )
	{
		$results = DBQueryExitOnFailure( $sql );
	}
	else
	{
		$results = GetSearchResults( $cardNumber, $postCode, $surname, $Email, $limit );	
	}

	$Options = false;
	if( $results )
	{
		$num_res =  mysql_num_rows( $results ); 
		if( $num_res == 0 )
		{
			if( $cardNumber != "" )
			{
				$CardType = CardRangeCheck( $cardNumber );
				if ( $CardType == "Unknown" )
				{
					$msg = "Card Number is out of range - please check";
				}
				else 
				{
				// We want to create the card (assuming the luhn is correct) then
				
				// We want to offer to link to an account or create a new one.

					$sql = "SELECT CardType, AccountNo AS GroupAccountNo FROM CardRanges WHERE CardStart <= '$cardNumber' AND CardFinish >= '$cardNumber'";
					$results = DBQueryExitOnFailure( $sql );
					$numrows = mysql_num_rows($results);
					if( $numrows >0 )
					{
						$row = mysql_fetch_assoc( $results );
						$CardType = $row['CardType'];
						$GroupAccountNo = $row['GroupAccountNo'];
					}
					$Options = 1;
				}


			}
			if (!$GroupAccountNo && $CardType != "Unknown" )
			{
				$msg = "No Matching Records";
			}

		}
		else if( $num_res == 1 )
		{
			$row = mysql_fetch_assoc( $results );
			if( $row["AccountNo"] == "" )
			{
				// We want to offer to link to an existing account
				// Or create a new account
				$CardType = "WEOU"; // The default
				$sql = "SELECT CardType, AccountNo AS GroupAccountNo FROM CardRanges WHERE CardStart <= '$cardNumber' AND CardFinish >= '$cardNumber'";
				$results = DBQueryExitOnFailure( $sql );
				$numrows = mysql_num_rows($results);
				if( $numrows >0 )
				{
					$row = mysql_fetch_assoc( $results );
					$CardType = $row['CardType'];
					if ( $row['GroupAccountNo'] )
					{
						$GroupAccountNo = $row['GroupAccountNo'];
					}
				}
				$Options = 2;
			}
			else
			{
				// Go to the account found
				$params = "AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]&Questions=true";
				if($cardNumber != "" )
				{
					$params .= "&CardNo=$cardNumber";
				}
				header("Location: DisplayMember.php?$params");
				exit();
			}
		}
		else if( $num_res >= $limit )
		{
			$msg = "<B style=\"color:red\">Too many records match your search please refine your search</B>";
			$showMatches = false;			
		}
		else
		{
			$showMatches = true;			
		}

	}	
	$bodyControl = "onload='SetFocus()'";
	$currentPage = "Search";
	$Title = "Search Card Holders";
	include "../MasterViewHead.inc";
	include "SearchPageButtons.inc";
?>
	<script>
		function setSelectionRange(input, selectionStart, selectionEnd) 
		{  
			if (input.setSelectionRange)  
			{
				input.focus();
				input.setSelectionRange(selectionStart, selectionEnd);
			}
			else if (input.createTextRange)
			{
				var range = input.createTextRange();
				range.collapse(true);
				range.moveEnd('character', selectionEnd);
				range.moveStart('character', selectionStart);
				range.select();
			}
		}

	function setCursorToEnd (input)
	{
		setSelectionRange(input, input.value.length, input.value.length); 
	}
		function SetFocus()
		{
			s = document.forms[0].cardNumber;
			s.focus();
			setCursorToEnd( s );
		}

		function ResetForm()
		{
			document.getElementsByName( "cardNumber" )[0].value = "7076550";
			document.getElementsByName( "surname" )[0].value = "";
			document.getElementsByName( "postCode" )[0].value = "";
			document.getElementsByName( "Email" )[0].value = "";
			document.getElementById( "submit" ).disabled = true;
		}
		function Enable()
		{
			if( ( document.getElementsByName( "cardNumber" )[0].value.length == 19 
				&& luhnCheck(document.getElementsByName( "cardNumber" )[0].value))
			  ||document.getElementsByName( "surname" )[0].value.length > 3 
			  ||document.getElementsByName( "postCode" )[0].value.length > 0 
			  ||document.getElementsByName( "Email" )[0].value.length > 5 
			  ||(document.getElementsByName( "AccountNo" )[0] != null && document.getElementsByName( "AccountNo" )[0].value.length > 0 )
			  ||(document.getElementsByName( "MemberNo" )[0] != null && document.getElementsByName( "MemberNo" )[0].value.length > 0 )
			  ||(document.getElementsByName( "StaffID" )[0] != null && document.getElementsByName( "StaffID" )[0].value.length > 0 )
				)
			{
				document.getElementById( "submit" ).disabled = false;
				if( event.keyCode == 13 )
				{
					SubmitForm();
				}
			}
			else
			{
				document.getElementById( "submit" ).disabled = true;
			}
		}

		function SubmitForm()
		{
			document.forms[0].submit();
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
			if( sum % 10 == 0 )
			{
				document.getElementById( "nolable" ).style.color = "black";
			}
			else
			{
				document.getElementById( "nolable" ).style.color = "red";
			}
			return (sum % 10 == 0);
		}

	</script>
	<center>
	<form action="SelectMember.php" method="post">
<table style = "border-style:solid; border-color: blue" width="70%">
	<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Search</td></tr>
	<tr>
	<td>
  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td id=nolable align="right" valign="middle">Card Number :</td>
      <td valign="middle"><input onkeyup="Enable()" onselect="Enable()" name="cardNumber" maxlength="19" value="<?php echo $cardNo;?>" ></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Surname :</td>
      <td valign="middle"><input onkeyup="Enable()" name="surname" maxlength="20"  value="<?php echo $surname;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Postcode :</td>
      <td valign="middle"><input onkeyup="Enable()" name="postCode" maxlength="10"  value="<?php echo $postCode;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Email :</td>
      <td valign="middle"><input onkeyup="Enable()" name="Email" maxlength="70"  value="<?php echo $Email;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">Staff ID :</td>
      <td valign="middle"><input onkeyup="Enable()" name="StaffID" maxlength="10"></td>
    </tr>
 <?php
	 if( CheckPermisions(PermissionsExtraSearch) ) 
	 { 
?>
    <tr class="bodytext">
      <td align="right" valign="middle">Account No :</td>
      <td valign="middle"><input onkeyup="Enable()" name="AccountNo" maxlength="10"></td>
    </tr>

    <tr class="bodytext">
      <td align="right" valign="middle">Member No :</td>
      <td valign="middle"><input onkeyup="Enable()" name="MemberNo" maxlength="10"></td>
    </tr>
    


 <?php } ?>
	
	</table>
	<td>
      <Button disabled onclick='SubmitForm()' id="Submit">Find...</Button>
	  &nbsp;<Button onclick="ResetForm()">Reset</Button>
	 </td>
    </tr>
  </table>
</form>

<?php
	if( $msg != "" )
	{
		echo "<center>$msg</center>";
	}
	if( $Options == 1 )
	{
		if( $GroupAccountNo )
		{
			echo "<center>Group Loyalty Account</center><br>";
			echo "<br><button onClick=\"window.location='MergeGroupMembers.php?CardNo=$cardNumber&Action=LinkGLC&AccountNo=$GroupAccountNo'\">Create Card and link to Group Account</button>";
		}
		else 
		{
			echo "<br><button onClick=\"window.location='CreateRawCard.php?CardNo=$cardNumber&Action=NewAccount'\">Create card and New Account</button>";
			echo "<br><button onClick=\"window.location='CreateRawCard.php?CardNo=$cardNumber&Action=LinkAccount'\">Create card and link to existing account</button>";		
		}
	}
	else if( $Options == 2 )
	{
		if( $GroupAccountNo )
		{
			echo "<center>Group Loyalty Account</center><br>";
			echo "<br><button onClick=\"window.location='MergeGroupMembers.php?CardNo=$cardNumber&Action=LinkGLC&AccountNo=$GroupAccountNo'\">Create Card and link to Group Account</button>";
		}
		else 
		{
			echo "<br><button onClick=\"window.location='DisplayMember.php?CardNo=$cardNumber&Action=NewAccount'\">Create New Account</button>";
			echo "<br><button onClick=\"window.location='MergeMembers.php?CardNo=$cardNumber'\">Link to existing account</button>";
		}

	}
	else if( $showMatches )
	{
		echo "<table width=95%><tr><th width=25%>Card No.</th><th width=25%>Name</th><th width=30%>Address</th><th width=10%>Postcode</th></tr>\n";
		echo "</table>\n";
		echo "<div style=\"width:95%; height:330; border-style:inset; background-color: white; overflow:auto\">";
		echo "<table width=100%>";
		
		//$fields = "MemberNo, AccountNo, DOB, Initials, GenderCode, Surname, Address1, Address2, 

		while( $row = mysql_fetch_assoc( $results ) )
		{
			if( $row[Status] == "Closed" )
			{
				$c = " style='color: purple; background-color: orange; font-size:smaller;' "; 
				$bg = "orange";
				$prefix = 'X';
				$b = "title='Closed Account'";
			}
			else
			{
				$bg = "";
				if( $row["PrimaryMember"] == 'Y' )
				{
					$c= " style=' font-size:smaller;' ";
					$prefix = 'P';
					$b = "title='Current Balance $row[Balance]'";
				}
				else
				{
					$c = " style='color: red; font-size:smaller;' "; 
					$prefix = 'S';
					$b = "";
				}				
			}
		
			
			if( strlen( $row["Forename"] ) > 0 )
			{
				$I = substr( $row["Forename"], 0, 1 );
			}
			else
			{
				$I = substr( $row["Initials"], 0, 1 );
			}
			
			echo "<tr $c onmouseover=\"this.style.backgroundColor='lavender'\" onmouseleave=\"this.style.backgroundColor='$bg'\" onClick=\"window.location='DisplayMember.php?AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]";
			if( $row[Status] == "Open" )
			{
				echo "&Questions=true'\" $b >";
			}
			else 
			{
				echo "'\" $b >";
			}
			echo "<td  width=25% >$prefix $row[CardNo]</td><td width=25%>$row[Title] $I $row[Surname] </td><td width=30%> $row[Address1] </td> <td  width=10%>$row[PostCode]</td>";
			echo "</tr>";
		}
		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	}
	include "../MasterViewTail.inc";
?>