<?php

#	functions.php

function CheckReplicationStopped()
{
	$sql = "Show Slave Status";

	$result = DBQueryExitOnFailure( $sql );
	$srow = mysql_fetch_assoc( $result );
	
	if( $srow["Slave_IO_Running"] != "No" )
	{
		echo "You need to stop Replication to have a static DB";
		exit();
	}
}

function GetNewMonth()
{
	$sql = "select date_format( now(), '%Y%m' )";
	$result = DBQueryExitOnFailure($sql);

	$row = mysql_fetch_row( $result );
	return $row[0];
}

function DecrementMonth( $month )
{
	if( ($month % 100) == 1 )
	{
		return ($month - 100 + 11);
	}
	else
	{
		return ($month - 1);
	}
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
 		DBQueryExitOnFailure($sql);

		$sql = "Drop table Transactions";
 		DBQueryExitOnFailure($sql);
 		echo "$sql\n";

		$sql = "flush tables";
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
		DBQueryExitOnFailure($sql);
		echo "$sql\n";
	}	
	
	
	

function outputheaders($reportrow, $delimiter=',')
{
	$output = '';

	if (is_array($reportrow))
	{
		$fields = count($reportrow);
		$fieldno = 0;

		foreach ($reportrow as $fieldname => $reportfield)
		{
			$fieldno++;

			// get rid of any double quote characters (") in the data
			$fieldname = str_replace('"', '', $fieldname);

			// if there's a delimiter in the data surround it with double quotes
			if (strpos($fieldname, $delimiter))
			{
				$output .= '"'.$fieldname.'"';
			}
			else
			{
				$output .=  $fieldname;
			}

			if ($fieldno != $fields)
			{
				$output .=  $delimiter;
			}
		}

		$output .=  "r\n";
	}

	return($output);
}


function outputreport($reportdata, $delimiter=',')
{
	$output = '';
	
	if (is_array($reportdata))
	{
		foreach ($reportdata as $reportrow)
		{
			$fields = count($reportrow);
			$fieldno = 0;

			foreach ($reportrow as $fieldname => $reportfield)
			{
				$fieldno++;



				// get rid of any double quote characters (") in the data
				$reportfield = str_replace('"', '', $reportfield);
				
				// get rid of any cr lf in the data
				$reportfield = str_replace('\r\n', '', $reportfield);
				

				// if there's a delimiter in the data surround it with double quotes
				#if (strpos($reportfield, $delimiter))
				#{
					$output .= '"'.$reportfield.'"';
				#}
				#else
				#{
				#	$output .= $reportfield;
				#}

				if ($fieldno != $fields)
				{
					$output .= $delimiter;
				}
			}

			$output .= "\r\n";
		}
	}

	return($output);
}




function outputreportdown($reportdata, $delimiter=',')
{
	if (is_array($reportdata))
	{
		foreach ($reportdata as $reportrow)
		{
			$fields = count($reportrow);
			$fieldno = 0;

			foreach ($reportrow as $fieldname => $reportfield)
			{
				$output .= $fieldname;

				$output .= $delimiter;

				// get rid of any double quote characters (") in the data
				$reportfield = str_replace('"', '', $reportfield);

				// if there's a delimiter in the data surround it with double quotes
				if (strpos($reportfield, $delimiter))
				{
					$output .= '"'.$reportfield.'"';
				}
				else
				{
					$output .= $reportfield;
				}


			$output .= "\n";


			}

		}
	}

	return($output);
}


?>
