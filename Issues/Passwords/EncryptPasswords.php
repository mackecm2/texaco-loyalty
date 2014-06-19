<?php 
	include "../../include/DB.inc";
	include "../../DBInterface/PasswordInterface.php";

	$db_pass = "Trave1";

	connectToDB( MasterServer, TexacoDB );

	$sql = "delete from OldPassword";
	DBQueryExitOnFailure( $sql );

	// Change DBAccount Passwords

	$Accounts = array( 'SAdmin', 'DAdmin', 'DBasic', 'MAdmin', 'MBasic', 'UAdmin', 'UBasic', 'RAdmin', 'RBasic', 'MPromo' );
	//$Accounts = array( 'DAdmin', 'DBasic', 'MAdmin', 'MBasic', 'UAdmin', 'UBasic' );
	$p = array();
	foreach( $Accounts as $name )
	{
		$p[$name] = RandomPassword();
		//$p[$name] = "Frank";
	}


	foreach( $p as $name => $pass )
	{
		$sql = "set password for $name@localhost = old_password( '$pass' )";
 			echo "$sql<br>\n";
		DBQueryExitOnFailure( $sql );


		$sql = "Select * from Users where Grp = '$name'";

		$res = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc( $res ) )
		{
			$EncPassWord = md5( $row["PassWrd"] );

			$EncGrpPass = EncryptGrpPass( $row["PassWrd"], $pass );
			
			$sql = "Update Users set EncPassWrd = '$EncPassWord', EncGrpPass = '$EncGrpPass', GrpPass = '$pass' where UserId = $row[UserID]";
 			echo "$sql<br>\n";
			DBQueryExitOnFailure( $sql );

			$sql = "insert into OldPassword values ( $row[UserID], '$EncPassWord', now() )";
			DBQueryExitOnFailure( $sql );

		}


		$sql = "Select * from CreateUserTypes where CreatorGrp = '$name'";
		$res = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_assoc( $res ) )
		{
			$NewUserPass = EncryptGrpPass( $pass, $p[$row["NewUserGrp"]] );
			
			$sql = "Update CreateUserTypes set NewUserPass = '$NewUserPass' where NewUserGrp = '$row[NewUserGrp]' and CreatorGrp = '$name'";
 			echo "$sql<br>\n";

			DBQueryExitOnFailure( $sql );

		}
	}	
		
	


?>