<?php
error_reporting( E_ALL ^ E_NOTICE);

#  This script contains all of the commands that are 2XXX


function preparexml($string)
{
  $string = stripslashes($string);
  $string = str_replace ("&", "&amp;", $string);
  $string = str_replace ("<", "&lt;", $string);
  $string = str_replace (">", "&gt;", $string);
  $string = str_replace ("£", "&#163;", $string);
  $string = str_replace ("ü", "&#252;", $string);
  return($string);
 }

function sendmemberdata( $row )
{
	#  Ok lets send the member details to the client


  $MemberNo 		= $row['MemberNo'];
  $AccountNo  		= $row['AccountNo'];
  $PrimaryMember  	= $row['PrimaryMember'];
  $PrimaryCard  	= $row['PrimaryCard'];
  $Title  			= preparexml($row['Title']);
  $Initials  		= preparexml($row['Initials']);
  $Forename  		= preparexml($row['Forename']);
  $Surname  		= preparexml($row['Surname']);
  $Honours  		= preparexml($row['Honours']);
  $Salutation  		= preparexml($row['Salutation']);
  $GenderCode  		= $row['GenderCode'];
  $DOB  			= $row['DOB'];
  $HomePhone  		= $row['HomePhone'];
  $HomeVerified  	= $row['HomeVerified'];
  $WorkPhone  		= $row['WorkPhone'];
  $WorkVerified  	= $row['WorkVerified'];
  $Fax  			= $row['Fax'];
  $Email 			= preparexml($row['Email']);
  $EmailVerified 	= $row['EmailVerified'];
  $Address1 		= preparexml($row['Address1']);
  $Address2 		= preparexml($row['Address2']);
  $Address3 		= preparexml($row['Address3']);
  $Address4 		= preparexml($row['Address4']);
  $Address5 		= preparexml($row['Address5']);
  $PostCode 		= preparexml($row['PostCode']);
  $AddressVerified 	= $row['AddressVerified'];
  $CntryCode 		= $row['CntryCode'];
  $StatementPref 	= $row['StatementPref'];
  $OKMail 			= $row['OKMail'];
  $TOKMail 			= $row['TOKMail'];
  $OKEMail 			= $row['OKEMail'];
  $OKSMS 			= $row['OKSMS'];
  $OKHomePhone 		= $row['OKHomePhone'];
  $OKWorkPhone 		= $row['OKWorkPhone'];
  $GoneAway 		= $row['GoneAway'];
  $Deceased 		= $row['Deceased'];
  $LastLogin 		= $row['LastLogin']  ;
  $StaffID	 	= $row['StaffID']  ;

	#	Lets sort out the characters that XML doesnt like

	$MemberData = preparexml($row['MemberData']);

	#	We need the Account balance as well

	$result = getaccount($AccountNo);
	$arow = mysql_fetch_array($result);
	$AccountType = $arow["AccountType"];
	$balance = $arow["Balance"];
	if(  $row['PrimaryMember'] == 'Y' and $arow["RedemptionStopDate"] == '' )
	{
		$CanRedeem = 'Y';
	}
	else
	{
		$CanRedeem = 'N';
	}

	$response =  "
<membernumber>$MemberNo</membernumber>
<account>$AccountNo</account>
<balance>$balance</balance>
<primarymember>$PrimaryMember</primarymember>
<primarycard>$PrimaryCard</primarycard>
<title>$Title</title>
<initials>$Initials</initials>
<forename>$Forename</forename>
<surname>$Surname</surname>
<honours>$Honours</honours>
<salutation>$Salutation</salutation>
<gendercode>$GenderCode</gendercode>
//<segmentcode>$SegmentCode</segmentcode>
<dob>$DOB</dob>
<homephone>$HomePhone</homephone>
<homeverified>$HomeVerified</homeverified>
<workphone>$WorkPhone</workphone>
<workverified>$WorkVerified</workverified>
<fax>$Fax</fax>
<email>$Email</email>
<emailverified>$EmailVerified</emailverified>
<address1>$Address1</address1>
<address2>$Address2</address2>
<address3>$Address3</address3>
<address4>$Address4</address4>
<address5>$Address5</address5>
<postcode>$PostCode</postcode>
<addressverified>$AddressVerified</addressverified>
<countrycode>$CntryCode</countrycode>
<statementpref>$StatementPref</statementpref>
<canredeem>$CanRedeem</canredeem>
<okmail>$OKMail</okmail>
<tokmail>$TOKMail</tokmail>
<okemail>$OKEMail</okemail>
<oksms>$OKSMS</oksms>
<okhomephone>$OKHomePhone</okhomephone>
<okworkphone>$OKWorkPhone</okworkphone>
<goneaway>$GoneAway</goneaway>
<deceased>$Deceased</deceased>
<memberdata>$MemberData</memberdata>
<lastlogin>$LastLogin</lastlogin>
<accounttype>$AccountType</accounttype>
<staffid>$StaffID</staffid>";

	sendsimpleresponse( "Member Data", $response );

}

