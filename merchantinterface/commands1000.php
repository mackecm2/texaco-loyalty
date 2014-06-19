<?php
error_reporting( E_ALL ^ E_NOTICE);
#  This script contains all of the commands that are 1XXX

# ALter table Orders add column Title varchar(10) after AccountNo, add column Forename varchar(40) after Title;

function command1001()
{

	#	Heartbeat Command

	$merchantid = $_GET['merchantid'];
	$datetime = date("Y-m-d H:i:s") ;
	$command = $_GET['requestcode'];
	$msgref = $_GET['msgref'];

	$result = heartbeat();

	if($result)
	{
		sendsimpleresponse( "System Online", "");
	}
	else
	{
// 			senderror( 1);

		$response =  "<response>
<merchantid>$merchantid</merchantid>
<responsecode>-1</responsecode>
<responsetext>System Offline</responsetext>
</response>";

		sendresponse($merchantid, $msgref, $command, $response,$datetime);
	}

	exit();
}

function command1002()
{
	$merchantid = $_GET['merchantid'];
	$merchanttxno = $_GET['merchanttxno'];
	$account = $_GET['account'];
	$amount = $_GET['amount'];

	# Now we need to look up the pin code.

 	$result = getaccount($account)  ;

 	if (!($row = mysql_fetch_array($result)))
	{

			#  Must be an invalid account - advise client
				senderror( 18);
 	}

	# echo "<br>Account OK";

	# Merchant and Account number is valid so now we need to create an Auth Code

	#  First - check the account balance can support the auth amount

	$balance = $row["Balance"];
	$shadowtotal = $row["TotalShadow"];

	if(($balance - $shadowtotal) < $amount )
	{
		#  Not enough to support the request - advise client
  		senderror( 19);
	}

	#  Account has sufficient credit so lets get an authcode

	$authcode = getauthcode ( $merchantid ,$account,$amount,$merchanttxno);

	# Now we have the Auth Code we need to update the Account Record

	# increment the shadowtotal

	updateaccountshadow($account,$amount);

	#  Now its time to tell the client

	$response =  "<authcode>$authcode</authcode><balance>$balance</balance>";

	sendsimpleresponse( "The Account/Pin has been Authorised", $response);

}


function command1003()
{
	$datetime = date("Y-m-d H:i:s") ;
	$merchantid = $_GET['merchantid'];
	$merchanttxno = $_GET['merchanttxno'];
	$account = $_GET['account'];
	$membernumber = $_GET['membernumber'];
	$amount = $_GET['amount'];

	# Now we need to look up the Account.

// 	$result = getaccount($account)  ;
 	$result = getaccountwithmember($account, $membernumber)  ;

 	if (!($row = mysql_fetch_array($result)))
	{

			#  Must be an invalid account - advise client
		senderror( 18);
 	}

	#	The account is ok, but first we need to see if the account is on RedemptionStop

	$balance = $row['Balance'];

	$stop = $row['RedemptionStopDate'];

	# echo "stop is $stop";

	if(($row['RedemptionStopDate'] > 0))
	{
		#  Must be an invalid account - advise client
 		senderror( 45);
 	}



	# Merchant and Account number is valid so now we need to check the Auth Code

	$authcodercv = $_GET['authcode'];

	# echo "Got authcode $authcodercv";

	if(!isset($authcodercv))
	{
		#  No - authcode
 		senderror( 25);
	}

	# echo "<br>Authcode received is $authcodercv ";

	$result = validateauthcode($merchantid , $account, $authcodercv );

	if (!($row = mysql_fetch_array($result)))
	{
		senderror( 26);
	}

	if ($row['AuthStatus'] == 'CONFIRMED')
	{
		senderror( 31);
	}

	#   Did the client specify the correct Transaction Number ?

	if ($merchanttxno != $row['MerchantTxNo'])
	{
		senderror( 28);
	}


	#   If the client has sent in a different amount for us to use then
	#	we need to use this amount not the original auth amount.  For shadow purposes however
	#	we still need to decrement the original auth amount from the shadow

	if(!isset($amount))
	{
		$amount = $row["Amount"];
	}

	$originalauthamount = $row["Amount"];

	#	Check that the request is not for a greater amount than the original auth.

	if($amount > $originalauthamount)
	{
		senderror( 22);
	}


	# Now set the authcode as Confirmed

	updateauthcode($authcodercv, "CONFIRMED");

	#  Now decrement the balance and Shadowtotals

	$result = getaccount($account)  ;

	if ($row = mysql_fetch_array($result))
	{

		# Now update the Account record

		$result = updateaccount($account, $amount, $originalauthamount);
   	}


	# Next we need to create an order record

	if( isset( $_GET['name'] ) )
	{
		$title		= "";
		$forename  = "";
		$name 		= $_GET['name'];
	}
	else
	{
		$title		= $_GET['title'];
		$forename  = $_GET['forename'];
		$name	= $_GET['surname'];
	}
	$address1 	= $_GET['address1'];
	$address2 	= $_GET['address2'];
	$address3 	= $_GET['address3'];
	$address4 	= $_GET['address4'];
	$address5 	= $_GET['address5'];
	$postcode 	= $_GET['postcode'];
	
	
	$userid		= $_GET['userid'];
	if($userid == '')
	{
		// If no userid then this is a web order
		
		$userid = 'WEB';
	
	}

	# echo "user is $userid<br>";

	$orderno = createorder($account, $membernumber, $title, $forename, $name, $address1,$address2,$address3,$address4,$address5,$postcode,$datetime, $userid, $balance);

	#  We need to write away the product lines that have been purchased

	extractproducts( $orderno, $merchantid, $merchanttxno );


	#  Now its time to tell the client

	$response = "<authcode>$authcodercv</authcode><balance>$newbalance</balance>";
	sendsimpleresponse( "The Account/Pin has been Confirmed", $response);

}




