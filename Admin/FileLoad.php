<?php
	include "../include/Session.inc";

	$Title = "File Upload Manager";
	$currentPage = "End Of Day";
	$cButton = "";
	$but="FileLoad";
	$HelpPage = "LoadFile";
	include "../MasterViewHead.inc";
	include "EndOfDayButtons.php";

?>

<form enctype="multipart/form-data" action="FileUpload.php" method="post">
	<center>
	<table><TR><TH colspan=5>File Type</TH>
	<TR>
	  <TD>Generic File Load
	  </TD>
	  <TD><input type="radio" name="filetype" value="Bulk">
	  </TD>
	  <TD width=50><TD>Registration File</TD>
	  </TD>
	  <TD><input type="radio" name="filetype" value="Registration" checked>
	  </TD>
	</TR>
	<TR>
	  <TD>Manual Credits
	  </TD>
	  <TD><input type="radio" name="filetype" value="MTV">
	  </TD>
	  <TD width=50><TD>Request File Load</TD>
	  </TD>
	  <TD><input type="radio" name="filetype" value="Request">
	  </TD>
	</TR>
	<TR>
	  <TD>Bonus Mailing List
	  </TD>
	  <TD><input type="radio" name="filetype" value="BonusMail">
	  </TD>
	  <TD></TD>
	  <TD>Gone Away List
	  </TD>
	  <TD><input type="radio" name="filetype" value="Gone">
	  </TD>
	</TR>
	<TR>
	  <TD>Scratch card
	  </TD>
	  <TD><input type="radio" name="filetype" value="ScratchCards">
	  </TD>
	  <TD></TD>
	  <TD>Q8 Merge
	  </TD>
	  <TD><input type="radio" name="filetype" value="Q8Merge">
	  </TD>
	</TR>
	<TR>
	  <TD>Tracking File
	  </TD>
	  <TD><input type="radio" name="filetype" value="Tracking">
	  </TD>
	  <td></TD>
	  <TD>Answers Import File
	  </TD>
	  <TD><input type="radio" name="filetype" value="Answers">
	  </TD>
	  <td></TD>
	  <TD></TD>
	  <td></TD>
	</TR>
		<TR>
	  <TD>Group Loyalty File
	  </TD>
	  <TD><input type="radio" name="filetype" value="GroupLoyalty">
	  </TD>
	  <td>	  <?php 
	   $sql = "SELECT AccountNo, Comments FROM CardRanges JOIN Accounts USING( AccountNo ) WHERE AccountType = 'G'";
			$results = DBQueryExitOnFailure( $sql );
			echo "<select id='accounts' name=AccountNo>";
			while( $row = mysql_fetch_assoc( $results ) )
			{
				echo "<option value='".$row['AccountNo']."' > ".$row['Comments']."</option>";
			}
			echo "</select>";
	      ?></TD>
	  <TD></TD>
	  <TD>
	  </TD>
	  <td></TD>
	  <TD></TD>
	  <td></TD>
	</TR>
	</table>
	 <input type="hidden" name="MAX_FILE_SIZE" value="32000000" />
	 <BR>Send this file: <input name="userfile" type="file" size=100 />
	<BR><input type="submit" value="Send File" /></center>
</form>
<BR>
<?php 
	include "../MasterViewTail.inc";
?>