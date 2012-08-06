<?php

include("access.php");

function formatTime($milliseconds) {
  $seconds = round($milliseconds/1000, 0);
  $origseconds = round($milliseconds/1000, 0);
  $hours = floor($seconds/3600);
  $seconds = $seconds - $hours*3600;
  $minutes = floor($seconds/60);
  $seconds = $seconds - $minutes*60;
  if ($hours == 0) { $hours = "00"; }
  else if ($hours < 10) { $hours = "0" . $hours; }
  if ($minutes == 0) { $minutes = "00"; }
  else if ($minutes < 10) { $minutes = "0" . $minutes; }
  if ($seconds == 0) { $seconds = "00"; }
  else if ($seconds < 10) { $seconds = "0" . $seconds; }
  return "$hours:$minutes:$seconds";
}

function retrieveProblemImagePromptsAndAnswers($problemID, $promptID, $con, $completed, $totalStudents, $avgCompletionTime, $postVisit, $avgPostCompletionTime) {

  $query = 'SELECT problemName, submittedBy '
	  .' FROM `problems` '
	  .' WHERE problemID='.$problemID;
  $result = mysql_query($query, $con);
  $problemName = "Problem: ";
  $submittedByUser;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problemName .= $row['problemName'];
    $submittedByUser = $row['submittedBy'];
  }

  $query =  'SELECT userID, userName '
	   .' FROM `users` '
	   .' WHERE userID='.$submittedByUser;

  $result = mysql_query($query, $con);

  $userName;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $userName = $row['userName'];
  }

  $query = 'SELECT promptName, promptData'
	  .' FROM `prompts` '
	  .' WHERE promptID='.$promptID;
  $result = mysql_query($query, $con);
  $promptData = "Prompt Data: ";
  $promptName = "Prompt Name: ";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $promptData = $row['promptData'];
    $promptName .= $row['promptName'];
  }

  $tmpPromptData = explode(";~;", $promptData);

  $question = array();

  for ($j = 0; $j < count($tmpPromptData)-1; $j++) {
    $tmpPromptParts = explode(";_;", $tmpPromptData[$j]);
    $question[$j] = $tmpPromptParts[3];
    $questionType[$j] = $tmpPromptParts[0];
    $questionTime[$j] = $tmpPromptParts[1];

    if ($tmpPromptParts[0] == "TF") {
      $tmpQuestionParts = explode(";^;", $tmpPromptParts[4]);
      $tmpQuestionPartsA = explode(";#;", $tmpQuestionParts[0]);
      $tmpQuestionPartsB = explode(";#;", $tmpQuestionParts[1]);
      $questionAnswer[$j] = "";
      $questionReason[$j] = "";
    }
    else if ($tmpPromptParts[0] == "RadioButton") {
      $tmpQuestionParts = explode(";^;", $tmpPromptParts[4]);
      $tmpQuestionPartsA = explode(";#;", $tmpQuestionParts[0]);
      $tmpQuestionPartsB = explode(";#;", $tmpQuestionParts[1]);
      $tmpQuestionPartsC = explode(";#;", $tmpQuestionParts[2]);
      $tmpQuestionPartsD = explode(";#;", $tmpQuestionParts[3]);
      $questionAnswer[$j] = "";
      $questionReason[$j] = "";
    }
    else if ($tmpPromptParts[0] == "TextField") {
      $questionAnswer[$j] = $tmpPromptParts[6];
      $questionReason[$j] = $tmpPromptParts[7];
    }
  }

  print "problemID: $problemID promptID: $promptID<BR>";

	$studentList = "
