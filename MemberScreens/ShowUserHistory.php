<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/UserInterface.php";

	//$sql = "Delete from UserActions where DateDiff( now(), CreationDate ) > 1";  
	ClearUserHistory();

	$results = GetUserHistory( $uname );
	
	$Title = "Latest Accounts";
	$currentPage = "Search";
	include "../MasterViewHead.inc";
	include "SearchPageButtons.inc";
?>

<?php
		

		echo "<center>";
		echo "<table width=100% ><tr><th width=20%>Card No.</th><th width=20%>Name</th><th width=10%>Time</th><th width=30%>Address</th><th width=10%>Postcode</th><th width=3%>&nbsp;</th></tr>\n";
		echo "</table>\n";
		
		echo "<div style=\"width:95%; height:300px; border-style:inset; background-color: white; overflow:auto\"><table width=100% >";
		while( $row = mysql_fetch_assoc( $results ) )
		{
			echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onClick=\"window.location='DisplayMember.php?AccountNo=$row[AccountNo]&MemberNo=$row[MemberNo]'\">";
			echo "<TD  width=20%>$row[PrimaryCard]<td td width=20%>$row[Title] $row[Initials] $row[Surname] </td><td  width=10%>$row[UDate]</td><td width=30%>$row[Address1]<td  width=10%>$row[PostCode]</td>";
			echo "</tr>";
		}

		echo "</table>\n";
		echo "</div></center>";
	include "../MasterViewTail.inc";

?>