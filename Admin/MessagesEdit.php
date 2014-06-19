<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/MessagesInterface.php";

	function OutputIFRow( $name, $fieldList, $fieldValue, $compList, $compValue, $mode, $singleList, $value, $Boolean )
	{
		echo "<TR id=\"$name\">\n<TD>\n";
		echo "<select name=\"FieldName[]\" onChange=\"ChangedField(this.parentNode.parentNode)\">\n";
		DisplaySelectOptions( $fieldList, $fieldValue );
		echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<select name='Comparison[]' id='comps'  onChange='ChangedComp(this.parentNode.parentNode)'>\n";
		DisplaySelectOptions( $compList, $compValue );
		echo $compList;
		echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<input name='Mode[]' type='hidden' value=\"$mode\">\n";
		if( $mode == "List" )
		{
			echo "<select name='Single[]' id='single'>\n";
			DisplaySelectOptions( $singleList, $value );
			echo "</select>\n";
		}
		else
		{
			echo "<select name='Single[]' id='single' style='display:none'>\n";
			echo "</select>\n";
		}

		if( $mode == "Multi" )
		{
			echo "<input name='Range[]' value=\"$value\"  onClick='processMultiSelect(this.parentNode.parentNode)'>\n";
		}
		else
		{
			echo "<input name='Range[]' style='display:none' onClick='processMultiSelect(this.parentNode.parentNode)'>\n";
		}
		if( $mode == "Text" )
		{
			echo "<input name=\"FreeText[]\" id=\"freeText\" value=\"$value\" >\n";
		}
		else
		{
			echo "<input name=\"FreeText[]\" id=\"freeText\" style=\"display:none\" >\n";
		}
		echo "</td>\n";
		echo "<td>\n";
		echo "<select name=\"Boolean[]\" id=\"boolean\" onChange=\"BooleanChanged(this.parentNode.parentNode, this)\">\n";
		$opt = array( "" => "", "AND" => "AND", "OR" => "OR" );
		DisplaySelectOptions( $opt,  $Boolean );
		echo "</select>\n";
		echo "</td>\n";
		echo "<TD><img OnClick=\"Delete(this.parentNode.parentNode)\" src=\"trash.gif\"></TD>\n";
		echo "</tr>\n";
	}

	$fieldList = GetMessagesFieldNameList();

	$promo = "";
	$promoName = "";
	$startDate = "";
	$endDate = "";
	$Priority = "";
	$Active = "";
	$DisplayTimes = "";
	$LogEvents = "";
	$Web = "";
	$Terminal = "";

	if( isset( $_GET["MessageNo"] ) )
	{
		$MessageNo = $_GET["MessageNo"];

		$currentSettings = GetCurrentMessageSettings( $MessageNo );

		if( $currentSettings )
		{
			$Description = $currentSettings["Description"];
			$MessageText = $currentSettings["MessageText"];
			$startDate = $currentSettings["StartDate"];
			$endDate = $currentSettings["ExpiryDate"];
			$Priority = $currentSettings["Priority"];
			$Active = $currentSettings["Active"];
			$DisplayTimes = $currentSettings["DisplayTimes"];
			$LogEvents = $currentSettings["LogEvents"];
			$Web = $currentSettings["Web"];
			$Terminal = $currentSettings["Terminal"];

		}
	}
	else
	{
		#echo "This must be a new message";
		$MessageNo = GetNextMessageNo();
		$Description ='';
		$MessageText = '';



	}



	$Title = "Message Editor";
	$currentPage = "Messages";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	include "../MasterViewHead.inc";

?>


