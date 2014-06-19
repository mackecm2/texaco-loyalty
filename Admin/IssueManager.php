<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
//	include "../DBInterface/BonusInterface.php";

	$sql = "Select *, Date_format( CreationDate, '%Y-%m-%d' ) as DateAdded, Date_Format( NeededBy, '%Y-%m-%d') as NeededBy from Issues where Status is null or Status != 'Closed' order by Priority is null, Priority";

	$results = DBQueryExitOnFailure( $sql );


	$Title = "Issue Manager";
	$currentPage = "Issues";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	include "../MasterViewHead.inc";
?>

<script>
	dirty = false;

	function SetDirty()
	{
		dirty = true;
		document.getElementById("update").disabled = false;
		document.getElementById("create").disabled = true;
	}

	function MoveUp( row )
	{
		if( row.parentNode.firstChild != row.previousSibling )
		{
			SetDirty();
			row.swapNode( row.previousSibling );
		}
		event.cancelBubble = true;
	}

	function MoveDown( row )
	{
		if( row.nextSibling )
		{
			SetDirty();
			row.swapNode( row.nextSibling );
		}
		event.cancelBubble = true;
	}

	function Edit( code )
	{
		location = "IssueEdit.php?IssueNo=" + code;
	}

	function Delete( row )
	{
		SetDirty();
		table = row.parentNode;
		table.deleteRow( row.rowIndex );
	}

	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved your changes to the database!";
		}
	}
	function RemoveDirty()
	{
		dirty = false;
	}

	function CreateEntry()
	{
		location = "IssueEdit.php";
	}

</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><Button id="create" OnClick="CreateEntry()">Add Entry</Button>
	</table>
	</center>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	Adjust the order for the priority of the issues.
	<br> If you are the initiator of a issue if you agree its been completed then mark the issue as closed.
	<FORM action="IssuesManagerProcess.php" method="POST">
	<TABLE id="DataArea">
		<TR>
			<TH>Priority<TH>Description</TH><TH>Need<span style='color:green'>/Est</span><TH>Effort</TH><TH>Status<TH>Date Added<TH>Created By
		</TR>
<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$col = "";
		if( $row["Status"] == "Completed" )
		{
			$col = "style='color:red'";
		}
		echo "<TR $col OnClick=\"Edit($row[IssueNo])\">";
		echo "<TD><input name=\"Issues[]\" type=\"hidden\" value=\"$row[IssueNo]\">";
		echo "$row[Priority] ";
		switch($row["PriorityGrp"])
		{
			case "U":
				echo "Urgent";
			break;
			case "H":
				echo "High";
			break;
			case "M":
				echo "Medium";
			break;
			case "L" :
				echo "Low";
			break;
		}
		echo "<TD>$row[ShortDescription]</TD>";
		if( $row["NeededBy"] == "" )
		{
			echo "<TD  style='color:green' >$row[EstimatedDate]</TD>";
		}
		else
		{
			echo "<TD>$row[NeededBy]</TD>";
		}
		echo "<TD>$row[Effort]</TD>";
		echo "<TD>$row[Status]</TD>";
		echo "<TD>$row[DateAdded]</TD>";
		echo "<TD>$row[CreatedBy]</TD>";
		echo "<TD>$row[Responsablity]</TD>";

?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			</TD>
<?php
		echo "</TR>";
	}
?>
	</TABLE>
	<input id="update" OnClick="RemoveDirty()" type="submit" value="Update" disabled>
	</FORM>
<?php
	include "../MasterViewTail.inc";
?>
