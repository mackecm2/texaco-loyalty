<?php

	/*
	   here we go - flag up the phpmailer class
	   we'll be using it later.
	*/

	require("class.phpmailer.php")												;

    /* let's get some variables sorted. */
	include "../include/ServerName.inc";
	# start with date variables -

    $now				=		date("H-i-d-m-y")								;
    $nowth				=		date("dS-F-Y")									;
    $creationday		=		date("l")										;
	$creationdaydate	=		date("d")										;
	$creationmonth		=		date("F")										;
	$creationmonthno	=		date("m")										;
	$creationyear		=		date("Y")										;
	$creationhour		=		date("H")										;
	$creationminute		=		date("i")										;

    $filename			=		"homes.zip"						;
    $filepath 			=		"/data/compower/downloads/"											;




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
	$mail->Host 		= 	"texaco.rsmsecure.com"					; // SMTP servers

	$mail->From     	= 	"texaco@retail-services.co.uk"			; // the email address shown
	$mail->Sender     	= 	"texaco@retail-services.co.uk"			; // the envelope sender(server) of the email
	$mail->FromName 	= 	"Texaco Server"								; // the name shown

	$mail->AddAddress("CDB.Atosworldlineuk@atosorigin.com","AtosOrigin")					; // mail recipients
//	$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour")					; // mail recipients
	$mail->AddAddress("mmackechnie@rsm2000.co.uk","Mike MacKechnie");
	$mail->AddReplyTo("pseymour@rsm2000.co.uk","Texaco Reply to Address")	; // the reply to mail address and name

	$mail->WordWrap = 50																	; // set word wrap
	$mail->AddAttachment("$filepath$filename", "$filename")								; // add attachment file names and descriptions
	$mail->IsHTML(true)																		; // set mail as html

	$mail->Subject  =  "AUTOPROCESS:RSMGCC" ; // set mail subject

	switch ($SERVER_NAME_FOR_ALL) {
    case "TEST":
		$env = "test";
        break;
    case "DEMO":
		$env = "demo";
        break;
	case "MASTER":
		$env = "live";
        break;
    default:
       	$env = "unknown";
	}
	
	$mail->Body     =   "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
						   This is a system generated email from the $env Texaco Server
						 <br>
  						  sent out at $creationhour:$creationminute on the $nowth .
  						 </font>
  						</p>
						<p>
						  &nbsp;
						</p>

						<p>&nbsp;</p>"		; // put your html mail body here
									;


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
