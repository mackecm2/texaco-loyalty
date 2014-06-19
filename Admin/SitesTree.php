<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";

	$sql = "Select SiteCode, AreaCode, RegionCode from Sites order by RegionCode, AreaCode ";
	
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


<style>
TABLE
{
	MARGIN: 0px; 
	TOP: 0px;
	Padding: 0;
}
.lshape
{
	margin: 0px;
	padding: 0px;
	font-size: 10;
	BORDER-LEFT: dotted thin; 
	BORDER-BOTTOM: dotted thin;
}

.middle
{
	margin: 0px;
	padding: 0px;
	font-size: 10;
	BORDER-LEFT: dotted thin; 
}

.last
{
}
</style>

<script>

function PopulateRegionData( RegionData )
{
	alert( RegionData );
	parent.SiteData.location = 'AreaRegion.php?RegionCode='+RegionData;
}

function PopulateAreaData( RegionData, AreaData )
{
	alert( AreaData );
	parent.SiteData.location = 'AreaRegion.php?RegionCode='+RegionData+'&AreaCode='+AreaData;
}

function PopulateSiteData( RegionData, AreaData, SiteData )
{
	alert( SiteData );
	parent.SiteData.location = 'AreaRegion.php?RegionCode='+RegionData+'&AreaCode='+AreaData+'&SiteCode='+SiteData;
}

function Toggle(img, node)
{
	stat = document.getElementById( "status" ).value;
	// Unfold the branch if it isn't visible
	if (node.style.display == 'none')
	{
		img.src = "minus.gif";
		node.style.display = '';
	}
	// Collapse the branch if it IS visible
	else
	{
		img.src = "plus.gif";
		node.style.display = 'none';
	}

}
</script>
</HEAD>


<BODY>
<?php
	$unique = 1;
	$CurrentArea = "ZZZZ";
	$CurrentRegion = "ZZZZ";
	$RegionExit = "";
	$AreaExit = "";
	?>
	<input type=hidden id=status>
	<?php
	echo "<TABLE>\n";
	while( $row = mysql_fetch_assoc( $results ))
	{
		if( $CurrentRegion != $row["RegionCode"] )
		{
			$CurrentRegion = $row["RegionCode"];
			if( $CurrentRegion == "" )
			{
				$CurrentRegion = "Blank!";
			}
			$CurrentArea = "ZZZZ";
			echo $AreaExit;
			echo $RegionExit;
			echo "<TR><TD><IMG onClick=\"Toggle( this, T$unique)\" height=9 src=\"plus.gif\" width=9>\n$CurrentRegion <div id = T$unique  style=\"Display: none\">\n<Table >\n";
			$AreaExit = "";
			$RegionExit = "</Table></DIV>\n";
			$unique++;
			$CurrentRegion = $row["RegionCode"];
		}
		if( $CurrentArea != $row["AreaCode"] )
		{
			$CurrentArea = $row["AreaCode"];
			if( $CurrentArea == "" )
			{
				$CurrentArea = "Blank!";
			}
			echo $AreaExit;
			echo "\t<TR><TD Width=10 ><TD ><IMG onClick=\"Toggle(this, T$unique)\" height=9 src=\"plus.gif\" width=9>\n$CurrentArea \t<DIV id = T$unique style=\"Display: none\">\n\t <TABLE>\n";
			$AreaExit = "\t</TABLE></DIV>\n";
			$unique++;
			$CurrentArea = $row["AreaCode"];
		}
		ECHO "\t\t<tr><td width=30><TD onclick=\"PopulateSiteData( '$CurrentArea','$CurrentRegion', '$row[SiteCode]')\">$row[SiteCode]</TR>\n";
	}
	ECHO $AreaExit;
	ECHO $RegionExit;
?>
</BODY>
</HTML>
