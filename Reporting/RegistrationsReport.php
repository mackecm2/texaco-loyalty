<?php 
	
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include_once "../include/DB.inc";

?>
<html>
<Head>
<Title>Registrations Report</Title>
</Head>
<body>
<form>
<input type="button" value="Close Window" onClick="window.close()">
</form>

<?php
	connectToDB( MasterServer, TexacoDB );
	$today = date("Y-m-d");  
	$staffid = $_GET['StaffID'];
	$sql = "SELECT Valid, COUNT( Valid ) AS Total FROM CustomerRegistrations WHERE StaffID = ".$staffid." AND CreationDate > '2009-05-25 00:00:00' GROUP BY Valid";
	$res1 = DBQueryExitOnFailure( $sql );
	echo "Staff ID ".$staffid ;
?>	
	<h1>Report On Staff Registrations</h1>
	<table border="0" cellpadding="5" cellspacing="0">
  	<tr>
    <td>Valid?</td>
    <td>Totals</td>
   	</tr>
<?php  	
	while( $row = mysql_fetch_assoc( $res1 ) )
	{
		echo "<tr><td>".$row["Valid"]."</td><td>".$row["Total"]."</td></tr>";
 	}
 	?>
</table><hr>
  	
<?php	
	$sql = "SELECT * FROM CustomerRegistrations WHERE StaffID = ".$staffid;
	$res = DBQueryExitOnFailure( $sql );
	
	$count = 1;

?>
	<table border="0" cellpadding="5" cellspacing="0">
	<tr><td>Member No</td><td>Account No</td><td>Date Created</td><td>Created By</td><td>Valid?</td></tr>
<?php		
	while( $row = mysql_fetch_assoc( $res ) ) 
	{
		
			$count++;
			if ($count & 1)
			{
				$color = "#99CCFF";
			}
			else
			{
				$color = "#ccffff";
			}
			echo "<tr bgcolor=$color><td>".$row["MemberNo"]."</td>"; 
			echo "<td>".$row["AccountNo"]."</td>"; 
			echo "<td>".$row["CreationDate"]."</td>";
			echo "<td>".$row["CreatedBy"]."</td>";
			echo "<td>".$row["Valid"]."</td></tr>";
			
	}
?>
</table>
<?php
if ($count == 1)
{
    echo "No Registrations found";
}
?>
</body>
</html>