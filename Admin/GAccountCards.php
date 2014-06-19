<?php
	#	This script lists all of the UKF Account Cards set up in the Database.
	#	The user can click the individual records to jump to the DisplayMember screen.

	include "../include/Session.inc";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/AccountCardsInterface.php";

	if( isset($_GET["SortBy"]))
	{
		$SortBy = $_GET["SortBy"];
	}
	else
	{
		$SortBy = "Surname";
	}

	#	Lets load up our accountcard records
	$results = GetAccountCards("", $SortBy);



	$Title = "UK Fuels Accounts Manager";
	$currentPage = "Config";
	$cButton = "";
	$but="GAccount";
	$HelpPage = "CardRequests";
	include "../MasterViewHead.inc";
	include "ConfigButtons.php";



?>
<script>
	function ShowMember( membernumber,cardno,accountno )
	{
		window.location="../MemberScreens/DisplayMember.php?&MemberNo=" + membernumber + "&CardNo=" + cardno + "&AccountNo=" + accountno;
	}

	function SortBy( field )
	{
		window.location="../Admin/GAccountCards.php?&SortBy=" + field; 
	}
</script>
<center>
<TABLE>
<TR><TH>UKFuels Account Cards<br>&nbsp;
<TR valign=top><TD>
<table>


<tr>
<th OnClick="SortBy('GAccountNo')" title="Click to sort">UKFuels AccountNo</th>
<th OnClick="SortBy('CardNo')"  title="Click to sort">Star Rewards Card</th>
<th align="left" OnClick="SortBy('Surname')"  title="Click to sort">Name</th>
<th OnClick="SortBy('Address1')"  title="Click to sort">Company</th></tr>

<?php


	if ($row = mysql_fetch_array($results))
	{

		do {

			echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"ShowMember('$row[MemberNo]','$row[CardNo]','$row[AccountNo]')\">";

			# echo "<td align = \"center\">$row[GAccountNo]</td> <td>$row[CardNo]</td><td align=\center\">$row[Title]</td><td align=\"left\">$row[Forename]</td><td align=\"left\">$row[Surname]</td>";
			echo "<td align = \"center\">$row[GAccountNo]</td> <td>$row[CardNo]</td><td align=\center\">$row[Name]</td><td>$row[Address1]</td>";

			echo "</tr>\n";
		} while($row = mysql_fetch_array($results));

	}
	ECHO "</table>";

	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

