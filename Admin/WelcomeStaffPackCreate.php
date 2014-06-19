<?php

	include "../include/Session.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../include/CSVFile.php";

	$timestamp = GetSQLTime();
	$friendly = GetBatchFilenameDateOnly( $timestamp );

	$sql = "SELECT Max(RunAt) AS LastTime FROM OutputFiles WHERE Type = 'StaffRegistrations'";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	
	$file = "StaffRegistrations". $friendly .".csv";
	
	//$sql = "select RequestNo As RefNo, RequestCode As RequestType, Title, Forename As FirstName, Surname, Organisation, Address1, Address2, Address3, Address4, Address5, PostCode from Members Join CardRequests using (MemberNo) where BatchTime='$timestamp' and Status = 'O'";
	$sql = "SELECT M.StaffID, M.MemberNo, A.HomeSite, M.Title, M.Initials, M.Forename, M.Surname, M.PrimaryCard, M.Organisation,";
	$sql .=" IF(M.Address1 IS NULL, S.SiteName, M.Address1) AS Address1,";
	$sql .=" IF(M.Address1 IS NULL, S.Address1, M.Address2) AS Address2,";
	$sql .=" IF(M.Address1 IS NULL, S.Address2, M.Address3) AS Address3,";
	$sql .=" IF(M.Address1 IS NULL, S.Address3, M.Address4) AS Address4,";
	$sql .=" IF(M.Address1 IS NULL, S.Address4, M.Address5) AS Address5,";
	$sql .=" IF(M.Address1 IS NULL, S.PostCode, M.Postcode) AS Postcode,";
	$sql .=" A.AccountNo, A.CreationDate, A.CreatedBy FROM texaco.Members AS M";
	$sql .=" JOIN texaco.Accounts AS A";
	$sql .=" USING ( AccountNo ) JOIN texaco.sitedata AS S"; 
	$sql .=" WHERE (A.HomeSite = S.SiteCode) AND  (A.AccountType = 'D') AND A.CreationDate > '2009-04-11 00:00:00' AND A.CreationDate >'".$row[0]."'";
	$results = DBQueryExitOnFailure( $sql );
	$numrows = mysql_num_rows($results);
	OutputCSV( $file, $results );
	$sql = "INSERT INTO OutputFiles (Type , RunAt, FileName, NoRecords) VALUES ('StaffRegistrations', NOW( ) , '$file', '$numrows')";
	$results = DBQueryExitOnFailure( $sql );
	
?>