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

header("content-type: text/xml");

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

$assignmentID = check_input($_POST['assignmentID']);
$problemType = check_input($_POST['problemType']);

if ($problemType == "'selfexplanation'") {
  //get the data for the problem from the selfexplanation table:


  $answer1 = "";
  $answer1Points = "";
  $answer1PossiblePoints = "";
  $answer2 = "";
  $answer2Points = "";
  $answer2PossiblePoints = "";
  $answer3 = "";
  $answer3Points = "";
  $answer3PossiblePoints = "";
  $selfExplanationCompletedOnDate = "";

  $query = 'SELECT selfExplanationProblemID, selfExplanationPromptID , selfExplanationCompletedOnDate, '
	   .' selfExplanationAnswer1, selfExplanationAnswer1Points, selfExplanationAnswer1PossiblePoints, '
	   .' selfExplanationAnswer2, selfExplanationAnswer2Points, selfExplanationAnswer2PossiblePoints, '
	   .' selfExplanationAnswer3, selfExplanationAnswer3Points, selfExplanationAnswer3PossiblePoints '
	   .' FROM `selfExplanation` '
           . ' WHERE selfExplanationID = '.$assignmentID;
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemID = $row['selfExplanationProblemID'];
    $promptID = $row['selfExplanationPromptID'];
    $answer1 = $row['selfExplanationAnswer1'];
    $answer1Points = $row['selfExplanationAnswer1Points'];
    $answer1PossiblePoints = $row['selfExplanationAnswer1PossiblePoints'];
    $answer2 = $row['selfExplanationAnswer2'];
    $answer2Points = $row['selfExplanationAnswer2Points'];
    $answer2PossiblePoints = $row['selfExplanationAnswer2PossiblePoints'];
    $answer3 = $row['selfExplanationAnswer3'];
    $answer3Points = $row['selfExplanationAnswer3Points'];
    $answer3PossiblePoints = $row['selfExplanationAnswer3PossiblePoints'];
    $selfExplanationCompletedOnDate = $row['selfExplanationCompletedOnDate'];
  }

  //get the problem data from the problems table:
  $query = 'SELECT problemName, drawData '
	   .' FROM `problems` '
           . ' WHERE problemID = '.$problemID;
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemName = $row['problemName'];
    $drawData = $row['drawData'];
  }

  //get the prompt data from the prompts table:  
  $query = 'SELECT promptName, promptData '
	   .' FROM `prompts` '
           . ' WHERE promptID = '.$promptID;
  $result = mysql_query($query, $con);
  $promptName = "";
  $promptData = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $promptName = $row['promptName'];
    $promptData = $row['promptData'];
  }

  $xmlData = "";
  $xmlData .= "<problem>\n";
  $xmlData .= "  <problemName>$problemName</problemName>\n";
  $xmlData .= "  <drawData>$drawData</drawData>\n";
  $xmlData .= "  <promptData>$promptData</promptData>\n";
  $xmlData .= "  <problemType>$problemType</problemType>\n";
  $xmlData .= "  <problemID>$problemID</problemID>\n";
  $xmlData .= "  <promptID>$promptID</promptID>\n";
  $xmlData .= "  <assignmentID>$assignmentID</assignmentID>\n";
  $xmlData .= "  <answer1>$answer1</answer1>\n";
  $xmlData .= "  <answer1Points>$answer1Points</answer1Points>\n";
  $xmlData .= "  <answer1PossiblePoints>$answer1PossiblePoints</answer1PossiblePoints>\n";
  $xmlData .= "  <answer2>$answer2</answer2>\n";
  $xmlData .= "  <answer2Points>$answer2Points</answer2Points>\n";
  $xmlData .= "  <answer2PossiblePoints>$answer2PossiblePoints</answer2PossiblePoints>\n";
  $xmlData .= "  <answer3>$answer3</answer3>\n";
  $xmlData .= "  <answer3Points>$answer3Points</answer3Points>\n";
  $xmlData .= "  <answer3PossiblePoints>$answer3PossiblePoints</answer3PossiblePoints>\n";
  $xmlData .= "  <completedOnDate>$selfExplanationCompletedOnDate</completedOnDate>\n";
  $xmlData .= "</problem>\n";
}
else if ($problemType == "'studentsubmission'") {
  //get the data for the problem from the studentsubmission table:
  $query = 'SELECT submissionProblemID, submissionPossiblePoints, submissionCompletedOnDate '
	   .' FROM `studentSubmissions` '
           . ' WHERE submissionID = '.$assignmentID;
  $result = mysql_query($query, $con);
  $promblemID = "";
  $submissionPossiblePoints = "";
  $submissionCompletedOnDate = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemID = $row['submissionProblemID'];
    $submissionPossiblePoints = $row['submissionPossiblePoints'];
    $submissionCompletedOnDate = $row['submissionCompletedOnDate'];
  }

  //get the problem data from the problems table:
  $query = 'SELECT problemName, drawData '
	   .' FROM `problems` '
           . ' WHERE problemID = '.$problemID;
  $result = mysql_query($query, $con);
  $problemName = "";
  $drawData = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemName = $row['problemName'];
    $drawData = $row['drawData'];
  }

  $xmlData = "";
  $xmlData .= "<problem>\n";
  $xmlData .= "  <problemName>$problemName</problemName>\n";
  $xmlData .= "  <drawData>$drawData</drawData>\n";
  $xmlData .= "  <problemType>$problemType</problemType>\n";
  $xmlData .= "  <problemID>$problemID</problemID>\n";
  $xmlData .= "  <assignmentID>$assignmentID</assignmentID>\n";
  $xmlData .= "  <submissionPossiblePoints>$submissionPossiblePoints</submissionPossiblePoints>\n";
  $xmlData .= "  <completedOnDate>$submissionCompletedOnDate</completedOnDate>\n";
  $xmlData .= "</problem>\n";
}
else if ($problemType == "'peerreview'") {
  //get the data for the problem from the peerreview table:
  $query = 'SELECT peerReviewBaseProblemID, peerReviewPromptID, peerReviewCompletedOnDate, '
	   .' peerReviewAnswer1, peerReviewAnswer1Points, peerReviewAnswer1PossiblePoints, '
	   .' peerReviewAnswer2, peerReviewAnswer2Points, peerReviewAnswer2PossiblePoints, '
	   .' peerReviewAnswer3, peerReviewAnswer3Points, peerReviewAnswer3PossiblePoints '
	   .' FROM `peerReview` '
           . ' WHERE peerReviewID = '.$assignmentID;

  $result = mysql_query($query, $con);
  $problemID = "";
  $promptID = "";
  $peerReviewCompletedOnDate = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemID = $row['peerReviewBaseProblemID'];
    $promptID = $row['peerReviewPromptID'];
    $answer1 = $row['peerReviewAnswer1'];
    $answer1Points = $row['peerReviewAnswer1Points'];
    $answer1PossiblePoints = $row['peerReviewAnswer1PossiblePoints'];
    $answer2 = $row['peerReviewAnswer2'];
    $answer2Points = $row['peerReviewAnswer2Points'];
    $answer2PossiblePoints = $row['peerReviewAnswer2PossiblePoints'];
    $answer3 = $row['peerReviewAnswer3'];
    $answer3Points = $row['peerReviewAnswer3Points'];
    $answer3PossiblePoints = $row['peerReviewAnswer3PossiblePoints'];
    $peerReviewCompletedOnDate = $row['peerReviewCompletedOnDate'];
  }

  //get the problem data from the problems table:
  $query = 'SELECT problemName, drawData '
	   .' FROM `problems` '
           . ' WHERE problemID = '.$problemID;
  $result = mysql_query($query, $con);
  $problemName = "";
  $drawData = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemName = $row['problemName'];
    $drawData = $row['drawData'];
  }

  //get the prompt data from the prompts table:  
  $query = 'SELECT promptName, promptData '
	   .' FROM `prompts` '
           . ' WHERE promptID = '.$promptID;
  $result = mysql_query($query, $con);
  $promptName = "";
  $promptData = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $promptName = $row['promptName'];
    $promptData = $row['promptData'];
  }

  $xmlData = "";
  $xmlData .= "<problem>\n";
  $xmlData .= "  <problemName>$problemName</problemName>\n";
  $xmlData .= "  <drawData>$drawData</drawData>\n";
  $xmlData .= "  <promptData>$promptData</promptData>\n";
  $xmlData .= "  <problemType>$problemType</problemType>\n";
  $xmlData .= "  <problemID>$problemID</problemID>\n";
  $xmlData .= "  <promptID>$promptID</promptID>\n";
  $xmlData .= "  <assignmentID>$assignmentID</assignmentID>\n";
  $xmlData .= "  <answer1>$answer1</answer1>\n";
  $xmlData .= "  <answer1Points>$answer1Points</answer1Points>\n";
  $xmlData .= "  <answer1PossiblePoints>$answer1PossiblePoints</answer1PossiblePoints>\n";
  $xmlData .= "  <answer2>$answer2</answer2>\n";
  $xmlData .= "  <answer2Points>$answer2Points</answer2Points>\n";
  $xmlData .= "  <answer2PossiblePoints>$answer2PossiblePoints</answer2PossiblePoints>\n";
  $xmlData .= "  <answer3>$answer3</answer3>\n";
  $xmlData .= "  <answer3Points>$answer3Points</answer3Points>\n";
  $xmlData .= "  <answer3PossiblePoints>$answer3PossiblePoints</answer3PossiblePoints>\n";
  $xmlData .= "  <completedOnDate>$peerReviewCompletedOnDate</completedOnDate>\n";
  $xmlData .= "</problem>\n";
}

print $xmlData;

?>