function sendgroupmemberdata( $row )
{
	#  Ok lets send the member details to the client


  $MemberNo 		= $row['MemberNo'];
  $AccountNo  		= $row['AccountNo'];
  $PrimaryMember  	= $row['PrimaryMember'];
  $PrimaryCard  	= $row['PrimaryCard'];
  $Organisation  	= $row['Organisation'];
  $Title  			= preparexml($row['Title']);
  $Initials  		= preparexml($row['Initials']);
  $Forename  		= preparexml($row['Forename']);
  $Surname  		= preparexml($row['Surname']);
  $Honours  		= preparexml($row['Honours']);
  $Salutation  		= preparexml($row['Salutation']);
  $GenderCode  		= $row['GenderCode'];
  $DOB  			= $row['DOB'];
  $HomePhone  		= $row['HomePhone'];
  $HomeVerified  	= $row['HomeVerified'];
  $WorkPhone  		= $row['WorkPhone'];
  $WorkVerified  	= $row['WorkVerified'];
  $Fax  			= $row['Fax'];
  $Email 			= preparexml($row['Email']);
  $EmailVerified 	= $row['EmailVerified'];
  $Address1 		= preparexml($row['Address1']);
  $Address2 		= preparexml($row['Address2']);
  $Address3 		= preparexml($row['Address3']);
  $Address4 		= preparexml($row['Address4']);
  $Address5 		= preparexml($row['Address5']);
  $PostCode 		= preparexml($row['PostCode']);
  $AddressVerified 	= $row['AddressVerified'];
  $CntryCode 		= $row['CntryCode'];
  $StatementPref 	= $row['StatementPref'];
  $OKMail 			= $row['OKMail'];
  $TOKMail 			= $row['TOKMail'];
  $OKEMail 			= $row['OKEMail'];
  $OKSMS 			= $row['OKSMS'];
  $OKHomePhone 		= $row['OKHomePhone'];
  $OKWorkPhone 		= $row['OKWorkPhone'];
  $GoneAway 		= $row['GoneAway'];
  $Deceased 		= $row['Deceased'];
  $LastLogin 		= $row['LastLogin']  ;
  $StaffID	 	= $row['StaffID']  ;
  $MemberBalance 	= $row['MemberBalance']  ;
  $MemberType 	= $row['MemberType']  ;

	#	Lets sort out the characters that XML doesnt like

	$MemberData = preparexml($row['MemberData']);

	#	We need the Account balance as well

	$result = getaccount($AccountNo);
	$arow = mysql_fetch_array($result);
	$AccountType = $arow["AccountType"];
	$balance = $arow["Balance"];
	if(  $row['PrimaryMember'] == 'Y' and $arow["RedemptionStopDate"] == '' )
	{
		$CanRedeem = 'Y';
	}
	else
	{
		$CanRedeem = 'N';
	}
	
#	We need the Account running total (all points earned) as well

	$RunningTotal = getredemptiontotal($AccountNo) + $balance;

	$response =  "
<membernumber>$MemberNo</membernumber>
<account>$AccountNo</account>
<balance>$balance</balance>
<memberbalance>$MemberBalance</memberbalance>
<primarymember>$PrimaryMember</primarymember>
<primarycard>$PrimaryCard</primarycard>
<organisation>$Organisation</organisation> 
<title>$Title</title>
<initials>$Initials</initials>
<forename>$Forename</forename>
<surname>$Surname</surname>
<honours>$Honours</honours>
<salutation>$Salutation</salutation>
<gendercode>$GenderCode</gendercode>
<dob>$DOB</dob>
<homephone>$HomePhone</homephone>
<homeverified>$HomeVerified</homeverified>
<workphone>$WorkPhone</workphone>
<workverified>$WorkVerified</workverified>
<fax>$Fax</fax>
<email>$Email</email>
<emailverified>$EmailVerified</emailverified>
<address1>$Address1</address1>
<address2>$Address2</address2>
<address3>$Address3</address3>
<address4>$Address4</address4>
<address5>$Address5</address5>
<postcode>$PostCode</postcode>
<addressverified>$AddressVerified</addressverified>
<countrycode>$CntryCode</countrycode>
<statementpref>$StatementPref</statementpref>
<canredeem>$CanRedeem</canredeem>
<okmail>$OKMail</okmail>
<tokmail>$TOKMail</tokmail>
<okemail>$OKEMail</okemail>
<oksms>$OKSMS</oksms>
<okhomephone>$OKHomePhone</okhomephone>
<okworkphone>$OKWorkPhone</okworkphone>
<goneaway>$GoneAway</goneaway>
<deceased>$Deceased</deceased>
<memberdata>$MemberData</memberdata>
<lastlogin>$LastLogin</lastlogin>
<accounttype>$AccountType</accounttype>
<staffid>$StaffID</staffid>
<runningtotal>$RunningTotal</runningtotal>
<membertype> $MemberType</membertype>";

	sendsimpleresponse( "Member Data", $response );

}


function command2001()
{

	#	This command returns the member details if the supplied username and passwords match.
	$datetime = date("Y-m-d H:i:s") ;

	$username = $_GET['username'];
	$password = $_GET['password'];
	$merchantid = $_GET['merchantid'];

	# Now we need to look up the Account.
	if ($merchantid < 4)
	{
		$result = userlogon($username, $password, $merchantid) ;
	}
 	else 
 	{
 		senderror( 54 );
 	}

	switch($result)
	{

	case 'FAILMEMBER':
		senderror( 35 );
	break;

	case 'PASSWORDFAIL':
		senderror( 36 );
	break;

	case 'NOPASSWORD':
		senderror( 37 );
	break;
		
	case 'WRONGSITE':
		senderror( 54 );	
		
	case 'CLOSED':
		senderror( 58 );	
	break;

	}  //  end switch($result)

	#	If we get here then the user exists and the password was correct. Now lets get the full
	#	User details

	$result = getmember($username, $password);

 	if (!($row = mysql_fetch_array($result)))
	{

		#  This should never happen as the member search succeeded before but the code is here just in case..
		senderror( 99 );
 	}

	$MemberNo = $row['MemberNo'];

	$update = "LastLogin = now() ";

	#	Now update the member record with the last login date and time

	$result = updatemember( $MemberNo, $update);


	#  Ok lets send the member details to the client
	sendmemberdata( $row );

}



function command2002()
{

	#	This command returns the password for a supplied username.

	$username = $_GET['username'];

	# Now we need to look up the Account.

 	$result = getpasswrd($username) ;

	if( mysql_num_rows( $result ) == 0 )
	{
		senderror( 35 );
 	}

	if( mysql_num_rows( $result ) == 1 )
	{
		$row = mysql_fetch_assoc( $result );
		if($row['Passwrd'] == '')
 		{
			senderror( 37 );
		}
		else
		{
	#	If we get here then the user exists and a password is set so lets return it to the client
			sendsimpleresponse( "Password detail", "<password>$row[Passwrd]</password>");
		}
 	}

	#	If we get here then the user exists and has multiple password so lets return them all

	$response =  "";
	while( $row = mysql_fetch_assoc( $result ) )
	{
		$response .= "<password>".preparexml($row[Passwrd])."</password>";
	}
	sendsimpleresponse( "Passwords details", $response );
 }



