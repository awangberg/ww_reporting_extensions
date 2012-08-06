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

$submittedBy = check_input($_POST['submittedBy']);
$table = $_POST['problemType'];
$assignmentID = check_input($_POST['assignmentID']);
$addTimeToField = $_POST['addTimeToField'];
$amountOfTime = check_input($_POST['amountOfTime']);
$addVisitToField = $_POST['addOneToVisitField'];

$query1 = "";

if ($table == "selfexplanation") {
  $query = "UPDATE selfExplanation "
	 . "SET $addTimeToField = $addTimeToField + $amountOfTime "
	 . "WHERE selfExplanationID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE selfExplanation "
	 . "SET $addVisitToField = $addVisitToField + 1 "
	 . "WHERE selfExplanationID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //(NEW CHANGE:)
  //make sure the visit is recorded on this date as completed,
  //if it is not recorded as completed yet.
  $todaysDate = date ("Y-m-d H:m:s");
  $query = "UPDATE selfExplanation "
	 . "SET selfExplanationCompletedOnDate = '$todaysDate' "
	 . "WHERE selfExplanationID = $assignmentID "
	 . "&& selfExplanationCompletedOnDate = '0000-00-00 00:00:00'";
  $query1 .= $query;
  $result = mysql_query($query, $con);

}

else if ($table == "studentsubmission") {
  $query = "UPDATE studentSubmissions "
	 . "SET $addTimeToField = $addTimeToField + $amountOfTime "
	 . "WHERE submissionID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE studentSubmissions "
	 . "SET $addVisitToField = $addVisitToField + 1 "
	 . "WHERE submissionID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);
}
else if ($table == "peerreview") {
  $query = "UPDATE peerReview "
	 . "SET $addTimeToField = $addTimeToField + $amountOfTime "
	 . "WHERE peerReviewID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  $query = "UPDATE peerReview "
	 . "SET $addVisitToField = $addVisitToField + 1 "
	 . "WHERE peerReviewID = $assignmentID";
  $query1 .= $query;
  $result = mysql_query($query, $con);

  //(NEW CHANGE:)
  //make sure the visit is recorded on this date as completed,
  //if it is not recorded as completed yet.
  $todaysDate = date ("Y-m-d H:m:s");
  $query = "UPDATE peerReview "
	 . "SET peerReviewCompletedOnDate = '$todaysDate' "
	 . "WHERE peerReviewID = $assignmentID "
	 . "&& peerReviewCompletedOnDate = '0000-00-00 00:00:00'";
  $query1 .= $query;
  $result = mysql_query($query, $con);

}
else {
  $query1 = "table did not match.  table = |$table|";
  $result = "";
}


//close connection
mysql_close($con);

print "resultCode=SENT&result=".$result;

?>
