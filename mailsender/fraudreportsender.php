<?php
include "../include/DB.inc";
include "../Reporting/GeneralReportFunctions.php";
$db_user = "pma001";
$db_pass = "amping";

$slave = connectToDB( ReportServer, ReportDB );

$timedate = date("Y-m-d")." ".date("H:i:s");
#$month = date("F y");
$filename = "FraudReport".date("Y_m_d").".csv";
$file2 = "BunkerCardMatches".date("Y_m_d").".csv";
$filepath = "/data/www/websites/texaco/FileProcessing/Processed/FraudReports/"	    ;

$month = GetLastMonth();

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "fraudreportsender.php - started $timedate\r\n";

	/*
	   here we go - flag up the phpmailer class
	   we'll be using it later.
	*/

	require("class.phpmailer.php")												;

    /* let's get some variables sorted. */

	# start with date variables -




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

	$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour"); 	// mail recipients
	$mail->AddAddress("emmas@vccp.com","Emma Sweatman"); 	// mail recipients
	$mail->AddAddress("andrewh@sfwlondon.com","Andrew Hardeman"); 	// mail recipients
	$mail->AddAddress("mmackechnie@rsm2000.co.uk","Mike MacKechnie"); 	// mail recipients
	
	$mail->AddReplyTo("pseymour@rsm2000.co.uk","Peter Seymour")	; // the reply to mail address and name

	$mail->WordWrap = 50																	; // set word wrap
	$mail->AddAttachment($filepath.$filename, $filename)	; // add attachment file names and descriptions
	$mail->AddAttachment($filepath.$file2, $file2)	; // add attachment file names and descriptions



	$mail->IsHTML(true)	; // set mail as html

	$mail->Subject  =  "$month Fraud Report" ; // set mail subject

	$mail->Body     =   "<p>
			 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
			   This is a system generated email from the Texaco Server containing the Bunker Card matching transactions for last month.
			 <br>
  			  The file is attached.
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



$timedate = date("Y-m-d")." ".date("H:i:s");
echo "fraudreportsender.php - completed $timedate\r\n";


?>
