<?php

	$dbServer = "localhost";
	$dbUser = "CompowerProcess";
	$dbPass = "ComPassword";
	$dbName = "texaco";


	function ConnectToDb($server, $user, $pass, $database)
	{
		# Connect to the database and return
		# true/false depending on whether or
		# not a connection could be made.

		$s = @mysql_connect($server, $user, $pass);
		$d = @mysql_select_db($database, $s);

		if(!$s || !$d)
			return false;
		else
			return true;
	}


	$date = date("Y-m-d") ;
?>
<head>
<title>Texaco Site Data Import Screen &#8226; Results</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link href="css/module.css" rel="stylesheet" type="text/css" />
</head>
<body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 class="bodytext">
<p class="HeaderMainOrange">&nbsp;</p>
<table width="75%" height="200" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="25%" align="left" valign="top">
      <br />
      <br />

    </td>
  	<td height="8">
  	<td width="85%" align="left" valign="top">
  	<span class="HeaderMain">&#8226; Database File Import Results &#8226;</span>


<?php
// In PHP versions earlier than 4.1.0, $HTTP_POST_FILES should be used instead
// of $_FILES.



# Get a connection to the database

$dbConn = ConnectToDb($dbServer, $dbUser, $dbPass, $dbName)	;

	$uploadfile = '/data/dataimport/distributorsites.csv';




#  First we need to find out which table we are interested in.


		$table 	=   "Sites";



#  Now we need to write into the relevant table




		$row = 1;
		$handle = fopen ("$uploadfile","r");

		while ($data = fgetcsv ($handle, 1000, ","))
		{
  		 	$query = "INSERT INTO $table (
   				`SiteType`,
   				`SiteCode`,
  				`SiteName`,
   				`Address1`,
   				`Address2`,
   				`Address3`,
   				`PostCode`,
   				`AreaCode`,
   				`CreationDate`,
   				`CreatedBy`)
   			VALUES(
   				'ADED',
   				'".(addslashes($data[2]))."',
   				'".(addslashes($data[3]))."',
   				'".(addslashes($data[4]))."',
   				'".(addslashes($data[5]))."',
   				'".(addslashes($data[6]))."',
   				'".(addslashes($data[7]))."',
   				'".(addslashes($data[10]))."',
   				'$date',
   				'SystemImport')";

			echo "$query<br>";

  			$result = mysql_query($query) or die("Invalid query: " . mysql_error().__LINE__.__FILE__);
   			$row++;
		}
		fclose ($handle);



if(!($result))
   	{
		$dataloadmessage = "<span class=\"bodytext\">$table file import failed.</span><br><br>"	;
	}
else
	{
		$dataloadmessage = "<span class=\"bodytext\">$table file import successful.<br><br>
		</span>"	;
	}

echo "$dataloadmessage<br />";




?>