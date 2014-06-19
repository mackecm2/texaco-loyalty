<?php

require "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";													



$db_user = "pma001";
$db_pass = "amping";
$count = 0;


#$slave = connectToDB( ReportServer, TexacoDB );
$master = connectToDB( MasterServer, TexacoDB );

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";

//	Start of Script.

print "Recard Import File Results\n\r";
echo "Process Started - $timedate\n\r";


$handle = fopen ("/tmp/RecardData2.csv","r");

// prepare the output file
unlink("/data/www/websites/texaco/RegularProcessing/Yearly/RecardMailingFile.csv");

$fp = fopen("/data/www/websites/texaco/RegularProcessing/Yearly/RecardMailingFile.csv", 'w');
fwrite( $fp, "MemberNo,AccountNo,NewCardNo,Salutation,Forename, Surname,Address1, Address2, Address3, Address4, Address5, Postcode\r\n" );


$lineno = 0;
$primaryerrors = "";
$inserterrors = "";

//	look up member
//	create new card record
//	create tracking note
//	create mailing file export

while ($data = fgetcsv ($handle, 2000, ","))
{


	#	Only do this if this isnt a header row

	if($data[0] <> 'Title')
	{
		$lineno ++;
		
		#	Assemble an array of the data we have received
		$PrimaryCardNo			= $data[14];
		$NewCardNo			= $data[20];

		$sql = "select MemberNo from Cards where CardNo = '$PrimaryCardNo' limit 1";

		$Res = mysql_query( $sql, $master ) or die( mysql_error($master) );

		
		if($memberrow = mysql_fetch_assoc( $Res ) )
		{

			#echo "we have Member - $memberrow[MemberNo]\r\n";

			// now get the account number
			
			$sql = "select MemberNo,AccountNo, if( Title is null and Surname is null, 'Dear Star Rewards Member', CONCAT_WS( ' ', 'Dear', Title, Forename, Surname ) ) as Salutation, 
					Forename, Surname,Address1, Address2, Address3, Address4, Address5, Postcode from Members where MemberNo = '$memberrow[MemberNo]' limit 1";

			$MemberRes = mysql_query( $sql, $master ) or die( mysql_error($master) );
			if($row = mysql_fetch_assoc( $MemberRes ) )
			{
			
				#echo "we now have Member - $row[MemberNo]\r\n";

				// Does the new card exist ?
/*
				$sql = "select CardNo from Cards where CardNo = '$NewCardNo' limit 1";
				#echo "$sql\r\n";
				$CardRes = mysql_query( $sql, $master ) or die( mysql_error($master) );
				if($cardrow = mysql_fetch_assoc( $CardRes ) )
				{

					$inserterrors .= "Card already exists,$NewCardNo\r\n";

				}
				else
				{
*/
					#echo "MemberNo is $row[MemberNo]\r\n";

					#$sql = "Insert into Cards( CardNo,MemberNo, CardType, IssueDate, CreationDate, CreatedBy ) 
					#values ( $NewCardNo,$row[MemberNo], 'StarRewards',now(),now(), 'Aug08Recarding')";

					#echo "Card insert sql is $sql\r\n";

					#$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

					# Write a tracking note against this Member

					#$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy ) 
					#values ( $row[MemberNo], $row[AccountNo],'1118', 'New Card Added - Aug 2008 Recarding', now(), 'Aug08Recarding')";

					#echo "$sql\r\n";
					#echo "Inserted Tracking Note\r\n";
					#$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );

					fwrite( $fp, "$row[MemberNo],$row[AccountNo],$NewCardNo,\"$row[Salutation]\",\"$row[Forename]\",\"$row[Surname]\",\"$row[Address1]\",\"$row[Address2]\",\"$row[Address3]\",\"$row[Address4]\",\"$row[Address5]\",\"$row[Postcode]\"\r\n" );

				#}
				unset($row);
				unset($cardrow);
				
			}
			
			unset($MemberRes);
			
		}
		else
		{
		
			$primaryerrors .= "PrimaryCard not found,$PrimaryCardNo \r\n";
			
		
		}

		
		if( ($lineno % 10000) == 0 )
		{
			
			echo date("h:i:s");
			echo " - $lineno lines processed\n";
			#die();
		}		
		
		
		
	}


}  // end while ($data = fgetcsv ($handle, 2000, ","))

fclose( $fp );

$date = date("Y-m-d");
$time = date("H:i:s");
$timedate = "$date $time";
print "$timedate Import Complete\n\r";
print " \n\r";


echo "Summary\n\r";
echo "----------------------------------------------\n\r";
echo "$lineno lines processed\n\r";
echo "Primary Cards not found:\n\r";
echo "$primaryerrors\n\r";
echo " \n\r";
echo " \n\r";
echo "----------------------------------------------\n\r";
echo "Cards not inserted:\n\r";
echo "$inserterrors\n\r";




?>

