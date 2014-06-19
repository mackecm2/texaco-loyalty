<?php
error_reporting( E_ALL ^ E_NOTICE);
/*************************************************************
	Various functions
*************************************************************/

$ErrorCodes = array();
$ErrorCodes[41] = "Card/Member not known";

$ErrorCodes[1] = "Invalid, unknown or empty requestcode";
$ErrorCodes[2] = "Unknown MerchantID";
$ErrorCodes[3] = "Card Number must be supplied";
$ErrorCodes[8] = "The amount of the transaction must be provided";
$ErrorCodes[9] = "The amount should be an integer number";
$ErrorCodes[16] = "You must provide an Account/PIN code";
$ErrorCodes[17] = "The Account/PIN code should be a alpha numeric character with a length lesser than or equal to 20";
$ErrorCodes[18] = "The Account/Pin provided is missing or invalid";
$ErrorCodes[19] = "The Account provided has insufficient credits to complete the transaction";
$ErrorCodes[20] = "The Account/PIN provided has expired";
$ErrorCodes[21] = "Request comes from an Invalid or Unregistered IP address.  Please contact RSM";
$ErrorCodes[22] = "The amount is greater than the original shadow";
$ErrorCodes[25] = "You must provide an authcode to access the account shadow";
$ErrorCodes[26] = "Unknown authcode for the specified account";
$ErrorCodes[27] = "Specified shadow is no longer active";
$ErrorCodes[28] = "Invalid MerchantTxNo for this AuthCode";
$ErrorCodes[29] = "You must provide an authcode to reference the transaction";
$ErrorCodes[30] = "You must provide a transaction id (txid) to reference the transaction";
$ErrorCodes[31] = "Authcode has already been confirmed";
$ErrorCodes[32] = "AuthCode is already cancelled";
$ErrorCodes[33] = "The merchant specified cannot perform the action requested in the requestcode";
$ErrorCodes[34] = "Site Code not recognised";
$ErrorCodes[35] = "User not known";
$ErrorCodes[36] = "Password Incorrect";
$ErrorCodes[37] = "User registered but password field is blank";
$ErrorCodes[38] = "Card already registered";
$ErrorCodes[39] = "Member Number not known";
$ErrorCodes[40] = "Member already exists";
$ErrorCodes[41] = "Card/Member Details not found";
$ErrorCodes[42] = "No Card Details Registered";
$ErrorCodes[43] = "No Transaction Details Available";
$ErrorCodes[44] = "No Redemption Details Available";
$ErrorCodes[45] = "The ability to redeem points has been disabled for this account";
$ErrorCodes[46] = "Username and password already taken.";
$ErrorCodes[47] = "Custom data flag not recognized.";
$ErrorCodes[48] = "Failed to cancel redemption request.";
$ErrorCodes[49] = "Account No. should be an integer.";
$ErrorCodes[50] = "Bad parameter.";
$ErrorCodes[51] = "Card number failed luhn check.";
$ErrorCodes[52] = "Failed to request card.";
$ErrorCodes[53] = "Invalid or missing StaffID.";
$ErrorCodes[54] = "Invalid Merchant ID.";
$ErrorCodes[55] = "Star Rewards member accessing Group Together web site.";
$ErrorCodes[56] = "Card number out of range.";
$ErrorCodes[57] = "Invalid e-mail address.";
$ErrorCodes[58] = "Account is closed.";

function senderror( $errorCode )
{
	$merchantid = $_GET['merchantid'];
	$datetime = date("Y-m-d H:i:s") ;
	$command = $_GET['requestcode'];
	$msgref = $_GET['msgref'];


	global $ErrorCodes;
	$response =  "<response>
<merchantid>$merchantid</merchantid>
<responsecode>-$errorCode</responsecode>
<responsetext>$ErrorCodes[$errorCode]</responsetext>
</response>";
	sendresponse($merchantid, $msgref, $command, $response, $datetime);
	exit();
}

function sendsimpleresponse( $responsetext, $responsefields )
{
	$merchantid = $_GET['merchantid'];
	$datetime = date("Y-m-d H:i:s") ;
	$command = $_GET['requestcode'];
	$msgref = $_GET['msgref'];

	$response =  "<response>
<merchantid>$merchantid</merchantid>
<responsecode>0</responsecode>
<responsetext>$responsetext</responsetext>
$responsefields
</response>";
	sendresponse($merchantid, $msgref, $command, $response, $datetime);
}

