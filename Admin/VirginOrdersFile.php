<?php 

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/OrdersInterface.php";
	include "../include/CSVFile.php";


	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilename( $timestamp );
	}
	else
	{
		$timestamp = GetSQLTime();
		$friendly = GetBatchFilename( $timestamp );

		MakeUpVirginBatch( $timestamp );
	}

	
	$fillname = "VirginFile". $friendly .".csv";

	$results = GetVirginBatchData( $timestamp );
	
	header('Content-type: text/csv');
	header("Content-Disposition: attachment; filename=\"$fillname\""); 

	echo "RefNo, Name, Address1, Address2, Address3, Address4, Address5, PostCode, MerchantId, ProductId, Cost, Quanity\n"; 
	while($row = mysql_fetch_assoc($results))
	{
		echo "$row[RefNo],$row[Name],";
		if( $row["srce"] == 0 )
		{
			echo "$row[Address1],$row[Address2],$row[Address3],$row[Address4],$row[Address5],$row[PostCode],";
		}
		else
		{
			echo "$row[MAddress1],$row[MAddress2],$row[MAddress3],$row[MAddress4],$row[MAddress5],$row[MPostCode],";
		}
		echo "$row[MerchantId], $row[ProductId], $row[Cost], $row[Quanity]\n";
	}

?>