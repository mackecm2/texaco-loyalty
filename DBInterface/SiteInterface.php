<?php
//* MRM 27/06/2008 - if( $numrows ==0 ) changed to if( $numrows >0 ) since we may have more than one Site Code if one has a status of "Closing"
function CheckSiteNumber( $siteNo )
{
	$sql = "Select * from Sites where SiteCode = $siteNo";

	$results = DBQueryExitOnFailure( $sql );
	$numrows = mysql_num_rows($results);
	if( $numrows >0 )
	{
		return true;
	}
	else
	{
		return false;
	}
}


?>