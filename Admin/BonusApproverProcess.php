<?php
//******************************************************************
//  BonusApproverProcess.php - MRM 10/09/08
//
//  Approves or rejects a promotion (from BonusApprovalManager.php)
//  Checks that the user is authorised
//  If the promotion is rejected, checks that there are some comments to explain why
//  Sends an e-mail once the job is done
//
//******************************************************************
 
	include "../include/Session.inc";
	include "../include/CacheCntl.inc";
	include "../DBInterface/BonusInterface.php";
	include "../include/BonusFunctions.inc";
	require("../mailsender/class.phpmailer.php");
	
$promocode = $_POST["promocode"];
$comments = $_POST["Comments"];
//if ($_SESSION["grp"] == "MAdmin" OR $_SESSION["grp"] == "SAdmin" OR $_SESSION["grp"] == "MPromo")
if ($_SESSION['username'] == "Bronagh Carron" OR $_SESSION['username'] == "Mandy Hodson" OR $_SESSION["grp"] == "MPromo")
{
    if (isset($_POST["Reject"])) 
    {
    	if ($comments == '')
    	{
    		header("Location: BonusApprover.php?promoCode=".$promocode."&comment=Missing");
    	    exit();
    	}
    	else 
    	{
    		$status = "R"; 
    		$urgent = 99;
    		sendemail($promocode, $comments, $urgent);
    	}
    }

        if (isset($_POST["Approve"])) 
    {
    	$status = "A";
    	$urgent = 100;
    	sendemail($promocode, $comments, $urgent);
    }
    
    $slashcomments = mysql_real_escape_string($comments);
	$sql = "UPDATE BonusPoints SET Status = '".$status."',
			 Comments = '".$slashcomments."',
			  RevisedBy = '".$_SESSION['username']."' WHERE PromotionCode = '".$promocode."'";
// 	echo $sql;
    $res = DBQueryExitOnFailure( $sql );
    

     header("Location: BonusApprovalManager.php");
}
else 
{
	header("Location: BonusApprover.php?promoCode=".$promocode."&comment=NotAuth");
}
?>