<?php
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";

	$id = $_POST["Id"];
	$DrillPath = $_POST["DrillPath"];
	$AdditionalFields = $_POST["AdditionalFields"];

//	print_r( $_POST );

	$Drill = "";
	$Add = "";

//	$Drill = implode( $DrillPath, "$" );
//	$Add = implode(  $AdditionalFields, "$" );
	$C = "";
	foreach( $DrillPath as $K => $P )
	{
		if( $P != "" )
		{
			$Drill .= $C.$P;
			$Add .= $C.$AdditionalFields[$K];
			$C = '$';
		}
	}

	$Drill = mysql_escape_string( $Drill );
	$Add = mysql_escape_string( $Add );
	$SumFields = mysql_escape_string( $_POST["SumFields"] )	;
	$Desc = mysql_escape_string( $_POST["Description"] );
	$Root = mysql_escape_string( $_POST["TableRoot"] );
	$Ext = mysql_escape_string( $_POST["TableExt"] );

	$sql = "Update ReportTypes 
	set Description = '$Desc', 
	TableRoot = '$Root',
	TableExt = '$Ext',
	DrillPath = '$Drill',
	AdditionalFields = '$Add',
	SumFields = '$SumFields'
	where ReportTypeId=$id";
	
//	echo $sql;
	$Results = DBQueryExitOnFailure( $sql );

	header("Location: ReportEditor.php");


?>