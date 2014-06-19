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
	mysqlQuery("DELETE FROM sitedata WHERE 1");	#echo "Now deleting the local records<br>";	foreach($masterrecord as $singlerecord)	{		if($LastSite == $singlerecord['SiteNo'])
		{
			#echo"Last site is $LastSite\r\n";
			#	We only want the most recent result
			
		}
		else
		{


			$ourData['SiteCode'] = $singlerecord['SiteNo'];			$ourData['Group_Id'] = $singlerecord['Group_Id'];
			$ourData['COT'] = $singlerecord['COT'];			$ourData['SiteName'] = $singlerecord['SiteName'];			$ourData['Address1'] = $singlerecord['Address1'];			$ourData['Address2'] = $singlerecord['Address2'];			$ourData['Address3'] = $singlerecord['Address3'];			$ourData['Address4'] = $singlerecord['Address4'];			$ourData['Address5'] = $singlerecord['Address5'];			$ourData['PostCode'] = $singlerecord['PostCode'];			$ourData['AreaCode'] = $singlerecord['AreaCode'];			$ourData['AreaManager'] = $singlerecord['AreaManager'];			$ourData['RegionCode'] = $singlerecord['RegionCode'];			$ourData['RegionalManager'] = $singlerecord['RegionalManager'];			$ourData['SiteContact'] = $singlerecord['SiteContact'];			$ourData['PhoneNo'] = $singlerecord['PhoneNo'];			$ourData['FaxNo'] = $singlerecord['FaxNo'];			$ourData['Status'] = $singlerecord['Status'];			$ourData['BMSLiveDate'] = $singlerecord['BMSLiveDate'];
			$ourData['UKFuelsMID'] = $singlerecord['UKFuelsMID'];			#$ourData['SiteClosedDate'] = $singlerecord['SiteClosedDate'];			#$ourData['CreationDate'] = $singlerecord['CreationDate'];			#$ourData['CreatedBy'] = $singlerecord['CreatedBy'];			#$ourData['RevisedDate'] = $singlerecord['RevisedDate'];			#$ourData['RevisedBy'] = $singlerecord['RevisedBy'];			escapeData($ourData) ;			$update = mysqlInsert($ourData,"sitedata");						#echo "Now inserting the new site record $ourData[SiteCode]<br>";		}				$LastSite = $singlerecord['SiteNo'];		#echo"Set Last site to $LastSite\r\n";			}	echo "$timedate SiteData updated.\r\n";}?>
