	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Processing File
	**
	********************************************************************/

	//******************************************************************
	//
	// /FileProcessing/FIS/CheckFISBunkerFile.php
	//
	// Checks for the monthly FIS Bunker Card Data file
	// sends an e-mail if no file found  
	// checks the size of the file
	// sends an e-mail if the file is empty
    //  
    //  MRM 14/03/2011 - First Issue
	//
	//******************************************************************

include "../../include/DB.inc";
require "../../Reporting/GeneralReportFunctions.php";
include "../../include/Locations.php";
require "../../mailsender/class.phpmailer.php";

	function sendemail($fileToFind, $error)
	{
			$mail = new phpmailer();
				# send via SMTP
			$mail->IsSendmail()	;
			$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
			$mail->FromName = ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM 
			$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
			$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
			$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
			$mail->AddAddress('Ian.Gay@fisglobal.com', 'Ian Gay'); // mail recipient address and name, repeat for each recipent
			$mail->AddAddress('transax.helpdesk@fisglobal.com', 'FIS Helpdesk');
			$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');

			$mail->AddCC('Martin.Skilling@fisglobal.com', 'Martin Skilling'); 
			$mail->AddCC('John.Mozie@valero.com', 'John Mozie'); 
			$mail->AddCC('pseymour@rsm2000.co.uk', 'Peter Seymour'); 
						
			switch ($error)
			{
			case 0:
			    $mail->Subject = 'FIS Bunker Card Data File ('.$fileToFind.') - File is empty';
			    $line2 = $fileToFind.' is empty. Please arrange for the file to be resent.</p></font>'."\n";
			    break;
			case 1:
			    $mail->Subject = 'FIS Bunker Card Data File ('.$fileToFind.') - File not found';
			    $line2 = $fileToFind.' has not been delivered. Please arrange for the file to be resent.</p></font>'."\n";
			    break;
			case 2:
			    $mail->Subject = 'FIS Bunker Card Data File ('.$fileToFind.') - File has the wrong number of columns';
			    $line2 = $fileToFind.' has the wrong number of columns. Please arrange for the file to be resent.</p></font>'."\n";
			    break;
			default: 
				$mail->Subject = 'FIS Bunker Card Data File ('.$fileToFind.') - Unknown Error';
				$line2 = 'Unknown error occurred - please check /FileProcessing/FIS/FileProcess.php.</p></font>'."\n";
			    break;   
			}
			
			$mail->WordWrap = 70; // set word wrap
			$mail->IsHTML(true); // set mail as html

			// HTML Message Body
			$mail->Body =
			'<font size=2><font face=Verdana><p>The monthly check for the FIS Bunker Card Data File has found the following error: ' 
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
			$mail->AltBody = "FIS Bunker Card Data File Error\n\n"
				.$line2;

			// send the email and check on its success
			if (!$mail->Send())
			{
				$dbMailSent = "Mail Send to FIS - fail\n\n";
			} 
			else
			{
				$dbMailSent = "Mail Send to FIS - pass\n\n";
			}
				echo $dbMailSent;
	}

	function sizeFile($fileToFind)
	{
		$lines = count(file($fileToFind)); 

		echo "There are $lines lines in $fileToFind\r\n"; 
		return $lines;
	}
	
	function ColumnCount($fileToFind)
	{
		$columns = explode("," , $fileToFind); 
		$result = count($columns);
		return $result;
	}
	
	// Main function

	connectToDB( MasterServer, TexacoDB );
	$filePath =  LocationFISDirectory;
	$LastMonth = GetLastMonth();
	$filePattern = "STARREWARDS_BUNKER_";
	$fileToFind = $filePath.$filePattern.$LastMonth.".csv";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

	if (file_exists($fileToFind))
	{
   		echo "filename is $fileToFind\r\n";
   		$NumberOfLines = sizeFile($fileToFind);
   		
		$handle   = fopen($fileToFind, 'r');
		$data     = fread($handle, filesize($fileToFind));
		$explodedData = explode ( "\n", $data);
		fclose($handle);
   		$firstline = $explodedData[0];
   		$NumberOfColumns = ColumnCount($firstline);
		echo "There are $NumberOfColumns  columns in $fileToFind\r\n"; 
   		
   		if ($NumberOfLines == 0)
   		{
   			sendemail($fileToFind, 0);
   		} 
   		else 
   		{
   			if ($NumberOfColumns != 6)
	   		{
	   			sendemail($fileToFind, 2);
	   		} 
   		}
	}
	else
	{
    	echo "The file $fileToFind does not exist\r\n";
    	
    	sendemail($fileToFind, 1);
	}

	echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>