function command2003()
{

	#	This command returns the member details if the supplied passwords match.

	$cardnumber = $_GET['cardnumber'];
 	if( !luhnCheck( $cardnumber ) )
	{
		senderror( 51 );
	}

	$result = cardmemberenquiry($cardnumber);

 	if (!($row = mysql_fetch_array($result)))
	{
		senderror( 41 );
	}
	else if( $row['Status'] == 'Closed' )
	{
		senderror( 58 ) ;
		
	}
	

	#  Ok lets send the member details to the client
	sendmemberdata( $row );
}



function command2004()
{

	#	This command returns the member details if the supplied Surname and Postcode match.

	$surname = $_GET['surname'];
	$postcode = $_GET['postcode'];

	$result = searchmember($surname, $postcode);

 	if (!($row = mysql_fetch_array($result)))
	{
		senderror( 41);
	}

	#  Ok lets send the member details to the client
	sendmemberdata( $row );
}


function command2005()
{

	#	This command returns the cards associated with the supplied member.

	$membernumber = $_GET['membernumber'];

	#	Lets see if the member exists

	$result = memberenquiry($membernumber);

 	if (!($row = mysql_fetch_array($result)))
	{
		#	The member does not exist in the database
		senderror( 39);
	}

	$account = $row['AccountNo'];

	#	Member exists so lets now look up all of the cards

	$result = getmembercards($membernumber);

	if ($row = mysql_fetch_array($result))
	{

		#	Make up the response message

		$response = "
<membernumber>$membernumber</membernumber>
<account>$account</account>";

		$i = 1;

		do
		{
			$response .= "<card$i>$row[CardNo]</card$i>";
			$i++;
		} while($row = mysql_fetch_array($result));

		sendsimpleresponse( "Card Details", $response );
	}
	else
	{

		#	The member has no cards linked
		senderror( 42);
	}
}


function command2006()
{

	#	This command returns the registration status of a card.

	$cardnumber = Trim($_GET['cardnumber']);

 	if( !luhnCheck( $cardnumber ) )
	{
		senderror( 51 );
	}

	$CardType = CardRangeCheck( $cardnumber );

	if ( $CardType == "Unknown" )
	{
		senderror( 56 );
	}
	
	$result = cardenquiry($cardnumber);

 	if (!($row = mysql_fetch_array($result)))
	{

		#	The card does not exists in the database
		sendsimpleresponse( "NOT KNOWN", "<cardnumber>$cardnumber</cardnumber>");

		exit();

	}

	#	If we get here the card is in the database - now lets see if it has an associated member record

	$memberno = $row['MemberNo'];
	if($memberno == '')
	{
		#	Card is not registered to a member - advise client
		sendsimpleresponse( "NOT REGISTERED", "<cardnumber>$cardnumber</cardnumber>");
 		exit();
	}
	else
	{
		#	Card is associated with a member so lets get the Member

		$result = memberenquiry($memberno);
		$row = mysql_fetch_array($result);

		$account = $row['AccountNo'];
		$accounttype = $row['AccountType'];
		$merchantid = $_GET['merchantid'];
	
	if($accounttype == 'G' && $merchantid < 4 )
	{
		#	GL member accessing SR
		senderror( 54 );
	}	
	if( ( $accounttype != 'G' OR is_null( $accounttype)) && $merchantid > 3 )
	{
		#	SR member accessing GL
		senderror( 55 );
	}		

	#	Is the password set ?

		if($row['Passwrd'] == '')
		{
			$response =  "<cardnumber>$cardnumber</cardnumber><membernumber>$memberno</membernumber><account>$account</account>";
			sendsimpleresponse( "NO PASSWORD", $response );
		}
		else
		{
			#	Card is registered and a password is set
			$response =  "<cardnumber>$cardnumber</cardnumber><membernumber>$memberno</membernumber><account>$account</account>";
			sendsimpleresponse( "REGISTERED", $response );
		}
	}
}


function command2007()
{

	#	This command returns the transaction history of a card.

	$cardnumber 	= $_GET['cardnumber'];
	$txlimit		= $_GET['txlimit'];

	#	Lets see if the card exists
  	if( !luhnCheck( $cardnumber ) )
	{
		senderror( 51 );
	}

	$result = cardenquiry($cardnumber);

 	if (!($row = mysql_fetch_array($result)))
	{

		#	The card does not exist in the database
		sendsimpleresponse( "NOT KNOWN", "<cardnumber>$cardnumber</cardnumber>");
 			exit();

	}

	#	card exists so lets get the transactions associated with it

	$result = GetTransactionHistory( $cardnumber, $txlimit);

	if ($row = mysql_fetch_array($result))
	{

		#	Make up the response message

		$response = "<cardnumber>$cardnumber</cardnumber>";

		do {

				$response .= "
<sitecode>$row[SiteCode]</sitecode>
<transtime>$row[TransTime]</transtime>
<transvalue>$row[TransValue]</transvalue>
<pointsawarded>$row[PointsAwarded]</pointsawarded>
<receiptno>$row[ReceiptNo]</receiptno>";


		} while($row = mysql_fetch_array($result));

		sendsimpleresponse("Transaction Details", $response);
	}
	else
	{
		#	The member has no transaction details
		senderror( 43);
	}

}

