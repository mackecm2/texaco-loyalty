<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/Locations.php";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/TrackingInterface.php";
	include "../DBInterface/TransactionsInterface.php";
	include "../DBInterface/RedemptionInterface.php";
	include "../DBInterface/OrdersInterface.php";
	include "../DBInterface/MonthlyInterface.php";
	include "../DBInterface/StatementInterface.php";
	include "../DBInterface/FraudInterface.php";
	include "../DBInterface/GeneralInterface.php";
	$TimeStamp = GetSQLTime1();

	if( isset( $_REQUEST["AccountNo"] ) )
	{
		$AccountNo = $_REQUEST["AccountNo"];
	}
	else
	{
		$AccountNo = "";
	}

	if( isset( $_REQUEST["MemberNo"] ) )
	{
		$MemberNo = $_REQUEST["MemberNo"];
	}
	else
	{
		$MemberNo = "";
	}

	if( isset( $_REQUEST["CardNo"] ) )
	{
		$CardNo = $_REQUEST["CardNo"];
	}
	else
	{
		$CardNo = "";
	}

	if( isset( $_REQUEST["AccountType"] ) )
	{
		$AccountType = $_REQUEST["AccountType"];
	}
	else
	{
		$AccountType = "";
	}
	
	if( $AccountNo == "" )
	{
		header("Location: SelectMember.php");
		exit();
	}


	if( isset( $_POST["RedemptionId"] ) and $_POST["RedemptionId"] != "" )
	{
		if( CancelRequest( $AccountNo, $_POST["RedemptionId"] ) )
		{
			$Msg = "Redemption Cancelled";
		}
		else
		{
			$Msg = "Redemption Not Cancelled";
		}
	}
	$Title = "Card Holder";
	$currentPage = "Card Holder";
	include "../MasterViewHead.inc";
	
?>
	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">

<form action="DisplayMemberHistory.php" method=POST>
	<center>
	<Table width="90%"><tr>
<?php
	echo "<input type=\"hidden\" name=\"AccountNo\" value=\"$AccountNo\">";
	echo "<input type=\"hidden\" name=\"MemberNo\" value=\"$MemberNo\">";
	echo "<input type=\"hidden\" name=\"CardNo\" value=\"$CardNo\">";
	echo "<input type=\"hidden\" name=\"AccountType\" value=\"$AccountType\">";
	echo "<input type=\"hidden\" name=\"RedemptionId\" id=\"RedemptionId\">";
	echo "<td>";
	DisplayCheckBox( "Transactions", isset( $_POST["Transactions"] ), "" );
	echo "Transactions";

	echo "<td>";
	DisplayCheckBox( "Monthlys", isset( $_POST["Monthlys"] ), "" );
	echo "Monthly Card Sum";

	echo "<td>";
	DisplayCheckBox( "MonthlyAccount", isset( $_POST["MonthlyAccount"] ), "" );
	echo "Monthly Account Sum";



	echo "<td>";
	DisplayCheckBox( "Statements", isset( $_POST["Statements"] ), "" );
	echo "Statements";

	echo "<td>";
	DisplayCheckBox( "Tracking", isset( $_POST["Tracking"] ), "" );
	echo "Tracking";

	echo "<td>";
	DisplayCheckBox( "Redemptions", isset( $_POST["Redemptions"] ), "" );
	echo "Redemptions";

	echo "<td>";
	DisplayCheckBox( "Campaigns", isset( $_POST["Campaigns"] ), "" );
	echo "Campaigns";

	//	Let's see if there's any fraud investigations for this account ... MRM Mantis 1893 27 APR 2010

	if ( CheckFraudHistory( $AccountNo ) )
	{
		echo "<td>";
		DisplayCheckBox( "Investigations", isset( $_POST["Investigations"] ), "" );
		echo "Investigations";
	}
	

	

	$links = "<TR><TD>Jump to:<TD><a href=\"#top\"> - Top </a> -";
	if( isset( $_POST["Transactions"] ) )
	{
		$links .= "<a href=\"#trans\">Transactions </a> - ";
	}
	if( isset( $_POST["Monthlys"] ) )
	{
		$links .= "<a href=\"#monthly\">Monthly Sum </a> - ";
	}
	if( isset( $_POST["MonthlyAccount"] ) )
	{
		$links .= "<a href=\"#MonthlyAccount\">Monthly Account Sum </a> - ";
	}

	if( isset( $_POST["Statements"] ) )
	{
		$links .= "<a href=\"#state\">Statement</a> - ";
	}
	if( isset( $_POST["Tracking"] ) )
	{
		$links .= "<a href=\"#tracking\">Tracking</a> - ";
	}
	if( isset( $_POST["Redemptions"] ) )
	{
		$links .= "<a href=\"#redempts\">Redemptions</a> - ";
	}
	if( isset( $_POST["Campaigns"] ) )
	{
		$links .= "<a href=\"#camps\">Campaigns</a> - ";
	}
	if( isset( $_POST["Investigations"] ) )
	{
		$links .= "<a href=\"#invests\">Investigations</a>";
	}



