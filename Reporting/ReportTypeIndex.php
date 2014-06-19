<?php 
	global $Reporting;
	$Reporting = 1;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/GeneralInterface.php";
	include "../DBInterface/CardInterface.php";
	
	$currentPage = "Reporting";
	$Title = "Report List";
	include "../MasterViewHead.inc";
?>
	<tr>
 	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: outset; MARGIN-LEFT:100">
<?php 
	$sql = "Select * from ReportTypes ORDER BY ReportTypeId ASC";
	$Results = DBQueryExitOnFailure( $sql );

	while( $row = mysql_fetch_assoc( $Results )  )
	{
		
		#	We need to restrict all except certain reports for the 'noone' user.
		if( ($_SESSION['username'] <> 'noone') OR (($row['ReportTypeId'] > 37) and ($row['ReportTypeId'] < 45)) )
		{
			if( $row['ReportTypeId'] < 46)
			{
				echo "<BR><a target=_blank href=\"ReportMonthIndex.php?Type=$row[ReportTypeId]\">$row[Description]</a>";
			}
		else
			{
				echo "<BR><a ";
				if( $row['TableRoot'] != "LiabilityAccrualForecast.php" )
				{
					echo "target=_blank";
				}
				echo" href=\"$row[TableRoot]\">$row[Description]</a>";
			}
		}
	}
	
	echo "</td></tr></Table>";
	include "../MasterViewTail.inc";
?>
	