function sendresponse($merchantid, $msgref, $command, $response, $datetime)
{

	#  	This command will be used to send all responses to the client.
	#	All responses with a msgref set are to be logged into the msgref table for recall
	#	if the client has not received our response for any reason

	if(isset($msgref))
	{

		$sql = "INSERT INTO Msgref
	    	(
	        	MerchantId,
	        	Msgref,
	        	RequestCode,
	        	Response,
	        	CreateDate
	    	)
	     	VALUES
			(
				'" . $merchantid . "',
				'" . $msgref . "',
				'" . $command . "',
				'" . $response . "',
				'" . $datetime . "'
			)
		";


		DBQueryExitOnFailure( $sql );

	}


	header("Content-Type: text/xml");
	echo "$response";
	exit();

}


function heartbeat()
{

	$sql = "	use texaco     " ;

	return DBQueryExitOnFailure( $sql );

}

function validatemerchant($merchantid)
{

$sql = "
    						SELECT
    							MerchantId
    						FROM
    							RedemptionMerchants
    						WHERE
    							MerchantId = '$merchantid'
    						LIMIT 1
	                    " ;

return DBQueryExitOnFailure( $sql );

}



function ValidateEmailAddress( $Email )
{
	$valid = true;
	
	if (!ereg("^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,6})$", $Email)) 
	{
		$valid = false;
	}
	
	return $valid;
}



function checkmsgref($merchantid, $msgref)
{

$sql = "
    						SELECT
    							*
    						FROM
    							Msgref
    						WHERE
    							MerchantId 	= '$merchantid'
    						AND
    							Msgref		= '$msgref'
    						LIMIT 1
	                    " ;

return DBQueryExitOnFailure( $sql );

}

function getauthcode($merchantid ,$account,$amount,$merchanttxno, $datetime )
{

$data['MerchantId'] = $merchantid;
$data['MerchantTxNo'] = $merchanttxno;
$data['AccountNo'] = $account;
$data['Amount'] = $amount;
$data['CreateDate'] = $datetime;

$table = "Authcodes";

$authcode = mysqlInsert($data, $table);

return($authcode);


}


function validateauthcode($merchantid ,$account, $authcode )
{


	    $sql = 	"
	    			SELECT
	    				*
	    			FROM
	    				Authcodes
	    			WHERE
	    				AuthNumber = '$authcode'
	    			AND
	    				AccountNo = '$account'
	    			LIMIT 1

	            ";

# echo "$sql";


return DBQueryExitOnFailure( $sql );


}


function updateauthcode( $authcode, $status)
{

	$sql = "UPDATE Authcodes
		SET
			AuthStatus = '$status'
		WHERE
			AuthNumber = '$authcode'
		LIMIT 1";

# echo "$sql";

	return DBQueryExitOnFailure( $sql );
}


function getaccount($accountno)
{
	# Now we need to look up the Account Number.

	if( !is_numeric($accountno))
	{
		senderror( 49 );
	}

	$sql = "SELECT Balance,TotalShadow,RedemptionStopDate,AccountType FROM Accounts WHERE AccountNo = $accountno LIMIT 1";

	return DBQueryExitOnFailure( $sql );

}

function getredemptiontotal($accountno)
{
	# Calculate Running Total for Group Account MRM Mantis 1873 11/02/10

	if( !is_numeric($accountno))
	{
		senderror( 49 );
	}

	$sql = 
	"SELECT SUM(TotalCost) FROM
	 ((SELECT SUM(Cost) AS TotalCost from Orders JOIN OrderProducts using( OrderNo ) where AccountNo = $accountno GROUP BY AccountNo)
		UNION
		(SELECT SUM(Cost) AS TotalCost from Orders JOIN OrderProducts using( OrderNo )
		 Join MergeHistory on( Orders.AccountNo = MergeHistory.SourceAccount)
		 where MergeHistory.DestinationAccount = $accountno GROUP BY AccountNo)) AS Total";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];

}

