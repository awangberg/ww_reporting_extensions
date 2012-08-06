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
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}


$submittedBy = check_input($_POST['submittedBy']);
$questionType = check_input($_POST['problemType']);
$earnedPoints = check_input($_POST['submissionPoints']);

$query1 = "";

if ($questionType == "'selfexplanation'") {
  $studentAnswer = check_input($_POST['studentAnswer']);
  $questionNumber = check_input($_POST['questionNumber']);
  $assignmentID = check_input($_POST['assignmentID']);

  $answerField = "selfExplanationAnswer" . $questionNumber;
  $answerPoints = "selfExplanationAnswer" . $questionNumber . "Points";

  //update the answer information into the database table 'selfExplanation'
  $query = "UPDATE selfExplanation SET $answerField = $studentAnswer WHERE selfExplanationID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //update the answer information into the database table 'selfExplanation'
  $query = "UPDATE selfExplanation SET $answerPoints = $earnedPoints WHERE selfExplanationID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //OLD:  update the completedOnDate if $questionNumber == 1
  //  if ($questionNumber == 1) {
  //Actually, update the record if
  //the old CompletedOnDate is 0000-00-00 00:00:00
  if (1) {
    $todaysDate = date ("Y-m-d H:m:s");
    $query = "UPDATE selfExplanation SET selfExplanationCompletedOnDate = '$todaysDate' WHERE selfExplanationID = $assignmentID && selfExplanationCompletedOnDate = '0000-00-00 00:00:00'";
    $query1 .= $query;
    $result = mysql_query($query, $con);
  }
}
else if ($questionType == "'studentsubmission'") {
  $submissionID = check_input($_POST['submissionID']);
  $drawData = check_input($_POST['drawData']);
  $problemID = check_input($_POST['problemID']);

  $query = "UPDATE studentSubmissions SET submissionPoints = $earnedPoints WHERE submissionID = $submissionID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $todaysDate = date ("Y-m-d H:m:s");
  $query = "UPDATE studentSubmissions SET submissionCompletedOnDate = '$todaysDate' WHERE submissionID = $submissionID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE problems SET drawData = $drawData WHERE problemID = $problemID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE problems SET submittedOnDate = '$todaysDate' WHERE problemID = $problemID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE problems SET submittedBy = $submittedBy WHERE problemID = $problemID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

}
else if ($questionType == "'peerreview'") {
  $studentAnswer = check_input($_POST['studentAnswer']);
  $questionNumber = check_input($_POST['questionNumber']);
  $assignmentID = check_input($_POST['assignmentID']);

  $answerField = "peerReviewAnswer" . $questionNumber;
  $answerPoints = "peerReviewAnswer" . $questionNumber . "Points";

  //update the answer information into the database table 'peerReview'
  $query = "UPDATE peerReview SET $answerField = $studentAnswer WHERE peerReviewID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //update the answer information into the database table 'peerReview'
  $query = "UPDATE peerReview SET $answerPoints = $earnedPoints WHERE peerReviewID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //(OLD):  update the completedOnDate if $questionNumber == 1
  //if ($questionNumber == 1) {
  //(NEW):  Update the completedOnDate if
  //the old completedOnDate = "0000-00-00 00:00:00"
  if (1) {
    $todaysDate = date ("Y-m-d H:m:s");
    $query = "UPDATE peerReview SET peerReviewCompletedOnDate = '$todaysDate' WHERE peerReviewID = $assignmentID && peerReviewCompletedOnDate = '0000-00-00 00:00:00'";
    $query1 .= $query;
    $result = mysql_query($query, $con);
  }
}


//close connection
mysql_close($con);

print "resultCode=SENT&result=".$result;

?>
