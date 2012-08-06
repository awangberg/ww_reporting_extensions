<?php

include("access.php");

$db = "goteam";

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table problems:
//drop table 'courses';


//create table courses in GOTEAM database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS courses
(
courseID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(courseID),
courseName VARCHAR(60),
sectionName VARCHAR(60),
createdBy VARCHAR(60),
createdOnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
startDate TIMESTAMP,
endDate TIMESTAMP
)";


if (mysql_query($sql,$con)) {
	echo "Table courses created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
