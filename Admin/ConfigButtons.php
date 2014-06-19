
<script>
	function ManageTrackingCodes()
	{
		window.location="ManageTrackingCodes.php";
	}


	function ManageLetters()
	{
		window.location="ManageLettersTemplates.php";
	}

	function ManageQuestions()
	{
		window.location="ManageQuestions.php";
	}

	function GAccountCards()
	{
		window.location="GAccountCards.php";
	}

	function CardRanges()
	{
		window.location="CardRanges.php";
	}
	
</script>

	<tr>
	<td colSpan="20" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: none">
	<center>
	<table cellpadding = 10px><tr>
	<td><button onclick="ManageTrackingCodes()" <?php echo $cButton; if($but=="Tracking") echo "style=\"background-color:red;\""; ?>>Tracking Codes </button></td>
	<td><button onclick="ManageLetters()" <?php echo $cButton; if($but=="Letters") echo "style=\"background-color:red;\""; ?>>Letters</button></td>
	<td><button onclick="ManageQuestions()" <?php echo $cButton;if($but=="Questions") echo "style=\"background-color:red;\""; ?>>Questions</button></td>
	<td><button onclick="GAccountCards()" <?php echo $cButton; if($but=="GAccount") echo "style=\"background-color:red;\"";?>>UKFuels Accounts</button></td>
	<td><button onclick="CardRanges()" <?php echo $cButton; if($but=="CardRanges") echo "style=\"background-color:red;\"";?>>Card Ranges</button></td>
	</tr></table>
	<center>
	</td>
	</tr>
	<tr>
 	<td colSpan="20" height="400" style="BORDER-TOP-STYLE: none; BORDER-RIGHT-STYLE: outset; BORDER-LEFT-STYLE: outset; BORDER-BOTTOM-STYLE: outset">



