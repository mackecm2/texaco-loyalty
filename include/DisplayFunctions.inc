<?php

	function DisplayFraudSelectOptions( $Options, $Selected )
	{
		foreach( $Options As $val => $opt )
		{
			if( $val == $Selected )
			{
				$Sel = " Selected";
			}
			else
			{
				$Sel = "";
			}
			if ( $opt)
			{
				echo "<option value=\"$val\" $Sel> $opt </option>\n";
			}			
		}
	}
	
	function DisplaySelectOptions( $Options, $Selected )
	{
		foreach( $Options As $val => $opt )
		{
			if( $Selected == $val )
			{
				$Sel = " Selected";
			}
			else
			{
				$Sel = "";
			}
			echo "<option value=\"$val\" $Sel> $opt </option>\n";
		}
	}

	function DisplayCheckBox( $name, $value, $misc )
	{
		if( $value == false )
		{
			echo "<input id=$name type=\"checkbox\" name=\"$name\"  $misc>\n";
		}
		else
		{
			echo "<input id=$name type=\"checkbox\" name=\"$name\" $misc checked>\n";
		}

	}

	function DisplayRadioButton( $name, $value, $current, $misc )
	{
		if( $value == $current )
		{
			echo "<input type=\"Radio\" name=\"$name\"  $misc value=\"$value\" checked>\n";
		}
		else
		{   // some buttons are disabled depending on current value of Fraud Status MRM 30 APR 10 
			$disabled ="";
			if( $name == "UnderInvestigation" or $name == "PreviouslyInvestigated" or $name == "Cleared" or $name == "Fraud" or $name == "NoAction" ) 
			{
				switch ($current)
				{
				case "1":
				  if( $value == "0" or $value == "2")
				  {
				  	$disabled ="disabled";
				  }
				  break;
				case "2":
				  if( $value == "0" or $value == "3" or $value == "4")
				  {
				  	$disabled ="disabled";
				  }
				  break;
				case "3":
				  if( $value == "0" or $value == "4")
				  {
				  	$disabled ="disabled";
				  }
				  break;
				case "4":
				  if( $value == "0" or $value == "2" or $value == "3")
				  {
				  	$disabled ="disabled";
				  }
				  break;
				default:
				  $disabled ="";
				} 
				$disabled ="disabled";  // temporary measure to disable all buttons MRM 26 MAY 10
				if( CheckPermisions(PermissionsConfigPages))
				{
					$disabled ="";  // changed this to "" when status change process is approved for implementation MRM 13 SEP 10 Mantis 2510 
				}
				         
			}
			
			echo "<input $disabled type=\"Radio\" name=\"$name\"  $misc value=\"$value\">\n";
		}
		if( $setdate && $value == $current )
		{
			echo "<input size=8 disabled value=".date('d/m/Y',strtotime($setdate))." name=FraudStatusSetDate>";
		}
		
	}

	function DisplayPrintableTable(  $results, $grpCols )
	{
		// Show field headers
		$tbhead = "<table cellpadding=-10 width = 100% style=\"font-size:xx-small; \"><tr bgcolor=\"#6699FF\" class = \"headertext\">";
		$fields = mysql_num_fields( $results );
		for( $k = $grpCols; $k < $fields; $k++)
		{
			$tbhead .= "<td align=\"center\">".mysql_field_name( $results, $k ). "</td>\n";
		}
		$tbhead .= "</tr>";

		$lastGroup = "";
		if( $grpCols == 0 )
		{
			echo $tbhead ;
		}
		while($row = mysql_fetch_row($results))
		{
			$grpBanner = "";
			for( $k = 0; $k < $grpCols; $k++)
			{
				$grpBanner .= mysql_field_name( $results, $k ) . " = " . $row[ $k ] ." ";
			}

			if( $grpBanner != $lastGroup )
			{
				if( $lastGroup != "" )
				{
					echo "</table><br>";
				}
				echo "<span style=\"font-size:xx-small; \"> $grpBanner.$tbhead</span>" ;
				$lastGroup = $grpBanner;
			}
			print( "<tr class = \"bodytext\">" );
			for( $k = $grpCols; $k < $fields; $k++)
			{
				if( mysql_field_type( $results, $k ) == "real" )
				{
					echo( "<td align=\"right\"> " );
				}
				else
				{
					echo( "<td align=\"center\">" );
				}
				echo( $row[ $k ]. "</td>\n" );
			}

		}
		echo( "</table>\n" );

	}

	function DisplayTable( $results, $grpCols )
	{
		// Show field headers
		$tbhead = "<table width = 100%><tr bgcolor=\"#6699FF\" class = \"headertext\">";
		$fields = mysql_num_fields( $results );
		for( $k = $grpCols; $k < $fields; $k++)
		{
			$tbhead .= "<td align=\"center\">".mysql_field_name( $results, $k ). "</td>\n";
		}
		$tbhead .= "</tr>";

		$lastGroup = "";
		$count = 1;
		if( $grpCols == 0 )
		{
			echo $tbhead ;
		}
		while($row = mysql_fetch_row($results))
		{
			$grpBanner = "";
			for( $k = 0; $k < $grpCols; $k++)
			{
				$grpBanner .= mysql_field_name( $results, $k ) . " = " . $row[ $k ] ." ";
			}

			if( $grpBanner != $lastGroup )
			{
				if( $lastGroup != "" )
				{
					echo "</table>";
				}
				echo $grpBanner.$tbhead ;
				$lastGroup = $grpBanner;
			}
			$count++;
			if ($count & 1)
			{
				$color = "#99CCFF";
				$font = "#004080";
			}
			else
			{
				$color = "#ccffff";
				$font = "#004080";
			}
			print( "<tr class = \"bodytext\" bgcolor=$color>\n" );
			for( $k = $grpCols; $k < $fields; $k++)
			{
				if( mysql_field_type( $results, $k ) == "real" )
				{
					echo( "<td bgcolor=$color align=\"right\"> " );
				}
				else
				{
					echo( "<td align=\"center\">" );
				}
				echo( $row[ $k ]. "</td>\n" );
			}

		}
		echo( "</table>\n" );

	}

	function DisplayTableWithClick( $results, $grpCols, $click, $clickrow )
	{
		// Show field headers
		$tbhead = "<table width = 100%><tr bgcolor=\"#6699FF\" class = \"headertext\">";
		$fields = mysql_num_fields( $results );
		for( $k = $grpCols; $k < $fields; $k++)
		{
			$tbhead .= "<td align=\"center\">".mysql_field_name( $results, $k ). "</td>\n";
		}
		$tbhead .= "</tr>";

		$lastGroup = "";
		$count = 1;
		if( $grpCols == 0 )
		{
			echo $tbhead ;
		}
		while($row = mysql_fetch_row($results))
		{
			$grpBanner = "";
			for( $k = 0; $k < $grpCols; $k++)
			{
				$grpBanner .= mysql_field_name( $results, $k ) . " = " . $row[ $k ] ." ";
			}

			if( $grpBanner != $lastGroup )
			{
				if( $lastGroup != "" )
				{
					echo "</table>";
				}
				echo $grpBanner.$tbhead ;
				$lastGroup = $grpBanner;
			}
			$count++;
			if ($count & 1)
			{
				$color = "#99CCFF";
				$font = "#004080";
			}
			else
			{
				$color = "#ccffff";
				$font = "#004080";
			}
			print( "<tr class = \"bodytext\" bgcolor=$color onclick=\"$click('$row[$clickrow]')\">\n" );
			for( $k = $grpCols; $k < $fields; $k++)
			{
				if( mysql_field_type( $results, $k ) == "real" )
				{
					echo( "<td bgcolor=$color align=\"right\"> " );
				}
				else
				{
					echo( "<td align=\"center\">" );
				}
				echo( $row[ $k ]. "</td>\n" );
			}

		}
		echo( "</table>\n" );

	}

?>