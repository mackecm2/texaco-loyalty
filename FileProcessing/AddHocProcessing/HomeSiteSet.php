<?php
	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";
//	$db_pass = "";
	require "../General/misc.php";

	connectToDB();

	$AccountsUpdated = 0;
	$ZeroTransactions = 0;
	$sql = "Select PrimaryCard, HomeSite, Accounts.AccountNo from Members Join Accounts using (AccountNo) where PrimaryMember = 'Y' and PrimaryCard is not null and PrimaryCard != '' and (HomeSite is null or HomeSite = 0) and RevisedDate > '2004-10-27";

	$results = mysql_query( $sql ) or die( mysql_error(). $sql );

	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<br>$row[PrimaryCard]";
		$siteCodes = array();

		$sql = "Select SiteCode from Transactions where CardNo = '$row[PrimaryCard]' order by TransTime desc limit 5"; 
		$maxCount = 0;
		$maxSite = 0;
		$trans = mysql_query( $sql ) or die( mysql_error(). $sql );

		if( mysql_num_rows( $trans ) > 0 )
		{
			while( $rowTrans = mysql_fetch_assoc( $trans ) )
			{
				$thisSite = $rowTrans["SiteCode"];
				echo "($thisSite)";
				if( isset( $siteCodes[$thisSite] ) )
				{
					$siteCodes[$thisSite]++;
				}
				else
				{
					$siteCodes[$thisSite] = 1;
				}
				if( $maxCount < $siteCodes[$thisSite] )
				{
					$maxCount = $siteCodes[$thisSite];
					$maxSite = $thisSite;
				}
			}
		}
		else
		{
			$ZeroTransactions++;
		}
		if( $maxCount > 0 )
		{
			$sql = "Update Accounts set HomeSite = $maxSite, HomeSiteDate = now() where AccountNo = $row[AccountNo]";
			
			$AccountsUpdated++;

			echo "=> $maxSite\n";

			mysql_query( $sql ) or die( mysql_error(). $sql );
		}
	}

	echo "<br> Accounts Updated $AccountsUpdated\n";
	echo "<br> Accounts Not Updated $ZeroTransactions\n";
?>