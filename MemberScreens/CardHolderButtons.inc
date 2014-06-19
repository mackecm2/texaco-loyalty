	<script>
		function replaceCard()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "replaceCard";
				document.getElementById("BigForm").submit();
			}
			else
			{
				if( window.showModalDialog('ReplaceMember.php<?php echo "?AccountNo=$AccountNo&MemberNo=$MemberNo"; ?>',0,'center:yes;resizable:no;dialogHeight:300px') )
				{
					window.location="ReplaceMemberProcess.php?Mode=RC<?php echo "&AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>";
				}
			}
		}
		function additionalCard()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "additionalCard";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('AdditionalMember.php<?php echo "?AccountNo=$AccountNo&MemberNo=$MemberNo"; ?>',0,'center:yes;resizable:no;dialogHeight:350px');
				if( rval )
				{
					if( rval[0] == "Card" )
					{
						window.location="ReplaceMemberProcess.php?Mode=AC<?php echo "&AccountNo=$AccountNo&MemberNo=$MemberNo"; ?>&Number="+rval[1];
					}
					else if( rval[0] == "Member" )
					{
						if( document.getElementById("AccountType").value == 'G' )
						{
							window.location="DisplayMember.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Action=NewMemberNoCopy"; ?>";
						}
						else
						{
							window.location="DisplayMember.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Action=NewMemberCopy"; ?>";

						}
					}
				}
			}
		}
		function mergeAccount()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "mergeAccount";
				document.getElementById("BigForm").submit();
			}
			else
			{
				window.location="MergeMembers.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>";
			}
		}

		function tracking()
		{
<?php
			if	($AccountStatus == "Closed")
			{
?>
				rval = window.showModalDialog('AddTracking.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$Details[Balance]"; ?>', 0, 'center:yes;resizable:no;dialogHeight:250px');
				if( rval )
				{
					window.location="AddTrackingProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&Code="+rval[0]+"&Notes="+rval[1];
				}
<?php
			}
			else
			{
?>
				if( dirty )
				{
					dirty = false;
					document.getElementById("ActionToDo").value = "tracking";
					document.getElementById("BigForm").submit();
				}
				else
				{
					rval = window.showModalDialog('AddTracking.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$Details[Balance]"; ?>', 0, 'center:yes;resizable:no;dialogHeight:250px');
					if( rval )
					{
						window.location="AddTrackingProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&Code="+rval[0]+"&Notes="+rval[1];
					}
				}
<?php
			}
?>
		}

		function adjust()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "adjust";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('Adjustment.php?Ammount=<?php echo $Details["Balance"];?>', 0, 'center:yes;resizable:no;dialogHeight:250px');
				if( rval )
				{
					var d;
					if( rval[1] > 0 )
					{ 
						d = "credit";
					}
					else
					{
						d = "debit";
					}
					if( confirm( "Are you sure you wish to " + d + " the balance by " + rval[1] + " stars") )
					{
						window.location="AdjustmentProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&Code="+rval[0]+"&Notes="+rval[2]+"&Stars="+rval[1];
					}
				}
			}
		}
			
		function letters()
		{
<?php
			if	($AccountStatus == "Closed")
			{
?>
				rval = window.showModalDialog('Letters.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo"; ?>', 0, 'center:yes;resizable:yes;dialogWidth:550px;dialogHeight:250px');
				if( rval )
				{
					window.location="LettersProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&Code="+rval[0];
				}
<?php
			}
			else
			{
?>
				if( dirty )
				{
					dirty = false;
					document.getElementById("ActionToDo").value = "letters";
					document.getElementById("BigForm").submit();
				}
				else
				{
					rval = window.showModalDialog('Letters.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo"; ?>', 0, 'center:yes;resizable:yes;dialogWidth:550px;dialogHeight:250px');
					if( rval )
					{
						window.location="LettersProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo"; ?>&Code="+rval[0];
					}
				}
<?php
			}
?>
		}

		function shop()
		{
			SetDirty();
			window.open( "<?php echo TandTInterface."secure/cust_manage_login.asp?".TandTAccount."&userid=$uname&cardnumber=$CardNo&accounttype=$AccountType&membertype=$Details[MemberType]";?>", "TandT", "" );
		}

		function history()
		{
			<?php
			if( $AccountStatus == "Closed" )
			{
				echo "window.location=\"DisplayMemberHistory.php?AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&AccountType=$AccountType\"";
			}
			else 
			{
			?>
				if( dirty )
				{
					dirty = false;
					document.getElementById("ActionToDo").value = "history";
					document.getElementById("BigForm").submit();
				}
				else
				{
					window.location="DisplayMemberHistory.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&CardNo=$CardNo&AccountType=$AccountType" ?>"; 
				}
			<?php
			}
			?>

		}
		
		


		function AddAccountCard()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "AddAccountCard";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('AddAccountCard.php?<?php echo "UKFAccount=$UKFuelsAccountNo"; ?>',0,'center:yes;resizable:no;dialogHeight:300px;dialogWidth:300px');
				if( rval )
				{
					window.location="AddAccountCardProcess.php?<?php echo "weoucardno=$CardNo&originalukfaccountno=$UKFuelsAccountNo&memberno=$MemberNo&accountno=$AccountNo&newukfaccountno=";?>"+rval[0];
				}
			}
		}

		
		function CloseAccount()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "CloseAccount";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('CloseAccount.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$DisplayTotal"; ?>', 0, 'center:yes;resizable:no;dialogHeight:250px');
				if( rval )
				{
					window.location="CloseAccountProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$DisplayTotal"; ?>&Code="+rval[0]+"&Notes="+rval[1];
				}
			}
		}	

		function ReinstateAccount()
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "ReinstateAccount";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('ReinstateAccount.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$DisplayTotal&StoppedPoints=$TotalStopped"; ?>', 0, 'center:yes;resizable:no;dialogHeight:250px');
				if( rval )
				{
					window.location="ReinstateAccountProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&Balance=$DisplayTotal&StoppedPoints=$TotalStopped"; ?>&Code="+rval[0]+"&Notes="+rval[1];
				}
			}
		}

		function fraudstatus(arg1)
		{
			if( dirty )
			{
				dirty = false;
				document.getElementById("ActionToDo").value = "fraudstatus";
				document.getElementById("BigForm").submit();
			}
			else
			{
				rval = window.showModalDialog('ChangeFraudStatus.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&FraudStatus=$fraudstatus"; ?>&NewStatus='+arg1, 0, 'center:yes;resizable:no;dialogHeight:250px');
				if( rval )
				{
					window.location="ChangeFraudStatusProcess.php?<?php echo "AccountNo=$AccountNo&MemberNo=$MemberNo&FraudStatus=$fraudstatus"; ?>&NewStatus="+rval[0]+"&Notes="+rval[1];
				}
			}
		}	

		</script>
	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
