<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	include "../DBInterface/FileProcessRecord.php";

	connectToDB( MasterServer, TexacoDB );

	echo "<H1>Processes on Texaco 1 </H1>";

	$results = GetLastProcessTimes();

	DisplayTable( $results, 0 );

	echo "<H1>Processes on Texaco 2 </H1>";
	
	connectToDB( ReportServer, ReportDB );

	$results = GetLastReportTimes();

	DisplayTable( $results, 0 );

?>