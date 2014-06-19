<?php 

	include "../include/Session.inc";
	include "../DBInterface/ReportRequestInterface.php";

	if( isset( $_POST["ReportType"] ))
	{
		switch( $_POST["ReportType"] )
		{
			case 'Fraud':
				$req = "SELECT Members.PrimaryCard, CONCAT_WS( ' ', Members.Title, Members.Forename, Members.Surname ) as MemberName, Accounts.Balance, Accounts.TotalRedemp INTO Outfile '%f' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\\r\\n' from Members Join Accounts using(AccountNo) where Members.PrimaryMember = 'Y' and Accounts.AccountType = 'F'";
				InsertReportRequest( $req, "Fraud Report", "CardNo, MemberName, Balance, Redeemed" );
			break;
		}
	}
	header("Location:Reports.php");

?>