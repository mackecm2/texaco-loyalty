<?php 
	//******************************************************************
	//
	// BonusEdit.php
	//
	//  MRM spelling mistake in line 321 corrected 17/03/08 ... "Promotion Description: "
	//  MRM spelling mistake in line 371 corrected 18/03/08 ... "Department Value"
	//  MRM spelling mistake in line 380 corrected 19/03/08 ... "if the Threshold is met."
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

	$fieldList = GetBonusFieldNameList();

	$promo = "";
	$promoName = "";
	$startDate = "";
	$endDate = "";
	$appliesTo = "";
	$BonusPoints = "0";
	$PerQuantity = "100";
	$Exclude = "";
	$Priority = "";
	$MaximumHits = "";
	$Threshold = "";
	$ThresholdPts = "";
	
	if( isset( $_GET["promoCode"] ) )
	{
		$promo = $_GET["promoCode"];
		
		$currentSettings = GetCurrentBonusSettings( $promo );

		if( $currentSettings )
		{
			$promoName = $currentSettings["BonusName"];
			$startDate = $currentSettings["StartDate"];
			$endDate = $currentSettings["EndDate"];
			$appliesTo = $currentSettings["AppliesTo"];
			$BonusPoints = $currentSettings["BonusPoints"];
			$PerQuantity = $currentSettings["PerQuantity"];
			$Priority = $currentSettings["Priority"];
			$Threshold = $currentSettings["Threshold"];
			$ThresholdPts = $currentSettings["ThresholdPts"];
			$MaximumHits = $currentSettings["MaximumHits"];
			if( $currentSettings["Exclude"] == 1 ) 
			{
				$Exclude = " checked";
			}
		}
	}

	$Title = "Bonus Editor";
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

	function ComparisionData( row, chil )
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

		function processMultiSelect( row  )
	{
		var fieldSel = row.cells[0].firstChild;
		var compSel =  row.cells[1].firstChild;
		var fn = fieldSel.options[fieldSel.selectedIndex].innerText;
		var comp = compSel.options[compSel.selectedIndex].innerText;
		var valBox = row.cells[2].children[2];
		var val = valBox.value;
		var request = "MultiSelectDialog.php?FieldName=" + fn + "&Comp=" + comp + "&Value=" + val;
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

	function ViewActive()
	{
		location = "BonusManager.php";
	}
	
	function ViewAll()
	{
		location = "BonusManagerAll.php";
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

<form name="BonusForm" action="BonusEditProcess.php" method="POST">
<Table>
<TR><TD>Promotion Code: </TD><TD><input name="promoCode" value="<?php echo $promo; ?>"></TD></TR>
<TR><TD>Promotion Description: </TD><TD><input name="promoName" value="<?php echo $promoName; ?>"></TD></TR>
<TR><TD>Beginning date: </TD><TD><input type="text" name="StartDate" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.StartDate');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a>&nbsp; <img
          src="question-icon31.gif" width="24" height="22"
          alt="The promotion will start at 00:00:00 on this date"></TD></TR>
<TR><TD>Ending date: </TD><TD><input type="text" name="EndDate"	size="20" value="<?php echo $endDate; ?>">  
	<!-- ggPosX and ggPosY are set, so popup goes where you tell it -->
	<a href="javascript:ggPosX=5;ggPosY=200;show_yearly_calendar('BonusForm.EndDate');" onMouseOver="window.status='Date Picker'" onMouseOut="window.status='';"><img src="show-calendar.gif" width=24 height=22 border=0></a>&nbsp; <img
          src="question-icon31.gif" width="24" height="22"
          alt="The promotion will end at 23:59:59 on this date"></TD></TR>
</TABLE>
<?php
		if( $Priority != "" )
		{
			echo "<input type=\"hidden\" name=\"Priority\" value=\"$Priority\">";
		}
		if( isset( $_GET["add"] ) )
		{
			$newpromo = $_GET["add"];
			echo "<input type=hidden name=newpromo value=$_GET[add]>";
		}
?>
<HR>
<BR>IF
	<table id="Rules">
<?php
		$currentCriteria = GetCurrentBonusCriteria( $promo );

		if( mysql_num_rows( $currentCriteria ) > 0 )
		{
			while( $row = mysql_fetch_assoc( $currentCriteria ) )
			{
				$FieldName = $row["FieldName"];
				$comp = $row["ComparisionType"];

				$compList = GetBonusFieldComparisionOptions( $FieldName );
				$singleList = GetBonusFieldValues( $row["Populate"] );
				
				OutputIFRow( "", $fieldList, $FieldName, $compList, $comp, $row["PopulateType"], $singleList, $row["ComparisionCrteria"], $row["Boolean"] );
			}
		}
		else
		{
			OutputIFRow( "", $fieldList, "", array( "" => "      " ),  "", "", "", "", "" );
		}
?>
	</table>
<HR>
THEN
	<BR>

	<input name="Pts" value="<?php echo $BonusPoints; ?>"> star
	per <input name="PerQuantity" value="<?php echo $PerQuantity; ?>">
	<br>
	Bonus Points apply to
	<SELECT name="AppliesTo">
	<?php
		$opt = array( ""=>"" ,"Visit" =>"Visit", "Total" => "Total Value", "Dept" => "Department Value", "Product" => "Product Value", "Quantity" => "Product Quantity", "PeriodSpend" => "Period Spend");
		DisplaySelectOptions( $opt, $appliesTo );
	?>
	</SELECT>
	<BR>
	<fieldset><legend>Threshold</legend>
		Threshold<input name="Threshold" value="<?php echo $Threshold; ?>"> 
		Points at Threshold<input name="ThresholdPts" value="<?php echo $ThresholdPts; ?>">
	<br>
		If a Threshold is set then the calculation above is only applied if the Threshold is met.

	</fieldset>
	Exclude from further calculations
	<input type="checkbox" name="Exclude" <?php echo $Exclude; ?> title="If checked the cost of this product is excluded from any higher level bonus calculations">
	<Br>
	Maximum hits for personal promotions<input type="text" name="MaximumHits" value="<?php echo $MaximumHits; ?>">
	<BR>
	<table border="0" cellpadding="0" cellspacing="0" width="80%">
  <tr>
  <?php 
  if( isset( $_GET["promoCode"] ) )
  {
  	echo "<td><p align=left><p></p></td>";
  }
  else 
  {
  	echo "<td><p align=left><p><input type=submit value='Submit Promotion'></p></td>";
  }
  
  if( isset( $_GET["prev"] ) )   // is prev set to "All"?
  {
  	$goback = "ViewAll";
  }
  else 
  {
  	$goback = "ViewActive";
  }  
 ?>
    <td><p align="right"><input type="button" value="Cancel" OnClick="<?php  echo $goback; ?>()"></p></td></tr>
	</table>
		
</form>

<xml id="data"></xml>
</BODY>
</HTML>
<?php
	include "../MasterViewTail.inc";
?>