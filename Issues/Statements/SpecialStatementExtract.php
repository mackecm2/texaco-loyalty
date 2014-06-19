<?php

include "../../include/DB.inc";

$db_user = "root";
$db_pass = "Trave1";

// To use this file you need to change the data in the next two lines and
// the query for the listcode data.

#$PreviousStatementIdentifier = "Jan06";
#$PreviousStatemntStopTime = "2006-01-18 12:04:25";
#$PreviousStatementIdentifier = "May06";
#$PreviousStatemntStopTime = "2006-05-09 11:00:00";
#$PreviousStatementIdentifier = "Oct06";
#$PreviousStatemntStopTime = "2006-10-11 17:18:02";
#$PreviousStatementIdentifier = "Feb07";
#$PreviousStatemntStopTime = "2007-02-05 14:55:21";
#$PreviousStatementIdentifier = "Jun07";
#$PreviousStatemntStopTime = "2007-06-27 12:37:59";
#$PreviousStatementIdentifier = "Oct07";
#$PreviousStatemntStopTime = "2007-10-23 08:53:06";

$PreviousStatementIdentifier = "Feb08";
$PreviousStatemntStopTime = "2008-02-05 12:58:38";

$ThisStatementStopTime = "2008-08-21 12:26:04";

connectToDB( AnalysisServer, AnalysisDB );

CheckReplicationStopped();

$StatementIdentifier = GetStatmentIdentifier();

#$StatementIdentifier .= "B";

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "--------------- Statement Extract for $StatementIdentifier Statement ---------------\r\n";
echo "Process Started $timedate\r\n";

echo "Creating Tables for $StatementIdentifier\n";

$ListcodeTable = $StatementIdentifier."StatementListcodes";					  

$sql = "Drop Table if exists  $ListcodeTable";

#DBQueryExitOnFailure( $sql );

$ListcodeQuery = "create Table $ListcodeTable 
select M.AccountNo, MemberNo,C.CardNo as PrimaryCard,'StarRewards' as CardType,
'1' as ListCode, 
Balance,
SegmentCode,
StatementPref
from texaco.Accounts join texaco.Members as M using(AccountNo) join texaco.Cards as C using (MemberNo)
where C.CardNo >= '7076550200814000009'
and C.CardNo <= '7076550200817073862'  
and RedemptionStopDate is null  
and AwardStopDate is null  
and M.GoneAway = 'N'  
and M.Deceased = 'N'  
and M.NonUKAddress = 'N'
and Balance > 0
";

#die("$ListcodeQuery\n");
	
#$result = DBQueryExitOnFailure( $ListcodeQuery );

#Extract1MonthTotals( $StatementIdentifier );
#CreateSpendTotals( $StatementIdentifier );
#CreateStatementBalance( $StatementIdentifier );
#BringForward( $StatementIdentifier, $PreviousStatementIdentifier );
#PeriodEarn( $StatementIdentifier, $PreviousStatemntStopTime );
#PeriodRedeem( $StatementIdentifier, $PreviousStatemntStopTime );
#PeriodAdjustments( $StatementIdentifier, $PreviousStatemntStopTime );
#CorrectForNonmailed( $StatementIdentifier ); 
#CorrectCancelledRedemptions( $StatementIdentifier, $PreviousStatemntStopTime );
#CheckForErrors( $StatementIdentifier );

#CreateOutputFile( $StatementIdentifier, FileName, Criteria );
unlink("/data/Aug08StatementData.csv");
CreateOutputFile( $StatementIdentifier, '/data/Aug08StatementData.csv', '1' );

// select *, BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents - Balance as Error  from Mar06StatementBalance where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents

function  CheckForErrors( $StatementIdentifier )
{
	$sql = "select *, BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents - Balance as Error  from ".$StatementIdentifier."StatementBalance where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents";

	echo "$sql\n";

 	$results = DBQueryExitOnFailure( $sql );

	echo "There were ". mysql_num_rows( $results ) . "Records that have not worked";

}

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

function GetStatmentIdentifier()
{
	$sql = "select Date_format( now(), '%b%y' )";
	return DBSingleStatQuery( $sql );
}