function command2008()
{

	#	This command returns the redemption history of a member.

	$membernumber 	= $_GET['membernumber'];
	$txlimit		= $_GET['txlimit'];
	$offset			= $_GET['offset'];

	$result = memberenquiry($membernumber);

 	if (!($row = mysql_fetch_array($result)))
	{

		#	The member does not exist in the database
		senderror( 39 );
	}


	#	member exists so lets get the transactions associated with it

	$result = GetOrderHistory( $membernumber, $txlimit, $offset);

	if ($row = mysql_fetch_array($result))
	{

		#	Make up the response message

		$response = "<membernumber>$membernumber</membernumber>";

		do {
				#	First output the order number

				$response .= "
<orderno>$row[OrderNo]</orderno>
<redeemdatetime>$row[CreationDate]</redeemdatetime>
";

				#	For each order record we need to get the order products

				$orderproductresult = GetOrderProducts($row['OrderNo']);
				$orderproductrow = mysql_fetch_array($orderproductresult);

				do
				{

					$response .= "
<merchantid>$orderproductrow[MerchantId]</merchantid>
<prodcode>$orderproductrow[ProductId]</prodcode>
<prodqty>$orderproductrow[Quantity]</prodqty>
<prodcost>$orderproductrow[Cost]</prodcost>";

				} while($orderproductrow = mysql_fetch_array($orderproductresult));


		} while($row = mysql_fetch_array($result));

		sendsimpleresponse("Redemption Details", $response );
	}
	else
	{

		#	The member has no order details

		senderror( 44);
	}
}






function command2009()
{

	#	This command registers a new member.
	$datetime = date("Y-m-d H:i:s") ;


	#	Lets find out if the card exists first.  If we get back a Member record then we need to
	#	reject this registration attempt

	// Need to check if the email is unique first.

  	$UserEmail = $_GET['email'];
	$cardnumber = trim($_GET['cardnumber']);
	$merchantid = $_GET['merchantid'];

	if( $UserEmail == "" )
	{
		senderror( 46);
	}

	if( !CheckUserNameUnique( $UserEmail ) )
	{
		senderror( 40 );
	}

	if( $cardnumber != "" )
	{
		if( !luhnCheck( $cardnumber ) )
		{
			senderror( 51 );
		}
		$result = cardenquiry($cardnumber);
		$row = mysql_fetch_array($result);

		if (isset($row['CardNo']))
		{
			# echo "have card<br>";

			#	We have  card number - now lets see if a member is already assigned
			$cardPresent = true;
			if (isset($row['MemberNo']))
			{
				senderror( 38);
			}
		}
	}

	#	If we have a card record but no member then we need to create a new member record
	#	If we have been supplied an account number then we are assigning this new member
	#	to that account if not, we need to get an account created first.
	#	We also need to transfer any points there might be already acrued on the card

	if(!isset($_GET['account']))
	{
		#	We do not have an account to attach to - set up a new account
		if( $merchantid != 4 )
		{
			$accountno = createaccount( 0, '', 0 );
			$primary = 'Y';
		}
		else 
		{
			$accountno = 2325212;
			$primary = 'N';
		}

		
		#	If we've created a new account we should set the homesite to the first swipe location
		#	of this card
		
		if($row['FirstSwipeLoc'] <> '')
		{
			sethomesite( $accountno, $row['FirstSwipeLoc']);		
		}
		
	}
	else
	{
		$accountno = $_GET['account'];
		// May want to verify the account exists
		$primary = 'N';
	}


	#	Now we can go ahead and create the new Member record

 # 	$data['MemberNo']			= $_GET['membernumber'];
  	$data['AccountNo']  		= $accountno;
  	$data['PrimaryMember']  	= $primary;
  	$data['Passwrd']  			= $_GET['password'];
  	$data['PrimaryCard']  		= $_GET['cardnumber'];
  	$data['Title']  			= $_GET['title'];
  	$data['Initials']  			= $_GET['initials'];
  	$data['Forename']  			= $_GET['forename'];
  	$data['Surname']  			= $_GET['surname'];
  	$data['Honours']  			= $_GET['honours'];
  	$data['Salutation']  		= $_GET['salutation'];
  	$data['GenderCode']  		= $_GET['gendercode'];
  	$data['DOB']  				= $_GET['dob'];
  	$data['HomePhone']  		= $_GET['homephone'];
  	$data['HomeVerified']  		= $_GET['homeverified'];
  	$data['WorkPhone']  		= $_GET['workphone'];
  	$data['WorkVerified']  		= $_GET['workverified'];
  	$data['Fax']  				= $_GET['fax'];
  	$data['Email'] 				= $_GET['email'];
  	$data['EmailVerified'] 		= $_GET['emailverified'];
  	$data['Address1'] 			= $_GET['address1'];
  	$data['Address2'] 			= $_GET['address2'];
  	$data['Address3'] 			= $_GET['address3'];
  	$data['Address4'] 			= $_GET['address4'];
  	$data['Address5'] 			= $_GET['address5'];
  	$data['PostCode'] 			= $_GET['postcode'];
  	$data['AddressVerified'] 	= $_GET['addressverified'];
  	$data['CntryCode'] 			= $_GET['countrycode'];
  	$data['StatementPref'] 		= $_GET['statementpref'];
//  	$data['CanRedeem'] 			= $_GET['canredeem'];
  	$data['OKMail'] 			= $_GET['okmail'];
  	$data['TOKMail'] 			= $_GET['tokmail'];
  	$data['OKEMail'] 			= $_GET['okemail'];
  	$data['OKSMS']				= $_GET['oksms'];
  	$data['OKHomePhone'] 		= $_GET['okhomephone'];
  	$data['OKWorkPhone'] 		= $_GET['okworkphone'];
  	$data['GoneAway'] 			= $_GET['goneaway'];
  	$data['Deceased'] 			= $_GET['deceased'];
  	$data['StaffID'] 			= $_GET['staffid'];
  	$data['MemberData'] 		= addslashes($_GET['memberdata']);
	$data['CreatedBy']			= 'WEB';
	$data['CreationDate']		= $datetime;
	if( $merchantid == 4 )
	{
		$data['Organisation'] = "Crystal Palace";
	}	

	$membernumber = createthemember($data);

	# to get to here we must have a unique email so give-em 50p
	
	## MRM 16/07/09  if we don't want them to receive the 50 point registration bonus we will have to put a check in here
	## MRM 22/02/09  only allocated to members who register online and have a statement preference of E (not P).
	
	if( $merchantid == 1 && $data['StatementPref'] == "E" && ValidateEmailAddress($UserEmail))
	{
		AdjustBalance( TrackingEmailBonus50, $membernumber, $accountno, "Online registration bonus", 50 );
	}


	#	Now we can update the card record with the new membernumber

	if( $cardnumber != "" )
	{
		MergeCardToMember(  $cardnumber, $membernumber, false );
		#	Last thing is to advise the client of the new details
	}
	else
	{
		#	We dont have a card number - so we need to create a CardRequest record so the
		#	customer will receive one. MRM 04 08 09 merchant id of 4 is Crystal Palace
		if( $merchantid == 4 )
		{
			InsertRequestRecord( $membernumber, "GM" );
		}
		else
		{
			InsertRequestRecord( $membernumber, "WR" );
		}
		
	}

	$response =  "<membernumber>$membernumber</membernumber><account>$accountno</account>";
	sendsimpleresponse( "OK",  $response );
}


