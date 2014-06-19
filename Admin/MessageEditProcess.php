<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";

	$MessageNo = $_POST["MessageNo"];

	$fieldNames = $_POST["FieldName"];
	$Comparisons = $_POST["Comparison"];
	$Booleans = $_POST["Boolean"];
	$Modes = $_POST["Mode"];

	$sql = "Delete from MessageCriteria where MessageNo = '$MessageNo'";

	$results = mysql_query( $sql );

	$count = 0;
	foreach( $fieldNames as $value )
	{
		if( $fieldNames[$count] != "" )
		{
			if( $Modes[$count] == "Text" )
			{
				$crit = $_POST["FreeText"][$count];
			}
			else if( $Modes[$count] == "List" )
			{
				$crit = $_POST["Single"][$count];
			}
			else if( $Modes[$count] == "Range" )
			{
				$crit = $_POST["Range"][$count];
			}

			$sql = "Insert into MessageCriteria (	MessageNo, CriteriaNo, FieldName, ComparisonType, Boolean, ComparisonCriteria ) values ( '$MessageNo', $count, '$fieldNames[$count]', '$Comparisons[$count]', '$Booleans[$count]', '$crit')";


//			print $sql;

			$results = mysql_query( $sql )or die (mysql_error());

			$count++;
		}
	}

	$sql = "Delete from MessageDetail where MessageNo = '$MessageNo'";

	$results = mysql_query( $sql ) or die (mysql_error());

	#if( isset( $_POST["Exclude"] ) )
	#{
	#	$Exclude = 1;
	#}
	#else
	#{
	#	$Exclude = 0;
	#}

	$sqlFields = "MessageNo, Description, MessageText, DisplayTimes, LogEvents, Web, Terminal, Active, CreationDate,	CreatedBy";
	$sqlValues = " '$MessageNo', '$_POST[Description]',  '$_POST[MessageText]',$_POST[DisplayTimes], '$_POST[LogEvents]', '$_POST[Web]', '$_POST[Terminal]',  '$_POST[Active]', now(), '$uname'";

	if( $_POST["StartDate"] != "" )
	{
		$sqlFields .= ",StartDate";
		$sqlValues .= ",'$_POST[StartDate]'";
	}

	if( $_POST["EndDate"] != "" )
	{
		$sqlFields .= ",ExpiryDate";
		$sqlValues .= ",'$_POST[EndDate]'";
	}

	if( isset( $_POST["Priority"] ) )
	{
		$sqlFields .= ",Priority";
		$sqlValues .= ",$_POST[Priority]";
	}


	$sql = "Insert into MessageDetail ( $sqlFields   ) values ( $sqlValues  )";

//	print $sql;

	$results = mysql_query( $sql ) or die (mysql_error());

//	print_r( $_POST );
	header("Location: MessagesManager.php");
?>
