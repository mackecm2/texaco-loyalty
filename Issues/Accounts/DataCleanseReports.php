<?php

require "../../include/DB.inc";
require "../../DBInterface/FileProcessRecord.php";
require "../../DBInterface/WelcomePackInterface.php";
require "../../DBInterface/RedemptionInterface.php";
require "../../DBInterface/FraudInterface.php";
require "../../DBInterface/TrackingInterface.php";
require "../../mailsender/class.phpmailer.php";
$db_user = "ReportGenerator";
$db_pass = "tldttoths";

$uname = "HouseKeeper";

function MailingList()
{
	return array(
	'Sally Gibson' => 'sally.gibson@dawleys.com', 
	'Michelle Cooper' => 'michelle.cooper@dawleys.com', 
	'John Aldred' => 'john.aldred@dawleys.com', 
	'Jason Wolff' => 'jason.wolff@valero.com', 
	'Peter Seymour' => 'pseymour@rsm2000.co.uk');
}
//- - - - - -   M E M B E R   A D D R E S S   D A T A   C L E A N S E  - - - - - - 
//
function CleanUpAddressLines($c)
{
	$b = $c - 1;
	$sql = "UPDATE Members SET Address$b = Address$c, Address$c = NULL WHERE (Address$b IS NULL OR Address$b = '') AND Address$c IS NOT NULL";
	$results = DBQueryLogOnFailure( $sql );
 	$num_rows = mysql_affected_rows();
 	echo date("Y-m-d H:i:s")." Moving Address Line $c to Line $b - $num_rows rows...\r\n"; 
	return;
}

function CleanUpAddressLinesLoop()
{
	echo date("Y-m-d H:i:s")." Cleaning up Member Address Lines...\r\n"; 

	for ($j=1; $j<=4; $j++)
  	{
  		for ($i=5; $i>1; $i--)
  		{
  			CleanUpAddressLines($i);
  		}
  	}

	return;
}
// - - - - - - - - C R E A T E   W O R K I N G   T A B L E S - - - - - - - - -
//   We will need two temporary tables (Arvato_Members and Arvato_SecondaryMembers)  to be able to generate these reports
	
