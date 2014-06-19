<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterfaceOld.php";

	
	function GetSearchResultsOld(  $cardNo, $postCode, $surname, $email, $limit )
	{
		$sql = "";
		$fields = "Members.AccountNo, Members.MemberNo,  DATE_FORMAT( DOB, '%d/%m/%y') as DOB, Title, Initials, Forename, Surname, Address1, Address2, PostCode, PrimaryMember, Balance"; 
		if( $cardNo != "" )
		{
			$sql = "select $fields, CardNo from CardsOLD left join Members using (MemberNo) left join Accounts using (AccountNo) where CardNo = '$cardNo'";
		}
		else 
		{
			$where = "";
			$and = "";
			if( $postCode != "" )
			{
				$where .= "$and Postcode Like '$postCode%'";
				$and = "and ";
			}
			if( $surname != "" )
			{
				$where .= "$and Surname = '$surname'";
				$and = "and ";
			}
			if( $email != "" )
			{
				$where .= "$and Email Like '$email%'";
				$and = "and ";
			}

			if( $where != "" )
			{
				$sql = "select $fields, PrimaryCard as CardNo, Balance from Members join Accounts using (AccountNo) where $where limit $limit";
			}
		}

		if( $sql != "" )
		{
			return DBQueryExitOnFailure( $sql );
		}
		else
		{
			return false;
		}
	}
	
	
	$sql = "";

	$cardNo = "7076550";
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
		$sql = "Select AccountNo, MemberNo from Members where AccountNo = $_POST[AccountNo] order by PrimaryMember DESC limit 1";
	}

	if( isset( $_POST["MemberNo"]) &&  $_POST["MemberNo"] != "" )
	{
		$alt = true;
		$sql = "Select AccountNo, MemberNo from Members where MemberNo = $_POST[MemberNo]";
	}


	$limit = 1000;

	if( $alt )
	{
		$results = DBQueryExitOnFailure( $sql );
	}
	else
	{
		$results = GetSearchResultsOld( $cardNumber, $postCode, $surname, $Email, $limit );	
	}

	$Options = false;
	if( $results )
	{
		$num_res =  mysql_num_rows( $results ); 
		if( $num_res == 0 )
		{
			if( $cardNumber != "" )
			{
				// We want to create the card (assuming the luhn is correct) then
				
				//CreateRawCard( $cardNumber );

				// We want to offer to link to an account or create a new one.

				$Options = 1;

			}
			$msg = "No Matching Records";
		}
		else if( $num_res == 1 )
		{
			$row = mysql_fetch_assoc( $results );
			if( $row["AccountNo"] == "" )
			{
				// We want to offer to link to an existing account
				// Or create a new account
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
				header("Location: DisplayMemberOLD.php?$params");
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
	ARSE
	<form action="SelectMemberOldCard.php" method="post">
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

 <?php
	 if( CheckPermisions(PermissionsExtraSearch) ) 
	 { 
?>
    <tr class="bodytext">
      <td align="right" valign="middle">AccountNo :</td>
      <td valign="middle"><input onkeyup="Enable()" name="AccountNo" maxlength="10"></td>
    </tr>

    <tr class="bodytext">
      <td align="right" valign="middle">MemberNo :</td>
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
		echo "<br><button onClick=\"window.location='CreateRawCard.php?CardNo=$cardNumber&Action=NewAccount'\">Create card and New Account</button>";
		echo "<br><button onClick=\"window.location='CreateRawCard.php?CardNo=$cardNumber&Action=LinkAccount'\">Create card and link to existing account</button>";		
	}
	else if( $Options == 2 )
	{
		echo "<br><button onClick=\"window.location='DisplayMemberOLD.php?CardNo=$cardNumber&Action=NewAccount'\">Create New Account</button>";
		echo "<br><button onClick=\"window.location='MergeMembers.php?CardNo=$cardNumber'\">Link to existing account</button>";
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
			if( strlen( $row["Forename"] ) > 0 )
			{
				$I = substr( $row["Forename"], 0, 1 );
			}
			else
			{
				$I = substr( $row["Initials"], 0, 1 );
			}
			
			echo "<tr $c onmouseover=\"this.style.backgroundColor='lavender'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"window.location='DisplayMemberOLD.php?AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]&Questions=true'\" $b >";
			echo "<td  width=25% >$prefix $row[CardNo]</td><td width=25%>$row[Title] $I $row[Surname] </td><td width=30%> $row[Address1] </td> <td  width=10%>$row[PostCode]</td>";
			echo "</tr>";
		}
		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	}
	include "../MasterViewTail.inc";
?>
