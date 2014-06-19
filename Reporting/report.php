<?php
error_reporting('E_NONE');
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";


#	If you want the sql to be echoed to the screen set this to 'On'
$echo = 'On';


/*
 * Reports Page
 */

function DateSelector($selectname, $useDate=0, $hidedays=0)
{
	// if date invalid or not supplied, use current time
	if ($useDate == 0)
	{
		$useDate = time() ;
	}
	else
	{
		$useDate = strtotime($useDate);
	}

	// make day selector
	if ($hidedays == 0)
	{
		$selectHTML = "<NoBr><SELECT NAME=\"".$selectname."Day\">\n";
		for ($currentDay=1; $currentDay <= 31; $currentDay++)
		{
			$selectedflag = '';
			if (intval(date("d", $useDate)) == $currentDay)
			{
				$selectedflag = 'selected';
			}
			$selectHTML .= "<OPTION VALUE=\"$currentDay\" $selectedflag>$currentDay</OPTION>\n";
		}
		$selectHTML .= "</SELECT>\n";
	}
	else
	{
		// If we want to hide the day selector, just use a hidden field and force it be day 1
		$selectHTML = "<input name=".$selectname."Day type=\"hidden\" value=\"1\">\n";
	}

	// create array so we can name months
	$monthNames = array("January","February","March","April","May","June","July","August","September","October","November","December");

	// make month selector
	$selectHTML .= "<SELECT name=\"".$selectname."Month\">\n";
	foreach ($monthNames as $key => $monthName)
	{
		$monthno = $key + 1;
		$selectedflag = '';
		if (intval(date("m", $useDate)) == $monthno)
		{
			$selectedflag = 'selected';
		}

		$selectHTML .= "<OPTION value=\"$monthno\" $selectedflag>$monthName</OPTION>\n";
	}
	$selectHTML .= "</SELECT>\n";

	// make year selector
	$selectHTML .= "<SELECT NAME=\"".$selectname."Year\">\n";
	$thisyear = date("Y");
	for ($currentYear = 2005; $currentYear <= $thisyear; $currentYear++)
	{
		$selectedflag = '';
		if (date("Y", $useDate) == $currentYear)
		{
			$selectedflag = 'selected';
		}
		$selectHTML .= "<OPTION $selectedflag>$currentYear</OPTION>\n";
	}
	$selectHTML .= "</SELECT></NoBr>\n";

	return($selectHTML);
}


function searchlist($type, $selectname, $selected)
{
	// returns HTML for status or search options select box

	switch ($type)
	{

		case 'trackingcodes':
			$options = array(	'Description' => 'Description');
			break;
			
		case 'tracking':
			$options = array(	'Notes' => 'Notes',
						'TrackingCode' => 'TrackingCode'
						);
			break;	
			
		case 'experian':
			$options = array(	'AccountNo' => 'AccountNo',
						'MemberNo' => 'MemberNo',
						'Forename' => 'Forename',
						'PostCode' => 'PostCode',);
			break;				
			
			
			
	}
	
	

	$selectHTML = "<SELECT name=\"$selectname\">\n";
	foreach ($options as $option => $value)
	{
		$selectedflag = '';
		if ($value == $selected)
		{
			$selectedflag = 'selected';
		}

		$selectHTML .= "<OPTION value=\"$value\" $selectedflag>$option</OPTION>\n";
	}
	$selectHTML .= "</SELECT>\n";

	return($selectHTML);
}

/****************************************************************************************
	Start of Script - Check Login and User Level
****************************************************************************************/
ob_start();


// Now check users security level depending on which report we're running
// Just in case they're clever enough to change the GET varaible in the url

$type = $_GET['type'];

