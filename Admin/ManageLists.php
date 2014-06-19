<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";

	if( isset($_GET["ListType"]) )
	{
		$ListType = $_GET["ListType"];
	}
	else
	{
		$ListType = "Tracking";
	}

	if( isset( $_GET["Status"] ) && $_GET["Status"] == "Deleted" )
	{
		$Status = "Deleted";
		$StatusQ = " and Active = 'N'";
	}
	else
	{
		$Status = "Active";
		$StatusQ = " and Active = 'Y'";
	}

	$Template = "" ;
	if( isset($_GET["Template"] ))
	{
		$Template = $_GET["Template"] ;
	}

	$Desc = "";
	if( isset($_GET["Desc"] ))
	{
		$Desc = $_GET["Desc"];
	}

	$NewS = "";
	if( isset( $_GET["Action"] )  )
	{
		if( $_GET["Action"] == "Delete" )
		{
			$NewS = " set Active = 'N'";
			$asql = "Update ";
		}
		else if( $_GET["Action"] == "Enable" )
		{
			$NewS = " set Active = 'Y'";
			$asql = "Update ";
		}
		else if( $_GET["Action"] == "Add" )
		{
			$Desc = $_GET["Desc"];
			$asql = "insert into ";
		}
		else if( $_GET["Action"] == "Update" )
		{
			$asql = "update ";
			$NewS = " set Description = \"$Desc\"";
			if( $Template != "" )
			{
				$NewS .= ", Template = \"$Template\"";
			}
		}
	}
	else
	{

	}

	$code = "";
	if( isset( $_GET["Code"] ) )
	{
		$code = " '$_GET[Code]'";
	}


	if($ListType == "Tracking")
	{
		$fields = "TrackingCode, Description, null";
		$sqlc = "TrackingType='L'";
		$table = "TrackingCodes";
		$ttype = "L";
		$indexField = "TrackingCode";
		$AddS = "( Description , TrackingType, Active) values ( \"$Desc\", 'L', 'Y' )";
	}
	else if($ListType == "Adjustments")
	{
		$fields = "TrackingCode, Description, null";
		$sqlc = "TrackingType='C'";
		$table = "TrackingCodes";
		$ttype = "C";
		$indexField = "TrackingCode";
		$AddS = "( Description , TrackingType, Active) values ( \"$Desc\", 'C', 'Y' )";
	}
	else if($ListType == "Letters")
	{
		$fields = "LetterCode, Description, Template";
		$table = "LetterCodes";
		$sqlc = "1";
		$ttype = "";
		$indexField = "LetterCode";
		$AddS = "( Description, Template, Active) values ( \"$Desc\", \"$Template\", 'Y' )";
	}
	else
	{
		$errorStr = "What are you doing";
		include "../include/NoPermission.php";
		exit();
	}

	if( isset( $_GET["Action"] )  )
	{
		if( $_GET["Action"] == "Add" )
		{
			$dsql = "$asql $table $AddS";
		}
		else
		{
			$dsql = "$asql $table $NewS where $indexField = $code ";
		}
		echo $dsql;
		$results = mysql_query( $dsql );
		if( !$results )
		{
			$errorStr = mysql_error();
			include "../include/NoPermission.php";
			exit();
		}

	}

	
	$sql = "Select $fields from $table where $sqlc $StatusQ";

	$results = mysql_query( $sql );

	if( !$results )
	{
		$errorStr = mysql_error();
		include "../include/NoPermission.php";
		exit();
	}

	$Title = "Manager";
	$currentPage = "User Manager";
	$cButton = "";
	include "../MasterViewHead.inc";
	include "ManagerButtons.php";

?>
<script>
	var status = "<?php echo $Status; ?>";
	var listType = "<?php echo $ListType; ?>";
	var code = 0;

	function Update()
	{
		if( confirm( "Changing the description of a entry will be reflected in all historical records are you sure you wish to proceed" ) )
		{
			desc = document.getElementById("Description").value;
			temp = document.getElementById("Template").value;
			window.location = "ManageLists.php?Action=Update&ListType=" + listType + "&Status=Active&Desc=" + desc + "&Template=" + temp +"&Code=" + code; 		
		}
	}

	function Add()
	{
		desc = document.getElementById("Description").value;
		temp = document.getElementById("Template").value;
		window.location = "ManageLists.php?Action=Add&ListType=" + listType + "&Status=Active&Desc=" + desc + "&Template=" + temp; 
	}

	function CopyValues( lcode, Desc, Temp )
	{
		document.getElementById("Description").value = Desc;
		document.getElementById("Template").value = Temp;
		code = lcode;
	}

	function ChangeList(t)
	{
		listType = t.value; 
		window.location = "ManageLists.php?ListType=" + listType + "&Status=" + status;
	}

	function ChangeStatus(t)
	{
		status = t.value;
		window.location = "ManageLists.php?ListType=" + listType + "&Status=" + status;
	}

	function Delete( t )
	{
		window.location = "ManageLists.php?Action=Delete&ListType=" + listType + "&Status=" + status + "&Code=" + t ;
	}

	function UnDelete( t )
	{
		window.location = "ManageLists.php?Action=Enable&ListType=" + listType + "&Status=" + status + "&Code=" + t;
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
		if( row.parentNode.firstChild != row.previousSibling )
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
	
	Tracking<?php DisplayRadioButton( "ListType", "Tracking", $ListType, "onactivate=\"ChangeList(this)\"")?>
	Adjustments<?php DisplayRadioButton( "ListType", "Adjustments", $ListType, "onactivate=\"ChangeList(this)\"")?>
	Letters<?php DisplayRadioButton( "ListType", "Letters", $ListType, "onactivate=\"ChangeList(this)\"")?>

	<table width=95%><tr><td width = 50%>
	<div style="width:95%; height:300px; border-style:inset; background-color: white; overflow:auto">
	<Table width=100%>
<?php
	while( $row = mysql_fetch_row( $results ) )
	{
		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" onclick=\"CopyValues('$row[0]','$row[1]', '$row[2]')\"><td width=100%>$row[1]\n<td>";
		if( $Status == "Deleted" )
		{
			echo "<img OnClick=\"UnDelete('$row[0]')\" src=\"untrash.gif\"></TD>\n";
		}
		else
		{
			echo "<img OnClick=\"Delete('$row[0]')\" src=\"trash.gif\"></TD>\n";
		}
	}
?>
	</Table>
	</div>
	<td>
	<form>
	<input type=hidden name="ListType" value="">
	<input type=hidden name="RecordID">
	<fieldset>
	<Table>
	<tr>
	<td>
	New Description: <td><input id="Description">
	<?php
	if($ListType == "Letters")
	{
		echo "<tr><td>Template: <td><input id=\"Template\">";
	}
	else
	{
		echo "<input type=hidden id=\"Template\">";

	}
	?>
	<tr><td><td><button onclick="Add()">Add</button>&nbsp;&nbsp;&nbsp;&nbsp;<button onclick="Update()">Update</button>
	</table>
	</fieldset>
	<br>
	<br>Active <?php DisplayRadioButton( "Status", "Active", $Status, "onactivate=\"ChangeStatus(this)\"")?>
	<br>Deleted <?php DisplayRadioButton( "Status", "Deleted", $Status, "onactivate=\"ChangeStatus(this)\"")?>
	</table></center>
<?php
	include "../MasterViewTail.inc";
?>


