<?php
error_reporting( E_ALL );
/*
 * 
 * MRM 06 04 11 - Mantis 0003199: Retrospectively apply bonus awards 
 * The process to retrospectively apply the bonus awards will be as follows:

    Iterate through the 5 csv files containing the accounts to be checked
    (	Migration Test File 1 - 50 points on 4th swipe.csv 
		Migration Test File 2 - 100 points on 4th swipe.csv
		Redemption Test File 2 - 100 points on nextswipe.csv
		Redemption Test File 1 - 50 points on next swipe.csv
		Migration Test File 3 - Double points on 4th swipe.csv  )
		
		Read the csv file
		For each account
			Check if a bonus has already been awarded
			If not, check if the account is eligible for a bonus
			If so, award the bonus
		Echo summary data 
	Echo completion data
 * ********************************************************************
 */
$db_user = "root";
$db_pass = "Trave1";

include "../../include/DB.inc";

function FileList()
{
	return array(
		"Migration1" => "Migration Test File 1 - 50 points on 4th swipe.csv",
		"Migration2" =>	"Migration Test File 2 - 100 points on 4th swipe.csv", 
		"Migration3" =>	"Migration Test File 3 - Double points on 4th swipe.csv", 
		"Redmption1" => "Redemption Test File 1 - 50 points on next swipe.csv",
		"Redmption2" => "Redemption Test File 2 - 100 points on next swipe.csv"
		 );
}

function createBonusRecord ( $bonushit, $month, $transno, $points, $promocode )
{
	$sql = "SELECT Max( SequenceNo ) AS Seq FROM $bonushit WHERE TransactionNo =$transno"; 
	$sequenceno = DBSingleStatQueryNoError( $sql ) + 1;
	$sql = "Insert into $bonushit ( Month, TransactionNo, SequenceNo, PromotionCode, Points ) values ( $month, $transno, $sequenceno, '$promocode', $points )";
	$results = DBQueryExitOnFailure( $sql );
}

function UpdateTransactionRecord ( $transno, $points, $table )
{
	$sql = "Update $table set PointsAwarded = PointsAwarded + $points where TransactionNo = $transno";
	$results = DBQueryExitOnFailure( $sql );
}

function AdjustBalance ( $accountno, $points )
{
	$sql = "Update Accounts set Balance = Balance + $points where AccountNo = $accountno";
	$results = DBQueryExitOnFailure( $sql );
}

function ProcessFile ( $filename, $promocode )
{
	if (($handle = fopen("$filename", "r")) !== FALSE) 
	{
		$accountsprocessed     = 0;
		$bonusawards	       = 0;
		$alreadyawarded	       = 0;
		$rowsread              = 0;
		$bonushit              = 'BonusHit201104';
		$month                 = '201104';
		$table                 = 'Transactions201104';  
		
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
    	{
	        $rowsread++;
	        if( $rowsread != 1) // skip the header
	        {
	        	$accountsprocessed++;
				$Accountno    = $data[0];
	        	
//	        	Check if a bonus has already been awarded
	        	$sql = "SELECT PromotionCode FROM $table  JOIN $bonushit  USING ( TransactionNo ) WHERE AccountNo = $Accountno AND PromotionCode = '$promocode'";    	
				$results = DBQueryLogOnFailure( $sql );
				
				if ( mysql_num_rows($results) == 0 )	 
				{
//					If not, check if the account is eligible for a bonus
					$promotype = substr($promocode, 0, -1);
					if ($promotype == "Migration" ) { $limit = "3, 1"; }
					if ($promotype == "Redmption" ) { $limit = "1"; }
					$sql = "SELECT TransactionNo, PointsAwarded FROM Transactions201104 WHERE AccountNo = $Accountno LIMIT $limit";
					$results2 = DBQueryLogOnFailure( $sql );
					if ( mysql_num_rows($results2) == 1 )
					{
//						If so, award the bonus
						while ($row = mysql_fetch_assoc($results2))
						{
						   $transno = $row["TransactionNo"];
							switch ($promocode)
							{
								case "Migration1":
						        case "Redmption1":
								$points  = 50;
						        	break;
						
						        case "Migration2":
						        case "Redmption2":
								$points  = 100;
						        	break;
						
						        case "Migration3":
								$points  = $row["PointsAwarded"];
						        	break;
						
						        default:	
						        	echo "Unknown Promotion Code\r\n";
								$points  = 0;
						        	break;			
						    }
						}
						
 						createBonusRecord ( $bonushit, $month, $transno, $points, $promocode );
						UpdateTransactionRecord ( $transno, $points, $table );
						AdjustBalance ( $Accountno, $points );

						$bonusawards++;
					}
				}
				else 
				{
					$alreadyawarded++;
				}
	        }
    		if( ($rowsread % 100) == 0 )
			{
				echo date("Y-m-d H:i:s")." Processed $rowsread, Updated $bonusawards\r\n";
			}
   		 }
	    fclose($handle);
	    if ( $alreadyawarded != 0)
	    {
	    	$alreadyawardedmessage = " $alreadyawarded accounts already awarded bonus, "; 	
	    }
	    else 
	    {
	    	$alreadyawardedmessage = " "; 
	    }
	    
	    echo date("Y-m-d H:i:s").' '.$filename." processed. $accountsprocessed accounts processed,".$alreadyawardedmessage."$bonusawards accounts awarded bonus points.\r\n";
	}
	else 
	{
		echo date("Y-m-d H:i:s")." ************* $filename not found !!!! \r\n";
	}
}	

//- - - - - - - - - - - - -   M A I N   F U N C T I O N   - - - - - - - - - - - - - 

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
connectToDB( MasterServer, TexacoDB );

foreach(FileList() as $promocode => $filename)
	{
		$promoname = substr($filename, 0, -4);
		echo date("Y-m-d H:i:s")." - - - - - - - - - - - - - - - - - - - - - - - - - - - -\r\n";
		echo date("Y-m-d H:i:s")." $promoname started \r\n";
		ProcessFile ( $filename, $promocode );
	}

echo date("Y-m-d H:i:s").' '.__FILE__." completed. \r\n";

//- - - - - - - - - - - - -   M A I N   F U N C T I O N   E N D  - - - - - - - - - - - - - 
?>