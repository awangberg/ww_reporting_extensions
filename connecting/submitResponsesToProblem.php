<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db:
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "Error selecting database $db: " . mysql_error();
}

$splitter = $_REQUEST['splitter'];

$user_id = $_REQUEST['user_id'];
$type = $_REQUEST['type'];
$problem_id = $_REQUEST['problem_id'];
$draw_id = $_REQUEST['draw_id'];
$prompt_id = $_REQUEST['prompt_id'];
$answer_id = $_REQUEST['answer_id'];
$tutorial_key = $_REQUEST['tutorial_key'];
$student_answer = $_REQUEST['studentAnswer'];
$studentReason = $_REQUEST['studentReason'];

$pointsPossible = $_REQUEST['pointsPossible'];
$pointsEarned = $_REQUEST['pointsEarned'];

$replayPart = $_REQUEST['replayPart'];

$to_see_problem = $_REQUEST['timeToReplayTutorialWork'];
$to_submit_answer = $_REQUEST['toSubmitAnswer'];
$to_submit_reason = $_REQUEST['toSubmitReason'];
$to_view_answer = $_REQUEST['toViewAnswer'];
$to_view_reason = $_REQUEST['toViewReason'];
$to_view_all_answers = $_REQUEST['toViewAllAnswers'];
$to_view_all_reasons = $_REQUEST['toViewAllReasons'];
$to_draw = $_REQUEST['toDraw'];

$askingForReasonOnReplay = $_REQUEST['askingForReasonOnReplay'];
$answerWasCorrect = $_REQUEST['answerWasCorrect'];
$previous_student_answer_id = $_REQUEST['previous_student_answer_id'];

$new_student_answer_id = -1;

$rq = "";

if ($previous_student_answer_id >= 0) {
  //create a new student_answer record:
  $query = 'INSERT INTO student_answer (problem_id, tutorial_key, student_id, internal_order) '
         . 'VALUES (' . $problem_id . ', ' . $tutorial_key . ', ' . $user_id . ', 1)';
  $result = mysql_query($query, $con);
  $new_student_answer_id = mysql_insert_id();
  $rq .= "<q1>$query</q1>\n";

  //update the previous student_answer record to point to a new student_answer record:
  $query = 'UPDATE student_answer SET next_answer_id=' . $new_student_answer_id . ' WHERE answer_id=' . $previous_student_answer_id;
  $result = mysql_query($query, $con);
  $rq .= "<q2>$query></q2>\n";
}
else {
  $new_student_answer_id = $answer_id;
}

//start putting the information into the student_answer record:
$query = 'UPDATE student_answer SET ' 
       . 'problem_id=' . $problem_id . ', ' 
       . 'draw_id='    . $draw_id    . ', ' 
       . 'prompt_id='  . $prompt_id  . ', ' 
       . 'student_id=' . $user_id    . ', ' 
       . 'askForReason="' . $askingForReasonOnReplay . '", ' 
       . 'reason="'    . $studentReason . '", ' 
       . 'points='     . $pointsEarned  . ', ' 
       . 'possible_points=' . $pointsPossible . ' ' 
       . 'WHERE answer_id=' . $new_student_answer_id;

$result = mysql_query($query, $con);
$rq .= "<q3>$query</q3>\n";

//If type is either Graph or Draw, then student_answer is a drawing.
//store the student_answer in a textfile, and put "drawing" in for answer.
if (($type == "Graph") || ($type == "Draw")) {
  //write the contents of $student_answer:
  if (strlen($studentAnswer)) {
    $tmpfname = tempnam("/opt/session/drawings", "replyToPrompt" . $prompt_id . "_");

    $handle = fopen($tmpfname, "w");
    fwrite($handle, $student_answer);
    fclose($handle);

    //insert the drawing information into the student_answer record:
    $query = 'UPDATE student_answer SET '
           . 'filename=' . check_input($tmpfname) . ', '
           . 'answer="' . $type . 'ing Answer" '
           . 'WHERE answer_id=' . $new_student_answer_id;
    $result = mysql_query($query, $con);
    $rq .= "<q4>$query<\q4>\n";
  }
}
else {
  //store the student_answer in student_answer.
  $query = 'UPDATE student_answer SET answer="' . $student_answer . '" WHERE answer_id=' . $new_student_answer_id;
  $result = mysql_query($query, $con);
  $rq .= "<q5>$query</q5>\n";
}

//Now, go put information into the viewed table:

$viewed_key = -1;
$internal_order = -1;
$query = 'SELECT viewed_key, internal_order FROM `student_answer` WHERE answer_id=' . $new_student_answer_id;
$result = mysql_query($query, $con);

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $viewed_key = $row['viewed_key'];
  $internal_order = $row['internal_order'];
}
$rq .= "<q6>$query</q6>\n";


if (!($viewed_key > -1)) {
  //There isn't a viewing, of the problem before,
  //so add a record to viewed and place the view_id from that record
  //in the viewed_key of the student_answer record.
  //The internal_order is 1.
  $query = 'INSERT INTO viewed ' . 
           '(prompt_id, problem_id, draw_id, internal_order, to_see_problem, to_submit_answer, to_submit_reason, to_view_answer, to_view_reason, to_view_all_answers, to_view_all_reasons, to_draw) ' . 
           'VALUES ' .
           "($prompt_id, $problem_id, $draw_id, 1, $to_see_problem, $to_submit_answer, $to_submit_reason, $to_view_answer, $to_view_reason, $to_view_all_answers, $to_view_all_reasons, $to_draw)";
  $result = mysql_query($query, $con);
  $view_id = mysql_insert_id();
  $rq .= "<q7>$query</q7>\n";


  $query = 'UPDATE viewed SET view_key=' . $view_id . ' WHERE view_id=' . $view_id;
  $result = mysql_query($query, $con);
  $rq .= "<q8>$query</q8>\n";

  $query = 'UPDATE student_answer SET viewed_key=' . $view_id . ' WHERE answer_id=' . $new_student_answer_id;
  $result = mysql_query($query, $con);
  $rq .= "<q9>$query</q9>\n";
}
else {
  //There was a viewing of the problem before,
  //so we created a viewed record but give it the view_key of $viewed_key.
  //The internal_order in the viewed record is that of the student_answer record.
  $query = 'INSERT INTO viewed ' .
           '(prompt_id, problem_id, draw_id, internal_order, to_see_problem, to_submit_answer, to_submit_reason, to_view_answer, to_view_reason, to_view_all_answers, to_view_all_reasons, to_draw, view_key) ' .
           'VALUES ' .
           '(' . $prompt_id . ', ' . $problem_id . ', ' . $draw_id . ', ' . $internal_order . ', ' . $to_see_problem . ', '. $to_submit_answer . ', ' . $to_submit_reason . ', '. $to_view_answer . ', '. $to_view_reason . ', ' . $to_view_all_answers . ', ' .  $to_view_all_reasons . ', '. $to_draw . ', ' . $viewed_key . ')';
  $result = mysql_query($query, $con);
  $view_id = mysql_insert_id();
  $rq .= "<q10>$query</q10>\n";
}

mysql_close($con);

print "<Results>\n";
print "  <Code>Success</Code>\n";
print "  <previous_student_answer_id>$new_student_answer_id</previous_student_answer_id>\n";
print "  <queries>$rq</queries>\n";
print "</Results>\n";

?>