function getaccountwithmember($accountno, $memberno)
{

	# Now we need to look up the Account Number.
	if( !is_numeric($accountno) or !is_numeric($memberno) )
	{
		senderror( 49 );
	}
    $sql = "SELECT Balance,TotalShadow,RedemptionStopDate, IF(ISNULL(Address1),'',Address1) AS Address1,
			IF(ISNULL(Address2),'',Address2) AS Address2,
			IF(ISNULL(Address3),'',Address3) AS Address3,
			IF(ISNULL(Address4),'',Address4) AS Address4,
			IF(ISNULL(Address5),'',Address5) AS Address5,
			IF(ISNULL(PostCode),'',PostCode) AS PostCode    
    		FROM Accounts join Members using( AccountNo) WHERE Accounts.AccountNo = $accountno and Members.MemberNo = $memberno LIMIT 1";

	return DBQueryExitOnFailure( $sql );

}

// MRM 07/05/09 used to be called getaccountdetails but clashed with another function in DBInterface/MemberInterface.php 

function gettheaccountdetails($accountno)
{
	# Now we need to look up the Account Number.
	if( !is_numeric($accountno))
	{
		senderror( 49 );
	}

    $sql = "SELECT * FROM Accounts WHERE AccountNo = $accountno LIMIT 1";

	return DBQueryExitOnFailure( $sql );

}

function createaccount($balance,$accounttype,$homesite)
{

	# All we need to do is insert a balance.
	if( !is_numeric($balance))
	{
		senderror( 9 );
	}

    $sql = "INSERT INTO Accounts (Balance, AccountType, HomeSite, CreatedBy, CreationDate) VALUES ($balance, '$accounttype', '$homesite', 'WEB', now())";

	DBQueryExitOnFailure( $sql );

	// Get the ID number of the new record
	$accountno = mysql_insert_id();
	
	$sql = "INSERT INTO AccountStatus (AccountNo, Status, StatusSetDate, FraudStatus, RevisedDate)
				VALUES ('$accountno', 'Open', NOW(), '0', NOW( ))";
		DBQueryExitOnFailure( $sql );

	return($accountno);

}

function sethomesite($account, $homesite)
{


	$sql = "Update Accounts SET HomeSite = $homesite,HomeSiteDate = now() where AccountNo = $account LIMIT 1";

	$result = DBQueryExitOnFailure( $sql );

	return $result;
}

function updateaccount($account, $amount, $authamount)
{

	#	Is this the first Redemption ?  If so set the FirstRedempDate
	if( !is_numeric($account))
	{
		senderror( 49 );
	}

	if( !is_numeric($amount))
	{
		senderror( 9 );
	}


	$accountdetails = gettheaccountdetails($account);
	$accountrow = mysql_fetch_array($accountdetails);

	if($row['FirstRedempDate'] == '')
	{
		$sql = "UPDATE Accounts set FirstRedempDate = now() where AccountNo = $account LIMIT 1";
		$updateresult = DBQueryExitOnFailure( $sql );
	}

	$sql = "Update Accounts SET Balance = Balance - $amount, TotalShadow = TotalShadow - $authamount, TotalRedemp = TotalRedemp + $amount, LastRedempDate = now() where AccountNo = $account LIMIT 1";

	# echo "$sql";

	$result = DBQueryExitOnFailure( $sql );

	return $result;
}

function updateaccountshadow($account, $amount)
{

	if( !is_numeric($account))
	{
		senderror( 49 );
	}

	if( !is_numeric($amount))
	{
		senderror( 9 );
	}

	$sql = "Update Accounts SET TotalShadow = TotalShadow +	$amount where AccountNo = $account LIMIT 1";

	# echo "$sql";

	$result = DBQueryExitOnFailure( $sql );

	return $result;

}