<BR><HR><BR>
Problem Submitted by $userName<BR>
<TABLE BORDER=1>
<TR>
<TD COLSPAN=5>
<CENTER><a href=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=4\" TARGET=\"IMG\"><img src=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=4\" width=250></a></CENTER>
</TD>
<TD COLSPAN=2>
<CENTER><a href=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=1\" TARGET=\"IMG\"><img src=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=1\" width=250></a></CENTER>
</TD>
<TD COLSPAN=2>
<CENTER><a href=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=2\" TARGET=\"IMG\"><img src=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=2\" width=250></a></CENTER>
</TD>
<TD COLSPAN=2>
<CENTER><a href=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=3\" TARGET=\"IMG\"><img src=\"createImage.php?problemID=$problemID&promptID=$promptID&segment=3\" width=250></a></CENTER>
</TD>
</TR>
<TR>
<TD WIDTH=250 COLSPAN=5>
Final Image
</TD>
<TD WIDTH=250 COLSPAN=2>
$questionType[0]: $question[0]
</TD>
<TD WIDTH=250 COLSPAN=2>
$questionType[1]: $question[1]
</TD>
<TD WIDTH=250 COLSPAN=2>
$questionType[2]: $question[2]
</TD>
</TR>
<TR><TD COLSPAN=11><HR></TD></TR>
";
//</TABLE>
//<P>
//<HR>
//<P>
//$studentList .= "<TABLE BORDER=1>";
  $studentList .=      "<TR>
			<TD></TD>
			<TD></TD>
			<TD></TD>
			<TD></TD>
			<TD></TD>
			<TD>SE1 Answer</TD>
			<TD>SE1 Points</TD>
			<TD>SE2 Answer</TD>
			<TD>SE2 Points</TD>
			<TD>SE3 Answer</TD>
			<TD>SE3 Points</TD>
			</TR>\n";

  $studentList .=      "<TR>
			<TD></TD>
		   	<TD>Did Tutorial</TD>
		   	<TD>Time to Complete</TD>
		   	<TD>Visits after Complete</TD>
		   	<TD>Time After Complete</TD>
			<TD>Q: " . $question[0] . "</TD>
			<TD>" . $questionType[0]. "</TD>
			<TD>Q: " . $question[1] . "</TD>
			<TD>" . $questionType[1]. "</TD>
			<TD>Q: " . $question[2] . "</TD>
			<TD>" . $questionType[2]. "</TD>
			</TR>\n";


  $studentList .=      "<TR>
	  	   	<TD>User Name</TD>
			<TD VALIGN='TOP'>Total: $completed/$totalStudents</TD>
			<TD VALIGN='TOP'>Avg Time: " . formatTime($avgCompletionTime) . "</TD>
			<TD VALIGN='TOP'>Total: $postVisit/$totalStudents</TD>
			<TD VALIGN='TOP'>Avg Time: " . formatTime($avgPostCompletionTime) . "</TD>
			<TD VALIGN='TOP'><B>Correct Answer:</B> <BR> " . $questionAnswer[0] . "<BR><B>Correct Reason:</B> <BR> " . $questionReason[0] . "</TD>
			<TD></TD>
			<TD VALIGN='TOP'><B>Correct Answer:</B> <BR> " . $questionAnswer[1] . "<BR><B>Correct Reason:</B> <BR> " . $questionReason[1] . "</TD>
			<TD></TD>
			<TD VALIGN='TOP'><B>Correct Answer:</B> <BR> " . $questionAnswer[2] . "<BR><B>Correct Reason:</B> <BR> " . $questionReason[2] . "</TD>
			<TD></TD>
			</TR>\n";

  print $studentList;
  print "<BR>\n";
  print $problemName;
  print "<BR>$promptName<BR>\n";
}

function retrieveProblemImage($problemID, $con) {

$query = 'SELECT problemName, submittedBy '
	.' FROM `problems` '
	.' WHERE problemID='.$problemID;
$result = mysql_query($query, $con);
$problemName = "Problem: ";
$submittedByUser;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $problemName .= $row['problemName'];
  $submittedByUser = $row['submittedBy'];
}

$query =  'SELECT userID, userName '
	.' FROM `users` '
	.' WHERE userID='.$submittedByUser;

$result = mysql_query($query, $con);

