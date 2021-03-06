<?php

include "../../include/DB2.inc";
include "../inc-mysql.php";

#$db_user = "pma001";
#$db_pass = "amping";
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
#$PreviousStatementIdentifier = "Feb08";
#$PreviousStatemntStopTime = "2008-02-05 12:58:38";
#$PreviousStatementIdentifier = "Aug08";
#$PreviousStatemntStopTime = "2008-08-21 12:26:04";
#$PreviousStatementIdentifier = "Nov08";
#$PreviousStatemntStopTime = "2008-11-05 10:35:40";
#$PreviousStatementIdentifier = "Mar09";
#$PreviousStatemntStopTime = "2009-03-05 11:03:20";
#$PreviousStatementIdentifier = "May09";
#$PreviousStatemntStopTime = "2009-05-14 14:51:15";
#$PreviousStatementIdentifier = "Aug09";
#$PreviousStatemntStopTime = "2009-08-04 15:36:18";
#$PreviousStatementIdentifier = "Nov09";
#$PreviousStatemntStopTime = "2009-11-10 14:36:22";
#$PreviousStatementIdentifier = "Mar10";
#$PreviousStatemntStopTime = "2010-03-23 15:05:11";
$PreviousStatementIdentifier = "Jun10";
$PreviousStatemntStopTime = "2010-06-28 10:23:05";

$ThisStatementStopTime = "2010-09-20 10:04:10";

connectToDB( ReplicationServer, AnalysisDB );

if( $SERVER_NAME_FOR_ALL <> "TEST" )
{
	CheckReplicationStopped();		
}

$StatementIdentifier = GetStatmentIdentifier();

#$StatementIdentifier .= "B";

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "--------------- Statement Extract for $StatementIdentifier Statement ---------------\r\n";
echo "Process Started $timedate\r\n";
echo "Creating Tables for $StatementIdentifier\r\n";

$ListcodeTable = $StatementIdentifier."StatementListcodes";					  

$sql = "Drop Table if exists  $ListcodeTable";

DBQueryExitOnFailure( $sql );

$ListcodeQuery = "create Table $ListcodeTable 
select M.AccountNo, M.MemberNo,M.PrimaryCard, Cards.CardType,
case 
when (A.SegmentCode like 'A%' or A.SegmentCode like 'N%'  or A.SegmentCode like 'L%' or A.SegmentCode like 'D%') and M.StatementPref = 'E' and Email like '%@%'  then 1
when (A.SegmentCode like 'A%' or A.SegmentCode like 'N%'  or A.SegmentCode like 'L%' or A.SegmentCode like 'D%') and M.StatementPref = 'N'  and Email like '%@%'   then 2
when (A.SegmentCode like 'A%' or A.SegmentCode like 'N%'  or A.SegmentCode like 'L%' or A.SegmentCode like 'D%') and M.StatementPref = 'E' and Email not like '%@%'  then 3
when A.SegmentCode like 'N%' and StatementPref = 'P' and M.GoneAway ='N'  then 4
when A.SegmentCode like 'N%'  and StatementPref = 'N' and Email not like '%@%'  and M.GoneAway ='N'  then 5
when (A.SegmentCode like 'A1L%'  or A.SegmentCode like 'A2L%'  )  and StatementPref = 'P'  and M.GoneAway ='N'  then 6
when (A.SegmentCode like 'A1L%'  or A.SegmentCode like 'A2L%'  )  and StatementPref = 'N'  and Email not like '%@%'  and M.GoneAway ='N'  then 7
when (A.SegmentCode like 'A1M%'  or A.SegmentCode like 'A2M%'  or A.SegmentCode like 'A1H%'  or A.SegmentCode like 'A2H%'  ) and StatementPref = 'P'  and M.GoneAway ='N'  then 8
when (A.SegmentCode like 'A1M%'  or A.SegmentCode like 'A1H%'  or  A.SegmentCode like 'A2M%'  or A.SegmentCode like 'A2H%'  ) and StatementPref = 'N'  and Email not like '%@%'  and M.GoneAway ='N'  then 9
end as ListCode, 
case 
when S.FraudStatus = '1' then 'Y'
else 'N'
end as ConfirmSpend, 
Balance,
A.SegmentCode,
StatementPref,
'N' as Statemented
from texaco.Accounts as A join texaco.Members as M using(AccountNo) join texaco.Cards on (Cards.CardNo = PrimaryCard) join texaco.AccountStatus as S using (AccountNo) 
where PrimaryMember = 'Y'  
and Cards.LostDate is null
and ((RedemptionStopDate is null and AwardStopDate is null) OR S.FraudStatus = '1')
and M.Deceased = 'N'  
and M.NonUKAddress = 'N'
and substring( A.SegmentCode, 1 , 1) in ('A', 'N', 'L','D' ) 
and Balance > 0
and (AccountType <> 'D'  OR AccountType is NULL)
and S.Status <> 'Closed' ";

