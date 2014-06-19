<?
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// A simple series of functions to help with debugging PHP bugs in my scripts...
// randell d @ fiprojects . com 2003/2004
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function dumpArray($anArray)
{	// DEBUG - Simple function to dump the contents of an array in to an HTML table
		if(!is_array($anArray))
		{ return(FALSE); }
	// Sort the array by its key/elements (as opposed to element values) This can be useful
	// to help us find a specific key in a large array....
		ksort($anArray);
	// $count is a numeric counter just for reference - note, because we sort our array
	// the counter does not nescessarily have to correspond with numeric indexed arrays
		$count=0;
	// Draw the beginings of our table
		print("<hr><table width=98% border=1 cellspacing=1 cellpadding=1>");
	// Loop through each element in the array
		foreach($anArray as $key=>$value)
		{	// This conditional statement allows us to display multi-dimensional arrays
			// by simply calling ourself over and over until we get to a string (as opposed
			// to array) variable
				if(is_array($value))
				{ print("\n<table width=98% border=0 cellspacing=0 cellpadding=0><tr><th nowrap width='5%' align=middle>$key</th><td align=left width='5%'>");
					dumpArray($value);
				  print("</td></tr></table>");
				}
				else
				{	// Display the counter, index/key/element name and index/key/element value
					// in three different cells
					print("\n<tr><th width='5%' align=right nowrap>$key</th><td width='5%' align=left>$value</td></tr>"); 
				}
			// Increment our counter for reference only
			$count++;
		}
	// Close our table
		print("</table>");
	// Return control to the parent calling function
	return;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
function pumpArray($anArray)
{	// DEBUG - Simple function to dump the contents of an array in to an HTML table and die
	dumpArray($anArray);
	die("<hr>");
}

function chowArray($anArray)
{	// DEBUG - Simple function to dump the contents of an array to our command line screen
		if(!is_array($anArray))
		{ return(FALSE); }
	// Sort the array by its key/elements (as opposed to element values) This can be useful
	// to help us find a specific key in a large array....
		ksort($anArray);
	// $count is a numeric counter just for reference - note, because we sort our array
	// the counter does not nescessarily have to correspond with numeric indexed arrays
		$count=0;
	// Loop through each element in the array
		print("\n");
		foreach($anArray as $key=>$value)
		{	// This conditional statement allows us to display multi-dimensional arrays
			// by simply calling ourself over and over until we get to a string (as opposed
			// to array) variable
				if(is_array($value))
				{ chowArray($value); }
				else
				{	// Display the counter, index/key/element name and index/key/element value
					// in three different cells
					print("\n$count - $key - $value"); 
				}
			// Increment our counter for reference only
			$count++;
		}
	// Return control to the parent calling function
	print("\n");
	return;
}

function testCLI()
{	////////////////////////////////////////////////////////////////////////////////////////////////////
	// Simple function to test if we are running from the command line or from a web client
	// We know this because DOCUMENT_ROOT has a value if we're running from within a client - its
	// defined, but empty when PHP is run form CLI.
	// We return TRUE if we are using CLI - False otherwise
		if(strlen($_SERVER['DOCUMENT_ROOT'])>0)
		{	// Return FALSE because we are not in CLI mode (we're in web browser mode).
			return(FALSE); 	
		}
		// Return true because we are in CLI mode
		return(TRUE);
}
?>