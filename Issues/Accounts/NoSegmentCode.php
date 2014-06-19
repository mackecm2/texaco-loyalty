<?php
error_reporting( E_ALL );

//**********************************************************************************************
//*                                                                                             *
//*           Program Name     :  NoSegmentCode.php                                             *
   //*           Path             :  /data/www/websites/texaco/Issues/Accounts                     *
   //*           Author / Date    :  MRM  / 25 JUN 2009                                            *   
   //*           Function         :  looks for accounts with no SegmentCode                        *
   //*                               goes through all cards in each account                        *
   //*                               finds the most active segment code                            *
   //*                               copies that code to the account                               *
   //                                                                                              *  
   //*           Revision History :                                                                *
   //*                                                                                             *
   //*                                                                                             *
   //*                                                                                             *
   //*                                                                                             *
   //*                                                                                             *
   //**********************************************************************************************
require "../../include/DB.inc";
require "../../include/Locations.php";

$db_name = "texaco";
$db_user = "HomeExport";
$db_pass = "FLOWER";

$ProcessName   = "NoSegmentCode";

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
   //**********************************************************************************************
   //*   This section updates accounts with the most up to date segment code from the              *
   //*   members' cards                                                                            *
   //**********************************************************************************************
$master = connectToDB( MasterServer, TexacoDB );
$sql = "SELECT Accounts.AccountNo, Cards.CardNo, Cards.LastSwipeDate, Cards.SegmentCode, 
		CASE 
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 45 DAY ) THEN 'A1'
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 3 MONTH ) THEN 'A2'
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 6 MONTH ) THEN 'L '
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 12 MONTH ) THEN 'D '
			ELSE 'XD'
		END AS Recency
		FROM Cards
		JOIN Members
		USING ( MemberNo ) 
		JOIN Accounts
		USING ( AccountNo ) 
		WHERE Accounts.Balance >0
		AND Accounts.SegmentCode IS NULL 
		AND Cards.SegmentCode IS NOT NULL 
		ORDER BY LastSwipeDate ASC";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
$count = 0;
while( $row = mysql_fetch_assoc( $res ) )
{
	$AccountNo = $row['AccountNo'];
	$SegmentCode = $row['SegmentCode'];
	$YourRecency = substr($SegmentCode,0,2);
	// We have a segment code, but is it the correct recency?
	$Recency = $row['Recency']; // this is the real recency
	if( $YourRecency != $Recency  )
	{
		$SegmentCode = substr_replace($SegmentCode,$Recency,0,2);
	}
		 
	$sql2 = "UPDATE Accounts SET SegmentCode = '".$SegmentCode."' WHERE AccountNo = $AccountNo";
	$res2 = mysql_query( $sql2, $master ) or die( mysql_error($master) );
	$count++;
}

echo date("Y-m-d H:i:s")." $count accounts updated with segment codes from cards\r\n";
   //**********************************************************************************************
   //*   This section looks at really old accounts with blank segment codes and updates            *
   //*   them with XDL L                                                                           *
   //**********************************************************************************************
$count = 0;
$sql = "SELECT AccountNo FROM Accounts
                JOIN Members
                USING ( AccountNo ) 
                JOIN Cards
                USING ( MemberNo ) 
                WHERE Accounts.SegmentCode IS NULL
                AND (LastSwipeDate IS NULL OR LastSwipeDate < date_sub( NOW(), INTERVAL 24 MONTH ))
				AND (LastRedempDate IS NULL OR LastRedempDate < date_sub( CURDATE(), INTERVAL 24 MONTH ))";

$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
$count = 0;
$SegmentCode = "XDL L";
while( $row = mysql_fetch_assoc( $res ) )
{
		$sql3 = "UPDATE Accounts SET SegmentCode = '".$SegmentCode."' WHERE AccountNo = ".$row['AccountNo'];
		$res3 = mysql_query( $sql3, $master ) or die( mysql_error($master) );
		$count++;
}

echo date("Y-m-d H:i:s")." $count dead accounts updated with XD segment codes\r\n";

   //**********************************************************************************************
   //*   This section looks at what's left and allocates segment codes depending on recent         *
   //*   card activity                                                                           *
   //**********************************************************************************************
$sql = "SELECT AccountNo, MemberNo, CardNo, LastSwipeDate, TotalSwipes, LastRedempDate,
		CASE 
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 1 MONTH ) THEN 'A1'
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 3 MONTH ) THEN 'A2'
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 6 MONTH ) THEN 'L '
			WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 12 MONTH ) THEN 'D '
            WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 24 MONTH ) THEN 'XD'
			WHEN LastRedempDate > date_sub( CURDATE() , INTERVAL 1 MONTH ) THEN 'A1'
			WHEN LastRedempDate > date_sub( CURDATE() , INTERVAL 3 MONTH ) THEN 'A2'
			WHEN LastRedempDate > date_sub( CURDATE() , INTERVAL 6 MONTH ) THEN 'L '
			WHEN LastRedempDate > date_sub( CURDATE() , INTERVAL 12 MONTH ) THEN 'D '
            WHEN LastRedempDate > date_sub( CURDATE() , INTERVAL 24 MONTH ) THEN 'XD'
			ELSE 'XD'
			END AS Recency
 		FROM Accounts JOIN Members USING ( AccountNo ) JOIN Cards USING ( MemberNo )WHERE Accounts.SegmentCode IS NULL 
 		ORDER BY LastSwipeDate ASC";