function command2010()
{

	#	This command assigns a card to a member.

	$cardnumber = $_GET['cardnumber'];
	$membernumber = $_GET['membernumber'];

 	if( !luhnCheck( $cardnumber ) )
	{
		senderror( 51 );
	}

	#	Lets find out if the card is already registered first.  If we get back a Member record then we need to
	#	reject this registration attempt

	$result = cardenquiry($cardnumber);
	if ($row = mysql_fetch_array($result))
	{
		#	We have  card number - now lets see if a member is already assigned

 		if ($row['MemberNo'] != '')
		{
			senderror( 38);
		}
	}
	#	This card is not in the database so we can create it and asign it to the member supplied.
	MergeCardToMember( $cardnumber, $membernumber, false );
	#	Advise the client

	sendsimpleresponse( "Card has been registered", "");
}

function checkDifference( $row, $field, $input )
{
	global $tracking;
	global $update;
	if( isset( $_GET[$input] ) )
	{
		if( $row[$field] != $_GET[$input] )
		{
			if ( $field == "Passwrd" )				//  01 09 10 MRM Mantis 2966: Mask password in tracking notes 
			{	
				$tracking .= " Password changed ";			
			}
			else 
			{	
				$tracking .= " $field ".$row[$field]."=>".$_GET[$input];
			}
			if( $_GET[$input] == "" )
			{
				$update .= ", $field = null";
				return true;
			}
			else
			{
				$update .= ", $field = '". smart_escape( $_GET[$input] ). "'";
				return true;
			}			
		}
	}
}

function command2012()
{
	global $tracking;
	global $update;

	#	This command updates a member record.
	$dueforbonus = false;
	$membernumber = $_GET['membernumber'];

	#	Lets find out if the member exists first.  If we do not get back a Member record then we need to
	#	reject this registration attempt

	$result = memberenquiry($membernumber);
	$update = "";
 	$tracking = "";

	if ($row = mysql_fetch_array($result))
	{
		$accountno = $row["AccountNo"];
		# echo "have member<br>";

		#	Ok so now we can set the new details
		$AddressChanged = false;

		checkDifference( $row,'Passwrd',	'password');
  		checkDifference( $row,'Title',		'title');
  		checkDifference( $row,'Initials',	'initials');
  		checkDifference( $row,'Forename',	'forename');
  		checkDifference( $row,'Surname',	'surname');
  		checkDifference( $row,'Honours',	'honours');
  		checkDifference( $row,'Salutation', 'salutation');
  		checkDifference( $row,'GenderCode', 'gendercode');

		checkDifference( $row,'DOB',		'dob');
   		if( checkDifference( $row,'HomePhone', 'homephone') )
		{
			$update .= ", HomeVerified = now()";
		}
   		if( checkDifference( $row,'WorkPhone', 'workphone') )
		{
			$update .= ", WorkVerified = now()";
		}

  		checkDifference( $row,'Fax',		'fax');

  		$AddressChanged |= checkDifference( $row,'Address1',	'address1');
  		$AddressChanged |= checkDifference( $row,'Address2',	'address2');
  		$AddressChanged |= checkDifference( $row,'Address3',	'address3');
  		$AddressChanged |= checkDifference( $row,'Address4',	'address4');
  		$AddressChanged |= checkDifference( $row,'Address5',	'address5');
  		$AddressChanged |= checkDifference( $row,'PostCode',	'postcode');
//  		checkDifference( $row,'AddressVerified', 'addressverified']);

		if( $AddressChanged )
		{
			$update .= ", AddressVerified = now()";
		}
		checkDifference( $row,'CntryCode', 'countrycode');
  		checkDifference( $row,'StatementPref', 'statementpref');
  		checkDifference( $row,'OKMail', 'okmail');
  		checkDifference( $row,'TOKMail', 'tokmail');
  		checkDifference( $row,'OKEMail', 'okemail');
  		checkDifference( $row,'OKSMS', 'oksms');
  		checkDifference( $row,'OKHomePhone', 'okhomephone');
  		checkDifference( $row,'OKWorkPhone', 'okworkphone');
  		checkDifference( $row,'GoneAway', 'goneaway');
  		checkDifference( $row,'Deceased', 'deceased');
  		checkDifference( $row,'MemberData', 'memberdata');

  		if( checkDifference( $row,'Email',		'email') )
		{
			$update .= ", EmailVerified = now()";

			$UserEmail = $_GET['email'];
			if( $UserEmail == "" )
			{
				# Email address not set
				senderror( 46);
			}
			if( !CheckUserNameUnique( $UserEmail ) )
			{
				# Email address already used
				senderror( 40 );
			}
			if( !ValidateEmailAddress( $UserEmail ) )
			{
				# Invalid Email Address
				senderror( 57 );
			}
			if( $row["Email"] == ""  or !ValidateEmailAddress( $row["Email"] ) )
			{
				# to get to here we must have a unique email, and the previous address was either blank or invalid so give-em 50p
				$dueforbonus = true;
			}
		}

		if( $update != "" )
		{
			$update = "RevisedDate = now(), RevisedBy = 'WEB'". $update;
			updatemember( $membernumber, $update);
			InsertTrackingRecord( TrackingWebUpdate, $membernumber, $accountno, $tracking, 0 );
			if( $dueforbonus )
			{
				$result = memberenquiry($membernumber);
				$row = mysql_fetch_array($result);
				if ($row["StatementPref"] == "E")
				{
					AdjustBalance( TrackingEmailBonus50, $membernumber, $accountno, "Online email registration bonus", 50 );
				}
			}
			sendsimpleresponse( "Member record updated","");
		}
		else
		{
			sendsimpleresponse( "No changes specified","");
		}
		#	Let the client know

	}
	else
	{
		#	Member not known - advise client
		senderror( 41);
	}
}