$userName;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $userName = $row['userName'];
}

print "problemID: $problemID promptID: ''<BR>";

	$studentList = "
<BR><HR><BR>
Problem Submitted by $userName<BR>
<TABLE BORDER=1>
<TR>
<TD COLSPAN=2>
<CENTER><a href=\"createImage.php?problemID=$problemID&promptID=1&segment=4\" TARGET=\"IMG\"><img src=\"createImage.php?problemID=$problemID&promptID=1&segment=4\" width=250></a></CENTER>
</TD>
</TR>
<TR>
<TD WIDTH=250 COLSPAN=2>
Final Image
</TD>
</TR>
<TR><TD COLSPAN=8><HR></TD></TR>
";
	print $studentList;
	print "<BR>\n";
	print $problemName;

}


$db = "goteam";

if (isset($_REQUEST['Course']) && isset($_REQUEST['Assignment']) && isset($_REQUEST['SEProblemIDAndPromptID'])) {
// INSERT DATA FROM FORM ONCE THE FORM HAS BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$courseID = $_REQUEST['Course'];
$Assignment = $_REQUEST['Assignment'];
$SEproblemIDAndPromptID = $_REQUEST['SEProblemIDAndPromptID'];
$PRproblemIDs = $_REQUEST['PRproblemIDs'];
$SSproblemIDs = $_REQUEST['SSproblemIDs'];



$query =  'SELECT userID, userName '
	.' FROM `users` '
	.' WHERE 1';

$result = mysql_query($query, $con);

$usersArray = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $usersArray[$row['userID']] = $row['userName'];
}

