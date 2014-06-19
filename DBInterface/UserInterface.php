<?php

	

	function CheckNameClash( $username )
	{
		$sql = "Select * from Users where UserName = '$username'";
		$results = DBQueryExitOnFailure( $sql );
		
		return mysql_num_rows( $results ) == 0;
	}

	function CheckPasswordClash( $userId, $password )
	{
		$sql = "Select * from PasswordHistory where UserID = $userId and PassWrd = '$password'";
		$results = DBQueryExitOnFailure( $sql );
		
		return mysql_num_rows( $results ) == 0;
	}

	function CheckControl( $adminGrp, $user )
	{
		$sql = "SELECT Grp from Users join CreateUserTypes on Users.Grp = CreateUserTypes.NewUserGrp where UserName = '$user' and CreatorGrp = '$adminGrp'";
		
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) == 0 )
		{
			return false;
		}
		$row = mysql_fetch_row( $results );
		
		return $row[0];
	}

	function GetUserGrp( $username, $adminGrp )
	{
		$sql = "SELECT Grp from Users join CreateUserTypes on Users.Grp = CreateUserTypes.NewUserGrp where UserName = '$username' and CreatorGrp = '$adminGrp'";

		$results = DBQueryExitOnFailure( $sql );
		if( mysql_num_rows( $results ) == 0 )
		{
			$errorStr = "No Matching Users";
			include "NoPermission.php";
			exit();
		}
		$row = mysql_fetch_row( $results );
		return $row[0];
	}

	function DeleteUser( $adminGrp, $userToDelete )
	{
		$usrGrp = CheckControl( $adminGrp, $userToDelete );

		if( !$usrGrp )
		{
			$errorStr = "No Matching Users";
			include "../include/NoPermission.php";
			exit();
		}

		$sql = "Update Users set Active='N' where UserName = '$userToDelete' and Grp = '$usrGrp'";
		$results = DBQueryExitOnFailure( $sql );
		return true;
	}

	function GetGrpList( $adminGrp )
	{
		$sql = "SELECT NewUserGrp, NewUserDesc from CreateUserTypes where CreatorGrp = '$adminGrp'";

		$results = DBQueryExitOnFailure( $sql );
		$userTypes = array();
		
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$userTypes[$row["NewUserGrp"]] = $row["NewUserDesc"];
		}
		return $userTypes;
	}

	function GetUserList( $adminGrp )
	{
		global 	$db_user;
		$sql = "SELECT UserName, Grp from Users join CreateUserTypes on Users.Grp = CreateUserTypes.NewUserGrp where CreatorGrp = '$db_user'";

		$results = mysql_query( $sql );

		$results = DBQueryExitOnFailure( $sql );
		if( mysql_num_rows( $results ) == 0 )
		{
			$errorStr = "No Matching Users";
			include "NoPermission.php";
			exit();
		}
		$users = array();
	
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$users[$row["UserName"]] = $row["UserName"];
		}			
		return $users;
	}

	function GetManagedUsers( $all )
	{
		global 	$db_user;
		$active = "";
		if( !$all )
		{
			$active = " and Active='Y'";
		}
		$sql = "SELECT UserName, if( Active='Y',Grp,'Deleted') as DGrp from Users join CreateUserTypes on Users.Grp = CreateUserTypes.NewUserGrp where CreatorGrp = '$db_user' $active" ;

		$results = mysql_query( $sql );

		$results = DBQueryExitOnFailure( $sql );
		if( mysql_num_rows( $results ) == 0 )
		{
			$errorStr = "You have no users to manage";
			include "../include/NoPermission.php";
			exit();
		}
		return $results;
	}

	function ClearUserHistory()
	{
		$sql = "Delete from UserActions where Dayofyear( now() ) != Dayofyear( CreationDate )"; 
		DBQueryExitOnFailure( $sql );
	}

	function AddUserHistory( $MemberNo )
	{
		global $uname;
		if( $MemberNo != "" )
		{
			$sql = "REPLACE into UserActions ( UserName, MemberNo ) values ( '$uname', $MemberNo )";
			DBQueryExitOnFailure( $sql );
		}
	}

	function GetUserHistory( $User )
	{
		$fields = "Members.MemberNo, AccountNo, DATE_FORMAT(UserActions.CreationDate, '%l:%m %p') as UDate, Title, Initials, GenderCode, Surname, Address1, Address2, PrimaryCard, PostCode ";

		$sql = "Select $fields from Members Join UserActions using(MemberNo) where UserName = '$User' order By UserActions.CreationDate DESC";

		return DBQueryExitOnFailure( $sql );
	}
?>