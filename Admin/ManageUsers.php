<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/UserInterface.php";

	$all = isset($_GET["ShowAll"]) ;
	
	$results = GetManagedUsers( $all );

	$Title = "User Manager";
	$currentPage = "Manager";
	$cButton = "";
	$helpID = "ManageUsers";
	include "../MasterViewHead.inc";
	include "ManagerButtons.php";
?>

<script>
	function DeleteUser(row)
	{
		var user = row.firstChild.innerText;
		window.location = "DeleteUser.php?UserName=" + user;
	}
</script>
	<center>
	<table cellpadding = 10px><tr>
	<td><button onclick="window.location='CreateUser.php'">Create New User</button>
<?php
	if( !$all )
	{
		echo "<td><button onclick=\"window.location='ManageUsers.php?ShowAll=true'\">Show All</button>";
	}
	else
	{
		echo "<td><button onclick=\"window.location='ManageUsers.php'\">Show Active</button>";
	}
?>
	</table>
	</center>
<center>
<table>


<tr><th>User Name</th><th>User Type</th></tr>

<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		if( $row['DGrp'] == "Deleted" )
		{
			echo "<tr background=grey><td>$row[UserName]</td><td>$row[DGrp]</td>";
			echo "<td><button onclick=\"window.location='ChangeUserPassword.php?Change=Type&UserName=$row[UserName]'\">Re-enable user</button>";
			
			echo "</tr>\n";
		}
		else
		{
			echo "<tr><td>$row[UserName]</td><td>$row[DGrp]</td>";
			echo "<td><button onclick=\"window.location='ChangeUserPassword.php?Change=Type&UserName=$row[UserName]'\">Change User Type</button>";
			echo "<button onclick=\"window.location='ChangeUserPassword.php?Change=Password&UserName=$row[UserName]'\">Change Password</button>";
			echo "<button onclick=\"DeleteUser(this.parentNode.parentNode)\">Delete</button></td>";
			echo "</tr>\n";
		}
	}
	echo "</table></center>";
	include "../MasterViewTail.inc";
?>