<script language="JavaScript" src="overlib_mini.js"></script>
<script language="JavaScript" src="DatePicker.js"></script>
<script>

	currentRow = null;

	function LeavePage()
	{

	}

	function requestData( url, row )
	{
		// Hide the alternative boxes
		row.cells[2].children[1].style.display = "none";
		row.cells[2].children[2].style.display = "none";
		row.cells[2].children[3].style.display = "none";

		row.cells[2].children[1].selectedIndex = -1;
		row.cells[2].children[2].value = "";
		row.cells[2].children[3].value = "";


		var doc = data.XMLDocument;
		doc.async = true;
		data.ondatasetcomplete = finishedLoading;
		doc.load( url );
		document.body.style.cursor = 'progress';
	}

	function ChangedField(row)
	{
		currentRow = row;
		var fieldSel = row.cells[0].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		requestData("MessageComparisons.php?FieldName=" + fn, row );
	}

	function ChangedComp(row)
	{
		currentRow = row;
		var fieldSel = row.cells[0].firstChild;
		var compSel =  row.cells[1].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		var comp = compSel.options[compSel.selectedIndex].innerText;
		requestData("MessageComparisons.php?FieldName=" + fn + "&Comp=" + comp, row  );
	}

	function ComparisonData( row, chil )
	{
		var lis =  row.cells[1].firstChild;

		lis.options.length = 0;
		var oOption = document.createElement("OPTION");
		lis.options.add(oOption);
		var count = 0;
		while( chil )
		{
			oOption = document.createElement("OPTION");
			lis.options.add(oOption);
			oOption.innerText = chil.text;
			chil = chil.nextSibling;
			count++;
		}
		if( count == 1 )
		{
			lis.selectedIndex = 1;
			ChangedComp(row);
		}
	}

	function ListData( row, chil )
	{

		row.cells[2].children[0].value = "List";
		lis = row.cells[2].children[1];
		lis.style.display = "";

		lis.options.length = 0;
		oOption = document.createElement("OPTION");
		lis.options.add(oOption);
		while( chil )
		{
			oOption = document.createElement("OPTION");
			lis.options.add(oOption);
			oOption.innerText = chil.text;
			oOption.value = chil.attributes.getNamedItem('value').value ;
			chil = chil.nextSibling;
		}
	}

	function TextData( row )
	{
		row.cells[2].children[0].value = "Text";
		row.cells[2].children[3].style.display = "";
	}

	function MultiSelectData( row )
	{
		row.cells[2].children[0].value = "Range";
		row.cells[2].children[2].style.display = "";

	}

	function finishedLoading()
	{
		var xxml = data.XMLDocument.lastChild;
		var dataType = xxml.nodeName;
		var row = currentRow;

		switch( dataType )
		{
		case "Comps":
			ComparisonData( row, xxml.firstChild );
			break;
		case "List":
			ListData( row, xxml.firstChild );
			break;
		case "Text":
			TextData( row );
			break;
		case "Multi":
			MultiSelectData( row );
			break;
		case "error":
			alert( xxml.firstChild.text );
			break;
		}
		document.body.style.cursor = 'auto';
	}

	function processMultiSelect( row  )
	{
		var fieldSel = row.cells[0].firstChild;
		var compSel =  row.cells[1].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		var comp = compSel.options[compSel.selectedIndex].innerText;
		var valBox = row.cells[2].children[2];
		var val = valBox.value;
		var request = "MessageMultiSelectDialog.php?FieldName=" + fn + "&Comp=" + comp + "&Value=" + val;
		res = window.showModalDialog( request, "", "resizable:yes" );
		if( res != undefined )
		{
			valBox.value = res;
		}
	}

	function AddRow( table )
	{
		var spare = document.getElementById("SpareRow");
		var newRow = spare.cloneNode( true );
		table.insertBefore(newRow );
	}

	function BooleanChanged( row, boolSel )
	{
		if( boolSel.selectedIndex == 0 )
		{
			if( row.parentNode.lastChild != row )
			{

			}
		}
		else
		{
			if( row.parentNode.lastChild == row )
			{
				AddRow( row.parentNode );
			}
		}
	}

	function Delete( row )
	{
		table = row.parentNode;
		table.deleteRow( row.rowIndex );
		if( table.firstChild == null )
		{
			AddRow( table );
		}
		else
		{
			BooleanChanged( table.lastChild,  table.lastChild.cells[3].firstChild );
		}
	}


