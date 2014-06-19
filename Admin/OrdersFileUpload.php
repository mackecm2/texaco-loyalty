<?php 

	include "../include/Session.inc";
	include "../include/CSVFile.php";
	include "../DBInterface/OrdersInterface.php";
	include "../DBInterface/FileProcessRecord.php";
	include "../FileProcessing/OrdersFile/OrdersFileProcess.php";

	$filePath =  "C:/projects/Texaco/FileProcessing/ToProcess/OrdersFile/";
	$fileMove =  "C:/projects/Texaco/FileProcessing/Processed/OrdersFile/";
	$filePattern = "OrdersFile*.csv";

	$uploadfile = $filePath . $_FILES['userfile']['name'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		ProcessFiles( $filePath, $filePattern, $fileMove );
	}
?>