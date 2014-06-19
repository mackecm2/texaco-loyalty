<?php 
/*
 * ---------------------------------------------------
 * Monthly Liability Accrual process
 * ---------------------------------------------------
 * Author : MRM
 * Date   : 27 MAR 09
 * 
 * Based on RegularProcessing/Yearly/March2009LiabilityReduction.php
 * 
 * 22/06/09 - 
 * 
 * 
 * 
 */

require "../../include/DB.inc";
require "../includes/LiabilityFunctions.inc";
require "../../Reporting/GeneralReportFunctions.php";													
require "../../mailsender/class.phpmailer.php";

#------- M A I N   P R O C E S S -----------------------------

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
#$timedate = date("Y-m-d H:i:s");
$oneyearago = date("Y-m-d",strtotime("-1 years"));
$timestamp = date ("FY");
$taskname = $timestamp."Liability";
$filepath =	"/data/www/websites/texaco/reportfiles/";
$db_user = "pma001";
$db_pass = "amping";

echo date("Y-m-d H:i:s")." connecting to ReportServer \r\n";
$slave = connectToDB( ReportServer, AnalysisDB );

//------------------------------------------------------------------------------------------------------------------------------------
//------- Section 1 - Registered Cards -----------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------
$message  = "\n\r\n\r".date("F Y")." Liability Reduction\n\r\n\r";
$message .= "Registered XD Cards\n\r";
$message .= "-----------------------\n\r";
$message .= "Criteria For Liability Reduction:\n\r\n\r";
$message .= "Registered non-Group XD Accounts with LastSwipeDate older than ".$oneyearago." and LastOrderDate older than ".$oneyearago." \n\rand LastTrackingDate older than ".$oneyearago."\n\r";
$message .= "Process Started - ".date("Y-m-d H:i:s")."\n\r\n\r";
echo $message;
$days = 365; // 12 months
$months = ($days/365) * 12;
$sql = RegisteredLiability($timestamp, $days);
$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

$messagestr = "Number of Members - ". mysql_num_rows($slaveRes). "\n\r";
echo $messagestr;

$message .= $messagestr;

$Points = 0;
$count = 0;

$master = connectToDB( MasterServer, TexacoDB );

while( $row = mysql_fetch_assoc( $slaveRes ) )
{

	#	First wipe the Balance from the Account ... and set the Status to Closed (MRM Mantis 2130 30th MAY 10)

	$sql = "Update Accounts JOIN AccountStatus USING ( AccountNo ) 
	set Balance = '0', Status = 'Closed', StatusSetDate = NOW(), 
	RedemptionStopDate = IF(RedemptionStopDate IS NOT NULL, RedemptionStopDate, NOW()), AwardStopDate = IF(AwardStopDate IS NOT NULL, AwardStopDate, NOW())
	where AccountNo = $row[AccountNo]";
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	#	Now we need to create a Tracking record
	$trackingname = "Liability Management ".date("F Y");
	$taskname = $timestamp."Liability";
	
	$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, Stars, CreatedBy, CreationDate ) 
				VALUES ('$row[AccountNo]', 1166, '$trackingname. XD Member No Swipes $months Months+', '-$row[Balance]', '$taskname',now())";
	
				mysql_query( $sql, $master )  or die( mysql_error($master) );
				
	#	Then add the points to the removed points total
    $mysqlError=mysql_errno();
	if ( $row['Balance'] > 0 && $mysqlError == 0 )
	{
		$Points += $row['Balance'];
		$count ++;
	}			

	if( ($count % 50000) == 0 && $count != 0 )
	{
		echo date("H:i:s");
		echo " Processed $count\n\r";
	}	
}
  
$messagestr  = "Total Points Recovered = $Points\n\r\n\r";
$messagestr .= "------------------------------------------------------------------------------------------------------------------------------------\n\r\n\r";
echo $messagestr;
$message .= $messagestr;

