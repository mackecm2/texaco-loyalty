<?php

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/OrdersInterface.php";
	include "../include/CSVFile.php";

	if( !isset( $_GET["Type"] ) )
	{
		echo "Type not set";
		exit();
	}

	$Type = $_GET["Type"];
	if( isset( $_GET["Repeat"] ))
	{
		$timestamp = $_GET["Repeat"];
		$friendly = GetBatchFilename( $timestamp );
	}
	else
	{
		$timestamp = GetSQLTime();
		$friendly = GetBatchFilename( $timestamp );

		MakeUpOrdersBatch( $Type, $timestamp );
	}



	// Dawleys type
	if( $Type == 0 OR $Type == 1 OR $Type == 3 )
	{
		$fillname = GetTypeDescription( $Type ). $friendly .".txt";
		$results = GetDawleysOrdersBatchData( $Type,  $timestamp );
		OutputDownloadHeaders( $fillname );

		$PrevOrder = -1;
		$PCount = 4;
		$endLine = "";
		while($row = mysql_fetch_assoc($results))
		{
			if( $PrevOrder != $row["OrderNo"] || $PCount == 4 )
			{
				for( $i = $PCount; $i < 4; $i++ )
				{
					echo "\$\$";
				}
				echo $endLine;
				$endLine = "\r\n";
				echo "0$row[PrimaryCard]\$$row[OrderDate]\$$row[OrderNo]\$$row[Title]\$$row[Forename]\$$row[Name]\$$row[Address1]\$$row[Address2]\$$row[Address3]\$$row[Address4]\$$row[Address5]\$$row[PostCode]";
				$PrevOrder = $row["OrderNo"];
				$PCount = 0;
			}
			$PCount++;
			echo "\$$row[ProductId]\$";
			printf( "%07.2f", $row['Cost']/100.0 );
			
			//			 * Mantis 811 MRM 04/03/09
			 
			if ( $row[CreatedBy] == "WEB" )
			{
				 echo "\$W\$";
			}
			else 
			{
				echo "\$T\$";
			}
			  //			 * Mantis 811 end			
			
			
		}
		for( $i = $PCount; $i < 4; $i++ )
		{
			echo "\$\$";
		}
		echo $endLine;

	}
	else if( $Type == 18 )
	{
		$fillname = GetTypeDescription( $Type ). $friendly .".txt";
		$results = GetVirginBatchData( $Type,  $timestamp );
		OutputDownloadHeaders( $fillname );

		while($row = mysql_fetch_assoc($results))
		{
			#printf( "%011d~TX~   %d~ A~%-6s~%-8s ~%s\r\n", $row['VirginNo'], $row["RedeemRate"], $row['Miles'], $row['OrderDate'], $row['PrimaryCard'] );
			printf( "%011d~TX~%d~A~%-6s~%-8s~%s\r\n", $row['VirginNo'], $row["RedeemRate"], $row['Miles'], $row['OrderDate'], $row['PrimaryCard'] );
			
		}

	}
	else if( $Type == 17 )
	{
		echo "What were you planning to do with this?";
	}
	else
	{

		# Woolworths/Argos/Flowers

		$fillname = GetTypeDescription( $Type ). $friendly .".txt";
		$results = GetOrdersBatchData( $Type,  $timestamp );
		OutputCSV( $fillname, $results  );
	}
?>