function command1004()
{

	$merchanttxno = $_GET['merchanttxno'];
	$account = $_GET['account'];
	$amount = $_GET['amount'];

	# Now we need to look up the Account.

 	$result = getaccount($account)  ;

 	if (!($row = mysql_fetch_array($result)))
	{
		#  Must be an invalid account - advise client
		senderror( 18);
 	}


	# echo "<br>Account OK";

	# Account number is valid so now we need to check the Auth Code

	$authcodercv = $_GET['authcode'];

	# echo "Got authcode $authcodercv";

	if(!isset($authcodercv))
	{
		#  No - authcode
		senderror( 25);
	}

	# echo "<br>Authcode received is $authcodercv ";

	$result = validateauthcode($merchantid , $account, $authcodercv );

	if (!($row = mysql_fetch_array($result)))
	{
		senderror( 26);
	}

	$amount = $row["Amount"];

	# echo "Authcode OK";

	if($row['AuthStatus'] == 'CANCELLED')
	{
		senderror( 32);
	}

	if ($row['AuthStatus'] == 'CONFIRMED')
	{
 		senderror( 31);
	}

	# Now update the authcode record as cancelled

	updateauthcode($authcodercv, "CANCELLED");

	#  Now decrement the Account Shadowtotal

 	$result = getaccount($account)  ;

	if ($row = mysql_fetch_array($result))
	{

		$result = updateaccount($account, "0", $amount );

   	}

	#  Now its time to tell the client

	sendsimpleresponse( "Authcode has been cancelled", "");
}




function command1005()
{

	$account = $_GET['account'];
	$amount = $_GET['amount'];
	$merchanttxno = $_GET['merchanttxno'];

    #	Account Enquiry.

	if(!isset($account))
	{
		#  No account supplied - return error
		senderror( 16);
	}

    $result = gettheaccountdetails($account);

	if (!$row = mysql_fetch_array($result))
	{

		# No Account found - advise client
		#  Must be an invalid account - advise client
		senderror( 18);
	}


	#  All checks completed - advise client of account details

	$AccountBalance = 	$row["Balance"];
	$ShadowTotal = 		$row["ShadowTotal"];

	#  Now its time to tell the client

	$response =  "<account>$row[AccountNo]</account>
<balance>$row[Balance]</balance>
<totalredemp>$row[TotalRedemp]</totalredemp>
<totalshadow>$row[TotalShadow]</totalshadow>
<firstredempdate>$row[FirstRedempDate]</firstredempdate>
<lastredempdate>$row[LastRedempDate]</lastredempdate>
<laststatement>$row[LastStatement]</laststatement>";

	sendsimpleresponse( "OK", $response);

}





function command1006()
{

	#	This command credits the account with a supplied value.

	$accountno = $_GET['account'];
	$membernumber = $_GET['member'];
	$amount = $_GET['amount'];

	# First we need to look up the account.



 	$result = getaccountwithmember($accountno, $membernumber);

 	if (!($row = mysql_fetch_array($result)))
	{

			#  Must be an invalid account - advise client
		senderror( 18);
 	}

	#	The account exists so lets simply increment the account with the credit

	# echo "<br>Account OK";

	AdjustBalance( MerchantInterfaceCredit, $membernumber, $accountno, "", $amount );

	#	Now read back the account balance to advise the client

	$result = getaccountwithmember($accountno, $membernumber);
	$row = mysql_fetch_array($result);


	$response =  "<balance>$row[Balance]</balance>";

	sendsimpleresponse( "The Account has been credited", $response);
}



function command1007()
{

	#	This command creates an account with the supplied balance.

	$balance = $_GET['balance'];

	# First we need to create the account.

	$accountno = createaccount($balance);

	#	Now advise the client


	$response =  "<account>$accountno</account>";

	sendsimpleresponse( "The Account has been created", $response);

}

