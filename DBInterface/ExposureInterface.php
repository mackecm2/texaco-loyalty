<?php 

	function CreateExposurePoint( $Description )
	{
		$sql = "Select Sum( Balance ) from Accounts";

		$results = DBQueryExitOnFailure( $sql ); 

		$row = mysql_fetch_row( $results );

		$AccountBalance = $row[0];

		$sql = "Select Sum( StoppedPoints ) from Cards";
		
		$results = DBQueryExitOnFailure( $sql ); 

		$row = mysql_fetch_row( $results );

		$CardBalance = $row[0];

		$sql = "Insert into ExposureHistory ( Description, AccountExposure, CardExposure, TotalExposure ) values ( '$Description', $AccountBalance,$CardBalance, $AccountBalance + $CardBalance)"; 

		$results = DBQueryExitOnFailure( $sql ); 

		return $AccountBalance + $CardBalance;
	}

?>