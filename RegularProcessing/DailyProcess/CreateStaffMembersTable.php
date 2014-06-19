<?php
// Includes available to every script that includes this one
//include_once("../includes/inc-mysql.php");
//include_once("../includes/debug.php");
$db_user = 'pma001';
$db_pass = 'amping';
include "../../include/DB.inc";

$timedate = date("Y-m-d H:i:s");

echo "$timedate ".__FILE__." started \r\n";

#	First connect to the master database and collect all of the records we need.
connectToDB( MasterServer, TexacoDB );
//include("../includes/db_connect.php");echo "Connected to master database\r\n";

$timedate = date("Y-m-d H:i:s");
echo "$timedate ".__FILE__." completed \r\n";

//	Drop table StaffMembers

$sql = "drop table if exists StaffMembers";
$results = DBQueryExitOnFailure( $sql );

$sql = "create table StaffMembers select StaffID, MemberNo,HomeSite,SiteName,Title,Initials,Forename ,Surname,PrimaryCard,M.Address1,M.Address2,M.Address3,";
$sql .= "M.Address4,M.Address5,M.Postcode,Accounts.AccountNo,AreaCode,M.CreationDate,M.CreatedBy,count(*) as NoOfRegistrations ";
$sql .= "from Members as M join Accounts using (AccountNo) join sitedata on (Accounts.Homesite = sitedata.SiteCode)";
$sql .= " join Tracking using(AccountNo) where Accounts.AccountType = 'D' and TrackingCode = '1188' and Tracking.CreationDate > '2009-05-18 00:00:00' and M.CreationDate > '2009-05-18 00:00:00' group by AccountNo";
$results = DBQueryExitOnFailure( $sql );

$sql = "ALTER TABLE `StaffMembers` ADD INDEX ( `AccountNo` ) ";
$results = DBQueryExitOnFailure( $sql );

//	Drop table StaffRedemptionsHistory

$sql = "drop table if exists StaffRedemptionsHistory";
$results = DBQueryExitOnFailure( $sql );

$sql = "CREATE TABLE StaffRedemptionsHistory ";
$sql .= "SELECT StaffID, Cost, MemberNo, AccountNo FROM OrderProducts JOIN Orders USING(OrderNo) JOIN StaffMembers USING(MemberNo) WHERE Orders.CreationDate > '2009-05-18'";
$results = DBQueryExitOnFailure( $sql );

$sql = "ALTER TABLE `StaffRedemptionsHistory` ADD INDEX ( `StaffID` ) ";
$results = DBQueryExitOnFailure( $sql );
?>