if ($SEproblemIDAndPromptID != "") {
	$tmp_pIDAndPID = explode(" and ", $SEproblemIDAndPromptID);
	$problemID = $tmp_pIDAndPID[0];
	$promptID = $tmp_pIDAndPID[1];
}
if ($PRproblemIDs != "") {
	$tmp_pIDAndPID = explode(",", $PRproblemIDs);
	$promptID = $tmp_pIDAndPID[0];
	$problemID = $tmp_pIDAndPID[1];
}
if ($SSproblemIDs != "") {
	$tmp_pIDAndPID = explode(",", $SSproblemIDs);
	$promptID = $tmp_pIDAndPID[0];
	$problemID = $tmp_pIDAndPID[1];
}


	if ($SEproblemIDAndPromptID != "") {
		$query =  'SELECT selfExplanationAnswer1, selfExplanationAnswer1Points, selfExplanationAnswer1PossiblePoints, '
			. 'selfExplanationAnswer2, selfExplanationAnswer2Points, selfExplanationAnswer2PossiblePoints, '
			. 'selfExplanationAnswer3, selfExplanationAnswer3Points, selfExplanationAnswer3PossiblePoints, '
			. 'selfExplanationAssignedToStudentID, selfExplanationID, selfExplanationToCompleteTime, selfExplanationToCompleteVisits, selfExplanationAfterCompletedTime, selfExplanationAfterCompletedVisits'
			.' FROM `selfExplanation` '
			.' WHERE selfExplanationProblemID=' . $problemID 
			.' AND selfExplanationPromptID=' . $promptID;
		$result = mysql_query($query, $con);


		$totalRecords = 0;
		$completedCount = 0;
		$totalCompletionTime = 0;
		$totalCompletionTimeCount = 0;
		$postVisitCount = 0;
		$totalPostCompletionTime = 0;
		$totalPostCompletionTimeCount = 0;
		$studentList = "";
		$formatted_row_data = array();
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		  $userID = $row['selfExplanationAssignedToStudentID'];
		  $userName = $usersArray[$userID];
		  //count of people completing tutorial:
		  $completedCount = $row['selfExplanationToCompleteVisits'] > 0 ? $completedCount + 1 : $completedCount;
		  //count of people with a valid tutorial time:
		  $totalCompletionTimeCount = $row['selfExplanationToCompleteTime'] > 0 ? $totalCompletionTimeCount + 1 : $totalCompletionTimeCount;
		  //cumulative time of people with a valid tutorial time:
		  $totalCompletionTime = $totalCompletionTime + $row['selfExplanationToCompleteTime'];
		  //count of poeple who visited tutorial after finishing it:
		  $postVisitCount = $row['selfExplanationAfterCompletedVisits'] > 0 ? $postVisitCount + 1 : $postVisitCount;
		  //count of people with a valid post-completion time:
		  $totalPostCompletionTimeCount = $row['selfExplanationAfterCompletedTime'] > 0 ? $totalPostCompletionTimeCount + 1 : $totalPostCompletionTimeCount;
		  //cumulative time of people with a valid post-completion time
		  $totalPostCompletionTime = $totalPostCompletionTime + $row['selfExplanationAfterCompletedTime'];		  		  

		  $totalRecords++;
		  //$studentList .= "<TR><TD>"
		  $formatted_row_data[$userName] = 
				"<TR><TD>"
				. "user" . $totalRecords . "@winona.edu " //$userName
				. "@winona.edu"
				. "</TD><TD>"
				. $row['selfExplanationToCompleteVisits']
				. "</TD><TD> "
				. formatTime($row['selfExplanationToCompleteTime'])
				. "</TD><TD> "
				. $row['selfExplanationAfterCompletedVisits']
				. "</TD><TD> "
				. formatTime($row['selfExplanationAfterCompletedTime'])
				. "</TD><TD> "
				. $row['selfExplanationAnswer1']
				. "</TD><TD> "
				. $row['selfExplanationAnswer1Points']
				. "/"
				. $row['selfExplanationAnswer1PossiblePoints']
				. "</TD><TD>"
				. $row['selfExplanationAnswer2']
				. "</TD><TD> "
				. $row['selfExplanationAnswer2Points']
				. "/"
				. $row['selfExplanationAnswer2PossiblePoints']
				. "</TD><TD> "
				. $row['selfExplanationAnswer3']
				. "</TD><TD>"
				. $row['selfExplanationAnswer3Points']
				. "/"
				. $row['selfExplanationAnswer3PossiblePoints']
				. "</TD>"
				. "</TR>\n";
		}

		//$studentList .= "</TABLE>\n";
		//print $studentList;

		$totalCompletionTimeCount = $totalCompletionTimeCount == 0 ? 1 : $totalCompletionTimeCount;
		$totalPostCompletionTimeCount = $totalPostCompletionTimeCount == 0 ? 1 : $totalPostCompletionTimeCount;

		retrieveProblemImagePromptsAndAnswers($problemID, $promptID, $con, $completedCount, $totalRecords, $totalCompletionTime/$totalCompletionTimeCount, $postVisitCount, $totalPostCompletionTime/$totalPostCompletionTimeCount);

		//print "<P>$query</P>";
		//print "<BR> Completed: " . $completedCount . "/" . $totalRecords;

		ksort($formatted_row_data);
		foreach ($formatted_row_data as $key => $value) {
		  print $value;
		}	
		print "</TABLE>\n";


	}
