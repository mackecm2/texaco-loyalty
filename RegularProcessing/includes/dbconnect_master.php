<?php
$mastercon= @mysql_connect("rsmdb:3307","sitedata","Cl3arWay")
or mysqlError("Cannot connect to MySQL.");

$db = @mysql_select_db("sitedata",$mastercon)
or mysqlError("Cannot select the sitedata database. Please check your details in the database connection file and try again");
?>