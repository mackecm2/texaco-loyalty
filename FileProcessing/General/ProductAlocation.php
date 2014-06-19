<?php
	global $gProductGroups; 
	$gProductGroups = array();

	$gProductGroups['00'] = 2;
	$gProductGroups['01'] = 1;
	$gProductGroups['02'] = 1;
	$gProductGroups['03'] = 1;
	$gProductGroups['04'] = 1;
	$gProductGroups['05'] = 1;
	$gProductGroups['06'] = 1;
	$gProductGroups['07'] = 2;
	$gProductGroups['08'] = 2;
	$gProductGroups['09'] = 2;
	$gProductGroups['10'] = 2;
	$gProductGroups['11'] = 2;
	$gProductGroups['12'] = 2;
	$gProductGroups['13'] = 2;
	$gProductGroups['14'] = 2;
	$gProductGroups['15'] = 2;
	$gProductGroups['16'] = 2;
	$gProductGroups['17'] = 2;
	$gProductGroups['18'] = 2;
	$gProductGroups['19'] = 2;
	$gProductGroups['20'] = 1;
	$gProductGroups['21'] = 1;
	$gProductGroups['22'] = 1;
	$gProductGroups['24'] = 2;
	$gProductGroups['25'] = 2;
	$gProductGroups['26'] = 2;
	$gProductGroups['27'] = 2;
	$gProductGroups['28'] = 2;
	$gProductGroups['29'] = 2;
	$gProductGroups['30'] = 2;
	$gProductGroups['31'] = 2;
	$gProductGroups['32'] = 2;
	$gProductGroups['33'] = 2;
	$gProductGroups['34'] = 2;
	$gProductGroups['35'] = 2;
	$gProductGroups['36'] = 2;
	$gProductGroups['37'] = 2;
	$gProductGroups['38'] = 2;
	$gProductGroups['39'] = 2;
	$gProductGroups['40'] = 2;
	$gProductGroups['41'] = 2;
	$gProductGroups['42'] = 2;
	$gProductGroups['43'] = 2;
	$gProductGroups['44'] = 2;
	$gProductGroups['45'] = 2;
	$gProductGroups['46'] = 2;
	$gProductGroups['47'] = 2;
	$gProductGroups['48'] = 2;
	$gProductGroups['49'] = 2;
	$gProductGroups['50'] = 2;
	$gProductGroups['51'] = 2;
	$gProductGroups['52'] = 2;
	$gProductGroups['53'] = 2;
	$gProductGroups['54'] = 2;
	$gProductGroups['55'] = 2;
	$gProductGroups['56'] = 2;
	$gProductGroups['57'] = 2;
	$gProductGroups['58'] = 2;
	$gProductGroups['59'] = 2;
	$gProductGroups['60'] = 2;
	$gProductGroups['61'] = 2;
	$gProductGroups['62'] = 2;
	$gProductGroups['63'] = 2;
	$gProductGroups['64'] = 2;
	$gProductGroups['65'] = 2;
	$gProductGroups['66'] = 2;
	$gProductGroups['67'] = 2;
	$gProductGroups['68'] = 2;
	$gProductGroups['69'] = 2;
	$gProductGroups['70'] = 2;
	$gProductGroups['71'] = 2;
	$gProductGroups['72'] = 2;
	$gProductGroups['73'] = 2;
	$gProductGroups['74'] = 2;
	$gProductGroups['75'] = 2;
	$gProductGroups['76'] = 2;
	$gProductGroups['77'] = 2;
	$gProductGroups['78'] = 2;
	$gProductGroups['79'] = 2;
	$gProductGroups['80'] = 2;
	$gProductGroups['81'] = 2;
	$gProductGroups['82'] = 2;
	$gProductGroups['83'] = 2;
	$gProductGroups['84'] = 2;
	$gProductGroups['85'] = 2;
	$gProductGroups['86'] = 2;
	$gProductGroups['87'] = 2;
	$gProductGroups['88'] = 2;
	$gProductGroups['89'] = 2;
	$gProductGroups['90'] = 2;
	$gProductGroups['91'] = 2;
	$gProductGroups['92'] = 2;
	$gProductGroups['93'] = 2;
	$gProductGroups['94'] = 2;
	$gProductGroups['95'] = 2;
	$gProductGroups['96'] = 2;
	$gProductGroups['97'] = 2;
	$gProductGroups['98'] = 2;
	$gProductGroups['99'] = 2;
	//$gProductGroups['XX'] = 1;
	//$gProductGroups['UK'] = 1;

	function ProductAllocate(  )
	{

		global $gProductGroups, $gUserData, $gProductData; 
		$group = 0;
		if( isset( $gProductGroups[$gProductData->code] ))
		{
			$group = $gProductGroups[$gProductData->code];
		}

		if( $group == 1 ) 
		{
			$gUserData->fuelAdd += $gProductData->value; 
		}
		else if( $group == 2 )
		{
			$gUserData->shopAdd += $gProductData->value; 
		}
	}

?>