////////////////
	else if ($PRproblemIDs != "") {
	  $PRproblemID = explode(",", $PRproblemIDs);

	  for ($j = 1; $j < count($PRproblemID)-1; $j++) {
	     $baseProblemID = $PRproblemID[$j];

		$query =  'SELECT peerReviewAnswer1, peerReviewAnswer1Points, peerReviewAnswer1PossiblePoints, '
			. 'peerReviewAnswer2, peerReviewAnswer2Points, peerReviewAnswer2PossiblePoints, '
			. 'peerReviewAnswer3, peerReviewAnswer3Points, peerReviewAnswer3PossiblePoints, '
			. 'peerReviewByUserID, peerReviewID, peerReviewToCompleteTimeByReviewer, '
			. 'peerReviewToCompleteTimeByReviewer, peerReviewToCompleteVisitsByReviewer, peerReviewAfterCompletedTimeByReviewer, peerReviewAfterCompletedVisitsByReviewer, peerReviewAfterCompletedTimeByProblemAuthor, peerReviewAfterCompletedVisitsByProblemAuthor '
			.' FROM `peerReview` '
			.' WHERE peerReviewBaseProblemID=' . $baseProblemID;
			//.' AND peerReviewPromptID=' . $promptID;


		$result = mysql_query($query, $con);

		$totalRecords = 0;
		$viewCompletedCount = 0;
		$totalViewCompletionTimeCount = 0;
		$totalViewCompletionTime = 0;
		$totalPostViewCompletionCount = 0;
		$totalPostViewCompletionTimeCount = 0;
		$totalPostViewCompletionTime = 0;

		$studentList = "";
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		  $userID = $row['peerReviewByUserID'];
		  $userName = $usersArray[$userID];
		  //count of people reviewing this student's work
		  $viewCompletedCount = $row['peerReviewToCompleteVisitsByReviewer'] > 0 ? $viewCompletedCount + 1 : $viewCompletedCount;
		  //count of people with a valid review time:
		  $totalViewCompletionTimeCount  = $row['peerReviewToCompleteTimeByReviewer'] > 0 ? $totalViewCompletionTimeCount + 1 : $totalViewCompletionTimeCount;
		  //cumulative time of people with a valid review time:
		  $totalViewCompletionTime  = $totalViewCompletionTime + $row['peerReviewToCompleteTimeByReviewer'];
		  //count of reviewers who returned to problem after finishing it:
		  $totalPostViewCompletionCount = $row['peerReviewAfterCompletedVisitsByReviewer'] > 0 ? $totalPostViewCompletionCount + 1 : $totalPostViewCompletionCount;
		  //count of reviewers with a valid post-viewed time:
		  $totalPostViewCompletionTimeCount = $row['peerReviewAfterCompletedTimeByReviewer'] > 0 ? $totalPostViewCompletionTimeCount + 1 : $totalPostViewCompletionTimeCount;
		  //cumulative time of reviewers with a valid post-viewed time
		  $totalPostViewCompletionTime = $totalPostViewCompletionTime + $row['peerReviewAfterCompletedTimeByReviewer'];

		  $totalRecords++;
		  $studentList .= "<TR><TD>"
				. $userName
				. "@winona.edu"
				. "</TD><TD>"
				. $row['peerReviewToCompleteVisitsByReviewer']
				. "</TD><TD>"
				. formatTime($row['peerReviewToCompleteTimeByReviewer'])
				. "</TD><TD>"
				. $row['peerReviewAfterCompletedVisitsByReviewer']
				. "</TD><TD>"
				. formatTime($row['peerReviewAfterCompletedTimeByReviewer'])
				. "</TD><TD> "
				. $row['peerReviewAnswer1']
				. "</TD><TD> "
				. $row['peerReviewAnswer1Points']
				. "/"
				. $row['peerReviewAnswer1PossiblePoints']
				. "</TD><TD>"
				. $row['peerReviewAnswer2']
				. "</TD><TD> "
				. $row['peerReviewAnswer2Points']
				. "/"
				. $row['peerReviewAnswer2PossiblePoints']
				. "</TD><TD> "
				. $row['peerReviewAnswer3']
				. "</TD><TD>"
				. $row['peerReviewAnswer3Points']
				. "/"
				. $row['peerReviewAnswer3PossiblePoints']
				. "</TD>"
				. "</TR>\n";
		}
	
		$studentList .= "</TABLE>\n";
	
		$totalViewCompletionTimeCount = $totalViewCompletionTimeCount == 0 ? 1 : $totalViewCompletionTimeCount;

		$totalPostViewCompletionTimeCount = $totalPostViewCompletionTimeCount == 0 ? 1 : $totalPostViewCompletionTimeCount;

		retrieveProblemImagePromptsAndAnswers($baseProblemID, $promptID, $con, $viewCompletedCount, $totalRecords, $totalViewCompletionTime/$totalViewCompletionTimeCount, $totalPostViewCompletionCount, $totalPostViewCompletionTime/$totalPostViewCompletionTimeCount);

		//print "<BR> Completed: " . $completedCount . "/" . $totalRecords;
	
		print $studentList;

           }	
	}
