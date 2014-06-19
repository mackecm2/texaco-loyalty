<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	
	$sql = "Select * from CardRanges ORDER BY CardStart DESC";
	$results = DBQueryExitOnFailure( $sql );

	$Title = "Card Ranges";
	$currentPage = "Config";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	$HelpPage = "CardRanges";
	$cButton = "";
	$but = "CardRanges";
	$helpID = "CardRanges";
	include "../MasterViewHead.inc";
	include "ConfigButtons.php";	
	
	echo "<center><font size=2>";
	echo "<table width=95% align=center><tr><td width=15%><strong>AccountNo</strong></td><td width=15%><strong>CardType</strong></td><td width=10%><strong>Logo</strong></td><td width=15%><strong>CardStart</strong></td><td width=15%><strong>CardFinish</strong></td><td width=30%><strong>Comments</strong></td></tr>\n";

	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"window.location='UpdateCardRanges.php?ID=$row[ID]&Action=Display'\">";
		echo "<TD width=15%>$row[AccountNo]</td><td width=15%>$row[CardType]</td><td width=10%>";
		echo "<img border=0 hspace=0 src=../MemberScreens/$row[Logo] alt=$row[Logo] width=70 height=18></td>";	
		echo "</td><td width=15%><font size=2>$row[CardStart]</font></td><td width=15%><font size=2>$row[CardFinish]</font><td  width=30%>$row[Comments]</td>";
		echo "</tr>";
	}

	echo "</font></table>\n";
	echo $msg."<p>";
//	echo "<input value=\"Add New Range\" type=\"button\" name=\"AddCardRange\" onclick=\"window.location='UpdateCardRanges.php?ID=0'\">";
	echo "</center>";
	include "../MasterViewTail.inc";