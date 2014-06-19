<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/Locations.php";
	include "../DBInterface/ReportRequestInterface.php";

	if( isset( $_GET["ID"] ) )
	{
		$filename = DeleteReport( $_GET["ID"] );
		if( $filename )
		{
			unlink( LocationReportsDirectory. $filename );
		}
		header("Location: Reports.php");
	}
?>