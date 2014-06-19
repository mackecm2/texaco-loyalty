<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";

	$FieldName = $_GET['FieldName'];
	if( isset( $_GET['Comp'] ) )
	{
		$val =  $_GET['Value'];
		$comp = strip_tags( $_GET['Comp'] );
		$sql = "Select PopulateType, Populate from MessagesFieldComparisons where FieldName = '$FieldName' and ComparisonType = '$comp'";

		$results = mysql_query( $sql );

		if( !$results )
		{
			$errorStr = mysql_error();
			include "NoPermission.php";
			exit();
		}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> New Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<script>
	function res()
	{
		var res = "";
		var boxes = document.forms(0).elements;
		for( i = 0; i < boxes.length ; i++ )
		{
			if( boxes( i ).checked )
			{
				res = res + "," + boxes( i ).id;
			}
		}
		window.returnValue = res;
		window.close();
	}
	function cancel()
	{
		window.returnValue = "CANCEL";
		window.close();
	}
</script>
</HEAD>

<BODY>
<center><Button onClick="res()">OK</Button></center>
<form>

<?php
		if( $row = mysql_fetch_row( $results ) )
		{
			$Type = $row[0];
			echo "<Table><TR>";
			$Populate = $row[1];
			if( $Populate != 'null' )
			{
				$count = 0;
				$results = mysql_query( $Populate );
				if( !$results )
				{
					$errorStr = mysql_error();
					include "NoPermission.php";
					exit();
				}

				while( $row = mysql_fetch_row( $results ) )
				{
					$count++;
					$t = htmlspecialchars( $row[0] );
					$t = str_replace( "Service ", "S/" ,$t );
					$t = str_replace( "Station", "S" ,$t );
					$t = str_replace( "Services", "Ss" ,$t );
					$t = str_replace( "Filling ", "F/" ,$t );

					echo "<td><input id=$row[1] type=\"checkbox\"";

					if( strstr( $val, $row[1])  )
					{
						echo " checked";
					}
					echo ">$t</td>\n";
					if( $count % 5 == 0 )
					{
						echo "</TR><TR>";
					}
				}
			}
			echo "</TR></TABLE>";
		}
	}
?>
</form>

</BODY>
</HTML>
