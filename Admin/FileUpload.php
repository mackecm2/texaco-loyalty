<?php
error_reporting(E_ERROR);

/****************************************************************
//
// Handles the different types of files that Dawleys can upload
//   None of the files have featres that makes it that easy to
//   detect the type of files.
//
//  The actual processing code is in /FileProcessing/UploadFiles
//
****************************************************************/


	require "../include/Session.inc";
	require "../include/CSVFile.php";
	require "../include/Locations.php";
	require "../DBInterface/CardRequestInterface.php";
	require "../DBInterface/FileProcessRecord.php";
	require "../DBInterface/TrackingInterface.php";
	require "../DBInterface/CardInterface.php";
	require "../DBInterface/QuestionaireInterface.php";
	require "../DBInterface/MemberInterface.php";
	require "../DBInterface/WelcomePackInterface.php";
	require "../DBInterface/BonusInterface.php";

	require "../FileProcessing/UploadFiles/CardRequestFileProcess.php";
	require "../FileProcessing/UploadFiles/BatchFileProcess.php";
	require "../FileProcessing/UploadFiles/GroupLoyaltyFileProcess.php";
	require "../FileProcessing/UploadFiles/PersonalCampaignFile.php";
	require "../FileProcessing/UploadFiles/MTVFileProcess.php";
	require "../FileProcessing/UploadFiles/GoneAways.php";
	require "../FileProcessing/UploadFiles/ScratchCards.php";
 	require "../FileProcessing/UploadFiles/Q8Merge.php";
	require "../FileProcessing/UploadFiles/TrackingFileProcess.php";
	require "../FileProcessing/UploadFiles/AnswersFileProcess.php";
	
	require "../FileProcessing/General/classes.php";
	require "../FileProcessing/General/BonusFuncs.php";
	require "../FileProcessing/General/ProductAlocation.php";
	require "../FileProcessing/General/DatabaseUpdate.php";
	require "../FileProcessing/General/Calculate.php";


	$filePath =  LocationFileProcessing;
	$errorCount = 0;


	$uploadfile = $filePath . $_FILES['userfile']['name'];

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
	{
		switch( $_POST["filetype"] )
		{
		case "Bulk":
			BatchProcessFile($uploadfile,"Bulk");
			break;
		case "Registration":
			BatchProcessFile($uploadfile,"Registration");
			break;			
		case "Request":
			RequestProcessFiles($uploadfile);
			break;
		case "BonusMail":
			PersonalCampaignFile( $uploadfile);
			break;
		case "MTV":
			MTVFile( $uploadfile );
			break;
		case "Gone":
			GoneAwayProcessFiles( $uploadfile);
			break;
		case "ScratchCards":
			ScratchcardProcessFiles( $uploadfile);
			break;
		case "Q8Merge":
			Q8ProcessFiles( $uploadfile );
			break;
		case "Tracking":
			TrackingProcessFile( $uploadfile );
			break;
		case "Answers":
			AnswersProcessFile( $uploadfile );
			break;		
		case "GroupLoyalty":
			$AccountNo = $_REQUEST['AccountNo'];
			GroupProcessFile( $uploadfile, $AccountNo );
			break;		
		default:
			echo $_POST["filetype"];

		}
	}
	else
	{
		echo "Failed to move ".$_FILES['userfile']['tmp_name']." to $uploadfile";
//		print_r( $_FILES );
		switch ($_FILES['userfile'] ['error'])
 		{  case 1:
      	     	echo '<p> The file is bigger than this PHP installation allows</p>';
          	 	break;
    		case 2:
           		echo '<p> The file is bigger than this form allows</p>';
           		break;
    		case 3:
           		echo '<p> Only part of the file was uploaded</p>';
           		break;
    		case 4:
           		echo '<p> No file was uploaded</p>';
           		break;
 		}
	}
?>