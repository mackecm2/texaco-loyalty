<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	$sql = "";
	
	$currentPage = "Search";
	$Title = "Group Loyalty Accounts";
	include "../MasterViewHead.inc";
	include "SearchPageButtons.inc";
	if( CheckPermisions(PermissionsGroupLoyalty) ) 
	{ 
?>
	<center>
	
<table style = "border-style:solid; border-color: blue" width="70%">
	<tr><td colspan=2 style="background-color: blue;color: white; text-align: center">Group Loyalty Accounts</td></tr>
	<tr>
	<td>
  <table width="100%"  margin = "3" border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td align="right" valign="middle">
      Add New Member to Existing Group Account
      </td>
      <td><form method="post" action="DisplayMember.php?&Action=NewGroupMember&Group=yes">
      <?php 
        $sql = "SELECT AccountNo, Comments FROM CardRanges JOIN Accounts USING( AccountNo ) WHERE AccountType = 'G'";
		$results = DBQueryExitOnFailure( $sql );
		echo "<select name=AccountNo id='accounts'>";
		while( $row = mysql_fetch_assoc( $results ) )
		{
			echo "<option value='".$row['AccountNo']."'>".$row['Comments']."</option>";
		}
		echo "</select>";
      ?>
	      <input value="Go" type="submit" name="submit"></td></form>
	</td>
	  <td>
    </tr><tr>
    <td></td></tr>
    <tr><td align="center" valign="middle">
    <form action="DisplayMember.php?Action=NewAccount&Group=yes" method="post">
	<input value="Create New Group Loyalty Account" type="submit" name="submit"></form>
    </tr>
	</table>
	<td>
	 </td>
    </tr>
  </table>
</form>

<?php
 }
	else 
	{
		echo "you do not have permission to use this facility.";
	}
	echo "</table>\n";
	echo "</div>\n";
	include "../MasterViewTail.inc";
?>
