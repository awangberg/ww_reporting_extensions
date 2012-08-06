<?php

#include("matrix.php");

//function returns microseconds.  Used for debugging & finding time consuming code parts.
function getTime() {
  $timer = explode(' ', microtime());
  $timer = $timer[1] + $timer[0];
  return $timer;
}


function my_array_merge($ar1, $ar2) {
  $arr = array_merge($ar1, $ar2);

  foreach ($arr as $key => $value) {
    if ($arr[$key] == "") {
      unset($arr[$key]);
    }
  }
  return $arr;
}

function compute_std_dev_key($mean, $list) {
  $s = 0;
  $count = 0;
  foreach ($list as $key => $val) {
    $s += ($key - $mean)*($key - $mean);
    $count++;
  }
  return $count > 2 ? sqrt($s/($count - 1)) : 0;
}

function compute_std_dev_val($mean, $list) {
  $s = 0;
  $count = 0;
  foreach ($list as $key => $val) {
    $s += ($val - $mean)*($val - $mean);
    $count++;
  }
  return $count > 2 ? sqrt($s/($count - 1)) : 0;
}
 
function compute_std_dev_val_2($mean, $list) {
  $s = 0;
  $count = 0;
  foreach ($list as $key1 => $tmp_array) {
    foreach ($tmp_array as $key => $val) {
      $s += ($val - $mean)*($val - $mean);
      $count++;
    }
  }
  return $count > 2 ? sqrt($s/($count - 1)) : 0;
}


function compute_mean_key($list) {
  $count = 0;
  $m = 0;
  foreach ($list as $key => $val) {
    $m += $key;
    $count++;
  }
  return $m/$count;
}

function compute_mean_val($list) {
  $count = 0;
  $m = 0;
  foreach ($list as $key => $val) {
    $m += $val;
    $count++;
  }
  return $m/$count;
}

function compute_mean_val_2($list) {
  $count = 0;
  $m = 0;
  foreach ($list as $k1 => $tmp_array) {
    foreach ($tmp_array as $key => $val) {
      $m += $val;
      $count++;
    }
  }
  return $m/$count;
}

function count_2($list) {
  $count = 0;
  foreach ($list as $k1 => $tmp_array) {
    $count += count($tmp_array);
  }
  return $count;
}

function formatTime($seconds) {
  $hours = floor($seconds/3600);
  $seconds = $seconds - $hours*3600;
  $minutes = floor($seconds/60); 
  $seconds = $seconds - $minutes*60;
  if ($hours == 0) { $hours = "00"; }
  else if ($hours < 10) { $hours = "0" . $hours; }
  if ($minutes == 0) { $minutes = "00"; }
  else if ($minutes < 10) { $minutes = "0" . $minutes; }
  $seconds = round($seconds, 0);
  if ($seconds == 0) { $seconds = "00"; }
  else if ($seconds < 10) { $seconds = "0" . $seconds; }
  return "$hours:$minutes:$seconds";
}

function shortFormatTime($seconds) {
  $time = explode(":", formatTime($seconds));
  if ($time[0] == "00") {
    return $time[1] . ":" . $time[2];
  }
  else {
    return $time[0] . ":" . $time[1] . ":" . $time[2];
  }
  
}

function valid_users($con, $ww_course, $meas='validForStatistics="1"') {
  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT id FROM `course_wwValidCourses` WHERE ww_course="' . $ww_course . '"';
  $result = mysql_query($query, $con);
  $course_id = -1;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['id'];
  }

  $query = 'SELECT id, ww_user_id FROM `course_wwUserPermissions` WHERE course_wwValidCourses_id="' . $course_id . '" AND ' . $meas . '';
  $result = mysql_query($query, $con);
  $validUsers = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $validUsers[$row['ww_user_id']] = $row['id'];
  }
  return $validUsers;
}

function get_quizNames() {
  $quizName = array();
  $quizName[0] = "Basics";
  $quizName[1] = "Graphs";
  $quizName[2] = "LinearRational";
  $quizName[3] = "ExpLog";
  $quizName[4] = "Trig";
  $quizName[5] = "pca";
  $quizName[6] = "pcb";
  return $quizName;
}



function html2rgb($color) {
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return array($r, $g, $b);
}



function get_colors() {
  $color = array();
  $color['Overall'] = "#789";
  $color['Basics'] = "#009";
  $color['Graphs'] = "#0b0";
  $color['LinearRational'] = "#d00";
  $color['ExpLog'] = "#FF8100";
  $color['Trig'] = "#00A874";
  $color['pca'] = "#A80074";
  $color['pcb'] = "#7400A8";
  return $color;
}

