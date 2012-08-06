<?php

include("access.php");

function check_input($value) {
  //Stripslashes
  if (get_magic_quotes_gpc()) {
	$value = stripslashes($value);
  }

  // Quote if not a number
  if (!is_numeric($value)) {
	$value = "'" . mysql_real_escape_string($value) . "'";
  }
  return $value;
}

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_POST['userDatabaseName'];

//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}


$drawData = check_input($_POST['drawData']);
$promptData = check_input($_POST['promptData']);
$problemName = check_input($_POST['problemName']);
$promptName = check_input($_POST['promptName']);
$assignToStudents = $_POST['assignToStudents'];
$peerReviewedBy = $_POST['peerReviewedBy'];
$submittedBy = check_input($_POST['submittedBy']);
$courseID = check_input($_POST['courseID']);
$existingHomeworkSetID  = check_input($_POST['existingHomeworkSetID']);
$homeworkSetName = check_input($_POST['homeworkSetName']);
$questionType = check_input($_POST['questionType']);
$dueDate = check_input($_POST['dueDate']);
$selfReview = check_input($_POST['selfReview']);
$selfReview = $selfReview == "'y'" ? 1 : 0;
$submissionPointsPossible = check_input($_POST['submissionPoints']);
$maxPointsPossibleQuestion1 = check_input($_POST['maxPointsPossibleQuestion1']);
$maxPointsPossibleQuestion2 = check_input($_POST['maxPointsPossibleQuestion2']);
$maxPointsPossibleQuestion3 = check_input($_POST['maxPointsPossibleQuestion3']);
$query1 = "";

$todaysDate = date ("Y-m-d H:m:s");

if ($homeworkSetName != "''") {
  //insert the new homework set information into the database table 'homeworkSets';
  $query = "INSERT INTO homeworkSets (homeworkSetName, forCourseID, submittedBy, submittedOnDate) ";
  $query .= "VALUES ($homeworkSetName, $courseID, $submittedBy, '$todaysDate')";

  $query1 .= $query;
  $result = mysql_query($query, $con);
  $homeworkSetID = mysql_insert_id();
}
else {
  $homeworkSetID = $existingHomeworkSetID;
}


if ($questionType == "'selfexplanation'") {
  //insert the information into the database table 'problems'
  $query = "INSERT INTO problems (problemName, drawData, submittedBy) ";
  $query .= "VALUES ($problemName, $drawData, $submittedBy)";

  $query1 .= $query;
  $result = mysql_query($query, $con);
  $problemID = mysql_insert_id();

  //insert the information into the database table 'prompts'
  $query = "INSERT INTO prompts (promptName, promptData, submittedBy) ";
  $query .= "VALUES ($promptName, $promptData, $submittedBy)";

  $query1 .= $query;
  $result = mysql_query($query, $con);
  $promptID = mysql_insert_id();

  //for each userID in the list of userID's,
  //insert a record into the table selfexplanation
  //and have it point to the $problemID and $promptID records.
  $addUserID = explode(" ", $assignToStudents);

  for ($i = 0; $i < count($addUserID); $i++) {
    //insert the information into the database table 'selfExplanation'
    $query = "INSERT INTO selfExplanation (selfExplanationProblemID, selfExplanationPromptID, selfExplanationAssignedToStudentID, selfExplanationHomeworkSetID, selfExplanationDueDate, selfExplanationAnswer1PossiblePoints, selfExplanationAnswer2PossiblePoints, selfExplanationAnswer3PossiblePoints, selfExplanationToCompleteTime, selfExplanationToCompleteVisits, selfExplanationAfterCompletedTime, selfExplanationAfterCompletedVisits) ";
    $query .= "VALUES ($problemID, $promptID, $addUserID[$i], $homeworkSetID, $dueDate, ";
    $query .= "$maxPointsPossibleQuestion1, $maxPointsPossibleQuestion2, $maxPointsPossibleQuestion3,";
    $query .= "0,0,0,0)";

    $query1 .= $query;
    $result = mysql_query($query, $con);
    $selfExplanationID = mysql_insert_id();
  }
}
else if ($questionType == "'studentsubmission'") {
  $addUserID = explode(" ", $assignToStudents);
  $peerReviewerUserID = explode(" ", $peerReviewedBy);

  //insert the information into the database table 'prompts'
  $query = "INSERT INTO prompts (promptName, promptData, submittedBy) ";
  $query .= "VALUES ($promptName, $promptData, $submittedBy)";

//  $query .= "VALUES ('";
//  $query .= $promptName;
//  $query .= "', '";
//  $query .= $promptData;
//  $query .= "', '";
//  $query .= $submittedBy;
//  $query .= "')";

  $query1 .= $query;
  $result = mysql_query($query, $con);
  $promptID = mysql_insert_id();

  for ($i = 0; $i < count($addUserID); $i++) {
    //For each student who needs to submit a studentsubmission,
    //insert the problem into the problem table
    //and update the studentSubmissions table
    $query = "INSERT INTO problems (problemName, drawData, submittedBy) ";
    $query .= "VALUES ($problemName, $drawData, $addUserID[$i])";

//    $query .= "VALUES ('";
//    $query .= $problemName;
//    $query .= "', '";
//    $query .= $drawData;
//    $query .= "', '";
//    $query .= $addUserID[$i];
//    $query .= "')";

    $query1 = $query;
    $result = mysql_query($query, $con);
    $problemID = mysql_insert_id();

    $query = "INSERT INTO studentSubmissions (submissionProblemID, submissionByUserID, submissionHomeworkSetID, submissionDueDate, submissionPossiblePoints, submissionToCompleteTime, submissionToCompleteVisits, submissionAfterCompletedTime, submissionAfterCompletedVisits)";
    $query .= "VALUES ($problemID, $addUserID[$i], $homeworkSetID, $dueDate, $submissionPointsPossible, 0, 0, 0, 0)";

    $query1 .= $query;
    $result = mysql_query($query, $con);
    $submissionID = mysql_insert_id();

    
    //then, for each student who is supposed to peerreview this problem,
    //insert the problem information and prompts into the peerreview table
    //for each peerreview student.	
	
    for ($j = 0; $j < count($peerReviewerUserID); $j++) {
      if (($peerReviewerUserID[$j] != $addUserID[$i]) || $selfReview) {
	//insert the information into the database table 'prompts'
	$query = "INSERT INTO peerReview (peerReviewBaseProblemID, peerReviewPromptID, peerReviewByUserID, peerReviewHomeworkSetID, peerReviewDueDate, peerReviewAnswer1PossiblePoints, peerReviewAnswer2PossiblePoints, peerReviewAnswer3PossiblePoints, peerReviewToCompleteTimeByReviewer, peerReviewToCompleteVisitsByReviewer, peerReviewAfterCompletedTimeByReviewer, peerReviewAfterCompletedVisitsByReviewer, peerReviewAfterCompletedTimeByProblemAuthor, peerReviewAfterCompletedVisitsByProblemAuthor) ";
	$query .= "VALUES ($problemID, $promptID, $peerReviewerUserID[$j], $homeworkSetID, $dueDate, $maxPointsPossibleQuestion1, $maxPointsPossibleQuestion2, $maxPointsPossibleQuestion3, 0, 0, 0, 0, 0, 0)";

	$query1 .= $query;
	$result = mysql_query($query, $con);
	$peerReviewID = mysql_insert_id();
      }
    }
  }
}
else if ($questionType == "'peerreview'") {

}


//close connection
mysql_close($con);

print "resultCode=SENT&query=".$query1."&result=".$result;

?>