<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
//	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../DBInterface/BonusInterface.php";

//	$results = GetUnsatisifiedCardRequestBatches( 7 );

	$PromotionCodes = GetBonusList();

	$PromotionCodes[""] = "";

	$Title = "Welcome Pack Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="WelcomePacks";
	$HelpPage = "WelcomePacks";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";

$startDate = "";

#	This script can take a while to process
set_time_limit(0);

?>
<script language="JavaScript" src="overlib_mini.js"></script>
<script language="JavaScript" src="DatePicker.js"></script>

<script>

	function WebRequestsToClient( batch, type )
	{
		if( batch == "" )
		{
			window.location="WelcomePackFile.php";
		}
		else
		{
			alert( type );
			window.location="WelcomePackFile.php?Repeat="+ batch+"&Type="+type;
		}
	}

	function CheckNewBatchData()
	{
		StartDate = document.getElementsByName("StartDate")[0].value;
		Date1 = document.getElementsByName("Date1")[0].value;
		Date2 = document.getElementsByName("Date2")[0].value;
		Date3 = document.getElementsByName("Date3")[0].value;
		Promo1 = document.getElementsByName("Promo1")[0].value;
		Promo2 = document.getElementsByName("Promo2")[0].value;
		Promo3 = document.getElementsByName("Promo3")[0].value;

		if( StartDate == '' || Date1 == '' || Promo1 == ''  )
		{
			alert( "You must set at least a start date, end date and one bonus" );
			return false;
		}

		if( Date1 < StartDate || (Date2 != "" && Date2 < Date1) || ( Date3 != "" && Date3 < Date2) )
		{
			alert( "Dates must be in order" );
			return false;
		}

		if( (Promo2 != "" && Date2 == "") || (Promo2 == "" && Date2 != "" ) )
		{
			alert( "Specify both promotion code and end date" );
			return false;
		}

		if( (Promo3 != "" && Date3 == "") || (Promo3 == "" && Date3 != "" ) )
		{
			alert( "Specify both promotion code and end date" );
			return false;
		}
		return true;
	}


</script>

<center>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<TABLE>
<TR valign=top><TD>
<table>
<TR><TH colspan = 2>Welcome Packs To Send
<tr><th>Batch Time<th>Registered<th>NMC<th>Bonus Only<th>No Bonus</tr>
<?php
	$results3 = UnwelcomedCount();
	$results2 = GetWelcomePackBatches( );

	$RadioGrp = "";

	if( mysql_num_rows( $results3 ) == 0 ) 
	{
 		echo "<tr><td>";
		echo "New Batch</td><td>0</td></tr>\n";
	}
	else
	{
		$checked = " checked";
		while( $row = mysql_fetch_assoc( $results3 ) )
		{
			echo "<tr><td>";
			echo $row["BatchGroup"] ;
			echo "</td><td>$row[NoRecords]</td>";
			echo "</tr>\n";
			$RadioGrp .= "<input type=\"radio\" name=SpecialCriteria value=\"$row[BatchGroup]\" $checked>$row[BatchGroup]";
			$checked = "";
		}
	}

	while( $row = mysql_fetch_assoc( $results2 ) )
	{

	//	echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]')\"><td>";
		echo "<tr><td>";
		echo $row["BatchTime"];
		if($row["NoRecords"] > 0 and $row["NMC"] == 0 and $row["Standard"] == 0 and $row["NoBonus"] == 0 )
		{
			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'All')\">$row[NoRecords]";
		}
		else
		{

			echo "<td>$row[NoRecords]";

			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'NMC')\">$row[NMC]";
			
			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'Standard')\">$row[Standard]";

			echo "<td onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"WebRequestsToClient('$row[BatchTime]', 'NoBonus')\">$row[NoBonus]";

		}
		echo "</tr>\n";
	}
	echo "</table>\n";
?>
	<td>
	<fieldset>
	<legend>New Batch Bonus Schedule</legend>
	<form name="BonusForm" action="WelcomePackCreateBatch.php" onSubmit="return CheckNewBatchData();" method="POST">
	<Table>
	<TR><TD colspan=2 align=right>Start date: </TD><TD><input type="text" name="StartDate" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.StartDate');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD>
	
	</TR>

	<TR>
	<TD>
	<SELECT name="Promo1">
	<?php  DisplaySelectOptions( $PromotionCodes, "" ) ?>
	</SELECT>
	<TD>to </TD><TD><input type="text" name="Date1" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.Date1');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD>
	</TR>

	<TR>
	<TD>
	<SELECT  name="Promo2">
<?php  DisplaySelectOptions( $PromotionCodes, "" ) ?>
	</SELECT>
	<TD>to </TD><TD><input type="text" name="Date2" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.Date2');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD>
	</TR>

	<TR>
	<TD>

	<SELECT  name="Promo3">
<?php  DisplaySelectOptions( $PromotionCodes, "" ) ?>
	</SELECT>
	<TD>to </TD><TD><input type="text" name="Date3" size="20" value="<?php echo $startDate; ?>">  
	<!-- ggPosX and ggPosY not set, so popup will autolocate to the right of the graphic -->
	<a href="javascript:show_calendar('BonusForm.Date3');" onMouseOver="window.status='Date Picker'; " onMouseOut="window.status=''"><img src="show-calendar.gif" width=24 height=22 border=0></a></TD>
	<TD>
	</TR>
	
	</table>
	<input type=submit  value="Create Batch">
	<?php echo $RadioGrp; ?>
	<br>New member cycle<input type=checkbox name=NMC>
	</form>
	</fieldset>
	<button onclick="window.location='NewMemberCycleManager.php'">New Member Cycle</button>
<?php
	echo "</TABLE></center>\n";
	include "../MasterViewTail.inc";
?>