function get_reviewColor() {
  $reviewColor = array();
  $reviewColor['Overall'] = "#89A";
  $reviewColor['Basics'] = "#11A";
  $reviewColor['Graphs'] = "#1c1";
  $reviewColor['LinearRational'] = "#e11";
  $reviewColor['ExpLog'] = "#FF9211";
  $reviewColor['Trig'] = "#11B985";
  return $reviewColor;
}

function get_conceptBankDescription($con) {
  $db = "wwSession";
  //select the database '$db';
  $result = mysql_select_db("$db", $con);

  $cbd = array();
  $query = 'SELECT concept_bank, description FROM `conceptBankDescription`';
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $cbd[$row['concept_bank']] = $row['description'];
  }
  return $cbd;
}

function get_courses_make_con($db_host, $db_user, $db_pass) {
  $con = mysql_connect($db_host, $db_user, $db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = 'wwSession';
  //select the database $db
  $result = mysql_select_db("$db", $con);
  $courses = get_courses($con);
  mysql_close($con);
  return $courses;
}

function get_courses($con) {
  $db = 'wwSession';
  //select teh database '$db'
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT ww_course FROM `course_wwValidCourses` WHERE validCourse=1 AND validForStatistics=1';
  $result = mysql_query($query, $con);
  $courses = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $courses[] = $row['ww_course'];
  }
  return $courses;
}

function get_courses_bad() {
  $courses = array();
  $courses[0] = "Math160_F2009_awangberg";
  $courses[1] = "Math160_S2010_awangberg_05";
  $courses[2] = "Math160_S2010_eerrthum";
  $courses[3] = "Math160_F2010_awangberg";
  $courses[4] = "Math160_F2011_awangberg";
  $courses[5] = "Math160_S2012_awangberg";
  $courses[6] = "RUME_2012";
  return $courses;
}

//
//  Get data from the database:
//


//get the users from this course:
function get_users_from_course($con, $course, $user, $validate=true, $studentCondition='validForStatistics="1"') {
  $db = "wwSession";
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  //get the course_id for this course:
  $query = 'SELECT id FROM `course_wwValidCourses` WHERE ww_course="' . $course . '"';
  $result = mysql_query($query, $con);
  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['id'];
  }

  if ($course_id >= 0) {
    //get all the users for this course:
    $query = 'SELECT ww_user_id FROM `course_wwUserPermissions` WHERE '
	     . ($validate ? $studentCondition . ' AND ' : '')
	     . ' course_wwValidCourses_id="' . $course_id . '"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['ww_user_id'];
      $user[$course][$this_user] = $this_user;
    }
  }
  return $user;
}

function get_users_ids_from_course($con, $course, $user, $validate=true, $studentCondition='validForStatistics="1"') {
  //array:  user[course][username] = id in course_wwUserPermissions table
  $db = "wwSession";
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  //get the course_id for this course:
  $query = 'SELECT id FROM `course_wwValidCourses` WHERE ww_course="' . $course . '"';
  $result = mysql_query($query, $con);
  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['id'];
  }

  if ($course_id >= 0) {
    //get all the users for this course:
    $query = 'SELECT id, ww_user_id FROM `course_wwUserPermissions` WHERE '
	   . ($validate ? $studentCondition . ' AND ' : '')
	   . ' course_wwValidCourses_id="' . $course_id . '"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['ww_user_id'];
      $id = $row['id'];
      $user[$course][$this_user] = $id;
    }
  }
  return $user;
}

function get_users_from_course_old($con, $course, $user, $validate=true) {
  $db = "webwork";
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  //get all the users for this course:
  $query = 'SELECT user_id FROM `' . $course . '_user`';
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_user = $row['user_id'];
    if ($validate) {
      if (is_valid_user($this_user)) {
        $user[$course][$this_user] = $this_user;
      }
    }
    else {
      $user[$course][$this_user] = $this_user;
    }
  }
  return $user;  
}


