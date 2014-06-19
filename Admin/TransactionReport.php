<?php 
	//******************************************************************
	//
	// TransactionReport.php ..... based on BonusEdit.php, hence all these functions below
	//
	//  MRM 10/09/2008 - reports on transactions a given Site Code within a specified timeframe
	//
	//
	//
	//******************************************************************
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/BonusInterface.php";
	
	function OutputIFRow( $name, $fieldList, $fieldValue, $compList, $compValue, $mode, $singleList, $value, $Boolean )
	{
		echo "<TR id=\"$name\">\n<TD>\n";
		echo "<select name=\"FieldName[]\" onChange=\"ChangedField(this.parentNode.parentNode)\">\n";
		DisplaySelectOptions( $fieldList, $fieldValue );
		echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<select name='Comparision[]' id='comps'  onChange='ChangedComp(this.parentNode.parentNode)'>\n";
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
	
	$Title = "Transaction Report";
	$currentPage = "Bonuses";
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
	
function finishedLoding()
	{
		var xxml = data.XMLDocument.lastChild;
		var dataType = xxml.nodeName;
		var row = currentRow;
        switch( dataType )
		{
		case "Comps":
			ComparisionData( row, xxml.firstChild ); 
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
		data.ondatasetcomplete = finishedLoding;
		doc.load( url );
		document.body.style.cursor = 'progress';
	}

	function ChangedField(row)
	{
		currentRow = row;
		var fieldSel = row.cells[0].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		requestData("Comparisions.php?FieldName=" + fn, row );
	}

	function ChangedComp(row)
	{
		currentRow = row;
		var fieldSel = row.cells[0].firstChild;
		var compSel =  row.cells[1].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		var comp = compSel.options[compSel.selectedIndex].innerText;
		requestData("Comparisions.php?FieldName=" + fn + "&Comp=" + comp, row  );
	}

	function ViewActive()
	{
		location = "BonusApprovalManager.php";
	}
</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<table style="Display:none">
<?php
		OutputIFRow( "SpareRow", $fieldList, "", "", "", "", "", "", "" );
?>
</table>
<h1>Transaction report</h1>
<form name="BonusForm" action="../Reporting/RunTransactionReport.php" method="POST">
<Table>
<TR><TD>SiteCode: </TD><TD><input name="SiteCode" value="<?php echo $sitecode; ?>"></TD><td rowspan=3>This report may take 30 seconds or more to run</td></TR>
<TR><TD>Beginning date: </TD><TD><input type="text" name="StartDate" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_yearly_calendar('BonusForm.StartDate');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD></TR>
<TR><TD>Ending date: </TD><TD><input type="text" name="EndDate"	size="20" value="<?php echo $endDate; ?>">  
	<!-- ggPosX and ggPosY are set, so popup goes where you tell it -->
	<a href="javascript:ggPosX=5;ggPosY=200;show_yearly_calendar('BonusForm.EndDate');" onMouseOver="window.status='Date Picker'" onMouseOut="window.status='';"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD></TR>
</TABLE>
<HR>
	<table border="0" cellpadding="0" cellspacing="0" width="80%">
    <tr>
    <td><p align="left"><p><input type="submit" value="Run Report"></p></td>
    <td><p align="right"><input type="button" value="Cancel" OnClick="ViewActive()"></p></td></tr>
	</table>

</form>

<?php
	include "../MasterViewTail.inc";
?>