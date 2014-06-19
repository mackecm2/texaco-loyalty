<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	#include "../include/DB.inc";
	include "../DBInterface/QuestionaireInterface.php";





	$MemberNo = $_POST["MemberNo"];
	$Questions = GetMemberQuestions( $MemberNo, 6 );
	while( $row = mysql_fetch_assoc($Questions) )
	{
		$QuestionId = $row["QuestionId"];
		if( isset($_POST[$QuestionId] ))
		{
			$Response = $_POST[$QuestionId];
			if( $row["Answer"] != $Response || isset( $_POST["Verfied".$QuestionId]))
			{
				RecordAnswer( $QuestionId, $MemberNo, $Response, $row["Type"] );
			}
		}
	}
	#echo "Answers recorded";
	#die();
?>

<html>
<head>
<script>
	function clo()
	{
		window.close();		
	}
</script>
</head>
<body onload="clo();">
</body>

</html>
