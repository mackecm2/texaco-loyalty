<?php
	function DateRange( $startDate, $endDate )
	{
		global $gTransactionData;

		if( ($startDate == "" or $gTransactionData->transDate >= $startDate ) 
		and ($endDate == "" or $gTransactionData->transDate <= $endDate ))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function ExpressionMatch( $transData, $exp )
	{
		return ($transData == $exp);
	}

	function RangeMatch( $transData, $exp )
	{
		if( strlen( $transData ) > 0 )
		{
			return strstr( $exp, $transData );
		}
		else
		{
			return false;
		}
	}
	
	function LowerSwipes( $totalswipes, $numswipes )
	{
        
		if( $totalswipes < $numswipes )
		{
			
			return true;
		}
		else
		{
			
			return false;
		}
	}
	
	function LowerCard( $cardno, $lowcard )
	{
		
		if( $cardno < $lowcard )
		{
			
			return true;
		}
		else
		{
			
			return false;
		}
	}

		function HigherCard( $cardno, $highcard )
	{
		
		if( $cardno > $highcard )
		{
			
			return true;
		}
		else
		{
			
			return false;
		}
	}
	
	// This function calculates the bonuses applicable to this transaction
	// It also adds transactions into the bonushit table for each hit.

	// The inclusion of period spends makes it more complicated so here is a discussion
	// Imagine a set of bonuses such like
	// 
	//	a) spend over 50 pounds in a month and get 2 stars for a pound (excluded from other bonuses)
	//  b) double points at site x
	//	c) Standard bonus one star for every pound

	// 	imagine a customer who has a) and c) operating who does a set of transactions
	//  £24, £24, £12
	// 	On the first two transactions they only earn normal points but on the last transaction
	//  it takes them over the threshold by £10.
	//  The first two transactions will need 24 points adding
	//  the final transaction will need 24 points also.
	//  However the system would find it hard to know if b) or c) applied to the first two transaction.

	// If however you view it as before the user gets to the threshold add what ever points are
	// aproriate.  At the threshold add the points that would have been added if all of these points had 
	// been added in one transaction and apply the bonus calculation to what ever is left.
	//
	// Those points that are then below the threshold (or required to bring the perod spend 
	// upto the threshold will then be passed to the lower bonuses for calculation.


	function Bonuses( $points, $perQ, $entryKey, $appliesTo, $exclude, $threshold, $thresholdPnts, &$sectionValue, $MaximumHits )
	{
		global $gProductData, $gTransactionData, $gUserData;

		// Set up some stuff according to what area we are working in.

		$bonuses = 0;
		$sectionExclude = $sectionValue;
		$nume = $sectionValue;
		$base = 0;
		switch( $appliesTo )
		{
		case "Visit":
			$excludeValue = 0;
			break;
		case "Total":
			$excludeValue = $gTransactionData->starValueCurrency; 
			break;
		case "Dept":
			$excludeValue = 0;
			break;
		case "Product":
			$excludeValue = $gProductData->value;
			break;
		case "Quantity":
			$excludeValue = $gProductData->value;
			break;
		case "PeriodSpend":
			$excludeValue = $gTransactionData->starValueCurrency;
			$base = $gUserData->periodSpend;
			$gUserData->periodAdd += $gTransactionData->starValueCurrency;
			break;
		}

		// Avoid a division by zero
		if( $perQ == 0 )
		{
			$denom = 1;
		}
		else
		{
			$denom = $perQ;
		}

		// Is this a thresholded transaction as these require a special calculation

		if( $threshold > 0  )
		{
			// Does this bonus meet the criteria for value
			// (This might be a period spend hence the use of base)
			if($base + $nume >= $threshold )
			{
				// Add threshold pts these should include any points that this bonus would 
				// add to those points below the threshold as these won't be added. by the calculation
				$bonuses = $thresholdPnts;
				$nume = $base + $nume - $threshold;
				if( $exclude )
				{
					// We only apply the bonus and exclude those pts over the threshold
					// as the threshold bonus should allow for those under the threshold
					if( $nume > 0 )
					{
						$sectionExclude = $nume;
					}
				}
			}
			else
			{
				// we want it to not apply the bonus at all and try the other bonuses.
				$nume = 0;
				$sectionExclude = 0;
			}
		}
		// normal no threshold bonusing
		$bonuses += IntVal( $nume / $denom) * $points;

		if( $bonuses > 0 )
		{
			if( $exclude )
			{
				$gTransactionData->starValueCurrency -= $excludeValue;
				$sectionValue -= $sectionExclude;
			}
			$gTransactionData->bonusPoints += $bonuses;

			TrackBonusHits( $gTransactionData,  $entryKey, $bonuses );
			$gTransactionData->bonusSeq++;

			if( $MaximumHits )
			{
				//echo "Setting deduct hit";
				$gUserData->deductHit = true;
			}
			//echo "Max hits $MaximumHits";

		}
		return $bonuses;
	}


?>