//------------------------------------------------------------------------------------------------------------------------------------
//------- Section 1a - Registered Cards with Stopped Points --------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------
$messagestr  = "Registered XD Cards Stopped Points\n\r";
$messagestr .= "--------------------------------------\n\r";
$messagestr .= "Criteria For Liability Reduction:\n\r\n\r";
$messagestr .= "Stopped Points on Registered Cards in non-Group XD Accounts with LastSwipeDate older than ".$oneyearago." and LastOrderDate older than ".$oneyearago." \n\rand LastTrackingDate older than ".$oneyearago."\n\r";
$messagestr .= "Process Started - ".date("Y-m-d H:i:s")."\n\r\n\r";
echo $messagestr;
$message .= $messagestr;

$sql = RegisteredLiabilityStoppedPoints($timestamp, $days);
$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );

$messagestr = "Number of Members - ". mysql_num_rows($slaveRes). "\n\r";
echo $messagestr;

$message .= $messagestr;

$Points = 0;
$count = 0;

$master = connectToDB( MasterServer, TexacoDB );

while( $row = mysql_fetch_assoc( $slaveRes ) )
{
	
	#	First wipe the Stopped Points from the Card

	$sql = "Update Cards set StoppedPoints = '0' where CardNo = '$row[CardNo]' limit 1;";
	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	#	Now we need to create a Tracking record
	$trackingname = "Liability Management ".date("F Y");
	$taskname = $timestamp."Liability";
	
	$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, Stars, CreatedBy, CreationDate ) 
				VALUES ('$row[AccountNo]', 1166, '$trackingname. XD Member Stopped Points No Swipes $months Months+', '-$row[StoppedPoints]', '$taskname',now())";

	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	#	Now add the points to the removed points total
	
	$mysqlError=mysql_errno();
	if ( $row['StoppedPoints'] > 0 && $mysqlError == 0 )
	{
		$Points += $row['StoppedPoints'];
		$count ++;
	}
	
	if( ($count % 50000) == 0 && $count != 0 )
	{
		echo date("H:i:s");
		echo " Processed $count\n\r";
	}	
}
  
$messagestr  = "Total Stopped Points Recovered = $Points\n\r\n\r";
$messagestr .= "------------------------------------------------------------------------------------------------------------------------------------\n\r\n\r";
echo $messagestr;
$message .= $messagestr;


//------------------------------------------------------------------------------------------------------------------------------------
//------- Section 1b - Fraudulent Accounts (only runs once a year, in September) -----------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------

