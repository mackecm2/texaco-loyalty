<?php  
	include "../include/Session.inc";
	include "../DBInterface/LettersInterface.php";
	include "../include/Locations.php";

	$results = GetUnconfirmedPrintBatches( 7 );

	$Title = "Letter Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$HelpPage = "MailMerge";
	$but="MailMerge";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";
?>

<SCRIPT>

warnOnce = true;

function BookmarkReplace( oDoc, bName, repText )
{
		//alert( bName + repText );
		try
		{
			someObject = oDoc.Bookmarks(bName);
			someObject.Range.Text = repText;
			//document.write( "," + repText ); 
		}
		catch( e )
		{
			//alert( "Failed" );
		}
}

function CreateDocument( oApp, template, record )
{	
	try
	{
		oDoc = oApp.Documents.Add( <?php echo "\"".MailTemplateLocations."\""; ?> + template );
	}
	catch( e )
	{
		alert( "Failed to print template" + template );
		return 0;
	}
	if( typeof(oDoc) == "undefined" )
	{
		if( warnOnce )
		{
			alert( "Failed to create document"+ template + "Check "+<?php echo "\"".MailTemplateLocations."\""; ?> );
		}
		warnOnce = false;
		return 0;
	}
	cNode = record.firstChild;
	while( cNode )
	{
		try
		{
			nodeName = cNode.nodeName ;
			nodeValue = cNode.firstChild.nodeValue ; 
			BookmarkReplace( oDoc, nodeName, nodeValue );
		}
		catch( e )
		{
			//alert( "Failed 2 " );
		}
		cNode = cNode.nextSibling;
	}
	oApp.Options.PrintBackground = false;
	oDoc.PrintOut();
	oDoc.Close( false );
	return 1;
}

function PrintBatch( tim )
{
	warnOnce = true;
	XMLDoc = new ActiveXObject("Microsoft.XMLDOM");
	XMLDoc.async = false; 
	lf = "MailData.php";
	if( tim != "" )
	{
		lf += "?Repeat=" + tim;
	}
	XMLDoc.load(lf);

//	window.location = lf;

	oApp = new ActiveXObject("Word.Application");

	root = XMLDoc.documentElement;
	records = XMLDoc.getElementsByTagName("record");
	n_records = records.length;
	suc = 0;
	for (i = 0; i < n_records; i++) 
	{ 
		record = records.item(i); 
		attr = record.getAttribute("template"); 
		suc += CreateDocument( oApp, attr, record );
	}

	oApp.Quit( );
	alert( "Finished printing " + suc + " of " + n_records );
	return false;
}

function ConfirmBatch( tim )
{
	window.location = "ConfirmPrint.php?Timestamp="+ tim;
}



</SCRIPT>

<center>


<table>


<tr><th>Print Time</th><th>Unconfirmed</th></tr>

<?php
	if( mysql_num_rows( $results ) == 0 )
	{
		echo "<tr><td colspan=2>No Outstanding Letters</tr>";
	}
	while( $row = mysql_fetch_assoc( $results ) )
	{
		echo "<tr onmouseover=\"this.style.backgroundColor='blue'\" onmouseleave=\"this.style.backgroundColor=''\" ><td>";
		if( $row["PrintStamp"] == "" )
		{
			echo "New Batch";
		}
		else
		{
			echo $row["PrintStamp"] ;
		}
		echo "</td><td>$row[Unconfirmed]</td>";
		if( $row["PrintStamp"] == "" )
		{
			echo "<td><button onclick=\"PrintBatch('')\">Print</Button>\n";
		}
		else
		{
			echo "<td><button onclick=\"PrintBatch('$row[PrintStamp]')\">Re-Print</Button>\n";
			echo "<td><button onclick=\"ConfirmBatch('$row[PrintStamp]')\">Confirm</Button>\n";
		}
		echo "</tr>\n";
	}
	echo "</table></center>";
	include "../MasterViewTail.inc";
?>

