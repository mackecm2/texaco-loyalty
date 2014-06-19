<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/MessagesInterface.php";

	$results = GetCurrentMessages();

	$Title = "Messages Manager";
	$currentPage = "Messages";
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

	function Edit( row )
	{
		code = row.firstChild.innerText;
		location = "MessagesEdit.php?MessageNo=" + code;
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
		location = "MessagesEdit.php";
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

	Adjust the order in which messages are applied within each level.
	<FORM action="MessageManagerProcess.php" method="POST">
	<TABLE id="DataArea">
		<TR>
			<TH>MessageNo</TH>
			<TH width = "20%" align="left">Message</TH>
			<TH width = "25%" align="left">Criteria Summary  </TH>
			<TH width = "15%" align="left">Start Date  </TH>
			<TH width = "15%" align="left">Expiry Date  </TH>
			<TH width = "10%" align="left">Priority</TH>
			<TH width = "12%" align="left">Active</TH>
		</TR>
<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$criteria = GetAbreviatedMessageCriteria( $row["MessageNo"] );

		echo "<TR OnClick=\"Edit(this)\">";
		echo "<TD><input name=\"MessageNo[]\" type=\"hidden\" value=\"$row[MessageNo]\">$row[MessageNo]</TD>";
		echo "<TD>$row[Description]</TD>";
		echo "<TD>$criteria</TD>";
		echo "<TD>$row[StartDate]</TD>";
		echo "<TD>$row[ExpiryDate]</TD>";

?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			</TD>
<?php
		echo "<TD>$row[Active]</TD>";
		echo "<TD><img OnClick=\"Delete(this.parentNode.parentNode)\" src=\"trash.gif\"></TD>\n";
		echo "</TR>";
	}
?>
	</TABLE>
	<input id="update" OnClick="RemoveDirty()" type="submit" value="Update" disabled>
	</FORM>
<?php
	include "../MasterViewTail.inc";
?>
