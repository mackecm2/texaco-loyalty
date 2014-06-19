<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
	include "../DBInterface/TrackingInterface.php";

	if( isset( $_GET["ListType" ] ) )
	{
		$ListType = $_GET["ListType" ];
	}
	else if( isset( $_POST["ListType" ] ) )
	{
		$ListType = $_POST["ListType" ];
	}
	else
	{
		$ListType = TrackingTypeList;
	}

	$c = 0;
	if( isset( $_POST["Records"] ) )
	{
		$a = $_POST["Records"];
		foreach( $a as $value )
		{
			if( isset( $_POST['C'.$value] ) )
			{
				$C = 'Y';
			}
			else
			{
				$C = 'N';
			}
			if( isset( $_POST['T'.$value] ) )
			{
				$T = 'Y';
			}
			else
			{
				$T = 'N';
			}
			if( isset( $_POST['S'.$value] ) )
			{
				$S = 'Y';
			}
			else
			{
				$S = 'N';
			}

			if( isset( $_POST['A'.$value] ) )
			{
				EnableTrackingCode( $value, $c, $C, $T, $S );
				$c++;
			}
			else
			{
				DeleteTrackingCode( $value );
			}
		}

	}

	$code = "";
	if( isset( $_POST["Code"] ) )
	{
		$code = $_POST["Code"];
	}

	$Desc = "";
	if( isset($_POST["Description"] ))
	{
		$Desc = $_POST["Description"];
	}

	if( isset( $_POST["Action"] )  )
	{
		switch( $_POST["Action"] )
		{
		case "Add":
			if( $Desc != "" )
			{
				InsertTrackingCode( $ListType, $Desc ); 
			}
			break;
		case "Update":
			if( $code != "" && $Desc != "" )
			{
				UpdateTrackingCode( $code, $Desc );
			}
			break;
		}
	}

	$results = GetTrackingCodeDetails( $ListType, true );	

	$Title = "Tracking Codes Manager";
	$currentPage = "Config";
	$bodyControl = "onbeforeunload=\"LeavePage()\"";
	$cButton = "";
	$but = "Tracking";

	$HelpPage = "TackingCodes";

	include "../MasterViewHead.inc";
	include "ConfigButtons.php";

?>
<script>
	var dirty = false;
	var dataDirty = false;
	function SetDirty()
	{
		dirty = true;
		event.cancelBubble = true;
	}

	function LeavePage()
	{
		if( dirty )
		{
			event.returnValue = "You have not saved you changes to the database!";
		}
	}

	function DataChanged()
	{
		dataDirty = true;
	}

	function Update()
	{
		if( dataDirty )
		{
			if( confirm( "Changing the description of a entry will be reflected in all historical records are you sure you wish to proceed" ) )
			{
				dirty = false;
				document.getElementById("Action").value = "Update";
				document.forms[0].submit();
			}
		}
		else
		{
			dirty = false;
			document.forms[0].submit();
		}
	}

	function Add()
	{
		dirty = false;
		document.getElementById("Action").value = "Add";
		document.forms[0].submit();
	}

	function CopyValues( Code, Desc, Temp )
	{
		var dataDirty = false;
		document.getElementById("Code").value = Code;
		document.getElementById("Description").value = Desc;
//		document.getElementById("Template").value = Temp;
	}

	function Swap( r1, r2 )
	{
		// This is to work round a bug in IE where 
		f1 = r1.lastChild.firstChild.checked;
		f2 = r2.lastChild.firstChild.checked;
		r1.swapNode( r2 );
		r1.lastChild.firstChild.checked = f1;
		r2.lastChild.firstChild.checked = f2;
		r1.style.backgroundColor='';
		r2.style.backgroundColor='';
	}

	function MoveUp( row )
	{
		if( row.parentNode.firstChild != row )
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


</script>
	<center>
	<form action="ManageTrackingCodes.php" method="POST">
	<input type=hidden name="Action">
	<input type=hidden name="ListType" value="<?php echo $ListType;?>">
	
	<table width="95%">
        <tr>
          <td width="75%"><table width="95%">
            <tr>
              <td width="60%">Description </td>
              <td width="10%">Credit</td>
              <td width="10%">Tracking</td>
              <td width="7%">Stop</td>
              <td width="6%"></td>
              <td width="7%">Active</td>
            </tr>
          </table>
          <div
          style="width:95%; height:550px; border-style:inset; background-color: white; overflow:auto"><table
          width="100%">
<?php
	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"CopyValues('$row[TrackingCode]','$row[Description]' )\"><td width=60%>$row[Description]\n";
		echo "<TD width=10% align=center>";
		DisplayCheckBox( 'C'.$row["TrackingCode"], $row["CreditDebit"] == 'Y', "onclick=\"SetDirty()\"" );
		echo "<TD width=10% align=center>";
		DisplayCheckBox( 'T'.$row["TrackingCode"], $row["AddTracking"] == 'Y', "onclick=\"SetDirty()\"" );
		echo "<TD  width=7% align=center>";
		DisplayCheckBox( 'S'.$row["TrackingCode"], $row["StopTracking"] == 'Y', "onclick=\"SetDirty()\"" );
?>
			<TD width="6%" align="center">
				<img OnClick="MoveUp(this.parentNode.parentNode)" src="uparrow.gif" Title="Move Up"><br>
				<img OnClick="MoveDown(this.parentNode.parentNode)" src="downarrow.gif" Title="Move Down">
			<TD>
<?php
		echo "<TD  width=7% align=center><input type=\"hidden\" name=\"Records[]\" value=\"$row[TrackingCode]\">";
		DisplayCheckBox( 'A'.$row["TrackingCode"], $row["Active"] == 'Y', "onclick=\"SetDirty()\"" );
	}
?>
	</Table>
	</div>
	<td style="vertical-align: top;">
	<fieldset>
	<Table>
	<tr>
	<td>Code Id:<td><input readonly name="Code">
	<tr><td>New Description: <td><input id="Description" name="Description" onchange="DataChanged()">
	<tr><td><td><button onclick="Add()">Add</button>&nbsp;&nbsp;&nbsp;&nbsp;<button onclick="Update()">Update</button>
	</table>
	</fieldset>
	</table></center>
<?php
	include "../MasterViewTail.inc";
?>


