<?php
 /*
	 Create Table OldPassword ( UserID int, EncPassWrd char( 50 ), CreationDate timestamp, Primary key( UserID, EncPassWrd )  );
	 Grant SELECT, INSERT, DELETE, UPDATE on texaco.OldPassword to SAdmin@localhost;
	 Grant SELECT, INSERT, DELETE, UPDATE on texaco.OldPassword to DAdmin@localhost;
	 Grant SELECT, INSERT on texaco.OldPassword to DBasic@localhost;
	 Grant SELECT, INSERT, DELETE, UPDATE on texaco.OldPassword to MAdmin@localhost;
	 Grant SELECT, INSERT on texaco.OldPassword to MBasic@localhost;
	 Grant SELECT, INSERT, DELETE, UPDATE on texaco.OldPassword to RAdmin@localhost;
	 Grant SELECT, INSERT on texaco.OldPassword to RBasic@localhost;
	 Grant SELECT, INSERT, DELETE, UPDATE on texaco.OldPassword to UAdmin@localhost;
	 Grant SELECT, INSERT on texaco.OldPassword to UBasic@localhost;

*/
	function DatabaseLogin( $username, $password )
	{

		$encPass = md5( $password );

       		$sql = "SELECT Grp, EncGrpPass, Permissions, (LastLogin <= PasswordExpire or LastLogin is null) as Expired,email FROM Users WHERE UserName = '$username' and EncPassWrd = '$encPass' AND Active='Y'";

       	$func = "login";	
       	$con = null;
		$results = DBQueryExitOnFailure( $sql, $con, $func );
        	$num_rows = mysql_num_rows($results);
		
		$msg = $num_rows;

		if (($num_rows) == 0)
		{
				return -1;
		}
		else
		{
			$info = mysql_fetch_Array($results);
			if( $info["Expired"] == 0 )
			{
				return -2;
			}
			else
			{
				$_SESSION['username'] = $username;
				$_SESSION['email'] = $info['email'];
				$subdomain = explode (".",$_SERVER['SSL_SERVER_S_DN_CN']);
				$_SESSION['subdomain'] = $subdomain[0];
				exec("rpm -q --qf '%{VERSION}.%{RELEASE}' texaco",$rpmres);
				$_SESSION['rpmrelease'] = $rpmres[0];
				
				if( $info['Grp'] != "" )
				{
					$GrpPass = DecryptGrpPass( $password, $info['EncGrpPass'] );  
					$_SESSION['grp']      = $info['Grp'];
					$_SESSION['grpPass']  = $GrpPass;
				}
				else
				{
					$_SESSION['grp']      = $username;
					$_SESSION['grpPass']  = $password;
				}
				$_SESSION['userPerms'] = $info['Permissions'];

				return 0;
			}
		}
	}

	function ChangePassword( $username, $oldword, $newword )
	{
		$oldEnc = md5( $oldword );
		$sql = "SELECT UserID, Grp, EncGrpPass from Users where UserName = '$username' and EncPassWrd = '$oldEnc'";

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) == 0 )
		{
			return -1;
		}
		else
		{
			$row = mysql_fetch_assoc( $results );

			// Encrypt the new password
			$EncPass = md5( $newword );

			$sql = "Select count(*) from OldPassword where UserID = $row[UserID] and EncPassWrd = '$EncPass'"; 

			if( DBSingleStatQuery( $sql ) > 0 )
			{
				return -2;
			}
			else
			{
				$sql = "Insert into OldPassword values( $row[UserID], '$EncPass', now() )";
				DBQueryExitOnFailure( $sql );

				// Decrypt the group password using old password
				$GrpPass =  DecryptGrpPass( $oldword, $row["EncGrpPass"]);

				// Encrypt the group password using new password
				$EncGrpPass = EncryptGrpPass( $newword, $GrpPass );

				$sql = "Update Users set PassWrd = '$newword', GrpPass = '$GrpPass', EncPassWrd = '$EncPass', EncGrpPass = '$EncGrpPass', PasswordExpire = DATE_ADD(CURRENT_DATE() , INTERVAL 30 DAY) where UserName = '$username' and Grp = '$row[Grp]'";

				$results = DBQueryExitOnFailure( $sql );
				return 0;
			}
		}
	}

	function InsertUser( $adminUser, $adminPass, $newUser, $newpass, $userType )
	{
		global $uname;
		// Need to check permissions again
		$sql = "SELECT NewUserPass, NewUserPerms from CreateUserTypes where CreatorGrp = '$adminUser' and NewUserGrp = '$userType'";	

		$results = DBQueryExitOnFailure( $sql );
	
		$row = mysql_fetch_assoc( $results ) ;
		
		// Need to decrypt the Group pass
		$newUserGrpPass =  DecryptGrpPass( $adminPass, $row["NewUserPass"]);
		$Permisions = $row["NewUserPerms"];

		// need to encrypt the passwords
		$encGroupPass = EncryptGrpPass( $newpass, $newUserGrpPass );
		$encPassWrd = md5( $newpass );

		$sql = "INSERT into Users (	UserName, PassWrd, Grp,	GrpPass, Permissions, CreatedBy, Created, EncPassWrd, EncGrpPass ) values ( '$newUser', '$newpass', '$userType', '$newUserGrpPass', '$Permisions', '$uname', now(), '$encPassWrd', '$encGroupPass' )";

		$results = DBQueryExitOnFailure( $sql );

		return true;
	}

	function UpdateUserGrpAndPassword( $adminUser, $adminPassword, $username, $newGrp, $oldGrp, $newpass )
	{
		// find the grp password from database
		$sql = "SELECT NewUserPass, NewUserPerms from CreateUserTypes where CreatorGrp = '$adminUser' and NewUserGrp = '$newGrp'";

		$results = DBQueryExitOnFailure( $sql );

		$row = mysql_fetch_assoc( $results );
		$GrpPass = $row["NewUserPass"];
		$Perms   = $row["NewUserPerms"];

		// Get User Id

		$sql = "Select UserID from Users where UserName = '$username' and Grp = '$oldGrp'";
		$UserId = DBSingleStatQuery( $sql );

		// Decrypt the group password using the admin password
 		$newUserGrpPass =  DecryptGrpPass( $adminPassword, $row["NewUserPass"]);

		// Encrypt the group password using new password
   		$encGroupPass = EncryptGrpPass( $newpass, $newUserGrpPass );
		$encPassWrd = md5( $newpass );

		// Encrypt the new password

		$sql = "Update Users set PassWrd = '$newpass', GrpPass = '$GrpPass', PasswordExpire = now(), Permissions = '$Perms', EncPassWrd = '$encPassWrd', EncGrpPass = '$encGroupPass'";
//		Mantis 1763 18/01/10 - don't change the Perms
 
//		$sql = "Update Users set PassWrd = '$newpass', GrpPass = '$GrpPass', PasswordExpire = now(), EncPassWrd = '$encPassWrd', EncGrpPass = '$encGroupPass'";
		
		if( $newGrp != $oldGrp )
		{
			$sql .= ", Grp = '$newGrp' ";	
		}
		$sql .= ", Active='Y' where UserId = $UserId";

		$results = DBQueryExitOnFailure( $sql );


		$sql = "Replace into OldPassword values( $UserId, '$encPassWrd', now() )";
		DBQueryExitOnFailure( $sql );


		return true;
	}


	function PasswordDaysLeft( $username, $usergrp )
	{

		$sql = "SELECT TO_DAYS( PasswordExpire ) - TO_DAYS( now() ) as DaysLeft from Users where UserName = '$username' and Grp = '$usergrp'";
		$results = DBQueryExitOnFailure( $sql );

		$info = mysql_fetch_assoc($results);
		return $info[ "DaysLeft" ];
	}

	function UpdateLastLogin( $username, $password )
	{
		$sql = "UPDATE Users SET LastLogin = now() WHERE UserName = '$username' and PassWrd = '$password'";
			
		$results = DBQueryExitOnFailure( $sql );
	}


