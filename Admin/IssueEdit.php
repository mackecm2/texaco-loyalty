<?php

	//Alter Table Issues add column PriorityGrp enum( 'U', 'H', 'M', 'L' ) default 'L';

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	global $uname;

	if( isset($_GET["IssueNo"] ) )
	{
		$sql = "select * from Issues where IssueNo = $_GET[IssueNo]";
		$sql2 = "select * from IssuesLog where IssueNo = $_GET[IssueNo] order by RevisedDate Desc";
	}
	else
	{
		$sql = "Select null as IssueNo, null as ShortDescription, null as Description, null as Effort,null as Additional, null as Notes, null as NeededBy, null as Priority, null as Status, '$uname' as CreatedBy, now() as CreationDate, null as RevisedBy, null as RevisedDate, null as EstimatedDate, null as Responsablity";
		$sql2 = "select null as RevisedDate, null as Notes, null as RevisedBy";

	}
	$Results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $Results );

	$StatusList = array();
	$StatusList[''] = '';
	$StatusList['New'] = 'New';
	$StatusList['Completed'] = 'Completed';
	$StatusList['Closed'] = 'Closed';
	$StatusList['Awaiting Response'] = 'Awaiting Response';
	$StatusList['Provisionaly Closed'] = 'Provisionaly Closed';
	$StatusList['Re-opened'] = 'Re-Opened';

	$ResponsablityList = array();
	$ResponsablityList[''] = '';
	$ResponsablityList['RSM'] = 'RSM';
	$ResponsablityList['T&T'] = 'T&T';
	$ResponsablityList['Texaco'] = 'Texaco';
	$ResponsablityList['Dawleys'] = 'Dawleys';
	$ResponsablityList['Nick'] = 'Nick';
	$ResponsablityList['Steve'] = 'Steve';
	$ResponsablityList['Mark'] = 'Mark';
	$ResponsablityList['James'] = 'James';
	$ResponsablityList['Ian'] = 'Ian';
	$ResponsablityList['Edd'] = 'Edd';
	$ResponsablityList['Patricia'] = 'Patricia';
	$ResponsablityList['Hannah'] = 'Hannah';

	$PriorityList['U'] = 'Urgent';
	$PriorityList['H'] = 'High';
	$PriorityList['M'] = 'Medium';
	$PriorityList['L'] = 'Low';

	$Results2 = DBQueryExitOnFailure( $sql2 );

	$Title = "Issue Manager";
	$currentPage = "Issues";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	include "../MasterViewHead.inc";

?>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>

<form action="UpdateIssue.php" method=POST>
<table>
<?php
	echo "<tr><td align=right>ID: <td><input name=IssueNo value='$row[IssueNo]' readonly>";
	echo "<td align=right>Needed Date: <td><input name=NeededBy value='$row[NeededBy]'>";
	echo "<td align=right>CreationDate: <td><input name=CreationDate value='$row[CreationDate]' readonly>";
	echo "<tr><td align=right>Priority: <td><input name=Priority value='$row[Priority]'>";
	echo "<td align=right>EstimatedDate: <td><input name=EstimatedDate value='$row[EstimatedDate]'>";
	echo "<td align=right>CreatedBy: <td><input name=CreatedBy value='$row[CreatedBy]' readonly>";
	echo "<tr><TD><Td><select name=PriorityGrp>";
	DisplaySelectOptions( $PriorityList, $row["PriorityGrp"] );
	echo "</select>";
	echo "<td align=right>Responsablity:";
	echo "<Td><select name=Responsablity value='$row[Responsablity]'>";
	DisplaySelectOptions( $ResponsablityList, $row["Responsablity"] );
	echo "</select>";
	echo "<td align=right>Effort: <td><input name=Effort value='$row[Effort]' >";
	echo "<tr><td align=right>Status: <td>";
	echo "<select name=Status value='$row[Priority]'>";
	DisplaySelectOptions( $StatusList, $row["Status"] );
	echo "</select>";
	echo "<td align=right>RevisedBy: <td><input name=RevisedBy value='$row[RevisedBy]' readonly>";
	echo "<td align=right>RevisedDate: <td><input name=RevisedDate value='$row[RevisedDate]' readonly>";
	echo "<tr><td align=right>Description: <td colspan=3><input name=ShortDescription value='$row[ShortDescription]' size=50>";
	echo "<tr><td align=right>Description: <td colspan = 5><TextArea cols=80 rows=3 name=Description>$row[Description]</TextArea>";
	echo "<tr><td align=right>Notes: <td  colspan = 5><TextArea cols=80 rows=3  name=Notes>$row[Notes]</TextArea>";
	echo "<tr><td align=right>Addtional: <td  colspan = 5><TextArea cols=80 rows=3  name=Additional>$row[Additional]</TextArea>";
	echo "</Table><input type=Submit>";
	echo "<button onclick='window.location=\"IssueManager.php\"'>Cancel</button>";
echo "</form>";
   DisplayTable( $Results2, 0 );
	include "../MasterViewTail.inc";
?>