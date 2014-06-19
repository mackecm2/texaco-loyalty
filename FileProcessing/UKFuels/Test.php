<?php


	include "../../include/DB.inc";
	$db_user = "UKFuelsProcess";
	$db_pass = "UKPassword";
		
		connectToDB( MasterServer, TexacoDB );
$line = "008834708352465510622220812210705041718420567205100050000001B7CFD     0612970";
		
	$cardNumber = substr( $line , 6 , 19 );

	echo "Card No = $cardNumber\n";

	$UKAccountNo = substr( $cardNumber, 6, 5 );
	
	echo "Account Np = $UKAccountNo\n";
	
	$sql = "Select AccountCards.CardNo, MemberNo from AccountCards left join Cards using( CardNo) where GAccountNo = $UKAccountNo limit 1";

	$results = DBQueryLogOnFailure( $sql );
		// Store for latter use
	if( $results )
	{
		if( mysql_num_rows( $results ) > 0 )
		{
			$row = mysql_fetch_assoc( $results );
			print_r( $row );
/*			if( $row["MemberNo"] != "" )
		{
				//  now see if the UKFuels number exists
				$sql = "select MemberNo from Cards where CardNo = '$cardNumber' ";
				$results = DBQueryLogOnFailure( $sql );
				if( $results )
				{
					if( mysql_num_rows( $results ) > 0 )
					{
						$row2 = mysql_fetch_assoc( $results );
						if( $row["MemberNo"] != $row2["MemberNo"] )
						{
							LogError( "Card $cardNumber moved from member $row[MemberNo] to $row2[MemberNo] " );
							$sql = "Update Cards set MemberNo = $row[MemberNo] where CardNo = '$cardNumber'";
							$results = DBQueryLogOnFailure( $sql );							
						}
					}
					else
					{
						$sql = "Insert into Cards ( CardNo, MemberNo, CreatedBy, CreationDate ) values ( '$cardNumber', $row[MemberNo], 'UKFuels', now() )";
						$results = DBQueryLogOnFailure( $sql );
					}
					return true;
				}
			}
			else
			{
				LogWarning( "Account card $UKAccountNo linked to unlinked card $row[CardNo]" );
			}
			*/
		}
	}
   ?>