function extractproducts( $orderno, $merchantid, $merchanttxno )
{

	for ($i=1; $i < 1000; $i++)
	{

		$prodcodesearch = "prodcode$i";
		$prodcode = $_GET["$prodcodesearch"];

		# echo " prodcode $prodcode";

		if(!isset($_GET["$prodcodesearch"]))
		{
			break;
		}

		$prodcostsearch = "prodcost$i";
		$prodcost = $_GET["$prodcostsearch"] ;

		$prodqtysearch = "prodqty$i";
		$prodqty = $_GET["$prodqtysearch"] ;
		$type = substr( $prodqty, 0, 1 );
		if( $type == "P" or $type == "M" or $type == "Q" )
		{
			$qty  = substr( $prodqty, 1 );
		}
		else if( $type >= "0" and $type <= "9" )
		{
			# tempary code to allow for change over.
			$qty = $prodqty;
			$type = " ";
			$prodcost = $prodcost * $prodqty;
		}
		else
		{
			echo "error";
		}

		# echo "<br> $prodcode $prodqty";

		$prodoptsearch = "prodopt$i";
		$prodopt = $_GET["$prodoptsearch"] ;


		$prodsuppliersearch = "prodsupplier$i";
		$prodsupplier = $_GET["$prodsuppliersearch"];

		$proddescsearch = "proddesc$i";
		$proddesc = $_GET["$proddescsearch"];

		$prodpersonalsearch = "prodpersonal$i";
		$prodpersonal = $_GET["$prodpersonalsearch"];

		$result = neworderline( $orderno, $prodcode, $merchantid, $merchanttxno, $prodopt, $type, $qty, $prodcost, $prodsupplier, $proddesc, $prodpersonal );

	}
}





function command1008()
{

	$datetime = date("Y-m-d H:i:s") ;
	$merchantid = $_GET['merchantid'];
	$merchanttxno = $_GET['merchanttxno'];
	$account = $_GET['account'];
	$membernumber = $_GET['membernumber'];
	$amount = $_GET['amount'];

	# Now we need to look up the Account.

 	$result = getaccountwithmember($account, $membernumber)  ;

 	if (!($row = mysql_fetch_array($result)))
	{
		#  Must be an invalid account - advise client
  		senderror( 18);
 	}


	if(($row['RedemptionStopDate'] > 0))
	{
			#  Must be an invalid account - advise client
			senderror( 45);
 	}



	# Merchant and Account number is valid so now we need to create an Auth Code

	#  First - check the account balance can support the transaction

	$balance = $row["Balance"];

	if(($balance - $amount) < 0 )
	{
		#  Not enough credit to support the request - advise client
		senderror( 19);
	}

	#  Account has sufficient credit so lets decrement the balance and process the order

	$result = getaccountwithmember($account, $membernumber)  ;

	if ($row = mysql_fetch_array($result))
	{

		$newbalance = ($balance - $amount);

		# Now update the Account record

		$result = updateaccount($account,$amount,"0");
   	}


	# Next we need to create an order record

	if( isset( $_GET['name'] ) )
	{
		$title		= "";
		$forename  = "";
		$name 		= $_GET['name'];
	}
	else
	{
		$title		= $_GET['title'];
		$forename  = $_GET['forename'];
		$name	= $_GET['surname'];
	}

	$address1 	= $_GET['address1'];
	$address2 	= $_GET['address2'];
	$address3 	= $_GET['address3'];
	$address4 	= $_GET['address4'];
	$address5 	= $_GET['address5'];
	$postcode 	= $_GET['postcode'];
	$userid		= $_GET['userid'];
	if($userid == '')
	{
		// If no userid then this is a web order
		
		$userid = 'WEB';
	
	}
	# echo "user is $userid<br>";

	$orderno = createorder($account,$membernumber, $title, $forename, $name, $address1,$address2,$address3,$address4,$address5,$postcode,$datetime, $userid, $balance);

	#  We need to write away the product lines that have been purchased

	extractproducts( $orderno, $merchantid, $merchanttxno );
	
	#  Has the customer used an alternate delivery address?

	if ( $address1 != $row[Address1] or $address2 != $row[Address2] or $address3 != $row[Address3] or 
	$address4 != $row[Address4] or $address5 != $row[Address5] or $postcode != $row[PostCode] )
	{
		$trackingcode = TrackingAltDeliveryAddress;
		$comments = "alt delivery address $address1,$address2,$address3,$address4,$address5,$postcode";
		$values = "$membernumber, $account, $trackingcode, '$comments', now(), '$userid'";
		
		$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy  ) 
				values ( $values )";

		DBQueryExitOnFailure( $sql );
		
//		
	}
	
	#  Now its time to tell the client

	$response = "<balance>$newbalance</balance>";
	sendsimpleresponse( "The Transaction has been Confirmed", $response);
}



?>