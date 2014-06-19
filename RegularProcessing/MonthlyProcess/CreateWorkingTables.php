<?php 
/*
 * ---------------------------------------------------
 * Monthly Liability Reporting & Accrual process
 * ---------------------------------------------------
 * Author : MRM
 * Date   : 02 NOV 10
 *  
 * Extracted from LiabilityAccrual.php
 * 
 * 
 */

require "../../include/DB.inc";
require "../includes/LiabilityFunctions.inc";

#------- M A I N   P R O C E S S -----------------------------

echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";

$timestamp = date ("FY");
$taskname = $timestamp."Liability";

$db_user = "pma001";
$db_pass = "amping";

echo date("Y-m-d H:i:s")." connecting to ReportServer \r\n";
$slave = connectToDB( ReportServer, AnalysisDB );

//------------------------------------------------------------------------------------------------------------------------------------
//------- Section 0 - Create Working Tables ------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------------------

CreateWorkingTables($slave,$timestamp);


echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";
?>