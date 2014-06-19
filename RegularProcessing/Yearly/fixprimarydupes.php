<?php 

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													

$timedate = date("Y-m-d H:i:s");

$count = 0;
$delaccounts = 0;
$delmembers = 0;
$db_user = "pma001";
$db_pass = "amping";

echo "Duplicate Primary Card Fix\n\r";
echo "Process Started - $timedate\n\r";

	$slave = connectToDB( ReportServer, TexacoDB );
	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "select PrimaryCard from Analysis.dupprimaries where 1 ";

	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

	#echo "Number of Cards - ". mysql_num_rows($slaveRes). "\n\r";

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$count++;
				
		$sql = "select MemberNo,AccountNo from Members where PrimaryCard = '$row[PrimaryCard]'";
			
		echo "---------------------------------------------------------------------------\r\n";
		echo "PrimaryCard - $row[PrimaryCard]\n\r";

		$Members = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		while( $MemberRow = mysql_fetch_assoc( $Members ) )
		{
		

			$sql = "select Balance from Accounts where AccountNo = '$MemberRow[AccountNo]'";
			#echo "SQL - $sql\n\r";
			
			$AccountDetails = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
			while( $AccountRow = mysql_fetch_assoc( $AccountDetails ) )
			{
			
				echo "Account $MemberRow[AccountNo] - balance $AccountRow[Balance]\r\n";
			
				if(($AccountRow['Balance'] == 0) OR ($AccountRow['Balance'] == 50))
				{


					#	We need to delete this Member and Account

					$sql = "delete from Members where MemberNo = '$MemberRow[MemberNo]' limit 1";
					#echo "SQL - $sql\n\r";
					echo "Deleted MemberNo - $MemberRow[MemberNo]\n\r";

					mysql_query( $sql, $master ) or die( mysql_error($master) );

					$sql = "delete from Accounts where AccountNo = '$MemberRow[AccountNo]' limit 1";
					#echo "SQL - $sql\n\r";
					echo "Deleted AccountNo - $MemberRow[AccountNo]\n\r";

					mysql_query( $sql, $master ) or die( mysql_error($master) );

				}
				else
				{

					# Write a tracking note against this Member

					$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy ) 
					values ( $MemberRow[MemberNo], $MemberRow[AccountNo],'6', 'This Member had duplicate hidden accounts removed 05/10/06', now(), 'Steve')";

					#echo "$sql\n";
					echo "Inserted Tracking Note\r\n";
					mysql_query( $sql, $master )  or die( mysql_error($master) );



				}
				
				unset($AccountRow);			
			
			
			}
			
			
			
			
			
		}
		
		
		unset($MemberRow);
		unset($AccountRow);
		
		
	}
  

$timedate = date("Y-m-d H:i:s");
echo "$timedate Process Completed\n\r";
?>