function EncryptGrpPass( $PersonalPassword, $GrpPass )
{
	$key_size = mcrypt_get_key_size( MCRYPT_3DES, MCRYPT_MODE_ECB);
	$key = substr( $PersonalPassword, 0, $key_size );

	$iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	return mysql_real_escape_string( mcrypt_encrypt(MCRYPT_3DES, $key, $GrpPass, MCRYPT_MODE_ECB, $iv));
}

function DecryptGrpPass( $PersonalPassword, $EncPass )
{
	$key_size = mcrypt_get_key_size( MCRYPT_3DES, MCRYPT_MODE_ECB);
	$key = substr( $PersonalPassword, 0,  $key_size );

	$iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

	return mcrypt_decrypt(MCRYPT_3DES, $key, $EncPass, MCRYPT_MODE_ECB, $iv);
}

function RandomPassword()
{

	$min=4; // minimum length of password
	$max=15; // maximum length of password
	$pwd=""; // to store generated password

	for($i=0;$i<rand($min,$max);$i++)
	{
		$num=rand(48,122);
		if(($num > 97 && $num < 122))
		{
			$pwd.=chr($num);
		}
		else if(($num > 65 && $num < 90))
		{
			$pwd.=chr($num);
		}
		else if(($num >48 && $num < 57))
		{
			$pwd.=chr($num);
		}
		else if($num==95)
		{
			$pwd.=chr($num);
		}
		else
		{
			$i--;
		}
	}
	return $pwd;
}

?>