<?php
	if( CheckPermisions(PermissionsSmallAdjust) || CheckPermisions(PermissionsBigAdjust) || CheckPermisions(PermissionsMassiveAdjust))
	{
		if	($AccountStatus == "Closed")
		{
			echo "<td><button disabled onclick=\"adjust()\" $cButton>Adjust</button></td>\n";
		}
		else
		{
			echo "<td><button onclick=\"adjust()\" $cButton>Adjust</button></td>\n";
		}
	}
	if( CheckPermisions(PermissionsMemberHistory) )
	{
		echo "<td><button onclick=\"history()\" $cButton>History</button></td>\n";
	}
	if( CheckPermisions(PermissionsShopping) )
	{
		if ($Details["RedemptionStopDate"] != '' or ( $Details["AccountType"] == 'G' && $Details["PrimaryMember"] != 'Y' ) or $AccountStatus == "Closed")
		{	
			echo "<td><button disabled onclick=\"shop()\" $cButton>Redeem</button></td>\n";
		}
		else 
		{		
			echo "<td><button onclick=\"shop()\" $cButton>Redeem</button></td>\n";
		}	
	}
	if( CheckPermisions(PermissionsTracking) )
	{
		echo "<td><button onclick=\"tracking()\" $cButton>Tracking</button></td>\n";
	}
	if( CheckPermisions(PermissionsRequestCard) )
	{
		if	($AccountStatus == "Closed")
		{
			echo "<td><button disabled $cButton>Additional</button></td>\n";
			echo "<td><button disabled $cButton>Replace</button></td>\n";
		}
		else
		{
			echo "<td><button onclick=\"additionalCard()\" $cButton>Additional</button></td>\n";
			echo "<td><button onclick=\"replaceCard()\" $cButton>Replace</button></td>\n";
		}
	}
	if( CheckPermisions(PermissionsMergeMembers) )  // Mantis 757 - Prevent Merging of Staff Incentive Accounts 14/04/09
	{
		if	(substr($CardNo,0,2) == '01' or $AccountType == 'G' or $AccountStatus == "Closed")
		{
			echo "<td><button disabled onclick=\"mergeAccount()\" $cButton>Merge</button></td>\n";
		}
		else 
		{
			echo "<td><button onclick=\"mergeAccount()\" $cButton>Merge</button></td>\n";
		}
	}
	
	echo "<td><button onclick=\"letters()\" $cButton>Letters</button></td>\n";

	if( CheckPermisions(PermissionsIssuesPages) && $fraudstatus != '0' && $AccountStatus == "Open" )
	{
		echo "<td><button onclick=\"fraudstatus()\" $cButton>Fraud</button></td>\n";
	}

?>
	</tr></table>
	<center>
	</td>
	</tr>
	<tr>
 	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: outset">
