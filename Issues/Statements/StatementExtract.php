<?php
error_reporting( E_ALL );
//*--------------------------------------------------------------------------------------------------------------------------*
//* StatementExtract.php (New Prototype)                         															 *
//*                                                                                                                          *
//* Author: MRM          Date: 22 OCT 2010                       															 *
//*                                                           																 *
//* Creates the Statement Extract without needing to update the code. It is based on the old StatementExtractMMMYY.php       *                                                       *
//*                                                                                                                          *
//* Prior to running this, you MUST insert a record into the Analysis.StatementingData containing the data for this run, e.g.*
//*                                                                                                                          *
//*  StatementIdentifier  	StopTime  		    ListcodeQuery  						                    CreationDate         *
//*      Sep10 		    2010-09-07 15:00:00 	select M.AccountNo, M.MemberNo,M.PrimaryCard, Card... 	2010-09-20 12:16:20  *
//*                                                                                                                          *
//*--------------------------------------------------------------------------------------------------------------------------*
require '../../include/DB.inc';
//include "../inc-mysql.php";

function  CheckForErrors( $StatementIdentifier )
{
	$sql = "select *, BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents - Balance as Error  from ".$StatementIdentifier."StatementBalance where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents";
 	$results = DBQueryExitOnFailure( $sql );
	echo date("Y-m-d H:i:s")." There were ". mysql_num_rows( $results ) . " records where opening and closing balances don't tally\n";
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

	$result = DBQueryExitOnFailure( $sql );

	$sql = "alter table ".$StatementIdentifier."StatementBalance add primary key(AccountNo)";
	$result = DBQueryExitOnFailure( $sql );
}

function BringForward( $NewStatement, $OldStatement )
{
	$OldStatement = $OldStatement."StatementBalance";
	$sql = "update ".$NewStatement."StatementBalance as NewStatement join ".$OldStatement." as OldStatement using( AccountNo) set NewStatement.BroughtForward = OldStatement.Balance";
	$result = DBQueryExitOnFailure( $sql );
}

function PeriodEarn( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementEarn";
	DBQueryExitOnFailure( $sql );
	
	$sql = "create Table ".$StatementIdentifier."StatementEarn select AccountNo, sum( PointsAwarded ) as PointsAwarded, sum( FLOOR(TransValue) ) as StandardPoints from texaco.Transactions where CreationDate > '$LastStopdate' group by AccountNo";
 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementEarn using( AccountNo) set StandardAwarded = StandardPoints, BonusAwarded = PointsAwarded - StandardPoints";
 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodRedeem(  $StatementIdentifier, $LastStopdate ) 
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementRedeem";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementRedeem select AccountNo, sum( Cost ) as PointsRedeemed from texaco.Orders join texaco.OrderProducts using(OrderNo) where Orders.CreationDate > '$LastStopdate' and Status != 'C' group by AccountNo";
 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementRedeem using( AccountNo) set Redemptions = PointsRedeemed";
 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodAdjustments( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementAdjustment";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementAdjustment select AccountNo, sum(Stars) as Adjustments from texaco.Tracking where CreationDate > '$LastStopdate' and Stars != 0 group by AccountNo";
 	$result = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."StatementAdjustment  as A using( AccountNo) set S.AdjustMents = A.Adjustments";
 	$result = DBQueryExitOnFailure( $sql );
}

function CorrectForNonmailed( $StatementIdentifier )
{
	$sql = "update ".$StatementIdentifier."StatementBalance set BroughtForward = Balance - StandardAwarded - BonusAwarded + Redemptions - AdjustMents where BroughtForward = 0";
 	$result = DBQueryExitOnFailure( $sql );
}

function CorrectCancelledRedemptions( $StatementIdentifier, $datelaststop )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."CancelledRedemptions";
	DBQueryExitOnFailure( $sql );

	$sql = "create table ".$StatementIdentifier ."CancelledRedemptions select AccountNo, sum(cost) as Adjustments from texaco.OrderProducts join texaco.Orders using( OrderNo ) where RevisedDate > '$datelaststop' and CreationDate < '$datelaststop' and Status = 'C' group by AccountNo";
	$results = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."CancelledRedemptions  as C using( AccountNo) set S.AdjustMents = S.AdjustMents +  C.Adjustments";
	$results = DBQueryExitOnFailure( $sql );

}

