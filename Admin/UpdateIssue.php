<?php
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../include/DisplayFunctions.inc";
	include "../include/email.php";

	global $uname;

	if( isset($_POST["IssueNo"]) and $_POST["IssueNo"] != ""  )
	{
		$IssueNo = 	$_POST["IssueNo"];
		$sql = "select * from  Issues where IssueNo = $IssueNo";
		$Mode = "Update";
	}
	else
	{
		$sql = "Select null as IssueNo, null as ShortDescription, null as Description, null as Effort, null as Additional, null as Notes, null as NeededBy, null as Priority, null as PriorityGrp, null as Status, '$uname' as CreatedBy, now() as CreationDate, null as RevisedBy, null as RevisedDate, null as EstimatedDate, null as Responsablity";
		$Mode = "Insert";
	}

	$Results = DBQueryExitOnFailure( $sql );

	$row = mysql_fetch_assoc( $Results );
	$Set = "";
	$Log = "";

	if( $row["ShortDescription"] != $_POST["ShortDescription"] )
	{
		$Set .= ",ShortDescription = '". mysql_escape_string($_POST["ShortDescription"])."'";
		$Log .= "ShortDescription => '$_POST[ShortDescription]'";
	}

	if( $row["Description"] != $_POST["Description"] )
	{
		$Set .= ",Description = '". mysql_escape_string($_POST["Description"])."'";
		$Log .= "Description => '$_POST[Description]'";
	}

	if( $row["Additional"] != $_POST["Additional"] )
	{
		$Set .= ",Additional = '". mysql_escape_string($_POST["Additional"])."'";
		$Log .= "Additional => '$_POST[Additional]'";
	}

	if( $row["Notes"] != $_POST["Notes"] )
	{
		$Set .= ",Notes = '". mysql_escape_string($_POST["Notes"])."'";
		$Log .= "Notes => '$_POST[Notes]'";
	}

	if( $row["Priority"] != $_POST["Priority"] )
	{
		$Set .= ",Priority = '". mysql_escape_string($_POST["Priority"])."'";
		$Log .= "Priority => '$_POST[Priority]'";
	}

	if( $row["Status"] != $_POST["Status"] )
	{
		$Set .= ",Status = '". mysql_escape_string($_POST["Status"])."'";
		$Log .= "Status => '$_POST[Status]'";
	}

	if( $row["EstimatedDate"] != $_POST["EstimatedDate"] )
	{
		$Set .= ",EstimatedDate = '$_POST[EstimatedDate]'";
		$Log .= "EstimatedDate => '$_POST[EstimatedDate]'";
	}
	if( $row["Effort"] != $_POST["Effort"] )
	{
		$Set .= ",Effort = '$_POST[Effort]'";
		$Log .= "Effort => '$_POST[Effort]'";
	}
 	if( $row["NeededBy"] != $_POST["NeededBy"] )
	{
		$Set .= ",NeededBy = '$_POST[NeededBy]'";
		$Log .= "NeededBy => '$_POST[NeededBy]'";
	}

 	if( $row["Responsablity"] != $_POST["Responsablity"] )
	{
		$Set .= ",Responsablity = '". mysql_escape_string($_POST["Responsablity"])."'";
		$Log .= "Responsablity => '$_POST[Responsablity]'";
	}

 	if( $row["PriorityGrp"] != $_POST["PriorityGrp"] )
	{
		$Set .= ",PriorityGrp = '". mysql_escape_string($_POST["PriorityGrp"])."'";
		$Log .= "PriorityGrp => '$_POST[PriorityGrp]'";
	}

	if( $Set != "" )
	{
		if( $Mode == "Insert" )
		{
			$sql = "Insert into Issues set CreatedBy = '$uname', CreationDate = now() $Set";
			DBQueryExitOnFailure( $sql );
			$IssueNo = Mysql_insert_id();

	 		$sql = "Insert into IssuesLog set IssueNo = $IssueNo, RevisedBy = '$uname', RevisedDate = now(), Notes = 'Created'";
			DBQueryExitOnFailure( $sql );

			sendemail("IssuesListNewItem",$IssueNo,"");

		}
		else
		{
  			$sql = "Update Issues set RevisedBy = '$uname', RevisedDate = now() $Set where IssueNo=$IssueNo";
			DBQueryExitOnFailure( $sql );

			sendemail("IssuesListItemEdit",$IssueNo,$Log);

			$Log = mysql_escape_string( $Log );
	 		$sql = "Insert into IssuesLog set IssueNo = $IssueNo, RevisedBy = '$uname', RevisedDate = now(), Notes = '$Log'";
			DBQueryExitOnFailure( $sql );




		}
	}
		header("Location: IssueManager.php");

  ?>