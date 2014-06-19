	<?php

 include "../../include/DB.inc";
 include "../../include/Locations.php";
 include "../../DBInterface/TrackingInterface.php";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
//	$db_pass = "";																		   


	// Main function

		global 	$ProcessName;
		global  $uname;
		global $lineNo;
		global $Updated;


		$fileToProcess =  "nohup.out";

		$ProcessName = "BatReproc";
		$uname = $ProcessName;

		connectToDB( MasterServer, TexacoDB );

		$fr = fopen( $fileToProcess, "r");

		if(!$fr) 
		{
			echo "Error! Couldn't open the file.";
		} 
		else 
		{
			$c = 0;
			$d = 0;
			$CardNo = "";
			while( $line = fgets( $fr ))
			{
				if( strstr( $line, "Oh dear " ) )
				{
					$CardNo = substr( $line, 8, 19 );
					$d++;
				}
				if( strstr( $line, "Update Cards set StoppedPoints" ) )
				{
					$strpos = strpos( $line, "'" ); 
					$Update = substr( $line, 0, $strpos ) . "'$CardNo'";
					echo "$Update\n";
					DBQueryExitOnFailure( $Update );
					$c++;
				}
			}
			fclose($fr);
			echo "$c Records changed, $d";
		}
	?>