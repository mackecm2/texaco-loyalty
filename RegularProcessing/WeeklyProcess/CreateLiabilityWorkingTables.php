<?php 
/*
 * ---------------------------------------------------
 * Liability Accrual - create working tables
 * ---------------------------------------------------
 * Author : MRM
 * Date   : 21 OCT 09
 * 
 * This is basically the section 0 of RegularProcessing/Monthly/LiabilityAccrual.php
 * 
 * 
 * 
 * 
 */

require "../../include/DB.inc";
require "../includes/LiabilityFunctions.inc";
require "../../Reporting/GeneralReportFunctions.php";													

#------- M A I N   P R O C E S S -----------------------------

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$db_user = "pma001";
$db_pass = "amping";
$timestamp = "WeeklyTable";

echo date("Y-m-d H:i:s")." connecting to ReportServer \r\n";
$slave = connectToDB( ReportServer, AnalysisDB );

//------------------------------------------------------------------------------------------------------------------------------------
//------- Section 0 - Create Working Tables ------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------
CreateWorkingTables($slave,$timestamp);

echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>