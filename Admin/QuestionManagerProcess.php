<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/QuestionaireInterface.php";

	$a = $_POST["Questions"];

	$c = 0;
	$d = 0;

//	$results = GetCurrentQuestions( true );

//	$oldEntries = array();

//	while( $row = mysql_fetch_assoc($results) )
//	{
//		$oldEntries[$row["QuestionId"]] = $row["Active"];
//	}

	foreach( $a as $value )
	{
		if( isset( $_POST["W".$value] ) )
		{
			$web = 'Y';
		}
		else
		{
			$web = 'N';
		}

		if( isset( $_POST["D".$value] ) )
		{
			$dawleys = 'Y';
		}
		else
		{
			$dawleys = 'N';
		}

		if( isset( $_POST["A".$value] ) )
		{
			SetQuestionPriority( $value, $c, $web, $dawleys );
			$c++;
		}
		else
		{
			DeleteQuestion( $value );
			$d++;
		}

	
	}

	$act = $_POST["paction"] ;
	switch( $act )
	{
		case "update":
			header("Location: ManageQuestions.php");
			break;
		case "create":
			header("Location: QuestionEdit.php?QuestionId=new" );
			break;
		default:
			header("Location: QuestionEdit.php?QuestionId=".$act );
			break;
	}
?>
