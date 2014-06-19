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
		location = "BonusEdit.php?add=new";
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
	<table cellpadding = 10px width=80%><tr>
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
	
	<TABLE id="DataArea" width=80%>
		<TR>
			<TH><font size=2>Promotion</font></TH>
			<TH><font size=2>Description</font></TH>
			<TH><font size=2>Criteria Summary</font></TH>
			<TH><font size=2>Points/Quant</font></TH>
			<TH><font size=2>Applies To</font></TH>
			<TH><font size=2>Start Date</font></TH>
			<TH><font size=2>End Date</font></TH>
			<TH></TH>
		</TR>
<?php
	

$numrows = mysql_num_rows($results);
if( $numrows >0 )
{
	$activecount = 0;
	while( $row = mysql_fetch_assoc( $results ) )
	{
		$criteria = GetAbriviatedBonusCriteria( $row["PromotionCode"] );
		$today = date("Y-m-d");   
		if ($row["StartDate"] <= $today AND ($row["EndDate"] >= $today  OR !isset($row["EndDate"])) AND $row["Status"] != "Pending Approval")
			{
				$activecount = 1;
				echo "<TR OnClick=\"Edit(this)\">";
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
				echo "<TD></TD>\n";
				echo "</TR>";
			}

	}
	if( $activecount == 0 )
	{
		echo "<br>no current bonus schemes found<br />"	;
	}
	echo "</TABLE>";
	
}
else 
{
	echo "<TR>no bonus schemes found</TR>"	;
}
		
	
	
	include "../MasterViewTail.inc";
?>