//get all the practice time for users in this course:
function get_practice_time_for_users($con, $course, $practice_time, $studentCondition='validForStatistics="1"') {
  $valid_users = valid_users($con, $course, $studentCondition);

  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT user_name, concept_bank, attempted_date, submitted_date FROM `attempts` WHERE course_name="' . $course . '" AND submitted_date != "0000-00-00 00:00:00"';
  $result = mysql_query($query, $con);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_user = $row['user_name'];
    if ($valid_users[$this_user]) {
      $t1 = 0; $t2 = 0;
      $t1 = $row['attempted_date'];
      $t2 = $row['submitted_date'];
      $this_c = "group:" . $row['concept_bank'];
      $acc_time = date(strtotime($t2) - strtotime($t1));
      if (($acc_time > 0) && ($acc_time < 20*60)) {
        if (array_key_exists($this_user, $practice_time['post']['success'][$this_c][$course])) {
          $practice_time['post']['success'][$this_c][$course][$this_user] += $acc_time;
        }
        else {
          if (!array_key_exists('failure', $practice_time['post'])) $practice_time['post']['failure'] = array();
          if (!array_key_exists($this_c, $practice_time['post']['failure'])) $practice_time['post']['failure'][$this_c] = array();
	  if (!array_key_exists($course, $practice_time['post']['failure'][$this_c]))  $practice_time['post']['failure'][$this_c][$course] = array();
          if (!array_key_exists($this_user, $practice_time['post']['failure'][$this_c][$course])) $practice_time['post']['failure'][$this_c][$course][$this_user] = 0;
          $practice_time['post']['failure'][$this_c][$course][$this_user] += $acc_time;
        }
      }
    }
  }
  return $practice_time;
}


//get the attendence at the review sessions for users in this course:
function get_attendence_at_review_sessions($con, $course, $did_review_session) {
  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT user_name, quizName, timeSpentOnReviewInSeconds FROM `attendedReviewSession` WHERE course_name="' . $course . '"';
  $result = mysql_query($query, $con);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_user = $row['user_name'];
    $this_quiz = $row['quizName'];
    $time = $row['timeSpentOnReviewInSeconds'];
    if (!array_key_exists($this_quiz, $did_review_session)) $did_review_session[$this_quiz] = array();
    if (!array_key_exists($course, $did_review_session[$this_quiz]))  $did_review_session[$this_quiz][$course] = array();
    $did_review_session[$this_quiz][$course][$this_user] = $time;
    if (!array_key_exists('Overall', $did_review_session)) $did_review_session['Overall'] = array();
    if (!array_key_exists($course, $did_review_session['Overall']))  $did_review_session['Overall'][$course] = array();
    if (!array_key_exists($this_user, $did_review_session['Overall'][$course])) $did_review_session['Overall'][$course][$this_user] = 0;
    $did_review_session['Overall'][$course][$this_user] += $time;
  }
  return $did_review_session;
}


function get_initial_scores_from_course($con, $course, $quizName, $concept, $practice_time, $quizSkill_to_conceptBank, $conceptBank_to_quizSkill, $studentCondition='validForStatistics="1"') {

  $valid_users = valid_users($con, $course, $studentCondition);

  $db = 'webwork';
  $result = mysql_select_db("$db", $con);

  $max_qN = count($quizName);
  for ($q = 0; $q < $max_qN; $q++) {
    $quiz = $quizName[$q];
    $query = 'SELECT ' . $course . '_problem_user.user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "' . $quiz .'"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      if ($this_user == '') print "GOT A USER OF '' ";

      $this_score = $row['status'];
      $this_attempt = $row['attempted'];
      $this_concept = $row['concept'];
      $this_problem_source = $row['problem_file'];

      if ($valid_users[$this_user]) {
        //initialize data?
        $conceptBank_to_quizSkill[$this_concept][$quiz] = $quiz;
        if (!array_key_exists($this_concept, $concept['pre']['den'])) {
          $concept['pre']['den'][$this_concept] = 0;
        }
        $concept['pre']['den'][$this_concept]++;
        $quizSkill_to_conceptBank[$quiz][$this_concept] = $this_concept;
        if ($this_score == 1) {
          if (!array_key_exists($this_concept, $concept['pre']['num']))  $concept['pre']['num'][$this_concept] = 0;
          if (!array_key_exists($this_concept, $concept['post']['num'])) $concept['post']['num'][$this_concept] = 0;
          if (!array_key_exists($this_concept, $concept['post']['den'])) $concept['post']['den'][$this_concept] = 0;
          if (!array_key_exists($this_concept, $concept['pre']['right'])) $concept['pre']['right'][$this_concept] = array();
          $concept['pre']['num'][$this_concept]++;
	  if ($quiz != "pca") {
            $concept['post']['num'][$this_concept]++;
            $concept['post']['den'][$this_concept]++;
          }
          $concept['pre']['right'][$this_concept][$course][$this_user] = 1;
          $practice_time['pre']['success'][$this_concept][$course][$this_user] = 1;
        }
        if ($this_attempt == 1) {
          if (!array_key_exists($this_concept, $concept['concept'])) $concept['concept'][$this_concept] = array();
          if (!array_key_exists('source_file', $concept['concept'][$this_concept])) $concept['concept'][$this_concept]['source_file'] = array();
          if (!array_key_exists($this_problem_source, $concept['concept'][$this_concept]['source_file'])) {
            $concept['concept'][$this_concept]['source_file'][$this_problem_source] = array();
            $concept['concept'][$this_concept]['source_file'][$this_problem_source]['num'] = 0;
            $concept['concept'][$this_concept]['source_file'][$this_problem_source]['den'] = 0;
          }
          $concept['concept'][$this_concept]['source_file'][$this_problem_source]['num'] += $this_score == 1 ? 1 : 0;
          $concept['concept'][$this_concept]['source_file'][$this_problem_source]['den']++;
        }
        if (($this_score < 1) || ($this_attempt != 1)) {
          $practice_time['pre']['failure'][$this_concept][$course][$this_user] = 1;
        }
        $practice_time['pre']['assigned'][$this_concept][$course][$this_user] = true;
      }
    }
  }

  $ret = array();
  $ret[0] = $concept;
  $ret[1] = $practice_time;
  $ret[2] = $quizSkill_to_conceptBank;
  $ret[3] = $conceptBank_to_quizSkill;

  return $ret;
}