switch($type)
{
	case 'redeemstop':
		$reporttitle = "Accounts on RedemptionStop";
		$searchtable = 'texaco.Accounts';
		$showdatesearch = true;
		#$showorderstatus = true;
		$showcsv = true;
		
		break;
		
	case 'trackingcodes':
		$reporttitle = "Tracking Codes";
		$searchtable = 'TrackingCodes';
		$showsearch = true;
		$searchtype = 'trackingcodes';
		$showcsv = true;
		
		break;
		
	case 'tracking':
		$reporttitle = "Tracking Report";
		$searchtable = 'Tracking';
		$showsearch = true;
		$searchtype = 'tracking';
		$showdatesearch = true;
		$showcsv = true;
		
		break;	
		
	case 'experian':
		$reporttitle = "Experian Data Cleanse Report";
		$searchtable = 'experiandata';
		$showsearch = true;
		$searchtype = 'experian';
		$showcsv = true;
		
		break;			

	case 'highvalue':
		$reporttitle = "High Value Transactions";
		$searchtable = 'Transactions';
		$showdatesearch = true;
		#$showorderstatus = true;
		$showcsv = true;
		
		break;
		
	case 'orders':
		$reporttitle = "Orders";
		$searchtable = 'webmartorderheaders';
		$showdatesearch = true;
		$showorderstatus = true;
		$showcsv = true;
		
		break;



}


/****************************************************************************************
****************************************************************************************/

// page name - saves referencing the page name throughout the script
$script = $_SERVER['SCRIPT_NAME'];

// this is the page description text.
$Title = "$SiteName &#8226; Reports &#8226; $reporttitle";


// set some page details

// default results per-page.
if (isset($_GET['limit']))
{
	$limit = $_GET['limit'];
}
else
{
	$limit = 20;
}

// default page value.
if (isset($_GET['page']))
{
	$page = $_GET['page'];
}
else
{
	$page = 0;
}

$limitquery = "LIMIT $page, $limit";


// Search options
if ($_POST['formsubmit'] == '1')
{
	// do search OR status & date filter
	if (isset($_POST['searchtext']) AND $_POST['searchtext'] != '')
	{
		$searchoption = $_POST['searchoption'];
		$searchtext = $_POST['searchtext'];
		
		
		if (isset($_POST['date1Day']) AND $_POST['date1Day'] != '')
		{
			$from = $_POST['date1Year'].'-'.$_POST['date1Month'].'-'.$_POST['date1Day'];
			$to = $_POST['date2Year'].'-'.$_POST['date2Month'].'-'.$_POST['date2Day'];
		}		
		
		
		
		
		
	}
	else
	{
		if (isset($_POST['statusoption']) AND $_POST['statusoption'] != '')
		{
			$statusoption = $_POST['statusoption'];
		}

		if (isset($_POST['date1Day']) AND $_POST['date1Day'] != '')
		{
			$from = $_POST['date1Year'].'-'.$_POST['date1Month'].'-'.$_POST['date1Day'];
			$to = $_POST['date2Year'].'-'.$_POST['date2Month'].'-'.$_POST['date2Day'];
		}
	}
}
else
{
	// do search OR status & date filter
	if (isset($_GET['searchtext']) AND $_GET['searchtext'] != '')
	{
		$searchoption = $_GET['searchoption'];
		$searchtext = $_GET['searchtext'];
	}
	else
	{
		if (isset($_GET['statusoption']) AND $_GET['statusoption'] != '')
		{
			$statusoption = $_GET['statusoption'];
		}

		if (isset($_GET['from']) AND $_GET['from'] != '')
		{
			$from = $_GET['from'];
			$to = $_GET['to'];
		}
		else if ($showdatesearch)
		{
			// default to todays date if date search allowed
			$from = date('Y-m-d');
			$to = $from;
		}
	}
}

if (isset($statusoption))
{
	// create the status query depending on which field it should apply to
	if ($type == 'audit' OR $type == 'log')
	{
		$searchquery = " AND $searchtable.type='$statusoption' ";
	}
	elseif($type == 'orders')
	{
		$searchquery = " AND $searchtable.orderstatus='$statusoption' ";
	}	
	else
	{
		$searchquery = " AND $searchtable.Status='$statusoption' ";
	}
}





