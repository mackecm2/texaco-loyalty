	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Processing File
	**
	********************************************************************/

	//******************************************************************
	//
	// /FileProcessing/UKFuels/CheckForDailyFile.php
	//
	// Checks for the daily UK Fuels file
	// sends an e-mail if no file found  
	// checks the size of the file
	// sends an e-mail if the file is empty
    //  
    //  MRM 19/11/2008 - First Issue
	//
	//******************************************************************

	function sendemail($fileToFind, $error)
	{
			$mail = new phpmailer();
				# send via SMTP
			$mail->IsSendmail()	;
			$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
			$mail->FromName	= ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM Admin
			$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
			$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
			$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
			$mail->AddAddress('martin.woodbinefms@ukfuels.co.uk', 'Martin Woodbine'); // mail recipient address and name, repeat for each recipent
			$mail->AddAddress('jennifer.caccamo@ukfuels.co.uk', 'Jennifer Caccamo'); // added 05/06/09 MRM Mantis 882
			$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');
			$mail->AddCC('pseymour@rsm2000.co.uk', 'Peter Seymour');
			$mail->AddCC('John.Mozie@valero.com', 'John Mozie'); 
									
			switch ($error)
			{
			case 0:
			    $mail->Subject = 'UK Fuels Transactions - File is empty';
			    $line2 = $fileToFind.' is empty. Please arrange for the file to be resent.</p></font>'."\n";
			    break;
			case 1:
			    $mail->Subject = 'UK Fuels Transactions - File not found';
			    $line2 = $fileToFind.' has not been delivered. Please arrange for the file to be resent.</p></font>'."\n";
			    break;
			default: 
				$mail->Subject = 'UK Fuels Transactions - Unknown Error';
				$line2 = 'Unknown error occurred - please check /FileProcessing/UkFuels/FileProcess.php.</p></font>'."\n";
			    break;   
			}
			
			$mail->WordWrap = 70; // set word wrap
			$mail->IsHTML(true); // set mail as html

			// HTML Message Body
			$mail->Body =
			'<font size=2><font face=Verdana><p>The daily check for the UK Fuels Transaction File has found the following error: ' 
			.$line2
			.'<p></p>Texaco Card Loyalty Monitoring System<BR><BR></font></font>'."\n"
			.'</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n"
			.'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n"
			.'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n"
			.'error please notify the sender by email and delete this message and any '."\n"
			.'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n"
			.'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n"
			.'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n"
			.'Registration Number: 3703548</EM></font></font></p>';

			// Text Message Body
			$mail->AltBody = "UK Fuels File Error\n\n"
				.$line2;

			// send the email and check on its success
			if (!$mail->Send())
			{
				$dbMailSent = "Mail Send to UK Fuels - fail\n\n";
			} 
			else
			{
				$dbMailSent = "Mail Send to UK Fuels - pass\n\n";
			}
				echo $dbMailSent;
	}

	function sizeFile($fileToFind)
	{
		$lines = count(file($fileToFind)); 

		echo "There are $lines lines in $fileToFind\r\n"; 
		return $lines;
	}

	// Main function
	include "../../include/DB.inc";
	include "../../include/Locations.php";
	require "../../mailsender/class.phpmailer.php";
	
	$filePath =  LocationUKFuelsDirectory;
	$filePattern = "TXAC".date("Ymd").".DAT";
	$fileToFind = $filePath.$filePattern;

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	if (file_exists($fileToFind))
	{
   		echo "filename is $fileToFind\r\n";
   		$NumberOfLines = sizeFile($fileToFind);
   		if ($NumberOfLines == 0)
   		{
   			sendemail($fileToFind, 0);
   		} 
	}
	else
	{
    	echo "The file $fileToFind does not exist\r\n";
    	
    	sendemail($fileToFind, 1);
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>
