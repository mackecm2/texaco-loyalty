<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/BonusInterface.php";

	$results = GetCurrentBonuses();

	$Title = "Priority Manager";
	$currentPage = "Bonuses";
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
		location = "BonusEdit.php?promoCode=" + code;
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
		location = "BonusEdit.php";
	}
	function ViewAll()
	{
		location = "BonusManagerAll.php";
	}
	function Approvals()
	{
		location = "BonusApprovalManager.php";
	}
	function UserAdmin()
	{
		location = "ManageUsers.php";
	}
</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><Button id="create" OnClick="CreateEntry()">Add Entry</Button> <Button id="ViewAll" OnClick="ViewAll()">View All</Button>
<?php
	if ($_SESSION["grp"] == 'MAdmin' OR $_SESSION["grp"] == 'SAdmin' OR $_SESSION["grp"] == 'MPromo')
	{
    	echo "<Button id=Approvals OnClick=Approvals()>Approvals</Button><Button id=UserAdmin OnClick=UserAdmin()>User Admin</Button";
    }
 ?>	
	</td>
	</table>
	</center>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	<b>Active Promotions</b><p></p>
	
	<TABLE id="DataArea">
		<TR>
				</TR>

	echo "</TABLE>";
	

	
	
	include "../MasterViewTail.inc";
?>