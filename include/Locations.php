<?php

	include "ServerName.inc";

if( $SERVER_NAME_FOR_ALL == "PANCAKE" )
{
#  Home box
	define("LocationReportsDirectory", "C:/Projects/Texaco/Reports/" );
	define("LocationHomeSitesDirectory", "C:/projects/Texaco/FileProcessing/ToProcess/" );
	define("LocationCompowerDirectory", "C:/projects/Texaco/FileProcessing/ToProcess/Compower/" );
	define("LocationUKFuelsDirectory", "C:/projects/Texaco/FileProcessing/ToProcess/ukfuels/" );
	define("LocationFileProcessing", "C:/Projects/Texaco/FileProcessing/" );
	define("MailTemplateLocations", "G:/APPS/Texaco/Browser Live Letters/" );
	define("TandTInterface", "http://testtobiastv.site.securepod.com/secure/" );
	define("TandTAccount", "username=thresher&password=amaranth" );
	define("LocationDataImport", "C:/Projects/Texaco/FileProcessing/ToProcess/" );
	define("LocationNMCEmailFiles", "C:/Projects/SampleData/" );
	define("LocationTempFiles", "C:/Projects/SampleData/" );
}	
else if(($SERVER_NAME_FOR_ALL == "MASTER") OR  ($SERVER_NAME_FOR_ALL == "SINGLE")) 
{
#Live Box
	define("LocationReportsDirectory", "/data/www/websites/texaco/Reports/" );
	define("LocationCompowerDirectory", "/data/compower/uploads/" );
	define("LocationUKFuelsDirectory", "/data/ukfuels/" );
	define("LocationHomeSitesDirectory", "/data/compower/downloads/" );
	define("LocationFISDirectory", "/data/FIS/uploads/" ); 
    define("LocationFISBalanceFile", "/data/FIS/downloads/" ); 
    define("LocationFISFilesProcessed", "/data/FIS/processed/" ); 
	define("LocationFileProcessing", "/data/www/websites/texaco/FileProcessing/" );
	define("MailTemplateLocations", "G:/APPS/Texaco/Browser Live Letters/" );
	define("TandTInterface", "https://www.starrewards.co.uk/" );
	define("TandTAccount", "username=dawleys&password=rjx9c6" );
	define("LocationDataImport", "/data/dataimport/" );
	define("LocationNMCEmailFiles", "/data/ecircle/download/" );
 	define("LocationTempFiles", "/data/temp/" );
 	define("LocationOccamFiles", "/data/Occam/" );

}
else if(  $SERVER_NAME_FOR_ALL == "SLAVE" ) 
{
 	define("LocationTempFiles", "/data/temp/" );

}
else if( $SERVER_NAME_FOR_ALL == "TEST"  ) 
{
	define("LocationReportsDirectory", "/data/www/websites/texaco/Reports/" );
	define("LocationCompowerDirectory", "/data/compower/uploads/" );
	define("LocationUKFuelsDirectory", "/data/ukfuels/" );
	define("LocationHomeSitesDirectory", "/data/compower/downloads/" );
	define("LocationFISDirectory", "/data/FIS/uploads/" ); 
    define("LocationFISBalanceFile", "/data/FIS/downloads/" ); 
    define("LocationFISFilesProcessed", "/data/FIS/processed/" ); 
	define("LocationFileProcessing", "/data/www/websites/texaco/FileProcessing/" );
   	define("MailTemplateLocations", "G:/APPS/Texaco/Browser Live Letters/" );
//	define("MailTemplateLocations", "S:/Mike/MikeM code/WeOU/Doc/Templates" );
	define("TandTInterface", "http://starrewards-newsite.gap.chevrontexaco.com/" );
	define("TandTAccount", "username=dawleys&password=rjx9c6" );
	define("LocationDataImport", "/data/dataimport/" );
	define("LocationNMCEmailFiles", "/data/ecircle/download/" );
	define("LocationTempFiles", "/data/temp/" );
}
else if( $SERVER_NAME_FOR_ALL == "DEMO"  ) 
{
	define("LocationReportsDirectory", "/data/www/websites/texaco/Reports/" );
	define("LocationCompowerDirectory", "/data/compower/uploads/" );
	define("LocationUKFuelsDirectory", "/data/ukfuels/" );
	define("LocationHomeSitesDirectory", "/data/compower/downloads/" );
	define("LocationFISDirectory", "/data/FIS/uploads/" ); 
    define("LocationFISBalanceFile", "/data/FIS/downloads/" ); 
    define("LocationFISFilesProcessed", "/data/FIS/processed/" ); 
	define("LocationFileProcessing", "/data/www/websites/texaco/FileProcessing/" );
   	define("MailTemplateLocations", "G:/APPS/Texaco/Browser Live Letters/" );
//	define("MailTemplateLocations", "S:/Mike/MikeM code/WeOU/Doc/Templates" );
	define("TandTInterface", "http://starrewards-newsite.gap.chevrontexaco.com/" );
	define("TandTAccount", "username=dawleys&password=rjx9c6" );
	define("LocationDataImport", "/data/dataimport/" );
	define("LocationNMCEmailFiles", "/data/ecircle/download/" );
	define("LocationTempFiles", "/data/temp/" );
}
?>