function CreateWorkingTables()
{
	echo date("Y-m-d H:i:s")." Creating Working Tables ...\r\n";
	$sql = "DROP TABLE IF EXISTS Arvato_SecondaryMembers";
	$results = DBQueryLogOnFailure( $sql );
	echo date("Y-m-d H:i:s")." creating Arvato_SecondaryMembers\r\n"; 		
	$sql = "CREATE TABLE Arvato_SecondaryMembers SELECT 
				AccountNo, MemberNo,  
				case 
				when ( Title = '' OR Title = 'DATAERROR' OR Title IS NULL ) then NULL
				else Title
				end as Title,
				case 
				when ( Forename = '' OR Forename = 'DATAERROR' OR Forename IS NULL ) then NULL
				else Forename
				end as Forename,
				case 
				when ( Surname = '' OR Surname = 'DATAERROR' OR Surname IS NULL ) then NULL
				else Surname
				end as Surname,
				case 
				when ( HomePhone = '' OR HomePhone = 'DATAERROR' OR HomePhone IS NULL ) then NULL
				else HomePhone
				end as HomePhone,case 
				when ( WorkPhone = '' OR WorkPhone = 'DATAERROR' OR WorkPhone IS NULL ) then NULL
				else WorkPhone
				end as WorkPhone,
				case 
				when ( Address1 = '' OR Address1 = 'DATAERROR' OR Address1 IS NULL ) then NULL
				else Address1
				end as Address1,
				case 
				when ( Address2 = '' OR Address2 = 'DATAERROR' OR Address2 IS NULL ) then NULL
				else Address2
				end as Address2,
				case 
				when ( Address3 = '' OR Address3 = 'DATAERROR' OR Address3 IS NULL ) then NULL
				else Address3
				end as Address3,
				case 
				when ( Address4 = '' OR Address4 = 'DATAERROR' OR Address4 IS NULL ) then NULL
				else Address4
				end as Address4,
				case 
				when ( Address5 = '' OR Address5 = 'DATAERROR' OR Address5 IS NULL ) then NULL
				else Address5
				end as Address5,
				case 
				when ( PostCode = '' OR PostCode = 'AA1 1AA' OR PostCode IS NULL ) then NULL
				else PostCode
				end as PostCode, Email, Passwrd, DOB, PrimaryCard 
				FROM Members JOIN AccountStatus USING ( AccountNo ) JOIN Accounts USING ( AccountNo )
				WHERE PrimaryMember = 'N' AND AccountType <> 'G' 
				AND ((Status = 'Closed' AND FraudStatus = '4') OR Status = 'Open')";
	$results = DBQueryLogOnFailure( $sql );
	$num_rows = mysql_affected_rows();
	
	$sql = "ALTER TABLE Arvato_SecondaryMembers ADD PRIMARY KEY ( MemberNo ) ";
	$results = DBQueryLogOnFailure( $sql );
	
	$sql = "ALTER TABLE Arvato_SecondaryMembers ADD INDEX ( AccountNo ) ";
	$results = DBQueryLogOnFailure( $sql );
	


	echo date("Y-m-d H:i:s")." Arvato_SecondaryMembers created - $num_rows rows...\r\n";
	echo date("Y-m-d H:i:s")."  - creating Arvato_Members\r\n";
	$sql = "DROP TABLE IF EXISTS Arvato_Members";
	$results = DBQueryLogOnFailure( $sql );
	$sql = "CREATE TABLE Arvato_Members SELECT Members.* FROM Members JOIN AccountStatus USING ( AccountNo ) JOIN Accounts USING ( AccountNo )
	 WHERE PrimaryMember = 'Y' AND AccountType <> 'G' AND ((Status = 'Closed' AND FraudStatus = '4') OR Status = 'Open')";
	$results = DBQueryLogOnFailure( $sql );
	$num_rows = mysql_affected_rows();
	
	$sql = "ALTER TABLE Arvato_Members ADD PRIMARY KEY ( MemberNo )";
	$results = DBQueryLogOnFailure( $sql );
	$sql = "ALTER TABLE Arvato_Members ADD INDEX ( AccountNo ) ";
	$results = DBQueryLogOnFailure( $sql );
	
	echo date("Y-m-d H:i:s")." Arvato_Members created - $num_rows rows...\r\n";
	return;
	
}
// - - - - - - - - Primary members with missing email and password - - - - - - - - -
function MissingEmailPassword($filepath,$filename)
{
	$fp = fopen($filepath.$filename, "w");

	$sql = "SELECT AccountNo, M.MemberNo AS PrimaryMemberNo, 
			M.Title AS PrimaryTitle, M.Forename AS PrimaryForename, M.Surname AS PrimarySurname, 
			S.MemberNo AS SecondaryMemberNo, 
			S.Title AS SecondaryTitle, S.Forename AS SecondaryForename, S.Surname AS SecondarySurname, 
			IFNULL(M.Email,'') AS PrimaryEmail, 
			IFNULL(M.Passwrd,'') AS PrimaryPassword,
			IFNULL(S.Email,'') AS SecondaryEmail, 
			IFNULL(S.Passwrd,'') AS SecondaryPassword 
			FROM Arvato_Members AS M JOIN Arvato_SecondaryMembers AS S
			USING ( AccountNo )
			WHERE (M.Email IS NULL 
			OR M.Passwrd IS NULL ) AND (S.Email IS NOT NULL 
			OR S.Passwrd IS NOT NULL )";
	$results = DBQueryLogOnFailure( $sql );
 	$num_rows = mysql_affected_rows();
 	echo date("Y-m-d H:i:s")." Primary members with missing email and password - $num_rows rows...\r\n"; 


// fetch a row and write the column names out to the file
	$row = mysql_fetch_assoc($results);
	$line = "";
	$comma = "";
	foreach($row as $name => $value) {
	    $line .= $comma . '"' . str_replace('"', '""', $name) . '"';
	    $comma = ",";
	}
	$line .= "\n";
	fputs($fp, $line);

// remove the result pointer back to the start
	mysql_data_seek($results, 0);

// and loop through the actual data
	while($row = mysql_fetch_assoc($results)) {
	   
	    $line = "";
	    $comma = "";
	    foreach($row as $value) {
	        $line .= $comma . '"' . str_replace('"', '""', $value) . '"';
	        $comma = ",";
	    }
	    $line .= "\n";
	    fputs($fp, $line);
	   
	}
	
	fclose($fp);
	return $num_rows;
}

