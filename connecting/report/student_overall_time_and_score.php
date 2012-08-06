<?php

include("../access.php");
include("common.php");

function cmpByLastName($a, $b) {
  $a = substr($a, 1);
  $b = substr($b, 1);
  return strcasecmp($a, $b);
}

$courses = array();
$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);
$print_out_timings = 1;


if (isset($_REQUEST['courses'])) {

  $start_all = getTime();

  $do_these_courses = $_REQUEST['courses'];
  $quizName = $_REQUEST['do_these_quizzes'];
  $print_user_name = $_REQUEST['print_user_name'] == "users";
  $print_out_problems = $_REQUEST['print_out_problems'] == 'yes' ? 1 : 0;
  $show_top_row = $_REQUEST['print_out_top_row'] == 1 ? 1 : 0;
  $sort_by = $_REQUEST['sort_by'];

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  //The user has asked for this course, so let's give them the results.
  for ($c = 0; $c < count($do_these_courses); $c++) {

    $db = "webwork";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    $user = array();
    $preNum = array();
    $preDen = array();
    $postNum = array();
    $postDen = array();
    $practiceTime = array();
    $preQuizTime = array();
    $postQuizTime = array();
    $reviewTime = array();
    $tookFinalQuiz = array();
    $individual_problem_scores = array();
    $individual_problem_order = array();

    $maxPreDen = 1;
    $maxPreDenArray = array();

    $course = $do_these_courses[$c]; //'Math160_F2009_awangberg';
    //get all the users for this course:
    $start = getTime();
    $query = 'SELECT user_id FROM `' . $course . '_user`';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      $user[] = $this_user;
      $preNum[$course][$this_user] = 0;
      $preDen[$course][$this_user] = 0;
      $postNum[$course][$this_user] = 0;
      $postDen[$course][$this_user] = 0;
      $practiceTime[$course][$this_user] = 0;
      $preQuizTime[$course][$this_user] = 0;
      $postQuizTime[$course][$this_user] = 0;
      $reviewTime[$course][$this_user] = 0;
      $tookFinalQuiz[$course][$this_user] = "";
    }
    $end = getTime();
    //print_r($preDen);
    if ($print_out_timings) { echo 'time to initialize variables for users: ' . round($end - $start, 4) . '<BR>'; }

    $start = getTime();
    //get all the initial quiz scores for the users in the course:
    for ($q = 0; $q < count($quizName); $q++) {
      $quiz = $quizName[$q];
      $query = 'SELECT ' . $course . '_problem.problem_id, ' . $course . '_problem.source_file, status, attempted, user_id FROM `' . $course . '_problem_user` LEFT JOIN `' . $course . '_problem` ON (' . $course . '_problem_user.set_id = CONCAT(' . $course . '_problem.set_id, ",v1") AND ' . $course . '_problem_user.problem_id = ' . $course . '_problem.problem_id) WHERE ' . $course . '_problem_user.set_id="' . $quiz . ',v1"';

      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_score = $row['status'];
        $this_attempt = $row['attempted'];
        $this_concept_set = $row['source_file'];
        $this_order = $row['problem_id'];

        $preNum[$course][$this_user] += $this_score == 1 ? 1 : 0;
        $preDen[$course][$this_user] += 1;

        $individual_problem_scores[$course][$this_user][$quiz][$this_concept_set]['preScore'] = $this_score == 1 ? 1 : 0;
        if ($quiz != 'pca') {
          $individual_problem_scores[$course][$this_user][$quiz][$this_concept_set]['postScore'] = $this_score == 1 ? 1 : 0;
        }
        else {
          $individual_problem_scores[$course][$this_user][$quiz][$this_concept_set]['postScore'] = 0;
        }
        $individual_problem_order[$course][$this_user][$quiz][$this_order] = $this_concept_set;

        if (($this_score == 1) && ($quiz != 'pca')) {
          //If the user gets it right on the initial quiz, then we don't ask the concept on the final quiz
          //we assume they would have gotten it right, so count this as right in the postNum.
          $postNum[$course][$this_user]++;
          $postDen[$course][$this_user]++;
        }
      }
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather initial quiz scores for the users in the course: ' . round($end - $start, 4) . '<BR>'; }


    $start = getTime();
    //get all the final quiz scores for the users in this course:

    for ($q = 0; $q < count($quizName); $q++) {
      $quiz = $quizName[$q];
        
      $query = 'SELECT ' . $course . '_problem.source_file, status, attempted, user_id FROM `' . $course . '_problem_user` LEFT JOIN `' . $course . '_problem` ON (' . $course . '_problem_user.set_id = CONCAT(' . $course . '_problem.set_id, ",v1") AND ' . $course . '_problem_user.problem_id = ' . $course . '_problem.problem_id) WHERE ' . $course . '_problem_user.set_id REGEXP "^finalQuiz\_[[:alnum:]]*(\_)*' . $quiz . ',v1$"';

      //print $query . "<BR>";
      //$s1 = getTime();
      $result = mysql_query($query, $con);
      //$s2 = getTime();
      //if ($print_out_timings) { echo 'time for query: ' . round($s2 - $s1, 4) . '<BR>'; }
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_status = $row['status'];
        $this_attempted = $row['attempted'];
        $this_concept_set = $row['source_file'];
        $this_score = $row['status'];

        //if ($quiz != 'pca') {
          $postDen["$course"]["$this_user"] += 1; //$attempted_problem;
        //}

        if (($this_attempted == 1) && ($this_status == 1) ) {
          $postNum["$course"]["$this_user"]++;
          $individual_problem_scores["$course"]["$this_user"]["$quiz"]["$this_concept_set"]['postScore'] = 1;
        }
      }
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather final quiz scores for the users in the course: ' . round($end - $start, 4) . '<BR>'; }

    $start = getTime();
    //get all the time spent on initial and final quizzes for the users in the course:
    for ($q = 0; $q < count($quizName); $q++) {
      $quiz = $quizName[$q];
      $query = 'SELECT user_id, version_creation_time, version_last_attempt_time FROM `' . $course . '_set_user` WHERE set_id="' . $quiz . ',v1"';
      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $t1 = 0;  $t2 = 0;
        $t1 = $row['version_creation_time'];
        $t2 = $row['version_last_attempt_time'];
        if (($t1 > 0) && ($t2 > 0)) {
          $preQuizTime[$course][$this_user] += ($t2 - $t1);
        }
      }
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather time spent on initial quizzes for the users in the course: ' . round($end - $start, 4) . '<BR>'; }

    $start = getTime();
    //get all the time spent on the final quizzes for the users in the course:
    for ($q = 0; $q < count($quizName); $q++) {
      $quiz = $quizName[$q];
      $query = 'SELECT user_id, version_creation_time, version_last_attempt_time FROM `' . $course . '_set_user` WHERE set_id REGEXP "^finalQuiz\_[[:alnum:]]*(\_)*' . $quiz . ',v1$"';
print "<BR>$query<BR>";
      $result = mysql_query($query, $con);
      $saw_quiz = -1;
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $t1 = 0; $t2 = 0;
        $t1 = $row['version_creation_time'];
        $t2 = $row['version_last_attempt_time'];
        if (($t1 > 0) && ($t2 > 0)) {
          $postQuizTime[$course][$this_user] += ($t2 - $t1);
        }
        $saw_quiz = 1;
        $tookFinalQuiz[$course][$this_user][$quiz] .= "T";
      }
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather time spent on final quizzes for the users in the course: ' . round($end - $start, 4) . '<BR>'; }
    //get all the practice time for the users in the course:

    $start = getTime();  
    $db = 'wwSession';
    $result = mysql_select_db("$db", $con);

    $query = 'SELECT user_name, attempted_date, submitted_date FROM `attempts` WHERE course_name="' . $course . '" AND submitted_date != "0000-00-00 00:00:00"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_name'];
      $t1 = 0; $t2 = 0;
      $t1 = $row['attempted_date'];
      $t2 = $row['submitted_date'];
      $acc_time = date(strtotime($t2)) - date(strtotime($t1));
      if (($acc_time > 0) && ($acc_time < 60*60)) {
        $practiceTime[$course][$this_user] += $acc_time;
      }
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather time spent on practice time for users in the course: ' . round($end - $start, 4) . '<BR>'; }

    $start = getTime();
    //get all the review time for the users in the course:
    $query = 'SELECT user_name, quizName, timeSpentOnReviewInSeconds FROM `attendedReviewSession` WHERE course_name="' . $course . '"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = ""; $this_quiz = ""; $thisTime = 0;
      $this_user = $row['user_name'];
      $this_quiz = $row['quizName'];
      $thisTime = $row['timeSpentOnReviewInSeconds'];
      $reviewTime[$course][$this_user] += $thisTime;
    }
    $end = getTime();
    if ($print_out_timings) { echo 'time to gather time spent at review sessions for users in the course: ' . round($end - $start, 4) . '<BR>'; }

    $start = getTime();
    print "<H2>Course: " . $course . "</H2>\n";
    print "<TABLE>\n";
    print "<TR><TH>Student</TH>";
    if ($show_top_row) { print "<TH width=100>Pre-Score</TH><TH width=100>Post-Score</TH>"; }
    else { print "<TH width=100>Pre %</TH><TH width=100>Pre Num</TH><TH width=100>Pre Den</TH><TH width=100>Post %</TH><TH width=100>Post Num</TH><TH width=100>Post Den</TH>"; }

    print "<TH>Completed Final Quizzes</TH><TH width=100>Overall Time</TH><TH width=100>Quiz Time</TH><TH width=100>Practice Time</TH><TH>Review Session Time</TH><TH>";
    print "<div style='width:200px; height:20px; background-color:#CCC;'>Performance</div>";
    print "<div style='width:180px; height: 20px; background-color:#123; color:#FFF;'>Final Quiz</div>";
    print "<div style='width:110px; height: 20px; background-color:#666; color:#FFF; border-right:1px #FFF solid; '>Initial Quiz</div>";
    print "</TH><TH>";
    print "<div style='width:200px; height:20px; background-color:#CCC;'>Time (20hrs)</div>";