function GenerateMonthList( $type )
{

	$swipes = "";
	$spends = "";
	$gswipes = "";
	$gspend ="";

	for( $i=12; $i > 0; $i-- )
	{
		$msql = "select Date_format( date_sub( now(), Interval $i MONTH ), '%Y%m' ) as MonthId,  Date_format( date_sub( now(), Interval $i MONTH ), '%b' ) as MonthName";

		$result = DBQueryExitOnFailure( $msql );
		$srow = mysql_fetch_assoc( $result );

		$swipes .= ", sum( if( YearMonth = '$srow[MonthId]', Swipes, 0 ) ) as $srow[MonthName]Swipes";
		$spends .= ", sum( if( YearMonth = '$srow[MonthId]', SpendVal, 0 ) ) as $srow[MonthName]SpendVal";

		$gswipes .= "$srow[MonthName]Swipes,";
		$gspend  .= "$srow[MonthName]SpendVal,";
	}

	if( $type )
	{
		return $swipes . $spends;
	}
	else
	{
		return $gswipes . $gspend;
	}
}

function Extract1MonthTotals( $StatementIdentifier )
{
	global $gswipes, $gspend;

	$sql = "Drop Table if exists  ".$StatementIdentifier."AccountTotals";
	$result = DBQueryExitOnFailure( $sql );

	$sql = "create table ".$StatementIdentifier."AccountTotals select AccountNo";


	$stuff = GenerateMonthList( true );

	$sql = $sql . $stuff . " from texaco.AccountMonthly2003 group by AccountNo";

	$result = DBQueryExitOnFailure( $sql );

	$sql = "alter table ".$StatementIdentifier."AccountTotals add primary key(AccountNo)";
	$result = DBQueryExitOnFailure( $sql );
}

function CreateStatementBalance( $StatementIdentifier )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementBalance";

	DBQueryExitOnFailure( $sql );
	
	$sql = "create Table ".$StatementIdentifier ."StatementBalance
	select AccountNo, Balance, 0 as AdjustMents, 0 as Redemptions, 0 as BroughtForward, 
	0 as StandardAwarded, 0 as BonusAwarded from ".$StatementIdentifier ."StatementListcodes";

	echo "$sql\n";
	
	$result = DBQueryExitOnFailure( $sql );

	$sql = "alter table ".$StatementIdentifier."StatementBalance add index (AccountNo)";
	$result = DBQueryExitOnFailure( $sql );
}

function BringForward( $NewStatement, $OldStatement )
{
	$OldStatement = $OldStatement."StatementBalance";
	//$sql = "update ".$NewStatement."StatementBalance as NewStatement join ".$OldStatement."StatementBalance as OldStatement using( AccountNo) set NewStatement.BroughtForward = OldStatement.Balance";

$sql = "update ".$NewStatement."StatementBalance as NewStatement join ".$OldStatement." as OldStatement using( AccountNo) set NewStatement.BroughtForward = OldStatement.Balance";


echo "$sql\n";

	$result = DBQueryExitOnFailure( $sql );
}

function PeriodEarn( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementEarn";
	DBQueryExitOnFailure( $sql );
	
	$sql = "create Table ".$StatementIdentifier."StatementEarn select AccountNo, sum( PointsAwarded ) as PointsAwarded, sum( FLOOR(TransValue) ) as StandardPoints from texaco.Transactions where CreationDate > '$LastStopdate' group by AccountNo";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementEarn using( AccountNo) set StandardAwarded = StandardPoints, BonusAwarded = PointsAwarded - StandardPoints";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodRedeem(  $StatementIdentifier, $LastStopdate ) 
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementRedeem";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementRedeem select AccountNo, sum( Cost ) as PointsRedeemed from texaco.Orders join texaco.OrderProducts using(OrderNo) where Orders.CreationDate > '$LastStopdate' and Status != 'C' group by AccountNo";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementRedeem using( AccountNo) set Redemptions = PointsRedeemed";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodAdjustments( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementAdjustment";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementAdjustment select AccountNo, sum(Stars) as Adjustments from texaco.Tracking where CreationDate > '$LastStopdate' and Stars != 0 group by AccountNo";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."StatementAdjustment  as A using( AccountNo) set S.AdjustMents = A.Adjustments";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );
}

function CorrectForNonmailed( $StatementIdentifier )
{
	$sql = "update ".$StatementIdentifier."StatementBalance set BroughtForward = Balance - StandardAwarded - BonusAwarded + Redemptions - AdjustMents where BroughtForward = 0";

echo "$sql\n";


 	$result = DBQueryExitOnFailure( $sql );

}

