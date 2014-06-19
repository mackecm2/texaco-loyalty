<?php

	function OutputDownloadHeaders( $fillname )
	{
		header('Content-Type:  application/csv');
		header('Pragma: cache');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Connection: close');
		header('Expires: '.date('r', time()+60*60));
		header('Last-Modified: '.date('r', time()));
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$fillname"); 
	}

	function OutputCSVFile( $path, $fillname, $Header )
	{
		OutputDownloadHeaders( $fillname );
		if( $Header != "" )
		{
			echo( $Header );
			echo( "\r\n" );
		}

		readfile( $path.$fillname );
	}

	function OutputCSV( $fillname, $results )
	{
		OutputDownloadHeaders( $fillname );

		$fields = mysql_num_fields( $results );
		$c = "";
		for( $k = 0; $k < $fields; $k++)
		{
			print( $c. mysql_field_name( $results, $k ) );
			$c = ",";
		}
		print( "\r\n" );

		while($row = mysql_fetch_row($results))
		{
			$c = "";
			for( $k = 0; $k < $fields; $k++)
			{
				print( $c.str_replace( ","," ",$row[ $k ] ));
				$c = ",";
			}
			print( "\r\n" );
		}
	}


	function ProcessFiles($filePath, $filePattern, $fileMove)
	{
		global $lineNo, $fileToProcess;
		echo "$filePath$filePattern\n";

		foreach (glob( $filePath . $filePattern ) as $fileToProcess )
		{
			connectToDB( MasterServer, TexacoDB );

			echo "$fileToProcess\n";

			$fr = fopen( $fileToProcess, "r");

			if(!$fr) 
			{
				echo "Error! Couldn't open the file.";
			} 
			else 
			{

				$fileRec = createFileProcessRecord($fileToProcess);
				if( $fileRec )
				{
					// $fr now can be used to represent the opened file
					$line = fgets( $fr );
					if( processHeaderLine( $line ) )
					{
						$success = false;			
						$lineNo = 1;

						while( $line = fgets( $fr ))
						{
							$lineNo++;
							processLine( $line );
						}

					}
					UpdateFileProcessRecord( $fileRec );
				}
				fclose($fr);
				rename( $fileToProcess, $fileMove . basename($fileToProcess) ); 
			}
		}
	}


?>