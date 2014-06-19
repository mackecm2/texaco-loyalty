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
		location = "BonusEdit.php";
	}
	function ViewAll()
	{
		location = "BonusManagerAll.php";
	}
</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><Button id="create" OnClick="CreateEntry()">Add Entry</Button> <Button id="ViewAll" OnClick="ViewAll()">View All</Button>
	</table>
	</center>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	Adjust the order in which bonuses are applied within each level.
	<FORM action="BonusManagerProcess.php" method="POST">
	<TABLE id="DataArea">
		<TR>
			<TH>Promotion</TH>
			<TH>Description</TH>
			<TH>Criteria Summary</TH>
			<TH>Points/Quant</TH>
			<TH>Applies To</TH>
			<TH></TH>
			<TH>Start Date</TH>
			<TH>End Date</TH>
			<TH>Exclude</TH>
		</TR>
<?php
	

$numrows = mysql_num_rows($results);
if( $numrows >0 )
{

	while( $row = mysql_fetch_assoc( $results ) )
	{
		$activecount = 0;
		$criteria = GetAbriviatedBonusCriteria( $row["PromotionCode"] );
		$today = date("Y-m-d");   
		if ($row["StartDate"] < $today AND $row["EndDate"] > $today)
			{
				$activecount = $activecount + 1;
				echo "<TR OnClick=\"Edit(this)\">";
				echo "<TD><input name=\"Promos[]\" type=\"hidden\" value=\"$row[PromotionCode]\">$row[PromotionCode]</TD>";
				echo "<TD>$row[BonusName]</TD>";
				echo "<TD>$criteria</TD>";
				echo "<TD>$row[BonusPoints]/$row[PerQuantity]";
				if( $row["Threshold"] != "" and $row["Threshold"]  != 0 )
				{
					echo " ($row[Threshold])";
				}
				echo "</TD>";
				echo "<TD>$row[AppliesTo]</TD>";
?>	
					<TD width="16">
						<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
						<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
					</TD>
<?php
				echo "<TD>$row[StartDate]</TD>";
				echo "<TD>$row[EndDate]</TD>";
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
		
		</FORM>
<?php
	}
	if( $activecount ==0 )
	{
		echo "<br>no current bonus schemes found<br />"	;
	}
	echo "</TABLE>";
	
}
else 
{
	echo "<TR>no current bonus schemes found</TR>"	;
}
		
	
	
	include "../MasterViewTail.inc";
?>