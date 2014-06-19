<?php
// create table MembersPostMigrationChanges ( MemberNo bigint, Old char, New char );
	include "../../include/DB.inc";
	$db_user = 'root';
	$db_pass = 'trave1';

	connectToDB( MasterServer, TexacoDB );
	$fileToProcess = "/data/temp/CorrectedTMail.csv";
//	$fileToProcess = "T:/csvfiles/CorrectedTMail.csv";
	$fr = fopen( $fileToProcess, "r");

	$Changed = 0;
	$ChangeCode = array();
	$UsedCodes = array();
	$c = 0;
	while( $line = fgetcsv( $fr, 2048, "~" )  )
	{
		$c++;
		if( $c % 10000 == 0)
		{
			echo "\n$c\n";
		}
		$TOKMail = $line[0];
		$Source  = trim( $line[1]);
		$OptIn_TParty = $line[2];
		$MemberNo = $line[3];


		$sql = "Select * from Members where MemberNo = $MemberNo";

		$results = DBQueryExitOnFailure( $sql );

		if( mysql_num_rows( $results ) > 0 )
		{
			$row = mysql_fetch_assoc( $results );
			if( ($row["TOKMail"] == 'Y') xor ( $TOKMail == '1' ) )
			{
				//echo "different to how originally translated $MemberNo";
//				$sql = "select * from Tracking where MemberNo = $MemberNo and CreationDate > '2004-10-24'";
//				$results = mysql_query( $sql );
//				while( $t = mysql_fetch_assoc( $results ) )
//				{
//					//echo "C $t[TrackingCode]";
//					$ChangeCode[$t["TrackingCode"]]++;
//				}
			}
			else
			{
//				$sql = "select * from Tracking where MemberNo = $MemberNo and CreationDate > '2004-10-24' and TrackingCode in ( 812, 1116, 1125, 1140 )";

				$sql = "select * from Tracking where MemberNo = $MemberNo and CreationDate > '2004-10-24'";
																									
				$results = DBQueryExitOnFailure( $sql );
				$ok = true;
				while( $t = mysql_fetch_assoc( $results ) )
				{
					$b = $t["TrackingCode"];
					if( $b == 812 or $b = 1116 or $b == 1125 or $b = 1140 )
					{
						$ok = false;
					}
					//echo "T $t[TrackingCode]";
//					$UsedCodes[$t["TrackingCode"]]++;
				}

//				if( mysql_num_rows( $results ) == 0 )
				if( $ok )
				{
					if( $OptIn_TParty == '1' )
					{
						$TOKMail = 'Y';
					}
					else if( $OptIn_TParty == '0' )
					{
						$TOKMail = 'N';
					}
					else
					{
						if( $Source == "TEX1" or $Source == "TEX3" or $Source == "TEX4" )
						{
							if( $TOKMail == '0' )
							{
								$TOKMail = 'N'; 		
							}
							else
							{
								$TOKMail = 'Y'; 		
							}
						}
						else
						{
							$TOKMail = 'N';
						}

						if( $TOKMail != $row["TOKMail"] )
						{
							$sql = "insert into Analysis.MembersPostMigrationChanges values ($MemberNo, '$row[TOKMail]', '$TOKMail') ";
							
							DBQueryExitOnFailure( $sql );

							$sql = "update Members set TOKMail = '$TOKMail' where MemberNo = $MemberNo";

							DBQueryExitOnFailure( $sql );

							//AddTrackingRecord( 1160, $MemberNo, $row["AccountNo"], "TOKMail '$row[TOKMail]'=>'TOKMail'", 0 );
							$Changed++;

						}
					}
				}
			}
		}
	}
	echo "************************\nChanged $Changed";
	print_r( $UsedCodes );
	print_r( $ChangeCode );
?>