<?php
//* MRM 17/06/2008 – changed all date("h:i:s") to ("H:i:s")
error_reporting(E_ERROR);

include "GeneralReportFunctions.php";
include "../include/DB2.inc";
include "inc-mysql.php";

$slave = connectToDB( ReportServer, ReportDB );

$timedate = date("Y-m-d")." ".date("H:i:s");
//*
//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
//echo "CreateSpendBandsReport.php - started $timedate\r\n";
echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

CreateSpendBandsReportTable();
PopulatePreResults05();
PopulatePreResults06();
PopulateDuringResults05();
PopulateDuringResults06();
PopulatePostResults05();
if(date("Y-m-d") > '2006-08-03')
{
	PopulatePostResults06(); 
}

UpdateSegments("PreSegCode05","RawKPIData200502");
UpdateSegments("PreSegCode05","RawKPIData200503");
UpdateSegments("PreSegCode05","RawKPIData200504");

UpdateSegments("DuringSegCode05","RawKPIData200504");
UpdateSegments("DuringSegCode05","RawKPIData200505");
UpdateSegments("DuringSegCode05","RawKPIData200506");
UpdateSegments("DuringSegCode05","RawKPIData200507");

UpdateSegments("PostSegCode05","RawKPIData200507");
UpdateSegments("PostSegCode05","RawKPIData200508");
UpdateSegments("PostSegCode05","RawKPIData200509");

UpdateSegments("PreSegCode06","RawKPIData200602");
UpdateSegments("PreSegCode06","RawKPIData200603");
UpdateSegments("PreSegCode06","RawKPIData200604");

UpdateSegments("DuringSegCode06","RawKPIData200604");
UpdateSegments("DuringSegCode06","RawKPIData200605");
UpdateSegments("DuringSegCode06","RawKPIData200606");
if(date("Y-m-d") > '2006-08-03')
{
	UpdateSegments("DuringSegCode06","RawKPIData200607");
	UpdateSegments("PostSegCode06","RawKPIData200507");	
}

if(date("Y-m-d") > '2006-09-03')
{
	UpdateSegments("PostSegCode06","RawKPIData200508");
}
if(date("Y-m-d") > '2006-10-03')
{
	UpdateSegments("PostSegCode06","RawKPIData200509");
}


CreateSegmentationReportTable();
CreateSegmentationReport();


CreateCumulativeReportTable();
CreateCumulativeReport();

CreateTopSpendersReportTable();
CreateTopSpendersReport();

CreateFrequencyReportTable();
CreateFrequencyReport();

CreateBandedTopSpendersReportTable();
CreateBandedTopSpendersReport();

CreateBandedFrequencyReportTable();
CreateBandedFrequencyReport();

$timedate = date("Y-m-d")." ".date("H:i:s");
//*
//* next line exchanged for the one below it for greater clarity in logs - MRM 17/06/2008
//echo "CreateSpendBandsReport.php - completed $timedate\r\n";
echo "\r\n".date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";


function CreateSpendBandsReportTable()
{
	$sql = "drop table if exists SpendBands";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBands
(
     CardNo             char(20), 
     PreSegCode05 	char(16),
     DuringSegCode05	char(16),     
     PostSegCode05 	char(16),  
     PreSegCode06 	char(16),
     DuringSegCode06	char(16),     
     PostSegCode06 	char(16),  
     PreSpend05     	DECIMAL(6,2) default 0, 
     DuringSpend05     	DECIMAL(6,2) default 0,      
     PostSpend05     	DECIMAL(6,2) default 0,      
     PreSpend06     	DECIMAL(6,2) default 0, 
     DuringSpend06     	DECIMAL(6,2) default 0,      
     PostSpend06     	DECIMAL(6,2) default 0,
     PreSwipes05	int(11) default 0,
     DuringSwipes05	int(11) default 0,
     PostSwipes05	int(11) default 0,
     PreSwipes06	int(11) default 0,
     DuringSwipes06	int(11) default 0,
     PostSwipes06	int(11) default 0 
)";

	DBQueryExitOnFailure($sql);
	
	
	$sql = "Alter Table SpendBands 
	        add index( CardNo )";
			   
 	DBQueryExitOnFailure($sql);	
}


function CreateCumulativeReportTable()
{
	$sql = "drop table if exists SpendBandsCumulativeReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsCumulativeReport
(
     Band            	char(20), 
     PreQty05		int(11) default 0,
     PreSpend05     	DECIMAL(10,2) default 0, 
     DuringQty05	int(11) default 0,
     DuringSpend05     	DECIMAL(10,2) default 0,      
     PostQty05		int(11) default 0,
     PostSpend05     	DECIMAL(10,2) default 0,      
     PreQty06	int(11) default 0,
     PreSpend06     	DECIMAL(10,2) default 0, 
     DuringQty06	int(11) default 0,
     DuringSpend06     	DECIMAL(10,2) default 0,      
     PostQty06	int(11) default 0, 
     PostSpend06     	DECIMAL(10,2) default 0
)";

	DBQueryExitOnFailure($sql);
}


function CreateSegmentationReportTable()
{
	$sql = "drop table if exists SpendBandsSegmentationReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsSegmentationReport
(

     SegmentCode 	char(16),
     PreQty05		int(11) default 0,
     DuringQty05	int(11) default 0,
     PostQty05		int(11) default 0,
     PreQty06		int(11) default 0,
     DuringQty06	int(11) default 0,
     PostQty06		int(11) default 0
)";

	DBQueryExitOnFailure($sql);
	
	$sql = "Alter Table SpendBandsSegmentationReport 
	        add index( SegmentCode )";
			   
 	DBQueryExitOnFailure($sql);	
	
	
}


