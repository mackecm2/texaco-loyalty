<?php
//*----------------------------------------------------------------*//
//* Program Name : BonusApprover.php                               *//
//* Author       : Mike MacKechnie                                 *//
//* Date         : 23/06/2008                                      *//
//* Comments     :                                                 *//
//*              based on BonusManager.php, this will display any  *//
//*              bonuses that are pending approval. This screen    *//
//*              will be displayed only to "MPromo" user accounts  *//
//*----------------------------------------------------------------*//

	include_once "../include/Session.inc";
	include_once "../include/CacheCntl.inc";
	include_once "../include/DB.inc";

    $PromoCode = $_GET['promoCode'];
    $Comment =  $_GET['comment'];
    $sql = "SELECT * FROM BonusCriteria RIGHT JOIN BonusPoints USING ( PromotionCode ) WHERE PromotionCode = '".$PromoCode."'";
      $results = DBQueryExitOnFailure($sql);

	$Title = "Bonus Approval";
	$currentPage = "Bonuses";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	include "../MasterViewHead.inc";
?>

<script>
	function Reject( row )
	{
		location = "BonusApproverProcess.php?Approve=R&promoCode=" + row;
	}
	function CreateEntry()
	{
		location = "BonusEdit.php";
	}
	function ViewActive()
	{
		location = "BonusManager.php";
	}
	function Approvals()
	{
		location = "BonusApprovalManager.php";
	}
	function ViewAll()
	{
		location = "BonusManagerAll.php";
	}
		function BackToApp()
	{
		window.location = "../Admin/BonusApprovalManager.php";
	}
</script>
<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><Button id="create" OnClick="CreateEntry()">Add Entry</Button> <Button id="ViewAll" OnClick="ViewAll()">View All</Button> <Button id="Approvals" OnClick="Approvals()">Approvals</Button>
	</table>
	</center>
	<tr>
	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

	<b>Approve Promotion</b><p></p>
	<TABLE id="DataArea">
