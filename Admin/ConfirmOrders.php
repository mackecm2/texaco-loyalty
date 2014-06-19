<?php  
	include "../include/Session.inc";
	include "../DBInterface/OrdersInterface.php";

	if( isset( $_GET["Type"] ) and isset( $_GET["Timestamp"] ) )
	{
		ConfirmOrdersBatch( $_GET["Type"], $_GET["Timestamp"] );
	}

	header("Location:TransferOrdersFile.php");
?>