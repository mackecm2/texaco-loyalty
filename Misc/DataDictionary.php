<?php

	include "../include/DB.inc";
	include "../include/DisplayFunctions.inc";

	#$db_host = "localhost";
	#$db_name = "texaco";
	#$db_user = "root";
	#$db_pass = "trave1";	
$db_user = "pma001";
$db_pass = "amping";
	
	//$db_pass = "";

	$update = true;
 
	connectToDB( ReplicationServer, TexacoDB );

	if( isset( $_GET["Table"] ) )
	{
		$Update = true;
		$TableName = $_GET["Table"];
	}
	else
	{
		$Update = false;
		$TableName = "";
	}

	$sql = "Show Tables like '$TableName'";
	$tables = DBQueryExitOnFailure( $sql );

	echo "<style> td {border :solid; font-size: xx-small;}";
	echo "table { empty-cells: show; border:solid ; border-collapse: collapse; font-size: x-small;}";
	echo "</style>";


	$LastMerge = "ZZZZ";


	while( $table = mysql_fetch_row( $tables ) )
	{
		$skip = false;
		$TblName = $table[0];

		$sql = "Describe $TblName";											   
		$fields = DBQueryExitOnFailure( $sql );

//		$sql = "Show table status like '$TableName'";
//		$Status = DBQueryExitOnFailure( $sql ); 
//		$TableStatus = mysql_fetch_assoc( $Status ); 

		$arse = '/[0-9]{4,6}/';
		if( preg_match( $arse , $TblName ) == 1 )
		{
			if( strstr( $TblName, $LastMerge ) )
			{
				$skip = true;
				echo "<br>Skipped: $TblName";
			}
		}

		$T = preg_split( $arse, $TblName );
		$LastMerge = $T[0];
		

		if( !$skip )
		{
			echo "<a name='$TblName' href='DataDictionary.php?Table=$TblName'><H2>$TblName</H2></a>\n"; 
			$sql = "Select Description from DataDictionary where TableName = '$TblName' and FieldName = ''";
			$Desc = DBQueryExitOnFailure( $sql ); 
			$TableDesciption = mysql_fetch_assoc( $Desc ); 		

			if( $Update )
			{
				echo "<form action='UpdateFieldDescription.php' Method=POST>";
				echo "<input name=TableName type=hidden value=$TableName>";
				echo "<TextArea name=TableDescription cols=80 rows=3>$TableDesciption[Description]</TextArea>";
			}
			else
			{
				echo "$TableDesciption[Description]";
			}
			echo "<Table >";
			echo "<TR><TH width=120>Field Name<TH width=70>Type<TH width = 30>Key<TH width=70>Default<TH width=400>Description\n";

			while( $field = mysql_fetch_assoc( $fields ) )
			{
				$sql = "Select Description from DataDictionary where TableName = '$TblName' and FieldName = '$field[Field]'";

				$Desc = DBQueryExitOnFailure( $sql ); 
				$d = mysql_fetch_assoc( $Desc ); 
				echo "<tr><td>$field[Field]<td>$field[Type]<td>$field[Key]<td>$field[Default]<td>\n";
				if( $Update )
				{
					echo "<input name='$field[Field]' value='$d[Description]'>";
				}
				else
				{
					echo "$d[Description]";
				}
			}
			echo "</table>\n";
			if( $Update )
			{
				echo "<input type=submit>";
				echo "</form>";
			}
		}
	}

?>