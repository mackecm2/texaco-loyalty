<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/QuestionaireInterface.php";

	$results = GetCurrentQuestions( true );

	$Title = "Question Manager";
	$currentPage = "Config";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	$HelpPage = "ManageQuestions";
	$cButton = "";
	$but = "Questions";
	$helpID = "ManageQuestions";
	include "../MasterViewHead.inc";
	include "ConfigButtons.php";

	// Ignore all this
	$questions = mysql_num_rows( $results );

	$maxPerCol = 120;

	$cols = ($questions / $maxPerCol) + 1;

	if( $cols > 3 )
	{
		$cols = 3;
		$maxPerCol = ($question / $cols) + 1;
	}


?>
<script>
	var dirty = false;
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

	function Swap( r1, r2 )
	{
		// This is to work round a bug in IE where 
		f1 = r1.lastChild.firstChild.checked;
		f2 = r2.lastChild.firstChild.checked;
		r1.swapNode( r2 );
		r1.lastChild.firstChild.checked = f1;
		r2.lastChild.firstChild.checked = f2;
		r1.style.backgroundColor='';
		r2.style.backgroundColor='';
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

	function Delete( id )
	{
		SetDirty();
		event.cancelBubble = true;
	}

	function Edit( id )
	{
		RemoveDirty();
		document.getElementById("paction").value = id;
	//	window.location="QuestionEdit.php?QuestionId="+ id;
		document.forms[0].submit();
		event.cancelBubble = true;
	}

	function CreateEntry()
	{
		RemoveDirty();
		document.getElementById("paction").value = "create";
		document.forms[0].submit();
	//	window.location="QuestionEdit.php?QuestionId=new";		
	}

</script>

<center>
	<FORM action="QuestionManagerProcess.php" method="POST">
	<input type="hidden" id=paction name="paction" value="update">

	<div style="width:50%; height:500px; border-style:inset; background-color: white; overflow:auto; ">
	<table>
	<tr>


<?php
	$closeTable = "";	
	$count = 0;
	while( $row = mysql_fetch_assoc( $results ) )
	{
		if( $count % $maxPerCol == 0 )
		{
			echo "$closeTable<td><table><tr><th>Question<th><th>Active<th>Web<th>Dawleys</tr>";
			$closeTable = "</table>";
		}
		$count++;		
		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"Edit($row[QuestionId])\">\n";
		echo "<td><input name=\"Questions[]\" type=hidden value=\"$row[QuestionId]\">$row[QuestionText]</td>\n";
?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			<TD>
<?php
		DisplayCheckBox( "A". $row["QuestionId"], $row["Active"] == 'Y', "onclick=\"Delete($row[QuestionId])\"" );
echo "<td>";
		DisplayCheckBox( "W". $row["QuestionId"], $row["Web"] == 'Y', "onclick=\"Delete($row[QuestionId])\"" );
echo "<td>";
		DisplayCheckBox( "D". $row["QuestionId"], $row["Dawleys"] == 'Y', "onclick=\"Delete($row[QuestionId])\"" );

	}
	echo "$closeTable";
?>
	</table>
	</div>
	<input id="update" OnClick="RemoveDirty()" type="submit" value="Update" disabled>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<Button id="create" OnClick="CreateEntry()">Add Entry</Button>
	</center>
<?php
	include "../MasterViewTail.inc";
?>

