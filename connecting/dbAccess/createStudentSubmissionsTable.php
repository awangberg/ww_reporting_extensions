<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table assignments:
//drop table 'studentSubmissions';  


//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS studentSubmissions
(
submissionID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(submissionID),
submissionProblemID INT,
submissionByUserID INT,
submissionHomeworkSetID INT,
submissionLastVisit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
submissionAssignedOnDate TIMESTAMP,
submissionDueDate TIMESTAMP,
submissionCompletedOnDate TIMESTAMP,
submissionToCompleteTime INT,
submissionToCompleteVisits INT,
submissionAfterCompletedTime INT,
submissionAfterCompletedVisits INT,
submissionPoints INT,
submissionPossiblePoints INT
)";

if (mysql_query($sql,$con)) {
	echo "Table studentSubmissions created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