function get_final_scores_from_course($con, $course, $user, $quizName, $concept, $practice_time, $studentCondition='validForStatistics="1"') {

  $valid_users = valid_users($con, $course, $studentCondition);

  $db = 'webwork';
  $result = mysql_select_db("$db", $con);

  $max_qN = count($quizName);
  foreach ($user[$course] as $this_user) {

    for ($q = 0; $q < $max_qN; $q++) {
      $quiz = $quizName[$q];
      if ($quiz == "pca") {
        $query = 'SELECT user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "finalQuiz_pca" AND user_id="' . $this_user . '"';
      }
      else {
        $query = 'SELECT user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "finalQuiz_' . $this_user . '_' . $quiz . '"';
      }
      $result = mysql_query($query, $con);

      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        //$this_user = $row['user_id'];
	if ($this_user == '') print "GOTT this_user is blank: this_user = " . $this_user . "<BR>";
        $this_score = $row['status'];
        $this_attempt = $row['attempted'];
        $this_concept = $row['concept'];
        $this_problem_source = $row['problem_file'];


        if ($valid_users[$this_user]) {
          if (!array_key_exists('post', $concept))  $concept['post'] = array();
          if (!array_key_exists('num', $concept['post'])) $concept['post']['num'] = array();
          if (!array_key_exists($this_concept, $concept['post']['num'])) $concept['post']['num'][$this_concept] = 0;
          if (!array_key_exists('den', $concept['post'])) $concept['post']['den'] = array();
          if (!array_key_exists($this_concept, $concept['post']['den'])) $concept['post']['den'][$this_concept] = 0;
          $concept['post']['num'][$this_concept] += $this_score == 1 ? 1 : 0;
          $concept['post']['den'][$this_concept]++;
          if ($this_attempt == 1) {
            if (!array_key_exists($this_concept, $concept['concept'])) $concept['concept'][$this_concept] = array();
            if (!array_key_exists('source_file', $concept['concept'][$this_concept])) $concept['concept'][$this_concept]['source_file'] = array();
            if (!array_key_exists($this_problem_source, $concept['concept'][$this_concept]['source_file'])) {
              $concept['concept'][$this_concept]['source_file'][$this_problem_source] = array();
              $concept['concept'][$this_concept]['source_file'][$this_problem_source]['num'] = 0;
              $concept['concept'][$this_concept]['source_file'][$this_problem_source]['den'] = 0;
            }


            $concept['concept'][$this_concept]['source_file'][$this_problem_source]['num'] += $this_score == 1 ? 1 : 0;
            $concept['concept'][$this_concept]['source_file'][$this_problem_source]['den']++;
          }
          if ($this_score == 1) {
            $practice_time['post']['success'][$this_concept][$course][$this_user] = 0;
          }
          else {
            $practice_time['post']['failure'][$this_concept][$course][$this_user] = 0;
          }
	  $practice_time['post']['assigned'][$this_concept][$course][$this_user] = true;
        }
      }
    }
  }

  $ret = array();
  $ret[0] = $concept;
  $ret[1] = $practice_time;

  return $ret;
}



