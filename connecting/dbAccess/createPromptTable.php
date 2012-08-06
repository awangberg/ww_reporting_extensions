<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table problems:
//drop table 'prompts';  


//create table problems in GOTEAM database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS prompts 
(
promptID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(promptID),
promptName VARCHAR(60),
promptData TEXT,
submittedBy INT,
submittedOnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";


if (mysql_query($sql,$con)) {
	echo "Table prompts created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