//  print "<div style='width:180px; height:20px; background-color:#123; color:#FFF;'>Final Quiz</div>";
    print "<div style='width:150px; height:20px; background-color:#789; color:#FFF;'>Review Sessions</div>";
    print "<div style='width:120px; height:20px; background-color:#456; color:#FFF;'>Online Practice</div>";
//  print "<div style='width:90px; height:20px; background-color:#666; color:#FFF;'>Initial Quiz</div>";
    print $print_out_problems ? '</TH><TH>Individual Problem Scores</TH>' : '';
    print "</TH></TR>\n";

    $maxPreDen = 84;
    $maxPreDen = 119;
    $maxPreDenArray["Math160_F2009_awangberg"] = 84;
    $maxPreDenArray["Math160_S2010_awangberg_05"] = 84;
    $maxPreDenArray["Math160_S2010_eerrthum"] = 84;
    $maxPreDenArray["Math160_F2010_awangberg"] = 119;

    //adjust the sorting algorithm here.
    if ($sort_by == 'last_name') { uksort($postNum[$course], "cmpByLastName"); }
    else if ($sort_by == 'post_score') { arsort($postNum[$course]); }
    else if ($sort_by == 'pre_score') { }
    else if ($sort_by == 'overall_time') { }
    else if ($sort_by == 'quiz_time') { }
    else if ($sort_by == 'practice_time') { }
    else if ($sort_by == 'review_session_time') { }
    else { print "NO Sorting selected"; }

    foreach ($postNum[$course] as $this_user => $postQuizNum) {
      $preQuizNum =  $preNum[$course][$this_user];
      $preQuizDen = $maxPreDen; //$preDen[$this_user];
      $preQuizDen = $maxPreDenArray[$course];
      $preQuizDen = $preDen[$course][$this_user];
      //$postQuizNum = $postNum[$this_user];
      $postQuizDen = $postDen[$course][$this_user];
      $time_on_quizzes = $preQuizTime[$course][$this_user] + $postQuizTime[$course][$this_user];
      $time_on_practice = $practiceTime[$course][$this_user];
      $time_on_review_session = $reviewTime[$course][$this_user];
      $finalQuizzesTaken = '';
      for ($q = 0; $q < count($quizName); $q++) {
        $finalQuizzesTaken .= array_key_exists($quizName[$q], $tookFinalQuiz[$course][$this_user]) ? "T" : "_";
      }

      $time_overall = $time_on_quizzes + $time_on_practice + $time_on_review_session;

      $prePercent = ($preQuizDen > 0) ? round(100*$preQuizNum/$preQuizDen) : 0;
      $postPercent = ($postQuizDen > 0) ? round(100*$postQuizNum/$postQuizDen) : 0;
      print "<TR>";
      print $print_user_name ? "<TD>$this_user</TD>" : "<TD>User $u</TD>";
      if ($show_top_row) {
        print "<TD>$prePercent% ($preQuizNum/$preQuizDen)</TD>";
        print "<TD>$postPercent% ($postQuizNum/$postQuizDen) </TD>";
      }
      else {
        print "<TD>$prePercent</TD><TD>$preQuizNum</TD><TD>$preQuizDen</TD>";
        print "<TD>$postPercent</TD><TD>$postQuizNum</TD><TD>$postQuizDen</TD>";
      }
      print "<TD>$finalQuizzesTaken</TD>";
      print "<TD>" . formatTime($time_overall) . "</TD>";
      print "<TD>" . formatTime($time_on_quizzes) . "";
      //print " pre: " . formatTime($preQuizTime[$course][$this_user]) . ", post: " . formatTime($postQuizTime[$course][$this_user]) . "";
      print "</TD>";
      print "<TD>" . formatTime($time_on_practice) . "</TD>";
      print "<TD>" . formatTime($time_on_review_session) . "</TD>";
      print "<TD>";
      if ($show_top_row) {
        print "<div style='width:200px; height:20px; background-color:#FFF; border:1px #CCC solid; '>";
//    print "<div style='width:159px; height: 20px; background-color:#CCC; border-right:1px #FFF solid; '>";
//    print "<div style='width:119px; height: 20px; background-color:#CCC; border-right:1px #FFF solid; '>";
//    print "<div style='width:79px; height: 20px; background-color:#CCC; border-right:1px #FFF solid; '>";
//    print "<div style='width:39px; height: 20px; background-color:#CCC; border-right:1px #FFF solid; '>";
        print "<div title='post: " . $postPercent . "%' style='width:" . 2*$postPercent . "px; height: 20px; background-color:#123; border-right:1px #FFF solid; '>";
        print "<div title='pre: " . $prePercent . "%' style='width:" . 2*$prePercent . "px; height: 20px; background-color:#666; border-right:1px #FFF solid; '>";

        print "</div></div>";
        print "</div></div></div></div>";
        print "</div>";
      }
      print "</TD>";
      print "<TD>";
      if ($show_top_row) {
        print "<div style='width:200px; height:20px; background-color:#FFF; border:1px #CCC solid; '>";
        $endDiv = "";
//      for ($b = 9; $b >= 1; $b--) {
//        $width = $b*20 - 1;
//        print "<div style='width:" . $width . "px; height:20px; background-color:#CCC; border-right:1px #FFF solid; '>";
//        $endDiv .= "</div>";
//      }
        print "<div title='" . formatTime($time_overall - $preQuizTime[$course][$this_user] - $time_on_practice - $time_on_review_session) . "' style='width:" . round(10*($time_overall)/3600) . "px; height:20px; background-color:#123; border-right:1px #FFF solid; '>";
        print "<div title='" . formatTime($time_on_review_session) . "' style='width:" . round(10*($preQuizTime[$course][$this_user] + $time_on_practice + $time_on_review_session)/3600) . "px; height:20px; background-color:#789; border-right:1px #FFF solid; '>";
        print "<div title='" . formatTime($time_on_practice) . "' style='width:" . round(10*($preQuizTime[$course][$this_user] + $time_on_practice)/3600) . "px; height:20px; background-color:#456; border-right:1px #FFF solid; '>";
        print "<div title='" . formatTime($preQuizTime[$course][$this_user]) . "' style='width:" . round(10*($preQuizTime[$course][$this_user])/3600) . "px; height:20px; background-color:#666; border-right:1px #FFF solid; '>";
        print "</div></div></div>$endDiv</div>";
      }
      print "</TD>";
      if ($print_out_problems) {
        print "<TD>";
        if ($show_top_row) { print "<div style='font-family:\"Courier New\", monospace; white-space:pre'>"; }
        $top_row = '';
        $bottom_row = '';
        foreach ($quizName as $kk => $quiz) {
          $la = '  ID: ' . $quiz . ': ';
          $lb = ' PRE: ' . $quiz . ': ';
          $lc = 'POST: ' . $quiz . ': ';
          $ld = 'REDO: ' . $quiz . ': ';
          foreach ($individual_problem_order[$course][$this_user][$quiz] as $k => $v) {
            $la .= ' <a href="' . $v . '">' . $k . '</a> ';
            $spaces = strlen($k) == 1 ? ' ' : (strlen($k) == 2 ? '  ' : '   ');
            $pres = $individual_problem_scores[$course][$this_user][$quiz][$v]['preScore'];
            $poss = $individual_problem_scores[$course][$this_user][$quiz][$v]['postScore'];
            $pres_color = $pres == 1 ? "<font color='blue'>" : "<font color='black'>";
            $poss_color = $poss > $pres ? "<font color='red'>" : "<font color='black'>";
            $lb .= $spaces . $pres_color . $pres . '</font> ';
            $lc .= $spaces . $poss_color . $poss . '</font> ';
          }
          $top_row .= "$la $la ";
          $bottom_row .= "$lb $lc ";
        }
        if ($show_top_row) {  print $top_row . "\n"; }
        print "$bottom_row";
        if ($show_top_row) {  print "</div>"; }
        print "</TD>";
      }
      print "</TR>\n";
    }
    print "</TABLE>\n";
    $end = getTime();
    if ($print_out_timings) { echo 'time to print out table for this course: ' . round($end - $start, 4) . '<BR>'; }

    unset($user);
    unset($preNum);
    unset($preDen);
    unset($postNum);
    unset($postDen);
    unset($practiceTime);
    unset($preQuizTime);
    unset($postQuizTime);
    unset($reviewTime);
    unset($tookFinalQuiz);
  }
  mysql_close();
  $end_all = getTime();
  if ($print_out_timings) { echo 'overall time to process data: ' . round($end_all - $start_all, 4) . '<BR>'; }

}
else {
  //ask the user for the course:

$quizName = get_quizNames();

  print "<form method='post'>";
  print "<BR><B>Courses</B><BR>";
  for ($c = 0; $c < count($courses); $c++) {
    print "<input name='courses[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
  }
  print "<BR><B>Quizzes</B><BR>";
  for ($c = 0; $c < count($quizName); $c++) {
    print "<input name='do_these_quizzes[]' type='checkbox' value='" . $quizName[$c] . "'>" . $quizName[$c] . "<BR>\n";
  }
  print "<BR><B>Options</B><BR>\n";
  print "<input name='print_out_problems' type='checkbox' value='yes'>List Individual Problems";
  print " and <input name='print_out_top_row' type='checkbox' value=1 CHECKED>Problem Numbers<BR>";
  print "List Usernames (Enter code):";
  print "<input name='print_user_name' type='text' id='print_user_name' value=''>\n";

  print "<BR><B>Sort According to:</B>";
  print "<input name='sort_by' type='checkbox' value='last_name'>Last Name\n";
  print "<input name='sort_by' type='checkbox' value='post_score'>Post Score\n";
  print "<input name='sort_by' type='checkbox' value='pre_score'>Pre Score\n";
  print "<input name='sort_by' type='checkbox' value='overall_time'>Overall Time\n";
  print "<input name='sort_by' type='checkbox' value='quiz_time'>Quiz Time\n";
  print "<input name='sort_by' type='checkbox' value='practice_time'>Practice Time\n";
  print "<input name='sort_by' type='checkbox' value='review_session_time'>Review Session Time\n";

  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";

}