function get_retest_scores_from_course($con, $course, $user, $quizName, $concept, $practice_time, $studentCondition='validForStatistics="1"') {
  $valid_users = valid_users($con, $course, $studentCondition);

  $db = 'webwork';
  $result = mysql_select_db("$db", $con);


  $max_qN = count($quizName);
  for ($q = 0; $q < $max_qN; $q++) {
    $quiz = $quizName[$q];
    $retest_sourcefile_user_attempt_score = array();
    $query = 'SELECT user_id, ' . $course . '_problem.set_id, ' . $course . '_problem_user.source_file, status, attempted FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id REGEXP "^finalQuiz" AND ' . $course . '_problem.set_id REGEXP "' . $quiz . '" AND ' . $course . '_problem.source_file REGEXP "tcerroc"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      if ($this_user == '')  {
	$tu = preg_split("/\_/", $row['set_id']);
	$this_user = $tu[1];
      }
      if ($this_user == '') print "GOTTA blank user: this_user = " . $this_user . "<BR>";
      $this_score = $row['status'];
      $this_attempt = $row['attempted'];
      $this_sourcefile = $row['source_file'];

      if ($valid_users[$this_user]) {
        $retest_sourcefile_user_attempt_score[$this_sourcefile][] =  array($this_user, $this_attempt, $this_score);
      }
    }

    //with limited customized assignments, it is much faster to 
    //suck in all of the set_id and source_file data from the course_problem set
    //and populate an array for reference later, than to
    //make the specific, individual calls for each source_file
    //on a need-to-know basis.
    $bb = array();
    $query = 'SELECT set_id, source_file FROM `' . $course . '_problem` WHERE NOT set_id REGEXP "finalQuiz" AND NOT set_id REGEXP "tcerroc" AND NOT set_id REGEXP "practice"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $bb[$row['source_file']][] = $row['set_id'];
    }

    //echo '<pre>';
    //print_r($bb);
    //echo '</pre>';

    foreach ($retest_sourcefile_user_attempt_score as $sourcefile => $user_attempt_scores ) {
      //$query = 'SELECT set_id FROM `' . $course . '_problem` WHERE source_file="' . $sourcefile . '" AND NOT set_id REGEXP "finalQuiz" AND NOT set_id REGEXP "tcerroc" AND NOT set_id REGEXP "practice"';
      //$result = mysql_query($query, $con);
      ////echo 'query is ' . $query . '<BR>';
      //echo '<BR>new query for <b>' . $sourcefile . '</b>:';
      //echo '<pre>';
      //print_r($bb[$sourcefile]);
      //echo '</pre>';
      //while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        //$this_concept = "group:" . $row['set_id'];
        //echo '<BR>------> ' . $this_concept . ', ';
      foreach ($bb[$sourcefile] as $xyz => $row_set_id) {
        $this_concept = "group:" . $row_set_id;
        //for each entry in the $retest_sourcefile_user_attempt_score[$sourcefile] array...
        $user_attempt_scores_count = count($user_attempt_scores);
        for ($i = 0; $i < $user_attempt_scores_count; $i++) {
          $key = $user_attempt_scores[$i];
          $this_user = $key[0];
          $this_attempt = $key[1];
          $this_score = $key[2];

          if (!array_key_exists('retest', $concept)) {
            $concept['retest'] = array();
            $concept['retest']['num'] = array();
            $concept['retest']['den'] = array();
          }
          if (!array_key_exists($this_concept, $concept['retest']['num'])) $concept['retest']['num'][$this_concept] = array();
          if (!array_key_exists($this_concept, $concept['retest']['den'])) $concept['retest']['den'][$this_concept] = array();
          if (!array_key_exists($sourcefile, $concept['retest']['num'][$this_concept])) $concept['retest']['num'][$this_concept][$sourcefile] = 0;
          if (!array_key_exists($sourcefile, $concept['retest']['den'][$this_concept])) $concept['retest']['den'][$this_concept][$sourcefile] = 0;

          $concept['retest']['num'][$this_concept][$sourcefile] += $this_score == 1 ? 1 : 0;
          $concept['retest']['den'][$this_concept][$sourcefile]++;

          if ($this_score == 1) {
            if (!isset($practice_time['retest']['success'][$this_concept][$course][$this_user])) {
              $practice_time['retest']['success'][$this_concept][$course][$this_user] = 1;
            }
            else {
              $practice_time['retest']['success'][$this_concept][$course][$this_user]++;
            }
          }
          else {
            if (!isset($practice_time['retest']['failure'][$this_concept][$course][$this_user])) {
              $practice_time['retest']['failure'][$this_concept][$course][$this_user] = 1;
            }
            else {
              $practice_time['retest']['failure'][$this_concept][$course][$this_user]++;
            }
          }
          if (!array_key_exists('den', $practice_time['retest'])) $practice_time['retest']['den'] = array();
          if (!array_key_exists($this_concept, $practice_time['retest']['den'])) $practice_time['retest']['den'][$this_concept] = array();
          if (!array_key_exists($this_user, $practice_time['retest']['den'][$this_concept][$course])) $practice_time['retest']['den'][$this_concept][$course][$this_user] = 0;
          $practice_time['retest']['den'][$this_concept][$course][$this_user]++;
	  $practice_time['retest']['assigned'][$this_concept][$course][$this_user] = true;
        }
      }
    }
  }

  $ret = array();
  $ret[0] = $concept;
  $ret[1] = $practice_time;
  return $ret;
}


