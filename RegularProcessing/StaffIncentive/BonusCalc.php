	<?php
error_reporting( E_ALL );

	/********************************************************************
	**
	**  Staff Incentive Scheme - Bonus Points Allocator 
	**   Written by MRM 19/08/2008
	********************************************************************/



	//******************************************************************
	//
	// /RegularProcessing/StaffIncentive/BonusCalc.php
	// Looks at the FilesProcessed table
	// Picks out the most recent run time of this script  (LastRunTime)
	// Writes a StartTime to the FilesProcessed table
	// Extracts all member registrations with a StaffID since the LastRunTime
	// Adds 25 points to the corresponding Staff Member's balance
	// If the add works, write a tracking record to that effect
	//
	// At end of process, writes an EndTime to the FilesProcessed table
	//
	//******************************************************************

	$db_user = "StaffProcess";
	$db_pass = "Staf7pr0ce55";

	include "../../include/DB.inc";

	function createFileProcessRecord($Process)
	{
		$sql = "Insert into FilesProcessed( StartTime, CreatedBy ) values ( now(), '$Process' )";
		$results = DBQueryExitOnFailure( $sql );

		return mysql_insert_id();
	}

	function UpdateRecordsProcessed( $recNum, $reccount )
	{
		$sql = "Update FilesProcessed set EndTime = now(), NewRecords = $reccount where ID = $recNum";
		$results = DBQueryExitOnFailure( $sql );
	}
	function AdjustStaffBalance( $StaffMemberNo, $AccountNo, $CustomerMemberNo, $Adjustment, $CreationDate )
	{
		$fields = "MemberNo, AccountNo, TrackingCode, CreatedBy, CreationDate, Notes";
		$values = "$StaffMemberNo, $AccountNo, 1188, 'Staff', now(), CONCAT('Bonus for Member ', $CustomerMemberNo,' registration on ', '".$CreationDate."')";
 
		if( $Adjustment != 0 )
		{
			$fields .= ",Stars";
			$values .= ", '$Adjustment'";

			if( $Adjustment > 0 )
			{
				$Adjustment = "+ " . $Adjustment;
	// Adds points to the corresponding Staff Member's balance
			$sql = "Update Accounts set Balance = Balance $Adjustment where AccountNo = $AccountNo";
			$results = DBQueryExitOnFailure( $sql );
	// If the add works, write a tracking record to that effect
			$sql = "Insert into Tracking( $fields ) values( $values )";
			$results = DBQueryExitOnFailure( $sql );
			}
		}
	}
	
	
	// Main function

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	connectToDB( MasterServer, TexacoDB );
	
	// Looks at the FilesProcessed table
	// Picks out the most recent run time of this script  (LastRunTime)
	
	$sql = "SELECT MAX(StartTime) FROM `FilesProcessed` WHERE CreatedBy = 'StaffIncentive'";
	$LastRunTime = DBSingleStatQueryNoError( $sql );
	
	// Extracts all member registrations with a StaffID since the LastRunTime	
	$sql = "SELECT M.StaffID, M.MemberNo, M.Title, M.Initials, M.Forename, M.Surname, A.AccountNo, M.CreationDate, A.CreatedBy ";
	$sql .= "FROM texaco.Members AS M JOIN texaco.Accounts AS A USING ( AccountNo ) ";
	$sql .= "WHERE (A.AccountType <> 'D' OR A.AccountType IS NULL) AND M.StaffID IS NOT NULL AND M.PrimaryCard NOT LIKE '01%'";
	
    if ($LastRunTime!=NULL AND $LastRunTime!="" )
    {
        $sql .= " AND M.CreationDate > '2009-05-25 00:00:00' AND M.CreationDate > '".$LastRunTime."'";
        $sql .= " AND M.CreationDate < '2009-07-20 00:00:00'";
		}
	
	$results = DBQueryExitOnFailure( $sql );
	
	// Writes a StartTime to the FilesProcessed table
	
	$process = 'StaffIncentive';
	$fileRec = createFileProcessRecord($process);
	$bonuscount = 0;
	$invalidbonuscount = 0;
	$unmatchedbonuscount = 0;
	while( $row = mysql_fetch_array( $results ) )
	{
		// Find the matching Staff Member
		$sql = "SELECT M.StaffID, M.MemberNo, A.AccountNo FROM texaco.Members AS M ";
		$sql .= "JOIN texaco.Accounts AS A ";
		$sql .= "USING ( AccountNo ) WHERE A.AccountType = 'D' AND M.StaffID = '" .$row['StaffID']."'";
		$staffdetails = DBQueryExitOnFailure( $sql );
	
		$staff = mysql_fetch_row( $staffdetails );
		if( $staff and $staff[0] != "")
		{
			// Check that this customer has not registered before
			$sql2 = "SELECT COUNT(CardNo) FROM Cards WHERE MemberNo = '".$row['MemberNo']."' GROUP BY MemberNo";
			$cardcount = DBQueryExitOnFailure( $sql2 );
			$cards = mysql_fetch_row( $cardcount );
			if ($cards[0] == 1)
			{                                                           // Mantis 842 Staff Incentive Scheme 2009 14/04/09			
			   	if ( $row['CreationDate'] < '2009-07-06 00:00:00' ) 
				{
					$Bonus = 25;
				}
				else
				{
					$Bonus = 50;
				}
                AdjustStaffBalance( $staff[1], $staff[2], $row['MemberNo'], $Bonus, $row['CreationDate'] );
                $bonuscount++;
                $valid = "Y";
            }
			
			else 
			{
				$valid = "N";
				$invalidbonuscount++;
			}
		}
		else 
		{
			$valid ="U";
			$unmatchedbonuscount++;
		}
		$sql3 = "Insert into CustomerRegistrations( MemberNo, AccountNo, CreationDate, CreatedBy, StaffID, Valid ) ";
		$sql3 .= "VALUES ( '".$row['MemberNo']."', '".$row['AccountNo']."', '".$row['CreationDate']."', '".$row['CreatedBy']."', '".$row['StaffID']."', '".$valid."')";
		$regresults = DBQueryExitOnFailure( $sql3 );
	}
	UpdateRecordsProcessed( $fileRec, $bonuscount );

	//$results = DBQueryExitOnFailure( $sql );

	echo date("Y-m-d H:i:s").' '.__FILE__." completed - bonus count is $bonuscount \r\n";
	echo " - invalid bonus count is $invalidbonuscount \r\n";
	echo " - unmatched bonus count is $unmatchedbonuscount \r\n";

?>