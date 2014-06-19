<?php 
	if( isset( $Title )) 
	{
		echo "<html>\n<head>\n<title>$Title</title>\n";
	}
	if( !isset( $helpPageId ) )
	{
		$helpPageId = 0;
	}
?>


<script>
	function FindMember()
	{
		window.location = "../MemberScreens/SelectMember.php";
	}

	function DisplayHelp( page )
	{
		window.open( "../Help/"+page+".html", page, 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=450,height=550');
	}
</script>

<LINK REL=StyleSheet HREF="../TabStyles.css">
<style type="text/css">
form { margin: 0; }
</style>

</HEAD>

<?php
	if( isset( $bodyControl ))
	{
		echo "<BODY class=\"desktop\" $bodyControl>";
	}
	else
	{
		echo "<BODY class=\"desktop\">";
	}
?>

<table style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: none; BORDER-LEFT-STYLE: none;  BACKGROUND-COLOR: <?php echo BackgroundColour;?>; BORDER-BOTTOM-STYLE: none" cellSpacing="0" cellPadding="0"  border="1">
	<tr id="TabRows" height="15px">
<?php 
	$Pages = array();
	$Pages["Search"] = "window.location='../MemberScreens/SelectMember.php'";
	$Pages["Card Holder"] = "window.location='../MemberScreens/DisplayMember.php'";
	$Pages["Password"] = "window.location='../Admin/ChangePassword.php'"; 
	if( CheckPermisions(PermissionsUserManager) )
	{
		$Pages["Manager"] = "window.location='../Admin/ManageUsers.php'";
	}
	if( CheckPermisions(PermissionsBonusManager) )
	{
		$Pages["Bonuses"] = "window.location='../Admin/BonusManager.php'";
	}
	if( CheckPermisions(PermissionsBonusManager) )
	{
		$Pages["Messages"] = "window.location='../Admin/MessagesManager.php'";
	}	
	if( CheckPermisions(PermissionsDailyProcess) )
	{
		$Pages["End Of Day"] = "window.location='../Admin/MailMerge.php'";
	}
	if( CheckPermisions(PermissionsConfigPages))
	{
		$Pages["Config"] = "window.location='../Admin/ManageQuestions.php'";
	}
	if( CheckPermisions(PermissionsReporting) )
	{
		$Pages["Reporting"] = "window.location='../Reporting/ReportTypeIndex.php'";
	}
	if( CheckPermisions(PermissionsEBSPages) )
	{
		$Pages["EBS"] = "window.location='../Misc/ManagementPage.html'";
	}

	if( CheckPermisions(PermissionsIssuesPages) )
	{
		$Pages["Issues"] = "window.location='../Admin/IssueManager.php'";
	}
	
	
	
	
	
	
	

	$Pages["Logout"] = "window.location='../Login.php'";

	if( isset( $HelpPage ) )
	{
		$Pages["Help"] = "DisplayHelp('$HelpPage')";
	}

	$hl = "../TabSheets/HlightedFirst.gif";
	$hr = "../TabSheets/HlightedRight.gif";
	$nl = "../TabSheets/NormalFirst.gif";
	$nr = "../TabSheets/NormalMidle.gif";
	$prev = false;
	foreach( $Pages as $page => $func)
	{
		if( $page == $currentPage )
		{
			echo "<td class=\"celliconfront\"><IMG height=20 src=\"$hl\" width=18></td>\n";
			echo "<td class=\"cellfront\"><button class=\"buttonfront\">$page</button></td>\n";
			$prev = true;	
		}
		else 
		{
			if( $prev )
			{
			echo "<td class=\"celliconfront\"><IMG height=20 src=\"$hr\" width=18></td>\n";

			}
			else
			{
			echo "<td class=\"celliconrear\"><IMG height=20 src=\"$nl\" width=18></td>\n";

			}
			echo "<td class=\"cellrear\"><button class=\"buttonrear\" onclick=\"$func\">$page</button></td>\n";

			$prev = false;
		}

		$hl = "../TabSheets/HlightedLeft.gif";
		$nl = $nr;
	}
	if( $prev )
	{
		echo "<td class=\"celliconfront\"><IMG height=20 src=\"../TabSheets/HlightedLast.gif\" width=18></td>\n";	
	}
	else
	{
		echo "<td class=\"celliconrear\"><IMG height=20 src=\"../TabSheets/NormalLast.gif\" width=18></td>\n";
	}
?>
		<td class="padding" width="1000">&nbsp;</td>
	</tr>