function get_conceptBank_concepts_skills_additional_info($con) {

}

function get_conceptBank_concepts_skills($con) {
  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);
  $query = 'SELECT concept_bank, concept_content, stage, level FROM `conceptBankContentConcepts`';
  $result = mysql_query($query, $con);

  $concepts_skill = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $concept_bank = $row['concept_bank'];
    $concept_content = $row['concept_content'];
    $stage = $row['stage'];
    $level = $row['level'];

    $concepts_skill[$concept_content]++;
  }
  return $concepts_skill;
}

function get_grade_items_for_course($con, $course, $grade_items_array) {
  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);
  $query = 'SELECT id, course_gradeItem FROM `course_gradeItem`';
  $result = mysql_query($query, $con);

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $grade_items_array[$row['id']]['name'] = $row['course_gradeItem'];
  }

  $query = 'SELECT gradeItem_id FROM `courseGrades` WHERE ww_course="' . $course . '"';
  $result = mysql_query($query, $con);

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $grade_items_array[$row['gradeItem_id']]['course'][$course] = true;
  }
  
  return $grade_items_array;
}


//
//  print out colored boxes
//

function print_javascript_toggle_display() {
  print "<script type='text/javascript'>

  var ie = (document.all) ? true : false;

  function toggleClass(objClass){
    if(getElementByClass(objClass).style.display=='none') {
      showClass(objClass)
    }
    else {
      hideClass(objClass)
    }
  }

  function hideClass(objClass){
    var elements = (ie) ? document.all : document.getElementsByTagName('*');
    for (i=0; i<elements.length; i++) {
      if(elements[i].className==objClass){
        elements[i].style.display='none'
      }
    }
  }

  function showClass(objClass){
    var elements = (ie) ? document.all : document.getElementsByTagName('*');
    for (i=0; i<elements.length; i++) {
      if(elements[i].className==objClass){
        elements[i].style.display='block'
      }
    }
  }

  function getElementByClass(objClass){
    var elements = (ie) ? document.all : document.getElementsByTagName('*');
    for (i=0; i<elements.length; i++) {
      if(elements[i].className==objClass){
        return elements[i]
      }
    }
  }

  </script>
  ";
}

function print_circle($className, $left, $top, $width, $color, $title, $opacity=0.66) {
  echo "<div class='$className' style='position: absolute; left:" . $left . "px; top: " . $top . "px; width:" . $width . "px; height: " . $width . "px; opacity: " . $opacity . "; z-index: 2; background-color:" . $color . "; border:1px #CCC solid; -webkit-border-radius: " . $width . "px; -moz-border-radius: " . $width . "px; ' title='$title'></div>";
}

function print_band($className, $left, $top, $height, $color, $title) {
  echo "<div class='$className' style='position: absolute; left: " . $left . "px; top: " . $top . "px; width: 1px; height: " . $height . "px; opacity: 0.25; background-color:" . $color . "; z-index: 1;' title='" . $title . "'></div>";
}

function print_one_box() {

}