<?php
echo "<div align=center><center>";
	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<table border=0 cellpadding=0 cellspacing=0 width=50% bgcolor=#FEE0FA>";
		  echo "<tr><td><form name=BonusForm action=BonusApproverProcess.php method=POST>";
		      echo "<input type=hidden name=promocode value=$row[PromotionCode]>";
		      echo "<div align=center><center><table>";
		        echo "<tr>";
		          echo "<td>Promotion Code: </td>";
		          echo "<td bgcolor=#FFFFFF>$row[PromotionCode]</td>";
		        echo "</tr>";
		        echo "<tr>";
		          echo "<td>Promotion Description: </td>";
		          echo "<td bgcolor=#FFFFFF>$row[BonusName]</font></td>";
		        echo "</tr>";
		        echo "<tr>";
		          echo "<td>Beginning date: </td>";
		          echo "<td bgcolor=#FFFFFF>$row[StartDate]</font></td>";
		        echo "</tr>";
		        echo "<tr>";
		          echo "<td>Ending date: </td>";
		          echo "<td bgcolor=#FFFFFF>$row[EndDate]</font></td>";
		        echo "</tr>";
		        echo "<tr>";
		          echo "<td>Bonus Creation Date:</td>";
		          echo "<td bgcolor=#FFFFFF>$row[CreationDate]</td>";
		        echo "</tr>";
		        echo "<tr>";
		          echo "<td>Created By:</td>";
		          echo "<td bgcolor=#FFFFFF>$row[CreatedBy]</td>";
		        echo "</tr>";
		      echo "</table>";
		      echo "</center></div><hr>";
		      echo "<div align=center><center><table border=0 cellpadding=0 cellspacing=2>";
		        echo "<tr><td>IF</td>";
		          echo "<td>$row[FieldName]</td>";
		          echo "<td>$row[ComparisionType]</td>";
		          echo "<td>$row[ComparisionCrteria]</td>";
		          echo "<td>$row[Boolean]</td>";
		        echo "</tr>";
		        echo "</table>";
		        echo "</center></div>";
		        echo "<div align=center><center><table border=0 cellpadding=0 cellspacing=2>";
		        if ($row[FieldName] == 'SiteID')
		        {
		        	echo "<tr><td>Site Name(s):</td></tr>";
		        	$sitearray = explode(",",$row[ComparisionCrteria]);
		        	foreach($sitearray as $sitecode)
		        	{
		        		$sql2 = "SELECT SiteName FROM sites WHERE SiteNo = '".$sitecode."'";
       					$sitename = DBSingleStatQueryNoError($sql2);
       					echo "<tr><td>$sitename</td></tr>";
		        	}
		        }
		        else 
		        {
		        	if (!isset($row[FieldName]) or $row[FieldName] == '')
		     		{
		     	   		$field="**BLANK!!**";
		     		}
		     		else
		     		{
		     			$field=$row[FieldName];
		     		}		     	   
		          	echo "<tr><td>WARNING: No Site Code Specified, FieldName is $field</td></tr>";
		        }
		      echo "</table>";
		      echo "</center></div><hr>";
		      echo "<div align=center><center><table border=0 cellpadding=0 cellspacing=2>";
		        echo "<tr><td>THEN</td>";
		          echo "<td>$row[BonusPoints]</td>";
		          echo "<td>extra stars per</td>";
		          echo "<td>$row[PerQuantity]</td>";
		        echo "</tr>";
		      echo "</table>";
		      echo "</center></div><hr>";
		      echo "</p><div align=center><center><p>Bonus Points apply to $row[AppliesTo]</p>";
		      echo "</center></div><div align=center><center><p><br>";
		      if ( $row[ThresholdPts] > 0 OR $row[Threshold] > 0 )
		      {
		      	echo "<fieldset><legend>Threshold</legend>$row[Threshold] Points at $row[ThresholdPts]</p>";
		      	echo "</center></div><div align=center><center><p><br>";
		      	echo "If a Threshold is set then the calculation above is only applied if the Threshold is met. </fieldset>";
		      }
		      else
		      {
		      	echo "No thresholds set<br>";
		      }
     		  if ( $row[Exclude] )
     		  {
     		  	echo "Exclude from further calculations";
     		  }
			  else
		      {
		      	echo "Not excluded from further calculations";
		      }
		      echo "</center></div><div align=center><center><p><br>";
		      if ( $row[MaximumHits] > 0 )
		      {
		     	echo "Maximum hits for personal promotions $row[MaximumHits] </p>";
		      }
		      else 
		      {
		      	echo "No maximum hit value set </p>";
		      }
		      echo "</center></div><div align=center><center><p>Notes<br>";
		      
		      echo "<table border=0 cellpadding=10 cellspacing=0>";
              echo "<tr><td></td>";
              echo "<td><textarea rows=2 name=Comments cols=63>$row[Comments]</textarea></td>";
              echo "<td></td></tr></table></p>";
		      echo "</center></div><div align=center><center><table border=0 cellpadding=0";
		      echo "cellspacing=0 width=80%>";
		        echo "<tr>";
		          echo "<td width=30%><div align=center><center><p><input type=submit value=Approve ";
		          echo "name=Approve></td>";
		          echo "<td width=40% align=center><div align=center><center><p><input type=button ";
		          echo "value=Cancel OnClick=BackToApp() name=cancel></td>";
		          echo "<td width=30%><div align=center><center><p><input type=submit value=Reject ";
		          echo "name=Reject></td>";
		        echo "</tr>";
		      echo "</table>";
		      echo "</center></div>";
		    echo "</form>";
		    echo "</td>";
		  echo "</tr>";
		echo "</table><p></p>";
		echo "</center></div>";
        if ($Comment == 'Missing')
        { 
        	echo "<------ Please enter a comment saying why you want to reject this promotion ------>";
        }
	        if ($Comment == 'NotAuth')
        { 
        	echo "<------ You are not authorised to approve/reject this promotion ------>";
        }

	}
?>
	</TABLE>
<?php
	include "../MasterViewTail.inc";
?> 