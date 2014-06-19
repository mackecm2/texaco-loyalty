<?php 

	$db_host = "localhost";
	$db_name = "Texaco";
	$db_user = "root";
	$db_pass = "FLOWER";

	
	function connectToDB()
	{
		global $con, $db;
		global 	$db_host, $db_name, $db_user, $db_pass;
		$con= @mysql_connect("$db_host","$db_user","$db_pass")
			or die ("Cannot connect to MySQL.");

		$db = @mysql_select_db("$db_name",$con)
			or die ("Cannot select the $db_name database. Please check your details in the database connection file and try again");

	}


	function WriteComments( &$Wiz )
	{
		
		fputs( $Wiz, "// Atomatically generated script file that produces the code to calculate bonuses\n");
		fputs( $Wiz, "// Script run ". date( "r" )."\n\n\n" );

	}

	function WriteProductFunction( &$Wiz )
	{
		$sql = "Select * from BonusPoints where AppliesTo = 'Product' or AppliesTo = 'Quantity' order by Priority";
		

	}


	function WriteDepartmentFunction( &$Wiz )
	{
		$sql = "Select * from BonusPoints where AppliesTo = 'Dept'";

		
	}

	function WriteTotalFunction( &$Wiz )
	{
		$sql = "Select * from BonusPoints where AppliesTo = 'Total'";

	}

	function WriteVisitFunction( &$Wiz )
	{
		$sql = "Select * from BonusPoints where AppliesTo = 'Visit'";

	}

	function WriteCalculateAlgo( &$Wiz )
	{
		$sql = "Select * from BonusPoints order by Priority";

		$results = mysql_query( $sql );
		$ifs = "if(";

		fputs( $Wiz, "function CalculateProductBonus()\n{\n");
		while( $row = mysql_fetch_assoc( $results ))
		{
			fputs( $Wiz, "     $ifs ProductRange( $row[ProductLow], $row[ProductHigh]) and " );
			fputs( $Wiz, "DateRange( '$row[StartDate]', '$row[EndDate]')" );
			
			if( $row['SiteLow'] != null )
			{
				fputs( $Wiz, "and SiteRange( $row[SiteLow], $row[SiteHigh])" );
			}
			fputs( $Wiz, ")\n     {\n          return  Bonuses( $row[BonusPoints], $row[PerQuantity], $row[BonusEntry], " );
			if( $row['Exclude'] == 1 )
			{
				fputs( $Wiz, "true" );
			}
			else
			{
				fputs( $Wiz, "false" );
			}
			fputs( $Wiz, ");\n     }\n" );
			$ifs = "else if(";
		}	
		fputs( $Wiz, "     return 0;\n}\n" );
	
	}

	function WriteConversionFunction( &$Wiz )
	{


	}

	connectToDB();
	$Wiz = fopen( "C:\\projects\\Texaco\\Calculate.inc", "w" );
	fputs( $Wiz, "<?php\n\n");
	WriteComments( $Wiz );
	WriteCalculateAlgo( $Wiz );

	fputs( $Wiz, "\n\n?>\n" ); 

	fclose( $Wiz );



?>