<?php

/*
alter table Questions add column Web enum( 'Y', 'N' );

alter table Questions add column Dawleys enum( 'Y', 'N' );

*/
	define( "QuestionTypeText", "T" );
	define( "QuestionTypeBoolean", "B" );
	define( "QuestionTypeList", "S" );
	define( "QuestionTypeInteger", "I" );

	define( "QuestionYes", "Y" );
	define( "QuestionNo", "N" );

	define( "QuestionNoChildren", 8 );
	define( "QuestionMilage", 1 );
	define( "QuestionCompanyPaidFuel", 3 );

function GetCurrentQuestions( $all )
{
	if( $all )
	{
		$Active = "";
	}
	else
	{
		$Active = "where Active = 'Y'";
	}
	$sql = "Select * from Questions  $Active order by priority, Active";
	return DBQueryExitOnFailure( $sql );
}

function GetQuestionData( $QuestionId )
{
	$sql = "Select QuestionText, VerifyPeriod, Type from Questions where QuestionId = $QuestionId";
	$results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $results );
	return $row;
}

// Old Function

function GetMemberQuestions( $MemberNo, $limit )
{
	return GetMemberQuestionsNew( $MemberNo, "", $limit );
}

function GetMemberQuestionsDawleys( $MemberNo, $limit )
{
	return GetMemberQuestionsNew( $MemberNo, "and Dawleys = 'Y'", $limit );
}

function GetMemberQuestionWeb( $MemberNo, $limit )
{
	return GetMemberQuestionsNew( $MemberNo, "and Web = 'Y'", $limit );	
}

function GetMemberQuestionsNew( $MemberNo, $Criteria, $limit )
{
	$sql = "Select 
		QuestionText, Type, Questions.QuestionId, Answers.Answer 
	from Questions 
	left join Answers on( Questions.QuestionId = Answers.QuestionId and MemberNo = $MemberNo  )	
	where ( Date_add( Answers.CreationDate,  INTERVAL  VerifyPeriod DAY) < now()) or ( Answers.CreationDate is null ) and Active = 'Y' $Criteria	
	Order By Priority 
	limit $limit"; 
	return DBQueryExitOnFailure( $sql );

}

function GetQuestionOptionList( $QuestionId, $all )
{
	$results = GetQuestionOptions( $QuestionId, $all );

	$TrackingOptions = array();

	while( $row = mysql_fetch_row( $results ) )
	{
		$TrackingOptions[$row[0]] = $row[1];
	}
	return $TrackingOptions;
	
}

function GetQuestionOptions( $QuestionId, $all )
{
	if( $all )
	{
		$Active = "";
	}
	else
	{
		$Active = "and Active = 'Y'";
	}

	$sql = "Select OptionValue, OptionText, Active from QuestionOptions where QuestionId = $QuestionId $Active order by Active, Priority"; 
	return DBQueryExitOnFailure( $sql );
}



function RecordAnswer( $QuestionId, $MemberNo, $ResponseId, $Type )
{
	global $uname;

	$sql = "Replace into Answers (QuestionId, MemberNo, Answer, CreatedBy, CreationDate) values ($QuestionId, $MemberNo, '$ResponseId', '$uname', now())";
	return DBQueryExitOnFailure( $sql );
}

function SetQuestionPriority( $QuestionId, $Priority, $web, $dawleys )
{
	$sql = "Update Questions set Active = 'Y', Web = '$web', Dawleys = '$dawleys', Priority = $Priority where QuestionId = $QuestionId";

	DBQueryExitOnFailure( $sql );
}

function DeleteQuestion( $QuestionId )
{
	$sql = "Update Questions set Active = 'N', Priority = 9999 where QuestionId = $QuestionId";

	DBQueryExitOnFailure( $sql );
}

function InsertQuestion( $QuestionText, $period, $Type )
{
	$sql = "Insert into Questions ( QuestionText, VerifyPeriod, Type ) values ( '$QuestionText', $period, '$Type' )";
	DBQueryExitOnFailure( $sql );
	return mysql_insert_id();
}

function UpdateQuestion( $QuestionId, $QuestionText, $period, $Type )
{
	$sql = "Update Questions set QuestionText = '$QuestionText', VerifyPeriod = $period, Active = 'Y', Type= '$Type' where QuestionId = $QuestionId";
	DBQueryExitOnFailure( $sql );
}

function UpdateQuestionOption( $QuestionId, $OptionValue, $Value, $Priority )
{
	// Check for duplicates maybee
	$sql = "Select OptionValue, Active from QuestionOptions where QuestionId = $QuestionId and OptionText = '$Value' and OptionValue != '$OptionValue'";

	$results = DBQueryExitOnFailure( $sql );

	if( mysql_num_rows( $results ) != 0 )
	{
		$row = mysql_fetch_row( $results );

		$id = $row[0];

		$sql = "Update QuestionOptions set Active = 'Y' where QuestionId = $QuestionId and OptionValue = '$id'";
		DBQueryExitOnFailure( $sql );
	}
	else
	{
		$sql = "Update QuestionOptions set Active = 'Y', OptionText = '$Value' where QuestionId = $QuestionId and OptionValue = '$OptionValue'";
		DBQueryExitOnFailure( $sql );
	}
}


function InsertQuestionOption( $QuestionId, $Value, $ValueOption, $Priority )
{
	$sql = "Insert into QuestionOptions ( QuestionId, OptionValue, OptionText, Priority ) values ( $QuestionId, '$ValueOption', '$Value', $Priority) ";
	DBQueryExitOnFailure( $sql );
}


function DeleteQuestionOption( $QuestionId, $OptionId )
{
	$sql = "Update QuestionOptions set Active = 'N', Priority = 9999 where QuestionId = $QuestionId and OptionValue = '$OptionId'";
	DBQueryExitOnFailure( $sql );	
}
?>