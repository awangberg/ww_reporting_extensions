<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table WeBWorK:
//drop table 'WeBWorK';


//create table problems in GOTEAM database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS WeBWorK 
(
wwTableID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(wwTableID),
wwUserName VARCHAR(60),
wwProblemNumber INT,
wwSet VARCHAR(60),
wwCourse VARCHAR(60),
problemsID INT,
nextWWTableID INT,
submittedOnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";


if (mysql_query($sql,$con)) {
	echo "Table WeBWorK created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