$result = DBQueryExitOnFailure( $ListcodeQuery );

//	Add an index.
$sql = "ALTER TABLE $ListcodeTable ADD INDEX ( `AccountNo` ) ";
$result = DBQueryExitOnFailure( $sql );

// We need to remove the blank ListCodes as it is possible due to the where clause including all A,N,L,D,  
// SegmentCodes that in the Case statement for ListCode you could have a Dormant individual with a bad or null Email address.
$sql = "delete from $ListcodeTable where ListCode = '' OR ListCode is NULL";
$result = DBQueryExitOnFailure( $sql );

// Update the Statemented field where the Account has received a 2010 statement.
$sql = "update $ListcodeTable join 2010StatementedAccounts set Statemented = 'Y' where $ListcodeTable.AccountNo = 2010StatementedAccounts.AccountNo";
$result = DBQueryExitOnFailure( $sql );

//	Next is to confirm the ConfirmSpend lines.
echo "Updating ConfirmSpend lines" .date("Y-m-d")." ".date("H:i:s")."\r\n";

mysqlSelect($data,"AccountNo","$ListcodeTable","ConfirmSpend = 'Y'","0");
foreach($data as $accountRow)
{
	if(mysqlSelect($accdata,"AccountNo","texaco.Tracking","$accountRow[AccountNo] and TrackingCode in ('1214','1197')","1") < 1)
	{
		//  No confirm spend letter to set ConfirmSpend back to N
		$sql = "update $ListcodeTable set ConfirmSpend = 'N' where AccountNo = $accountRow[AccountNo] limit 1 ";
		$result = DBQueryExitOnFailure( $sql );		
	
	}

	unset($accdata);
	unset($accountRow);

}

Extract1MonthTotals( $StatementIdentifier );
CreateSpendTotals( $StatementIdentifier );
CreateStatementBalance( $StatementIdentifier );
BringForward( $StatementIdentifier, $PreviousStatementIdentifier );
PeriodEarn( $StatementIdentifier, $PreviousStatemntStopTime );
PeriodRedeem( $StatementIdentifier, $PreviousStatemntStopTime );
PeriodAdjustments( $StatementIdentifier, $PreviousStatemntStopTime );
CorrectForNonmailed( $StatementIdentifier ); 
CorrectCancelledRedemptions( $StatementIdentifier, $PreviousStatemntStopTime );
CheckForErrors( $StatementIdentifier );


// Next prepare for the second run for clients not statemented in 2010
$PreviousStatemntStopTime = "2010-01-01 00:00:00";
$StatementIdentifier= "Sept10NonStatemented";

echo "\nCreating Tables for $StatementIdentifier\r\n";

$sql = "Drop Table if exists  Sept10NonStatementedStatementListcodes";
DBQueryExitOnFailure( $sql );

// Create the listcode table by taking all the Non Statemented Accounts from the current table
$sql = "create table Sept10NonStatementedStatementListcodes select * from $ListcodeTable  where Statemented = 'N'  ";
$result = DBQueryExitOnFailure( $sql );

//	Add an index.
$sql = "ALTER TABLE Sept10NonStatementedStatementListcodes ADD INDEX ( `AccountNo` ) ";
$result = DBQueryExitOnFailure( $sql );

// delete all the Non Statemented Accounts from the original tables
echo "Starting deletion from Statement Tables ".date("Y-m-d")." ".date("H:i:s")."\r\n";

mysqlSelect($nonstatdata,"AccountNo","$ListcodeTable","Statemented = 'N'","0");
foreach($nonstatdata as $accountRow)
{
	$sql = "delete from $ListcodeTable where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10AccountTotals where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10CancelledRedemptions where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10SpendTotals where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10StatementAdjustment where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10StatementBalance where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10StatementEarn where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "delete from Sep10StatementRedeem where AccountNo = '$accountRow[AccountNo]' limit 1";
	$result = DBQueryExitOnFailure( $sql );
	
	unset($accountRow);
}

