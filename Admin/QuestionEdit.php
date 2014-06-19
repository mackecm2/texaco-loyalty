<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/QuestionaireInterface.php";

	$QuestionId = $_GET["QuestionId"];

	if( $QuestionId != "new" )
	{
		$QuestionData = GetQuestionData( $QuestionId );
		$Question = $QuestionData["QuestionText"];
		$Period = $QuestionData["VerifyPeriod"];
		$Type = $QuestionData["Type"]; 
		$QuestionOptions = GetQuestionOptions( $QuestionId, true );
		if( $Type == QuestionTypeList )
		{
			$optionsDisabled = "false";

		}
		else
		{
			$optionsDisabled = "true";
		}
	}
	else
	{
		$Type = 'B';
		$QuestionOptions = GetQuestionOptions( -1, true );;
		$Question = "";
		$Period = 365;
		$optionsDisabled = "true";
	}
	
	$Title = "Edit Question";
	$currentPage = "Config";
	$bodyControl = "onbeforeunload=\"LeavePage()\" onload=\"startup()\"";
	$cButton = "";
	$HelpPage = "EditQuestions";
	$helpID = "ManageQuestions";
	include "../MasterViewHead.inc";
	include "ManagerButtons.php";
?>
<script>
	var dirty = false;
	var disabled = <?php echo $optionsDisabled; ?>;
	var N = 2;
	function SetDirty()
	{
		dirty = true;
		document.getElementById("update").disabled = false;
//		document.getElementById("create").disabled = true;
	}

	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved you changes to the database!";
		}
	}
	function RemoveDirty()
	{
		dirty = false;
	}

	function DeleteOld( box )
	{
		SetDirty();
		event.cancelBubble = true;
	}


	function DeleteNew( row )
	{
		if( !disabled )
		{
			table = row.parentNode;
			if( table.lastChild != row )
			{
				table.deleteRow( row.rowIndex );
				if( table.firstChild == null )
				{
					AddRow( table );
				}
				SetDirty();
			}
		}
		event.cancelBubble = true;
	}

	function Edit( id )
	{
		window.location="EditQuestions.php?QuestionId="+ id;
		event.cancelBubble = true;
	}

	function AddRow( table )
	{
		var spare = document.getElementById("SpareRow");
		var newRow = spare.cloneNode( true );
		table.insertBefore(newRow );
		var nb = document.getElementsByName( "ToReplace" );
		nb[nb.length - 1].name = "N" + N;
		var nb = document.getElementsByName( "ToReplace2" );
		nb[nb.length - 1].name = "V" + N++;

	}

	function TextChanged( row )
	{
		if( row.parentNode.lastChild == row )
		{
			AddRow( row.parentNode );
		}
		SetDirty();
	}

	function DisableOptions()
	{
//		document.getElementById("optionsd").disabled = true;
		disabled = true;
		document.getElementById("optionsd").style.filter="progid:DXImageTransform.Microsoft.Alpha(opacity=50);";
		var o = document.getElementsByName("options");
		for( i = 0; i < o.length; i++ )
		{
			o[i].disabled = true;
		}
		SetDirty();
	}

	function EnableOptions()
	{
		disabled = false;
//		document.getElementById("optionsd").disabled = false;
		document.getElementById("optionsd").style.filter="";
		var o = document.getElementsByName("options");
		for( i = 0; i < o.length; i++ )
		{
			o[i].disabled = false;
		}

		SetDirty();
	}

	function startup()
	{
		if( disabled )
		{
			DisableOptions();
		}
		else
		{
			EnableOptions();
		}
		RemoveDirty();
	}

	function Swap( r1, r2 )
	{
		// This is to work round a bug in IE where 
		f1 = r1.lastChild.firstChild.checked;
		f2 = r2.lastChild.firstChild.checked;
		r1.swapNode( r2 );
		r1.lastChild.firstChild.checked = f1;
		r2.lastChild.firstChild.checked = f2;
	}

	function MoveUp( row )
	{
		if( row.parentNode.firstChild != row.previousSibling )
		{
			SetDirty();
			Swap( row, row.previousSibling );
		}
		event.cancelBubble = true;
	}

	function MoveDown( row )
	{
		if( row.nextSibling )
		{
			SetDirty();
			Swap( row, row.nextSibling );
		}
		event.cancelBubble = true;
	}


</script>