function CreateSpendTotals( $StatementIdentifier )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."SpendTotals";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier ."SpendTotals select AccountNo, sum(TotalSpend) as TotalSpend, sum( TotalSwipes )  as TotalSwipes, min(FirstSwipeDate) as FirstSwipeDate, Max( LastSwipeDate) as LastSwipeDate from texaco.Cards join texaco.Members using( MemberNo ) group by ( AccountNo )";
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
	$stuff = GenerateMonthList( false );
	$sql = "update ".$StatementIdentifier."StatementBalance set BroughtForward = (Balance - StandardAwarded - BonusAwarded  + Redemptions - AdjustMents) where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents";
	$result2 = DBQueryExitOnFailure( $sql );

	$sql = "Select
	Listcode, AccountNo, PrimaryCard, CardType,
	case 
	when Surname = '' OR Surname is null OR Title = '' OR  Title is NULL then 'Dear Star Rewards Member'
	when Surname <> '' AND Title <> ''  then CONCAT_WS( ' ', 'Dear', Title, Surname )
	end as Salutation,
	Title,
	Forename, Initials,Surname,
	Address1, Address2, Address3, Address4, Address5, Postcode,
	Email, 
	A.Status,
	L.SegmentCode,
	M.StatementPref,
	S.TotalSpend, S.TotalSwipes, 
	cast( Datediff( S.LastSwipeDate, S.FirstSwipeDate )/30 AS signed) as Relationship,
	S.FirstSwipeDate,
	M.CreationDate,
	M.CreatedBy as WebRegistration,
	B.BroughtForward,
	B.StandardAwarded,
	B.BonusAwarded + B.Adjustments,
	B.Redemptions,
	B.Balance,	
	A.FraudStatus
	from 
		".$StatementIdentifier."StatementListcodes as L 
		join  ".$StatementIdentifier."StatementBalance as B using( AccountNo ) 
		join texaco.Members as M using( AccountNo )
		join texaco.AccountStatus as A using( AccountNo )
		left join ".$StatementIdentifier."SpendTotals as S using (AccountNo )
		left join ".$StatementIdentifier."AccountTotals using( AccountNo ) 
		where PrimaryMember = 'Y' and $Criteria";

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

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$db_name = "Analysis";
$db_user = "root";
$db_pass = "Trave1";
$master = connectToDB( ReplicationServer, AnalysisDB );

$sql = "SELECT * FROM StatementingData ORDER BY StopTime DESC LIMIT 2";
$results = DBQueryExitOnFailure( $sql );
$count = 0;
while($row = mysql_fetch_assoc($results))
{
	$count++;
	if ($count == 1)          // The record for this statment extract
	{
		$ThisStatementIdentifier = $row[StatementIdentifier];
		$ThisStatemntStopTime = $row[StopTime];
		$ThisListcodeQuery = $row[ListcodeQuery];  
	}
	else                     // the previoust statement extract   
	{
		$PreviousStatementIdentifier = $row[StatementIdentifier];
		$PreviousStatemntStopTime = $row[StopTime];
	}
}

if( $SERVER_NAME_FOR_ALL <> "TEST" && $SERVER_NAME_FOR_ALL <> "DEMO" )  
{
	CheckReplicationStopped();		
}

$StatementIdentifier = GetStatmentIdentifier();
if ($StatementIdentifier!= $ThisStatementIdentifier)
{
	echo date("Y-m-d H:i:s")." #### ERROR #### Mismatch with database. Last entry found is ";
	echo "$ThisStatementIdentifier instead of $StatementIdentifier\n";
	exit;
}

