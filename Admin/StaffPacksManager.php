<?php 

	include "../include/Session.inc";
	include "../include/DisplayFunctions.inc";
//	include "../DBInterface/CardRequestInterface.php";
	include "../DBInterface/WebRegistrations.php";
	include "../DBInterface/WelcomePackInterface.php";
	include "../DBInterface/BonusInterface.php";

//	$results = GetUnsatisifiedCardRequestBatches( 7 );

	$PromotionCodes = GetBonusList();

	$PromotionCodes[""] = "";

	$Title = "Welcome Pack Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="StaffIncentive";
	$HelpPage = "WelcomePacks";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";

$startDate = "";

#	This script can take a while to process
set_time_limit(0);

?>
<script language="JavaScript" src="overlib_mini.js"></script>
<script language="JavaScript" src="DatePicker.js"></script>

<center>

<TABLE>
<TR valign=top>
<TABLE>
<? 
// MRM 30 04 09 Mantis 963 Only display 2009 registrations
$sql = "SELECT RunAt FROM OutputFiles WHERE Type = 'StaffRegistrations' AND RunAt > '2009-04-11 00:00:00' ORDER BY RunAt ASC";
$results = DBQueryExitOnFailure( $sql );
$numrows = mysql_num_rows($results);
if( $numrows >0 )
{
?>
	<div align="center"><center>Previous Batch Runs
	<table border="0" cellpadding="2" cellspacing="2">
  	<tr><td align=center>Start Date</td><td align=center>End Date</td></tr>
  	<tr><td align="center">0000-00-00 00:00:00</td>
<?
	while( $row = mysql_fetch_assoc( $results ) )
	{
	echo "<td align=center>".$row['RunAt']."</td></tr><tr><td align=center>".$row['RunAt']."</td>";
	}

//	echo"</tr></table></center></div></div></td></tr>";
}
?>
                <td><p align="center"><fieldset><legend>Create Welcome Packs For Staff</legend> </p>
                <form name="BonusForm" action="WelcomeStaffPackCreate.php"
                onSubmit="return CheckNewBatchData();" method="POST">
                  <div align="center"><center><p><input type="submit" value="Create New Batch" /></fieldset> </p>
                  </center></div>
                </form>
                </td>
              </tr>
            </table>
            </center></div></div></td>
          </tr>
          <tr>
            <td></td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    </center></div></td>

	<?php
	echo "</TABLE></center></div></div></td>\n";
	include "../MasterViewTail.inc";
?>