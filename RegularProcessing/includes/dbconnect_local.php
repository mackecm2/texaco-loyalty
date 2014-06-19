<?php

	$localcon= @mysql_connect("localhost","siteimport","campbell23")
	or mysqlError("Cannot connect to MySQL.");

	$db = @mysql_select_db("texaco",$localcon)
	or mysqlError("Cannot select the texaco database. Please check your details in the database connection file and try again");
?>