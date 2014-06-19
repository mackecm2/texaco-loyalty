<?php
	//session_start();
	if( isset( $_SESSION['grp'] ) and isset( $_SESSION['grpPass'] ) )
	{
		global $uname, $db_user, $db_pass, $userPerms;
		global $Reporting;
		$uname = $_SESSION['username'] ;
		$db_user = $_SESSION['grp'] ;
		$db_pass = $_SESSION['grpPass'];
		$userPerms = $_SESSION['userPerms'];
		require 'DB.inc';

		if( isset($Reporting) )
		{
			$db_host = ReportServer;
			$db_name = ReportDB;
		}
		else
		{
			if( !isset( $db_host ) )
			{
				$db_host = MasterServer;
			}
			if( !isset( $db_name ) )
			{
				$db_name = TexacoDB;
			}
		}

		connectToDB( $db_host, $db_name );
		
		
		#$fp = fopen("/data/www/logs/testoutput.txt","a");
		
		#fwrite($fp,"positive route \r\n");
		#fclose($fp);

	}
	else
	{
		
		header("Location: ../Login.php");
		exit();		
	}

?>
