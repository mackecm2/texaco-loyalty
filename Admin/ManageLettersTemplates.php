<?php

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/Locations.php";

	$filePath =  MailTemplateLocations;

	$c = 0;
	if( isset( $_POST["Records"] ) )
	{
		$a = $_POST["Records"];
		foreach( $a as $value )
		{
			if( isset( $_POST[$value] ) )
			{
				EnableLetterTemplate( $value, $c );
				$c++;
			}
			else
			{
				DeleteLetterTemplate( $value );
			}
		}

	}

	$code = "";
	if( isset( $_POST["Code"] ) )
	{
		$code = " '$_POST[Code]'";
	}

	$Desc = "";
	if( isset($_POST["Description"] ))
	{
		$Desc = $_POST["Description"];
	}

	$Temp = "";
	if( isset($_FILES['userfile']) )
	{
		$Temp = $_FILES['userfile']['name'];
	}

	if( $Temp != "" )
	{
		move_uploaded_file( $filePath. $Temp, $Temp);
	}
	else
	{
		if( isset($_POST["Template"] ))
		{
			$Temp = $_POST["Template"];
		}
	}

	if( isset( $_POST["Action"] )  )
	{
		switch( $_POST["Action"] )
		{
		case "Add":
			if( $Desc != "" )
			{
				InsertLetterTemplate( $Desc, $Temp );
			}
			break;
		case "Update":
			if( $code != "" && $Desc != "" )
			{
				UpdateLetterTemplate( $code, $Desc, $Temp );
			}
			break;
		}
	}

	$results = GetLettersCodes(  );

	$Title = "Letters Manager";
	$currentPage = "Config";
	$HelpPage = "AddingTemplates";
	$cButton = "";
	$but = "Letters";
	include "../MasterViewHead.inc";
	include "ConfigButtons.php";

?>
<script>
	var dirty = false;
	var dataDirty = false;
	function SetDirty()
	{
		dirty = true;
	}

	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved you changes to the database!";
		}
	}

	function DataChanged()
	{
		dataDirty = true;
	}

	function Update()
	{
		if( dataDirty )
		{
			if( confirm( "Changing the description of a entry will be reflected in all historical records are you sure you wish to proceed" ) )
			{
				dirty = false;
				document.getElementById("Action").value = "Update";
				document.forms[0].submit();
			}
		}
		else
		{
			dirty = false;
			document.forms[0].submit();
		}
	}

	function Add()
	{
		dirty = false;
		document.getElementById("Action").value = "Add";
		document.forms[0].submit();
	}

	function CopyValues( Code, Desc, Temp )
	{
		var dataDirty = false;
		document.getElementById("Code").value = Code;
		document.getElementById("Description").value = Desc;
		document.getElementById("Template").value = Temp;
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
		if( row.parentNode.firstChild != row )
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


//function Edit()
//{
//	doc = document.getElementById("Template").value;
//	if( doc != "" )
//	{
//		oApp = new ActiveXObject("Word.Application");
//		oApp.visible= true;
//		oDoc = oApp.Documents.Add( "<?php echo MailTemplateLocations;?>" + doc );
//	}
//	else
//	{
//		alert( "You need to select a template" );
//	}
//}

</script>

	<center>

<form enctype="multipart/form-data" action="ManageLettersTemplates.php" method="post">

	<input type=hidden name="Action">

	<table width=95%><tr><td width = 70%>
	<div style="width:95%; height:550px; border-style:inset; background-color: white; overflow:auto">
	<Table width=100%>
<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<tr onmouseover=\"this.style.backgroundColor='lavender'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"CopyValues('$row[LetterCode]','$row[Description]', '$row[Template]' )\"><td width=100%>$row[Description]<td>$row[Template]\n";
?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			<TD>
<?php
		echo "<input type=\"hidden\" name=\"Records[]\" value=\"$row[LetterCode]\">";
		DisplayCheckBox( $row["LetterCode"], $row["Active"] == 'Y', "onclick=\"SetDirty()\"" );
	}
?>
	</Table>
	</div>
	<td style="vertical-align: top;">
	<input type="hidden" name="Code">
	<fieldset>
	<Table>
	<tr><td>Description: <td><input id="Description" name="Description" onchange="DataChanged()">
	<tr><td>Template: <td><input id="Template" name="Template" onchange="DataChanged()">
	<tr><td><td>
	<button onclick="Add()">Add</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<button onclick="Update()">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="reset">&nbsp;&nbsp;&nbsp;&nbsp;
	</table>
	</fieldset>

	</table></center>
<?php
	include "../MasterViewTail.inc";
?>
