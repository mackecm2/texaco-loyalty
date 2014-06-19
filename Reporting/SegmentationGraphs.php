<?php 
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../charting/charts.php";

	$Cells = array ();

	if( isset( $_GET["Type"] ) )
	{
		$Type = $_GET["Type"];
	}
	else
	{
		$Type = "Recency";
	}

	switch( $Type  )
	{
		case "Value":
			$Rows = array( 'H', 'MH', 'M', 'L' );
			$Field = "Value";			
		break;
		case "Frequency":
			$Rows = array( 'H', 'M', 'L' );
			$Field = "Frequency";			
		break;
		default:
			$Rows = array( '', 'A1', 'A2', 'N1', 'N2', 'L', 'D' );
			$Field = "Recency";
		break;
	}	
	for( $i = 0; $i < count( $Rows ); $i++ )
	{
		$Cells[$i] = array( $Rows[$i] );
	}

	$Header = &$Cells[0];
//	should use Reporting but will use analysis for debug
	$sql = "Show Tables from Reporting like 'RawKPIData%'";

	$Results = DBQueryExitOnFailure( $sql );
	$Header[0] = "";
	$i = 1;
	while( $row = mysql_fetch_row( $Results ) )
	{
		$Month = substr( $row[0], 10 );  
		$Header[ $i ] = $Month;
		
		$sql = "Select  Recency, count(*) from Reporting.$row[0] group by Recency";
		$Results1 = DBQueryExitOnFailure( $sql );
		while( $row1 = mysql_fetch_row( $Results1 ) )
		{
			$r = array_search( $row1[0], $Rows );
			if( $r )
			{
				$t = &$Cells[$r];
				$t[$i] = $row1[1];
			}
		}
		$i++;
	}

	$D = $Cells;

	echo "<table>";
	for( $d = 0; $d < count($D); $d++ )
	{
		$r = $D[$d];
		echo "<tr>";
		for( $c = 0; $c < count($r); $c++ )
		{
			echo "<td>$r[$c]";
		}
	}
 	echo "</table><br>";

 	$chart [ 'chart_data' ] = $D;

	$chart [ 'canvas_bg' ] = array (   'width'   =>  640, 'height'  =>  480  );

	$chart [ 'axis_category' ] = array (   'skip'         =>  0,
                                       'font'         =>  "Arial", 
                                       'bold'         =>  true, 
                                       'size'         =>  10, 
                                       'color'        =>  "88FF00", 
                                       'alpha'        =>  75,
                                       'orientation'  =>  "diagonal_up"
                                   ); 
	



?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE> Report Document </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>


<BODY>
	<a style="Color:blue" href="ReportTypeIndex.php">Index</a> - 
<?php
	DrawChart ( $chart );
?>
 </BODY>
 </HTML>
