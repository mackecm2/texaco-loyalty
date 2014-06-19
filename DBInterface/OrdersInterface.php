<?php

	define ( "VirginProductCode600", "VIR600" );
	define ( "VirginProductCode1000", "VIR004" );

	function checkOrderNumber( $requestNumber )
	{
		$sql = "Select OrderNo, Status from OrderProducts where RedeptionId = $requestNumber";
		$results = DBQueryExitOnFailure( $sql );
		$numrows = mysql_num_rows($results);
		if( $numrows == 1 )
		{
			$row = mysql_fetch_row( $results );
			if( $row[1] != 'O' )
			{
				DBLogError( "RequestNumber '$requestNumber' already satisifed('$row[1])'\n");
				return false;
			}
			else
			{
				return $row[0];
			}
		}
		else
		{
			DBLogError( "Unrecognised requestNumber '$requestNumber'\n");
			return false;
		}
	}


	function GetUnsatisifiedOrdersBatches( $type, $limit )
	{
/*		if( $type == 0 )
		{
			$sql = "SELECT BatchTime, count(*) as Unsatisfied from OrderProducts LEFT JOIN SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)  where Status != 'F' and Status != 'R' and FileGroup is null and  BatchTime != '2004-10-22 00:00:00' group by BatchTime order by BatchTime limit $limit" ;

		}
		else
		{
			$sql = "SELECT BatchTime, count(*) as Unsatisfied from OrderProducts JOIN SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)  where Status != 'F' and Status != 'R' and FileGroup = $type and  BatchTime != '2004-10-22 00:00:00' group by BatchTime order by BatchTime limit $limit" ;
		}
*/

		$sql = "SELECT SupplierCodes.Description, FileGroup, BatchTime, count(*) as Unsatisfied from OrderProducts LEFT JOIN SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)  where Status != 'F' and Status != 'R'  and Status != 'C' and  (BatchTime != '2004-10-22 00:00:00' or BatchTime is null) group by FileGroup, BatchTime order by FileGroup, BatchTime " ;
		#echo "$sql";

		return DBQueryExitOnFailure( $sql );
	}


	function MakeUpOrdersBatch( $type, $timestamp )
	{
		if( $type == 0 )
		{
			$sql = "Update OrderProducts left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)  set BatchTime='$timestamp', Status='O' where Status='P' and FileGroup is null and BatchTime is null";
			$results = DBQueryExitOnFailure( $sql );
		}
		else
		{
			$sql = "Update OrderProducts join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)   set BatchTime='$timestamp', Status='O' where Status='P' and FileGroup = $type and BatchTime is null";
		}
		DBQueryExitOnFailure( $sql );
	}

	function GetOrderFileTypes( )
	{
		$sql = "Select * from FileGroups where Active = 'Y'";
		$results = DBQueryExitOnFailure( $sql );
		$Types = array();
		$Types[0] = 'Unkown';
		while( $row = mysql_fetch_row( $results ) )
		{
			$Types[$row[0]] = $row[1];
		}
		return $Types;
	}

	function GetTypeDescription( $type )
	{
		if( $type == 0 )
		{
			return 'Unkown';
		}
		$sql = "Select Description from FileGroups where FileGroup = $type";
		$results = DBQueryExitOnFailure( $sql );

		$row = mysql_fetch_row( $results );
		return $row[0];
	}


	function GetDawleysOrdersBatchData( $type, $timestamp )
	{
		if( $type == 0 )
		{
			# $sql = "select PrimaryCard, DATE_FORMAT( Orders.CreationDate, '%d%m%Y' ) as OrderDate, Orders.OrderNo, Members.Title, Members.Forename, Members.Surname, Members.Address1, Members.Address2, Members.Address3, Members.Address4, Members.Address5, Members.Postcode, OrderProducts.ProductId,  OrderProducts.Quantity, OrderProducts.Cost,Orders.CreatedBy from Members Join Orders using(MemberNo) Join OrderProducts using (OrderNo) left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) where BatchTime='$timestamp' and Status = 'O' and (FileGroup = 0 or FileGroup is null)";

			$sql = "select PrimaryCard, DATE_FORMAT( Orders.CreationDate, '%d%m%Y' ) as OrderDate, Orders.OrderNo, Orders.Title, Orders.Forename, Orders.Name, Orders.Address1, Orders.Address2, Orders.Address3, Orders.Address4, Orders.Address5, Orders.PostCode, OrderProducts.ProductId,  OrderProducts.Quantity, OrderProducts.Cost,OrderProducts.Personalisation,Orders.CreatedBy from Members Join Orders using(MemberNo) Join OrderProducts using (OrderNo) left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) where BatchTime='$timestamp' and Status = 'O' and (FileGroup = 0 or FileGroup is null)";

		}
		else
		{
			#$sql = "select PrimaryCard, DATE_FORMAT( Orders.CreationDate, '%d%m%Y' ) as OrderDate, Orders.OrderNo, Members.Title, Members.Forename, Members.Surname, Members.Address1, Members.Address2, Members.Address3, Members.Address4, Members.Address5, Members.Postcode, OrderProducts.ProductId,  OrderProducts.Quantity, OrderProducts.Cost,Orders.CreatedBy from Members Join Orders using(MemberNo) Join OrderProducts using (OrderNo) left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) where BatchTime='$timestamp' and Status = 'O' and FileGroup = $type";

			$sql = "select PrimaryCard, DATE_FORMAT( Orders.CreationDate, '%d%m%Y' ) as OrderDate, Orders.OrderNo, Orders.Title, Orders.Forename, Orders.Name, Orders.Address1, Orders.Address2, Orders.Address3, Orders.Address4, Orders.Address5, Orders.PostCode, OrderProducts.ProductId,  OrderProducts.Quantity, OrderProducts.Cost,OrderProducts.Personalisation, Orders.CreatedBy from Members Join Orders using(MemberNo) Join OrderProducts using (OrderNo) left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) where BatchTime='$timestamp' and Status = 'O' and FileGroup = $type";
		}
		return DBQueryExitOnFailure( $sql );

	}

	function GetOrdersBatchData( $type,  $timestamp )
	{
/*
		$sql = "select PrimaryCard, RedeptionId As RefNo, Members.Title, Members.Forename, Members.Surname, Members.Address1, Members.Address2, Members.Address3, Members.Address4, Members.Address5, Members.Postcode, OrderProducts.ProductId,  OrderProducts.Quantity, Orders.CreationDate, OrderProducts.Description, OrderProducts.Personalisation,Orders.CreatedBy
		from Members Join Orders using(MemberNo)
        Join OrderProducts using (OrderNo)
		join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)
		where BatchTime='$timestamp'
		and Status = 'O' and FileGroup = $type";

*/

		$sql = "select PrimaryCard, RedeptionId As RefNo, Orders.Title, Orders.Forename, Orders.Name, Orders.Address1, Orders.Address2, Orders.Address3, Orders.Address4, Orders.Address5, Orders.PostCode, OrderProducts.ProductId,  OrderProducts.Quantity, Orders.CreationDate, OrderProducts.Description, OrderProducts.Personalisation,OrderProducts.ProductOption,Orders.CreatedBy
		from Members Join Orders using(MemberNo)
        Join OrderProducts using (OrderNo)
		join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier)
		where BatchTime='$timestamp'
		and Status = 'O' and FileGroup = $type";


		#	echo "$sql";

		return DBQueryExitOnFailure( $sql );

	}

	function GetVirginBatchData( $type, $timestamp )
	{
		$sql = "SELECT VirginNo, 'TX' as PartnerCode, if( ProductId = 'VIR600', 1, 2 ) as RedeemRate, 'A' as Filler2, sum( if( QuantityType = 'M', Quantity,  1000 * Quantity ) ) as Miles, DATE_FORMAT( Orders.CreationDate,'%Y%m%d' )as OrderDate,  PrimaryCard,Orders.CreatedBy from SupplierCodes Join OrderProducts on( SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) Join Orders using (OrderNo) Join Members using(MemberNo) Join Accounts ON ( Members.AccountNo = Accounts.AccountNo )   where BatchTime='$timestamp' and Status ='O' and FileGroup = $type group by VirginNo, RedeemRate";
		#echo"$sql";
		return DBQueryExitOnFailure( $sql );

	}

	function ConfirmOrdersBatch( $type, $timestamp )
	{
		if( $type == 0 )
		{
			$sql = "Update OrderProducts left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) set Status='F' where BatchTime='$timestamp' and  Status='O' and FileGroup is null";
		}
		else
		{
			$sql = "Update OrderProducts left join SupplierCodes on(SupplierCodes.SupplierCode = OrderProducts.ProductSupplier) set Status='F' where BatchTime='$timestamp' and  Status='O' and FileGroup = $type";
		}
		return DBQueryExitOnFailure( $sql );
	}


	function SatisfyRequest( $requestNo )
	{
		$sql = "Update OrderProducts Set Status='F' where RedeptionId = $requestNo and Status ='O'";

		return DBQueryExitOnFailure( $sql );
	}

	function FailRequest( $requestNo )
	{
		$sql = "Update OrderProducts Set Status='R' where RedemptionId = $requestNo and Status = 'O'";

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_affected_rows() == 1 )
		{
			$sql = "Select * from OrderProducts where RedemptionId = $requestNo";
			$results = DBQueryExitOnFailure( $sql );
			$row = mysql_fetch_assoc( $results );
			$cost = $row["Cost"];
			$accountNo = $row["AccountNo"];
			$sql = "Update Accounts Set Balance = Balance + $Cost, TotalRedemp = TotalRedemp - $Cost where AccountNo = $accountNo";
			DBQueryExitOnFailure( $sql );
		}
	}

	function CancelRequest( $AccountNo, $requestNo )
	{
		$sql = "Update OrderProducts join Orders using(OrderNo) set Status='C' where RedeptionId = $requestNo and Status='P' and AccountNo = $AccountNo";
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_affected_rows() == 1 )
		{
			$sql = "Select * from OrderProducts join Orders using(OrderNo) where RedeptionId = $requestNo";
			$results = DBQueryExitOnFailure( $sql );
			$row = mysql_fetch_assoc( $results );
			$cost = $row["Cost"];
			$accountNo = $row["AccountNo"];
			$memberNo = $row["MemberNo"];

			$sql = "Update Accounts set Balance = Balance + $cost, TotalRedemp = TotalRedemp - $cost where AccountNo = $accountNo";
			DBQueryExitOnFailure( $sql );

			InsertTrackingRecord(  TrackingCancelRedemption,  $memberNo, $accountNo, $row["ProductId"], 0 );
		}

	}
?>