// - - - - - - - - Primary members with missing mandatory address details - - - - - - - - -
function MissingAddressDetails($filepath,$filename)
{
	$fp = fopen($filepath.$filename, "w");

	$sql = "SELECT AccountNo, M.MemberNo AS PrimaryMemberNo,
			S.MemberNo AS SecondaryMemberNo, 
			IFNULL(M.Address1,'') as PrimaryAddressLine1, 
			IFNULL(M.Address2,'') as PrimaryAddressLine2,
			IFNULL(M.Address3,'') as PrimaryAddressLine3, 
			IFNULL(M.Address4,'') as PrimaryAddressLine4,
			IFNULL(M.Address5,'') as PrimaryAddressLine5,
			IFNULL(M.PostCode,'') as PrimaryPostCode, 
			IFNULL(S.Address1,'') as SecondaryAddressLine1, 
			IFNULL(S.Address2,'') as SecondaryAddressLine2, 
			IFNULL(S.PostCode,'') as SecondaryPostCode  		
			FROM Arvato_Members AS M
			JOIN Arvato_SecondaryMembers AS S USING ( AccountNo )
		  WHERE (M.Address1 = '' OR M.Address1 IS NULL 
		  OR M.Address2 = '' OR M.Address2 IS NULL 
		  OR M.PostCode = '' OR M.PostCode IS NULL) 
		  AND ((S.Address1 <> '' AND S.Address1 IS NOT NULL) 
		  OR (S.Address2 <> '' AND S.Address2 IS NOT NULL) 
		  OR (S.PostCode <> '' AND S.PostCode IS NOT NULL))";
		$results = DBQueryLogOnFailure( $sql );
		$num_rows = mysql_affected_rows();
		echo date("Y-m-d H:i:s")." Primary members with missing mandatory address details - $num_rows rows...\r\n";

	// fetch a row and write the column names out to the file
	$row = mysql_fetch_assoc($results);
	$line = "";
	$comma = "";
	foreach($row as $name => $value) {
		$line .= $comma . '"' . str_replace('"', '""', $name) . '"';
		$comma = ",";
	}
	$line .= "\n";
	fputs($fp, $line);

	// remove the result pointer back to the start
	mysql_data_seek($results, 0);

	// and loop through the actual data
	while($row = mysql_fetch_assoc($results)) {
	   
		$line = "";
		$comma = "";
		foreach($row as $value) {
			$line .= $comma . '"' . str_replace('"', '""', $value) . '"';
			$comma = ",";
		}
		$line .= "\n";
		fputs($fp, $line);
	   
	}

	fclose($fp);
	return $num_rows;
}

// - - - - - - - - Primary members with missing year of birth details - - - - - - - - -
function MissingYoBDetails($filepath,$filename)
{
	$fp = fopen($filepath.$filename, "w");

	$sql = "SELECT AccountNo, M.MemberNo AS PrimaryMemberNo,
			M.Title AS PrimaryTitle, M.Forename AS PrimaryForename, M.Surname AS PrimarySurname, 
			S.MemberNo AS SecondaryMemberNo, 
			S.Title AS SecondaryTitle, S.Forename AS SecondaryForename, S.Surname AS SecondarySurname, 
			M.DOB AS PrimaryDOB, S.DOB AS SecondaryDOB
			FROM Arvato_Members AS M
			JOIN Arvato_SecondaryMembers AS S
			USING ( AccountNo ) 
			WHERE ( M.DOB IS NULL OR M.DOB = '0000') AND (S.DOB IS NOT NULL AND S.DOB <> '0000')";
	$results = DBQueryLogOnFailure( $sql );
	$num_rows = mysql_affected_rows();
	echo date("Y-m-d H:i:s")." Primary members with missing year of birth details - $num_rows rows...\r\n"; 

	// fetch a row and write the column names out to the file
	$row = mysql_fetch_assoc($results);
	$line = "";
	$comma = "";
	foreach($row as $name => $value) {
		$line .= $comma . '"' . str_replace('"', '""', $name) . '"';
		$comma = ",";
	}
	$line .= "\n";
	fputs($fp, $line);

	// remove the result pointer back to the start
	mysql_data_seek($results, 0);

	// and loop through the actual data
	while($row = mysql_fetch_assoc($results)) {
	   
		$line = "";
		$comma = "";
		foreach($row as $value) {
			$line .= $comma . '"' . str_replace('"', '""', $value) . '"';
			$comma = ",";
		}
		$line .= "\n";
		fputs($fp, $line);
	   
	}

	fclose($fp);
	return $num_rows;
}	