function Command2014()
{
	// Get Member Questions.
	$membernumber = $_GET['membernumber'];
	$limit =$_GET['limit'];
	$result = GetMemberQuestionWeb( $membernumber, $limit );

	while( $row = mysql_fetch_assoc( $result ) )
	{
		$response .= "<question>\n";
		$response .= "<questionid>$row[QuestionId]</questionid>\n";
		$response .= "<questiontext>$row[QuestionText]</questiontext>\n";
		$response .= "<questiontype>$row[Type]</questiontype>\n";
		$response .= "<currentvalue>$row[Answer]</currentvalue>\n";
		if( $row["Type"] == 'S')
		{
			$result2 = GetQuestionOptions( $row[QuestionId], false );
 			while( $row2 = mysql_fetch_assoc( $result2 ) )
			{
				$response .= "<questionoption value='$row2[OptionValue]'>".preparexml($row2[OptionText])."</questionoption>\n";
			}
		}
		$response .= "</question>\n";
	}
	sendsimpleresponse( "Questions", $response);
}

function Command2015()
{
	$membernumber = $_GET['membernumber'];

	$marker = 'response';
	// Set Member Response
	$count = 0;
	foreach( $_GET as $field => $value )
	{
		if( strncmp( $field, $marker, strlen($marker) ) == 0 )
		{
			$questionId = substr( $field, strlen($marker) );
			RecordAnswer( $questionId, $membernumber, $value, "" );
			$count++;
		}
	}

	sendsimpleresponse( "$count records updated", $response);
}

function Command2016()
{

	#	Get Custom data.

 	$membernumber = $_GET['membernumber'];

	if( isset($_GET['customflag1'])  )
	{
		if( $_GET['customflag1'] == 'virginno' )
		{
			$result = GetVirginNo( $membernumber );

			if ($row = mysql_fetch_assoc($result))
			{
				$response = "<virginno>$row[VirginNo]</virginno>";
			}
			else
			{
				senderror( 41);
			}
		}
		else
		{
			senderror( 47);
		}

		sendsimpleresponse( "OK", $response);
	}
}

function Command2017()
{

	#	Set Custom data.

	$memberno = $_GET['membernumber'];
	$virginno = $_GET['virginno'];
	$count = 0;
	if( isset( $virginno ) && isset($memberno) )
	{
		$count++;
		SetVirginNo( $memberno, $virginno );
	}
	sendsimpleresponse( "$count Data record(s) updated", "");
}

function Command2018()
{
	$accountnumber = $_GET['accountnumber'];
	$redemptionid = $_GET['redemptionid'];
	if( CancelRequest( $accountnumber, $redemptionid ) 	)
	{
	 	sendsimpleresponse( "OK", $response);
	}
	else
	{
		senderror( 48);
	}
}

function Command2019()
{
	$membernumber = $_GET['membernumber'];
	$AccountNo = GetTheAccountNo( $membernumber );
	if( $AccountNo > 0 )
	{
		InsertRequestRecord( $membernumber, "WR" );
 		sendsimpleresponse( "OK", $response);
	}
	else
	{
		senderror( 39 );
	}
}


function Command2020()
{
	// Get Member Messages.
	$membernumber = $_GET['membernumber'];
	$limit =$_GET['limit'];
	$results = GetCurrentWebActiveMessages();
	$i = 1;

	while( $row = mysql_fetch_assoc( $results ) )
	{
		if(($limit == '0' ) OR ($i <=  $limit) )
		{

			#	We need to check that the message relates to this member.

			#echo"Have MessageNo $row[MessageNo]<br>";
			$result = checkmembermessage($membernumber,$row);
			#echo"result is $result<br>";
			if( $result )
			{
				$response .= "<message>\n";
				$response .= "<messageid>$i</messageid>\n";
				$response .= "<messagetext>$row[MessageText]</messagetext>\n";
				$response .= "</message>\n";
				$i++;

			}
			else
			{
				 #echo "Not valid";
			}

		}
		else
		{
			break;
		}

	}
	#echo"<br>$response<br>";
	sendsimpleresponse( "Messages", $response);
}


function command2028()
{

	#	This command returns the redemption history of a member in the new format.

	$membernumber 	= $_GET['membernumber'];
	$txlimit		= $_GET['txlimit'];
	$offset			= $_GET['offset'];

	$result = memberenquiry($membernumber);

 	if (!($row = mysql_fetch_array($result)))
	{

		#	The member does not exist in the database
		senderror( 39);
	}


	#	member exists so lets get the transactions associated with it

	$result = GetOrderHistory( $membernumber, $txlimit, $offset);

	if ( mysql_num_rows($result) > 0)
	{
		#	Make up the response message

		$response = "<membernumber>$membernumber</membernumber>";

		while($row = mysql_fetch_array($result))
		{
			#	First output the order number

			$response .= "<order> <orderno>$row[OrderNo]</orderno><redeemdatetime>$row[CreationDate]</redeemdatetime><products>";

			#	For each order record we need to get the order products

			$orderproductresult = GetOrderProducts($row['OrderNo']);

			while($orderproductrow = mysql_fetch_array($orderproductresult))
			{

				$response .= "<product>
<merchantid>$orderproductrow[MerchantId]</merchantid>
<code>$orderproductrow[ProductId]</code>
<qty>$orderproductrow[Quantity]</qty>
<type>$orderproductrow[QuantityType]</type>
<cost>$orderproductrow[Cost]</cost>
<redemptionid>$orderproductrow[RedeptionId]</redemptionid>
<status>$orderproductrow[Status]</status>
<description>".preparexml($orderproductrow[Description])."</description>
</product>\n";

			}
			$response .= "<balancebefore>".$row[BalanceBefore]."</balancebefore></products></order>";
		}

		sendsimpleresponse("Redemption Details", $response );

	}
	else
	{
		#	The member has no order details
 		senderror( 44);
	}

}