if (date("M") == "Oct")
{
	$messagestr  = "Registered Fraudulent Cards\n\r";
	$messagestr .= "--------------------------------------\n\r";
	$messagestr .= "Criteria For Liability Reduction:\n\r\n\r";
	$messagestr .= "Registered Cards in Accounts with Redemption Stop and Award Stop and FraudStatus is Fraud"."\n\r";
	$messagestr .= "Process Started - ".date("Y-m-d H:i:s")."\n\r\n\r";
	echo $messagestr;
	$message .= $messagestr;
	
	$sql = RegisteredFraudAccLiability();
	$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
	
	$messagestr = "Number of Accounts - ". mysql_num_rows($slaveRes). "\n\r";
	echo $messagestr;
	
	$message .= $messagestr;
	
	$Points = 0;
	$count = 0;
	
	$master = connectToDB( MasterServer, TexacoDB );
	
	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{
		$balance = $row['Balance'];
		$stoppedpoints = $row['StoppedPoints'];
		
		
		#	First wipe the Balance from the Account
		
		if( $balance != 0  )
		{
			$sql = "Update Accounts set Balance = '0' where AccountNo = $row[AccountNo] limit 1;";
			mysql_query( $sql, $master )  or die( mysql_error($master) );
			
			#	Now we need to create a Tracking record
			
			$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, Stars, CreatedBy, CreationDate ) 
						VALUES ('$row[AccountNo]', 1166, '$trackingname. Fraud Account', '-$balance', '$taskname',now())";
			
						mysql_query( $sql, $master )  or die( mysql_error($master) );
						
			#	Then add the points to the removed points total
		    $mysqlError=mysql_errno();
			if ( $balance > 0 && $mysqlError == 0 )
			{
				$Points += $balance;
			}			
		}
		
		#	There may be stopped points as well
		if( $stoppedpoints != 0  )
		{
			$sql = "UPDATE Cards JOIN Members USING ( MemberNo ) JOIN Accounts USING ( AccountNo )
			 SET StoppedPoints = '0' where AccountNo = $row[AccountNo]";
			mysql_query( $sql, $master )  or die( mysql_error($master) );
			
			#	Now we need to create a Tracking record
			
			$sql = "INSERT INTO Tracking ( AccountNo, TrackingCode, Notes, Stars, CreatedBy, CreationDate ) 
						VALUES ('$row[AccountNo]', 1166, '$trackingname. Fraud Stopped Points', '-$stoppedpoints', '$taskname',now())";
			
						mysql_query( $sql, $master )  or die( mysql_error($master) );
						
			#	Then add the points to the removed points total
		    $mysqlError=mysql_errno();
			if ( $stoppedpoints > 0 && $mysqlError == 0 )
			{
				$Points += $stoppedpoints;
			}			
		}
		$count ++;
		if( ($count % 50000) == 0 && $count != 0 )
		{
			echo date("H:i:s");
			echo " Processed $count\n\r";
		}	
	}
	  
	$messagestr  = "Total Points Recovered = $Points\n\r\n\r";
	$messagestr .= "------------------------------------------------------------------------------------------------------------------------------------\n\r\n\r";
	echo $messagestr;
	$message .= $messagestr;
}

//------------------------------------------------------------------------------------------------------------------------------------
//---------- Section 2 - Unregistered Cards ------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------
$Points = 0;
$count = 0;

$messagestr = "Unregistered XD Cards\n\r";
$messagestr .= "-------------------------\n\r";
$messagestr .= "Criteria For Liability Reduction:\n\r\n\r";
$messagestr .= "Stopped Points on cards with No MemberNo where LastSwipeDate is older than ".$oneyearago."\n\r";
$messagestr .= "Process Started - ".date("Y-m-d H:i:s")."\n\r\n\r";

echo $messagestr;
$message .= $messagestr;

$sql = UnregisteredLiability($oneyearago);
$masterRes = mysql_query( $sql, $master ) or die( mysql_error($master) );
$messagestr = "Number of Unregistered Cards - ". mysql_num_rows($masterRes). "\n\r";
echo $messagestr;
$message .= $messagestr;

while( $row = mysql_fetch_assoc( $masterRes ) )
{

	#	First wipe the points from the Card

	$sql = "Update Cards set StoppedPoints = '0' where CardNo = '$row[CardNo]' limit 1;";
	#echo "SQL - $sql\n\r";
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );

	#	Now write the Card Details etc away in the LiabilityReduction table

	$sql = "INSERT INTO `LiabilityReduction` 
		( `CardNo` , `Points` , `CreatedBy` , `CreationDate` ) 
		VALUES ($row[CardNo], $row[StoppedPoints], '$taskname', now())";
	
	mysql_query( $sql, $master )  or die( mysql_error($master) );
	
	#	Now add the points to the removed points total
	
	$mysqlError=mysql_errno();
	if ( $row['StoppedPoints'] > 0 && $mysqlError == 0 )
	{
		$Points += $row['StoppedPoints'];
		$count ++;
	}

	if( ($count % 50000) == 0 && $count != 0 )
	{
		echo date("H:i:s");
		echo " Processed $count\n\r";
	}
}

#	Now we need to create a Tracking record

$sql = "INSERT INTO Tracking 
			( Notes , TrackingCode, Stars , CreatedBy , CreationDate ) 
			VALUES 
			('Points removed from Unregistered Cards with LastSwipeDate < $startdate', 1166, '-$Points', '$taskname',now())";

