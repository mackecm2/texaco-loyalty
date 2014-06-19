<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	connectToDB( ReplicationServer, TexacoDB );

	$RegionOptions = array();
	$AreaManagers = array();
	$RegionManagers = array();

	$RegionCode = "";
	$AreaCode = "";
	$SiteCode = "";
	$SiteManager = "";
	$Table = "sitedata";

	if( isset( $_GET["AreaCode"] ) )
	{
		$AreaCode = $_GET["AreaCode"];
		// Get all the area managers of this area
		$sql = "Select distinct AreaManager from $Table where AreaCode = '$AreaCode'";
//		echo $sql;
		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_row($results) )
		{
//			echo "$row[0]";
			$AreaManagers[$row[0]] = $row[0];
		}

		// Get all the regions with this area as an area
		$sql = "Select distinct RegionCode from $Table where AreaCode = '$AreaCode'";
//		echo $sql;
		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_row($results) )
		{
//			echo "$row[0]";
			$RegionOptions[$row[0]] = $row[0];
		}

	}
	else
	{

	}

	// Get all the Region managers of this region
	if( isset( $_GET["RegionCode"] ) )
	{
		$RegionCode =  $_GET["RegionCode"];
		$sql = "Select distinct RegionalManager from $Table where RegionCode = '$RegionCode'";
//		echo $sql;
		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_row($results) )
		{
//			echo "$row[0]";
			$RegionManagers[$row[0]] = $row[0];
		}
	}

	if( isset( $_GET["SiteCode"]) )
	{
			$SiteCode =  $_GET["SiteCode"];
			$sql = "Select * from $Table where SiteCode = $SiteCode";
			$results = DBQueryExitOnFailure( $sql );

			$row = mysql_fetch_assoc($results);
			$SiteManager = $row["SiteContact"];
			$SiteAreaManager = $row["AreaManager"];
			$SiteRegionManager = $row["RegionalManager"];
			$COT = $row["COT"];
			$SiteName = $row["SiteName"];
			$Address = "$row[Address1]<br>$row[Address2]<br>$row[Address3]<br>$row[Address4]<br>$row[Address5]<br>$row[PostCode]"; 

			$d = strpos( $row["PostCode"], ' ' ); 

			$PostCode = substr($row["PostCode"], 0, $d + 2);
			
			$sql = "select SiteCode, SiteName, Postcode, Miles from newpostcodedata join sitedata on( Target = mid(PostCode, 1, char_length(Target))) where source = '$PostCode' and SiteCode != $SiteCode order by miles limit 20";

			$nearsites = DBQueryExitOnFailure( $sql );

	}
	else
	{
			$SiteManager = "";
			$SiteAreaManager = "";
			$SiteRegionManager = "";
			$Address = "";
			$COT = "";
			$SiteName = "";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> New Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>

<BODY>
</DIV>
</TABLE>
<TD>
<?php
/*	echo "Region = ";
	if( isset($_GET["RegionCode"]) )
	{	
		echo "$_GET[RegionCode]";
	}
	echo "AreaCode = ";
	if( isset($_GET["AreaCode"]) )
	{	
		echo "$_GET[AreaCode]";
	}
	echo "SiteCode = ";
	if( isset($_GET["SiteCode"]) )
	{	
		echo "$_GET[SiteCode]";
	}
*/
?>
<FieldSet><Legend>Region Information</Legend>
<Table>
<TR><TD>Region Code: <?php echo $RegionCode; ?>
<TR valign=top><TD valign=top>Region managers assigned to this Region:
<TD valign=top> 
<?php
	if( count($RegionManagers) > 0 )
	{
		foreach( $RegionManagers as $Managers )
		{
			if( $SiteRegionManager == $Managers )
			{
				echo "<font style='color:red;'>=&gt; $Managers &lt;=\n<br>";
			}
			else
			{
				echo "$Managers\n<br>";
			}
		}
	}
	else
	{
		echo "None";
	}
?>

	<Tr  valign=top><TD valign=top>Regions Including the area below
<TD valign=top> 
<?php
	if( count($RegionOptions) > 0 )
	{
		foreach( $RegionOptions as $Regions )
		{
			echo "$Regions\n<br>";
		}
	}
	else
	{
		echo "None";
	}
?>

</TABLE>
</FieldSet>

<FieldSet><Legend>Area Information</Legend>
<Table>
<Tr><TD>Area Code: <?php echo $AreaCode; ?>

<TR valign=top><TD valign=top>Area Managers assigned to this Area:
<TD valign=top>
<?php
	if( count($AreaManagers) >0  )
	{
		foreach( $AreaManagers as $AreaManager )
		{
			if( $SiteAreaManager == $AreaManager )
			{
				echo "<font style='color:red;'>=&gt; $AreaManager &lt;=\n<br>";
			}
			else
			{
				echo "$AreaManager\n<br>";
			}
		}
	}
	else
	{
		echo "None";
	}
?>
</TABLE>
</FieldSet>
<FieldSet><Legend>Site Information</Legend>
<Table style="text-align: top;">
<tr><td>Site Code: <td> <?php echo $SiteCode; ?>
<tr><td>Site manager: <td> <?php echo $SiteManager; ?>
<tr><td>COT: <td> <?php echo $COT; ?>
<tr><td>Site Name: <td> <?php echo $SiteName; ?>
<tr valign=top><td valign=top>Address: <td> <?php echo $Address; ?>

</TABLE>
 
</FieldSet>

<?php 
	if( isset( $_GET["SiteCode"]) )
	{
		DisplayTable( $nearsites, 0 ) ;
		//DisplayTableWithClick( $nearsites, 0, 'SiteCode' );
	}
?>

</TABLE>


<?php
//print_r( $row );
?>
</BODY>
</HTML>

