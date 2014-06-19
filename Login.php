<?php
    /* database connect script. */
//	session_start();
	require "include/Locations.php";
	require "include/DB.inc";
	include "include/CacheCntl.inc";
	include "DBInterface/PasswordInterface.php";
	include "include/ServerName.inc";


	$uname = "";
	$msg = "";
	
	// special db_user and db_pass for test system on rsm4 ...............MikeM     18/04/08   //
	
	switch ($SERVER_NAME_FOR_ALL) {
    case "TEST":
        $db_user = "pma001";
		$db_pass = "amping";
		$env = "test - test - test - test - test - test - test - test - test - test - test - test - test - test - test";
        break;
    case "DEMO":
        $db_user = "pma001";
		$db_pass = "amping";
		$env = "demo - demo - demo - demo - demo - demo - demo - demo - demo - demo - demo - demo - demo - demo - demo";
        break;
    default:
       	$db_user = "UserCheck";
		$db_pass = "";
	}
	
	
	connectToDB( MasterServer, TexacoDB );

	if( isset($_POST['uname']) && isset( $_POST['passwd'] ))
	{    // if form has been submitted
        if (!get_magic_quotes_gpc())
        {
            $uname = addslashes($_POST['uname']);
			$pass  = $_POST['passwd'];
        }
		else
		{
			$uname = $_POST['uname'];
			$pass = stripslashes($_POST['passwd']);
		}

        //$pass = md5($pass);
		$result = DatabaseLogin( $uname, $pass );
		if( $result == -1 )
		{
			$msg = "Incorrect user or password";
		}
		else if( $result == -2 )
		{
			$msg = "This user/password has expired";
		}
		else
		{
		// if we get here username and password are correct,
		// register session variables and set last login time.

			UpdateLastLogin( $uname, $pass );

			$DaysLeft = PasswordDaysLeft( $uname, $_SESSION['grp'] );



			if( $DaysLeft < 3 )
			{
				header("Location: Admin\\ChangePassword.php");
			}
			else
			{
				header("Location: MemberScreens\\SelectMember.php");
			}
			exit();
		}
    }


	unset( $_SESSION['username'] );
	unset( $_SESSION['grp'] );
	unset( $_SESSION['grpPass']);
	unset( $_SESSION['userPerms']);
?>
<html>
<head>
<title>Login</title>
<link href="css/module.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
   function formfocus() {
      document.getElementById('loginelement').focus();
   }
   window.onload = formfocus;
</script>
</head>
<body>
        <!-- This is the first screen when a user sees when he is not logged in -->
        <form action="Login.php" method="post">
        <center>

		<?php echo $msg;?>
        <table width="250" border="1" cellspacing="0" cellpadding="4" bordercolor="#000000" bordercolordark="#000000" bordercolorlight="#000000" bgcolor="#FFFFFF" style="border-collapse: collapse">
        <tr>
        <td class="updatecontent"><center>
        <!-- <form action="login.php" method="post"> -->
  <table width="60%"  border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td align="right" valign="middle">user : &nbsp;</td>
      <td valign="middle"><input id="loginelement" name="uname" maxlength="50" value="<?php echo $uname;?>"></td>
    </tr>
    <tr class="bodytext">
      <td align="right" valign="middle">password : &nbsp;</td>
      <td valign="middle"><input type="password" name="passwd" maxlength="50"></td>
    </tr>
    <tr>
      <td align="right">&nbsp;</td>
      <td><input name="submit" type="submit" class="bodytext" value="Login"></td>
    </tr>
  </table>
</TR>
</table>
<p align=center><? echo $env; ?></p>
</form>
</body>
</html>
