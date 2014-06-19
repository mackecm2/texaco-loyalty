<?php
	require '../include/Session.inc';
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/UserInterface.php";

	if( !isset( $_GET["UserName"] ) )
	{
		$sUser = "";
		$selectableUser = true; 
		$users = GetUserList( $db_user );
		$grp = "";
	}
	else
	{
		$sUser = $_GET["UserName"];
		$selectableUser = false; 
		$grp = GetUserGrp( $sUser, $db_user );
	}

	if( isset( $_GET["Change"] ) && $_GET["Change"] == "Type" )
	{
		$changeType = true;
		$userTypes = GetGrpList( $db_user );
	}
	else
	{
		$changeType = false;
	}
	if( $changeType )
	{
		$Title = "Change User Type";
	}
	else
	{
		$Title = "Change User Password";
	}
	$currentPage = "Manager";
	include "../MasterViewHead.inc";
	$cButton = "";
	include "ManagerButtons.php";
?>

<script>
	function FormVerify()
	{
		if( document.getElementById( "psswrd1").value.length < 6 )
		{
			alert( "Password is too short" );
			return false;
		}

		if( (document.getElementById( "psswrd1").value != document.getElementById( "psswrd2").value ) )
		{
			alert( "Passwords do not match" );
			return false;
		}

		return true;
	}

</script>
<center>
	<form action="ChangeUserPasswordProcess.php" method="post" onsubmit="return FormVerify();">
	<table>
	<tr>
	<td> User</td>
	<td>
<?php
		if( $selectableUser )
		{
			echo "<select name=\"username\">\n";
			DisplaySelectOptions( $users, "" );  
			echo "</select>\n";
		}
		else
		{
			echo "<input readonly name=\"username\" value=\"$sUser\">\n";
		}
?>
	</td>
	</tr>
<?php
		if( $changeType )
		{
?>
	<tr>
	<td>User Group</td>
	<td>
		<select name="usertype">
		<?php DisplaySelectOptions( $userTypes, $grp ); ?>
		</select>
	</td></tr>
<?php
		}
?>
	<tr><td> New Password</td><td><input type="password" name="psswrd1" id="psswrd1"></td></tr>
	<tr><td> Repeat</td><td><input type="password" name="psswrd2" id="psswrd2"></td></tr>
	<tr><td></td><td><input type="submit" value="Enter"></td></tr>
	</table>
</form>
	<button onclick="window.location='ManageUsers.php'">Cancel</button>

<?PHP
	echo "</table></center>";
	include "../MasterViewTail.inc";
?>
