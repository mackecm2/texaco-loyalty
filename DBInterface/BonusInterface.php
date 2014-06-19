<?php

function GetAllBonuses()
{
	$sql = "Select * from BonusPoints where Active = 'Y' order by priority";
	
	return DBQueryExitOnFailure( $sql );
}
	
function GetCurrentBonuses()
{
	$sql = "Select * from BonusPoints where Active = 'Y' AND Status = 'A' order by priority";
	
	return DBQueryExitOnFailure( $sql );
}

function GetApprovalBonuses()
{
	$sql = "Select * from BonusPoints where Active = 'Y' AND Status = 'P' order by priority";
	
	return DBQueryExitOnFailure( $sql );
}

function GetBonusFieldNameList()
{
	$sql = "Select Distinct FieldName from FieldComparisions";

	$results = DBQueryExitOnFailure( $sql );

	$fieldsList = array( "" => "");

	while( $row = mysql_fetch_row( $results ) )
	{
		$fieldsList[$row[0]] = $row[0];
	}

	return $fieldsList;
}

function GetCurrentBonusSettings( $promoCode )
{

		$sql = "Select * from BonusPoints where PromotionCode = '$promoCode'";

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) > 0 )
		{
			return mysql_fetch_assoc( $results ) ;
		}
		return false;
}

function GetCurrentBonusCriteria( $promoCode )
{

		$sql = "Select BonusCriteria.FieldName, BonusCriteria.ComparisionType, ComparisionCrteria, Boolean, PopulateType, Populate  from BonusCriteria inner join FieldComparisions using (FieldName, ComparisionType) where PromotionCode = '$promoCode' order by CriteriaNo";

		return DBQueryExitOnFailure( $sql );
}

function GetAbriviatedBonusCriteria( $promoCode )
{
		$criteria = "";
		$sql = "Select * from BonusCriteria where PromotionCode = '$promoCode' order by CriteriaNo";

		$results = DBQueryExitOnFailure( $sql );
		while( $row= mysql_fetch_assoc( $results ) )
		{
			$criteria .=" $row[FieldName] $row[ComparisionType] $row[ComparisionCrteria]";
		}
		return $criteria;
}

function GetBonusFieldComparisionOptions( $FieldName )
{
		$sql = "Select ComparisionType from FieldComparisions where FieldName = '$FieldName'";

		$results = DBQueryExitOnFailure( $sql );

		$compList = array();
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$compList[$row["ComparisionType"]] = htmlspecialchars( $row["ComparisionType"]); 
		}
		return $compList;
}


function GetBonusFieldValues( $sql )
{
	$singleList = array();
	if( !is_null( $sql ) )
	{
		$results = DBQueryExitOnFailure( $sql );
		while( $row = mysql_fetch_row( $results ) )
		{
			$singleList[$row[1]] = htmlspecialchars( $row[0]); 
		}
	}
	return $singleList;
}

function DeletePromotionCode( $promo )
{
	$sql = "Update BonusPoints set Active = 'N' where PromotionCode = '$promo'";
	$results = DBQueryExitOnFailure( $sql );
	$sql = "Delete from BonusCriteria where PromotionCode = '$promo'";
	$results = DBQueryExitOnFailure( $sql );
}

function SetPromotionPriority( $promo, $priority )
{
	$sql = "Update BonusPoints set Priority = $priority where PromotionCode = '$promo'";
	DBQueryExitOnFailure( $sql );
}


	function GetBonusList(  )
	{
		$sql = "SELECT PromotionCode from BonusPoints";

		$results = DBQueryExitOnFailure( $sql );
		$userTypes = array();
		
		while( $row = mysql_fetch_assoc( $results ) )
		{
			$userTypes[$row["PromotionCode"]] = $row["PromotionCode"];
		}
		return $userTypes;
	}

	function CreatePersonalBonus( $MemberNo, $PromotionCode, $StartDate, $Period )
	{
		global $uname;
		$sql = "Insert into PersonalCampaigns( MemberNo, PromotionCode, StartDate, EndDate, CreationDate, CreatedBy ) values ( $MemberNo, '$PromotionCode', $StartDate, Date_add( $StartDate, interval $Period), now(), '$uname'  )";
		DBQueryExitOnFailure( $sql );
	}

?>