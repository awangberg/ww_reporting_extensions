<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";


if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table assignments:
//drop table 'selfExplanation';  


//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS selfExplanation 
(
selfExplanationID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(selfExplanationID),
selfExplanationProblemID INT,
selfExplanationPromptID INT,
selfExplanationAssignedToStudentID INT,
selfExplanationHomeworkSetID INT,
selfExplanationLastVisit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
selfExplanationAssignedOnDate TIMESTAMP,
selfExplanationDueDate TIMESTAMP,
selfExplanationCompletedOnDate TIMESTAMP,
selfExplanationToCompleteTime INT,
selfExplanationToCompleteVisits INT,
selfExplanationAfterCompletedTime INT,
selfExplanationAfterCompletedVisits INT,
selfExplanationAnswer1 LONGTEXT,
selfExplanationAnswer1Points INT,
selfExplanationAnswer1PossiblePoints INT,
selfExplanationAnswer2 LONGTEXT,
selfExplanationAnswer2Points INT,
selfExplanationAnswer2PossiblePoints INT,
selfExplanationAnswer3 LONGTEXT,
selfExplanationAnswer3Points INT,
selfExplanationAnswer3PossiblePoints INT
)";

if (mysql_query($sql,$con)) {
	echo "Table selfExplanation created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