if (isset($searchtext))
{
	// create the search query
	$searchquery .= " AND $searchtable.$searchoption LIKE '%$searchtext%' ";
	// and create the link for subsequent refreshes
	$searchlink .= "&searchoption=$searchoption&searchtext=$searchtext";
}

if (isset($from))
{
	// create the date query depending on which field it should apply to
	switch ($type)
	{
		case 'audit':
		case 'log':
			$searchquery .= " AND $searchtable.datetime BETWEEN '$from 00:00:00' AND '$to 23:59:59' ";
			break;
			
		case 'redeemstop':
			$searchquery .= " AND $searchtable.RedemptionStopDate BETWEEN '$from 00:00:00' AND '$to 23:59:59' ";
			break;			

		case 'highvalue':
			$searchquery .= " AND $searchtable.TransTime BETWEEN '$from 00:00:00' AND '$to 23:59:59' ";
			break;
			
		case 'tracking':
			$searchquery .= " AND $searchtable.CreationDate BETWEEN '$from 00:00:00' AND '$to 23:59:59' ";
			break;	
			
		default:
			$searchquery .= " AND $searchtable.Date BETWEEN '$from' AND '$to' ";
			$searchlink .= "&from=$from&to=$to";
	}
}


// Setup the query depending on the lobby menu option used to access this page

// the first field of the Sumary statment must be the count of the total number of fields
// the rest must align with the statement above using blanks if you don't one text

