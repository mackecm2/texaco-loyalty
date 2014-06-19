<?php
error_reporting( E_ALL ^ E_NOTICE);

	# add the functions that well need throughout the site by using
	# an include file.

	require '../include/DB.inc';
	require 'interfacesql.php';
	require 'commands1000.php';
	require 'commands2000.php';

	global 	$db_user, $db_pass;

	$db_user = "WEOU";
	$db_pass = "WEOUpass";

#	$db_user = "steve";
#	$db_pass = "start";

	connectToDB();



	if( isset( $_POST['merchantid'] ))
	{
		$merchantid = $_POST['merchantid'];
	}
	else
	{
			header("Content-Type: text/xml");
			echo "<response>
<merchantid></merchantid>
<responsecode>-2</responsecode>
<responsetext>merchantid not set</responsetext>
</response>
			 	";

			exit() ;
	}

	# This page is called by the remote server, first we need to variablise the request data
	if( isset($_POST['requestcode']) )
	{
		$requestcode = $_POST['requestcode'];
	}
	else
	{
	echo "<response>
<merchantid>$merchantid</merchantid>
<responsecode>-1</responsecode>
<responsetext>requestcode not set</responsetext>
</response>

		 ";
		 exit;
	}


//	$merchanttxno = $_POST['merchanttxno'];
//	$account = $_POST['account'];
//	$amount = $_POST['amount'];

	$date = date("Y-m-d") ;
	$time = date("H:i:s") ;
	$datetime = date("Y-m-d H:i:s") ;



	#	Regardless of the command type we need to validate the merchant id


	$result = validatemerchant($merchantid);

	if (!($row = mysql_fetch_array($result)))
	{

			header("Content-Type: text/xml");
			echo "<response>
<merchantid>$merchantid</merchantid>
<responsecode>-2</responsecode>
<responsetext>Unknown MerchantID</responsetext>
</response>

			 	";

			exit() ;

	}

	#	Next thing is to check the msgref to see if we have seen this message before
	#   If so - we simply call the original response out of the msgref table



	if(isset( $_POST['msgref']))
	{

		$msgref = $_POST['msgref'];

		$result= checkmsgref($merchantid, $msgref);

		if ($row = mysql_fetch_array($result))
		{

			#	We have a duplicate msgref for this merchant - send original response.

			$response = $row['Response'];
			header("Content-Type: text/xml");
			echo "$response";
			exit();

		}
	}
	else
	{
		$msgref = "";
	}



#	Now lets find out what function we have to complete

switch($requestcode) {



case '1002':

	# echo "We're in case 1002";

	#	Authorisation request.

	command1002();


break; // END case '1002'



case '1003':


	# echo "We're in case 1003";

    #	Confirm request.

	command1003();

break; // END case '1003'


case '1004':

	# echo "We're in case 1004";

    #	Cancel request.

	command1004();

break; // END case '1004'




case '1005':

	# 	echo "We're in case 1005";

	#	Balance Enquiry

	command1005();

	break; // END case '1005'



case '1006':

	# 	echo "We're in case 1006";

	#	Account Recharge

	command1006();

	break; // END case '1006'


case '1007':

	# 	echo "We're in case 1007";

	#	New Account

	command1007();

	break; // END case '1007'










case '1008':

	# echo "We're in case 1008";

    #	Authorise and Confirm request.

	command1008();


break; // END case '1008'



case '2001':

	# echo "We're in case 2001";

    #	Member Logon.

	command2001();


break; // END case '2001'


case '2002':

	# echo "We're in case 2002";

    #	Member Logon.

	command2002();


break; // END case '2002'




case '2003':

	# echo "We're in case 2003";

    #	Member Logon.

	command2003();


break; // END case '2003'


case '2004':

	# echo "We're in case 2004";

    #	Member Logon.

	command2004();


break; // END case '2004'


case '2005':

	# echo "We're in case 2005";

    #	Member Logon.

	command2005();


break; // END case '2005'


case '2006':

	# echo "We're in case 2006";

    #	Member Logon.

	command2006();


break; // END case '2006'



case '2007':

	# echo "We're in case 2007";

    #	Get Transaction History.

	command2007();


break; // END case '2007'


case '2008':

	# echo "We're in case 2008";

    #	Get redemption history.

	command2008();


break; // END case '2008'



case '2009':

	# echo "We're in case 2003";

    #	Register new Member.

	command2009();


break; // END case '2009'

case '2010':

	# echo "We're in case 2010";

    #	Assign card to a member.

	command2010();


break; // END case '2010'


case '2012':

	# echo "We're in case 2012";

    #	Update Member Record.

	command2012();


break; // END case '2012'


	# There was an error of some kind
	default:

	#  If we are in here then there is an invalid or missing RequestCode

	echo "<response>
<merchantid>$merchantid</merchantid>
<responsecode>-1</responsecode>
<responsetext>Invalid, Unknown or empty requestcode</responsetext>
</response>

		 ";

	break; // END case 'default'



} // END switch($requestcode)




?>



