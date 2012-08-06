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
if (mysql_select_db($db, $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$userID = check_input($_POST['userID']);
$courseID = $_POST['courseID'];

$xmlData = "";

$xmlData .= "<AllAssignments>\n";

$sql =  'SELECT homeworkSetID, homeworkSetName'
	. ' FROM `homeworkSets` '
	. ' WHERE forCourseID='.$courseID;

$result = mysql_query($sql, $con);

$homeworkSetNames = array();
$homeworkSetMaxIndex = 0;

$validHomeworkSetIndices = array();

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $homeworkSetNames[$row['homeworkSetID']] = "" . $row['homeworkSetName'] . "";
  array_push($validHomeworkSetIndices, $row['homeworkSetID']);  
  $homeworkSetMaxIndex = $homeworkSetMaxIndex < $row['homeworkSetID'] ? $row['homeworkSetID'] : $homeworkSetMax;
}


//  for ($homeworkSetIndex = 1; $homeworkSetIndex <= count($homeworkSetNames); $homeworkSetIndex++) {
  for ($vhsi = 0; $vhsi < count($validHomeworkSetIndices); $vhsi++) {
  
  $homeworkSetIndex = $validHomeworkSetIndices[$vhsi];

  $xmlData .= "<homeworkSet>\n";
  $xmlData .= "  <homeworkSetName>" . $homeworkSetNames[$homeworkSetIndex] . "</homeworkSetName>\n";
  $xmlData .= "  <listOfProblems>";
   
  //Return the problems the user is supposed to view and selfExplain
  $sql = 'SELECT selfExplanationID, selfExplanationProblemID, selfExplanationPromptID, selfExplanationDueDate, selfExplanationCompletedOnDate, '
        . ' selfExplanationAnswer1Points, selfExplanationAnswer1PossiblePoints, '
        . ' selfExplanationAnswer2Points, selfExplanationAnswer2PossiblePoints, '
        . ' selfExplanationAnswer3Points, selfExplanationAnswer3PossiblePoints '
        . ' FROM `selfExplanation` '
        . ' WHERE selfExplanationAssignedToStudentID = '.$userID
	. ' AND selfExplanationHomeworkSetID = '.$homeworkSetIndex;

  $result = mysql_query($sql, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

	$pointsEarned = 0;
	$pointsPossible = 0;
	if ($row['selfExplanationCompletedOnDate'] == "0000-00-00 00:00:00") {
	  //do nothing - problem is not completed.
	}
	else if (is_numeric($row['selfExplanationAnswer1Points']) &&
		 is_numeric($row['selfExplanationAnswer2Points']) &&
		 is_numeric($row['selfExplanationAnswer3Points']) &&
		 is_numeric($row['selfExplanationAnswer1PossiblePoints']) &&
		 is_numeric($row['selfExplanationAnswer2PossiblePoints']) &&
		 is_numeric($row['selfExplanationAnswer3PossiblePoints']))
	{
	  $pointsEarned += $row['selfExplanationAnswer1Points'];
	  $pointsEarned += $row['selfExplanationAnswer2Points'];
	  $pointsEarned += $row['selfExplanationAnswer3Points'];

	  $pointsPossible += $row['selfExplanationAnswer1PossiblePoints'];
	  $pointsPossible += $row['selfExplanationAnswer2PossiblePoints'];
	  $pointsPossible += $row['selfExplanationAnswer3PossiblePoints'];
	}
	else {
	  //user is stuck here.
	}

	  $xmlData .= "  <Problem>\n";
	  $xmlData .= "    <type>selfExplanation</type>\n";
	  $xmlData .= "    <recordID>" . $row['selfExplanationID'] . "</recordID>\n";
	  $xmlData .= "    <problemID>" . $row['selfExplanationProblemID'] . "</problemID>\n";

  	  $sqla = 'SELECT problemName from `problems` WHERE problemID = '.$row['selfExplanationProblemID'];
	  $resulta = mysql_query($sqla, $con);
	  while ($rowa = mysql_fetch_array($resulta, MYSQL_ASSOC)) {
	    $xmlData .= "    <problemName>" . $rowa['problemName'] . "</problemName>\n";
	  }

	  $xmlData .= "    <promptID>" . $row['selfExplanationPromptID'] . "</promptID>\n";
	  $xmlData .= "    <dueDate>" . $row['selfExplanationDueDate'] . "</dueDate>\n";
	  $xmlData .= "    <completed>" . $row['selfExplanationCompletedOnDate'] . "</completed>\n";
	  $xmlData .= "    <pointsEarned>" . $pointsEarned . "</pointsEarned>\n";
	  $xmlData .= "    <pointsPossible>" . $pointsPossible . "</pointsPossible>\n";
	  $xmlData .= "  </Problem>\n";
  }

  //Now, return the problems the user is supposed to submit.

  $sql = 'SELECT submissionID, submissionProblemID, submissionDueDate, submissionCompletedOnDate, submissionPoints, submissionPossiblePoints '
          . ' FROM `studentSubmissions` '
          . ' WHERE submissionByUserID = '.$userID
	  . ' AND submissionHomeworkSetID = '.$homeworkSetIndex;


  $result = mysql_query($sql, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

       $pointsPossible = 0;
       $pointsEarned = 0;
	if ($row['submissionCompletedOnDate'] == "0000-00-00 00:00:00") {
	  //do nothing
	}
	else if (is_numeric($row['submissionPoints']) && is_numeric($row['submissionPossiblePoints'])) {
	  $pointsEarned = $row['submissionPoints'];
	  $pointsPossible = $row['submissionPossiblePoints'];
	}
	else {
	  //do nothing.  not valid data.  User will be stuck.
	}

	$xmlData .= "  <Problem>\n";
	$xmlData .= "    <type>studentsubmissions</type>\n";
	$xmlData .= "    <recordID>" . $row['submissionID'] . "</recordID>\n";
	$xmlData .= "    <problemID>" . $row['submissionProblemID'] . "</problemID>\n";
	$sqla = 'SELECT problemName from `problems` WHERE problemID = '.$row['submissionProblemID'];
	$resulta = mysql_query($sqla, $con);
	while ($rowa = mysql_fetch_array($resulta, MYSQL_ASSOC)) {
	  $xmlData .= "    <problemName>" . $rowa['problemName'] . "</problemName>\n";
	}	

	$xmlData .= "    <dueDate>" . $row['submissionDueDate'] . "</dueDate>\n";
	$xmlData .= "    <completed>" . $row['submissionCompletedOnDate'] . "</completed>\n";
	$xmlData .= "    <pointsEarned>" . $pointsEarned . "</pointsEarned>\n";
	$xmlData .= "    <pointsPossible>" . $pointsPossible . "</pointsPossible>\n";
        $xmlData .= "    <peerReviewedBy>\n";

	$baseProblemID = $row['submissionProblemID'];

	$sql2 = 'SELECT peerReviewID, peerReviewCompletedOnDate '
		. ' FROM `peerReview` '
		. ' WHERE peerReviewBaseProblemID = '.$baseProblemID;

	$result2 = mysql_query($sql2, $con);

	while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
	  $xmlData .= "      <peerReviewedProblem>\n";
	  $xmlData .= "        <peerReviewedID>" . $row2['peerReviewID'] . "</peerReviewedID>\n";
	  $xmlData .= "        <completedOnDate>" . $row2['peerReviewCompletedOnDate'] . "</completedOnDate>\n";
	  $xmlData .= "      </peerReviewedProblem>\n";
	}

        $xmlData .= "    </peerReviewedBy>\n";
	$xmlData .= "  </Problem>\n";
  }


  //Return the problems the user is supposed to peer review
  $sql = 'SELECT peerReviewID, peerReviewBaseProblemID, peerReviewPromptID, peerReviewDueDate, peerReviewCompletedOnDate, '
        . ' peerReviewAnswer1Points, peerReviewAnswer1PossiblePoints, '
        . ' peerReviewAnswer2Points, peerReviewAnswer2PossiblePoints, '
        . ' peerReviewAnswer3Points, peerReviewAnswer3PossiblePoints '

        . ' FROM `peerReview` '
        . ' WHERE peerReviewByUserID = '.$userID
	. ' AND peerReviewHomeworkSetID = '.$homeworkSetIndex;

  $result = mysql_query($sql, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

       $pointsEarned = 0;
       $pointsPossible = 0;
	if ($row['peerReviewCompletedOnDate'] == "0000-00-00 00:00:00") {
	  //do nothing - problem is not completed.
	}
	else if (is_numeric($row['peerReviewAnswer1Points']) &&
		 is_numeric($row['peerReviewAnswer2Points']) &&
		 is_numeric($row['peerReviewAnswer3Points']) &&
		 is_numeric($row['peerReviewAnswer1PossiblePoints']) &&
		 is_numeric($row['peerReviewAnswer2PossiblePoints']) &&
		 is_numeric($row['peerReviewAnswer3PossiblePoints']))
	{
	  $pointsEarned += $row['peerReviewAnswer1Points'];
	  $pointsEarned += $row['peerReviewAnswer2Points'];
	  $pointsEarned += $row['peerReviewAnswer3Points'];

	  $pointsPossible += $row['peerReviewAnswer1PossiblePoints'];
	  $pointsPossible += $row['peerReviewAnswer2PossiblePoints'];
	  $pointsPossible += $row['peerReviewAnswer3PossiblePoints'];
	}
	else {
	  //user is stuck here.
	}

	$xmlData .= "  <Problem>\n";
	$xmlData .= "    <type>peerReview</type>\n";
	$xmlData .= "    <recordID>" . $row['peerReviewID'] . "</recordID>\n";
	$xmlData .= "    <problemID>" . $row['peerReviewBaseProblemID'] . "</problemID>\n";
	$xmlData .= "    <promptID>" . $row['peerReviewPromptID'] . "</promptID>\n";

	$sqla = 'SELECT problemName from `problems` WHERE problemID = '.$row['peerReviewBaseProblemID'];
	$resulta = mysql_query($sqla, $con);
	while ($rowa = mysql_fetch_array($resulta, MYSQL_ASSOC)) {
	  $xmlData .= "    <problemName>" . $rowa['problemName'] . "</problemName>\n";
	}

	$xmlData .= "    <dueDate>" . $row['peerReviewDueDate'] . "</dueDate>\n";
	$xmlData .= "    <completed>" . $row['peerReviewCompletedOnDate'] . "</completed>\n";
	$xmlData .= "    <pointsEarned>" . $pointsEarned . "</pointsEarned>\n";
	$xmlData .= "    <pointsPossible>" . $pointsPossible . "</pointsPossible>\n";

	//Return whether the studentSubmission problem has been completed by date:
	$ss_sql = 'SELECT submissionCompletedOnDate'
		. ' FROM `studentSubmissions` '
		. ' WHERE submissionProblemID = '.$row['peerReviewBaseProblemID'];
	$ss_result = mysql_query($ss_sql, $con);
	while($ss_row = mysql_fetch_array($ss_result, MYSQL_ASSOC)) {
	  $xmlData .= "    <submissionCompletedOnDate>" . $ss_row['submissionCompletedOnDate'] . "</submissionCompletedOnDate>\n";	
	}
	$xmlData .= "  </Problem>\n";
  }

  $xmlData .= "  </listOfProblems>";
  $xmlData .= "  </homeworkSet>\n";
}

$xmlData .= "</AllAssignments>\n";

print $xmlData;

?>
