<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/QuestionaireInterface.php";

	$MemberNo = $_GET["MemberNo"];

	$Questions = GetMemberQuestionsDawleys( $MemberNo, 6 );

	if( mysql_num_rows( $Questions ) == 0 )
	{
?>
<html>
<head>
<title>

</title>
<script>
	function clo()
	{
		window.close();		
	}
</script>

</head>
<body onload="clo();">
</body>
<?php
	}
	else
	{
?>

<html>
<head>
<title>

</title>
</head>
<body>
<script>
	function done()
	{
		document.forms[0].submit();
		window.close();		
	}

	function cancel()
	{
		window.close();		
	}
</script>

	<form target="_blank" method="POST" action="QuestionaireProcess.php">
	<table style="width:80%; align:center">
<?php 
	echo "<input type=\"hidden\" name=\"MemberNo\" value=\"$MemberNo\">\n";
	while( $row = mysql_fetch_assoc($Questions) )
	{
		echo "<tr><td style=\"text-align:right\">$row[QuestionText]<td>\n";

		switch ($row["Type"])
		{
			case QuestionTypeBoolean:
				$BoolOptions = array();
				$BoolOptions[''] = "";
				$BoolOptions[QuestionYes] = "Yes";
				$BoolOptions[QuestionNo] = "No";
				echo "<Select name=\"$row[QuestionId]\">\n";
				DisplaySelectOptions( $BoolOptions, $row["Answer"] );
				echo "</select>";
			break;
			case QuestionTypeInteger:
				echo "<input name=\"$row[QuestionId]\" value=\"$row[Answer]\">";
			break;
			case QuestionTypeText:
				echo "<input name=\"$row[QuestionId]\" value=\"$row[Answer]\">";
			break;
			case QuestionTypeList:
				$Options = GetQuestionOptionList( $row["QuestionId"], false );
				echo "<Select name=\"$row[QuestionId]\">\n";
				echo "<option></option>\n";
				DisplaySelectOptions( $Options, $row["Answer"] );
				echo "</select>";
			break;
		}
		
		echo "<td>";

		if( $row["Answer"] != "" )
		{
			Echo "Verified";
			Echo "<input type=\"checkbox\" name=\"Verfied$row[QuestionId]\">";
		}
	}

?>
	</table>
	<center>
	<button onclick="done()">OK</button>&nbsp;&nbsp;&nbsp;&nbsp;
	<button onclick="cancel()">Cancel</button>
	</center>
	</form>
</body>
<?php

	}
?>