function CreateTopSpendersReportTable()
{
	$sql = "drop table if exists SpendBandsTopSpendersReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsTopSpendersReport
(

     Batch		char(20), 
     id			int(11) default 0,
     NoOfCustomers	int(11) default 0,
     TotalSpend    	DECIMAL(10,2) default 0, 
     AverageValue     	DECIMAL(10,2) default 0
)";

	DBQueryExitOnFailure($sql);
	
	$sql = "Alter Table SpendBandsTopSpendersReport 
	        add index( Batch )";
			   
 	DBQueryExitOnFailure($sql);	
	
	
}

function CreateBandedTopSpendersReportTable()
{
	$sql = "drop table if exists SpendBandsBandedTopSpendersReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsBandedTopSpendersReport
(

     Batch		char(20), 
     id			int(11) default 0,
     NoOfCustomers	int(11) default 0,
     TotalSpend    	DECIMAL(10,2) default 0, 
     AverageValue     	DECIMAL(10,2) default 0
)";

	DBQueryExitOnFailure($sql);
	
	$sql = "Alter Table SpendBandsTopSpendersReport 
	        add index( Batch )";
			   
 	DBQueryExitOnFailure($sql);	
	
	
}


function CreateFrequencyReportTable()
{
	$sql = "drop table if exists SpendBandsFrequencyReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsFrequencyReport
(

     Batch		char(20), 
     id			int(11) default 0,
     NoOfCustomers	int(11) default 0,
     TotalSwipes    	DECIMAL(10,2) default 0, 
     AverageSwipes     	DECIMAL(10,2) default 0
)";

	DBQueryExitOnFailure($sql);
	
	$sql = "Alter Table SpendBandsFrequencyReport 
	        add index( Batch )";
			   
 	DBQueryExitOnFailure($sql);	
	
	
}

function CreateBandedFrequencyReportTable()
{
	$sql = "drop table if exists SpendBandsBandedFrequencyReport";
	DBQueryExitOnFailure($sql);

	$sql = "create table SpendBandsBandedFrequencyReport
(

     Batch		char(20), 
     id			int(11) default 0,
     NoOfCustomers	int(11) default 0,
     TotalSwipes    	DECIMAL(10,2) default 0, 
     AverageSwipes     	DECIMAL(10,2) default 0
)";

	DBQueryExitOnFailure($sql);
	
	$sql = "Alter Table SpendBandsBandedFrequencyReport 
	        add index( Batch )";
			   
 	DBQueryExitOnFailure($sql);	
	
	
}