?>
	
	<tr><td colspan=5 align=right>
		<button onclick="CheckAll()">Check All</button>&nbsp;&nbsp;&nbsp;&nbsp;
		<button onclick="UncheckAll()">Uncheck All</button>&nbsp;&nbsp;&nbsp;&nbsp;
		<input style="width: 100" type="Submit" value="Enter">&nbsp;&nbsp;&nbsp;&nbsp;
	<tr><td colspan=5 align=center>
		<button disabled onclick="PrintablePage(true)"> Printable/Saveable version</button>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button onclick="window.location='DisplayMember.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo";?>'">Cancel</button>
	</table>
	</center>
</form>
<script>

<?php
	if( CheckPermisions( PermissionsRestrictedHistoryPrinting ) )
	{
		echo "RestricedPrint=true;";
	}
	else
	{
		echo "RestricedPrint=false;";
	}
?>
	function CheckAll()
	{
		document.getElementById( "Transactions" ).checked = true;
		document.getElementById( "Monthlys").checked = true;
		document.getElementById( "MonthlyAccount").checked = true;
		document.getElementById( "Statements").checked = true;
		document.getElementById( "Tracking").checked = true;
		document.getElementById( "Redemptions").checked = true;
		document.getElementById( "Campaigns").checked = true;
		document.getElementById( "Investigations").checked = true;
	}

	function UncheckAll()
	{
		document.getElementById( "Transactions" ).checked = false;
		document.getElementById( "Monthlys" ).checked = false;
		document.getElementById( "MonthlyAccount" ).checked = false;
		document.getElementById( "Statements" ).checked = false;
		document.getElementById( "Tracking" ).checked = false;
		document.getElementById( "Redemptions" ).checked = false;
		document.getElementById( "Campaigns").checked = false;
		document.getElementById( "Investigations").checked = false;
	}

	function CancelProduct( ProductNo )
	{
		document.getElementById( "RedemptionId" ).value = ProductNo;
		document.forms[0].submit();
	}

 function PrintablePage()
 {
	ok = false;
	if( document.getElementById( "Tracking" ).checked)
	{
		if( RestricedPrint )
		{
			if( confirm('You are requesting the printing of restricted data\n.  Are you sure you wish to do this?' ) )
			{
				ok = true;
			}
		}
		else
		{
			alert( "You are not allowed to print the tracking history uncheck the option and try again");
		}
	}
	else
	{
		ok = true;
	}

	var red = 0;

	red +=	document.getElementById( "Transactions" ).checked;
	red +=	document.getElementById( "Monthlys" ).checked;
	red +=	document.getElementById( "MonthlyAccount" ).checked;

	if(  red > 1 && confirm('The options "Transactions", "Monthly Sums" and "Account Sums"\n display different views of the same information.\n  Are you sure you wish to do this?' ) )
	{
		ok = true;
	}

	if( ok )
	{
		document.forms[0].action = 'PrintableMemberHistory.php';
		document.forms[0].submit();
	}
 }
 function SetDirty()
	{
		dirty = true;
		document.getElementById("update").disabled = false;
	}

	function DisableSubmit()
	{
		document.getElementById("update").disabled = true;
	}

	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved your changes to the database!\n Press OK to lose your changes.";
		}
	}

	function RemoveDirty()
	{
		dirty = false;
	}
	
	function SetDateField( cur, label )
	{
		var sel = document.getElementById(label);
		if( cur.checked )
		{
			sel.value = "<?php echo $TimeStamp; ?>";
		}
		else
		{
			sel.value = "";
		}
		SetDirty();
	}
	
	function DateSet( but, label )
	{
		SetDirty();
		SetDateField( but, label );
	}
	
