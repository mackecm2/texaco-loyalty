	<?php

	//******************************************************************
	//
	// AnswersFileProcess.php
	//
	//
	//
	//******************************************************************


	function AnswersProcessLine( $line )
	{
		global $numMembers;
		#echo "<br>data[0] is $line[0]";

		if(($line[0] <> 'MemberNo') AND isset($line[1]))
		{

			$CardNo 	= $line[1];
			$filepos 	= 2;
			// If we don't have a MemberNo then get it from the Card

			if(($line[0] == '') AND ($CardNo <> ''))
			{

				$sql = "Select Cards.MemberNo from Cards left Join Members using( MemberNo ) where CardNo = '$CardNo'";
				#echo "<br>sql is $sql";
				$results = DBQueryLogOnFailure( $sql );

				if( mysql_num_rows($results) == 0 )
				{
					LogError( "<br>Error in import file CardNo - $CardNo not found" );
					
				}
				else
				{
					$row = mysql_fetch_assoc( $results );
					if( $row["MemberNo"] != "" )
					{
						$MemberNo = $row["MemberNo"];
						#echo "<br>Found MemberNo - $MemberNo";	

					}

				}
				
				$numMembers++;
				
			}
			elseif($line[0] <> '')
			{
			
				$MemberNo = $line[0];
				#echo "<br>Supplied MemberNo - $MemberNo";	
				$numMembers++;
			
			
			}
			
			$AccountNo = GetAccountNo( $MemberNo );


			#echo "<br>filepos is $filepos - content $line[$filepos]";

			while(isset($line[$filepos]) AND ($MemberNo <> ''))
			{

				
				// See if the QuestionId exisits in the db
				
				/* SDT Update 15/1/09
				   This allows for preference data import for the Members table
				   OPTOUT_INF = Members.OKMail field
				   OPTOUT_POS = Members.TOKMail field
				   OPTOUT_ELEC = Members.OKEMail and Members.OKSMS fields
				   
				*/
				

				$cuttext = substr($line[$filepos],0,6);	
				$fulltext = $line[$filepos];
				
				#echo "<br>We have cuttext of $cuttext, fulltext of $fulltext<br>";
				
				if( $cuttext == 'OPTOUT')
				{
					// OK we have a preference item
					
					
					
					$answerfilepos = $filepos+1;
					$answer = $line[$answerfilepos];
					
					#echo "Member $MemberNo Preference Item found - $filepos, answer - $answer<br>";

					if($fulltext == 'OPTOUT_INF')
					{
						if($answer == 'Y')
						{
							$updatedata['OKMail'] = 'N';
						}
						else
						{
							$updatedata['OKMail'] = 'Y';
						}
					}					
					elseif($fulltext == 'OPTOUT_POS')
					{
						if($answer == 'Y')
						{
							$updatedata['TOKMail'] = 'N';
						}
						else
						{
							$updatedata['TOKMail'] = 'Y';
						}
					}
					elseif($fulltext == 'OPTOUT_ELEC')
					{
						if($answer == 'Y')
						{
							$updatedata['OKEmail'] = 'Y';
							$updatedata['OKSMS'] = 'Y';
						}
						else
						{
							$updatedata['OKEMail'] = 'N';
							$updatedata['OKSMS'] = 'N';
						}
					}
					
					
					
					$result = mysqlUpdate($updatedata, "Members", "MemberNo = '$MemberNo'");
					
					$sql = "Insert into Tracking( MemberNo, AccountNo, TrackingCode, Notes, CreationDate, CreatedBy ) 
					values ( $MemberNo,$AccountNo, '1116', 'Preferences updated by questionnaire import', now(), 'FileLoad')";
					
					#echo "$sql<br>";

					$results = DBQueryLogOnFailure( $sql );
					
					
				
				}
				else
				{

					$sql = "Select QuestionId from Questions where QuestionId = '$line[$filepos]'";
					#echo "<br>sql is $sql";

					$results = DBQueryLogOnFailure( $sql );

					if( mysql_num_rows($results) == 0 )
					{
						LogError( "<br>MemberNo - $MemberNo, CardNo - $CardNo - QuestionId - $line[$filepos] not found" );
						return;
					}
					else
					{

						$QuestionId = $line[$filepos];
						$answerfilepos = $filepos+1;
						$Answer = $line[$answerfilepos];

						// Have we seen this answer before ?

						$sql = "Select MemberNo,QuestionId from Answers where QuestionId = '$QuestionId' and MemberNo = '$MemberNo'";
						#echo "<br>sql is $sql";

						$results = DBQueryLogOnFailure( $sql );

						if( mysql_num_rows($results) == 0 )
						{
							$sql = "insert into Answers set MemberNo = '$MemberNo', QuestionId = '$QuestionId', Answer = '$Answer',CreationDate = now(),CreatedBy='AnswersImport'";
						}
						else
						{
							$sql = "update Answers set Answer = '$Answer',CreationDate = now(),CreatedBy='AnswersImport' where MemberNo = '$MemberNo' and QuestionId = '$QuestionId';";

						}

						#echo "<br>$sql";
						$results = DBQueryLogOnFailure( $sql );

					}	
					

				} 

				$filepos += 2;


			}//end while 
			
			
			
			
		} // end if($line[0] <> 'MemberNo')



	}



	// Main function

	function AnswersProcessFile($file)
	{
		global 	$ProcessName;
		global  $uname;
		global $lineNo;
		global $fileToProcess;
		global $numMembers;
		
		$numMembers = 0;

		$fileToProcess = $file;

		$fileMove =  LocationFileProcessing. "Processed/Answers/";

		echo "<HTML><HEAD></HEAD><BODY>";
		echo "<BR>*****************************************************\n";
		echo "<BR> Processing Answers File.\n";
		echo "<BR>$fileToProcess\n";
		echo "<BR>*****************************************************\n";

		$ProcessName = "AnswersImport";
		$uname = $ProcessName;

		$fr = fopen ("$fileToProcess","r");
		$lineNo = 0;

		if(!$fr)
		{
			echo "Error! Couldn't open the file.";
		}
		else
		{

			#$fileRec = createFileProcessRecord($fileToProcess);

			while ($line = fgetcsv ($fr, 10000, ","))
			{

				$lineNo += 1;
				#if( $fileRec )
				#{
					# echo "<br>Processing Line $lineNo";
					AnswersProcessLine( $line );
				#}


			}
			#UpdateFileProcessRecord( $fileRec );
			fclose($fr);
			rename( $fileToProcess, $fileMove . basename($fileToProcess) );
			echo "<br>Finished - $numMembers Member's Answers Imported</BODY></HTML>";
		}
	}




	?>