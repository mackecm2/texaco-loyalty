<?php 
error_reporting(E_ALL);
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
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
	}
	

	if( isset( $_POST["postCode"] ) && $_POST["postCode"] != "" )
	{
		$postCode = $_POST["postCode"];
	}

	if( isset( $_POST["surname"] ) && $_POST["surname"] != "" )
	{
			$surname = $_POST["surname"];
	}

	if( isset( $_POST["Email"] ) && $_POST["Email"] != "" )
	{
			$Email = $_POST["Email"];
	}


	$limit = 1000;

	$results = GetSearchResults( $cardNumber, $postCode, $surname, $Email, $limit );	
	
	if( $results )
	{
		$num_res =  mysql_num_rows( $results ); 
		if( $num_res == 0 )
		{
			$msg = "No Matching Records";
		}
		else if( $num_res == 1 )
		{
			$row = mysql_fetch_assoc( $results );
			print_r( $row );
			$params = "AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]&Questions=true";
			if($cardNumber != "" )
			{
				$params .= "&CardNo=$cardNumber";
			}
			echo "Location: DisplayMember.php?$params";
			exit();
		}
		else if( $num_res == $limit )
		{
			$msg = "<B style=\"color:red\">Too many records match your search please refine your search</B>";
			$showMatches = false;			
		}
		else
		{
			$showMatches = true;			
		}

	}
	else
	{
		echo "Failed";
	}
	$currentPage = "Search";
	include "../MasterViewHead.inc";
	include "SearchPageButtons.inc";
?>
	<script>
		function ResetForm()
		{
			document.getElementsByName( "cardNumber" )[0].value = "7076550";
			document.getElementsByName( "surname" )[0].value = "";
			document.getElementsByName( "postCode" )[0].value = "";
			document.getElementsByName( "Email" )[0].value = "";
			document.getElementsByName( "submit" )[0].disabled = true;
		}
		function Enable()
		{
			if( document.getElementsByName( "cardNumber" )[0].value.length == 19
			  ||document.getElementsByName( "surname" )[0].value.length > 3 
			  ||document.getElementsByName( "postCode" )[0].value.length > 0 
			  ||document.getElementsByName( "Email" )[0].value.length > 5 )
			{
				document.getElementsByName( "submit" )[0].disabled = false;
			}
			else
			{
				document.getElementsByName( "submit" )[0].disabled = true;
			}
		}

	</script>
	<center>
	<form action="SelectMemberTest.php" method="post">
<table style = "border-style:solid; border-color: blue" width="70%">
	<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Search</td></tr>
	<tr>
	<td>
  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td align="right" valign="middle">Card Number :</td>
      <td valign="middle"><input onkeyup="Enable()" name="cardNumber" maxlength="19" value="<?php echo $cardNo;?>"></td>
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

	
	</table>
	<td>
      <input id=find name="submit" type="submit" class="bodytext" value="Find..." disabled>
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
	if( $showMatches )
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
			}
			else
			{
				$c = " style='color: red; font-size:smaller;' "; 
				$prefix = 'S';
			}

			echo "<tr $c onmouseover=\"this.style.backgroundColor='lavender'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"window.location='DisplayMember.php?AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]&Questions=true'\">";
			echo "<td  width=25%>$prefix $row[CardNo]</td><td width=25%>$row[Title] $row[Initials] $row[Surname] </td><td width=30%> $row[Address1] </td> <td  width=10%>$row[PostCode]</td>";
			echo "</tr>";
		}
		echo "</table>\n";
		echo "</div>\n";
		echo "<br>";
	}
	include "../MasterViewTail.inc";
?>
