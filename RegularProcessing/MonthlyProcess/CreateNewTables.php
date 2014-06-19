<?php
	$db_host = "weoudb";
	$db_name = "texaco";
	$db_user = "ReportGenerator";
	$db_pass = "tldttoths";
	#$db_user = "root";
	#$db_pass = "Trave1";
	require "../../include/DB.inc";
	// MRM 01/04/10 - update to revision 387 when Mantis 2022 is ready for implementation
	function GrantUserAccess( $month, $user )
	{
		echo date("H:i:s");
		$sql = "GRANT INSERT, SELECT, UPDATE ON `texaco`.`Transactions$month` TO '$user'@'weoudb'";
		echo " $sql\n";
		DBQueryExitOnFailure($sql);
		$sql = "GRANT INSERT ON `texaco`.`BonusHit$month` TO '$user'@'weoudb'";
		echo " $sql\n";
		DBQueryExitOnFailure($sql);
		$sql = "GRANT INSERT ON `texaco`.`ProductsPurchased$month` TO '$user'@'weoudb'";
		echo " $sql\n";
		DBQueryExitOnFailure($sql);
	}

	function CreateMonthTables( $month )
	{
		$sql = "create table Transactions$month
				(  `TransactionNo` int(11) NOT NULL auto_increment primary key,
				  `Month` int(11) default $month,
				  `CardNo` varchar(20) NOT NULL default '',
				  `AccountNo` bigint(20) default NULL,
				  `SiteCode` int(11) default NULL,
				  `TransTime` datetime default NULL,
				  `TransValue` decimal(6,2) default NULL,
				  `PanInd` char(1) default NULL,
				  `Flag` char(1) default NULL,
				  `PayMethod` char(1) default NULL,
				  `PointsAwarded` int(11) default NULL,
				  `InputFile` varchar(25) default NULL,
				  `ReceiptNo` varchar(10) default NULL,
				  `EFTTransNo` int(11) default NULL,
				  `CreationDate` datetime default NULL,
				  `CreatedBy` varchar(20) default NULL,	
				  INDEX( CardNo ) )";
		echo date("H:i:s");
		echo " Creating Transactions$month table\n";	
		DBQueryExitOnFailure($sql);
		

		$sql = "create table ProductsPurchased$month
				(
					Month		integer default $month,
					TransactionNo	BIGINT NOT NULL,
					SequenceNo	TINYINT NOT NULL,
					DepartmentCode  integer,
					ProductCode	integer,
					PointsAwarded	integer,
					Quantity	integer,
					Value		Decimal(10,2),

					PRIMARY KEY ( TransactionNo , SequenceNo ) 

				)";
		echo date("H:i:s");
		echo " Creating ProductsPurchased$month table\n";		
		DBQueryExitOnFailure($sql);


		$sql = "create table BonusHit$month
				(
					Month		integer default $month,
					TransactionNo	BIGINT NOT NULL,
					SequenceNo	TINYINT NOT NULL,
					PromotionCode	varchar(10),
					Points		integer,

					PRIMARY KEY ( TransactionNo , SequenceNo ) 
				)";
		echo date("H:i:s");
		echo " Creating BonusHit$month table\n";
		DBQueryExitOnFailure($sql);
	}

	function GetNewMonth()
	{
		$sql = "select date_format( now(), '%Y%m' )";
		$result = DBQueryExitOnFailure($sql);

		$row = mysql_fetch_row( $result );
		return $row[0];
	}

	function CreateTransactionMasterTable( $finalMonth )
	{
		$SubTables = "";
		$y = 2004;
		$m = 01;
		$M = "";
		$c = "";
		while( $M < $finalMonth )
		{
			$M = sprintf( "%04d%02d", $y, $m );
			$SubTables .= "$c Transactions$M";  
			$c = ",";
			$m++;
			if( $m > 12 )
			{
				$m = 1;
				$y++;
			}
		}

		$sql = "flush tables";
 		echo date("H:i:s");
 		echo " Flushing tables\n";
		DBQueryExitOnFailure($sql);

		$sql = "Drop table Transactions";
		echo date("H:i:s");
 		echo " Dropping Transactions Merge tables\n";
 		DBQueryExitOnFailure($sql);
 		
		$sql = "flush tables";
		echo date("H:i:s");
 		echo " Flushing tables\n";
 		DBQueryExitOnFailure($sql);

		$sql = "create table Transactions(
  `TransactionNo` int(11) NOT NULL auto_increment,
  `Month` int(11) default '1',
  `CardNo` varchar(20) NOT NULL default '',
  `AccountNo` bigint(20) default NULL,
  `SiteCode` int(11) default NULL,
  `TransTime` datetime default NULL,
  `TransValue` decimal(6,2) default NULL,
  `PanInd` char(1) default NULL,
  `Flag` char(1) default NULL,
  `PayMethod` char(1) default NULL,
  `PointsAwarded` int(11) default NULL,
  `InputFile` varchar(25) default NULL,
  `ReceiptNo` varchar(10) default NULL,
  `EFTTransNo` int(11) default NULL,
  `CreationDate` datetime default NULL,
  `CreatedBy` varchar(20) default NULL,
UNIQUE KEY(Month,TransactionNo),
INDEX( CardNo ))  ENGINE=MERGE UNION=($SubTables) INSERT_METHOD=NO";
		echo date("H:i:s");
		echo " Creating Transactions Merge table\n";
		DBQueryExitOnFailure($sql);
	}

	connectToDB( MasterServer, TexacoDB );

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	$newMonth = GetNewMonth();
	CreateMonthTables( $newMonth  );
	GrantUserAccess( $newMonth , "CompowerProcess" );
	GrantUserAccess( $newMonth , "FISProcess" );
	GrantUserAccess( $newMonth , "UKFuelsProcess" );
	GrantUserAccess( $newMonth , "DAdmin" );
	GrantUserAccess( $newMonth , "SAdmin" );
	CreateTransactionMasterTable( $newMonth  );
	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>
	