///////////////////////
	else if ($SSproblemIDs != "") {
	  $SSproblemID = explode(",", $SSproblemIDs);

	  for ($j = 1; $j < count($SSproblemID)-1; $j++) {
	     $ProblemID = $SSproblemID[$j];

             retrieveProblemImage($ProblemID, $con);

	     //print $studentList;

           }	
	}
///////////////////


	//close connection
	mysql_close($con);
}


elseif (isset($_REQUEST['Course']) && isset($_REQUEST['Assignment'])) {

	// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

	$con = mysql_connect($db_host, $db_user, $db_pass);

	if(!$con) {
	  die('Could not connect: ' . mysql_error());
	}

	//select the database '$db'
	$res = mysql_select_db("$db", $con);
	$courseID = $_REQUEST['Course'];
	$homeworkSetID = $_REQUEST['Assignment'];


	$query = 'SELECT problemID, problemName'
		.' FROM `problems` '
		.' WHERE 1';
	$result = mysql_query($query, $con);
	$problemArray = array();
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $problemArray[$row['problemID']] = $row['problemName'];
	}


	$query =   'SELECT selfExplanationProblemID, selfExplanationPromptID'
		  .' FROM `selfExplanation` '
		  .' WHERE selfExplanationHomeworkSetID="'.$homeworkSetID. '"';

	$result = mysql_query($query, $con);

	$SEproblemIDAndPromptID = "";
	$SEproblemIDAndPromptIDArray = array();	
	$SEproblemIDAndPromptID   .= '<option value=""></option.\n';

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  if (in_array($row['selfExplanationProblemID'] . " and " . $row['selfExplanationPromptID'], $SEproblemIDAndPromptIDArray)) {
		// do nothing
	  }
	  else {
	    array_push($SEproblemIDAndPromptIDArray, $row['selfExplanationProblemID'] . " and " . $row['selfExplanationPromptID']);
	  $SEproblemIDAndPromptID   .= '<option value="' . $row['selfExplanationProblemID'] . ' and ' . $row['selfExplanationPromptID'] . '">'
		  		  .  $problemArray[$row['selfExplanationProblemID']] 
				  //. ' and '
			 	  //.  $row['selfExplanationPromptID']
			 	  . '</option>\n';
	  }
	}
////////////////////

	$query =   'SELECT peerReviewID, peerReviewBaseProblemID, peerReviewPromptID '
		  .' FROM `peerReview` '
		  .' WHERE peerReviewHomeworkSetID="'.$homeworkSetID. '"';

	$result = mysql_query($query, $con);

	$PRproblemIDs = "";

	$PRproblemIDs   .= '<option value=""></option.\n';

	$PRSimilarProblems = array();

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $peerReviewBaseProblemID = $row['peerReviewBaseProblemID'];
	  $peerReviewPromptID = $row['peerReviewPromptID'];
	  $problemName = $problemArray[$peerReviewBaseProblemID];

	  if (array_key_exists($problemName, $PRSimilarProblems)) {
	    if (in_array($peerReviewBaseProblemID, $PRSimilarProblems[$problemName])) {
		//do nothing
	    }
	    else {
	      array_push($PRSimilarProblems[$problemName], $peerReviewBaseProblemID);
	    }
	}
	else {
	    $PRSimilarProblems[$problemName] = array($peerReviewPromptID, $peerReviewBaseProblemID);
	}

     }

    ksort($PRSimilarProblems);

    foreach ($PRSimilarProblems as $key => $val) {
	$listOfProblems = "";
	foreach ($PRSimilarProblems[$key] as $key2 => $val2) {
	  $listOfProblems .= "$val2,";
	}
	$PRproblemIDs .= "<option value='$listOfProblems'>$key</option>\n";
    }

