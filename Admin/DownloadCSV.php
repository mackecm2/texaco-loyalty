<?php 

	include "../include/Session.inc";
	include "../include/Locations.php";
	include "../DBInterface/ReportRequestInterface.php";
	include "../include/CSVFile.php";

	$row = GetReportRecord( $_GET["ID"] );

	OutputCSVFile( LocationReportsDirectory, $row["ResultsFile"], $row["ColumnHeads"] );

?>