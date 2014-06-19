<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	include "../DBInterface/MemberInterface.php";

	$AN = $_GET["AccountNo"];
	$CN = $_GET["CardNo"];

	if( $AN != "" )
	{
		$sql = "select Title, Forename, Surname, DOB, Address1, Address2, Address3, Address4, Address5, PostCode, Members.AccountNo, Members.MemberNo, PrimaryCard, (isnull(AwardStopDate) or AwardStopDate = '0000-00-00') as AwardStop, AwardStopDate, (isnull(RedemptionStopDate) or RedemptionStopDate = '0000-00-00') as RedemptionStop, PrimaryMember, Balance from Accounts Join Members using(AccountNo) where Members.AccountNo=$AN and PrimaryMember = 'Y'";
	}
	else if( $CN != "" )
	{
		$sql = "select '' as Title, '' as Forename, '' as Surname, '' as Address1, '' as Address2,'' as Address3,'' as Address4,'' as Address5, '' as PostCode, 1 as AwardStop, 1 as RedemptionStop, 'N' as PrimaryMember, StoppedPoints as Balance, CardNo from Cards where CardNo = $CN";
	}
	


	$results = DBQueryExitOnFailure( $sql );
	

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> New Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<script>
	function Merge()
	{
		window.returnValue = true;
		window.close();
	}

</script>

</HEAD>

<BODY>

	<Center>
	<BR>
	<H3>Confirm Merger of Account</H3>
	<TABLE>

<?php
	$row = mysql_fetch_assoc( $results );

	
	echo "<Tr><TD width=60>CardNo <TD>$row[PrimaryCard]\n";
	echo "<Tr><TD> Name <TD>$row[Title] $row[Forename] $row[Surname]\n";
	echo "<Tr><TD>Address<TD>$row[Address1]\n";
	echo "<Tr><TD><TD>$row[Address2]\n";
	echo "<Tr><TD><TD>$row[Address3]\n";
	echo "<Tr><TD><TD>$row[Address4]\n";
	echo "<Tr><TD><TD>$row[Address5]\n";
	echo "<Tr><TD>Postcode<TD>$row[PostCode]\n";
	echo "<tr><td>&nbsp;";
	echo "<TR><TD>Stars<TD>$row[Balance]\n";

		echo "<TR><TD colspan=2><table style=\"width: 100%; background-color: orange; color: white;\"><TR>";
		echo "<td style=\"width:30%; text-align: left\">Stops";
		echo "<td style=\"width:30%; text-align: left\">Redeem";
			DisplayCheckBox( "r1", $row["RedemptionStop"] == 0, "disabled" );
		echo "<td style=\" text-align: left\">Awards";
			DisplayCheckBox( "a1", $row["AwardStop"] == 0, "disabled" );
		echo "</table>";


?>	

	</TABLE>
	<Button onclick="Merge()">Merge</Button>	
	<Button onclick="window.close()">Cancel</Button>
	</Center>
</BODY>
</HTML>
