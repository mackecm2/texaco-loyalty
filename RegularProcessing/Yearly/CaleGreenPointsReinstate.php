<?php 

	require "../../include/DB.inc";
	require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");

$Points = 0;
$count = 0;
$nonremove = '';
$NonRemoveablePoints = '';
$StartProcessing = '';

$db_user = "pma001";
$db_pass = "amping";

echo "CaleGreen Bonus Points Reinstatement - Unregistered cards\n\r";
echo "Process Started - $timedate\n\r";

#$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );


function GetAccountNo( $MemberNo )
{
	$sql = "Select AccountNo from Members where MemberNo = $MemberNo ";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];
}

function GetCardMemberNo( $CardNo )
{
	$sql = "Select MemberNo from Cards where CardNo = '$CardNo'";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	if( $row and $row[0] != 0 and $row[0] != "" )
	{
		return $row[0];
	}
	else
	{
		return false;
	}
}

/*

$sql = "SELECT * 
FROM `Tracking` 
WHERE CreationDate > '2007-09-28 09:00:00'
AND Notes = 'Bonus Adjustment due to CaleGreen Bonus error'";

$Res = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "Number of Members - ". mysql_num_rows($Res). "\n\r";


while( $row = mysql_fetch_assoc( $Res ) )
{
	

	#	First add the points to the reinstated points total
	$Stars = 0 - $row['Stars'];
	
	$Points += $Stars;
	$count ++;

	#	Now wipe the Balance from the Account

	$sql = "Update Accounts set Balance = Balance +$Stars where AccountNo = $row[AccountNo] limit 1;";

	#echo "SQL - $sql\n\r";

	mysql_query( $sql, $master )  or die( mysql_error($master) );


	#	Now we need to create a Tracking record

	$Stars = 


	#$sql = "INSERT INTO `Tracking` 
	#			( `AccountNo` ,`Notes` , `Stars` , `CreatedBy` , `CreationDate` ) 
	#			VALUES 
	#			('$row[AccountNo]','Cale Green Bonus Adjustment reversal', '$Stars', 'Steve',now())";

	$sql = "delete from `Tracking` where `AccountNo` = '$row[AccountNo]' and Notes = 'Bonus Adjustment due to CaleGreen Bonus error' and CreationDate > '2007-09-28 00:00:00' limit 1;";
	
	
	#echo "SQL - $sql\n\r";

	mysql_query( $sql, $master )  or die( mysql_error($master) );
	

	
	

	unset($row);
	
}

*/
  
//	Now lets do the NULL Accounts  
  
$count = 0;  
  
$sql = "select calegreen.CardNo,sum(Points) as total
from calegreen where AccountNo is NULL group by calegreen.CardNo";

$Res = mysql_query( $sql, $master ) or die( mysql_error($master) );

echo "Number of Unregistered Members - ". mysql_num_rows($Res). "\n\r";


while( $row = mysql_fetch_assoc( $Res ) )
{

	#  First we need to find out if this card is now registered.
	$count++;
	
	if($row['CardNo'] == '7076550200800927256')
	{
		$StartProcessing = 'Y';
	}
	$Points += $row['total'];
	
	if($StartProcessing == 'Y')
	{
	
		$MemberNo = GetCardMemberNo($row['CardNo']);
		if($MemberNo <>'')
		{
			$AccountNo = GetAccountNo( $MemberNo );
		}
		
		if($AccountNo <> '')
		{

			#echo "We have member $MemberNo\r\n";
			#echo "We have AccountNo $AccountNo\r\n";
			
			$sql = "Update Accounts set Balance = Balance +$row[total] where AccountNo = $AccountNo limit 1;";

			#echo "$sql\n\r";

			mysql_query( $sql, $master )  or die( mysql_error($master) );


			#       Now we need to create a Tracking record


			$sql = "INSERT INTO `Tracking`
						( `AccountNo` ,`Notes` , `Stars` , `CreatedBy` , `CreationDate` )
						VALUES
						('$AccountNo','Cale Green Bonus Adjustment reversal', '$row[total]', 'Steve',now())";

			#$sql = "delete from `Tracking` where  AccountNo = '$AccountNo' and Notes = 'Cale Green Bonus Adjustment reversal' and CreationDate > '2007-10-01 00:00:00'";



			#echo "$sql\n\r";
			mysql_query( $sql, $master )  or die( mysql_error($master) );

		}
		else
		{

			#	Now wipe the Balance from the Card
			$sql = "Update Cards set StoppedPoints = StoppedPoints + $row[total] where CardNo = $row[CardNo] limit 1;";
			#echo "$sql\n\r";
			mysql_query( $sql, $master )  or die( mysql_error($master) );
		}

		unset($row);
	}
	
	unset($row);
	unset($MemberNo);
	unset($AccountNo);

 	if( $count % 500 == 0 )
	{
		$timedate = date("Y-m-d H:i:s");
		echo "$timedate Processed $count\n\r";


	}
                                             
}
  
  
 

  
echo "Total Points Recovered = $Points\n\r";
#echo "Total Points Not Recovered = $NonRemoveablePoints\n\r";
#echo "We could not recover:\n\r";
#echo "$nonremove";


$timedate = date("Y-m-d H:i:s");



echo "$timedate Process Completed\n\r";
?>
