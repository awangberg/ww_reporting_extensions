<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table assignments:
//drop table 'peerReview';  


//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS peerReview 
(
peerReviewID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(peerReviewID),
peerReviewBaseProblemID INT,
peerReviewPromptID INT,
peerReviewByUserID INT,
peerReviewHomeworkSetID INT,
peerReviewLastVisit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
peerReviewAssignedOnDate TIMESTAMP,
peerReviewDueDate TIMESTAMP,
peerReviewCompletedOnDate TIMESTAMP,
peerReviewToCompleteTimeByReviewer INT,
peerReviewToCompleteVisitsByReviewer INT,
peerReviewAfterCompletedTimeByReviewer INT,
peerReviewAfterCompletedVisitsByReviewer INT,
peerReviewAfterCompletedTimeByProblemAuthor INT,
peerReviewAfterCompletedVisitsByProblemAuthor INT,
peerReviewAnswer1 LONGTEXT,
peerReviewAnswer1Points INT,
peerReviewAnswer1PossiblePoints INT,
peerReviewAnswer2 LONGTEXT,
peerReviewAnswer2Points INT,
peerReviewAnswer2PossiblePoints INT,
peerReviewAnswer3 LONGTEXT,
peerReviewAnswer3Points INT,
peerReviewAnswer3PossiblePoints INT

)";

if (mysql_query($sql,$con)) {
	echo "Table peerReview created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