// select AccountNo, sum(cost) from OrderProducts join Orders using( OrderNo ) where RevisedDate > '2006-01-18 12:04:25' and CreationDate < '2006-01-18 12:04:25' and Status = 'C' group by AccountNo

function CorrectCancelledRedemptions( $StatementIdentifier, $datelaststop )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."CancelledRedemptions";
	DBQueryExitOnFailure( $sql );

	$sql = "create table ".$StatementIdentifier ."CancelledRedemptions select AccountNo, sum(cost) as Adjustments from texaco.OrderProducts join texaco.Orders using( OrderNo ) where RevisedDate > '$datelaststop' and CreationDate < '$datelaststop' and Status = 'C' group by AccountNo";
echo "$sql\n";

	$results = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."CancelledRedemptions  as C using( AccountNo) set S.AdjustMents = S.AdjustMents +  C.Adjustments";
echo "$sql\n";

	$results = DBQueryExitOnFailure( $sql );

}

function CreateSpendTotals( $StatementIdentifier )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."SpendTotals";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier ."SpendTotals select AccountNo, sum(TotalSpend) as TotalSpend, sum( TotalSwipes )  as TotalSwipes, min(FirstSwipeDate) as FirstSwipeDate, Max( LastSwipeDate) as LastSwipeDate from texaco.Cards join texaco.Members using( MemberNo ) group by ( AccountNo )";
echo "$sql\n";
	DBQueryExitOnFailure( $sql );

	$sql = "alter table ".$StatementIdentifier ."SpendTotals add Primary Key( AccountNo )";
	DBQueryExitOnFailure( $sql );
}

function extract_fputcsv($filePointer,$dataArray,$delimiter,$enclosure)
  {
  // Write a line to a file
  // $filePointer = the file resource to write to
  // $dataArray = the data to write out
  // $delimeter = the field separator
  
  // Build the string
  $string = "";
  
  // No leading delimiter
  $writeDelimiter = FALSE;
  foreach($dataArray as $dataElement)
   {
   // Replaces a double quote with two double quotes
   $dataElement=str_replace("\"", "\"\"", $dataElement);
   
   // Adds a delimiter before each field (except the first)
   if($writeDelimiter) $string .= $delimiter;
   
   // Encloses each field with $enclosure and adds it to the string
   $string .= $enclosure . $dataElement . $enclosure;
   
   // Delimiters are used every time except the first.
   $writeDelimiter = TRUE;
   } // end foreach($dataArray as $dataElement)
  
  // Append new line
  $string .= "\n";
  
  // Write the string to the file
  fwrite($filePointer,$string);
  }


function CreateOutputFile( $StatementIdentifier, $FileName, $Criteria )
{
	#$stuff = GenerateMonthList( false );

	$sql = "Select
	Listcode, PrimaryCard, CardType,
	Title,
	if( Title is null and Surname is null, 'Dear Star Rewards Member', CONCAT_WS( ' ', 'Dear', Title, Forename, Surname ) ) as Salutation, 
	Forename, Surname,
	Address1, Address2, Address3, Address4, Address5, Postcode,
	M.OKMail, M.StatementPref,
	L.SegmentCode,
	B.BroughtForward,
	B.StandardAwarded,
	B.BonusAwarded + B.Adjustments,
	B.Redemptions,
	B.Balance
	from 
		".$StatementIdentifier."StatementListcodes as L 
		join  ".$StatementIdentifier."StatementBalance as B using( AccountNo ) 
		join texaco.Members as M using( AccountNo )
		left join ".$StatementIdentifier."AccountTotals using( AccountNo ) 
		where PrimaryMember = 'Y' and $Criteria group by PrimaryCard";


	$results = DBQueryExitOnFailure( $sql );
	
	$fp = fopen($FileName, 'w');

   	$fields = mysql_num_fields( $results );
	$c = "";
	for( $k = 0; $k < $fields; $k++)
	{
		fwrite( $fp, $c. mysql_field_name( $results, $k ) );
		$c = ",";
	}
	fwrite( $fp, "\r\n" );

	while($row = mysql_fetch_row($results))
	{
		extract_fputcsv( $fp, $row,',', "\"" );
	}
	fclose( $fp );
}

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "--------------- Statement Extract for $StatementIdentifier Statement ---------------\r\n";
echo "Process Completed $timedate\r\n";



?>