<table style="Display:none">
	<tr id="SpareRow"><td><input id="options" onchange="TextChanged(this.parentNode.parentNode)" name="ToReplace">
	<TD><Input id="OptionValues"  onchange="TextChanged(this.parentNode.parentNode)" name="ToReplace2">
	<TD width="16">
		<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
		<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
	</TD>
	<TD><img OnClick="DeleteNew(this.parentNode.parentNode)" src="trash.gif"></TD>
</table>

<center>
<table style="text-align: center">
	<FORM action="QuestionEditProcess.php" method="POST">
	<input name="QuestionId" type=hidden value="<?php echo $QuestionId; ?>">
	<tr><td><fieldset><legend>Question</legend><input size=80 name="Question" onchange="SetDirty()" value="<?php echo $Question; ?>"></fieldset>
	<tr><td><table><tr><td>
	<fieldset><legend>Verify Period</legend>
	<table>
	<tr><td style="text-align: right">Every Contact
	<td style="text-align: left"><?php DisplayRadioButton( "Period", 1, $Period, "onclick=\"SetDirty()\"" ); ?>
	<tr><td style="text-align: right">Annually
	<td style="text-align: left"><?php DisplayRadioButton( "Period", 365, $Period, "onclick=\"SetDirty()\"" ); ?>
	<tr><td style="text-align: right">Every 2 Years
	<td style="text-align: left"><?php DisplayRadioButton( "Period", 730, $Period, "onclick=\"SetDirty()\"" ); ?>
	<tr><td style="text-align: right">Every 3 Years
	<td style="text-align: left"><?php DisplayRadioButton( "Period", 1095, $Period, "onclick=\"SetDirty()\"" ); ?>
	<tr><td style="text-align: right">Never
	<td style="text-align: left"><?php DisplayRadioButton( "Period", 99999, $Period, "onclick=\"SetDirty()\"" ); ?>
	</TABLE>
	</fieldset>
	<td>
	<fieldSet><legend>Field Type</legend>
	<TABLE>
	<tr><td style="text-align: right">Boolean 
	<td style="text-align: left"><?php DisplayRadioButton( "QuestionType", QuestionTypeBoolean, $Type, "onclick=\"DisableOptions()\"" ); ?>
	<tr><td style="text-align: right">Drop Down List
	<td style="text-align: left"><?php DisplayRadioButton( "QuestionType", QuestionTypeList, $Type, "onclick=\"EnableOptions()\"" ); ?>
	<tr><td style="text-align: right">Integer
	<td style="text-align: left"><?php DisplayRadioButton( "QuestionType", QuestionTypeInteger, $Type, "onclick=\"DisableOptions()\"" ); ?>
	<tr><td style="text-align: right">Text
	<td style="text-align: left"><?php DisplayRadioButton( "QuestionType", QuestionTypeText, $Type, "onclick=\"DisableOptions()\"" ); ?>
	</TABLE>
	</fieldset>
	</table>
	<tr><td>
	<fieldset  id="optionsd" title="Type the options in the box and press the tab key to add extra rows" >
	<legend>Options</legend>
	<table>
	<tr><th>Option<th>Reponse Value<th><th>Active</tr>
<?php

	if( $Type == QuestionTypeList )
	{
		$cntl = "disabled ";
	}
	else
	{
		$cntl = "";
	}
	while( $row = mysql_fetch_assoc( $QuestionOptions ) )
	{
		echo "<tr>\n";
		echo "<td><input id=\"options\" onchange=\"TextChanged(this.parentNode.parentNode)\" name=\"D$row[OptionValue]\" value=\"$row[OptionText]\">\n";
		echo	"<TD>$row[OptionValue]";
?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			<TD>
<?php
		DisplayCheckBox( "A".$row["OptionValue"], $row["Active"] == 'Y', "onclick=\"DeleteOld(this)\"" );
		echo "</TD>\n";

		
	}
?>
	<tr><td><input id="options" onchange="TextChanged(this.parentNode.parentNode)" name="N1">
	<TD><Input id="OptionValues"  onchange="TextChanged(this.parentNode.parentNode)" name="V1">
	<TD width="16">
		<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
		<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
	</TD>
	<TD><img OnClick="DeleteNew(this.parentNode.parentNode)" src="trash.gif"></TD>

	</table>
	</fieldSet>
	<tr><td>
	<input id="update" OnClick="RemoveDirty()" type="submit" value="Update" disabled>
	&nbsp;&nbsp;&nbsp;&nbsp;<button onclick="window.location='ManageQuestions.php'">Cancel</button>
	</table>
	</center>
<?php
	include "../MasterViewTail.inc";
?>