switch ($type)
{
		
	case 'redeemstop':
	
		$selectFields= " Accounts.AccountNo as id,Accounts.AccountNo,Members.MemberNo,RedemptionStopDate";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(Accounts.AccountNo) as NumAccounts, ' ' as blank1, ' ' as blank2 , ' ' as blank3 ";

		$ordertype = " RedemptionStopDate is not null" ;
		$orderquery = " Accounts.AccountNo ";
		#$groupquery = " MemberNo ";
		$table = "texaco.Accounts join texaco.Members using (AccountNo) " ;
		$query = "?type=$type$searchlink";

		#$_SESSION['lastmainreport'] = "$script$query$searchlink&page=$page&limit=$limit";
		#$_SESSION['lastpage'] = "$script$query$searchlink&page=$page&limit=$limit";
		$submenu = 'submenu.php';
		
		#$action = "updateorder.php?action=release";
		#$actionlabel = "Release";
		#$action2 = "updateorder.php?action=cancel";
		#$actionlabel2 = "Cancel";
		#$action3 = "viewdetails.php?type=order";
		#$actionlabel3 = "View";
		
		break;


	case 'highvalue':
	
		$selectFields= " TransactionNo as id,AccountNo,MemberNo,TransValue";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(TransactionNo) as NumTx, ' ' as blank1, ' ' as blank2 , ' ' as blank3 ";

		$ordertype = " TransValue >= '150'";
		$orderquery = " id ";
		#$groupquery = " MemberNo ";
		$table = "texaco.Transactions " ;
		$query = "?type=$type$searchlink";

		#$_SESSION['lastmainreport'] = "$script$query$searchlink&page=$page&limit=$limit";
		#$_SESSION['lastpage'] = "$script$query$searchlink&page=$page&limit=$limit";
		$submenu = 'submenu.php';
		
		#$action = "updateorder.php?action=release";
		#$actionlabel = "Release";
		#$action2 = "updateorder.php?action=cancel";
		#$actionlabel2 = "Cancel";
		#$action3 = "viewdetails.php?type=order";
		#$actionlabel3 = "View";
		
		break;


	case 'trackingcodes':
	
		$selectFields= " TrackingCode as id,TrackingCode,Description";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(TrackingCode) as NumCodes, ' ' as blank1, ' ' as blank2 ";

		$ordertype = " 1 ";
		$orderquery = " TrackingCode ";
		$table = "texaco.TrackingCodes " ;
		$query = "?type=$type$searchlink&searchoption=$searchoption&searchtype=$searchtype";

		#$_SESSION['lastmainreport'] = "$script$query$searchlink&page=$page&limit=$limit";
		#$_SESSION['lastpage'] = "$script$query$searchlink&page=$page&limit=$limit";
		$submenu = 'submenu.php';
		
		#$action = "updateorder.php?action=release";
		#$actionlabel = "Release";
		#$action2 = "updateorder.php?action=cancel";
		#$actionlabel2 = "Cancel";
		#$action3 = "viewdetails.php?type=order";
		#$actionlabel3 = "View";
		
		break;

	case 'tracking':
	
		$selectFields= " Tracking.AccountNo as id,Tracking.AccountNo,Members.MemberNo,CONCAT_WS(Title, Forename,Surname ) as Name,TrackingCode,Notes,CreatedBy,CreationDate";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(Tracking.AccountNo) as NumCodes, ' ' as blank1, ' ' as blank2, ' ' as blank3, ' ' as blank4, ' ' as blank5";

		$ordertype = " 1 ";
		$orderquery = " TrackingCode ";
		$table = "texaco.Tracking join texaco.Members using (MemberNo)" ;
		$query = "?type=$type$searchlink";

		$submenu = 'submenu.php';
		

		
		break;
		
	case 'experian':
	
		$selectFields= " AccountNo as id,AccountNo,MemberNo,Title,Initials,Forename,Surname,Address1,Address2,Address3,Address4,Address5,PostCode,NCOAIndicator,Deceased,GoneAway";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(AccountNo) as NumAccounts, ' ' as blank1, ' ' as blank2, ' ' as blank3, ' ' as blank4, ' ' as blank5, ' ' as blank6 , ' ' as blank7, ' ' as blank8, ' ' as blank9, ' ' as blank10, ' ' as blank11, ' ' as blank12, ' ' as blank13, ' ' as blank14, ' ' as blank15 ";

		$ordertype = " 1 ";
		$orderquery = " AccountNo ";
		$table = "texaco.experiandata" ;
		$query = "?type=$type$searchlink";

		$submenu = 'submenu.php';
	
		break;	
		
		
		
	case 'orders':
	
		$selectFields= " id, id as OrderNo,datetime, cardholderName, charityid,orgname as Organisation,TxType,orderstatus,AddressChange,transTotal as OrderTotal";
		# the first field of the Summary statement must be the count of the total number of fields
		# the rest must align with the statement above using blanks if you don't one text
		$summaryFields = " count(id) as totaltransactions, ' ' as blank1,  ' ' as blank2, ' ' as blank3, ' ' as blank4, ' ' as blank5, ' ' as blank6 , ' ' as blank7 , ' ' as blank8,sum(transTotal) as totalamount";

		$ordertype = " Status = 'OK' " ;
		$groupquery = " OrderNo ";
		$table = "webmartorderheaders" ;
		$query = "?type=$type$searchlink";

		#$_SESSION['lastmainreport'] = "$script$query$searchlink&page=$page&limit=$limit";
		$_SESSION['lastpage'] = "$script$query$searchlink&page=$page&limit=$limit";
		$submenu = 'submenu.php';
		
		$action = "updateorder.php?action=release";
		$actionlabel = "Release";
		$action2 = "updateorder.php?action=cancel";
		$actionlabel2 = "Cancel";
		$action3 = "viewdetails.php?type=order";
		$actionlabel3 = "View";
		
		break;
		






}

/****************************************************************************************
	First sql query calculates all the summary information
****************************************************************************************/

$sql = "SELECT
			$summaryFields
		FROM
			$table
		WHERE
			$ordertype $searchquery";
//echo $sql.'<br>';
/****************************************************************************************
****************************************************************************************/

$results = DBQueryExitOnFailure($sql);
$summaryrow = mysql_fetch_row($results);

$numrows = $summaryrow[0];

# number of results pages.
# $pages now contains int of pages,
# unless there is a remainder from division.

