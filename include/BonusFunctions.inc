<?php

	function sendemail($Process, $startparm, $urgency)
	{
			$mail = new phpmailer();
				# send via SMTP
			$mail->IsSendmail()	;
			$mail->Host 		= 	"texaco.rsmsecure.com"	; // SMTP servers
			$mail->FromName = ServerEnvironment.' Star Rewards Application'; // text for "From" shown to recipient e.g. RSM Admin
			$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
			$mail->AddReplyTo('root@texaco.rsmsecure.com', 'RSM 2000 Ltd'); // the reply to mail address and name
			$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail
			$mail->AddAddress('Bronagh.Carron@valero.com', 'Bronagh M Carron'); // mail recipient address and name, repeat for each recipent
			$mail->AddAddress('Mandy.Hodson@valero.com', 'Mandy Hodson'); 
			$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');
			$mail->AddAddress('Stuart.McBride@valero.com', 'Stuart McBride'); 
			$mail->AddCC('Kersten.Williams@valero.com', 'Kersten Williams'); 
						
			switch ($urgency)
			{
			case 0:
			    $mail->Subject = 'Promotions Automation System - This promotion has Expired';
			    $line2 = 'This promotion has passed its approval date of '. $startparm .'. and so has been set to expired.</p></font>'."\n";
			    break;
			case 24:
			    $mail->Subject = 'Promotions Automation System - FINAL NOTICE';
			    $line2 = 'This promotion needs to be approved before '. $startparm .'.</p></font>'."\n";
			    break;
			case 48:
			    $mail->Subject = 'Promotions Automation System - Please action';
			    $line2 = 'This promotion needs to be approved before '. $startparm .'.</p></font>'."\n";
			    break;
			case 99:
			    $mail->Subject = 'Promotions Automation System - This Promotion has been rejected';
			    $line2 = 'This promotion has been rejected by '. $_SESSION['username'] .' with the following comments: '. stripslashes($startparm) .'.</p></font>'."\n";
			    break;
		    case 100:
			    $mail->Subject = 'Promotions Automation System - This Promotion has been Approved';
			    $line2 = 'This promotion has been approved by '. $_SESSION['username'] .'.</p></font>'."\n";
			    break;
			default: 
				$mail->Subject = "Loyalty Promotions Automation System";
				$line2 = 'Please log onto the Loyalty Promotions Automation System at your earliest convenience to approve/reject the promotion.</p></font>'."\n";
			    break;   
			}
			
			$mail->WordWrap = 70; // set word wrap
			$mail->IsHTML(true); // set mail as html

			// HTML Message Body
			$mail->Body =
			'<font size=2><font face=Verdana><p>A promotion, code name '. $Process .' has been entered on the system. ' 
			.$line2
			.'<p><font size=2><font face=Verdana>Click on <a href=https://texaco.rsmsecure.com/Login.php>https://texaco.rsmsecure.com/Login.php</a> to access the system.</p><BR><BR></font></font>'."\n"
			.'</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n"
			.'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n"
			.'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n"
			.'error please notify the sender by email and delete this message and any '."\n"
			.'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n"
			.'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n"
			.'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n"
			.'Registration Number: 3703548</EM></font></font></p>';

			// Text Message Body
			$mail->AltBody = "Mandy/Bronagh: ".$Process."\n\n"
				.$line2;

			// send the email and check on its success
			if (!$mail->Send())
			{
				$dbMailSent = "Mail Send to Chevron - fail\n\n";
			} 
			else
			{
				$dbMailSent = "Mail Send to Chevron - pass\n\n";
			}
			if ($urgency == 24 or $urgency == 48)
			{
				echo $dbMailSent;
			}
	}

?>