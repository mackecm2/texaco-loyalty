<?php
?>
<html>
<head>
<title>Login</title>
<link href="css/module.css" rel="stylesheet" type="text/css">
</head>
<body>
        <!-- This is the first screen when a user sees when he is not logged in -->
		<form action="Login.php" method="post">
        <center>
		<div style="color:red">The service is currently unavailable due to technical difficulties</div>

		<?php echo $msg;?>
        <table width="250" border="1" cellspacing="0" cellpadding="4" bordercolor="#000000" bordercolordark="#000000" bordercolorlight="#000000" bgcolor="#FFFFFF" style="border-collapse: collapse">
        <tr>
        <td class="updatecontent"><center>
        <!-- <form action="login.php" method="post"> -->
  <table width="60%"  border="0" align="center" cellpadding="3" cellspacing="3">
    <tr class="bodytext">
      <td align="right" valign="middle">user : &nbsp;</td>
      <td valign="middle"><input name="uname" maxlength="50" value=""></td>
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
</form>
</body>
</html>