# number of results pages.


# $pages now contains int of pages, unless there is a remainder from division.

# has remainder so add one page

if ($numrows == 0)
{
	$first = 0;
	$last  = 0;
	$current = 0;
	$pages = 0;
}
else
{
	$pages = intval($numrows/$limit);
	if ($numrows % $limit)
	{
		$pages++;
	}

	# current page number.

	$current = intval($page/$limit);

	# the first result.

	$first = $current * $limit + 1;

	if ($current != $pages )
	{
		# if not last results page, last result equals $first plus $limit.
		$last = $first + $limit - 1;
	}
	else
	{
		# if last results page, last result equals
		# total number of results.
		$last = $numrows;
	}
	$current++;
}


$PageLinks = " ";
if ($page != 0)
{
	$back_page = $page - $limit;
	$PageLinks = "<a href=\"$script$query&page=$back_page&limit=$limit\"><span class=\"bodytext\">back</span></a>\n";
}
# loop through each page and give link to it.

for ($i=1; $i <= $pages; $i++)
{
	$ppage = $limit*($i - 1);
	if ($i == $current)
	{
		$PageLinks = $PageLinks . "<span class=\"bodytext\"><b>$i</b></span>\n";
	}
	else
	{
		$PageLinks = $PageLinks . "<a href=\"$script$query&page=$ppage&limit=$limit\"><span class=\"bodytext\">$i</span></a>\n" ;
	}
}

if ($current != $pages && $pages > 1)
{
	// If last page don't give next link.
	$next_page = $page + $limit;
	$PageLinks = $PageLinks . "    <a href=\"$script$query&page=$next_page&limit=$limit\"><span class=\"bodytext\">next</span></a>";
}


# the first result.
$first = $page + 1;

# if not last results page, last result equals $page plus $limit.
if (!((($page + $limit) / $limit) >= $pages) AND $pages != 1)
{
	$last = $page + $limit;
}
else
{
	# if last results page, last result equals
	# total number of results.
	$last = $numrows;
}


/****************************************************************************************
	Start of HTML
****************************************************************************************/
?>

<html>
<head>
<title><? echo $_SESSION['pagetitle']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="css/module.css" rel="stylesheet" type="text/css">
</head>

<body marginheight="0" bgcolor="#FFFFFF">
<table align="center" width="95%" border="0" cellpadding="2" align="top">
	<tr>
		<td colspan="2" align="left" class="HeaderMainBlue"><?php echo $Title ; ?> </td>
	</tr>
	<tr>
		<td colspan="2" align="left"><? include($submenu); ?></td>
	</tr>
	<tr>
		<td colspan="2" align="left" class="bodytext">
		<? if ($searchtable != '')
		{
			echo "<form action=\"$script$query&limit=$limit\" method=\"post\">";

			if ($showorderstatus)
			{
				echo "Show ";
				echo searchlist('orderstatus','statusoption',$statusoption);
				echo " ";
			}
			if ($showdatesearch)
			{
  				echo " between ";
  				echo DateSelector("date1", $from);
  				echo " and ";
  				echo DateSelector("date2", $to);
			}

			if ($showsearch)
			{
				echo "<br>Search ";
				echo searchlist($searchtype,'searchoption',$statusoption);
				echo "contains ";
				echo "<input name=\"searchtext\" value=\"$searchtext\" type=\"text\" class=\"texta\" id=\"searchtext\" size=\"12\" maxlength=\"20\">";
				echo " ";
			}

			echo "<input type=\"hidden\" name=\"formsubmit\" value=\"1\">";
			echo "<input type=\"submit\" name=\"action\" value=\"Submit\">";

			echo "</form>";
		}
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="left" class="bodytexttiny"> Results per-page:
			<a href="<?=$script.$query;?>&page=<?=$page?>&limit=5">
			<span class="bodytexttiny">5</span></a>
			| <a href="<?=$script.$query;?>&page=<?=$page?>&limit=10">
			<span class="bodytexttiny">10</span></a>
			| <a href="<?=$script.$query;?>&page=<?=$page?>&limit=20">
			<span class="bodytexttiny">20</span></a>
			| <a href="<?=$script.$query;?>&page=<?=$page?>&limit=50">
			<span class="bodytexttiny">50</span></a>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="left" valign="top">
			<?php print $PageLinks; ?>
		</td>
	</tr>
	<tr>
		<td align="left" class="bodytext"> Showing Results <b>
			<?=$first?>
			</b> - <b>
			<?=$last?>
			</b> of <b>
			<?=$numrows?>
			</b>
		</td>
		<td  align="right" > <font size="2" face="Arial, Helvetica, sans-serif">
			<span class="bodytext">Page <b>
			<?=$current?>
			</b> of <b>
			<?=$pages?>
			</b> </span></font>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="left" class = "headertext">

			<?php echo $heading;
			if ($showcsv)
			{
			?>

			<a href="reportcsv.php<? echo $query ;?>" target="_blank">&#8226; run this report as a csv file &#8226;</a>

			<?
			}
			?>