mysql_query( $sql, $master )  or die( mysql_error($master) );

$messagestr  =  "Total Unregistered Points Recovered = $Points\n\r\n\r";
$messagestr .= "------------------------------------------------------------------------------------------------------------------------------------\n\r\n\r";
$messagestr .= "Process Completed - ".date("Y-m-d H:i:s")."\n\r\n\r";
echo $messagestr;
$message .= $messagestr;

//------------------------------------------------------------------------------------------------------------------------------------
//---------- Section 2.5 - Create the Spreadsheet ----------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------

echo date("H:i:s");
echo " Creating Spreadsheet\r\n";
$filename = $filepath."$taskname.csv";
$outputfile = fopen("$filename", "w");

$filetitlerow = "AccountNo, Stars, CreatedBy, CreationDate\n";
fwrite($outputfile, $filetitlerow);

$sql = "SELECT AccountNo, Stars, CreatedBy, CreationDate FROM Tracking WHERE CreatedBy = LEFT('$taskname', 20)";

$spreadsheetRes = mysql_query( $sql, $master )  or die( mysql_error($master) );

$numline = 0;
while( $row = mysql_fetch_assoc( $spreadsheetRes ) )
{
	$numline++;
	$filerow = "$row[AccountNo],$row[Stars],$row[CreatedBy],$row[CreationDate]\n";
	fwrite($outputfile, $filerow);
	if( $numline % 1000 == 0 )
	{
		echo date("H:i:s")." ".$numline." lines written\r\n";
	}
}
echo date("H:i:s")." ".$numline." lines written\r\n";
fclose($outputfile);

//------------------------------------------------------------------------------------------------------------------------------------
//---------- Section 3 - Send the Email ----------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------
if( $message )
{
		$mail = new phpmailer();
			# send via SMTP
		$mail->IsSendmail()	;
		$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
		$mail->FromName	= ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM Admin
		$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
		$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
		$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
		$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie'); // mail recipient address and name, repeat for each recipent
		$mail->AddAddress('Bronagh.Carron@valero.com', 'Bronagh M Carron'); 
		$mail->AddAddress('Mandy.Hodson@valero.com', 'Mandy Hodson'); 
		$mail->AddAddress('andrewh@vccp.com', 'Andrew Hardeman');
		$mail->AddAddress('emmas@vccp.com', 'Emma Sweatman');
		$mail->AddAddress('jonathanb@vccp.com', 'John Boardman');
		$mail->AddCC('John.Mozie@valero.com', 'John Mozie'); 
		$mail->AddCC('Tola.Akintola@valero.com', 'Tola Akintola'); 
		$mail->AddAttachment($filename, $filename)	;
	    $mail->Subject = date("F Y")." Liability Reduction\n\r";
		$mail->WordWrap = 70; // set word wrap
		
		$mail->IsHTML(true); // set mail as html
		$htmlmessage = str_replace("\n\r", "<p>", $message);
		// HTML Message Body
		$mail->Body =
		'<font size=2><font face=Verdana><p>' 
		.$htmlmessage
		.'<p></p>Texaco Loyalty Card System<BR><BR></font></font>'."\n"
		.'</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n"
		.'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n"
		.'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n"
		.'error please notify the sender by email and delete this message and any '."\n"
		.'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n"
		.'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n"
		.'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n"
		.'Registration Number: 3703548</EM></font></font></p>';

		// Text Message Body
		$mail->AltBody = date("F Y")." Liability Reduction\n\n"
			.$message;

		// send the email and check on its success
		if (!$mail->Send())
		{
			$dbMailSent = " Liability Accrual Mail Send - fail\n\n";
		} 
		else
		{
			$dbMailSent = " Liability Accrual Mail Send - pass\n\n";
		}
			echo date("H:i:s");
			echo $dbMailSent;
}

echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>