function createorder($account, $membernumber, $title, $forename, $name, $address1, $address2, $address3, $address4, $address5, $postcode, $datetime, $userid, $balanceBefore)
{

	if( !is_numeric($account) or !is_numeric($membernumber))
	{
		senderror( 49 );
	}


	$sql = "INSERT INTO Orders
	    (
	        AccountNo,
	        MemberNo,
	        Title, Forename, Name,
	        Address1,
	        Address2,
	        Address3,
			Address4,
			Address5,
			PostCode,
			CreationDate,
			CreatedBy,
			BalanceBefore
	    )
	     VALUES
		(
			" . $account . ",
			" . $membernumber . ",
			'" . smart_escape( $title ) . "',
			'" . smart_escape( $forename ) . "',
			'" . smart_escape( $name ) . "',
			'" . smart_escape($address1) . "',
			'" . smart_escape($address2) . "',
			'" . smart_escape($address3) . "',
			'" . smart_escape($address4) . "',
			'" . smart_escape($address5) . "',
			'" . smart_escape($postcode) . "',
			now(),
			'" . $userid . "',
			$balanceBefore

		)
	";


	# echo "$sql";

	DBQueryExitOnFailure( $sql );


	// Get the ID number of the new record
	$orderno = mysql_insert_id();

	return($orderno);


}



function neworderline( $orderno, $prodcode, $merchantid, $merchanttxno, $prodopt, $qtyType, $prodqty,$prodcost, $prodsupplier, $proddesc, $prodpersonal )
{

	$sql = "INSERT INTO OrderProducts
	    (
	        OrderNo,
	        ProductId,
	        MerchantId,
	        MerchantTxNo,
	        ProductOption,
	        Cost,
			QuantityType,
			Quantity,
			ProductSupplier,
			Description,
			Personalisation
	    )
	     VALUES
		(
			$orderno,
			'".smart_escape($prodcode)."',
			'".smart_escape($merchantid)."',
			'".smart_escape($merchanttxno)."',
			'".smart_escape($prodopt)."',
			'".smart_escape($prodcost)."',
			'".smart_escape($qtyType)."',
			'".smart_escape($prodqty)."',
			'".smart_escape($prodsupplier)."',
			'".smart_escape($proddesc)."',
			'".smart_escape($prodpersonal)."'
		)
	";


//	 echo "$sql";

	DBQueryExitOnFailure( $sql );


	// Get the ID number of the new record
	$result = mysql_insert_id();

	return($result);

}


function userlogon($username, $password, $merchantid) 
{

	$sql = "
    			SELECT
    				Email,Passwrd, AccountType, Status FROM Members JOIN Accounts USING (AccountNo) JOIN AccountStatus USING (AccountNo)
    			WHERE
    				Email = '".smart_escape($username)."'
				order by
				    Passwrd = '".smart_escape($password)."' desc
    			LIMIT 1
	         " ;

	         # echo "$sql";

	$result =  DBQueryExitOnFailure( $sql );

 	if (!($row = mysql_fetch_array($result)))
	{
		#  Member does not exist - return failure
		$response = "FAILMEMBER";

	}
	else
	{
		if (($row['AccountType'] == 'G' && $merchantid == 1) OR ($row['AccountType'] != 'G' && ($merchantid == 4 OR $merchantid == 5)))
		{
			$response = "WRONGSITE";
		}
		
		#	Member exists so does the password match ?

		elseif( strcasecmp($row['Passwrd'],$password ) == 0)
		{
			#	Password matches - but has the account been closed?
			
			if( $row['Status'] == 'Closed' )
			{
				$response = "CLOSED";
			}
			else 
			{
				#	Password matches - return success
				$response = "OK";
			}

		}
		elseif($row['Passwrd'] == '')
		{

			#  Password not set - return failure
			$response = "NOPASSWORD";
		}
		else
		{
			#  Password error - return failure
			$response = "PASSWORDFAIL";
		}

	}

return $response;

}


function getpasswrd( $username )
{
	$sql = "SELECT DISTINCT	Passwrd	FROM Members WHERE Email = '".smart_escape($username)."'" ;

	$result =  DBQueryExitOnFailure( $sql );

	return $result;
}



function getmember($username, $password)
{
	$sql = "SELECT	* FROM Members WHERE Email = '".smart_escape($username)."' AND Passwrd = '".smart_escape($password)."' LIMIT 1" ;

	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}

function memberenquiry($membernumber)
{
	if( !is_numeric($membernumber))
	{
		senderror( 49 );
	}

	$sql = "SELECT *, Accounts.AccountType FROM Members JOIN Accounts USING ( AccountNo )  WHERE MemberNo = $membernumber LIMIT 1";

	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}


function searchmember($surname, $postcode)
{

	$sql = "SELECT * FROM Members WHERE	Surname ='".smart_escape($surname)."' AND PostCode like '" . smart_escape($postcode) . "%' LIMIT 1  " ;

	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}

