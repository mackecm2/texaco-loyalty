<?php 
global $Reporting;
$Reporting = 1;
include "../include/Session.inc";
include "../RegularProcessing/includes/LiabilityFunctions.inc";
include "../include/CacheCntl.inc";

$currentPage = "Reporting";
$Title = "Liability Accrual Forecasting";
include "../MasterViewHead.inc";


function DisplayMonth($x)
{
	$time = strtotime("+$x months");
	$displaymonth = date('F Y',$time);
	$month = date('n',$time);
	$year = date('Y',$time);
	$firstday = date('Y-m-d',mktime(0, 0, 0, $month, 1, $year)); 
	echo '<option value="'.$firstday.'">'.$displaymonth.'</month>';
}
/********************************************************
 *  M A I N   P R O C E D U R E
 ********************************************************/
?>

<tr>
<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: outset; MARGIN-LEFT:100">
<h2 align="center">Liability Accrual Forecast</h2>
<form method="post" name="form1" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<p>
<table style="WIDTH: 400px; HEIGHT: 59px" border=0 cellspacing=0 cellpadding=3 width=400 align=center>
  <tbody>
  <tr>
    <td>Start</td>
    <td>End</td>
    <td>
      <p align="center">#months</p></td></tr>
  <tr>
    <td>
<?php
echo '<select name="startmonth">';
for($x=0;$x<24;$x++)
{
	DisplayMonth($x);
}
echo '</select>';
?>
</td>
<td>
<?php
echo '<select name="endmonth">';
for($x=0;$x<24;$x++)
{
	DisplayMonth($x);
}
echo '</select>';
?>
<td>
<?php      
echo '<select name="no_of_months">';
for($x=1;$x<25;$x++)
{
	echo '<option value="'.$x.'">'.$x.'</>';
}
echo '</select>'; 
?>     
</td></tr></tbody></table>

<p align="center"><input value="Run Forecast" type="submit" name="submit"></p> </form>
<p align="center">&nbsp;</p>
<?php

   	$db_user = "pma001";
	$db_pass = "amping";
	$slave = connectToDB( ReportServer, AnalysisDB );

	$startmonth = $_POST[startmonth];
   	$yourage = $_POST[no_of_months];
   	$endmonth = $_POST[endmonth];
   	$NoOfDays = ($yourage/12)*365;
   	$monthdate = date("m"); 
   	$yeardate = date("Y"); 
   	$date1 = $yeardate."-".$monthdate."-01";
   
   	$thismonth = $startmonth;
   	$timeago = strtotime($$thismonth.' -'.$yourage.' months');    
   	$endDate = date('Y-m-d', $timeago);  
   	$sql = "SELECT DATEDIFF( '$thismonth', '$date1' )";
   	$subtracteddate = DBSingleStatQuery($sql) ;
   	$yourdays = $NoOfDays - $subtracteddate;
   	$LastMonthRegPoints = 0;
	$LastMonthRegCount = 0;
	$LastMonthUnRegPoints = 0;
	$LastMonthUnRegCount = 0;

  	if( $_POST[endmonth] && $endmonth >= $startmonth )
  	{
		echo "<table border=0 cellspacing=0 cellpadding=3 width=60% align=center>";
	  	echo "<tbody align=middle>";
	  	echo "<tr bgcolor=#ffeb9c>";
	  	echo "<td colspan=6>";
	    echo "<p><strong>Liability Accrual after $yourage+ months inactivity</strong></p></td></tr>";
		echo "<tr bgcolor=#ffeb9c>";
	    echo "<td>Month</td>";
	    echo "<td colspan=2>Reg</td>";
	    echo "<td colspan=2>Unreg</td>";
	    echo "<td></td></tr>";
	  	echo "<tr bgcolor=#ffeb9c>";
	    echo "<td></td>";
	    echo "<td>#cards</td>";
	    echo "<td>points</td>";
	    echo "<td>#cards</td>";
	    echo "<td>points</td>";
	    echo "<td>Liability</td>";
		echo "</tr>";
	
		while ($thismonth <= $endmonth)
		{
			//------------------------------------------------------------------------------------------------------------------------------------
			//------- Section 1 - Registered Cards -----------------------------------------------------------------------------------------------
			//------------------------------------------------------------------------------------------------------------------------------------
			
			
			$timestamp = 'WeeklyTable';
			$sql = RegisteredLiability($timestamp, $yourdays);
			
			$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
		
			echo "<tr>";
			$thisdisplaymonth = date('d/m/Y', strtotime($thismonth));	
			echo "<td>$thisdisplaymonth</td>";
		
			$RegPoints = 0;
			$RegCount = 0;
		
			while( $row = mysql_fetch_assoc( $slaveRes ) )
			{
					if ( $row['Balance'] > 0 )
				{
					$RegPoints += $row['Balance'];
					$RegCount ++;
				}
			}
			$ThisMonthRegCount = $RegCount - $LastMonthRegCount;
			$ThisMonthRegPoints = $RegPoints - $LastMonthRegPoints;
			echo "<td>$ThisMonthRegCount</td>";  
			echo "<td>$ThisMonthRegPoints</td>";
			
			//------------------------------------------------------------------------------------------------------------------------------------
			//---------- Section 2 - Unregistered Cards ------------------------------------------------------------------------------------------
			//------------------------------------------------------------------------------------------------------------------------------------
			$UnRegPoints = 0;
			$UnRegCount = 0;
			
			$sql = "select CardNo, StoppedPoints from LiabilityCardsWeeklyTable where LastSwipeDate < '$endDate'";
			$slaveRes = mysql_query( $sql, $slave ) or die( mysql_error($slave) );
			
			while( $row = mysql_fetch_assoc( $slaveRes ) )
			{
				#	First add the points to the removed points total
				if ( $row['StoppedPoints'] > 0 )
				{
					$UnRegPoints += $row['StoppedPoints'];
					$UnRegCount ++;
				}
			}
			$ThisMonthUnRegCount = $UnRegCount - $LastMonthUnRegCount;
			$ThisMonthUnRegPoints = $UnRegPoints - $LastMonthUnRegPoints;
			echo "<td>$ThisMonthUnRegCount</td>"; 
			echo "<td>$ThisMonthUnRegPoints</td>";
		  	$Liability = ($ThisMonthUnRegPoints+$ThisMonthRegPoints)/100;
			echo "<td>&pound".number_format($Liability, 2, '.', ',')."</td>"; 
			echo "</tr>";
			$thismonth = strtotime(date("Y-m-d", strtotime($thismonth)) . " +1 month");
			$thismonth = date("Y-m-d",$thismonth);
			$timeago = strtotime(date("Y-m-d", strtotime($thismonth)).' -'.$yourage.' months');   
			$endDate = date('Y-m-d', $timeago);
			$sql = "SELECT DATEDIFF( '$thismonth', '$date1' )";
			$subtracteddate = DBSingleStatQuery($sql) ;
			$yourdays = $NoOfDays - $subtracteddate;
		   	$LastMonthRegPoints = $RegPoints;
			$LastMonthRegCount = $RegCount;
			$LastMonthUnRegPoints = $UnRegPoints;
			$LastMonthUnRegCount = $UnRegCount;
		}
		
		echo "</tbody></table>";
  	}
  	else 
  	{
  		echo "<p align=center><strong><font size=1>This report may take up to 30 seconds to run </strong></font></p>";
  		if( $endmonth < $startmonth )
  		{
  			echo "<p><strong>End Date is earlier than Start Date</strong></p>";
  		}
  	}
	
echo "</td></tr></Table>";
include "../MasterViewTail.inc";
?>