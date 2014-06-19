<?php


include "../include/DB.inc";
include "../Reporting/GeneralReportFunctions.php";

$slave = connectToDB( ReportServer, ReportDB );

$timedate = date("Y-m-d")." ".date("H:i:s");
$filedate = date("Y-m-d");
$filepath 		=	"/data/www/websites/texaco/reportfiles/"	    ;

$month = GetLastMonth();

$timedate = date("Y-m-d")." ".date("H:i:s");
echo "reportsender.php - started $timedate\r\n";
echo "Emailing month -  $month\r\n";
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
	$mail->FromName 	= 	ServerEnvironment." Texaco Server"				; // the name shown

	$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour"); 	// mail recipients
	$mail->AddAddress("mmackechnie@rsm2000.co.uk","Mike MacKechnie"); 	// mail recipients
	$mail->AddAddress("Mandy.Hodson@valero.com","Mandy Hodson"); 	// mail recipients
	$mail->AddAddress("Bronagh.Carron@valero.com","Bronagh Carron"); 	// mail recipients
	$mail->AddAddress("Tony.Webb@valero.com","Tony Webb"); 	// mail recipients
	
	$mail->AddReplyTo("pseymour@rsm2000.co.uk","Peter Seymour")	; // the reply to mail address and name

	$mail->WordWrap = 50																	; // set word wrap
	$mail->AddAttachment($filepath."TopValueTransactions".$month.".csv", "TopValueTransactions".$month.".csv")	; // add attachment file names and descriptions
	$mail->AddAttachment($filepath."StoppedAccounts.zip", "StoppedAccounts.zip")	; // add attachment file names and descriptions
	$mail->AddAttachment($filepath."HighValueTransactions.csv", "HighValueTransactions.csv")	; // add attachment file names and descriptions






	$mail->IsHTML(true)	; // set mail as html

	$mail->Subject  =  "Star Rewards Extracts for $month" ; // set mail subject

	$mail->Body     =   "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
						   This is a system generated email from the Texaco Server
						 <br>
  						  Your files are attached.
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





	$mail = new phpmailer();

	# send via SMTP
	$mail->IsSendmail()	;
	$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers

	$mail->From     	= 	"texaco@rsm2000.co.uk"	; // the email address shown
	$mail->Sender     	= 	"texaco@rsm2000.co.uk"	; // the envelope sender(server) of the email
	$mail->FromName 	= 	"Texaco Server"			; // the name shown

	$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour"); 	// mail recipients
	$mail->AddAddress("Mandy.Hodson@valero.com","Mandy Hodson"); 	// mail recipients
	$mail->AddAddress("Bronagh.Carron@valero.com","Bronagh Carron"); 	// mail recipients
	$mail->AddAddress("Tony.Webb@valero.com","Tony Webb"); 	// mail recipients
	
	$mail->AddReplyTo("pseymour@rsm2000.co.uk","Peter Seymour")	; // the reply to mail address and name

	$mail->WordWrap = 50						; // set word wrap
	$mail->AddAttachment($filepath."HighFrequencyTransactions.zip", "HighFrequencyTransactions.zip")	; // add attachment file names and descriptions






	$mail->IsHTML(true)	; // set mail as html

	$mail->Subject  =  "Star Rewards Extracts for $month" ; // set mail subject

	$mail->Body     =   "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
						   This is a system generated email from the Texaco Server
						 <br>
  						  Your files are attached.
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

	echo "$dbMailSent\r\n"	
















?>
