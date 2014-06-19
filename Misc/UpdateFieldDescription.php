<?php
	// alter table DataDictionary add column ForeignKey varchar(40);
	include "../include/DB.inc";
	include "../include/DisplayFunctions.inc";

	$db_host = "localhost";
	$db_name = "texaco";
	$db_user = "root";
	$db_pass = "trave1";																		   
	//$db_pass = "";

	$update = true;
 
	if( isset( $_GET["DB"] ) )
	{
		$DB = $_GET["DB"];
	}
	else
	{
		$DB = "Texaco";
	}

	if( $DB == "Texaco" )
	{
		$db_user = "root";
		$db_pass = "trave1";																		   
		connectToDB( MasterServer, TexacoDB );
	}
	else if( $DB == "Reporting" )
	{
		$db_user = "root";
		$db_pass = "trave1";																		   
		connectToDB( ReportServer, ReportDB );
	}

	$ForeignKey = "";
	$FieldDesc = "";

	foreach( $_POST as $Field => $Value )
	{
		
		if($Field == "TableName")
		{
			$TableName = $Value;
		}
		else 
		{
			if($Field == "TableDescription")
			{
				$FieldName = "";
 				$FieldDesc = mysql_escape_string( $Value );
 				$sql = "Replace	into DataDictionary values ( '$TableName', '$FieldName', '$FieldDesc', '' )";
				DBQueryExitOnFailure( $sql ); 

			}
			else if( strstr( $Field, "_ForeignKey" ) )
			{
				$ForeignKey = $Value;
				$sql = "Replace	into DataDictionary values ( '$TableName', '$FieldName', '$FieldDesc', '$ForeignKey' )";
				DBQueryExitOnFailure( $sql ); 
			}
			else if( strstr( $Field, "_Description" ) )
			{
				$FieldName = substr( $Field, 0, strpos( $Field, "_Description" )) ;
				$FieldDesc = mysql_escape_string( $Value );
			}

		}
	}
	header("Location: DataDictionary.php?DB=$DB#$TableName");
?>