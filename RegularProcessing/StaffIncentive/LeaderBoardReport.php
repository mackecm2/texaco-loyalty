	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Staff Incentive Scheme - LeaderBoard Management Report  
	**   Written by MRM 30/09/2008
	********************************************************************/

    /// **** NEW VERSION

	//******************************************************************
	//
	// /RegularProcessing/StaffIncentive/LeaderBoardReport.php
	// reports on:
	//  Number of participants on the leaderboard
	//  Number of sites particpating
	//  Total number of stars awarded
	//  Total number of stars redeeemed
	//  Total number of stars declined
	//  Ranking on the leader-board 
	//  Staff name 
	//  Total number of registrations 
	//  Total number of stars 
	//  Total number of £5 vouchers available to redeem
	//  Total number of stars redeemed  
	//  Site number 
	//  Site name 
	//  Area Code 
	//  Staff ID 
	//  Number of rejected registrations
	//  Points deducted for invalid registrations 
	//******************************************************************

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";
	
	//  Number of participants on the leaderboard
	function NumberOfParticipants()
	{
		$sql = "SELECT M.StaffID FROM texaco.Members AS M JOIN texaco.Accounts AS A USING ( AccountNo ) JOIN sitedata AS S WHERE (A.HomeSite = S.SiteCode) AND  (A.AccountType = 'D') AND A.CreationDate > '2009-05-18'";
		$results = DBQueryExitOnFailure( $sql );
		$number=mysql_num_rows($results); 
		return $number;
	}
	
	//  Number of sites particpating
		function NumberOfSitesParticipating()
	{
		$sql="SELECT A.HomeSite, COUNT(*) FROM texaco.Members AS M JOIN texaco.Accounts AS A USING ( AccountNo ) JOIN sitedata AS S WHERE (A.HomeSite = S.SiteCode) AND  (A.AccountType = 'D') AND A.CreationDate > '2009-05-18' GROUP BY A.HomeSite";
		$results = DBQueryExitOnFailure( $sql );
		$number=mysql_num_rows($results); 
		return $number;
	}
	
	//  Total number of stars awarded
		function TotalNumberOfSingleStarsAwarded()
	{
		$sql = "SELECT COUNT(*)*25 AS StarsAwarded FROM CustomerRegistrations WHERE Valid = 'Y' AND CreationDate > '2009-05-24 23:59:59' AND CreationDate < '2009-07-06 00:00:00' GROUP BY Valid";
		$results = DBSingleStatQueryNoError( $sql );
		return $results;
	}
		//  Total number of double stars awarded
		function TotalNumberOfDoubleStarsAwarded()
	{
		$sql = "SELECT COUNT(*)*50 AS StarsAwarded FROM CustomerRegistrations WHERE Valid = 'Y' AND CreationDate > '2009-07-05 23:59:59' AND CreationDate < '2009-07-20 00:00:00'  GROUP BY Valid";
		$results = DBSingleStatQueryNoError( $sql );
		return $results;
	}
	
	//  Total number of stars redeeemed
		function TotalNumberOfStarsRedeemed()
	{
		$sql = "SELECT SUM( Cost ) FROM StaffRedemptionsHistory";
		$results = DBSingleStatQueryNoError( $sql );
		return $results;
	}	
	
	//  Total number of stars declined
		function TotalNumberOfSingleStarsDeclined()
	{
		$sql = "SELECT COUNT(*)*25 AS StarsDeclined FROM CustomerRegistrations WHERE Valid = 'N' AND CreationDate > '2009-05-24 23:59:59' AND CreationDate < '2009-07-06 00:00:00' GROUP BY Valid";
		$results = DBSingleStatQueryNoError( $sql );
		return $results;
	}
	
	//  Total number of doublestars declined
		function TotalNumberOfDoubleStarsDeclined()
	{
		$sql = "SELECT COUNT(*)*50 AS StarsDeclined FROM CustomerRegistrations WHERE Valid = 'N' AND CreationDate > '2009-07-05 23:59:59' AND CreationDate < '2009-07-20 00:00:00'  GROUP BY Valid";
		$results = DBSingleStatQueryNoError( $sql );
		return $results;
	}
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	$count = 1;
	$TotalNumberOfStarsAwarded =  TotalNumberOfSingleStarsAwarded() + TotalNumberOfDoubleStarsAwarded();
	$TotalNumberOfStarsDeclined =  TotalNumberOfSingleStarsDeclined() + TotalNumberOfDoubleStarsDeclined();
	echo "Number of participants on the leaderboard : ". NumberOfParticipants()."\r\n";
	echo "Number of sites participating : ". NumberOfSitesParticipating()."\r\n";
	echo "Total number of stars awarded : ".$TotalNumberOfStarsAwarded."\r\n";
	echo "Total number of stars redeemed : ". TotalNumberOfStarsRedeemed()."\r\n";
	echo "Total number of stars declined : ".$TotalNumberOfStarsDeclined."\r\n";
	
	$sql = "SELECT StaffID, SUBSTR( StaffID, 1, 6 ) AS SiteCode, Forename, Surname, NoOfRegistrations, A.Balance AS NoOfStars, SiteName, NoOfRegistrations
		FROM StaffMembers AS S JOIN Members AS M USING ( MemberNo ) JOIN Accounts AS A WHERE ( M.AccountNo = A.AccountNo )ORDER BY NoOfRegistrations DESC"; 
	$results = DBQueryExitOnFailure( $sql );
	$file = fopen("LeaderBoardReport.csv","w");
	$line = "Ranking on the leader-board,Staff Name,Total number of valid registrations,Total number of stars earned,Total number of stars redeemed,Total number of £5 vouchers available to redeem,";
	$line .= "Site number,Site name,Area Code,Staff ID,Number of rejected registrations,Points deducted for invalid registrations";
	
	fputcsv($file,split(',',$line));
	
	while( $row = mysql_fetch_array( $results ) )
	{
		$staffid = $row['StaffID'];
		$balance = $row['NoOfStars'];
		$Forename = $row['Forename'];
		$Surname = $row['Surname'];
		
		$sql = "SELECT SUM(Cost) FROM StaffRedemptionsHistory WHERE StaffID = ".$staffid;
		$staffredemptions = DBSingleStatQueryNoError( $sql );
		if ($staffredemptions!=NULL AND $staffredemptions!="" )
		{
			$pointsearned = $balance + $staffredemptions;
		}
		else 
		{
			$pointsearned = $balance;
			$staffredemptions = 0;	
		}
		$vouchers = floor($balance/500);
		
		$sql = "SELECT AreaCode FROM sitedata WHERE SiteCode = ".$row['SiteCode'];
		$areacode = DBSingleStatQueryNoError( $sql );

	//  Number of rejected registrations
		$sql = "SELECT Count( * ) FROM CustomerRegistrations WHERE Valid = 'N' AND StaffID = ". $row['StaffID']." AND CreationDate > '2009-05-25'";
		$rejects = DBSingleStatQueryNoError( $sql );

	//  Points deducted for invalid registrations 
		$deducts = $rejects * 25;
		
		$line = $count.",".$Forename." ".$Surname.",".$row['NoOfRegistrations'].",".$pointsearned.",".$staffredemptions.",".$vouchers.",".$row['SiteCode'].",".$row['SiteName'];
		$line .= ",".$areacode.",".$row['StaffID'].",".$rejects.",".$deducts;
		fputcsv($file,split(',',$line));
		$count++;		
		
	}
	require("../../mailsender/class.phpmailer.php");
	$mail = new phpmailer();

	$mail->FromName	= 'RSM 2000 Ltd'; // text for "From" shown to recipient e.g. RSM Admin
	$mail->From	= 'root@texaco.rsmsecure.com'; // email address for "From" shown to recipient
	$mail->AddReplyTo('mmackechnie@rsm2000.co.uk', 'RSM 2000 Ltd'); // the reply to mail address and name
	$mail->Sender =	'root@texaco.rsmsecure.com'; // the envelope sender(server) of the email for undeliverable mail

	$mail->AddAddress('Bronagh.Carron@valero.com', 'Bronagh Carron'); // mail recipient address and name, repeat for each recipent
	$mail->AddAddress('mmackechnie@rsm2000.co.uk', 'Mike MacKechnie');
	$mail->AddAddress('Mandy.Hodson@valero.com', 'Mandy Hodson');
	$mail->AddAddress('Stuart.McBride@valero.com', 'Stuart McBride');
	$mail->Subject = 'LeaderBoard Management Report'; // set mail subject

	$mail->WordWrap = 70; // set word wrap
	$mail->AddAttachment("LeaderBoardReport.csv")	; // add attachment file names and descriptions
	$mail->IsHTML(true); // set mail as html

	// HTML Message Body
	$mail->Body =
	'<font size=3>Bronagh<p>Attached is the latest LeaderBoard Management Report.</p></font>'."\n"
	.'<p>'.date("Y-m-d H:i:s")." \r\n"
	.'<p>Number of participants on the leaderboard : '. NumberOfParticipants()."\r\n"
	.'<p>Number of sites participating : '. NumberOfSitesParticipating()."\r\n"
	.'<p>Total number of stars awarded : '.$TotalNumberOfStarsAwarded."\r\n"
	.'<p>Total number of stars redeemed : '.TotalNumberOfStarsRedeemed()."\r\n"
	.'<p>Total number of stars declined : '.$TotalNumberOfStarsDeclined."\r\n"
	.'<p><font size=2><font face=Verdana></p>Regards<BR><BR></font><font face=Verdana><font size=4><font face="Vladimir Script" '."\n"
	.'color=#0000ff size=6>Star Rewards Staff Incentive Scheme</font><BR></font><BR><BR>'."\n"
	.'</font><font face=Verdana size=1><EM>The content of this email and any attachment is private and may be legally privileged. '."\n"
	.'If you are not the intended recipient, any use, disclosure, copying or forwarding of this email '."\n"
	.'and/or its attachments is unauthorised.<BR>If you have received this email in '."\n"
	.'error please notify the sender by email and delete this message and any '."\n"
	.'attachments immediately from this system.<BR>RSM2000 Ltd - Suite One, Second '."\n"
	.'Floor, Wrest House, Wrest Park, Silsoe, United Kingdom, MK45 4HR.<BR>Registered '."\n"
	.'Address: 16 St. Cuthberts Street, Bedford, United Kingdom MK40 3JG, Company '."\n"
	.'Registration Number: 3703548</EM></font></font></p>';

	// Text Message Body
	$mail->AltBody = "Bronagh\n\n"
		." The latest LeaderBoard Management Report has been created.";
	// send the email and check on its success
	if (!$mail->Send()) {
	$dbMailSent = "Mail Send to Chevron - fail";
	} else {	$dbMailSent = "Mail Send to Chevron - pass";
	}
	echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";
	fclose($file);
	
?>