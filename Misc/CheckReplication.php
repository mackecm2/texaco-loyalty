<?php
	include "../include/Session.inc";


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Replication Check </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>

<BODY>
<?php

function CompareFeature( $mrow, $srow, $col )
{
	if( $mrow[$col] != $srow[$col] )
	{
		echo "<td>$col<td>";
		echo $mrow[$col];
		echo "<td>";
		echo $srow[$col];
		echo "\n";
		return false;
	}
	else
	{
		return true;
	}
}

$db_user = "ReadOnly";
$db_pass = "ORANGE";


$mastercon = connectToDB( MasterServer, TexacoDB );
$slavecon  = connectToDB( ReplicationServer, TexacoDB );

$sql = "Show Master Status";

$mtablesres = mysql_query( $sql, $mastercon );// or die( mysql_error() );

echo mysql_error();

$mrow = mysql_fetch_assoc( $mtablesres );

$Files = "<TR>";
$Position = "<TR>";

$Files .= "<TD>$mrow[File]";
$Position .= "<TD>$mrow[Position]";


$sql = "Show Slave Status";
$stablesres = mysql_query( $sql, $slavecon );
echo mysql_error();
$srow = mysql_fetch_assoc( $stablesres );

$Files .= "<TD>$srow[Master_Log_File]";
$Position .= "<TD>$srow[Read_Master_Log_Pos]";

$Files .= "<TD>$srow[Relay_Log_File]";
$Position .= "<TD>$srow[Relay_Log_Pos]";

$Files .= "<TD>$srow[Relay_Master_Log_File]";
$Position .= "<TD>$srow[Exec_Master_Log_Pos]";

echo "<H2>Thread Status</H2>\n";
echo "<TABLE>";
echo "<TR><TD style='text-align=right; font-weight=bold;'>Slave_IO_State: <TD>$srow[Slave_IO_State]";
echo "<TR><TD style='text-align=right; font-weight=bold;'>Slave_IO_Running: <TD>$srow[Slave_IO_Running]";
echo "<TR><TD style='text-align=right; font-weight=bold;'>Last_Errno: <TD>$srow[Last_Errno]";
echo "<TR><TD style='text-align=right; font-weight=bold;'>Last_Error: <TD>$srow[Last_Error]";
echo "<TR><TD style='text-align=right; font-weight=bold;'>Seconds_Behind_Master:<TD>$srow[Seconds_Behind_Master]";
echo "</TABLE>";

$sql = "Show Master Status";

$mtablesres = mysql_query( $sql, $slavecon );
echo  mysql_error();
$mrow = mysql_fetch_assoc( $mtablesres );

$Files .= "<TD>$mrow[File]";
$Position .= "<TD>$mrow[Position]";

echo "<H2>Files Status</H2>\n";
echo "<TABLE>";
echo "<TH>Master<TH>Slave Read<TH>Slave Process<TH>Slave Execute<TH>Slave(Master)";
echo $Files;
echo $Position;
echo "</TABLE>";

//print_r( $srow );


$sql = "Show Table status";


$mtablesres = mysql_query( $sql, $mastercon ) or die( mysql_error() );


echo "Num results = ". mysql_num_rows( 	$mtablesres );
echo "<H2>Table Status</H2>\n";
echo "<table>";
echo "<tr><TH>Table<TH><TH>".MasterServer."<TH>".ReplicationServer."\n";
while( $mrow = mysql_fetch_assoc( $mtablesres ) )
{
	echo "<tr><td>$mrow[Name]";
	$sql = "Show Table Status like '$mrow[Name]'";
	
	$stablesres = mysql_query( $sql, $slavecon ) ;
	echo mysql_error();	
//	$ArrayCols = Array( "Version", "Rows", "Data_length", "Index_length", "Auto_increment", "Update_time" );

	$ArrayCols = Array( "Rows", "Auto_increment" );

	if( $srow = mysql_fetch_assoc( $stablesres ) )
	{
		$ok = true;

		$sql = "select count(*) as RowsC from $mrow[Name]";
		$mtables = mysql_query( $sql, $mastercon );
		echo mysql_error();
		$mrowc = mysql_fetch_assoc( $mtables );
		$stables = mysql_query( $sql, $slavecon );
		echo mysql_error();
		$srowc = mysql_fetch_assoc( $stables );
		$ok = CompareFeature( $mrowc, $srowc, "RowsC" );
		
		if( $ok )
		{
			foreach( $ArrayCols as $col )
			{
				$ok =  CompareFeature($mrow, $srow, $col ); 
				if( !$ok )
				{
					break;
				}
			}
		}

		if( $ok )
		{
			echo "<td>OK<td>".number_format($mrowc["RowsC"])."\n";
		}

	}
	else
	{
		
		echo "<td>Not Found\n";
	}

}

?>

</BODY>
</HTML>
