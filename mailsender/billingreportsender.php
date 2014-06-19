<?php
include "../include/DB.inc";
include "../Reporting/GeneralReportFunctions.php";
$db_user = "pma001";
$db_pass = "amping";

$slave = connectToDB( ReportServer, ReportDB );

$timedate = date("Y-m-d")." ".date("H:i:s");
#$month = date("F y");
$filedate = date("Y-m-d");
$filepath 		=	"/data/www/websites/texaco/reportfiles/"	    ;
$thismonth = date("m");
$thisyear = date("Y");

$month = GetLastMonth();

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "billingreportsender.php - started $timedate\r\n";
	/*
	   here we go - flag up the phpmailer class
	   we'll be using it later.
	*/

	require("class.phpmailer.php")												;

	/*
	   here comes the email - make sure you have the
	   phpmailer class installed in the include directory
	   in your php.ini file, or in the same directory as
	   this script.

	   you can get phpmailer help online at : -
	   http://phpmailer.sourceforge.net
	*/

	# here's all the stuff that we need whatever the record count.

	# create new phpmailer instance

	$mail = new phpmailer();

	# send via SMTP
	$mail->IsSendmail()	;
	$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers

	$mail->From     	= 	"texaco@retail-services.co.uk"	; // the email address shown
	$mail->Sender     	= 	"texaco@retail-services.co.uk"	; // the envelope sender(server) of the email
	$mail->FromName 	= 	"Texaco Server"			; // the name shown

	$mail->AddAddress("mmackechnie@rsm2000.co.uk","Mike MacKechnie"); // mail recipients
	$mail->AddAddress("pward@rsm2000.co.uk","Peter Ward"); 	// mail recipients
	$mail->AddAddress("mfreeland@rsm2000.co.uk","Mark Freeland"); 	// mail recipients
	$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour"); 	// mail recipients
	
	$mail->AddReplyTo("pseymour@rsm2000.co.uk","Peter Seymour")	; // the reply to mail address and name

	$mail->WordWrap = 50																	; // set word wrap
	$mail->AddAttachment($filepath."TxCounts".$thisyear.$thismonth.".csv", "TxCounts.csv")	; // add attachment file names and descriptions
	$mail->AddAttachment($filepath."TxDailyDetails".$thisyear.$thismonth.".csv", "TxDailyDetails.csv")	;
	
	$mail->IsHTML(true)	; // set mail as html

	$mail->Subject  =  "$month Star Rewards Totals Report" ; // set mail subject

	$mail->Body     =   "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
						   This is a system generated email from the Texaco Server containing the Transaction Quantities for the last month.
						 <br>
  						  The files are attached.
  						 </font></p>"; // put your html mail body here
	/*
	  report back on whether the mail send was successful or not....
	*/

	if(!$mail->Send())
		{
			$dbMailSent = "Mail Send - fail"								;
		}

	else

		{
			$dbMailSent = "Mail Send - pass"								;
		}

	echo "$dbMailSent\r\n"												;
?>