// Run the statement calculations on the new tables for the Non Statemented accounts
Extract1MonthTotals( $StatementIdentifier );
CreateSpendTotals( $StatementIdentifier );
CreateStatementBalance( $StatementIdentifier );
BringForward( $StatementIdentifier, $PreviousStatementIdentifier );
PeriodEarn( $StatementIdentifier, $PreviousStatemntStopTime );
PeriodRedeem( $StatementIdentifier, $PreviousStatemntStopTime );
PeriodAdjustments( $StatementIdentifier, $PreviousStatemntStopTime );
CorrectForNonmailed( $StatementIdentifier ); 
CorrectCancelledRedemptions( $StatementIdentifier, $PreviousStatemntStopTime );
CheckForErrors( $StatementIdentifier );

// Now write back the Non Statemented Accounts into the original tables
echo "Starting write back ".date("Y-m-d")." ".date("H:i:s")."\r\n";
mysqlSelect($newnonstatdata,"AccountNo","Sept10NonStatementedStatementListcodes","Statemented = 'N'","0");
foreach($newnonstatdata as $accountRow)
{
	$sql = "insert into $ListcodeTable select * from Sept10NonStatementedStatementListcodes where AccountNo = '$accountRow[AccountNo]'> '0'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10AccountTotals select * from Sept10NonStatementedAccountTotals where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10CancelledRedemptions select * from Sept10NonStatementedCancelledRedemptions where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10SpendTotals select * from Sept10NonStatementedSpendTotals where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10StatementAdjustment select * from Sept10NonStatementedStatementAdjustment  where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10StatementBalance select * from Sept10NonStatementedStatementBalance where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10StatementEarn select * from Sept10NonStatementedStatementEarn where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	$sql = "insert into Sep10StatementRedeem select * from Sept10NonStatementedStatementRedeem where AccountNo = '$accountRow[AccountNo]'  limit 1";
	$result = DBQueryExitOnFailure( $sql );
	
	unset($accountRow);
}

echo "Completed write back ".date("Y-m-d")." ".date("H:i:s")."\r\n";

// Now create the full file


$StatementIdentifier= "Sep10";
CreateOutputFile( $StatementIdentifier, '/tmp/'.$StatementIdentifier.'StatementData.csv', '1' );

function  CheckForErrors( $StatementIdentifier )
{
	$sql = "select *, BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents - Balance as Error  from ".$StatementIdentifier."StatementBalance where Balance !=  BroughtForward + StandardAwarded + BonusAwarded - Redemptions + AdjustMents";

	echo "$sql\r\n";

 	$results = DBQueryExitOnFailure( $sql );

	echo "There were ". mysql_num_rows( $results ) . "Records that have not worked\r\n";

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

	echo "$sql\r\n";
	
	$result = DBQueryExitOnFailure( $sql );

	$sql = "alter table ".$StatementIdentifier."StatementBalance add primary key(AccountNo)";
	$result = DBQueryExitOnFailure( $sql );
}

function BringForward( $NewStatement, $OldStatement )
{
	$OldStatement = $OldStatement."StatementBalance";
	//$sql = "update ".$NewStatement."StatementBalance as NewStatement join ".$OldStatement."StatementBalance as OldStatement using( AccountNo) set NewStatement.BroughtForward = OldStatement.Balance";

	$sql = "update ".$NewStatement."StatementBalance as NewStatement join ".$OldStatement." as OldStatement using( AccountNo) set NewStatement.BroughtForward = OldStatement.Balance";
	echo "$sql\r\n";
	$result = DBQueryExitOnFailure( $sql );
}

function PeriodEarn( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementEarn";
	DBQueryExitOnFailure( $sql );
	
	$sql = "create Table ".$StatementIdentifier."StatementEarn select AccountNo, sum( PointsAwarded ) as PointsAwarded, sum( FLOOR(TransValue) ) as StandardPoints 
	from texaco.Transactions where CreationDate > '$LastStopdate' group by AccountNo";
 	echo "$sql\r\n";
 	$result = DBQueryExitOnFailure( $sql );

 	$sql = "alter table ".$StatementIdentifier ."StatementEarn add INDEX ( AccountNo )";
	DBQueryExitOnFailure( $sql );
	
	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementEarn using( AccountNo) set StandardAwarded = StandardPoints, BonusAwarded = PointsAwarded - StandardPoints";
	echo "$sql\r\n";
 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodRedeem(  $StatementIdentifier, $LastStopdate ) 
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementRedeem";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementRedeem select AccountNo, sum( Cost ) as PointsRedeemed from texaco.Orders join texaco.OrderProducts using(OrderNo) where Orders.CreationDate > '$LastStopdate' and Status != 'C' group by AccountNo";
	echo "$sql\r\n";
 	$result = DBQueryExitOnFailure( $sql );

 	$sql = "alter table ".$StatementIdentifier ."StatementRedeem add INDEX ( AccountNo )";
	DBQueryExitOnFailure( $sql );
	
	$sql = "update ".$StatementIdentifier."StatementBalance join ".$StatementIdentifier."StatementRedeem using( AccountNo) set Redemptions = PointsRedeemed";

echo "$sql\r\n";


 	$result = DBQueryExitOnFailure( $sql );
}

