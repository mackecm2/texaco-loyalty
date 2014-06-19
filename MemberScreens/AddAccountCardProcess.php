<?php

	#	This page is called by AddAccountCard.php and creates or update an account card.

	include "../include/Session.inc";
	include "../DBInterface/AccountCardsInterface.php";

	# echo "Need to add account card to account number $_GET[AccountNo] and UKF $_GET[UKFAccount]";

	include "../DBInterface/TrackingInterface.php";
	include "../include/DisplayFunctions.inc";

//		echo "New Account Card $_GET[newukfaccountno]<br>";
//		echo "Original Account Card $_GET[originalukfaccountno]<br>";
//		echo "weou Card $_GET[weoucardno]<br>";
//		echo "member no  $_GET[memberno]<br>";
//		echo "accountno $_GET[accountno]<br>";

		#	If they had a UKF account before we simply need to overwrite the original
		#	record with the new detail

		$newUKFAccountNo = $_GET['newukfaccountno'];
		$originalUKFAccountNo = $_GET['originalukfaccountno'];
		$cardno = $_GET['weoucardno'];

		$newUKFAccountNoA = explode( ",", $newUKFAccountNo   );  
		$originalUKFAccountNoA = explode( ",", $originalUKFAccountNo );
		
	//	print_r($newUKFAccountNoA);
	//	print_r($originalUKFAccountNoA);
		
		foreach( $newUKFAccountNoA as $newUKFAccountNo )
		{
			$key = array_search( $newUKFAccountNo, $originalUKFAccountNoA ); 
			if( $key === FALSE ) 
			{
				if( Trim( $newUKFAccountNo ) != "" )
				{
					if( UpdateUKFCard( $newUKFAccountNo, $cardno  ) )
					{
						InsertTrackingRecord( TrackingUKFAccountCard, $_GET['memberno'], $_GET['accountno'],  "New $newUKFAccountNo", 0 );
					}
				}
		//		echo "New $newUKFAccountNo";
			}
			else
			{
		//		echo "Not new $newUKFAccountNo";
				unset( $originalUKFAccountNoA[$key]);	
			}
		}
		foreach( $originalUKFAccountNoA as $originalUKFAccountNo)
		{
			if( Trim($originalUKFAccountNo) != "" )
			{
				if( RemoveUKFCard( $originalUKFAccountNo ) )
				{
						InsertTrackingRecord( TrackingUKFAccountCard, $_GET['memberno'], $_GET['accountno'],  "Removed $originalUKFAccountNo", 0 );
				}
			}
		//	echo "Remove  $originalUKFAccountNo";
		}

		#	And now we need to check the account type. we do this using the function CheckAccount

		$update = CheckAccount($_GET['accountno']);

		if($update)
		{
			InsertTrackingRecord( TrackingUKFAccountCard, $_GET['memberno'], $_GET['accountno'],  "AccountType set to Account Card", 0 );
		}


//	die();

	// End Transaction
	header("Location: ../MemberScreens/DisplayMember.php?AccountNo=$_GET[accountno]&MemberNo=$_GET[memberno]");
?>





