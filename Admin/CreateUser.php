<?php
	require '../include/Session.inc';
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/UserInterface.php";
	include "../DBInterface/PasswordInterface.php";

	$msg = "";
	if( !isset( $_POST["newUser"])  || !isset( $_POST["psswrd1"] )|| !isset( $_POST["psswrd2"]) || !isset( $_POST["userType"]) ) 
	{
		$msg = " ";
	}
	else
	{
		if( $_POST["psswrd1"] != $_POST["psswrd2"] )
		{
			$msg = "Passwords do not match";
		}
	}

	if( $msg == "" )
	{
		// Need to check for name clash
		if( !CheckNameClash( $_POST["newUser"] ) )
		{
			$msg = "You have a user name clash with an already exisitng user";
		}
		else
		{
			InsertUser( $db_user, $db_pass, $_POST["newUser"], $_POST["psswrd1"], $_POST["userType"] );

			header("Location: ManageUsers.php");
			exit();	
		}
	}

	if( $msg != "" )
	{
		$userTypes = GetGrpList( $db_user );

	$Title = "Add User";
	$currentPage = "User Manager";
	$cButton = "";
	include "../MasterViewHead.inc";
	include "ManagerButtons.php";

?>


<script>
	function FormVerify()
	{

		if( document.getElementById( "newUser").value.length < 4 )
		{
			alert( "User name is too short" );
			return false;
		}

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

<form action="CreateUser.php" method="post" onsubmit="return FormVerify();">

	<center>
<?php echo $msg; ?>
	<br>
	<table>
	<tr><td> User Name</td><td><input name="newUser" id="newUser"></td></tr>
	<tr><td> Password</td><td><input type="password" name="psswrd1" id="psswrd1"></td></tr>
	<tr><td> Repeat</td><td><input type="password" name="psswrd2" id="psswrd2"></td></tr>

	<tr>
	<td> User type</td>
	<td>
		<select name="userType">
		<?php DisplaySelectOptions( $userTypes, "" ); ?>
		</select>
	</td>
	</tr>
	<tr><td></td><td><input type="submit"></td></tr>
	</table>
</form>
	<button onclick="window.location='ManageUsers.php'">Cancel</button>

<?php
	echo "</table></center>";
	include "../MasterViewTail.inc";
}
?>