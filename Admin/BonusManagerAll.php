<?php

	include_once "../include/Session.inc";
	include_once "../include/CacheCntl.inc";
	include_once "../DBInterface/BonusInterface.php";

	$results = GetAllBonuses();

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
		location = "BonusEdit.php?prev=All&promoCode=" + code;
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
		location = "BonusEdit.php?add=new";
	}
	function ViewActive()
	{
		location = "BonusManager.php";
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
	<table cellpadding = 10px width=80%><tr>
	<td><Button id="create" OnClick="CreateEntry()">Add Entry</Button> <Button id="ViewActive" OnClick="ViewActive()">View Active</Button>
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

	<table border="0" cellpadding="0" cellspacing="8">
      <tr>
      	<td width="16">
		<img src="uparrow.gif" Title="Move Up">
		<img src="downarrow.gif" Title="Move Down">
		</td>
        <td><font size=2>Adjust the order in which bonuses are applied.</font></td>
        <td width="100"></td>
        <td bgcolor="#FF3300">Rejected</td>
        <td bgcolor="#00F5FF">Expired</td>
        <td bgcolor="#FCD116">Pending</td>
      </tr>
      <tr>
      	<td width="16">
		</td>
        <td><font size=2>Click the 'Confirm Delete' button, after you have clicked on the trash can(s), to delete promotions.</font></td>
        <td width="100"></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </table>
	<FORM action="BonusManagerProcess.php" method="POST">
	<input id="update" OnClick="RemoveDirty()" type="submit" value="Confirm Delete" disabled>
	<TABLE id="DataArea">
		<TR>
			<TH><font size=2>Promotion</font></TH>
			<TH><font size=2>Description</font></TH>
			<TH><font size=2>Criteria Summary</font></TH>
			<TH><font size=2>Points/Quant</font></TH>
			<TH><font size=2>Applies To</font></TH>
			<TH></TH>
			<TH><font size=2>Start Date</font></TH>
			<TH><font size=2>End Date</font></TH>
			<TH><font size=2>Exclude</font></TH>
		</TR>
<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$criteria = GetAbriviatedBonusCriteria( $row["PromotionCode"] );

        switch ($row["Status"])
		{
		case "P":
        	echo "<TR bgcolor=#FCD116 OnClick=\"Edit(this)\">";
        	break;
        case "A":
        	echo "<TR OnClick=\"Edit(this)\">";
        	break;
        case "R":
        	echo "<TR bgcolor=#FF3300 OnClick=\"Edit(this)\">";
        	break;
        case "E":
        	echo "<TR bgcolor=#00F5FF OnClick=\"Edit(this)\">";
        	break;
        default:	
        	echo "<TR OnClick=\"Edit(this)\">";
        	break;			
        }
		echo "<TD><font size=2><input name=\"Promos[]\" type=\"hidden\" value=\"$row[PromotionCode]\">$row[PromotionCode]</font></TD>";
		echo "<TD><font size=2>$row[BonusName]</font></TD>";
		echo "<TD><font size=2>$criteria</font></TD>";
		echo "<TD><font size=2>$row[BonusPoints]/$row[PerQuantity]";
		if( $row["Threshold"] != "" and $row["Threshold"]  != 0 )
		{
			echo " ($row[Threshold])";
		}
		echo "</font></TD>";
		echo "<TD><font size=2>$row[AppliesTo]</font></TD>";
?>
			<TD width="16"><font size=2>
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			</font></TD>
<?php
		echo "<TD><font size=2>$row[StartDate]</font></TD>";
		echo "<TD><font size=2>$row[EndDate]</font></TD>";
		if( $row["Exclude"] == 1 )
		{
			echo "<TD><input type=\"checkbox\" disabled checked></TD>";
		}
		else
		{
			echo "<TD><input type=\"checkbox\" disabled></TD>";
		}
		echo "<TD><img OnClick=\"Delete(this.parentNode.parentNode)\" src=\"trash.gif\"></TD>\n";
		echo "</TR>";
	}
?>
	</TABLE>
		<input id="update" OnClick="RemoveDirty()" type="submit" value="Confirm Delete" disabled>
	</FORM>
<?php
	include "../MasterViewTail.inc";
?>
