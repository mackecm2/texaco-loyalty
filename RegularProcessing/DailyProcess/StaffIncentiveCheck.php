	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**   Staff Incentive Check - Report on invalid Staff Incentive memberships
	**   Written by MRM 06/04/2008
	********************************************************************/



	//******************************************************************
	//
	// RegularProcessing/StaffIncentiveCheck.php
	// 
	//
	//******************************************************************

	$db_user = 'pma001';
	$db_pass = 'amping';

	include "../../include/DB.inc";
#	require("../../mailsender/class.phpmailer.php");
#	include "../../include/BonusFunctions.inc";
	echo "\r\n\r\n";
	echo date("Y-m-d H:i:s").' '.__FILE__." started\r\n\r\n";

	
	// Main function

	echo "\r\n\r\n";
	echo "Accounts containing both Star Rewards and Staff Incentive Members\r\n";
	echo "=================================================================\r\n\r\n";
	connectToDB( MasterServer, TexacoDB );
	
	$today = date("Y-m-d");
	$sql = "SELECT AccountNo, COUNT(*) AS NumberOfMembers FROM Members JOIN Accounts USING (AccountNo) 
	WHERE Accounts.AccountType = 'D' GROUP BY AccountNo ORDER BY COUNT( * ) DESC";
		
	$results = DBQueryExitOnFailure( $sql );
	$count = 0;
	while( $row = mysql_fetch_array( $results )  )
	{
		
		if ($row["NumberOfMembers"] > 1)
	        {
			echo "Account number ".$row["AccountNo"]." has ".$row["NumberOfMembers"]." members\r\n\r\n";
			$count = $count + 1;
			$sql1 = "SELECT MemberNo, AccountNo, PrimaryMember, PrimaryCard FROM Members WHERE AccountNo = ".$row["AccountNo"];
			$results1 = DBQueryExitOnFailure( $sql1 );
			echo "MemberNo   AccountNo  PrimaryMember   PrimaryCard\r\n";
			echo "-----------------------------------------------------\r\n";
			while( $row1 = mysql_fetch_array( $results1 )  )
				{
					echo $row1["MemberNo"]."     ";
					echo $row1["AccountNo"] ."   ";
					echo $row1["PrimaryMember"] ."           ";
					echo $row1["PrimaryCard"]; 
					echo "\r\n"; 
				}	
	        }
        else 
       		{
	        break;
	   		}
		
	}
	echo "\r\n Accounts containing both Star Rewards and Staff Incentive Members - count is $count \r\n\r\n";
	
	//-------------------------------------- N E X T   R E P O R T ---------------------------------------------//
	
	echo "Staff Cards that are not in Staff Accounts\r\n";
	echo "==========================================\r\n\r\n";
	
	$sql = "SELECT C.CardNo, C.MemberNo, M.AccountNo, A.AccountType FROM Cards AS C 
			JOIN Members AS M USING ( MemberNo ) 
			JOIN Accounts AS A USING ( AccountNo ) 
			WHERE C.CardNo LIKE ( '01%' ) 
			AND ( A.AccountType <> 'D' OR A.AccountType IS NULL )";
				
	$results = DBQueryExitOnFailure( $sql );
	$num_rows = mysql_num_rows($results);
	if ( $num_rows > 0 )
	{
		echo "MemberNo  AccountNo  Account Type  PrimaryCard\r\n";
		echo "-------------------------------------------------------\r\n";
	}
	$count = 0;

	while( $row = mysql_fetch_array( $results )  )
	{
			$count = $count + 1;
			echo $row["MemberNo"]."   ";
			echo $row["AccountNo"] ."      ";
			if ($row["AccountType"] )
			{
				echo $row["AccountType"] ."            ";
			}
			else 
			{
				echo "NULL         ";
			}
			echo $row["CardNo"]; 
			echo "\r\n"; 
		
	}
	echo "\r\n Staff Cards that are not in Staff Accounts - count is $count \r\n\r\n";
	
	echo date("Y-m-d H:i:s").' '.__FILE__." completed\r\n\r\n";
?>