function print_two_boxes($init_width, $init_score_color, $init_title, $init_error, $second_width, $second_color, $second_title, $second_error, $second_error_title, $height = 15, $width_o="200", $bgcolor="#FFF", $border="1px #CCC solid;") {
  //The outside box:
  echo "<div style='width: " . $width_o . "px; height:" . $height . "px; background-color:" . $bgcolor . "; border:" . $border . "; '>";

  //The initial score box:
  echo "<div style='width:" . $init_width . "px; height:" . $height . "px; background-color:" . $init_score_color . ";' title='" . $init_title . "'>";

  //The change in score box:
  echo "<div style='position:relative; top:-1px; left:" . $init_width . "px; width:" . (-2 + $second_width) . "px; height: " . $height . "px; background-color:" . $bgcolor . "; border:1px " . $init_score_color . " solid;' title='" . $second_title . "'></div>";

  //The average Change error bar:
  echo "<div style='position:relative; top:" . (-$height + round($height/4,4)) . "px; left:" . (-1 + $init_width + $second_width - $second_error) . "px; width:" . (2*$second_error + 1) . "px; height:" . round($height/4, 4) . "px; background-color:" . $init_score_color . "; ' title='" . $second_error_title . "'></div>";

  //The color initial error bar:
  echo "<div style='position:relative; top:" . (-$height + 0) . "px; left:" . ($init_width - $init_error) . "px; width:" . (2*$init_error + 1) . "px; height:" . round($height/4,4) . "px; background-color:" . $init_score_color . ";'></div>";

  //The white initial error bar:
  echo "<div style='position:relative; top:" . (-$height - round($height/4,4)) . "px; left:" . ($init_width - $init_error) . "px; width:" . $init_error . "px; height:" . round($height/4, 4) . "px; background-color:" . $bgcolor . " ;'></div>";
  echo "</div></div>";
}


//
//  javascript to show / hide / show or hide content
//

function print_show_class_link($div_name, $name) {
  //print "<a onmouseover=\"showClass('" . $div_name . "'); return true;\" href=\"javascript:showClass('" . $div_name . "')\">[Show " . $name . "]</a>";
  echo "<a href=\"javascript:showClass('" . $div_name . "')\">[Show " . $name . "]</a>";
}

function print_hide_class_link($div_name, $name) {
  //print "<a onmouseover=\"hideClass('" . $div_name . "'); return true;\" href=\"javascript:hideClass('" . $div_name . "')\">[Hide" . $name . "]</a>";
  echo "<a href=\"javascript:hideClass('" . $div_name . "')\>[Hide " . $name . "]</a>";
}

function print_toggle_class_link($div_name, $name) {
  //print "<a onmouseover=\"toggleClass('" . $div_name . "'); return true;\" href=\"javascript:toggleClass('" . $div_name . "')\">[Show/Hide" . $name . "]</a>";
  echo "<a href=\"javascript:toggleClass('" . $div_name . "')\">[Show/Hide " . $name . "]</a>";
}

function print_naked_class_link($div_name, $name) {
  echo " <a href=\"javascript:toggleClass('" . $div_name . "')\">[" . $name . "]</a> ";
}
//
//  print out the standard form fields:
//


function print_course_checkboxes($courses) {
  for ($c = 0; $c < count($courses); $c++) {
    echo "<input name='report_for_this_course[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
  }
}

function print_quiz_checkboxes($quizName) {
  foreach ($quizName as $key => $quizName) {
    echo "<input name='report_for_this_quiz[]' type='checkbox' value='" . $quizName . "'>" . $quizName . "<BR>\n";
  }
}

function print_concept_skills($concepts_skill, $low_tol) {
   $cols = 4;
   $valid_skill = 0;
   foreach ($concepts_skill as $skill => $num) {
      if ($num > $low_tol) {  $valid_skill++; }
   }
   $num_per_col = floor($valid_skill / $cols);
   $row_count = 0;
   $count_cols = 0;
   echo "<script type='text/javascript' src='../../js/jscolor/jscolor.js'></script>\n";
   echo "<TABLE><TR><TD VALIGN='top'>";
   foreach ($concepts_skill as $skill => $num) {
      if ($num > $low_tol) {
        echo "<input class='color' name=\"Color For " . $skill . "\"' class=\"color {required:false}\" style='width:4em;' /><input name='report_for_this_skill[]' type='checkbox' value='" . $skill . "'>" . $skill. "(" . $num . ") <BR>\n";
        $row_count++;
      }
      if ($row_count > $num_per_col) {
        echo $count_cols == $cols ? "</TD>" : "</TD><TD VALIGN='TOP'>";
        $row_count = 0;
        $count_cols++;
      }
   }
   echo "</TD></TR></TABLE>";
}


function print_combined_quiz_skills_concepts($quizName, $concepts_skill, $low_tol) {
  $cols = 5;
//  print "<script type='text/javascript' src='../../js/jscolor/jscolor.js'></script>\n";
  print "<TABLE><TR>";
  for($i = 0; $i < $cols; $i++) {
    echo "<TD VALIGN='TOP'>\n";
    echo "Name: <input type='text' name='combined" . $i . "_name' /><BR>";
    echo "<select name='combined" . $i . "_skills[]' MULTIPLE>";
    foreach ($quizName as $key => $theQuizName) {
      echo "<option value='$theQuizName'>$theQuizName</option>\n";
    }
    foreach ($concepts_skill as $skill => $num) {
      if ($num > $low_tol) {
        echo "<option value='$skill'>$skill</option>\n";
      }
    }
    echo '</select>';
    echo '<BR>';
    echo "Color: <input class='color' name='combined" . $i . "_color' /><BR>";
    echo "</TD>\n";
  }
  echo "</TR></TABLE>\n";
}

