<?php 
	
	$Reporting = 1;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
//	require "GeneralReportFunctions.php";
	include "../charting/charts.php";
?>
<html>
<Head>
<Title>Bonus Report</Title>

<style>
	table { border:inset; border-collapse: collapse; }
	td { border:solid; }
</style>
<body>
<?php
	connectToDB( ReportServer, ReportDB );

	if( isset( $_GET["FieldType"] ) )
	{
		$FieldType = $_GET["FieldType"];
	}
	else
	{
		$FieldType = "Hits";
	}

	$Where = "";

	$thisPromo = array( "Months" );

	$sql = "Select distinct Month from MonthlyBonusReport $Where Order By Month ";
	$res1 = DBQueryExitOnFailure( $sql );
	$count = 1;

	while( $row = mysql_fetch_assoc( $res1 ) ) 
	{
		$thisPromo[$count++] = $row["Month"];
	}

	$scale =  $thisPromo;

	$sql = "Select PromotionCode, Month, sum($FieldType) as Hits from MonthlyBonusReport $Where group by PromotionCode, Month order by PromotionCode, Month";

	$res1 = DBQueryExitOnFailure( $sql );

	$promocnt = 0;
	$lastPromo = "";
	$Promotions = array( );

	while( $row =mysql_fetch_assoc( $res1 ) ) 
	{
		if( $lastPromo != $row["PromotionCode"] )
		{
			$Promotions[$promocnt++] = $thisPromo; 
			$lastPromo = $row["PromotionCode"];
			$thisPromo = array( $lastPromo );
			// reset array
			foreach( $scale as $b => $c )
			{
				if( $b != 0 )
				{
					$thisPromo[$b] = 0;
				}
			}
			
		}
		$index = array_search( $row["Month"], $scale );
		$thisPromo[$index] = $row["Hits"];
	}
	$Promotions[$promocnt++] = $thisPromo; 
	echo "<table>";
	for( $d = 0; $d < $promocnt; $d++ )
	{
		$r = $Promotions[$d];
		echo "<tr>";
		for( $c = 0; $c < $count; $c++ )
		{
			echo "<td>$r[$c]";
		}
	}
 	echo "</table><br>";



	$chart [ 'chart_data' ] = $Promotions;

	$chart [ 'canvas_bg' ] = array (   'width'   =>  640, 'height'  =>  480  );

	$chart [ 'axis_category' ] = array (   'skip'         =>  0,
                                       'font'         =>  "Arial", 
                                       'bold'         =>  true, 
                                       'size'         =>  10, 
                                       'color'        =>  "88FF00", 
                                       'alpha'        =>  75,
                                       'orientation'  =>  "diagonal_up"
                                   ); 
	

	DrawChart ( $chart );

?>
</body>
</html>