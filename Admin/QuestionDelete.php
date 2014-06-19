<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/QuestionaireInterface.php";

	$QuestionId = $_GET["QuestionId"];

	DeleteQuestion( $QuestionId );

	header("Location: ManageQuestions.php");
?>