function GetTheAccountNo( $MemberNo )
{
	$sql = "Select AccountNo from Members where MemberNo = $MemberNo ";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];
}


function createthemember($data)
{

	$table = "Members";

	$memberno = mysqlInsert($data, $table);

	return($memberno);

}

function updatemember($memberno,$update)
{

	if( !is_numeric($memberno))
	{
		senderror( 49 );
	}

	$sql = "
    			UPDATE
    				Members
    			SET
    				$update
    			WHERE
    				MemberNo = $memberno
    			LIMIT 1
	         " ;

	DBQueryExitOnFailure( $sql );
}

function cardmemberenquiry($cardnumber)
{

	$sql = "
				SELECT
					*
				FROM
					Cards join Members using( MemberNo ) JOIN AccountStatus USING ( AccountNo )          
				WHERE
					Cards.CardNo = '".smart_escape($cardnumber)."'
					";                                                    // Account Status needed MRM 28 07 10 Mantis 2429

	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}

function getmembercards($memberno)
{

	if( !is_numeric($memberno))
	{
		senderror( 49 );
	}

	$sql = "
				SELECT
					*
				FROM
					Cards
				WHERE
					MemberNo = $memberno
					";


	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}

function cardenquiry($cardnumber)
{

	$sql = "
				SELECT
					*
				FROM
					Cards
				WHERE
					CardNo = '".smart_escape($cardnumber)."'
				LIMIT 1
					";


	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}


function GetTransactionHistory( $cardnumber, $txlimit)
{

	#	if we have a limit set then setup the limit string

	if(isset($txlimit))
	{
		if( !is_numeric($txlimit) )
		{
			senderror( 50 );
		}
		$limitquery = "LIMIT $txlimit";
	}

	$sql = "SELECT  	SiteCode,TransTime,TransValue,PointsAwarded,ReceiptNo
			FROM		Transactions
			WHERE 		CardNo = '".smart_escape($cardnumber)."'
			ORDER BY	TransTime
			DESC
			$limitquery";

			# echo "$sql";

	return DBQueryExitOnFailure( $sql );
}


function GetOrderHistory( $membernumber, $txlimit, $offset)
{
	if( !is_numeric($membernumber))
	{
		senderror( 49 );
	}

	#	if we have a limit set then setup the limit string

	if(isset($txlimit))
	{
		if( !is_numeric($txlimit) )
		{
			senderror( 50 );
		}
		$limitquery = "LIMIT $txlimit";

		#	If there is an offset add this onto the string

		if(isset($offset))
		{
			if( !is_numeric($offset) )
			{
				senderror( 50 );
			}

			$limitquery = "LIMIT $offset,$txlimit";
		}

	}

	$sql = "SELECT  	OrderNo,MemberNo,CreationDate,BalanceBefore 
			FROM		Orders
			WHERE 		MemberNo = $membernumber
			ORDER BY	CreationDate
			DESC
			$limitquery";

			# echo "$sql";

	return DBQueryExitOnFailure( $sql );
}


function GetOrderProducts($orderno)
{

	$sql = "
				SELECT
					*
				FROM
					OrderProducts
				WHERE
					OrderNo = $orderno
					";


	$result =  DBQueryExitOnFailure( $sql );

	return $result;

}


function CheckUserNameUnique( $Email )
{

	$sql = "select Email from Members where Email= '".smart_escape($Email)."'";

	$results = DBQueryExitOnFailure( $sql );

	return (mysql_num_rows( $results ) == 0);
}


function GetVirginNo( $membernumber )
{
	if( !is_numeric($membernumber))
	{
		senderror( 49 );
	}
	$sql = "select VirginNo from Accounts join Members using(AccountNo) where MemberNo = $membernumber";
	return DBQueryExitOnFailure( $sql );
}


function SetVirginNo( $memberno, $virginno )
{
	if( !is_numeric($memberno))
	{
		senderror( 49 );
	}
	if( !is_numeric($virginno))
	{
#		senderror( 49 );
	}
		$sql = "Update Accounts join Members using( AccountNo ) set VirginNo = '$virginno' where MemberNo = $memberno";
		DBQueryExitOnFailure( $sql );
}

?>