</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<table style="Display:none">
<?php
		OutputIFRow( "SpareRow", $fieldList, "", "", "", "", "", "" );
?>
</table>

<form name="BonusForm" action="MessageEditProcess.php" method="POST">
<Table>
<TR><TD>Message No: </TD><TD><input name="MessageNo" value="<?php echo $MessageNo; ?>"></TD></TR>
<TR><TD>Description: </TD><TD><input type="text" name="Description" size="50" maxlength="50" value="<?php echo $Description; ?>">

<TR><TD valign="top">Message: </TD><TD>
<textarea rows="5" name="MessageText" cols="40"  ><? echo "$MessageText";?></textarea>
</TD></TR>
<TR><TD>Start date: </TD><TD><input type="text" name="StartDate" size="20" value="<?php echo $startDate; ?>">
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.StartDate');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD></TR>
<TR><TD>Ending date: </TD><TD><input type="text" name="EndDate"	size="20" value="<?php echo $endDate; ?>">
	<!-- ggPosX and ggPosY are set, so popup goes where you tell it -->
	<a href="javascript:ggPosX=5;ggPosY=200;show_yearly_calendar('BonusForm.EndDate');" onMouseOver="window.status='Date Picker'" onMouseOut="window.status='';"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD></TR>
</TABLE>
<?php
		if( $Priority != "" )
		{
			echo "<input type=\"hidden\" name=\"Priority\" value=\"$Priority\">";
		}
?>
<HR>
<BR>Recipient Criteria
	<table id="Rules">
<?php
		$currentCriteria = GetCurrentMessageCriteria( $MessageNo );
		$rows = mysql_num_rows( $currentCriteria );
		#echo "Num rows - $rows";
		if( mysql_num_rows( $currentCriteria ) > 0 )
		{
			while( $row = mysql_fetch_assoc( $currentCriteria ) )
			{

				$FieldName = $row["FieldName"];
				$comp = $row["ComparisonType"];

				$compList = GetMessageFieldComparisonOptions( $FieldName );
				$singleList = GetMessageFieldValues( $row["Populate"] );

				OutputIFRow( "", $fieldList, $FieldName, $compList, $comp, $row["PopulateType"], $singleList, $row["ComparisonCriteria"], $row["Boolean"] );
			}
		}
		else
		{
			OutputIFRow( "", $fieldList, "", array( "" => "      " ),  "", "", "", "", "" );
		}
?>
	</table>
<HR>
	<BR>
	<table>
	<tr>
		<td>
			Display Times
		</td>
		<td>
			<input type="text" name="DisplayTimes"  size="4" maxlength="4" value="<?php echo $DisplayTimes; ?> ">
		</td>
	</tr>


<?php
		$opt = array( "" => "", "Y" => "Y", "N" => "N" );

		echo"<tr><td>Log Events</td><td>";
		echo "<select name=\"LogEvents\"\n";
		DisplaySelectOptions( $opt,  $LogEvents );
		echo "</select>\n</td></tr>";

		echo"<tr><td>Web</td><td>";
		echo "<select name=\"Web\"\n";
		DisplaySelectOptions( $opt,  $Web );
		echo "</select>\n</td></tr>";

		echo"<tr><td>Terminal</td><td>";
		echo "<select name=\"Terminal\"\n";
		DisplaySelectOptions( $opt,  $Terminal );
		echo "</select>\n</td></tr>";

		echo"<tr><td>Active</td><td>";
		echo "<select name=\"Active\"\n";
		DisplaySelectOptions( $opt,  $Active );
		echo "</select>\n</td></tr></table>";
?>



	<BR>
	<input type=submit>


</form>

<xml id="data"></xml>
</BODY>
</HTML>
<?php
	include "../MasterViewTail.inc";
?>