function PopulatePreResults05() 
{
	echo date( "h:i:s" );
	echo " Processing Pre Results for 05\n";
	
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200502"," 1 ",0) >0)
	{
		echo "Processing Feb05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($pre05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200502","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-02-13' and '2005-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre05data[Swipes]/$pre05data[TotalSpend]/$pre05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						PreSwipes05 = (PreSwipes05 + '$pre05data[Swipes]'),
						PreSpend05 = (PreSpend05 + '$pre05data[TotalSpend]'),
						PreSegCode05 = '$pre05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";
					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes05'] = $pre05data['Swipes'];
					$updatedata['PreSpend05'] = $pre05data['TotalSpend'];
					$updatedata['PreSegCode05'] = $pre05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($pre05data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200503"," 1 ",0) >0)
	{
		echo "Processing March05\n";
		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($pre05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200503","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-02-13' and '2005-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre05data[Swipes]/$pre05data[TotalSpend]/$pre05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PreSwipes05 = (PreSwipes05 + '$pre05data[Swipes]'),
						PreSpend05 = (PreSpend05 + '$pre05data[TotalSpend]'),
						PreSegCode05 = '$pre05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes05'] = $pre05data['Swipes'];
					$updatedata['PreSpend05'] = $pre05data['TotalSpend'];
					$updatedata['PreSegCode05'] = $pre05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($pre05data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200504"," 1 ",0) >0)
	{
		echo "Processing April05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($pre05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200504","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-02-13' and '2005-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre05data[Swipes]/$pre05data[TotalSpend]/$pre05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,PreSpend05,PreSwipes05","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PreSwipes05 = (PreSwipes05 + '$pre05data[Swipes]'),
						PreSpend05 = (PreSpend05 + '$pre05data[TotalSpend]'),
						PreSegCode05 = '$pre05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes05'] = $pre05data['Swipes'];
					$updatedata['PreSpend05'] = $pre05data['TotalSpend'];
					$updatedata['PreSegCode05'] = $pre05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($pre05data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}
	
	#	After creating the Pre05 results we need to store the Card Members for the specific
	#	Member band reports
	#	Need to:  1 - create SpendBandMembers table
	#	Select all cardnos with PreSwipes05 > 0
	#	add into SpendBandMembers table with Year = 2005
	
	

}



function PopulatePreResults06() 
{
	echo date( "h:i:s" );
	echo " Processing Pre Results for 06\n";
	
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200602"," 1 ",0) >0)
	{
		echo "Processing Feb06\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($pre06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200602","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-02-13' and '2006-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre06data[Swipes]/$pre06data[TotalSpend]/$pre06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						PreSwipes06 = (PreSwipes06 + '$pre06data[Swipes]'),
						PreSpend06 = (PreSpend06 + '$pre06data[TotalSpend]'),
						PreSegCode06 = '$pre06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes06'] = $pre06data['Swipes'];
					$updatedata['PreSpend06'] = $pre06data['TotalSpend'];
					$updatedata['PreSegCode06'] = $pre06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($pre06data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200603"," 1 ",0) >0)
	{
		echo "Processing March06\n";
		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($pre06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200603","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-02-13' and '2006-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre06data[Swipes]/$pre06data[TotalSpend]/$pre06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PreSwipes06 = (PreSwipes06 + '$pre06data[Swipes]'),
						PreSpend06 = (PreSpend06 + '$pre06data[TotalSpend]'),
						PreSegCode06 = '$pre06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes06'] = $pre06data['Swipes'];
					$updatedata['PreSpend06'] = $pre06data['TotalSpend'];
					$updatedata['PreSegCode06'] = $pre06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($pre06data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200604"," 1 ",0) >0)
	{
		echo "Processing April06\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
			if(mysqlSelect($pre06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200604","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-02-13' and '2006-04-23' group by CardNo ") >0)
			{

#		echo "Got Cards $pre06data[Swipes]/$pre06data[TotalSpend]/$pre06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,PreSpend06,PreSwipes06","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PreSwipes06 = (PreSwipes06 + '$pre06data[Swipes]'),
						PreSpend06 = (PreSpend06 + '$pre06data[TotalSpend]'),
						PreSegCode06 = '$pre06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PreSwipes06'] = $pre06data['Swipes'];
					$updatedata['PreSpend06'] = $pre06data['TotalSpend'];
					$updatedata['PreSegCode06'] = $pre06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($pre06data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}

}




function PopulateDuringResults05() 
{
	echo date( "h:i:s" );
	echo " Processing During Results for 05\n";
	
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200504"," 1 ",0) >0)
	{
		echo "Processing April05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200504","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-04-24' and '2005-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during05data[Swipes]/$during05data[TotalSpend]/$during05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						DuringSwipes05 = (DuringSwipes05 + '$during05data[Swipes]'),
						DuringSpend05 = (DuringSpend05 + '$during05data[TotalSpend]'),
						DuringSegCode05 = '$during05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes05'] = $during05data['Swipes'];
					$updatedata['DuringSpend05'] = $during05data['TotalSpend'];
					$updatedata['DuringSegCode05'] = $during05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($during05data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200505"," 1 ",0) >0)
	{
		echo "Processing May05\n";
		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200505","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-04-24' and '2005-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during05data[Swipes]/$during05data[TotalSpend]/$during05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes05 = (DuringSwipes05 + '$during05data[Swipes]'),
						DuringSpend05 = (DuringSpend05 + '$during05data[TotalSpend]'),
						DuringSegCode05 = '$during05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes05'] = $during05data['Swipes'];
					$updatedata['DuringSpend05'] = $during05data['TotalSpend'];
					$updatedata['DuringSegCode05'] = $during05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($during05data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200506"," 1 ",0) >0)
	{
		echo "Processing June05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200506","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-04-24' and '2005-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during05data[Swipes]/$during05data[TotalSpend]/$during05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,DuringSpend05,DuringSwipes05","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes05 = (DuringSwipes05 + '$during05data[Swipes]'),
						DuringSpend05 = (DuringSpend05 + '$during05data[TotalSpend]'),
						DuringSegCode05 = '$during05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes05'] = $during05data['Swipes'];
					$updatedata['DuringSpend05'] = $during05data['TotalSpend'];
					$updatedata['DuringSegCode05'] = $during05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($during05data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200507"," 1 ",0) >0)
	{
		echo "Processing July05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200507","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-04-24' and '2005-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during05data[Swipes]/$during05data[TotalSpend]/$during05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,DuringSpend05,DuringSwipes05","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes05 = (DuringSwipes05 + '$during05data[Swipes]'),
						DuringSpend05 = (DuringSpend05 + '$during05data[TotalSpend]'),
						DuringSegCode05 = '$during05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		
#					echo "$sql\n\r";

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes05'] = $during05data['Swipes'];
					$updatedata['DuringSpend05'] = $during05data['TotalSpend'];
					$updatedata['DuringSegCode05'] = $during05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($during05data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}




}

function PopulateDuringResults06() 
{
	echo date( "h:i:s" );
	echo " Processing During Results for 06\n";
	
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200604"," 1 ",0) >0)
	{
		echo "Processing April06\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200604","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-04-24' and '2006-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during06data[Swipes]/$during06data[TotalSpend]/$during06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						DuringSwipes06 = (DuringSwipes06 + '$during06data[Swipes]'),
						DuringSpend06 = (DuringSpend06 + '$during06data[TotalSpend]'),
						DuringSegCode06 = '$during06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes06'] = $during06data['Swipes'];
					$updatedata['DuringSpend06'] = $during06data['TotalSpend'];
					$updatedata['DuringSegCode06'] = $during06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($during06data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}

if(date("Y-m-d") > '2006-06-01')
{
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200605"," 1 ",0) >0)
	{
		echo "Processing May06\n";
		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200605","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-04-24' and '2006-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during06data[Swipes]/$during06data[TotalSpend]/$during06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes06 = (DuringSwipes06 + '$during06data[Swipes]'),
						DuringSpend06 = (DuringSpend06 + '$during06data[TotalSpend]'),
						DuringSegCode06 = '$during06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes06'] = $during06data['Swipes'];
					$updatedata['DuringSpend06'] = $during06data['TotalSpend'];
					$updatedata['DuringSegCode06'] = $during06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($during06data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}
}

if(date("Y-m-d") > '2006-07-01')
{

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200606"," 1 ",0) >0)
	{
		echo "Processing June06\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200606","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-04-24' and '2006-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during06data[Swipes]/$during06data[TotalSpend]/$during06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,DuringSpend06,DuringSwipes06","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes06 = (DuringSwipes06 + '$during06data[Swipes]'),
						DuringSpend06 = (DuringSpend06 + '$during06data[TotalSpend]'),
						DuringSegCode06 = '$during06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes06'] = $during06data['Swipes'];
					$updatedata['DuringSpend06'] = $during06data['TotalSpend'];
					$updatedata['DuringSegCode06'] = $during06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($during06data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}

}

if(date("Y-m-d") > '2006-08-01')
{

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200607"," 1 ",0) >0)
	{
		echo "Processing July06\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($during06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200607","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-04-24' and '2006-07-02' group by CardNo ") >0)
			{

#		echo "Got Cards $during06data[Swipes]/$during06data[TotalSpend]/$during06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,DuringSpend06,DuringSwipes06","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						DuringSwipes06 = (DuringSwipes06 + '$during06data[Swipes]'),
						DuringSpend06 = (DuringSpend06 + '$during06data[TotalSpend]'),
						DuringSegCode06 = '$during06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['DuringSwipes06'] = $during06data['Swipes'];
					$updatedata['DuringSpend06'] = $during06data['TotalSpend'];
					$updatedata['DuringSegCode06'] = $during06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($during06data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}

}


}





function PopulatePostResults05() 
{
	echo date( "h:i:s" );
	echo " Processing Post Results for 05\n";
	
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200507"," 1 ",0) >0)
	{
		echo "Processing July05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200507","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-07-03' and '2005-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post05data[Swipes]/$post05data[TotalSpend]/$post05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						PostSwipes05 = (PostSwipes05 + '$post05data[Swipes]'),
						PostSpend05 = (PostSpend05 + '$post05data[TotalSpend]'),
						PostSegCode05 = '$post05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes05'] = $post05data['Swipes'];
					$updatedata['PostSpend05'] = $post05data['TotalSpend'];
					$updatedata['PostSegCode05'] = $post05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($post05data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200508"," 1 ",0) >0)
	{
		echo "Processing August05\n";
		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200508","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-07-03' and '2005-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post05data[Swipes]/$post05data[TotalSpend]/$post05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PostSwipes05 = (PostSwipes05 + '$post05data[Swipes]'),
						PostSpend05 = (PostSpend05 + '$post05data[TotalSpend]'),
						PostSegCode05 = '$post05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes05'] = $post05data['Swipes'];
					$updatedata['PostSpend05'] = $post05data['TotalSpend'];
					$updatedata['PostSegCode05'] = $post05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($post05data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200509"," 1 ",0) >0)
	{
		echo "Processing Sept05\n";
		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post05data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200509","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2005-07-03' and '2005-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post05data[Swipes]/$post05data[TotalSpend]/$post05data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,PostSpend05,PostSwipes05","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PostSwipes05 = (PostSwipes05 + '$post05data[Swipes]'),
						PostSpend05 = (PostSpend05 + '$post05data[TotalSpend]'),
						PostSegCode05 = '$post05data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes05'] = $post05data['Swipes'];
					$updatedata['PostSpend05'] = $post05data['TotalSpend'];
					$updatedata['PostSegCode05'] = $post05data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($post05data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}



}




function PopulatePostResults06() 
{
	echo date( "h:i:s" );
	echo " Processing Post Results for 06\n";
	
if(date("Y-m-d") > '2006-07-01')
{
	echo "Processing July06\n";
		
	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200607"," 1 ",0) >0)
	{

		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200607","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-07-03' and '2006-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post06data[Swipes]/$post06data[TotalSpend]/$post06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					
					$sql = "update SpendBands set 
						PostSwipes06 = (PostSwipes06 + '$post06data[Swipes]'),
						PostSpend06 = (PostSpend06 + '$post06data[TotalSpend]'),
						PostSegCode06 = '$post06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
					
				
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes06'] = $post06data['Swipes'];
					$updatedata['PostSpend06'] = $post06data['TotalSpend'];
					$updatedata['PostSegCode06'] = $post06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}			
			}

#			echo "Inserted Record\n";
			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}	


			unset($updatedata);
			unset($cardrow);
			unset($post06data);
			unset($thiscard);
		}
		
		unset($cards);

		
	}
}




if(date("Y-m-d") > '2006-09-07')
{

	echo "Processing August06\n";


	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200608"," 1 ",0) >0)
	{

		$count = '0';
		
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200608","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-07-03' and '2006-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post06data[Swipes]/$post06data[TotalSpend]/$post06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PostSwipes06 = (PostSwipes06 + '$post06data[Swipes]'),
						PostSpend06 = (PostSpend06 + '$post06data[TotalSpend]'),
						PostSegCode06 = '$post06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes06'] = $post06data['Swipes'];
					$updatedata['PostSpend06'] = $post06data['TotalSpend'];
					$updatedata['PostSegCode06'] = $post06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			unset($updatedata);
			unset($cardrow);
			unset($post06data);
			unset($thiscard);

		}
		
		unset($cards);
		
	}
}


if(date("Y-m-d") > '2006-10-07')
{

	echo "Processing Sept06\n";

	if(mysqlSelect($cards,"distinct CardNo","NonnormailsedTransactionLog200609"," 1 ",0) >0)
	{

		$count = '0';
	
		foreach($cards as $cardrow)
		{
		
#		echo "Card is $cardrow[CardNo] \n";
		
		
		
			if(mysqlSelect($post06data,"count(*) as Swipes, sum(TransactionValue) as TotalSpend,SegmentCode",
			"NonnormailsedTransactionLog200609","CardNo = '$cardrow[CardNo]' and 
			TransactionDate between '2006-07-03' and '2006-10-09' group by CardNo ") >0)
			{

#		echo "Got Cards $post06data[Swipes]/$post06data[TotalSpend]/$post06data[SegmentCode]\n";

				
				if(mysqlSelect($thiscard,"CardNo,PostSpend06,PostSwipes06","SpendBands"," CardNo = '$cardrow[CardNo]' ") >0)
				{
					#	Card exists so update
					#$updaterecord = mysqlUpdate($updatedata,"SpendBands"," CardNo = '$cardrow[CardNo]' ");
					$sql = "update SpendBands set 
						PostSwipes06 = (PostSwipes06 + '$post06data[Swipes]'),
						PostSpend06 = (PostSpend06 + '$post06data[TotalSpend]'),
						PostSegCode06 = '$post06data[SegmentCode]'
						where CardNo = '$cardrow[CardNo]' ";		

					DBQueryExitOnFailure($sql);					
				}
				else
				{
					$updatedata = array();
					$updatedata['CardNo'] = $cardrow['CardNo'];
					$updatedata['PostSwipes06'] = $post06data['Swipes'];
					$updatedata['PostSpend06'] = $post06data['TotalSpend'];
					$updatedata['PostSegCode06'] = $post06data['SegmentCode'];
				
					$insertrecord = mysqlInsert($updatedata,"SpendBands");
					
				}
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}

			unset($updatedata);
			unset($cardrow);
			unset($post06data);
			unset($thiscard);

		}
	
		unset($cards);
		
	}

}




}







function CreateCumulativeReport()
{

	#	Cumulative Spend Report
	
	$lower = 0;
	$upper = 100;
	
	while($lower < 600)
	{
		mysqlSelect($pre05data,"count(*) as Qty, sum(PreSpend05) as TotalSpend","SpendBands","PreSpend05 > $lower and PreSpend05 <= $upper ");
		mysqlSelect($during05data,"count(*) as Qty, sum(DuringSpend05) as TotalSpend","SpendBands","DuringSpend05 > $lower and DuringSpend05 <= $upper");
		mysqlSelect($post05data,"count(*) as Qty, sum(DuringSpend05) as TotalSpend","SpendBands","PostSpend05 > $lower and PostSpend05 <= $upper");
		mysqlSelect($pre06data,"count(*) as Qty, sum(PreSpend06) as TotalSpend","SpendBands","PreSpend06 > $lower and PreSpend06 <= $upper ");
		mysqlSelect($during06data,"count(*) as Qty, sum(DuringSpend06) as TotalSpend","SpendBands","DuringSpend06 > $lower and DuringSpend06 <= $upper");
		mysqlSelect($post06data,"count(*) as Qty, sum(DuringSpend06) as TotalSpend","SpendBands","PostSpend06 > $lower and PostSpend06 <= $upper");

		$insertdata['Band'] 		= "$lower - $upper";
		$insertdata['PreQty05'] 	= $pre05data[Qty];	
		$insertdata['PreSpend05'] 	= $pre05data[TotalSpend];
		$insertdata['DuringQty05'] 	= $during05data[Qty];
		$insertdata['DuringSpend05'] 	= $during05data[TotalSpend];
		$insertdata['PostQty05'] 	= $post05data[Qty];	
		$insertdata['PostSpend05'] 	= $post05data[TotalSpend];  
		$insertdata['PreQty06'] 	= $pre06data[Qty];	
		$insertdata['PreSpend06'] 	= $pre06data[TotalSpend];   
		$insertdata['DuringQty06'] 	= $during06data[Qty];
		$insertdata['DuringSpend06'] 	= $during06data[TotalSpend];
		$insertdata['PostQty06'] 	= $post06data[Qty];	
		$insertdata['PostSpend06'] 	= $post06data[TotalSpend];  
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsCumulativeReport");

		$lower += 100;
		$upper += 100;
		
		unset($pre05data);
		unset($during05data);
		unset($post05data);
		unset($pre06data);
		unset($during06data);
		unset($post06data);
		unset($insertrecord);

	}
	
	#	Now calculate the final band
	
	
	mysqlSelect($pre05data,"count(*) as Qty, sum(PreSpend05) as TotalSpend","SpendBands","PreSpend05 > $lower");
	mysqlSelect($during05data,"count(*) as Qty, sum(DuringSpend05) as TotalSpend","SpendBands","DuringSpend05 > $lower ");
	mysqlSelect($post05data,"count(*) as Qty, sum(DuringSpend05) as TotalSpend","SpendBands","PostSpend05 > $lower");
	mysqlSelect($pre06data,"count(*) as Qty, sum(PreSpend06) as TotalSpend","SpendBands","PreSpend06 > $lower");
	mysqlSelect($during06data,"count(*) as Qty, sum(DuringSpend06) as TotalSpend","SpendBands","DuringSpend06 > $lower");
	mysqlSelect($post06data,"count(*) as Qty, sum(DuringSpend06) as TotalSpend","SpendBands","PostSpend06 > $lower");
	
	$insertdata['Band'] 		= "$lower +";
	$insertdata['PreQty05'] 	= $pre05data[Qty];	
	$insertdata['PreSpend05'] 	= $pre05data[TotalSpend];
	$insertdata['DuringQty05'] 	= $during05data[Qty];
	$insertdata['DuringSpend05'] 	= $during05data[TotalSpend];
	$insertdata['PostQty05'] 	= $post05data[Qty];	
	$insertdata['PostSpend05'] 	= $post05data[TotalSpend];  
	$insertdata['PreQty06'] 	= $pre06data[Qty];	
	$insertdata['PreSpend06'] 	= $pre06data[TotalSpend];   
	$insertdata['DuringQty06'] 	= $during06data[Qty];
	$insertdata['DuringSpend06'] 	= $during06data[TotalSpend];
	$insertdata['PostQty06'] 	= $post06data[Qty];	
	$insertdata['PostSpend06'] 	= $post06data[TotalSpend];  
	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsCumulativeReport");

	

}



function CreateSegmentationReport()
{

	#	Segmentation Report
	
	echo"Creating Segmentation Report\r\n";
	
	#	Im certain this isn't the best way of doing it but my head couldnt
	#	cope with trying to work out an alternative.
	
	mysqlSelect($pre05segA,"count(*) as Qty","SpendBands","PreSegCode05 like 'A%' AND PreSpend05 > 0");
	mysqlSelect($pre05segN,"count(*) as Qty","SpendBands","PreSegCode05 like 'N%' AND PreSpend05 > 0");
	mysqlSelect($pre05segL,"count(*) as Qty","SpendBands","PreSegCode05 like 'L%' AND PreSpend05 > 0");
	mysqlSelect($pre05segD,"count(*) as Qty","SpendBands","PreSegCode05 like 'D%' AND PreSpend05 > 0");
	mysqlSelect($pre05segX,"count(*) as Qty","SpendBands","PreSegCode05 like 'X%' AND PreSpend05 > 0");
	mysqlSelect($pre05segNULL,"count(*) as Qty","SpendBands","(PreSegCode05 is NULL OR PreSegCode05 ='') AND PreSpend05 > 0");

	mysqlSelect($during05segA,"count(*) as Qty","SpendBands","DuringSegCode05 like 'A%' AND DuringSpend05 > 0");
	mysqlSelect($during05segN,"count(*) as Qty","SpendBands","DuringSegCode05 like 'N%' AND DuringSpend05 > 0");
	mysqlSelect($during05segL,"count(*) as Qty","SpendBands","DuringSegCode05 like 'L%' AND DuringSpend05 > 0");
	mysqlSelect($during05segD,"count(*) as Qty","SpendBands","DuringSegCode05 like 'D%' AND DuringSpend05 > 0");
	mysqlSelect($during05segX,"count(*) as Qty","SpendBands","DuringSegCode05 like 'X%' AND DuringSpend05 > 0");
	mysqlSelect($during05segNULL,"count(*) as Qty","SpendBands","(DuringSegCode05 is NULL OR DuringSegCode05 ='') AND DuringSpend05 > 0");
	
	mysqlSelect($post05segA,"count(*) as Qty","SpendBands","PostSegCode05 like 'A%' AND PostSpend05 > 0");
	mysqlSelect($post05segN,"count(*) as Qty","SpendBands","PostSegCode05 like 'N%' AND PostSpend05 > 0");
	mysqlSelect($post05segL,"count(*) as Qty","SpendBands","PostSegCode05 like 'L%' AND PostSpend05 > 0");
	mysqlSelect($post05segD,"count(*) as Qty","SpendBands","PostSegCode05 like 'D%' AND PostSpend05 > 0");
	mysqlSelect($post05segX,"count(*) as Qty","SpendBands","PostSegCode05 like 'X%' AND PostSpend05 > 0");
	mysqlSelect($post05segNULL,"count(*) as Qty","SpendBands","(PostSegCode05 is NULL  OR PostSegCode05 ='')AND PostSpend05 > 0");
	
	mysqlSelect($pre06segA,"count(*) as Qty","SpendBands","PreSegCode06 like 'A%' AND PreSpend06 > 0");
	mysqlSelect($pre06segN,"count(*) as Qty","SpendBands","PreSegCode06 like 'N%' AND PreSpend06 > 0");
	mysqlSelect($pre06segL,"count(*) as Qty","SpendBands","PreSegCode06 like 'L%' AND PreSpend06 > 0");
	mysqlSelect($pre06segD,"count(*) as Qty","SpendBands","PreSegCode06 like 'D%' AND PreSpend06 > 0");
	mysqlSelect($pre06segX,"count(*) as Qty","SpendBands","PreSegCode06 like 'X%' AND PreSpend06 > 0");
	mysqlSelect($pre06segNULL,"count(*) as Qty","SpendBands","(PreSegCode06 is NULL OR PreSegCode06 ='') AND PreSpend06 > 0");

	mysqlSelect($during06segA,"count(*) as Qty","SpendBands","DuringSegCode06 like 'A%' AND DuringSpend06 > 0");
	mysqlSelect($during06segN,"count(*) as Qty","SpendBands","DuringSegCode06 like 'N%' AND DuringSpend06 > 0");
	mysqlSelect($during06segL,"count(*) as Qty","SpendBands","DuringSegCode06 like 'L%' AND DuringSpend06 > 0");
	mysqlSelect($during06segD,"count(*) as Qty","SpendBands","DuringSegCode06 like 'D%' AND DuringSpend06 > 0");
	mysqlSelect($during06segX,"count(*) as Qty","SpendBands","DuringSegCode06 like 'X%' AND DuringSpend06 > 0");
	mysqlSelect($during06segNULL,"count(*) as Qty","SpendBands","(DuringSegCode06 is NULL OR DuringSegCode06 ='') AND DuringSpend06 > 0");
	
	mysqlSelect($post06segA,"count(*) as Qty","SpendBands","PostSegCode06 like 'A%' AND PostSpend06 > 0");
	mysqlSelect($post06segN,"count(*) as Qty","SpendBands","PostSegCode06 like 'N%' AND PostSpend06 > 0");
	mysqlSelect($post06segL,"count(*) as Qty","SpendBands","PostSegCode06 like 'L%' AND PostSpend06 > 0");
	mysqlSelect($post06segD,"count(*) as Qty","SpendBands","PostSegCode06 like 'D%' AND PostSpend06 > 0");
	mysqlSelect($post06segX,"count(*) as Qty","SpendBands","PostSegCode06 like 'X%' AND PostSpend06 > 0");
	mysqlSelect($post06segNULL,"count(*) as Qty","SpendBands","(PostSegCode06 is NULL OR PostSegCode06 ='') AND PostSpend06 > 0");
	

	$insertdata['SegmentCode'] =	"A*";
	$insertdata['PreQty05'] =	$pre05segA['Qty'];
	$insertdata['DuringQty05'] =	$during05segA['Qty'];
	$insertdata['PostQty05'] =	$post05segA['Qty'];	
	$insertdata['PreQty06'] =	$pre06segA['Qty'];		
	$insertdata['DuringQty06'] =	$during06segA['Qty'];
	$insertdata['PostQty06'] =	$post06segA['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);

	$insertdata['SegmentCode'] =	"N*";
	$insertdata['PreQty05'] =	$pre05segN['Qty'];
	$insertdata['DuringQty05'] =	$during05segN['Qty'];
	$insertdata['PostQty05'] =	$post05segN['Qty'];	
	$insertdata['PreQty06'] =	$pre06segN['Qty'];		
	$insertdata['DuringQty06'] =	$during06segN['Qty'];
	$insertdata['PostQty06'] =	$post06segN['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);

	$insertdata['SegmentCode'] =	"L*";
	$insertdata['PreQty05'] =	$pre05segL['Qty'];
	$insertdata['DuringQty05'] =	$during05segL['Qty'];
	$insertdata['PostQty05'] =	$post05segL['Qty'];	
	$insertdata['PreQty06'] =	$pre06segL['Qty'];		
	$insertdata['DuringQty06'] =	$during06segL['Qty'];
	$insertdata['PostQty06'] =	$post06segL['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);

	$insertdata['SegmentCode'] =	"D*";
	$insertdata['PreQty05'] =	$pre05segD['Qty'];
	$insertdata['DuringQty05'] =	$during05segD['Qty'];
	$insertdata['PostQty05'] =	$post05segD['Qty'];	
	$insertdata['PreQty06'] =	$pre06segD['Qty'];		
	$insertdata['DuringQty06'] =	$during06segD['Qty'];
	$insertdata['PostQty06'] =	$post06segD['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);

	$insertdata['SegmentCode'] =	"X*";
	$insertdata['PreQty05'] =	$pre05segX['Qty'];
	$insertdata['DuringQty05'] =	$during05segX['Qty'];
	$insertdata['PostQty05'] =	$post05segX['Qty'];	
	$insertdata['PreQty06'] =	$pre06segX['Qty'];		
	$insertdata['DuringQty06'] =	$during06segX['Qty'];
	$insertdata['PostQty06'] =	$post06segX['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);
	
	$insertdata['SegmentCode'] =	"NULL";
	$insertdata['PreQty05'] =	$pre05segNULL['Qty'];
	$insertdata['DuringQty05'] =	$during05segNULL['Qty'];
	$insertdata['PostQty05'] =	$post05segNULL['Qty'];	
	$insertdata['PreQty06'] =	$pre06segNULL['Qty'];		
	$insertdata['DuringQty06'] =	$during06segNULL['Qty'];
	$insertdata['PostQty06'] =	$post06segNULL['Qty'];	
	$insertrecord = mysqlInsert($insertdata,"SpendBandsSegmentationReport");
	unset($insertrecord);	
	
}





function CreateTopSpendersReport()
{
	echo"Creating Top Spenders Report\r\n";
#	Pre 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSpend05","SpendBands","PreSpend05 > 0 order by PreSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend05 > $minvalue and PreSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	During 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSpend05","SpendBands","DuringSpend05 > 0 order by DuringSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend05 > $minvalue and DuringSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	Post 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSpend05","SpendBands","PostSpend05 > 0 order by PostSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend05 > $minvalue and PostSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);

#	Pre 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSpend06","SpendBands","PreSpend06 > 0 order by PreSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend06 > $minvalue and PreSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	During 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSpend06","SpendBands","DuringSpend06 > 0 order by DuringSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend06 > $minvalue and DuringSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	Post 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSpend06","SpendBands","PostSpend06 > 0 order by PostSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend06 > $minvalue and PostSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);
 
}


function CreateBandedTopSpendersReport()
{
	echo"Creating Banded Top Spenders Report\r\n";

#	Pre 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSpend05","SpendBands","PreSpend05 > 0 order by PreSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend05 > $minvalue and PreSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	During 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSpend05 > 0 and PreSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSpend05","SpendBands","DuringSpend05 > 0 and PreSpend05 > 0 order by DuringSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend05 > $minvalue and DuringSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	Post 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSpend05 > 0 AND PreSpend05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSpend05","SpendBands","PostSpend05 > 0 AND PreSpend05 > 0 order by PostSpend05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSpend05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend05 > $minvalue and PostSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);

#	Pre 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSpend06 > 0 AND PreSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSpend06","SpendBands","PreSpend06 > 0  AND PreSpend06 > 0 order by PreSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend06 > $minvalue and PreSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	During 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSpend06 > 0 AND PreSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSpend06","SpendBands","DuringSpend06 > 0 AND PreSpend06 > 0 order by DuringSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend06 > $minvalue and DuringSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);



#	Post 06 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSpend06 > 0 AND PreSpend06 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSpend06","SpendBands","PostSpend06 > 0 AND PreSpend06 > 0 order by PostSpend06 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSpend06'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend06 > $minvalue and PostSpend06 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post06";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSpend'] 	=	$total;
		$insertdata['AverageValue'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedTopSpendersReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);
 
}



function CreateFrequencyReport()
{
	echo"Creating Frequency Report\r\n";

#	Pre 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSwipes05","SpendBands","PreSwipes05 > 0 order by PreSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend05 > $minvalue and PreSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);

#	During 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSwipes05","SpendBands","DuringSwipes05 > 0 order by DuringSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend05 > $minvalue and DuringSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);
 
 
#	Post 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSwipes05","SpendBands","PostSwipes05 > 0 order by PostSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend05 > $minvalue and PostSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals); 
 
 
 #	Pre 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSwipes06 > 0");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"PreSwipes06","SpendBands","PreSwipes06 > 0 order by PreSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['PreSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend06 > $minvalue and PreSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"Pre06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
 	unset($totals);
 
 #	During 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSwipes06 > 0");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"DuringSwipes06","SpendBands","DuringSwipes06 > 0 order by DuringSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['DuringSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend06 > $minvalue and DuringSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"During06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
 	unset($totals);
  
  
 #	Post 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSwipes06 > 0");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"PostSwipes06","SpendBands","PostSwipes06 > 0 order by PostSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['PostSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend06 > $minvalue and PostSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"Post06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
	unset($totals); 
 
 
 
 
 
 
 
 
 
 
}


function CreateBandedFrequencyReport()
{
	echo"Creating Banded Frequency Report\r\n";

#	Pre 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PreSwipes05","SpendBands","PreSwipes05 > 0 order by PreSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PreSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend05 > $minvalue and PreSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Pre05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);

#	During 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSwipes05 > 0 AND PreSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"DuringSwipes05","SpendBands","DuringSwipes05 > 0  AND PreSwipes05 > 0 order by DuringSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['DuringSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend05 > $minvalue and DuringSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"During05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals);
 
 
#	Post 05 Data	

	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSwipes05 > 0 AND PreSwipes05 > 0");
	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
	$total = 0;
	$offset = 0;
	$maxvalue = $batchvalue;
	$i = 1;	
	
	echo"NumRows = $totals[NumRows]\n\r";
		
	while ($i < 11)
	{
	
	
		mysqlSelect($batch,"PostSwipes05","SpendBands","PostSwipes05 > 0 AND PreSwipes05 > 0 order by PostSwipes05 DESC ","$offset,$batchvalue");
		foreach($batch as $line)
		{
			$total += $line['PostSwipes05'];	
			unset($line);
		}
		unset($batch);		
		
		$average = number_format( ($total / $batchvalue), 2, ".", "");
		
		
		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend05 > $minvalue and PostSpend05 < $maxvalue");
	
		$insertdata['Batch'] 		=	"Post05";
		$insertdata['id'] 		=	$i;
		$insertdata['NoOfCustomers'] 	=	$batchvalue;
		$insertdata['TotalSwipes'] 	=	$total;
		$insertdata['AverageSwipes'] 	=	$average;		
		
		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
		unset($insertrecord);

		$offset += $batchvalue;
		$i++;
		$total=0;
		
		unset($batch);
		unset($batchqty);
		unset($insertdata);
	}
	
	unset($totals); 
 
 
 #	Pre 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PreSwipes06 > 0");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"PreSwipes06","SpendBands","PreSwipes06 > 0 order by PreSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['PreSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PreSpend06 > $minvalue and PreSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"Pre06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
 	unset($totals);
 
 #	During 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","DuringSwipes06 > 0 AND PreSwipes06 > 0 ");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"DuringSwipes06","SpendBands","DuringSwipes06 > 0 AND PreSwipes06 > 0  order by DuringSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['DuringSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","DuringSpend06 > $minvalue and DuringSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"During06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
 	unset($totals);
  
  
 #	Post 06 Data	
 
 	mysqlSelect($totals,"count(*) as NumRows","SpendBands","PostSwipes06 > 0 AND PreSwipes06 > 0 ");
 	$batchvalue = number_format(($totals['NumRows'] / 10), 0, ".", "");
 	$total = 0;
 	$offset = 0;
 	$maxvalue = $batchvalue;
 	$i = 1;	
 	
 	echo"NumRows = $totals[NumRows]\n\r";
 		
 	while ($i < 11)
 	{
 	
 	
 		mysqlSelect($batch,"PostSwipes06","SpendBands","PostSwipes06 > 0  AND PreSwipes06 > 0 order by PostSwipes06 DESC ","$offset,$batchvalue");
 		foreach($batch as $line)
 		{
 			$total += $line['PostSwipes06'];	
 			unset($line);
 		}
 		unset($batch);		
 		
 		$average = number_format( ($total / $batchvalue), 2, ".", "");
 		
 		
 		# mysqlSelect($batchqty,"count(*) as Qty","SpendBands","PostSpend06 > $minvalue and PostSpend06 < $maxvalue");
 	
 		$insertdata['Batch'] 		=	"Post06";
 		$insertdata['id'] 		=	$i;
 		$insertdata['NoOfCustomers'] 	=	$batchvalue;
 		$insertdata['TotalSwipes'] 	=	$total;
 		$insertdata['AverageSwipes'] 	=	$average;		
 		
 		$insertrecord = mysqlInsert($insertdata,"SpendBandsBandedFrequencyReport");
 		unset($insertrecord);
 
 		$offset += $batchvalue;
 		$i++;
 		$total=0;
 		
 		unset($batch);
 		unset($batchqty);
 		unset($insertdata);
 	}
 	
	unset($totals); 
 
 
 
 
 
 
 
 
 
 
}




function UpdateSegments($field,$kpitable)
{


	if(mysqlSelect($cards,"CardNo","SpendBands","$field is NULL",0) >0)
	{
		$count =0;
		
		echo "UpdateSegments - Processing $field\n";
		foreach($cards as $cardrow)
		{
		
			if(mysqlSelect($kpidata,"Recency","$kpitable","CardNo = '$cardrow[CardNo]'") >0)
			{

				$sql = "update SpendBands set $field = '$kpidata[Recency]' where CardNo = '$cardrow[CardNo]' ";		

				DBQueryExitOnFailure($sql);					
			}

			$count++;
			if( ($count % 20000) == 0 )
			{
				echo date("H:i:s");
				echo "Processed $count records\n\r";
			}
			
			unset($updatedata);
			unset($cardrow);
			unset($kpidata);

		}
		
		unset($cards);
		
	}


}

?>