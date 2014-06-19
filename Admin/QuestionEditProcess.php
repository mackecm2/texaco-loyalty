<?php 

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/QuestionaireInterface.php";

	// Begin Transaction

	$QuestionId = $_POST["QuestionId"];

	$Question = $_POST["Question"];

	$Period = $_POST["Period"];

	$Type =  $_POST["QuestionType"];

	if( $QuestionId == "new" )
	{
		$QuestionId = InsertQuestion( $Question, $Period, $Type );
//		$QuestionOptions = array();
	}
	else
	{
		UpdateQuestion( $QuestionId, $Question, $Period, $Type );
	}

	if( $Type == QuestionTypeList )
	{
		$priority = 0;
		foreach( $_POST as $fieldname => $value )
		{
			if( $fieldname != "QuestionId" && $fieldname != "Question" && $fieldname != "Period" && $fieldname != "QuestionType" )
			{
				$fType = substr( $fieldname, 0 , 1 );
				$id = substr($fieldname,1);
				if( $fType == "N" && $value != "" )
				{
					if( isset( $_POST["V".$id] ) )
					{
						$OptionValue = $_POST["V".$id];
						InsertQuestionOption( $QuestionId, $value, $OptionValue,	$priority );
						$priority++;
					}
				}
				if( $fType == "V")
				{

				}
				else if( $fType == "D" ) 
				{
					if( isset($_POST["A". $id]) )
					{
						UpdateQuestionOption( $QuestionId, $id, $value, $priority);
						$priority++;
					}
					else
					{
						DeleteQuestionOption( $QuestionId, $id );
					}
				}
			}
		}
	}

	
	// End Transaction
	//header("Location: QuestionEdit.php?QuestionId=$QuestionId");
	header("Location: ManageQuestions.php");

?>