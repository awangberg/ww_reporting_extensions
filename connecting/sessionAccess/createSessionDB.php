<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

if (mysql_query("CREATE DATABASE session",$con)) {
	echo "Database created";
}
else {
	echo "Error creating database: " . mysql_error();
}

mysql_close($con);
?>
