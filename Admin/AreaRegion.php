<?php
	include "../include/Session.inc";

	$RegionOptions = array();
	$AreaOptions = array();

	if( isset( $_GET["AreaCode"] ) )
	{
		// Get all the area managers of this area
		$sql = "Select distinct AreaManager from Sites where RegionCode = '$_GET[AreaCode]'";

		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_row($results) )
		{
			$AreaOptions[$row[0]] = $row[1];
		}

		// Get all the areas with this area as a region
		$sql = "Select distinct RegionCode from Sites where AreaCode = '$_GET[AreaCode]'";
		$results = DBQueryExitOnFailure( $sql );

		while( $row = mysql_fetch_row($results) )
		{
			$RegionOptions[$row[0]] = $row[1];
		}

	}
	else
	{
		// Get all the Region managers of this region
		if( isset( $_GET["RegionCode"] ) )
		{
			$sql = "Select distinct RegionManager from Sites where RegionCode = '$_GET[RegionCode]'";

			$results = DBQueryExitOnFailure( $sql );

			while( $row = mysql_fetch_row($results) )
			{
				$RegionOptions[$row[0]] = $row[1];
			}
		}
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
<FieldSet><Legend>Region Information</Legend>
<Table><TR><TD>Region managers:
<TD>
<?php
	foreach( $RegionOptions as $RegionManager )
	{
		echo "<BR>$RegionManager\n";
	}
?>
<TR>Region Code: 
<TD>


<BR> Region manager:
</TABLE>
</FieldSet>

<FieldSet><Legend>Area Information</Legend>

Area Code: 

<BR> Area manager:
<?php
	foreach( $AreaOptions as $AreaManager )
	{
		echo "<BR>$RegionManager\n";
	}
?>
<FieldSet><Legend>Site Information</Legend>

Site Code: 

<BR> Site manager:

</FieldSet>
</FieldSet>


</TABLE>

</BODY>
</HTML>

