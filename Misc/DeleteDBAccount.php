<?php
	include "../include/DB.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   

	connectToDB( MasterServer, TexacoDB );

	$name = $_GET["name"];

	$sql = "show grants for $name";
	$tables = DBQueryExitOnFailure( $sql );

	while( $granta = mysql_fetch_row( $tables ) )
	{
		$grant = $granta[0];

		$tablename = strstr( $grant, " ON " );

		$pos =  strpos( $tablename, " TO " );

		$tablename = substr( $tablename, 4, $pos - 4 );  

		$sql = "REVOKE all on $tablename from $name";
		echo "$sql\n";
		DBQueryExitOnFailure( $sql );
	}

	$sql = "DROP user $name";
	echo "$sql\n";

	DBQueryExitOnFailure( $sql );

?>