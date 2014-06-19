<?php
// Includes available to every script that includes this one
include_once("../includes/inc-mysql.php");
include_once("../includes/debug.php");


$timedate = date("Y-m-d H:i:s");

echo "$timedate ".$_SERVER['PHP_SELF']." started \r\n";

#	First connect to the master database and collect all of the records we need.

include("../includes/dbconnect_master.php");

echo "Connected to Master\r\n";
$LastSite = 0;
$result = mysqlSelect($masterrecord,"*","sites left join merchantnumbers using (RSMSiteId)","(Status <> 'Pending' AND Status <> 'Deleted' and Status <> 'Cancelled') AND sites.VendorID = '1' AND sites.COT <> 'LUBES' AND sites.COT <> 'LUBES HO' order by SiteNo,BMSLiveDate DESC","0");

if($result >0)
{

	#	We have master data now delete from the local database

	include("../includes/dbconnect_local.php");
	mysqlQuery("DELETE FROM sitedata WHERE 1");
		{
			#echo"Last site is $LastSite\r\n";
			#	We only want the most recent result
			
		}
		else
		{


			$ourData['SiteCode'] = $singlerecord['SiteNo'];
			$ourData['COT'] = $singlerecord['COT'];
			$ourData['UKFuelsMID'] = $singlerecord['UKFuelsMID'];