function print_course_grade_items($con, $courses) {
  $max_courses = count($courses);

  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);

  $query = "SELECT DISTINCT ww_course, course_gradeItem, course_gradeItem.id FROM `course_grades` LEFT JOIN `course_wwUserPermissions` ON course_wwUserPermissions_id=course_wwUserPermissions.id  LEFT JOIN `course_wwValidCourses` ON course_wwValidCourses_id=course_wwValidCourses.id LEFT JOIN `course_gradeItem` ON course_gradeItem_id=course_gradeItem.id WHERE 1";
  $result = mysql_query($query, $con);

  $items_for_course = array();

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ww_course = $row['ww_course'];
    $gradeItem = $row['course_gradeItem'];
    $id = $row['id'];
    $items_for_course[$ww_course]["$id"] = $gradeItem;
  }
  print "<TABLE>";
  echo '<TR><TH>Course</TH><TH>Grade Item</TH></TR>';
  for($i = 0; $i < $max_courses; $i++) {
    echo '<TR><TD>' . $courses[$i] . '</TD><TD><SELECT name="gradeItemForCourse_' . $courses[$i] . '">'; //MULTIPLE
    echo '<option value=""></option>';
    foreach ($items_for_course[$courses[$i]] as $id => $gradeItem) {
      echo '<option value="' . $id . '">' . $gradeItem . '</option>';
    }
    echo '</SELECT></TD></TR>';
  }
  echo '</TABLE>';
}


function  print_time() {
  echo "<input name='time' type='checkbox' value='time' checked>time<BR>\n";
}

function print_review_sessions() {
  echo "<input name='review_sessions' type='checkbox' value='review_sessions'>Review Sessions<BR>\n";
}

function print_single_problems() {
  echo "<input name='single_problems' type='checkbox' value='single_problems' checked>Single Problem Success<BR>\n";
}

function print_retest_problems() {
  echo "<input name='retest_problems' type='checkbox' value='retest_problems'>Retest Problems<BR>\n";
}

function print_show_data() {
  echo "<input name='show_data' type='checkbox' value='show_data'>Show Data<BR>\n";}

function print_print_user_name() {
  echo "Print User Name: <input name='print_user_name' type='text' id='print_user_name' value='print_user_name'><BR>\n";
}

function print_no_zero_times() {
  echo "<input name='no_zero_times' type='checkbox' value='no_zero_times'>Remove times of zero from average times<BR>\n";
}

function print_select_student_groups() {
  $options = array();
  $options[] = "validUser";
  $options[] = "validForStatistics";
  $options[] = "finishedCourse";

  echo '<Table><TR><TD>Select Student Group: </TD><TD>';
  foreach ($options as $k => $v) {
    echo '<input name="studentCondition[]" type="checkbox" value="' . $v . '=1">' . $v . ' ';
  }
  echo '</TD></TR><TR><TD></TD><TD>';
  foreach ($options as $k => $v) {
    echo '<input name="studentCondition[]" type="checkbox" value="' . $v . '!=1">Not ' . $v . ' ';
  }
  echo '</TD><TR><TD></TD><TD>';
  echo 'Glue:  ';
  echo '<input name="studentConditionGlue" type="checkbox" value=" || "> OR ';
  echo '<input name="studentConditionGlue" type="checkbox" value=" && "> AND ';
  echo '</TD></TR></TABLE>';
}

function print_no_jitter() {
  echo "<input name='no_jitter' type='checkbox' value='no_jitter'>Remove Jitter effect<BR>\n";
}

function print_remove_std_dev_band() {
  echo "<input name='remove_std_dev_band' type='checkbox' value='remove_std_dev_band'>Remove Standard Deviation Band<BR>\n";
  echo "<input name='reg1' type='checkbox' value='reg1' CHECKED>Use linear regression line<BR>\n";
  echo "<input name='reg2' type='checkbox' value='reg2' CHECKED>Use quadratic regression curve<BR>\n";
  echo "<input name='reg3' type='checkbox' value='reg3' CHECKED>Use Cubic regression curve<BR>\n";
}


