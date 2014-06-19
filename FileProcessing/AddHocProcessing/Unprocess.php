<?php
	include "../../include/DB.inc";
	$db_user = 'root';
	$db_pass = 'trave1';

	$master = connectToDB( MasterServer, TexacoDB );

	$slave =  connectToDB( AnalysisServer, AnalysisDB );

	$c = 0;

	$sql = "select * from BackupTOKMail";
	$results = mysql_query( $sql, $slave );
		if( !$results )
		{
			 die( mysql_error($master));
		}

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$c++;
		if( $c % 10000 == 0 )
		{
			echo "$c\n";
		}
		$sql = "Update Members set TOKMail = '$row[TOKMail]' where MemberNo = $row[MemberNo] and TOKMail != '$row[TOKMail]'";
		$results2 = mysql_query( $sql, $master );
		if( !$results2 )
		{
			 die( mysql_error($master));
		}
	}
	echo "$c\n";

	
?>