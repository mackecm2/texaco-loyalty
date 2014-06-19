	<?php
	error_reporting( E_ALL );
	
	//**********************************************************************************************
	//*                                                                                             *
	//*           Program Name     :  ConvertDOBNotes.php                                           *
    //*           Path             :  /data/www/websites/texaco/Issues/Accounts                     *
    //*           Author / Date    :  MRM  / 22 REB 2010                                            *   
    //*           Function         :  Changes any instances of DOB to YOB in tracking notes         * 
    //*           Revision History :                                                                *
    //*                                                                                             *
    //*                                                                                             *
    //*                                                                                             *
    //*                                                                                             *
    //*                                                                                             *
    //**********************************************************************************************
	require "../../include/DB.inc";
	require "../../include/Locations.php";
	
	$db_name = "texaco";
	$db_user = "HomeExport";
	$db_pass = "FLOWER";
	
	$ProcessName   = "ConvertDOBNotes";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	$master = connectToDB( MasterServer, TexacoDB );
	$sql = "SELECT * FROM Tracking WHERE Notes LIKE '%DOB =>%'";
	$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
	$numrows = mysql_num_rows($res);
	echo date("H:i:s");
	echo " $numrows rows to update\r\n";
	$readcount = 0;
	$updatecount = 0;
	while( $row = mysql_fetch_assoc( $res ) )
	{
		$matchstring = mysql_real_escape_string(preg_replace("/DOB =>(\d{4})-(\d{2})-(\d{2})/", 'DOB =>$1', $row[Notes]));
		$sql1 = "UPDATE Tracking SET Notes = '$matchstring' WHERE MemberNo = $row[MemberNo] AND AccountNo = $row[AccountNo] AND TrackingCode = $row[TrackingCode] AND CreationDate = '$row[CreationDate]' ";
		$res1 = mysql_query( $sql1, $master );
		$numrows = mysql_affected_rows();
		if( $numrows != 0 )
		{
			echo $sql1."\r\n";
			$updatecount++;
			if( ($updatecount % 100) == 0 )
			{
				echo date("H:i:s");
				echo " Updated $updatecount tracking records\r\n";
			}
		}
		$readcount++;
		if( ($readcount % 1000) == 0 )
		{
			echo date("H:i:s");
			echo " Processed $readcount tracking records\r\n";
		}
	}

echo date("Y-m-d H:i:s").' '.__FILE__." completed. $readcount records processed, $updatecount records updated. \r\n";

?>