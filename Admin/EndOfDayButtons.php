
<script>
	function MailMerge()
	{
		window.location="MailMerge.php";
	}

	function PickLists()
	{
		window.location="TransferOrdersFile.php";
	}

	function CardRequests()
	{
		window.location="CardRequestsManager.php";
	}

	function Reports()
	{
		window.location="Reports.php";
	}

	function FileLoad()
	{
		window.location="FileLoad.php";
	}


	function WekcomePacks()
	{
		window.location="WelcomePacksManager.php";
	}
	function StaffIncentive()
	{
		window.location="StaffPacksManager.php";
	}

  	function NMC()
	{
		window.location="NewMemberCycleManager.php";
	}

	function SiteClosure()
	{
		window.location="SiteClosureManager.php";
	}

</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><button onclick="MailMerge()" <?php echo $cButton; if($but=="MailMerge") echo "style=\"background-color:red;\"";?>>Mail Merge</button></td>
	<td><button onclick="PickLists()" <?php echo $cButton; if($but=="Orders") echo "style=\"background-color:red;\"";?>>EOD File</button></td>
	<td><button onclick="CardRequests()" <?php echo $cButton; if($but=="CardRequests") echo "style=\"background-color:red;\"";?>>Cards</button></td>
	<td><button onclick="WekcomePacks()" <?php echo $cButton; if($but=="WelcomePacks") echo "style=\"background-color:red;\"";?>>Welcome</button></td>
	<td><button onclick="StaffIncentive()" <?php echo $cButton; if($but=="StaffIncentive") echo "style=\"background-color:red;\"";?>>Staff</button></td>
	<td><button onclick="NMC()" <?php echo $cButton; if($but=="NMC") echo "style=\"background-color:red;\"";?>>NMC</button></td>
	<td><button onclick="SiteClosure()" <?php echo $cButton; if($but=="SiteClosure") echo "style=\"background-color:red;\"";?>>Site Closure</button></td>

	<td><button onclick="Reports()" <?php echo $cButton; if($but=="Reports") echo "style=\"background-color:red;\"";?>>Reports</button></td>
	<td><button onclick="FileLoad()" <?php echo $cButton; if($but=="FileLoad") echo "style=\"background-color:red;\"";?>>File Load</button></td>

	</tr></table>
	<center>
	</td>
	</tr>
	<tr>
 	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: outset">