</script>

<TABLE>

<?PHP
	if( isset( $_POST["Transactions"] ) )
	{
		echo $links;
		echo "<a id=\"trans\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Transactions</I><TD>\n";

		if( $AccountType == "G" )
		{
			$results = GetGroupLoyaltyTransactionHistory( $MemberNo );
		}
		else 
		{
			$results = GetTransactionHistory( $AccountNo );
		}
		
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 1 );
		}
		else
		{
			echo "No Entries";
		}

	}

	if( isset( $_POST["Monthlys"] ) )
	{
		echo $links;
		echo "<a id=\"monthly\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Monthly Sum</I><TD>\n";
		
		if( $AccountType == "G" )
		{
			$results = GetMonthlyGroupCardHistory( $MemberNo );
		}
		else 
		{
		$results = GetMonthlyCardHistory( $AccountNo );
		}
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 1 );
		}
		else
		{
			echo "No Entries";
		}
	}

	if( isset( $_POST["MonthlyAccount"] ) && $AccountType <> "G" )
	{
		echo $links;
		echo "<a id=\"MonthlyAccount\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Monthly Account Sum</I><TD>\n";
		$results = GetMonthlyAccountHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 0 );
		}
		else
		{
			echo "No Entries";
		}
	}

	if( isset( $_POST["Statements"] ) && $AccountType <> "G" )
	{
		echo $links;
		echo "<a id=\"state\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Statement</I><TD>\n";

		$results = GetStatementHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 0 );
		}
		else
		{
			echo "No Entries";
		}
	}

	if( isset( $_POST["Tracking"] ) )
	{
		echo $links;
		echo "<a id=\"tracking\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Tracking</I><TD>\n";
		
		if( $AccountType == "G" )
		{
			$results = GetGroupTrackingHistory( $MemberNo );
		}
		else 
		{
			$results = GetTrackingHistory( $AccountNo );
		}
		
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 0 );
		}
		else
		{
			echo "No Entries";
		}
	}
	if( isset( $_POST["Redemptions"] ) )
	{
		echo $links;
		echo "<a id=\"redempts\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Redemptions</I><TD>\n";

		$results = GetRedemptionHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
		// Show field headers
		echo "<table width = 100%><tr bgcolor=\"#6699FF\" class = \"headertext\">";
		echo "<td>Date<td>Product Id<td>Description<td>Merchant<td>Type<td>Quantity<td>TotalCost<td>Agent<td>Status\n";
		$count = 1;
		while($row = mysql_fetch_assoc($results))
		{
			$count++;
			if ($count & 1)
			{
				$color = "#99CCFF";
				$font = "#004080";
			}
			else
			{
				$color = "#ccffff";
				$font = "#004080";
			}
			echo "<tr class = \"bodytext\" bgcolor=$color>\n" ;

			echo "<td>$row[Date]<td>$row[ProductId]<td>$row[Description]<td>$row[MerchantName]<td>$row[Type]<td>$row[Quantity]<td>$row[TotalCost]<td>$row[Agent]<td>$row[StatusDescrip]\n";
			if( $row["Status"] == 'P' and $row["AccountNo"] == $AccountNo )
			{
				echo "<td><button onclick=\"CancelProduct( $row[RedeptionId] )\">Cancel</button>\n";
			}
		}
		echo"</table>\n" ;

		}
		else
		{
			echo "No Entries";
		}
	}

	if( isset( $_POST["Campaigns"] ) )
	{
		echo $links;
		echo "<a id=\"camps\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Campaigns</I><TD>\n";

		$results = GetCampaignHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 0 );
		}
		else
		{
			echo "No Entries";
		}

		unset($results);




		#	Now display the personal campaigns data

		echo "<TR><TD style=\"vertical-align: top\"><I>Personal Campaigns</I><TD>\n";

		$results = GetPersonalCampaignHistory( $AccountNo );
		if( mysql_num_rows( $results ) > 0 )
		{
			DisplayTable( $results, 0 );
		}
		else
		{
			echo "No Entries";
		}

	}
	
		if( isset( $_POST["Investigations"] ) )
	{
		echo $links;
		echo "<a id=\"invests\"></a>";
		echo "<TR><TD style=\"vertical-align: top\"><I>Investigations</I><TD>\n";
//........ insert a form here
		$results = GetFraudHistory( $AccountNo );
		
		if( mysql_num_rows( $results ) > 0 )
		{
			$row = mysql_fetch_assoc($results);
			echo "<table border=0 cellspacing=0 cellpadding=0 width=100% align=center><tbody><tr><td>";
		   	if( $row['ConfirmSpend1SentDate'] )
		   	{
		   		echo FraudHistoryDisplay( $row['ConfirmSpend1SentDate'], $row['ConfirmSpend1ReturnedDate'], $row['ConfirmSpend1Comments'], 'ConfirmSpend1' );
		   	}
			if( $row['ConfirmSpend2SentDate'] )
			{
				echo FraudHistoryDisplay( $row['ConfirmSpend2SentDate'], $row['ConfirmSpend2ReturnedDate'], $row['ConfirmSpend2Comments '],  'ConfirmSpend2' );
			}
	        
            if( $row['ProofOfReceiptsSentDate'] )
            {
            	echo FraudHistoryDisplay( $row['ProofOfReceiptsSentDate'], $row['ProofOfReceiptsReturnedDate'], $row['ProofOfReceiptsComments'],  'ProofOfReceipts' );        
            }
	        
            if( $row['AccountClosedDate'] )
            {
            	echo FraudHistoryDisplay( '', $row['AccountClosedDate'], $row[' AccountClosedComments'],  'AccountClosed' );    
            }
	               
            if( $row['AccountClearedDate'] )
            {
            	echo FraudHistoryDisplay( '', $row['AccountClearedDate'], $row['AccountClearedComments'],  'AccountCleared' );
            }
	        
            echo "</td></tr>
            <tr><td><fieldset>
			<legend>Fraud Investigation Status</legend><div align=center>";
            ?>
			<NOBR class="FieldLabel">No Action <?php DisplayRadioButton( "FraudStatus", "0", $row['FraudStatus'], $fraudstatus, $row['FraudStatusSetDate']) ?></NOBR>&nbsp;
			<NOBR class="FieldLabel">Under Investigation <?php DisplayRadioButton( "FraudStatus", "1", $row['FraudStatus'], $fraudstatus, $row['FraudStatusSetDate']) ?></NOBR>
			<NOBR class="FieldLabel">Previously Investigated <?php DisplayRadioButton( "FraudStatus", "2", $row['FraudStatus'], $fraudstatus, $row['FraudStatusSetDate']) ?></NOBR>&nbsp;
			<NOBR class="FieldLabel">Cleared <?php DisplayRadioButton( "FraudStatus", "3", $row['FraudStatus'], $fraudstatus, $row['FraudStatusSetDate']) ?></NOBR>&nbsp;
			<NOBR class="FieldLabel">Fraud <?php DisplayRadioButton( "FraudStatus", "4", $row['FraudStatus'], $fraudstatus, $row['FraudStatusSetDate']) ?></NOBR>
			</fieldset></td></tr></tbody></table>
			<?php 
				  	
		}
		else
		{
			echo "No Entries";
		}
	}
	
	echo "</TABLE>";
	include "../MasterViewTail.inc";
?>