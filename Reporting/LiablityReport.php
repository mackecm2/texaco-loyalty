<?php
	// MRM 11/05/10 - Mantis 2022 is ready for implementation
	$Reporting = true;
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../charting/charts.php";


 	$DrillOrder = $_SESSION['DrillOrder'];
	$Order = explode( ",", $DrillOrder);

	$NextIndex = "+KPIPages.php";
	$Key = array_search( $NextIndex, $Order );

	if( isset( $_GET["WhereClause"] ) )
	{
		$WhereClause = $_GET["WhereClause"];
	}
	else if( isset( $WhereStack[$Key] ) )
	{
		$WhereClause = $WhereStack[$Key] ;
	}
	else
	{
		$WhereClause = "";
	}
	$WhereClause = stripslashes( $WhereClause );


	$sql = "Select if( YearMonth = 999999999, 'Total (2010 onwards)', YearMonth ) as YearMonth, 
	Spend, Swipes, Points, Redeemed, Credits,
	 if( PointsRelease = 0, NULL, PointsRelease ) as PointsRelease, 
	 Debits, 
	 Points + Credits - Debits - Redeemed - PointsRelease  as 'Liability +/-', 
	 if( PointsRedeemable = 0, NULL, PointsRedeemable ) as PointsRedeemable, 
	 if( RunningTotal = 0, NULL, RunningTotal  ) as RunningTotal 
	 from MonthlyLiablityReport $WhereClause and YearMonth > 200402 ORDER BY YearMonth ASC";
	$Results = DBQueryExitOnFailure( $sql );

	DisplayTable( $Results, 0 );

	$chartSpend [ 'chart_type' ] = "line";
	$chartSwipes [ 'chart_type' ] = "line";

	$scale = array( "" );
	$Spend = array( "Spend" );
	$Points = array( "Points" );
	$Swipes = array( "Swiped" );
	$count = 1;
	while( $row =mysql_fetch_assoc( $Results ) )
	{

		$scale[$count] = $row["YearMonth"]/100;
		$Swipes[$count] = $row["Swipes"];
		$Points[$count] = $row["Points"];
		$Spend[$count] = $row["Spend"];
		$count++;
	}
	$chart [ 'chart_data' ] = array ( $scale, $Spend, $Points );

	$chart1 [ 'chart_data' ] = array ( $scale,  $Swipes );

//$chart [ 'axis_category' ] = array ( 'skip' =>  1, 'orientation' =>  "diagonal_down" );

	$chart [ 'axis_category' ] = array (   'skip'         =>  0,
                                       'font'         =>  "Arial",
                                       'bold'         =>  true,
                                       'size'         =>  10,
                                       'color'        =>  "88FF00",
                                       'alpha'        =>  75,
                                       'orientation'  =>  "diagonal_up"
                                   );

	$chart1 [ 'axis_category' ] = $chart [ 'axis_category' ];

	$chart [ 'axis_value' ] = array (   'min'           =>  0,
                                    'show_min'      =>  true,
                                    'font'          =>  "Arial",
                                    'bold'          =>  true,
                                    'size'          =>  10,
                                    'color'         =>  "88FF00",
                                    'alpha'         =>  75,
                                    'orientation'   =>  "diagonal_up"
                                   );

	$chart1 [ 'axis_value' ] = $chart [ 'axis_value' ];

	$chart [ 'canvas_bg' ] = array (   'width'   =>  640, 'height'  =>  480  );

	$chart1 [ 'canvas_bg' ] = $chart [ 'canvas_bg' ];
	
	$sql = "SELECT * FROM MonthlyLiablityReport WHERE MemberType = 'Process' AND YearMonth <> 999999999 ORDER BY YearMonth DESC LIMIT 1";
	$Results = DBQueryExitOnFailure( $sql );
	$row =mysql_fetch_assoc( $Results );

?>
<table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffff80" align="center">
  <tbody>
  <tr>
      <td>Closing Points Review: </td>
      <td>All Points</td> 
      <td>Points Redeemable</td> 
  </tr>   
  <tr>
    <td>Cards registered and used in 0 - 12 months</td>
    <td align="right">
    <?php echo $row["tot1"];?>
    </td>
        <td align="right">
    <?php echo $row["tot10"];?>
    </td>
   </tr>
  <tr>
    <td>Cards not registered but used in 0 - 12 months </td>
    <td align="right">
    <?php echo $row["tot2"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards registered but not used in 13 - 18 months</td>
    <td align="right">
    <?php echo $row["tot3"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards not registered and not used in 13 - 18 months</td>
    <td align="right">
    <?php echo $row["tot4"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards registered but not used in 19 - 24 months</td>
    <td align="right">
    <?php echo $row["tot5"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards not registered and not used in 19 - 24 months</td>
    <td align="right">
    <?php echo $row["tot6"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards registered but not used in 24+ months</td>
    <td align="right">
    <?php echo $row["tot7"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards not registered and not used in 24+ months</td>
    <td align="right">
    <?php echo $row["tot8"];?>
    </td><td></td></tr>
  <tr>
    <td>Cards registered with no swipe date</td>
    <td align="right">
    <?php echo $row["tot9"];?>
    </td><td></td></tr>
  <tr>
    <td></td>
    <td align="right">
    <?php echo $row["tot1"] + $row["tot2"] + $row["tot3"] + $row["tot4"] + $row["tot5"] + $row["tot6"] + $row["tot7"] + $row["tot8"] + $row["tot9"];?>
    </td> 
    <td align="right">
    <?php echo $row["tot10"];?>
    </td></tr></tbody></table>