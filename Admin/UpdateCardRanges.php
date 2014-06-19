<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
//	include "../DBInterface/CardInterface.php";

function check_input($value)
{
// Stripslashes
if (get_magic_quotes_gpc())
  {
  $value = stripslashes($value);
  }
// Quote if not a number
if (!is_numeric($value))
  {
  $value = "'" . mysql_real_escape_string($value) . "'";
  }
return $value;
}

	$Title = "Card Ranges";
	$currentPage = "Config";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	$HelpPage = "CardRanges";
	$cButton = "";
	$but = "CardRanges";
	$helpID = "CardRanges";
	include "../MasterViewHead.inc";
	include "ConfigButtons.php";
	
	if ( isset($_GET["ID"]) )
	{
		if ( $_GET["ID"] != 0 )
		{
			$ID = $_GET["ID"];
			$sql = "Select * from CardRanges where ID = $ID LIMIT 1";
			$results = DBQueryExitOnFailure( $sql );
			$row = mysql_fetch_assoc( $results ); 
			$accountno = $row["AccountNo"];
			$cardtype = $row["CardType"];
			$logo = $row["Logo"];
			$cardstart = $row["CardStart"];
			$cardfinish = $row["CardFinish"];
			$comments = $row["Comments"];
			$success = "NotYet";
		}
		else
		{
			$success = "AddMe";
		}
	}
	if ( isset($_GET["ID"]) && $_GET["Action"] == "update" ) 
	{
		/* V A L I D A T I O N */
		$valid = "yes";
		/* ------- Account Number ------- */
		if (isset($_POST[AccountNo]) && $_POST[AccountNo] != '' )
		{
			$sql = "SELECT AccountNo FROM Accounts WHERE AccountNo = $accountno";
			$results = DBQueryExitOnFailure( $sql );
			$numrows = mysql_num_rows($results);
			if ($numrows != 1)
			{
				$valid = "no";
				$emsg = "Invalid Account Number";
			}
		}

		/* ------- Card Start ------- */
		if (strlen($_POST[CardStart]) != 19 && strlen($_POST[CardStart]) != 0 && $valid == "yes")
		{
			$valid = "no";
			$emsg = "Invalid Start Card Number - must be 19 chars long";
		}
		if (!is_numeric($_POST[CardStart]) && strlen($_POST[CardStart]) != 0 && $valid == "yes")
		{
			$valid = "no";
			$emsg = "Invalid Start Card Number - must be numeric";
		}
		/* ------- Card Finish ------- */
		if (strlen($_POST[CardFinish]) != 19 && strlen($_POST[CardFinish]) != 0 && $valid == "yes")
		{
			$valid = "no";
			$emsg = "Invalid Finish Card Number - must be 19 chars long";
		}
		if (!is_numeric($_POST[CardFinish]) && strlen($_POST[CardFinish]) != 0 && $valid == "yes")
		{
			$valid = "no";
			$emsg = "Invalid Finish Card Number - must be numeric";
		}
		if ($_POST[CardFinish] < $_POST[CardStart] && $valid == "yes")
		{
			$valid = "no";
			$emsg = "Invalid Finish Card Number - must be greater than the start number";
		}
		/* ------- Card Range Overlap ------- */
		if ($valid == "yes" && strlen($_POST[CardFinish]) != 0 && strlen($_POST[CardFinish]) != 0)
		{
			$sql2 = "Select CardStart, CardFinish from CardRanges where ID <> $ID and (CardStart <> '' OR CardFinish <> '')";
			$results2 = DBQueryExitOnFailure( $sql2 );
			while( ($row2 = mysql_fetch_assoc( $results2 )) && $valid == "yes" )
			{
				if ('$row2[CardStart]' > '$_POST[CardFinish]' or '$row2[CardFinish]' < '$_POST[CardStart]')
				{
					$valid == "yes";
				}
				else
				{
					$valid = "no";
					$emsg = "Number Range overlaps with existing card range - please check";
				}
			}
		}

		
		
		
		/* V A L I D A T I O N   E N D S */
		
		if ( $valid == "yes" )
		{
			$sql = "Update CardRanges SET Logo = '$_POST[Logo]', CardStart = '$_POST[CardStart]',
				CardFinish = '$_POST[CardFinish]', Comments = ".check_input($_POST[Comments]).", LastUpdate = NOW( )  WHERE ID = $_POST[updateid]";
			$results = DBQueryExitOnFailure( $sql );
			$success = "Yes";
		}	
		else 
		{
			$success = "NotYet";
		}
			$accountno = $_POST["AccountNo"];
			$cardtype = $_POST["CardType"];
			$logo = $_POST["Logo"];
			$cardstart = $_POST["CardStart"];
			$cardfinish = $_POST["CardFinish"];
			$comments = $_POST["Comments"];
			

		
	}

	
	
	echo "<center>";
	
 	if ( $success == "AddMe" )
 	{
 		echo "<strong>Add New Card Range</strong><p>";
 		echo "<table width=95% align=center><tr><td>";
 		echo "<FORM method=POST name=UpdateRange action=AddCardRange.php>";
 	}
 	else 
 	{
 		echo "<strong>Modify Card Range</strong><p>";
 		echo "<table width=95% align=center><tr><td>";
 		echo"<FORM method=POST name=UpdateRange action=UpdateCardRanges.php?ID=$ID&Action=update>";
 	}

    echo "<p><table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"80%\" align=\"center\">";
    echo "<tbody>";
    echo "<input name=updateid id=updateid type=hidden value=$ID>";
    echo "<input name=AccountNo id=AccountNo type=hidden value=$accountno>";
    echo "<input name=CardType id=CardType type=hidden value=$cardtype>";
    echo "<tr><td><strong>AccountNo</strong></td><td>$accountno</td><td>*optional field</td></tr>";
    echo "<tr><td><strong>CardType</strong></td><td>$cardtype</td><td></td></tr>";
    echo "<tr><td><strong>Logo</strong></td><td><input name=Logo value=$logo>&nbsp;&nbsp;";
    if ( $success != "AddMe" )
    {
    	echo "<img border=0 hspace=0 src=../MemberScreens/$logo alt=$logo width=70 height=18></td>";
    }
   	echo "<td>*the filename of the logo</td></tr>";
    echo "<tr><td><strong>CardStart</strong></td><td><input name=CardStart value=$cardstart></td><td>*optional field</td></tr>";
    echo "<tr><td><strong>CardFinish</strong></td><td><input name=CardFinish value=$cardfinish></td><td>*optional field</td></tr>";
    echo "<tr><td><strong>Comments</strong></td><td><input style=\"WIDTH: 241px; HEIGHT: 22px\" size=31  name=Comments value=\"$comments\"></td><td></td>";
 	echo "</tr></tbody></table></p>";
	echo "<p><table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"80%\" align=\"center\">";
	if ( $success == "NotYet" or $success == "Yes" )
	{
		$label = "Update";
	}
	if ( $success == "AddMe" )
	{
		$label = "Add";
	}
	echo "<tr><td><p align=center><input value=$label type=\"submit\" name=\"submit\">";
	if ( $success == "Yes" )
	{
		echo "</p></td></tr><p><tr><td>$cardtype Card Range updated.";
	}
	if ( $_GET["Action"] == "Added" )
	{
		echo "</p></td></tr><p><tr><td>$cardtype Card Range added.";
	}
	if ( $success == "NotYet" )
	{
		echo "</p></td></tr><p><tr><td>$emsg";
	}
	echo "</p></td></tr></p></table>";

	echo "</form>";
	echo "</table>";
	echo "</center>";
	include "../MasterViewTail.inc";
?>