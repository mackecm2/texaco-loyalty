<?php

	include "../include/Session.inc";
	include "../include/CacheCntl.inc";

	echo "<?xml version=\"1.0\"?>\n";


	$FieldName = $_GET['FieldName'];
	if( isset( $_GET['Comp'] ) )
	{
		$comp = $_GET['Comp'];
		if ( $_GET['Comp'] != "<")
		{
			$comp = strip_tags( $_GET['Comp'] );
		}
		
		$sql = "Select PopulateType, Populate from FieldComparisions where FieldName = '$FieldName' and ComparisionType = '$comp'";

		$results = mysql_query( $sql );
		if( !$results )
		{
			echo "<error>".mysql_error()."</error>\n";
		}
		else
		{
			if( $row = mysql_fetch_row( $results ) )
			{
				$Type = $row[0];
				echo "<$Type>";
				$Populate = $row[1];
				if( $Populate != 'null' )
				{
					$results = mysql_query( $Populate );
					if( !$results )
					{
						echo "<error value='error'>".mysql_error()."</error>\n";
					}
					else
					{
					
						while( $row = mysql_fetch_row( $results ) )
						{
							$t = htmlspecialchars( $row[0] );
							echo "<entry value='$row[1]'>$t</entry>\n";
						}
					}
				}
				echo "</$Type>";
			}
			else
			{
				echo "<error>No Entries</error>\n";
			}
		}
	}
	else
	{
		echo "<Comps>";
		$sql = "Select ComparisionType from FieldComparisions where FieldName = '$FieldName'";

		$results = mysql_query( $sql );

		if( !$results )
		{
			echo "<entry>".mysql_error()."</entry>\n";
		}
		else
		{
			while( $row = mysql_fetch_row( $results ) )
			{
				$t = htmlspecialchars( $row[0] );
				echo "<entry>$t</entry>\n";
			}
		}
		echo "</Comps>";
	}

?>
