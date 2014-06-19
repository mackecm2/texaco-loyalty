<?php
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";


	$id = $_GET["Id"];

	if( $id == "New" )
	{
		$sql = "insert into ReportTypes( Description ) values ( 'New Report' )";
		DBQueryExitOnFailure( $sql );
		$id = mysql_insert_id();
	}

	if( isset($_GET["Action"] ))
	{
		if( $_GET["Action"] == "Copy" )
		{
			$sql = "insert into ReportTypes( Description, TableRoot, TableExt, DrillPath, AdditionalFields, SumFields) select Description, TableRoot, TableExt, DrillPath, AdditionalFields, SumFields from ReportTypes where ReportTypeId = $id";
			
			DBQueryExitOnFailure( $sql );
			$id = mysql_insert_id();
		}
	}

	$sql = "select * from ReportTypes where ReportTypeId=$id";
	
	$Results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $Results );

	$sql = "show tables";

	$Tables = DBQueryExitOnFailure( $sql );

	$TablesList = array();
	$TablesList[0] = "";
	while( $t = mysql_fetch_row( $Tables ) )
	{
		$TblName = $t[0];
		$arse = '/[0-9]{4,6}/';
		if( preg_match( $arse , $TblName ) == 1 )
		{
			if( strstr( $TblName, $LastMerge ) )
			{
				$skip++;
			}
			else
			{
				$skip=0;	
			}
		}
		else
		{
			$skip=0;
		}

		$T = preg_split( $arse, $TblName );
		$LastMerge = $T[0];

		if( $skip <= 1 )
		{
			$TablesList[$LastMerge] = $LastMerge;
		}
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Report Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
<script>
	function Copy( id )
	{
		window.location='EditSingleReport.php?Id='+id+'&Action=Copy';
	}

	var dirty = false;
	function SetDirty()
	{
		dirty = true;
//		document.getElementById("update").disabled = false;
//		document.getElementById("create").disabled = true;
	}

	function Swap( r1, r2 )
	{
		// This is to work round a bug in IE where 
//		f1 = r1.lastChild.firstChild.checked;
//		f2 = r2.lastChild.firstChild.checked;
		r1.swapNode( r2 );
//		r1.lastChild.firstChild.checked = f1;
//		r2.lastChild.firstChild.checked = f2;
//		r1.style.backgroundColor='';
//		r2.style.backgroundColor='';
	}


	function MoveUp( row )
	{
		if( row.previousSibling )
		{
			SetDirty();
			Swap( row, row.previousSibling );
		}
		event.cancelBubble = true;
	}

	function MoveDown( row )
	{
		if( row.nextSibling )
		{
			SetDirty();
			Swap( row, row.nextSibling );
		}
		event.cancelBubble = true;
	}

	function AddRow( box)
	{
		if( box.parentNode.parentNode.nextSibling == null )
		{
			
			row = box.parentNode.parentNode;
			var newRow = row.cloneNode( true );
			row.parentNode.insertBefore(newRow);
			newRow.firstChild.firstChild.value = '';
			newRow.lastChild.firstChild.value = '';
		}
	}

	function ChangeTable( box )
	{
		 document.getElementById("TableRoot").value = box.options[box.selectedIndex].value;
	}

</script>
</HEAD>

<Body>

<form action="UpdateReportType.php" method=POST>
<table>
<?php
	echo "<tr><td align=right>ID: <td><input name=Id value=$id>";
	echo "<tr><td align=right>Description: <td><input name=Description value='$row[Description]' size=80>";
	echo "<tr><td align=right>Table Root: <td><Select name=TmpTableRoot id=TmpTableRoot onChange='ChangeTable(this)'>";
	DisplaySelectOptions( $TablesList, $row["TableRoot"] ); 
	echo "</Select>";
	echo  "<br><input id=TableRoot name=TableRoot value='$row[TableRoot]' size=80>";
	echo "<tr><td align=right>Table Extension: <td>Year";
	DisplayRadioButton( "TableExt", "Y", $row["TableExt"], "");
	echo "&nbsp Month";
	DisplayRadioButton( "TableExt", "M", $row["TableExt"], "");
	echo "&nbsp Week";
	DisplayRadioButton( "TableExt", "W", $row["TableExt"], "");
	echo "&nbsp Daily";
	DisplayRadioButton( "TableExt", "D", $row["TableExt"], "");
	echo "&nbsp None";
	DisplayRadioButton( "TableExt", "N", $row["TableExt"], "");


	$DrillOrder = $row["DrillPath"];
	if( strstr($DrillOrder, "$" ) )
	{
		$Order = explode( "$", $DrillOrder);
	}
	else
	{
		$DrillOrder = stripslashes( $DrillOrder ); 
		$DrillOrder = str_replace( "/,", "$%^", $DrillOrder );
		$Order = explode( ",", $DrillOrder);
		$Order = str_replace( "$%^",",",  $Order );  
	}
	$AdditionalArray = explode( "$", $row["AdditionalFields"]);


	echo "<tr><td align=right>Drill Path:<td><Table><TH>Path<TH><TH><TH>Additional Fields";
	foreach( $Order as $Key => $O )
	{
		echo "<tr>\n";
		echo "<td><input name='DrillPath[]'  value=\"$O\"  size=60>\n";
?>
			<TD width="16">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			<TD>
<?php
		if(isset(  $AdditionalArray[$Key] ) )
		{
			$v = $AdditionalArray[$Key];
		}
		else
		{
			$v = "";
		}
		echo "<td><input name='AdditionalFields[]' value=\"$v\" size=60>";
	}
?>
<tr>
<td><input name='DrillPath[]' OnChange="AddRow(this)"  size=60>
<TD width="16">
		<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up">
		<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
<TD>
<td><input name='AdditionalFields[]' size=60>
<?php	
	echo "</Table>";
	echo "<tr><td align=right>Fields:<td><TextArea cols=80 rows=3  name=SumFields>$row[SumFields]</TextArea>";
	echo "</Table><input type=Submit>";
	echo "<br><button onclick='Copy($id)'>Copy</Button>";

?>
</form>
	<br><button onclick="window.location='ReportEditor.php'">Cancel</button>
</Body>