function command2029()
{
    //  MRM 3/7/2008 - new command to register a staff member into the Customer Loyalty process 
	#	
	$datetime = date("Y-m-d H:i:s") ;
	$sitecode = $_GET['SiteCode'];

	#	No validation on card number yet, but check the e-mail id.  If we get back a Member record then we need to
	#	reject this registration attempt

	// Need to check if the email is unique first.

  	$UserEmail = $_GET['email'];
	//$cardnumber = trim($_GET['cardnumber']);

	if( $UserEmail == "" )
	{
		senderror( 46);
	}

	if( !CheckUserNameUnique( $UserEmail ) )
	{
		senderror( 40 );
	}

	if(!isset($_GET['account']))
	{
		#	We do not have an account to attach to - set up a new account
		$accountno = createaccount( 0, 'D', $sitecode );
		$primary = 'Y';
	}
	else
	{
		$accountno = $_GET['account'];
		// May want to verify the account exists
		$primary = 'N';
	}


	#	Now we can go ahead and create the new Member record

 # 	$data['MemberNo']			= $_GET['membernumber'];
  	$data['AccountNo']  		= $accountno;
  	$data['PrimaryMember']  	= $primary;
  	$data['Passwrd']  			= $_GET['password'];
  	$data['PrimaryCard']  		= $_GET['cardnumber'];
  	$data['Title']  			= $_GET['title'];
  	$data['Initials']  			= $_GET['initials'];
  	$data['Forename']  			= $_GET['forename'];
  	$data['Surname']  			= $_GET['surname'];
  	$data['Honours']  			= $_GET['honours'];
  	$data['Salutation']  		= $_GET['salutation'];
  	$data['GenderCode']  		= $_GET['gendercode'];
  	$data['DOB']  				= $_GET['dob'];
  	$data['HomePhone']  		= $_GET['homephone'];
  	$data['HomeVerified']  		= $_GET['homeverified'];
  	$data['WorkPhone']  		= $_GET['workphone'];
  	$data['WorkVerified']  		= $_GET['workverified'];
  	$data['Fax']  				= $_GET['fax'];
  	$data['Email'] 				= $_GET['email'];
  	$data['EmailVerified'] 		= $_GET['emailverified'];
  	$data['Address1'] 			= $_GET['address1'];
  	$data['Address2'] 			= $_GET['address2'];
  	$data['Address3'] 			= $_GET['address3'];
  	$data['Address4'] 			= $_GET['address4'];
  	$data['Address5'] 			= $_GET['address5'];
  	$data['PostCode'] 			= $_GET['postcode'];
  	$data['AddressVerified'] 	= $_GET['addressverified'];
  	$data['CntryCode'] 			= $_GET['countrycode'];
  	$data['StatementPref'] 		= $_GET['statementpref'];
// MRM 05/05/09 - Redemption Flag set to N for new Staff Members
  	$data['CanRedeem'] 			= 'N';
  	$data['OKMail'] 			= $_GET['okmail'];
  	$data['TOKMail'] 			= $_GET['tokmail'];
  	$data['OKEMail'] 			= $_GET['okemail'];
  	$data['OKSMS']				= $_GET['oksms'];
  	$data['OKHomePhone'] 		= $_GET['okhomephone'];
  	$data['OKWorkPhone'] 		= $_GET['okworkphone'];
  	$data['GoneAway'] 			= $_GET['goneaway'];
  	$data['Deceased'] 			= $_GET['deceased'];
  	$data['MemberData'] 		= addslashes($_GET['memberdata']);
  	$data['CreatedBy']			= 'WEB';
	$data['CreationDate']		= $datetime;

	$sql = "select StaffRegistrations, SiteCode from SiteRegistrations where SiteCode= $sitecode";
	$results = DBQueryExitOnFailure( $sql );
	$numrows = mysql_num_rows($results);
		if( $numrows >0 )
	{
		$row = mysql_fetch_assoc( $results );
		$data['StaffID'] = ($row['SiteCode']*1000) + ($row['StaffRegistrations'] + 1);
		$staffid = $data['StaffID'];
		$data['PrimaryCard'] = "01".$staffid.date("Ymd");
		$primarycard = $data['PrimaryCard'];
	}
	else
	{
		senderror( 34);
	}
			
	$membernumber = createthemember($data);
	$sql = "Insert into Cards (CardNo, MemberNo, CreatedBy, CreationDate ) values ('$primarycard', $membernumber, '$uname', now() )"; 
	$results = DBQueryExitOnFailure( $sql );

	$sql = "update SiteRegistrations set StaffRegistrations = (StaffRegistrations + 1) where SiteCode= $sitecode";
	$results = DBQueryExitOnFailure( $sql );
	$response =  "<responsecode>$responsecode</responsecode><responsetext>$responsetext</responsetext><membernumber>$membernumber</membernumber><account>$accountno</account><staffid>$staffid</staffid>";
	sendsimpleresponse( "OK",  $response );
}