////////////////////
	$query =   'SELECT submissionID, submissionProblemID '
		  .' FROM `studentSubmissions` '
		  .' WHERE submissionHomeworkSetID="'.$homeworkSetID. '"';

	$result = mysql_query($query, $con);

	$SSproblemIDs = "";

	$SSproblemIDs   .= '<option value=""></option.\n';

	$SSSimilarProblems = array();

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $submissionProblemID = $row['submissionProblemID'];
	  $problemName = $problemArray[$submissionProblemID];

	  if (array_key_exists($problemName, $SSSimilarProblems)) {
	    if (in_array($submissionProblemID, $SSSimilarProblems[$problemName])) {
		//do nothing
	    }
	    else {
	      array_push($SSSimilarProblems[$problemName], $submissionProblemID);
	    }
	}
	else {
	    $SSSimilarProblems[$problemName] = array("", $submissionProblemID);
	}

     }

    ksort($SSSimilarProblems);

    foreach ($SSSimilarProblems as $key => $val) {
	$listOfProblems = "";
	foreach ($SSSimilarProblems[$key] as $key2 => $val2) {
	  $listOfProblems .= "$val2,";
	}
	$SSproblemIDs .= "<option value='$listOfProblems'>$key</option>\n";
    }
//////////////////////////

    mysql_close($con);

?>
<form method="post" action="">
<input name="Course" value="
<?php
print $courseID;
?>
" type="hidden">
<input name="Assignment" value="
<?php
print $Assignment;
?>
" type="hidden">
<BR><HR><BR>
Select Assignment:
<P>
Self Explanation:
<select name="SEProblemIDAndPromptID">
<?php
print $SEproblemIDAndPromptID;
?>
</select>

Student Submission:
<select name="SSproblemIDs">
<?php
print $SSproblemIDs;
?>
</select>

Peer Review:
<select name="PRproblemIDs">
<?php
print $PRproblemIDs;
?>
</select>

<BR>
<input type="submit" name="Submit" value="Submit"> <BR>
</form>

<?php

}

elseif (isset($_REQUEST['Course'])) {

	// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

	$con = mysql_connect($db_host, $db_user, $db_pass);

	if(!$con) {
	  die('Could not connect: ' . mysql_error());
	}

	//select the database '$db'
	$res = mysql_select_db("$db", $con);
	$courseID = $_REQUEST['Course'];

	$query =   'SELECT homeworkSetID, homeworkSetName '
		  .' FROM `homeworkSets` '
		  .' WHERE forCourseID="'.$courseID. '"';

	$result = mysql_query($query, $con);

	$homeworkSetList = "";
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $homeworkSetList   .= '<option value="' . $row['homeworkSetID'] . '">'
	  		     .  $row['homeworkSetName'] 
			     . '</option>\n';
	}

	mysql_close($con);

?>
<form method="post" action="">
<BR><HR><BR>
Select Assignment: 
<input name="Course" value="
<?php
print $courseID;
?>
" type="hidden">
<select name="Assignment">
<?php
print $homeworkSetList;
?>
</select>

<input type="submit" name="Submit" value="Submit"> <BR>
</form>

<?php
}

else 
{
// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$query =   'SELECT courseID, courseName, sectionName '
	  .' FROM `courses` ';
$result = mysql_query($query, $con);

$courseList = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $courseList   .= '<option value="' . $row['courseID'] . '">'
		.  $row['courseName'] . ': ' . $row['sectionName'] 
		. '</option>\n';
}

mysql_close($con);

?>
<form method="post" action="">
<BR><HR><BR>
Select Class: 
<select name="Course">
<?php
print $courseList
?>
</select>

<input type="submit" name="Submit" value="Submit"> <BR>
</form>
<?php
} 



?>