// - - - - - - - - Primary members with missing contact name details - - - - - - - - -
function MissingContactDetails($filepath,$filename)
{
	$fp = fopen($filepath.$filename, "w");

	$sql = "SELECT AccountNo, M.MemberNo AS PrimaryMemberNo, S.MemberNo AS SecondaryMemberNo, 
			IFNULL(M.Forename,'') as PrimaryForename, 
			IFNULL(M.Surname,'') as PrimarySurname, 
			IFNULL(S.Forename,'') as SecondaryForename, 
			IFNULL(S.Surname,'') as SecondarySurname 
				FROM Arvato_Members AS M
				JOIN Arvato_SecondaryMembers AS S
				USING ( AccountNo ) 
				WHERE (M.Forename = '' OR M.Forename IS NULL 
				OR M.Surname = '' OR M.Surname IS NULL) AND 
				((S.Forename <> '' AND S.Forename IS NOT NULL) 
				OR (S.Surname <> '' AND S.Surname IS NOT NULL))";
	$results = DBQueryLogOnFailure( $sql );
	$num_rows = mysql_affected_rows();
	echo date("Y-m-d H:i:s")." Primary members with missing contact name details - $num_rows rows...\r\n"; 

	// fetch a row and write the column names out to the file
	$row = mysql_fetch_assoc($results);
	$line = "";
	$comma = "";
	foreach($row as $name => $value) {
		$line .= $comma . '"' . str_replace('"', '""', $name) . '"';
		$comma = ",";
	}
	$line .= "\n";
	fputs($fp, $line);

	// remove the result pointer back to the start
	mysql_data_seek($results, 0);

	// and loop through the actual data
	while($row = mysql_fetch_assoc($results)) {
	   
		$line = "";
		$comma = "";
		foreach($row as $value) {
			$line .= $comma . '"' . str_replace('"', '""', $value) . '"';
			$comma = ",";
		}
		$line .= "\n";
		fputs($fp, $line);
	   
	}

	fclose($fp);
	return $num_rows;
}	
	

// - - - - - - - - M A I N   P R O C E S S - - - - - - - - -

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

connectToDB( MasterServer, TexacoDB );

echo "-------------------------------------------------------------------------\r\n";
CleanUpAddressLinesLoop();
CreateWorkingTables();
$filepath = "/tmp/";

echo "-------------------------------------------------------------------------\r\n";
$filename = "missingemailandpassword.csv";
$rows = MissingEmailPassword($filepath,$filename);
$line2 = "Primary members with missing email and password - $rows rows<p>"; 

echo "-------------------------------------------------------------------------\r\n";
$filename = "missingmandatoryaddressdetails.csv";
$rows = MissingAddressDetails($filepath,$filename);
$line2 .= "Primary members with missing mandatory address details - $rows rows<p>"; 
 
echo "-------------------------------------------------------------------------\r\n";
$filename = "missingyearofbirthdetails.csv";
$rows = MissingYoBDetails($filepath,$filename);
$line2 .= "Primary members with missing year of birth details - $rows rows<p>"; 

echo "-------------------------------------------------------------------------\r\n";
$filename = "missingcontactnamedetails.csv";
$rows = MissingContactDetails($filepath,$filename);
$line2 .= "Primary members with missing contact name details - $rows rows<p>"; 

echo "-------------------------------------------------------------------------\r\n";
echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>