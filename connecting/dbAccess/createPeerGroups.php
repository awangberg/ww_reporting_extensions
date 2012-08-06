<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table problems:
//drop table 'peerGroups';


//create table problems in GOTEAM database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS peerGroups
(
peerGroupsID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(peerGroupsID),
peerGroupName VARCHAR(60),
listOfStudentIDs VARCHAR(200),
forCourseID INT,
submittedByID INT,
lastVisitedOnDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
submittedOnDate TIMESTAMP,
startDate TIMESTAMP,
endDate TIMESTAMP
)";


if (mysql_query($sql,$con)) {
	echo "Table peerGroups created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
