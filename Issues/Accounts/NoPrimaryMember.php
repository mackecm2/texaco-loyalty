	<?php
	error_reporting( E_ALL );
	
	//**********************************************************************************************
	//*                                                                                             *
	//*           Program Name     :  NoPrimaryMember.php                                           *
    //*           Path             :  /data/www/websites/texaco/Issues/Accounts                     *
    //*           Author / Date    :  MRM  / 29 MAY 2009                                            *   
    //*           Function         :  looks for accounts with no Primary Member                     * 
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
	
	$ProcessName   = "NoPrimaryMembers";

	echo date("Y-m-d H:i:s").' '.__FILE__." started \r\n";
	
	$master = connectToDB( MasterServer, TexacoDB );
	$sql = "SELECT * FROM Members WHERE PrimaryMember = 'N'";
	$res = mysql_query( $sql, $master ) or die( mysql_error($master) );
	while( $row = mysql_fetch_assoc( $res ) )
	{
		$sql1 = "SELECT * FROM Members WHERE PrimaryMember = 'Y' AND AccountNo = $row[AccountNo]";
		$res1 = mysql_query( $sql1, $master );
		$numrows = mysql_num_rows($res1);
		if( $numrows < 1 )
		{
			echo "Account ".$row[AccountNo]." has no Primary Member!\r\n";
			$sql2 = "SELECT * FROM Members WHERE AccountNo = $row[AccountNo]";
			$res2 = mysql_query( $sql2, $master );
			while( $row2 = mysql_fetch_assoc( $res2 ) )
			{
				echo "Account ".$row[AccountNo]." has Member ".$row2[MemberNo]."\r\n";
			}
		}
	}

echo date("Y-m-d H:i:s").' '.__FILE__." completed \r\n";

?>