<?php
/****************************************************************************************
	Second sql query, this one is for the main body of the report
****************************************************************************************/

$sql = "SELECT
			$selectFields
		FROM
			$table
		WHERE
			$ordertype $searchquery";

if ($groupquery != '')
{
	$sql .= "GROUP BY $groupquery $limitquery";
}
else
{
	$sql .= "ORDER BY $orderquery $limitquery";
}

if($echo == 'On')
{
	echo $sql.'<br>';
}
/****************************************************************************************
****************************************************************************************/


$results1 = DBQueryExitOnFailure( $sql );

// Show field headers
print( "<table width = 100%><tr bgcolor=\"#6699FF\" class = \"headertext\">");
$fields = mysql_num_fields( $results1 );

if ($type == 'batcollectsum')
{
	// don't display the last field for this report, it's used to see if a statement exists
	$fields--;
}

for( $k = 1; $k < $fields; $k++)
{
	print( "<td align=\"center\">".mysql_field_name( $results1, $k ). "</td>\n" );
}

// field header for Action column
if ($action != '')
{
	print( "<td align=\"center\">Action</td>\n" );
}

print( "</tr>\n" );

$count = 1;
while($row = mysql_fetch_row($results1))
{
	$count++;

	// decide the background display colour for the row
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

	if (isset($datefieldpos))
	{
		// calculate 6 working days from the batch creation date
		$finalisedate = workingdays(6, $row[$datefieldpos]);
		$finalisestamp = strtotime($finalisedate);

		if ($todaystamp >= $finalisestamp)
		{
			$finalised = 1;

			if ($count & 1)
			{
				$color = "#F26C4F";
				$font = "#004080";
			}
			else
			{
				$color = "#F69679";
				$font = "#004080";
			}
		}
	}

	// loop through the current record
	// outputting each field's contents in a separate column in the table
	print( "<tr class = \"bodytext\" bgcolor=$color>\n" );
	for( $k = 1; $k < $fields; $k++)
	{
		if( mysql_field_type( $results1, $k ) == "real" )
		{
			print( "<td bgcolor=$color align=\"right\"> " );
		}
		else
		{
			print( "<td align=\"center\">" );
		}

		// output the data as plain text
		print( $row[ $k ]. "</td>\n" );
	}

	// now create the links for the Action column for editing records etc.
	$hideaction = 0;
	$hideaction2 = 0;
	if ($action != '')
	{
		/*
		if (($type == 'batcollect' OR $type == 'collect') AND $row[$statusfieldpos] == 'FAILED')
		{
			// use alt action
			print("<td align=\"center\"><a href=\"$actionalt&id=".$row[0]."\">$actionlabelalt</a>");
		}
		*/
		if (($type == 'batcollect' OR $type == 'collect') AND $row[$statusfieldpos] != 'SUCCESS')
		{
			$hideaction = 1;
			print("<td align=\"center\">");
		}
		else if (($type == 'batmandate') AND $row[$statusfieldpos] != 'LIVE')
		{
			$hideaction = 1;
			print("<td align=\"center\">");
		}
		else
		{
			print("<td align=\"center\"><a href=\"$action&id=".$row[0]."\">$actionlabel</a>");
		}

		if ($action2 != '')
		{
			if ($type == 'batcollectsum' AND $finalised == 1)
			{
				if (is_null($row[$statementfieldpos]))
				{
					// use alt action (Reconcile) for batches after 6 days with no related statement
					print(" <a href=\"$actionalt2&id=".$row[0]."\">$actionlabelalt2</a>");
				}
				else
				{
					$hideaction2 = 1;
				}
			}
			else if (!($type == 'collect' AND $row[$statusfieldpos] != 'NEW'))
			{
				if ($hideaction != 1)
				{
					print(" / ");
				}
				print("<a href=\"$action2&id=".$row[0]."\">$actionlabel2</a>");
			}
			else
			{
				$hideaction2 = 1;
			}

			if ($action3 != '')
			{
				if (!($type == 'batcollectsum' AND $row[$sentfieldpos] != ''))
				{
					if ($hideaction2 != 1 OR $hideaction1 != 1)
					{
						print(" / ");
					}
					print("<a href=\"$action3&id=".$row[0]."\">$actionlabel3</a></td>\n");
				}
			}
			else
			{
				print("</td>\n");
			}
		}
		else
		{
			print("</td>\n");
		}
	}

	print( "</tr>\n" );
}

