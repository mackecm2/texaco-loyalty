<?php 

	require "../../include/DB.inc";

	$db_user = "DAdmin";
	$db_pass = "Global";
	
	$slave = connectToDB( ReportServer, AnalysisDB );

	$db_user = "root";
	$db_pass = "trave1";
	
	$master = connectToDB( MasterServer, TexacoDB );

	$sql = "select Bereweeke.MemberNo, AccountNo from Bereweeke join texaco.Members using(MemberNo)";

	$slaveRes = mysql_query( $sql, $slave )  or die( mysql_error() );

	echo "There are ". mysql_num_rows($slaveRes). " to be updated";
	$c = 0;

	while( $row = mysql_fetch_assoc( $slaveRes ) )
	{


		$MemberNo = $row["MemberNo"];
		$AccountNo = $row["AccountNo"];

		$sql = "insert into PersonalCampaigns( MemberNo, PromotionCode, StartDate, EndDate, PromoHitsLeft, CreationDate, CreatedBy ) values ( $MemberNo, 'HomeSiteCl', '2005-07-23', '2005-08-19', -1, now(), 'DawleysAdmin' )"; 
		mysql_query( $sql, $master )  or die( mysql_error() );

		$sql = "insert into CampaignHistory( MemberNo, AccountNo, CampaignType, CampaignCode, CreationDate, CreatedBy) values ( $MemberNo, AccountNo, 'SITECLSE', 'BEREWEEKE', '2005-07-26', 'DawleysAdmin' )";

		mysql_query( $sql, $master )  or die( mysql_error() );

	}
  
	echo date("h:i:s");
	echo "Finished\n";
?>