<?php
// functions.php
// Insert into here any functions to be used by the whole project


function formatdate($indate)
{

	#echo "function date is $indate\r\n";

	$indate = trim($indate);
	$outdate = '';

	// determine date separator used
	if (strpos($indate, '/'))
	{
		$expdate = explode('/', $indate);
		
		#echo "We have / \r\n";
	}
	else if (strpos($indate, '-'))
	{
		$expdate = explode('-', $indate);
		#echo "We have - \r\n";
		
	}
	else
	{

		#	Date has no formatting characters so likely to be DDMMMYY

		$Day 	= substr($indate, 0, 2) ;
		$Month 	= substr($indate, 2, 3) ;
		$Year 	= '20'.substr($indate, 5, 2) ;

		if($Month == 'JAN'){$Month = '01';}
		elseif($Month == 'JAN'){$Month = '01';}
		elseif($Month == 'FEB'){$Month = '02';}
		elseif($Month == 'MAR'){$Month = '03';}
		elseif($Month == 'APR'){$Month = '04';}
		elseif($Month == 'MAY'){$Month = '05';}
		elseif($Month == 'JUN'){$Month = '06';}
		elseif($Month == 'JUL'){$Month = '07';}
		elseif($Month == 'AUG'){$Month = '08';}
		elseif($Month == 'SEP'){$Month = '09';}
		elseif($Month == 'OCT'){$Month = '10';}
		elseif($Month == 'NOV'){$Month = '11';}
		elseif($Month == 'DEC'){$Month = '12';}

		$date = $Day.'-'.$Month.'-'.$Year;
		$expdate = explode('-', $date);

	}
	
	$Day 	= $expdate['0'] ;
	$Month 	= $expdate['1']  ;
	$Year 	= '20'.$expdate['2'] ;
	
	if($Month == 'JAN'){$Month = '01';}
	elseif($Month == 'JAN'){$Month = '01';}
	elseif($Month == 'FEB'){$Month = '02';}
	elseif($Month == 'MAR'){$Month = '03';}
	elseif($Month == 'APR'){$Month = '04';}
	elseif($Month == 'MAY'){$Month = '05';}
	elseif($Month == 'JUN'){$Month = '06';}
	elseif($Month == 'JUL'){$Month = '07';}
	elseif($Month == 'AUG'){$Month = '08';}
	elseif($Month == 'SEP'){$Month = '09';}
	elseif($Month == 'OCT'){$Month = '10';}
	elseif($Month == 'NOV'){$Month = '11';}
	elseif($Month == 'DEC'){$Month = '12';}	
	
	
	$outdate =   $Year."-".$Month."-".$Day ;
	
	return(	$outdate  );
	
}


function alterdate($indate)
{

	#	Date has no formatting characters so likely to be DDMMMYY

	$Day 	= substr($indate, 6, 2) ;
	$Month 	= substr($indate, 4, 2) ;
	$Year 	= substr($indate, 0, 4) ;
	
	$outdate =   $Year."-".$Month."-".$Day ;
	
	return(	$outdate  );



}


?>