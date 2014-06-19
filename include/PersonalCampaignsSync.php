<?php

include "../include/DB.inc";

$db_user = "ReadOnly";
$db_pass = "ORANGE";

$master = connectToDB( MasterServer, TexacoDB );

$slave = connectToDB( ReportServer, TexacoDB );

$sql = "select count(*) from PersonalCampaigns";

$d = mysql_query( $sql, $master );
$row = mysql_fetch_row( $d );
$c = $row[0];

echo "master $c\n";

$d = mysql_query( $sql, $slave );
$row = mysql_fetch_row( $d );
$c = $row[0];

echo "slave $c\n";


$sql = "select * from PersonalCampaigns";

$Camps = mysql_query( $sql, $slave );

$count = 0;
$missing = 0;
$dups = 0;

while( $row = mysql_fetch_assoc( $Camps ) )
{
	$sql = "select * from PersonalCampaigns where MemberNo = $row[MemberNo] and PromotionCode = '$row[PromotionCode]'";	

	$matches = mysql_query( $sql, $master );

	$num = mysql_num_rows( $matches );

	$count++;
	if( $count % 1000 == 0 )
	{
		echo "$count\n";
	}
	if( $num == 0 )
	{
		$missing++;
		echo "Match not found for $row[MemberNo], $row[PromotionCode], $row[StartDate], $row[CreationDate], $row[CreatedBy]\n";
	}
	else if( $num > 1 )
	{
		$dups++;
		echo "$num matches found for $row[MemberNo], $row[PromotionCode], $row[StartDate], $row[CreationDate], $row[CreatedBy]\n";
	}
}

echo "Extra in slave $missing, Duplicates $dups\n";

?>