function PeriodAdjustments( $StatementIdentifier, $LastStopdate )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."StatementAdjustment";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier."StatementAdjustment select AccountNo, sum(Stars) as Adjustments from texaco.Tracking where CreationDate > '$LastStopdate' and Stars != 0 group by AccountNo";
	echo "$sql\r\n";
 	$result = DBQueryExitOnFailure( $sql );

 	$sql = "alter table ".$StatementIdentifier ."StatementAdjustment add INDEX ( AccountNo )";
	DBQueryExitOnFailure( $sql );
	
	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."StatementAdjustment  as A using( AccountNo) set S.AdjustMents = A.Adjustments";
	echo "$sql\r\n";
 	$result = DBQueryExitOnFailure( $sql );
}

function CorrectForNonmailed( $StatementIdentifier )
{
	$sql = "update ".$StatementIdentifier."StatementBalance set BroughtForward = Balance - StandardAwarded - BonusAwarded + Redemptions - AdjustMents where BroughtForward = 0";

echo "$sql\r\n";


 	$result = DBQueryExitOnFailure( $sql );

}

// select AccountNo, sum(cost) from OrderProducts join Orders using( OrderNo ) where RevisedDate > '2006-01-18 12:04:25' and CreationDate < '2006-01-18 12:04:25' and Status = 'C' group by AccountNo

function CorrectCancelledRedemptions( $StatementIdentifier, $datelaststop )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."CancelledRedemptions";
	DBQueryExitOnFailure( $sql );

	$sql = "create table ".$StatementIdentifier ."CancelledRedemptions select AccountNo, sum(cost) as Adjustments from texaco.OrderProducts join texaco.Orders using( OrderNo ) where RevisedDate > '$datelaststop' and CreationDate < '$datelaststop' and Status = 'C' group by AccountNo";
echo "$sql\r\n";

	$results = DBQueryExitOnFailure( $sql );

	$sql = "update ".$StatementIdentifier."StatementBalance as S join ".$StatementIdentifier."CancelledRedemptions  as C using( AccountNo) set S.AdjustMents = S.AdjustMents +  C.Adjustments";
echo "$sql\r\n";

	$results = DBQueryExitOnFailure( $sql );

}

function CreateSpendTotals( $StatementIdentifier )
{
	$sql = "Drop Table if exists  ".$StatementIdentifier ."SpendTotals";
	DBQueryExitOnFailure( $sql );

	$sql = "create Table ".$StatementIdentifier ."SpendTotals select AccountNo, sum(TotalSpend) as TotalSpend, sum( TotalSwipes )  as TotalSwipes, min(FirstSwipeDate) as FirstSwipeDate, Max( LastSwipeDate) as LastSwipeDate from texaco.Cards join texaco.Members using( MemberNo ) group by ( AccountNo )";
echo "$sql\r\n";
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
	when Surname = '' OR Surname is NULL OR Title = '' OR  Title is NULL then 'Dear Star Rewards Member'
	when Surname <> '' AND Title <> ''  then CONCAT_WS( ' ', 'Dear', Title, Surname )
	end as Salutation,
	Title,
	Forename, Initials,Surname,
	Address1, Address2, Address3, Address4, Address5, Postcode,
	Email, 
	S.Status as AccountStatus,
	L.SegmentCode,
	M.StatementPref,
	S.TotalSpend, S.TotalSwipes, 
	cast( Datediff( S.LastSwipeDate, S.FirstSwipeDate )/30 AS signed) as Relationship,
	S.FirstSwipeDate,
	M.CreationDate,
	M.CreatedBy = 'WEB' as WebRegistration,
	B.BroughtForward,
	B.StandardAwarded,
	B.BonusAwarded + B.Adjustments,
	B.Redemptions,
	B.Balance,
	Statemented,
	ConfirmSpend
	from 
	".$StatementIdentifier."StatementListcodes as L 
	join  ".$StatementIdentifier."StatementBalance as B using( AccountNo ) 
	join texaco.Members as M using( AccountNo )
	join texaco.AccountStatus as S using( AccountNo )
	left join ".$StatementIdentifier."SpendTotals as S using (AccountNo )
	left join ".$StatementIdentifier."AccountTotals using( AccountNo ) 
	where PrimaryMember = 'Y' and $Criteria";

	echo "$sql\r\n";

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

















