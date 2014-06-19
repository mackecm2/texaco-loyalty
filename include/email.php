<?

require("class.phpmailer.php")												;
$filepath 	=	"/tmp/"	    ;

function sendemail($type,$detail1,$detail2)
{

	/*
	   here comes the email - make sure you have the
	   phpmailer class installed in the include directory
	   in your php.ini file, or in the same directory as
	   this script.

	   you can get phpmailer help online at : -
	   http://phpmailer.sourceforge.net
	*/

	# create new phpmailer instance

	$mail = new phpmailer();

	# send via SMTP
	$mail->IsSendmail()	;
	$mail->Host 		= 	"texaco.rsmsecure.com"					; // SMTP servers

	$mail->FromName	= 'WEOU Server'; // text for "From" shown to recipient e.g. RSM Admin
	$mail->From	= 'weou@rsm2000.com'; // email address for "From" shown to recipient
	$mail->AddReplyTo('pseymour@rsm2000.co.uk', 'Peter Seymour'); // the reply to mail address and name
	$mail->Sender =	'pseymour@rsm2000.co.uk'; // the envelope sender(server) of the email for undeliverable mail

	$mail->WordWrap = 50																	; // set word wrap
	$mail->IsHTML(true)																		; // set mail as html


	switch ($type)
	{

		case 'IssuesListNewItem':

			#	For this case $detail1 contains the issue number so lets go and retrieve some details

			$sql = "select * from Issues where IssueNo = $detail1";
			$Results = DBQueryExitOnFailure( $sql );
			$row = mysql_fetch_assoc( $Results );

			$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour")	; // mail recipient
			$mail->AddAddress("jonb@vccp.com","Jon Boardman")	; // mail recipient
//			$mail->AddAddress("hannahf@vccp.com","Hannah Fitzgerald")	; // mail recipient
//			$mail->AddAddress("ed@tobias.tv","Ed Wilson")	; // mail recipient
//			$mail->AddAddress("patricia@tobias.tv","Patricia Vogelenzang")	; // mail recipient

			#	$mail->AddAttachment("$filepath$attachment", "$attachment")								; // add attachment file names and descriptions

			$mail->Subject  =  "New Issue Created - $row[ShortDescription]" ; // set mail subject

			$mail->Body     =  "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
<BR><strong>Issue Details</strong>
<table class=\"table2\" width=\"50%\" >
<tr>
	<td>Creation Date</td><td> = $row[CreationDate]</td>
</tr>
<tr>
	<td>Created By</td><td> = $row[CreatedBy]</td>
</tr>
<tr>
	<td>Priority</td><td> = $row[PriorityGrp]</td>
</tr>
<tr>
	<td>Responsibility</td><td> = $row[Responsablity]</td>
</tr>
<tr>
	<td valign=top>Description</td><td> = $row[Description]</td>
</tr>
</table>
<br></font>";

		break;





		case 'IssuesListItemEdit':

			#	For this case $detail1 contains the issue number and $detail2
			#	contains the changed item details so lets go and retrieve some details

			$sql = "select * from Issues where IssueNo = $detail1";
			$Results = DBQueryExitOnFailure( $sql );
			$row = mysql_fetch_assoc( $Results );

			$mail->AddAddress("pseymour@rsm2000.co.uk","Peter Seymour")	; // mail recipient
//			$mail->AddAddress("nick@meanley.com","Nick Meanley")	; // mail recipient
			$mail->AddAddress("jonb@vccp.com","Jon Boardman")	; // mail recipient
//			$mail->AddAddress("hannahf@vccp.com","Hannah Fitzgerald")	; // mail recipient
//			$mail->AddAddress("sladems@chevrontexaco.com","Matt Slade")	; // mail recipient
//			$mail->AddAddress("ed@tobias.tv","Ed Wilson")	; // mail recipient
//			$mail->AddAddress("patricia@tobias.tv","Patricia Vogelenzang")	; // mail recipient

			#	$mail->AddAttachment("$filepath$attachment", "$attachment")								; // add attachment file names and descriptions

			$mail->Subject  =  "Issue Edited - $row[ShortDescription]" ; // set mail subject

			$mail->Body     =  "<p>
						 <font color=\"#004080\" size=\"-1\" face=\"Trebuchet MS, Tahoma, Verdana, Arial\">
<BR><strong>Issue Details</strong>
<table class=\"table2\" width=\"50%\" >
<tr>
	<td>Creation Date</td><td> = $row[CreationDate]</td>
</tr>
<tr>
	<td>Created By</td><td> = $row[CreatedBy]</td>
</tr>
<tr>
	<td>Priority</td><td> = $row[PriorityGrp]</td>
</tr>
<tr>
	<td>Responsibility</td><td> = $row[Responsablity]</td>
</tr>
<tr>
	<td valign=top>Description</td><td> = $row[Description]</td>
</tr>
<tr>
	<td>Change</td><td> = $detail2</td>
</tr>
</table>
<br></font>";

		break;









	}
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

	# echo "$dbMailSent<br />"												;

}



?>