$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
$count = 0;
while( $row = mysql_fetch_assoc( $res ) )
{
		$Recency = $row['Recency'];

/*
 * OK we've got the Recency, let's work out the frequency and value
 */
	
    	$sql4 = "SELECT Count(CardNo) AS Frequency, SUM(TransValue) AS Value FROM Transactions WHERE CardNo = '".$row['CardNo']."' AND CreationDate <= '".$row['LastSwipeDate']."' AND CreationDate >= date_sub('".$row['LastSwipeDate']."', INTERVAL 1 MONTH ) GROUP BY CardNo";
		$res4 = mysql_query( $sql4, $master ) or die( mysql_error($master) );
		if ( mysql_num_rows($res4) > 0)
		{
			while( $row4 = mysql_fetch_assoc( $res4 ) )
			{
				switch ($row4['Frequency'])
				{
				  case "0":
				  case "1":
				  case "2":
				    $Frequency = 'L';
				    break;
				  case "3":
				  case "4":
				  case "5":	
				    $Frequency = 'M';
				    break;
				  default:
				    $Frequency = 'H';
				}
	
				if ( $row4['Value'] > 149.99 ) {
				     $Value = 'H ';
				} elseif ($row4['Value'] > 119.99) {
				     $Value = 'MH';
				} elseif ($row4['Value'] > 59.99) {
				    $Value = 'M ';
				} else $Value = 'L ';
				

			}
		}
		else /* customer hasn't swiped for ages so lets just look at how many times he's swiped for frequency */
		{
			$Value = 'L ';
			switch ($row['TotalSwipes'])
			{
			  case "0":
			  case "1":
			  case "2":
			    $Frequency = 'L';
			    break;
			  default:
			    $Frequency = 'M';
			}
		}
		
		$SegmentCode = $Recency.$Value.$Frequency;
		$sql5 = "UPDATE Accounts SET SegmentCode = '".$SegmentCode."' WHERE AccountNo = ".$row['AccountNo'];
		$res5 = mysql_query( $sql5, $master ) or die( mysql_error($master) );
		$count++;		

		
}
echo date("Y-m-d H:i:s")." $count live accounts updated with segment codes\r\n";

   //**********************************************************************************************
   //*   OK what about new unregistered cards?                                                   *
   //*                                                                                           *
   //**********************************************************************************************
$sql = "SELECT CardNo,
CASE
WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 1 MONTH ) THEN 'N1'
WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 3 MONTH ) THEN 'N2'
WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 6 MONTH ) THEN 'L '
WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 12 MONTH ) THEN 'D '
WHEN Cards.LastSwipeDate > date_sub( NOW( ) , INTERVAL 24 MONTH ) THEN 'XD'
ELSE 'XD'
END AS Recency
FROM Cards
WHERE MemberNo IS NULL
AND SegmentCode IS NULL";

$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
$count = 0;
while( $row = mysql_fetch_assoc( $res ) )
{
		$sql6 = "UPDATE Cards SET SegmentCode = '".$row['Recency']."' WHERE CardNo = '".$row['CardNo']."'";
		$res6 = mysql_query( $sql6, $master ) or die( mysql_error($master) );
		$count++;
}
echo date("Y-m-d H:i:s")." $count unregistered cards updated with segment codes\r\n";

   //**********************************************************************************************
   //*   Final step - (Mantis 1516) - add "L L" to blank frequency and value codes               *
   //*                                                                                           *
   //**********************************************************************************************

$sql = "UPDATE Cards SET SegmentCode = CONCAT( LEFT(SegmentCode,2), 'L L') WHERE SegmentCode IN ('N1','N2','L ','D ','XD','A1','A2')";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) ); 
$numrows = mysql_affected_rows();
echo date("Y-m-d H:i:s")." $numrows cards updated with L L frequency / value codes\r\n";

$sql = "UPDATE Accounts SET SegmentCode = CONCAT( LEFT(SegmentCode,2), 'L L') WHERE SegmentCode IN ('N1','N2','L ','D ','XD','A1','A2')";
$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
$numrows = mysql_affected_rows();
echo date("Y-m-d H:i:s")." $numrows accounts updated with L L frequency / value codes\r\n";

echo date("Y-m-d H:i:s").' '.__FILE__." completed.\r\n";
?>