echo date("Y-m-d H:i:s")." --------------- Statement Extract for $StatementIdentifier Statement ---------------\r\n";
echo date("Y-m-d H:i:s")." Creating Tables for $StatementIdentifier\n";

$ListcodeTable = $StatementIdentifier."StatementListcodes";					  

$sql = "Drop Table if exists  $ListcodeTable";
DBQueryExitOnFailure( $sql );

$ListcodeQuery = "create Table ".$ListcodeTable." ".$ThisListcodeQuery; 
$result = DBQueryExitOnFailure( $ListcodeQuery );

//	Add an index.
$sql = "ALTER TABLE $ListcodeTable ADD INDEX ( `AccountNo` ) ";
$result = DBQueryExitOnFailure( $sql );

// We need to remove the blank ListCodes as it is possible due to the where clause including all A,N,L,D,  
// SegmentCodes that in the Case statement for ListCode you could have a Dormant individual with a bad or null Email address.

$sql = "delete from $ListcodeTable where ListCode = '' OR ListCode is NULL";
$result = DBQueryExitOnFailure( $sql );

echo date("Y-m-d H:i:s")." $ListcodeTable Table created - now extracting Month Totals...\n";
Extract1MonthTotals( $StatementIdentifier );
echo date("Y-m-d H:i:s")." Extracted Month Totals to $StatementIdentifier"."AccountTotals - now creating Spend Totals...\n";
CreateSpendTotals( $StatementIdentifier );
echo date("Y-m-d H:i:s")." Created Spend Totals (".$StatementIdentifier."SpendTotals) - now creating Statement Balances...\n";
CreateStatementBalance( $StatementIdentifier );
echo date("Y-m-d H:i:s")." Created Statement Balances (".$StatementIdentifier."StatementBalance) - now creating Bring Forward values...\n";
BringForward( $StatementIdentifier, $PreviousStatementIdentifier );
echo date("Y-m-d H:i:s")." Created Bring Forward values (set NewStatement.BroughtForward = OldStatement.Balance) - now creating Period Earn values...\n";
PeriodEarn( $StatementIdentifier, $PreviousStatemntStopTime );
echo date("Y-m-d H:i:s")." Created Period Earn values (".$StatementIdentifier."StatementEarn) - now creating Period Redeems...\n";
PeriodRedeem( $StatementIdentifier, $PreviousStatemntStopTime );
echo date("Y-m-d H:i:s")." Created Period Redeems (".$StatementIdentifier."StatementRedeem) - now creating Period Adjustments...\n";
PeriodAdjustments( $StatementIdentifier, $PreviousStatemntStopTime );
echo date("Y-m-d H:i:s")." Created Period Adjustments (".$StatementIdentifier."StatementAdjustment) - now creating Corrections for Non Mailed...\n";
CorrectForNonmailed( $StatementIdentifier ); 
echo date("Y-m-d H:i:s")." Set BroughtForward = Balance - StandardAwarded - BonusAwarded + Redemptions - AdjustMents where BroughtForward = 0...\n";
echo date("Y-m-d H:i:s")." Created Corrections for Non Mailed - now creating Corrections for Cancelled Redemptions...\n";
CorrectCancelledRedemptions( $StatementIdentifier, $PreviousStatemntStopTime );
echo date("Y-m-d H:i:s")." Created Corrections for Cancelled Redemptions (".$StatementIdentifier."CancelledRedemptions) - now checking for errors...\n";
CheckForErrors( $StatementIdentifier );
echo date("Y-m-d H:i:s")." Checked for errors - now creating output csv file...\n";

CreateOutputFile( $StatementIdentifier, '/tmp/'.$StatementIdentifier.'StatementData.csv', '1' );
echo date("Y-m-d H:i:s")." output csv file /tmp/$StatementIdentifier.StatementData.csv created\n";
echo date("Y-m-d H:i:s")." --------------- Statement Extract for $StatementIdentifier Statement ---------------\r\n";
echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>