// summary row
echo "<tr bgcolor=\"#6699FF\" class = \"bodytext\">";
for( $k = 1; $k < $fields ; $k++)
{
	if( @mysql_field_type( $results, $k ) == "real" )
	{
		print( "<td align=\"right\"> " );
	}
	else
	{
		print( "<td align=\"center\">" );
	}
	print( $summaryrow[ $k ]. "</td>\n" );
}

if ($action != '')
{
	if ($mainaction != '')
	{
		print("<td bgcolor=\"#ccffff\" align=\"center\"><a href=\"$mainaction\">$mainactionlabel</a></td>\n");
	}
	else
	{
		print("<td>&nbsp;</td>\n");
	}
}

print( "</tr></table>\n" );

mysql_free_result($results);
mysql_free_result($results1);
?>

		</td>
	</tr>
	<tr>
		<td align="left" class="bodytext"> Showing Results <b>
			<?=$first?>
			</b> - <b>
			<?=$last?>
			</b> of <b>
			<?=$numrows?>
			</b>
		</td>
		<td align="right" class="bodytext"> Page <b>
			<?=$current?>
			</b> of <b>
			<?=$pages?>
			</b>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="left" valign="top">
			<?php print $PageLinks; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="left" class="bodytexttiny"> Results per-page:
			<a href="<?=$script.$query?>&page=<?=$page?>&limit=5">
			<span class="bodytexttiny">5</span></a>
			| <a href="<?=$script.$query?>&page=<?=$page?>&limit=10">
			<span class="bodytexttiny">10</span></a>
			| <a href="<?=$script.$query?>&page=<?=$page?>&limit=20">
			<span class="bodytexttiny">20</span></a>
			| <a href="<?=$script.$query?>&page=<?=$page?>&limit=50">
			<span class="bodytexttiny">50</span></a>
		</td>
		<td colspan="2" align="left"></td>
	</tr>
	<tr>
		<td width="50%" align="left" valign="bottom">&nbsp;</td>
		<td width="50%" align="right" valign="bottom">&nbsp;</td>
	</tr>
</table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="3">
	<tr>
		<td align="left">
			<a href="http://www.rsm2000.com"><span class="copyright">&#8249;&#8249;
            &copy; <?php echo date("Y") ?> - rsm2000 limited &#8250;&#8250;</span></a>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>
</body>
</html>