function command2030()
{
    //  SDT 1808/2008 - new command to return Leaderboard for Staff Registrations
	#	
	$datetime = date("Y-m-d H:i:s") ;
	
	if(isset($_GET['top']))
	{
		$numresponses = $_GET['top'];
		$limitquery = "limit $numresponses";
	}
	else
	{
		$numresponses = 0;
	};
	
	$i = 1;


	$sql = "select StaffID,Forename,Surname,SiteName,NoOfRegistrations, A.Balance AS NoOfStars from StaffMembers 
	AS S JOIN Members AS M USING ( MemberNo ) JOIN Accounts AS A WHERE ( M.AccountNo = A.AccountNo )
	order by NoOfRegistrations DESC $limitquery "; 

	$results = DBQueryExitOnFailure( $sql );

	$response = "<LEADERBOARD>\n";	

	
	while( $row = mysql_fetch_assoc( $results ) )
	{

		$Forename = preparexml($row['Forename']);
		$Surname = preparexml($row['Surname']);
		$SiteName = preparexml($row['SiteName']);

		if(($numresponses == '0' ) OR ($i <=  $numresponses) )
		{
				$response .= "<STAFF>\n";
				$response .= "<POSITION>$i</POSITION>\n";
				$response .= "<NAME>$Forename $Surname</NAME>\n";
				$response .= "<STATION>$SiteName</STATION>\n";
				$response .= "<REGISTRATIONS>$row[NoOfRegistrations]</REGISTRATIONS>\n";
				$response .= "<STARS>$row[NoOfStars] </STARS>\n";
				$response .= "</STAFF>\n";
				$i++;

		}
		else
		{
			break;
		}
		
		unset($staffrecord);

	}
	
	
	
	$response .= "</LEADERBOARD>\n";	
	
	
	#echo"<br>$response<br>";
	
	sendsimpleresponse( "Leaderboard Results", $response);
}


function command2031()
{
    //  SDT 1808/2008 - new command to return Position in Leaderboard for a member of staff
	#	
	
	
	
	$datetime = date("Y-m-d H:i:s") ;
	
	if(!isset($_GET['StaffID']))
	{
		senderror(53);
	}	

	
	$StaffID = $_GET['StaffID'];

	$i = 1;
	
	
	$sql = "SELECT Forename,Surname,StaffID	FROM `StaffMembers` where  StaffID = '$StaffID' "; 

		$staffresult = DBQueryExitOnFailure( $sql );
		$staffrecord = mysql_fetch_assoc( $staffresult );
		
	if($staffrecord['StaffID'] == '')
	{
		senderror(53);
	}		


	$sql = "SELECT StaffID,NoOfRegistrations, A.Balance AS NoOfStars FROM StaffMembers
	AS S JOIN Members AS M USING ( MemberNo ) JOIN Accounts AS A WHERE ( M.AccountNo = A.AccountNo )
	order by NoOfRegistrations DESC $limitquery "; 


	$results = DBQueryExitOnFailure( $sql );

	$response = "<LEADERBOARD>\n";	

	
	while( $row = mysql_fetch_assoc( $results ) )
	{


		$Forename = preparexml($row['Forename']);
		$Surname = preparexml($row['Surname']);
		$SiteName = preparexml($row['SiteName']);

		if(  $StaffID == $row['StaffID'])
		{
				$response .= "<STAFF>\n";
				$response .= "<POSITION>$i</POSITION>\n";
				$response .= "<NAME>$Forename $Surname</NAME>\n";
				$response .= "<STATION>$SiteName</STATION>\n";
				$response .= "<REGISTRATIONS>$row[NoOfRegistrations] </REGISTRATIONS>\n";
				$response .= "<STARS>$row[NoOfStars] </STARS>\n";
				$response .= "</STAFF>\n";
				
				break;
		}		
		$i++;
		
		unset($staffrecord);

	}
	
	
	
	$response .= "</LEADERBOARD>\n";	
	
	
	#echo"<br>$response<br>";
	
	sendsimpleresponse( "Leaderboard Results", $response);
}
function command2032()
{
    //  MRM 26/09/2008 - new command to return registrations for staff for a given Station
		
	$datetime = date("Y-m-d H:i:s") ;
	
	if(!isset($_GET['StationID']))
	{
		senderror(54);
	}	

	$StationID = $_GET['StationID'];
// 											MRM 22/05/09 Staff ID added to call
	$sql = "SELECT StaffID, Forename, Surname, NoOfRegistrations, A.Balance AS NoOfStars FROM StaffMembers 
	AS S JOIN Members AS M USING ( MemberNo ) JOIN Accounts AS A
	WHERE ( M.AccountNo = A.AccountNo ) AND S.HomeSite = '$StationID' ORDER BY NoOfRegistrations DESC  "; 
	$i = 1;
	$results = DBQueryExitOnFailure( $sql );

	$response = "<LEADERBOARD>\n";	

	while( $row = mysql_fetch_assoc( $results ) )
	{

		$Forename = preparexml($row['Forename']);
		$Surname = preparexml($row['Surname']);
		$response .= "<STAFF>\n";
		$response .= "<POSITION>$i</POSITION>\n";
		$response .= "<NAME>$Forename $Surname</NAME>\n";
		$response .= "<STATION>$SiteName </STATION>\n";
		$response .= "<REGISTRATIONS>$row[NoOfRegistrations] </REGISTRATIONS>\n";
		$response .= "<STARS>$row[NoOfStars] </STARS>\n";
		$response .= "<STAFFID>$row[StaffID] </STAFFID>\n";
		$response .= "</STAFF>\n";
		$i++;
	
	#unset($staffrecord);

	}

	$response .= "</LEADERBOARD>\n";	
		
	sendsimpleresponse( "Leaderboard Results", $response);
}
	
function command2033()
{
    //  MRM 31/07/2008 - new command for Group Loyalty member login 
		
	$datetime = date("Y-m-d H:i:s") ;

	$username = $_GET['username'];
	$password = $_GET['password'];

	# Now we need to look up the Account.

 	$$result = userlogon($username, $password, $merchantid) ;

	switch($result)
	{

	case 'FAILMEMBER':
		senderror( 35 );
	break;

	case 'PASSWORDFAIL':
		senderror( 36 );
	break;

	case 'NOPASSWORD':
		senderror( 37 );
	break;
	
	case 'WRONGSITE':
		senderror( 55 );
	break;


	}  //  end switch($result)

	#	If we get here then the user exists and the password was correct. Now lets get the full
	#	User details

	$result = getmember($username, $password);

 	if (!($row = mysql_fetch_array($result)))
	{

		#  This should never happen as the member search succeeded before but the code is here just in case..
		senderror( 99 );
 	}

	$MemberNo = $row['MemberNo'];

	$update = "LastLogin = now() ";

	#	Now update the member record with the last login date and time

	$result = updatemember( $MemberNo, $update);


	#  Ok lets send the member details to the client
	sendgroupmemberdata( $row );
	
}

?>