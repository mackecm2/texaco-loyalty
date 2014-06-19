<?php

function GetNewBatchSize()
{
	$LastTimes = GetNewWebRegBatchTimes();
	$sql = "SELECT count(*) as NoRecords from Members where CreatedBy = 'WEB' and CreationDate >= '$LastTimes[1]'";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row[0];
}

function GetWebRequestBatches( $limit )
{
	
	$sql = "select * from WebRegistrations order by BatchTime DESC limit $limit";

	return DBQueryExitOnFailure( $sql );
}

function GetNewWebRegBatchTimes()
{
	$sql = "select BatchTime, now() from WebRegistrations order by BatchTime limit 1";
	$results = DBQueryExitOnFailure( $sql );
	$row = mysql_fetch_row( $results );
	return $row;
}

function CreateWebRegBatch()
{
	$LastTimes = GetNewWebRegBatchTimes();
	$sql = "insert into WebRegistrations values ( '$LastTimes[1]', '$LastTimes[0]', 0 )";
	$results = DBQueryExitOnFailure( $sql );
	return $LastTimes[1];
}

function GetWebRequestBatchData( $BatchTime)
{
	$sql = "select StartTime, NoRecords from WebRegistrations where BatchTime = '$BatchTime'";
	

	$results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_row( $results );

	$sql = "Select PrimaryCard, Title, Forename As FirstName, Surname, Address1, Address2, Address3, Address4, Address5, PostCode from Members 
	where CreatedBy = 'WEB' and CreationDate between  '$row[0]' and  '$BatchTime'";


	return DBQueryExitOnFailure( $sql );
}

function WebRegistrationsUpdateNo( $BatchTime, $NoRecords )
{
	$sql = "Update WebRegistrations Set NoRecords = $NoRecords where  BatchTime = '$BatchTime'";
	
	$results = DBQueryExitOnFailure( $sql );
}
?>