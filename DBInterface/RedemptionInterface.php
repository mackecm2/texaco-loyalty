<?php

	define( "RedemptionCardNotRecognised", -1 );
	define( "RedemptionInsufficentFunds", -2 );
	define( "RedemptionRedeemNotAllowed", -3 );
	define( "RedemptionAccountNotRecognised", -4 );
	define( "RedemptionCardLost", -5 );

	function CheckCardBalance( $CardNo )
	{
		$sql = "Select Balance, CanReedem, CardLostDate isnull as allowed from Cards join Members using( MemberNo ) join Accounts using (AccountNo ) where CardNo = $CardNo"; 
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) == 0 )
		{
			return RedemptionCardNotRecognised;
		}
		$row = mysql_fetch_row( $results );

		if( $row[1] != 'Y' )
		{
			return RedemptionRedeemNotAllowed;
		}

		if( $row[2] != 0 )
		{
			return RedemptionCardLost;
		}
		return $row[0];
	}

	function RedeemAgainstCard( $CardNo, $Description, $MerchantNo, $ProductId, $MerchantRef, $Cost, $ProductSupplier  )
	{
		// Check Can Redeem

		$sql = "Select Balance, AccountNo, MemberNo, (CanReedem = 'Y' and CardLostDate isnull) as allowed from Cards join Members using( MemberNo ) join Accounts using (AccountNo ) where CardNo = $CardNo"; 

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) == 0 )
		{
			return RedemptionCardNotRecognised;
		}
		$row = mysql_fetch_assoc( $results );

		if( $row["Balance"] < $cost )
		{
			return RedemptionInsufficentFunds;
		}

		if( $row["allowed"] == 0 )
		{
			return RedemptionRedeemNotAllowed;
		}

		return RedeemAgainstAccount( $row["AccountNo"], $row["MemberNo"], $Description, $MerchantNo, $ProductId, "", $MerchantRef, $Cost, $Quantity, $ProductSupplier );
	}


	function RedeemAgainstAccount( $AccountNo, $MemberNo, $Description, $MerchantNo, $ProductId, $MerchantRef, $ProductOptions, $Cost, $Quantity, $ProductSupplier  )
	{
		global $uname;
		// Begin Transaction

		$sql = "Select Balance from Accounts where AccountNo = $AccountNo";

		$BalanceBefore = DBSingleStatQuery( $sql );

		$sql = "Update Accounts set Balance = Balance - ( $Cost * $Quantity ) where AccountNo = $AccountNo and Balance >= $Cost";
		
		$results = DBQueryExitOnFailure( $sql );

		if( mysql_affected_rows() > 0 )
		{
			$sql = "insert into Orders( AccountNo, MemberNo, BalanceBefore, CreationDate, CreatedBy ) values ( $AccountNo, $MemberNo, $BalanceBefore, now(), '$uname' )";
			DBQueryExitOnFailure( $sql );
			$id = mysql_insert_id();

			$sql = "Insert into OrderProducts ( OrderNo, ProductId, MerchantId,	MerchantRef, Description, Cost, Quantity, ProductOption, ProductSupplier ) values ( $id, '$ProductId', $MerchantNo, '$MerchantRef', '$Description', $Cost, $Quantity, '$ProductOptions', '$ProductSupplier'  )";

			DBQueryExitOnFailure( $sql );

			return $id;
		}
		else
		{
			return false;
		}
		// End Transaction
	}

	function GetRedemptionHistory( $AccountNo )
	{
#		$sql = "SELECT Date_Format( Redemptions.CreationDate, '%d/%m/%Y') as Date, Description, MerchantName, Cost, Quantity, Redemptions.CreatedBy as Agent from Redemptions JOIN RedemptionMerchants using( MerchantId ) where AccountNo = $AccountNo order by Redemptions.CreationDate DESC";
		$sql = "(SELECT Date_Format( Orders.CreationDate, '%Y-%m-%d %H:%i') as Date, ProductId, Description, MerchantName, QuantityType as Type, Quantity, Cost as TotalCost, Orders.CreatedBy as Agent,  StatusDescrip, OrderProducts.Status, AccountNo, RedeptionId  from Orders JOIN OrderProducts using( OrderNo ) JOIN RedemptionMerchants using( MerchantId ) join ProductStatusLUT on ( OrderProducts.Status = ProductStatusLUT.Status ) where AccountNo = $AccountNo)
		UNION
		(SELECT Date_Format( Orders.CreationDate, '%Y-%m-%d %H:%i') as Date, ProductId, Description, MerchantName, QuantityType as Type, Quantity, Cost as TotalCost, Orders.CreatedBy as Agent, StatusDescrip, OrderProducts.Status, MergeHistory.SourceAccount as AccountNo, RedeptionId  from Orders JOIN OrderProducts using( OrderNo ) JOIN RedemptionMerchants using( MerchantId ) join ProductStatusLUT on ( OrderProducts.Status = ProductStatusLUT.Status ) Join MergeHistory on( Orders.AccountNo = MergeHistory.SourceAccount) where MergeHistory.DestinationAccount = $AccountNo)
		order by Date DESC";
		return DBQueryExitOnFailure( $sql );
	}

 	function GetPrintableRedemptionHistory( $AccountNo )
	{
#		$sql = "SELECT Date_Format( Redemptions.CreationDate, '%d/%m/%Y') as Date, Description, MerchantName, Cost, Quantity, Redemptions.CreatedBy as Agent from Redemptions JOIN RedemptionMerchants using( MerchantId ) where AccountNo = $AccountNo order by Redemptions.CreationDate DESC";
		$sql = "(SELECT Date_Format( Orders.CreationDate, '%Y-%m-%d %H:%i') as Date, ProductId, Description, MerchantName, QuantityType as Type, Quantity, Cost as TotalCost, Orders.CreatedBy as Agent, StatusDescrip  as Status from Orders JOIN OrderProducts using( OrderNo ) JOIN RedemptionMerchants using( MerchantId ) join ProductStatusLUT on ( OrderProducts.Status = ProductStatusLUT.Status ) where AccountNo = $AccountNo)
		UNION
		(SELECT Date_Format( Orders.CreationDate, '%Y-%m-%d %H:%i') as Date, ProductId, Description, MerchantName, QuantityType as Type, Quantity, Cost as TotalCost, Orders.CreatedBy as Agent, StatusDescrip  as Status from Orders JOIN OrderProducts using( OrderNo ) JOIN RedemptionMerchants using( MerchantId ) join ProductStatusLUT on ( OrderProducts.Status = ProductStatusLUT.Status ) Join MergeHistory on( Orders.AccountNo = MergeHistory.DestinationAccount) where MergeHistory.SourceAccount = $AccountNo)
		order by Date DESC";
		return DBQueryExitOnFailure( $sql );
	}


?>