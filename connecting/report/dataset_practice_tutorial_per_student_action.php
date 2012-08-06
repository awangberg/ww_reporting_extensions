<?php


include("../access.php");

include("is_valid_users.php");
include("common.php");

require_once 'Spreadsheet/Excel/Writer.php';


function pprint($line) {
  $do = 1;
  if ($do) { echo $line; }
}

function dprint($line) {
  $do = 0;
  if ($do) { echo $line; }
}

function ddprint($line) {
  $do = 1;
  if ($do) { echo $line; }
}

function dprint_r($array) {
  $do = 0;
  if ($do) { print_r($array); }
}

function clean_data($line) {
  return cleanData($line);
  return $line;
}

ini_set("memory_limit","2400000M");

//formatTime($seconds);
//cleanData($line);

$quizNames = get_quizNames();
$courseNames = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);


$user = array();

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

pprint("<a href='#pre_post_retest'>pre / post / retest</a>\n");
pprint("<a href='#tutorial_info'>tutorial_info</a>");
pprint("<a href='#all_info'>practice info</a>");


//get info from webwork:

$db = "webwork";

$interaction_record = array();
//$user = array();
//$conceptBank_to_quizSkill = array();
//$quizSkill_to_conceptBank = array();
//$concept = array();
//$practice_time = array();
$quizScores = array();
//$courseNames = array();
//$courseNames[0] = "Math160_S2010_awangberg_05";

$limit = "";  //" LIMIT 100"; //" LIMIT 600";  //" LIMIT 16000";

//get the users in webwork for each course:
foreach ($courseNames as $k => $courseName) {
  //get the users in webwork for each course:
  $user = get_users_from_course($con, $courseName, $user);

  //get the initial scores for each user for each quiz:
  $db = "webwork";
  $result = mysql_select_db("$db", $con);
  foreach ($quizNames as $k => $quiz) {
    $query = 'SELECT ' . $courseName . '_problem_user.user_id, ' . $courseName . '_problem.source_file as concept, ' . $courseName . '_problem_user.problem_id, status, attempted, ' . $courseName . '_problem_user.source_file as source_file FROM `' . $courseName . '_problem` LEFT JOIN `' . $courseName . '_problem_user` ON CONCAT(' . $courseName . '_problem.set_id, ",v1") = ' . $courseName . '_problem_user.set_id AND ' . $courseName . '_problem.problem_id = ' . $courseName . '_problem_user.problem_id WHERE ' . $courseName . '_problem.set_id = "' . $quiz . '"' . $limit;
    //dprint $query . "<BR>";
ddprint("A:  query is $query\n");
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      $this_problem_id = $row['problem_id'];
      $this_score = $row['status'];
      $this_attempt = $row['attempted'];
      $this_concept = $row['concept'];
ddprint("got concept $this_concept\n");
      $this_problem_source = $row['source_file'];

      $search_string = "|" . $this_user . "|" . $quiz . "|" . $this_problem_id . "|";
      //dprint "\n trying grep \"$search_string\" /opt/webwork/courses/$courseName/logs/answer_log | grep submit";
      $reply = `grep "$search_string" /opt/webwork/courses/$courseName/logs/answer_log | grep submit`;
      $a = explode("\t", $reply);
      $b = explode("|", $a[0]);
      if(count($b) >= 4) {
        $counts = count_chars($b[4]);
        $num = $counts[ord("1")];
        $den = $num + $counts[ord("0")];
      }
      else {
        $num = "NA";
        $den = "NA";
      }
      $quiz_scores[$courseName][$this_user][$this_concept]['pre']['num'] = $num;
      $quiz_scores[$courseName][$this_user][$this_concept]['pre']['den'] = $den;
      $quiz_scores[$courseName][$this_user][$this_concept]['pre']['percent'] = $this_score;
      $quiz_scores[$courseName][$this_user][$this_concept]['pre']['attempted'] = $this_attempt == 1 ? "Yes" : "No Attempt";
    }
  }

  //get the final scores for each user for each quiz:
  foreach ($user[$courseName] as $this_user) {
    foreach ($quizNames as $k => $quiz) {
      $query = 'SELECT user_id, ' . $courseName . '_problem.source_file as concept, ' . $courseName . '_problem_user.problem_id, status, attempted, ' . $courseName . '_problem_user.source_file as problem_file FROM `' . $courseName . '_problem` LEFT JOIN `' . $courseName . '_problem_user` ON CONCAT(' . $courseName . '_problem.set_id, ",v1") = ' . $courseName . '_problem_user.set_id AND ' . $courseName . '_problem.problem_id = ' . $courseName . '_problem_user.problem_id WHERE ' . $courseName . '_problem.set_id = "finalQuiz_' . $this_user . '_' . $quiz . '"' . $limit;

ddprint("C: query is $query\n");
      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_problem_id = $row['problem_id'];
        $this_score = $row['status'];
        $this_attempt = $row['attempted'];
        $this_concept = $row['concept'];
ddprint("Got $this_concept from query\n");
        $this_problem_source = $row['problem_file'];

        $search_string = "|" . $this_user . "|finalQuiz_" . $this_user . "_" . $quiz . "|" . $this_problem_id . "|";
        $reply = `grep "$search_string" /opt/webwork/courses/$courseName/logs/answer_log | grep submit`;
        $a = explode("\t", $reply);
        $b = explode("|", $a[0]);
        if(count($b) >= 4) {
          $counts = count_chars($b[4]);
          $num = $counts[ord("1")];
          $den = $num + $counts[ord("0")];
        }
        else {
          $num = "NA";
          $den = "NA";
        }
        $quiz_scores[$courseName][$this_user][$this_concept]['post']['num'] = $num;
        $quiz_scores[$courseName][$this_user][$this_concept]['post']['den'] = $den;
        $quiz_scores[$courseName][$this_user][$this_concept]['post']['percent'] = $this_score;
        $quiz_scores[$courseName][$this_user][$this_concept]['post']['attempted'] = $this_attempt == 1 ? "Yes" : "No Attempt";
      }
    }
  }


  //get the retest scores for the student
  foreach($quizNames as $k => $quiz) {
    $retest_sourcefile_user_attempt_score = array();
    $query = 'SELECT user_id, ' . $courseName . '_problem.set_id, ' . $courseName . '_problem_user.source_file, ' . $courseName . '_problem_user.problem_id, status, attempted FROM `' . $courseName . '_problem` LEFT JOIN `' . $courseName . '_problem_user` ON CONCAT(' . $courseName . '_problem.set_id, ",v1") = ' . $courseName . '_problem_user.set_id AND ' . $courseName . '_problem.problem_id = ' . $courseName . '_problem_user.problem_id WHERE ' . $courseName . '_problem.set_id REGEXP "^finalQuiz" AND ' . $courseName . '_problem.set_id REGEXP "' . $quiz . '" AND ' . $courseName . '_problem.source_file REGEXP "tcerroc"' . $limit;
ddprint("D:  query is $query\n");

    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      $this_problem_id = $row['problem_id'];
      $this_score = $row['status'];
      $this_attempt = $row['attempted'];
      $this_sourcefile = $row['source_file'];

      $search_string = "|" . $this_user . "|finalQuiz_" . $this_user . "_" . $quiz . "|" . $this_problem_id . "|";
      $reply = `grep "$search_string" /opt/webwork/courses/$courseName/logs/answer_log | grep submit`;
      $a = explode("\t", $reply);
      $b = explode("|", $a[0]);
      if(count($b) >= 4) {
        $counts = count_chars($b[4]);
        $num = $counts[ord("1")];
        $den = $num + $counts[ord("0")];
      }
      else {
        $num = "NA";
        $den = "NA";
      }

      if (is_valid_user($this_user)) {
        $retest_sourcefile_user_attempt_score[$this_sourcefile][] =  array($this_user, $this_attempt, $this_score, $num, $den);
      }
    }


    foreach ($retest_sourcefile_user_attempt_score as $sourcefile => $user_attempt_scores ) {
      $query = 'SELECT set_id FROM `' . $courseName . '_problem` WHERE source_file="' . $sourcefile . '" AND NOT set_id REGEXP "finalQuiz" AND NOT set_id REGEXP "tcerroc" AND NOT set_id REGEXP "practice"' . $limit;

      $result = mysql_query($query, $con);
ddprint("E: query is $query\n");
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_concept = "group:" . $row['set_id'];
ddprint("----> concept: $this_concept\n");
        if (($this_concept == "group:MasterPlottingProblems3") || ($this_concept == "group:ExtraPractice_kdufour08_Basics") || ($this_concept == "group:ExtraPractice_jtanderson06_Graphs")) {
          //do nothing.  Not a valid concept bank.
ddprint("Doing nothing: concept: $this_concept\n");
        }
        else {
          //fo each entry in the $retest_sourcefile_user_attempt_score[$sourcefile] array...
          for ($i = 0; $i < count($user_attempt_scores); $i++) {
            $key = $user_attempt_scores[$i];
            $this_user = $key[0];
            $this_attempt = $key[1];
            $this_score = $key[2];
            $num = $key[3];
            $den = $key[4];

            $quiz_scores[$courseName][$this_user][$this_concept]['retest']['num'][] = $num;
            $quiz_scores[$courseName][$this_user][$this_concept]['retest']['den'][] = $den;
            $quiz_scores[$courseName][$this_user][$this_concept]['retest']['percent'][] = $this_score;
            $quiz_scores[$courseName][$this_user][$this_concept]['retest']['attempted'][] = $this_attempt == 1 ? "Yes" : "No Attempt";
          }
        }
      }
    }
  }

}

//print "Data for AJohnson08\n";
//print_r($quiz_scores['Math160_S2010_eerrthum']['AJohnson08']);
//print "|||||||||||||||||||||||||||||||||||||||||||||||||||||||\n";
//print_r($quiz_scores['Math160_S2010_eerrthum']);
//print "___________________________________________________________________\n";


if (0) {

echo "<a name='pre_post_retest'></a>";
echo "<TABLE>";
echo "<TR><TD>Course</TD><TD>User</TD><TD>Concept</TD><TD>Pre N</TD><TD>Pre D</TD><TD>Pre %</TD><TD>Pre Attempt</TD><TD>Post N</TD><TD>Post D</TD><TD>Post %</TD><TD>Post Attempt</TD><TD>Retest N1</TD><TD>Retest D1</TD><TD>Retest %1</TD><TD>Retest Attempt1</TD><TD>Retest N2</TD><TD>Retest D2</TD><TD>Retest %2</TD><TD>Retest Attemp2</TD></TR>";
foreach($quiz_scores as $this_course => $userData) {
  foreach($userData as $this_user => $cb_data) {
    foreach($cb_data as $this_concept => $data) {
      if (is_valid_user($this_user) AND !(preg_match("/tcerroc/", $this_concept))) {
        echo "<TR><TD>$this_course</TD><TD>$this_user</TD><TD>$this_concept</TD>";
        echo "<TD>" . $data['pre']['num'] . "</TD>";
        echo "<TD>" . $data['pre']['den'] . "</TD>";
        echo "<TD>" . $data['pre']['percent'] . "</TD>";
        echo "<TD>" . $data['pre']['attempted'] . "</TD>";
        echo "<TD>" . $data['post']['num'] . "</TD>";
        echo "<TD>" . $data['post']['den'] . "</TD>";
        echo "<TD>" . $data['post']['percent'] . "</TD>";
        echo "<TD>" . $data['post']['attempted'] . "</TD>";
        if (array_key_exists('retest', $data)) {
          for ($i = 0; $i <= 1; $i++) {
            echo "<TD>";
            echo array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "";
            echo "</TD>";
            echo "<TD>";
            echo array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "";
            echo "</TD>";
            echo "<TD>";
            echo array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "";
            echo "</TD>";
            echo "<TD>";
            echo array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "";
            echo "</TD>";
          }
        }
        else { echo '<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>'; }
        echo '</TR>';
      }
    }
  }
}
echo '</TABLE>';

}

dprint("<P>DONE WITH pretest/posttest data</P>");

$db = "wwSession";
//select the database $db:
$result = mysql_select_db("$db", $con);

$saw_tutorials = array();
$practice = array();

//$tmp_course = " AND course_name='Math160_S2010_awangberg_05'";

foreach ($courseNames as $keyk => $courseName) {
  $tmp_course = " AND course_name='" . $courseName . "'";

  //get the practice information for each user from wwSession.
  $query = "SELECT course_name, user_name, concept_bank, pg_sourcefile, submitted_date, attempted_date, submitted_answer, was_successful  FROM `attempts` WHERE submitted_date != '0000-00-00 00:00:00'" . $tmp_course . $limit;
  $result = mysql_query($query, $con);

  dprint($query . "\n<BR>\n");
  $count_of_records = 0;

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $count_of_records++;
    if ($count_of_records % 300 == 0) { print "$courseName: " . $count_of_records;  print "\n"; }
    $this_course = $row['course_name'];
    $this_user = $row['user_name'];
    $this_concept_bank = $row['concept_bank'];
    $this_pg_sourcefile = $row['pg_sourcefile'];
    $this_submitted_date = $row['submitted_date'];
    $this_attempted_date = $row['attempted_date'];
    $submitted_answer = $row['submitted_answer'];
    $was_successful = $row['was_successful'];
    $this_date_submitted = $this_submitted_date;
    $time_practiced = date(strtotime($this_submitted_date)) - date(strtotime($this_attempted_date));
    $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['time_practiced'] = $time_practiced;
    $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['answer'] = $submitted_answer;
    $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['boolean_success'] = $was_successful;
    if (!array_key_exists('total_incorrect_attempts', $practice[$this_course][$this_user][$this_concept_bank])) {
      $practice[$this_course][$this_user][$this_concept_bank]['total_incorrect_attempts'] = 0;
    }
    $practice[$this_course][$this_user][$this_concept_bank]['total_incorrect_attempts'] += 1 - $was_successful;
    //search in /opt/webwork/courses/$course_name/logs/answer_log for
    //a record which is from user $this_user,
    //                  from set 'practice_$this_user_'
    //                  with timestamp matching  $this_date_submitted, but converted to 'Sun Sep 05 17:2'
    //                  and check that answer matches $submitted_answer
  
    $hms = explode(" ", $this_submitted_date);
    $hhmmss = $hms[1];
  
    $reply = `grep $this_user /opt/webwork/courses/$this_course/logs/answer_log | grep $hhmmss`;
    $a = explode("\t", $reply);
    $b = explode("|", $a[0]);
    array_shift($a);
    array_shift($a);
    if (count($b) >= 4) {
      $answer = implode("\t", $a);
      $counts = count_chars($b[4]);
      $num = $counts[ord("1")];
      $den = $num + $counts[ord("0")];
      if (trim($answer) == trim($submitted_answer)) {
        $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = $num;
        $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = $den;
      }
      else {
        $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = "NA";
        $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = "NA";
      }
    }
    else {
      //try one second later
      $hourMinuteSecond = explode(":", $hhmmss);
      $seconds = 60*60*$hourMinuteSecond[0] + 60*$hourMinuteSecond[1] + $hourMinuteSecond[2];
      $hhmmss = formatTime($seconds + 1);

      $reply = `grep $this_user /opt/webwork/courses/$this_course/logs/answer_log | grep $hhmmss`;
      $a = explode("\t", $reply);
      $b = explode("|", $a[0]);
      array_shift($a);
      array_shift($a);
      if (count($b) >= 4) {
        $answer = implode("\t", $a);
        $counts = count_chars($b[4]);
        $num = $counts[ord("1")];
        $den = $num + $counts[ord("0")];
        if (trim($answer) == trim($submitted_answer)) {
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = $num;
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = $den;
        }
        else {
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = "NA";
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = "NA";
        }
      }
      else {
        //or one second sooner
        $hhmmss = formatTime($seconds - 1);

        $reply = `grep $this_user /opt/webwork/courses/$this_course/logs/answer_log | grep $hhmmss`;
        $a = explode("\t", $reply);
        $b = explode("|", $a[0]);
        array_shift($a);
        array_shift($a);
        if (count($b) >= 4) {
          $answer = implode("\t", $a);
          $counts = count_chars($b[4]);
          $num = $counts[ord("1")];
          $den = $num + $counts[ord("0")];
          if (trim($answer) == trim($submitted_answer)) {
            $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = $num;
            $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = $den;
          }
          else {
            $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = "NA";
            $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = "NA";
          }
        }
        else {
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['num_score'] = "NA";
          $practice[$this_course][$this_user][$this_concept_bank][$this_date_submitted]['den_score'] = "NA";
        }
      }
    }
  }
}


dprint("Get information about seeing tutorials for concept banks\n");

$query = "SELECT * FROM `sawTutorialForConceptBank`" . $limit;
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $this_course = $row['course_name'];
  $this_user = $row['user_name'];
  $this_concept_bank = $row['concept_bank'];
  $this_tutorial_problem_id = $row['session_problem_id'];
  $this_students_answer_id = $row['answer_id'];
  $this_date_viewed = $row['date_viewed'];
  $saw_tutorials[$this_course][$this_user][$this_concept_bank]['date_viewed'][$this_date_viewed]['tutorial_id'] = $this_tutorial_problem_id;
  $saw_tutorials[$this_course][$this_user][$this_concept_bank]['date_viewed'][$this_date_viewed]['student_answer_id'] = $this_students_answer_id;
  $saw_tutorials[$this_course][$this_user][$this_concept_bank]['shown_tutorial'] = 1;
}


pprint("<P><a name='tutorial_info'></a><P>\n");

pprint("<TABLE><TR><TD>Course</TD><TD>User</TD><TD>Concept Bank</TD><TD>Shown?</TD><TD>Date</TD><TD>Tutorial_id</TD><TD>Answer_id</TD><TD></TR>\n");
foreach ($saw_tutorials as $this_course => $val_user) {
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept_bank => $tut_view_data) {
      $shown = $tut_view_data['shown_tutorial'];
      foreach ($tut_view_data['date_viewed'] as $this_date => $viewed_data) {
        pprint("<TR><TD>$this_course</TD><TD>$this_user</TD><TD>$this_concept_bank</TD><TD>$shown</TD><TD>$this_date</TD>");
        pprint("<TD>" . $viewed_data['tutorial_id'] . "</TD>");
        pprint("<TD>" . $viewed_data['student_answer_id'] . "</TD>");
        pprint("</TR>");
      }
    }
  }
}


pprint("</TABLE>\n");

$skill_area = array();
$skill_area['specific']['index']['blah'] = 0;
$skill_area['general']['index']['blah'] = 0;
$query = "SELECT conceptBankContentConcepts.concept_bank, conceptBankContentConcepts.concept_content, conceptBankContentAreas.concept_area FROM `conceptBankContentConcepts` LEFT JOIN `conceptBankContentAreas` ON conceptBankContentConcepts.concept_content = conceptBankContentAreas.concept_content";
$result = mysql_query($query, $con);
$specific_count = 1;
$general_count = 1;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $this_concept_bank = $row['concept_bank'];
  $this_concept_specific = $row['concept_content'];
  $this_concept_area = $row['concept_area'];
  pprint("------------------------------------------\n"); 
  dprint_r($skill_area);

  $this_specific_index = array_key_exists($this_concept_specific, $skill_area['specific']['index']) ? $skill_area['specific']['index'][$this_concept_specific] : -1;
  pprint("this_specific_index is now $this_specific_index\n");
  if ($this_specific_index == -1) {
    pprint("  setting skill_area['specific']['index'][$this_concept_specific] to $specific_count\n");
    $skill_area['specific']['index'][$this_concept_specific] = $specific_count;
    $this_specific_index = $specific_count;
    $specific_count = $specific_count + 1;
    pprint("  and increasing specific_count to $specific_count\n");
  }
  $skill_area['specific'][$this_concept_bank][$this_concept_specific] = $this_specific_index;
  pprint("just set ['specific'][$this_concept_bank][$this_concept_specific] = $this_specific_index\n");

  $this_general_index = array_key_exists($this_concept_area, $skill_area['general']['index']) ? $skill_area['general']['index'][$this_concept_area] : -1;
  pprint("this_general_index is now $this_general_index\n");
  if ($this_general_index == -1) {
    pprint("  setting skill_area['general']['index'][$this_concept_specific] to $general_count\n");
    $skill_area['general']['index'][$this_concept_area] = $general_count;
    $this_general_index = $general_count;
    $general_count = $general_count + 1;
    pprint("  and increasing general_count to $general_count\n");
  }
  $skill_area['general'][$this_concept_bank][$this_concept_area] = $this_general_index;
  pprint("just set ['general'][$this_concept_bank][$this_concept_specific] = $this_general_index\n");

  pprint("Finished with specific $this_concept_specific, which has index " . $skill_area['specific'][$this_concept_bank][$this_concept_specific] . "\n");
  pprint("Just got done with general $this_concept_area, which has index " . $skill_area['general'][$this_concept_bank][$this_concept_area] . "\n");
}


ddprint("\nskill area is:\n");
dprint_r($skill_area);

mysql_close($con);


dprint("------------------------\n");

$con = mysql_connect($db_host, $db_user, $db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//get the tutorial interactions for each user from session:
$db = 'session';

$result = mysql_select_db("$db", $con);


$tutorial_tree = array();
$tutorial_student_response = array();

$tutorial_available_for_concept_bank = array();



//get the tutorials that could be seen by the user:
$query = "SELECT ww_set_id as concept_bank, session_problem_id as tutorial_id FROM `wwSetToSessionTutorial` LEFT JOIN `wwSetInSession` ON wwSetToSessionTutorial.ww_set_and_course_id = wwSetInSession.ww_set_and_course_id WHERE valid_tutorial = 'YES'";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $concept_bank = $row['concept_bank'];
  $tutorial_id = $row['tutorial_id'];
  $tutorial_available_for_concept_bank[$concept_bank] = $tutorial_id;
}


//for each user interaction with the session tutorial, follow their trail until we've exhausted the search for that user:
$can_do = 1;

ddprint("<P>FOLLOW USER INTERACTIONS WITH THE SESSION TUTORIAL<P>\n");
foreach ($saw_tutorials as $this_course => $val_user) {
ddprint("T: this course: $this_course<BR>\n");
  foreach ($val_user as $this_user => $val_cb) {
ddprint("UU: this user: $this_user<BR>\n");
    foreach ($val_cb as $this_concept_bank => $tut_view_data) {
ddprint("VVV: this_concept_bank: $this_concept_bank<BR>\n");
      $shown = $tut_view_data['shown_tutorial'];
      foreach ($tut_view_data['date_viewed'] as $this_date_viewed => $data4) {
ddprint("WWWW: shown_tutorial: $shown on date_viewed: $this_date_viewed<BR>\n");
        $this_tutorial_id = $data4['tutorial_id'];
        $this_student_answer_id = $data4['student_answer_id'];

        if (array_key_exists($this_tutorial_id, $tutorial_tree)) {
ddprint("Tutorial information for tutorial $this_concept_bank already in this_tutorial_id<BR>");
        }
        else {
	  //get the tutorial information for tutorial_id $this_tutorial_id:

          $query = "SELECT initial_draw_id, initial_prompt_decision_key, initial_review_decision_key, name, comment, type, ww_set_id  FROM `problem` LEFT JOIN `wwSetToSessionTutorial` ON problem.problem_id = wwSetToSessionTutorial.session_problem_id LEFT JOIN `wwSetInSession` ON wwSetToSessionTutorial.ww_set_and_course_id = wwSetInSession.ww_set_and_course_id WHERE problem.problem_id=" . $this_tutorial_id; 

ddprint("\n\nA query:\n  $query\n<BR>");

	  $result = mysql_query($query, $con);
	  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $this_title = $row['name'];
            $this_draw_id = $row['initial_draw_id'];
            $this_prompt_key = $row['initial_prompt_decision_key'];
            $this_review_key = $row['initial_review_decision_key'];
            $this_type = $row['type'];
ddprint("\nRecord: this_draw_id = $this_draw_id\n<BR>");
            if (!array_key_exists($this_tutorial_id, $tutorial_tree)) { $tutorial_tree[$this_tutorial_id]['blah'] = "BLAH"; }
            $tutorial_tree[$this_tutorial_id]['name'] = $this_title;
            $tutorial_tree[$this_tutorial_id]['draw_parts'] = 0;
            $tutorial_tree[$this_tutorial_id]['prompt_parts'] = 0;
            $tutorial_tree[$this_tutorial_id]['draw'][1]['id'] = $this_draw_id;
            $tutorial_tree[$this_tutorial_id]['prompt_decision'][1]['key'] = $this_prompt_key;
            $tutorial_tree[$this_tutorial_id]['review_decision'][1]['key'] = $this_review_key;
            $tutorial_tree[$this_tutorial_id]['webwork_concept_bank'] = $row['ww_set_id'];
          }

          $part = 0;
ddprint("\nthis_draw_id = $this_draw_id and this_prompt_key = $this_prompt_key.  Going to trace this now.\n<BR>");
	  while (($this_draw_id > 0) || ($this_prompt_key > 0) ) {
            $part++;
            //get the current prompt info:
            if ($this_prompt_key > 0) {
              $query = "SELECT prompt_id FROM `prompt_decision` WHERE prompt_decision_key=$this_prompt_key";
ddprint("\n prompt_key_query: $query\n<BR>");
              $result = mysql_query($query, $con);
              while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $this_prompt_id = $row['prompt_id'];
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['id'] = $this_prompt_id;
              }

              $query = "SELECT name, data, type, classification, question_word FROM `prompt` LEFT JOIN `blooms_classification` ON prompt.blooms_classification_id = blooms_classification.id LEFT JOIN `blooms_question` ON prompt.blooms_question_id = blooms_question.blooms_question_id WHERE prompt_id = $this_prompt_id";
ddprint("\n 489: prompt query: $query<BR>\n");
              $result = mysql_query($query, $con);
              while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $this_prompt_name = $row['name'];
                $this_prompt_data = $row['data'];
                $this_prompt_type = $row['type'];
                $this_prompt_classification = $row['classification'];
                $this_prompt_blooms_word = $row['question_word'];
                if (!array_key_exists($part, $tutorial_tree[$this_tutorial_id]['prompt_id'])) { $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['BLAH'] = 'BLAH'; }
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['name'] = addslashes($this_prompt_name);
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['data'] = $this_prompt_data;
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['type'] = $this_prompt_type;
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['classification'] = $this_prompt_classification;
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['blooms_word'] = $this_prompt_blooms_word;
              }
              $tutorial_tree[$this_tutorial_id]['prompt_parts'] += 1;
            }  

            //get the current draw info, and
            //put the next draw and prompt info into $this_draw_id and $this_prompt_key
            if ($this_draw_id > 0) {
              $query = "SELECT name, default_next_draw_id, default_next_prompt_decision_key FROM `draw` WHERE draw_id=$this_draw_id";
ddprint("draw_id query: $query<BR>");
              $result = mysql_query($query, $con);
              while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $this_draw_name = $row['name'];
                $this_draw_id = $row['default_next_draw_id'];
                $this_prompt_key = $row['default_next_prompt_decision_key'];
                $tutorial_tree[$this_tutorial_id]['draw'][$part]['name'] = $this_draw_name;
              }
              $tutorial_tree[$this_tutorial_id]['draw_parts']++;
            }
          }
        }

ddprint("The tutorial_tree for this_tutorial_id=$this_tutorial_id is:\n<BR>");
dprint_r($tutorial_tree[$this_tutorial_id]);
ddprint("\n<BR>");

        //get the tutorial student's answers:
        //$this_student_answer_id
        $part = 0;
        while ($this_student_answer_id > 0) {
ddprint("Tracking down this_student_answer_id = $this_student_answer_id\n<BR>");
          $part++;
          $query = "SELECT answer, askForReason, reason, points, possible_points, next_answer_id, date, to_see_problem, to_submit_answer, to_submit_reason, to_view_answer, to_view_reason, to_view_all_answers, to_view_all_reasons, to_draw, viewed.problem_id, viewed.draw_id, viewed.prompt_id FROM `student_answer` LEFT JOIN `viewed` ON student_answer.viewed_key = viewed.view_id WHERE answer_id=" . $this_student_answer_id . " LIMIT 1";
ddprint("<BR>D: query $query</BR>");
          $this_student_answer_id = 0;
          $result = mysql_query($query, $con);
          while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $this_answer = $row['answer'];
            $askForReason = $row['askForReason'];
            $reason = $row['reason'];
            $points = $row['points'];
            $possible_points = $row['possible_points'];
            $next_answer_id = $row['next_answer_id'];
            $date = $row['date'];
            $to_see_problem = trim($row['to_see_problem']);
            $to_submit_answer = trim($row['to_submit_answer']);
            $to_submit_reason = trim($row['to_submit_reason']);
            $to_view_answer = trim($row['to_view_answer']);
            $to_view_reason = trim($row['to_view_reason']);
            $to_view_all_answers = trim($row['to_view_all_answers']);
            $to_view_all_reasons = trim($row['to_view_all_reasons']);
            $to_draw = trim($row['to_draw']);
            $tutorial_id = $row['problem_id'];
            $draw_id = $row['draw_id'];
            $prompt_id = $row['prompt_id'];

            $student_thinking_interaction_time = 0;
            $student_thinking_interaction_time += $to_submit_answer    > 0 ? $to_submit_answer : 0;
            $student_thinking_interaction_time += $to_submit_reason    > 0 ? $to_submit_reason : 0;
            $student_thinking_interaction_time += $to_view_answer      > 0 ? $to_view_answer : 0;
            $student_thinking_interaction_time += $to_view_reason      > 0 ? $to_view_reason : 0;
            $student_thinking_interaction_time += $to_view_all_answers > 0 ? $to_view_all_answers : 0;
            $student_thinking_interaction_time += $to_view_all_reasons > 0 ? $to_view_all_reasons : 0; 

            $tutorial_for_concept = array_key_exists($tutorial_id, $tutorial_tree) ? $tutorial_tree[$tutorial_id]['webwork_concept_bank'] : "No Concept Bank from no available tutorial id";

            //record the information
ddprint("recording information in tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]<BR>\n");
ddprint("to_submit_answer is _$to_submit_answer.  Check out what we are recording\n");
ddprint("to_submit_reason is _$to_submit_reason.  Check out what we are recording\n");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response']['parts_viewed'] = $part;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response']['tutorial_id'] = $tutorial_id;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['draw_id'] = $draw_id;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['prompt_id'] = $prompt_id;

            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['answer'] = $this_answer;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['askForReason'] = $askForReason;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['reason'] = $reason;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['points'] = $points;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['possible_points'] = $possible_points;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['date'] = $date;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_see_problem'] = $to_see_problem;
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_submit_answer'] = $to_submit_answer > 0 ? $to_submit_answer : ($to_submit_answer == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_submit_reason'] = $to_submit_reason > 0 ? $to_submit_reason : ($to_submit_reason == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_answer'] = $to_view_answer > 0 ? $to_view_answer : ($to_view_answer == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_reason'] = $to_view_reason > 0 ? $to_view_reason : ($to_view_reason == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_all_answers'] = $to_view_all_answers > 0 ? $to_view_all_answers : ($to_view_all_answers == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_all_reasons'] = $to_view_all_reasons > 0 ? $to_view_all_reasons : ($to_view_all_reasons == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_draw'] = $to_draw > 0 ? $to_draw : ($to_draw == -1 ? "Declined Option" : "No Option");
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['thinking_time'] = $student_thinking_interaction_time > 0 ? $student_thinking_interaction_time : "No Interaction";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['total_time'] = $student_thinking_interaction_time + $to_see_problem;

dprint_r($tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]);
            $this_student_answer_id = $next_answer_id;
          }
ddprint("ZZZZZZ\n");
        }
ddprint("YYYYY\n");
      }
ddprint("XXXX\n");
    }
ddprint("WWW\n");
  }
ddprint("VV\n");
}
ddprint("U\n");
ddprint("T\n");

//foreach ($tutorial_tree as $tutorial_id => $tut_data) {
//  $tut_name = $tut_data['name'];
//  $tut_draw_parts = $tut_data['draw_parts'];
//  $tut_prompt_parts = $tut_data['prompt_parts'];
//  $ww_cb = $tut_data['webwork_concept_bank'];
//  foreach ($tut_data['draw'] as $draw_part => $this_draw_id) {
//
//  }
//}

mysql_close($con);

//now have tutorial information in $tutorial_tree array
//and student viewing information in $tutorial_student_response array.


pprint("<P></P><a name='all_info'></a>");

$csv_data = array();

$csv_data[] = array('Course', 'User', 'Concept Bank', 'Pre Num', 'Pre Den', 'Pre %', 'Pre Attempt', 'Post Num', 'Post Den', 'Post %', 'Post Attempt', 'Retest Num1', 'Retest Den1', 'Retest %1', 'Retest Attempt1', 'Retest Num2', 'Retest Den2', 'Retest %2', 'Retest Attempt2', 'Practice Data', 'Seconds Practiced', 'Answer', 'Success', 'Practice Num', 'Practice Den', 'Tutorial Available', 'Viewed Tutorial', 'Parts Viewed', 'Tutorial Parts', 'Tutorial Think Time', 'Total Tutorial Time', 'Tutorial State Group');


$xls = new Spreadsheet_Excel_Writer('student_interactions.xls');
$sheet =& $xls->addWorkSheet("Student_Interactions");
$tut_sheet =& $xls->addWorkSheet("Tutorial_Interactions");

pprint("<TABLE><TR>");
$c = 0;
foreach ($csv_data as $akey => $val_arr) {
  foreach ($val_arr as $afield) {
    pprint("<TD>$afield</TD>");
    $sheet->write(0,$c, $afield);
    $c++;
  }
}
foreach ($skill_area['general']['index'] as $this_concept_area => $index) {
    $this_index_here = 32 + $index;
    $sheet->write(0, $this_index_here, "general_skill_" . $this_concept_area);
    //$sheet->write(0, $this_index_here , "$index. general_skill_" . $this_concept_area);
}

$offset = count($skill_area['general']['index']);
foreach ($skill_area['specific']['index'] as $this_concept_specific => $index) {
    $this_index_here = 32 + 1 + $index + $offset;
    $sheet->write(0, $this_index_here, "specific_skill_" . $this_concept_specific);
    //$sheet->write(0, $this_index_here, "$index. specific_skill_" . $this_concept_specific . " o = $offset");
}


if(0){
print "<TD>Course</TD>";		//0
print "<TD>User</TD>";
print "<TD>Concept Bank</TD>";
print "<TD>Pre Num</TD>";		//3
print "<TD>Pre Den</TD>";
print "<TD>Pre %</TD>";
print "<TD>Pre Attempt</TD>";		//6
print "<TD>Post Num</TD>";
print "<TD>Post Den</TD>";
print "<TD>Post %</TD>";		//9
print "<TD>Post Attempt</TD>";
print "<TD>Retest Num1</TD>";
print "<TD>Retest Den1</TD>";		//12
print "<TD>Retest %1</TD>";
print "<TD>Retest Attempt1</TD>";
print "<TD>Retest Num2</TD>";		//15
print "<TD>Retest Den2</TD>";
print "<TD>Retest %2</TD>";
print "<TD>Retest Attempt2</TD>";	//18
print "<TD>Practice Date</TD>";
print "<TD>Time Practiced</TD>";
print "<TD>Answer</TD>";		//21
print "<TD>Success</TD>";
print "<TD>Practice Num</TD>";
print "<TD>Practice Den</TD>";		//24
print "<TD>Tut. Avail</TD>";
print "<TD>saw Tut.</TD>";
print "<TD>Parts Viewed</TD>";		//27
print "<TD>Tut. Parts</TD>";
print "<TD>Think Time</TD>";
print "<TD>Total Tutorial Time</TD>";	//30
print "<TD>Tutorial State Group</TD>";  //31
print "</TR>";
}

$r = 0;

//print "JUST BEFORE WRITING DATA TO DATABASE:\n";
//print "Data for AJohnson08\n";
//print_r($quiz_scores['Math160_S2010_eerrthum']['AJohnson08']);
//print "|||||||||||||||||||||||||||||||||||||||||||||||||||||||\n";
//print_r($quiz_scores['Math160_S2010_eerrthum']);
//print "___________________________________________________________________\n";


//print "PROVIDING INFORMATION ABOUT USERS:\n";
//print_r($quiz_scores);
//print "DONE PROVIDING INFORMATION ABOUT USERS\n";

//foreach ($quiz_scores as $this_course => $val_user) {
//  foreach ($val_user as $this_user => $val_cb) {
//    print "ZZZZZZZZZZZZZZ:  User: $this_user   -> ";
//    $tmp_bbbbbbbbbb = 0;
//    foreach ($val_cb as $this_concept => $data) {
//    $tmp_bbbbbbbbbb++;
//    }
//    print "$tmp_bbbbbbbbbb\n";
//  }
//}

foreach ($quiz_scores as $this_course => $val_user) {
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept => $data) {
//print "user: ||$this_user|| -> concept: ||$this_concept||\n";

      $no_group_cb = preg_replace("/group\:/", "", $this_concept);
      if (is_valid_user($this_user) AND !(preg_match("/tcerroc/", $this_concept))) {
        if (
            (array_key_exists('pre', $data) && ($data['pre']['percent'] == 1))    // initially correct, no practice.
            ||
            (array_key_exists($this_user, $practice[$this_course])                // student did practice something, but
             && 
             !array_key_exists($no_group_cb, $practice[$this_course][$this_user]) // student did not practice this.
            )
            ||
            (!array_key_exists($this_user, $practice[$this_course]))              // student did not practice anything.
           ) {
          $r++;
echo "no practice row: $r\n";
          pprint("<TR><TD>$this_course</TD>");
          $sheet->write($r,0,$this_course);
          pprint("<TD>$this_user</TD>");
          $sheet->write($r,1,clean_data($this_user));
          pprint("<TD>$no_group_cb</TD>");
          $sheet->write($r,2,$no_group_cb);
          if (array_key_exists('pre', $data)) {
            pprint("<TD>" . $data['pre']['num'] . "</TD>");
            pprint("<TD>" . $data['pre']['den'] . "</TD>");
            pprint("<TD>" . $data['pre']['percent'] . "</TD>");
            pprint("<TD>" . $data['pre']['attempted'] . "</TD>");
          }
          else {  pprint("<TD></TD><TD></TD><TD></TD><TD></TD>"); }

          if (array_key_exists('post', $data)) {
            pprint("<TD>" . $data['post']['num'] . "</TD>");
            pprint("<TD>" . $data['post']['den'] . "</TD>");
            pprint("<TD>" . $data['post']['percent'] . "</TD>");
            pprint("<TD>" . $data['post']['attempted'] . "</TD>");
          }
          else {
            if (array_key_exists('pre', $data) && ($data['pre']['percent'] == 1)) {
              pprint("<TD>Not Assigned</TD><TD>Not Assigned</TD><TD>Not Assigned</TD><TD>Not Assigned</TD>");
            }
            else {
              pprint("<TD></TD><TD></TD><TD></TD><TD></TD>");
            }
          }

          if (array_key_exists('pre', $data)) {
            $sheet->write($r,3, $data['pre']['attempted'] == 'Yes' ? $data['pre']['num'] : 'No Attempt');
            $sheet->write($r,4, $data['pre']['attempted'] == 'Yes' ? $data['pre']['den'] : 'No Attempt');
            $sheet->write($r,5, $data['pre']['attempted'] == 'Yes' ? $data['pre']['percent'] : 'No Attempt');
            $sheet->write($r,6, $data['pre']['attempted'] == 'Yes' ? $data['pre']['attempted'] : 'No Attempt');
          }
          else {
            $sheet->write($r,3, 'No Data');
            $sheet->write($r,4, 'No Data');
            $sheet->write($r,5, 'No Data');
            $sheet->write($r,6, 'No Data');
          }

          if (array_key_exists('post', $data)) {
            $sheet->write($r,7, $data['post']['num']);
            $sheet->write($r,8, $data['post']['den']);
            $sheet->write($r,9, $data['post']['percent']);
            $sheet->write($r,10, $data['post']['attempted']);
          }
          else {
            if (array_key_exists('pre', $data) && ($data['pre']['percent'] == 1)) {
              $sheet->write($r,7, 'Not Assigned');
              $sheet->write($r,8, 'Not Assigned');
              $sheet->write($r,9, 'Not Assigned');
              $sheet->write($r,10, 'Not Assigned');
            }
            else {
              $sheet->write($r,7, 'No Attempt');
              $sheet->write($r,8, 'No Attempt');
              $sheet->write($r,9, 'No Attempt');
              $sheet->write($r,10, 'No Attempt');
            }
          }

          if (array_key_exists('retest', $data)) {
            for ($i = 0; $i <= 1; $i++) {
              pprint("<TD>");
              pprint(array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "");
              pprint("</TD>");

              $sheet->write($r,11 + 4*$i, array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "");
              $sheet->write($r,12 + 4*$i, array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "");
              $sheet->write($r,13 + 4*$i, array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "");
              $sheet->write($r,14 + 4*$i, array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "");

            }
          }
          else {
            pprint("<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>");
          }
          pprint("<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>");
          pprint("<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>");
          if (array_key_exists('pre', $data) && ($data['pre']['percent'] == 1)) {
            pprint("<TD>No need to practice or see tutorial</TD>");
            $sheet->write($r,31, "No need to practice or see tutorial");
          }
          else if (array_key_exists($this_user, $practice[$this_course]) && 
                   !(array_key_exists($no_group_cb, $practice[$this_course][$this_user]))) {
            pprint("<TD>Did not practice this concept so could not see tutorial</TD>");
            $sheet->write($r,31, "Did not practice this concept so could not see tutorial</TD>");
          }
          else {
            pprint("<TD>Did not practice anything so could not see tutorial</TD>");
            $sheet->write($r,31, "Did not practice anything so could not see tutorial");
          }

          if (array_key_exists($no_group_cb, $skill_area['general'])) {
            foreach ($skill_area['general'][$no_group_cb] as $this_concept_area => $index) {
              $this_index_here = 32 + $index;
              $sheet->write($r, $this_index_here, 1);
              //$sheet->write($r, $this_index_here, "y $index. G: $this_concept_area: $no_group_cb");
            }
          }
          $offset = count($skill_area['general']['index']);
          if (array_key_exists($no_group_cb, $skill_area['specific'])) {
            foreach($skill_area['specific'][$no_group_cb] as $this_content_specific => $index) {
              $this_index_here = 32 + 1 + $index + $offset;
              $sheet->write($r, $this_index_here, 1);
              //$sheet->write($r, $this_index_here, "y $index. S: $this_content_specific: $no_group_cb.  o=$offset");
            }
          }
          pprint("</TR>");
        }
        else {
          //student practiced, so do this work later.
        }
      }
    }
  }
}


foreach ($practice as $this_course => $val_user) {
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept_bank => $val_date) {
      $total_incorrect_attempts_at_concept = $val_date['total_incorrect_attempts'];
      $incorrect_attempts_at_concept = 0;
      foreach ($val_date as $this_date => $data) {
        if (($this_date != 'total_incorrect_attempts') && (is_valid_user($this_user))) {
          $r++;
echo "practice ROW: $r\n";
          $group_cb = "group:" . $this_concept_bank;
          pprint("<TR><TD>$this_course</TD>");
          pprint("<TD>" . clean_data($this_user) . "</TD>");
          pprint("<TD>$this_concept_bank</TD>");
          if (!array_key_exists($group_cb, $quiz_scores[$this_course][$this_user])) {
            print "$group_cb doesn't exist for quiz_scores[$this_course][$this_user]  What to do?\n";
          }
          else if (array_key_exists('pre', $quiz_scores[$this_course][$this_user][$group_cb]) && array_key_exists('attempted', $quiz_scores[$this_course][$this_user][$group_cb]['pre'])) {
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['num'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['den'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['percent'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['attempted'] . "</TD>");
          }
          else { pprint("<TD>Not Attempted</TD><TD>Not Attempted</TD><TD>Not Attempted</TD><TD>Not Attempted</TD>"); }
          if (!array_key_exists($group_cb, $quiz_scores[$this_course][$this_user])) {
            echo "$group_cb doesn't exist for quiz_scores[$this_course][$this_user].  What to do?\n";
          }
          else if (array_key_exists('post', $quiz_scores[$this_course][$this_user][$group_cb]) && array_key_exists('attempted', $quiz_scores[$this_course][$this_user][$group_cb]['post'])) {
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['num'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['den'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['percent'] . "</TD>");
            pprint("<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['attempted'] . "</TD>");
          }
          else { pprint("<TD>Not Attempted</TD><TD>Not Attempted</TD><TD>Not Attempted</TD><TD>Not Attempted</TD>"); }

          $sheet->write($r,0, $this_course);
          $sheet->write($r,1, clean_data($this_user));
          $sheet->write($r,2, $this_concept_bank);

          if (!array_key_exists($group_cb, $quiz_scores[$this_course][$this_user])) {
            $sheet->write($r,6, 'Concept Bank Not Existing for Student');
          }
          else if (array_key_exists('pre', $quiz_scores[$this_course][$this_user][$group_cb]) && array_key_exists('attempted', $quiz_scores[$this_course][$this_user][$group_cb]['pre'])) {
            $sheet->write($r,3, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['num']);
            $sheet->write($r,4, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['den']);
            $sheet->write($r,5, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['percent']);
            $sheet->write($r,6, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['attempted']);
          }
          else {
            $sheet->write($r,3, 'Not Attempted');
            $sheet->write($r,4, 'Not Attempted');
            $sheet->write($r,5, 'Not Attempted');
            $sheet->write($r,6, 'Not Attempted');
          }
          if (!array_key_exists($group_cb, $quiz_scores[$this_course][$this_user])) {
            $sheet->write($r,10, 'Concept Bank Not Existing for Student');
          }
          else if (array_key_exists('post', $quiz_scores[$this_course][$this_user][$group_cb]) && array_key_exists('attempted', $quiz_scores[$this_course][$this_user][$group_cb]['post'])) {
            $sheet->write($r,7, $quiz_scores[$this_course][$this_user][$group_cb]['post']['num']);
            $sheet->write($r,8, $quiz_scores[$this_course][$this_user][$group_cb]['post']['den']);
            $sheet->write($r,9, $quiz_scores[$this_course][$this_user][$group_cb]['post']['percent']);
            $sheet->write($r,10, $quiz_scores[$this_course][$this_user][$group_cb]['post']['attempted']);
          }
          else {
            $sheet->write($r,7, 'Not Attempted');
            $sheet->write($r,8, 'Not Attempted');
            $sheet->write($r,9, 'Not Attempted');
            $sheet->write($r,10, 'Not Attempted');
          }

          if (!array_key_exists($group_cb, $quiz_scores[$this_course][$this_user])) {
            echo "$group_cb doesn't exist for quiz_scores[$this_course][$this_user]:  retest\n";
            $sheet->write($r, 14, 'Concept Bank Not Existing for Student');
          }
          else if (array_key_exists('retest', $quiz_scores[$this_course][$this_user][$group_cb])) {
            for ($i = 0; $i <= 1; $i++) {
              pprint("<TD>AAA");
              pprint(array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'][$i] : "");
              pprint("</TD>");
              pprint("<TD>");
              pprint(array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'][$i] : "");
              pprint("</TD>");
              $sheet->write($r, 11+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'][$i] : ""));
              $sheet->write($r, 12+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'][$i] : ""));
              $sheet->write($r, 13+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'][$i] : ""));
              $sheet->write($r, 14+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'][$i] : ""));

            }
          }
          else {
            pprint("<TD></TD><TD></TD><TD></TD><TD></TD>");
            pprint("<TD></TD><TD></TD><TD></TD><TD></TD>");
          }


          pprint("<TD>" . $this_date . "</TD>");
          pprint("<TD>" . $data['time_practiced'] . "</TD>");
          pprint("<TD>" . $data['answer'] . "</TD>");
          $incorrect_attempts_at_concept += (1 - $data['boolean_success']); 
          pprint("<TD>" . $data['boolean_success'] . "</TD>");
          pprint("<TD>" . $data['num_score'] . "</TD>");
          pprint("<TD>" . $data['den_score'] . "</TD>");


          $sheet->write($r, 19, $this_date);
          $sheet->write($r, 20, $data['time_practiced']);
          $sheet->write($r, 21, $data['answer']);
          $sheet->write($r, 22, $data['boolean_success']);
          $sheet->write($r, 23, $data['num_score']);
          $sheet->write($r, 24, $data['den_score']);

          pprint("<TD>");
          if ($this_course == "Math160_F2009_awangberg") {
            pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Did not exist yet" : "No Tutorial Exists");
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Did not exist yet" : "No Tutorial Exists");
          }
          else if ($this_course == "Math160_S2010_eerrthum") {
            pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists");
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists");
          }
          else {
            pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists" : "No Tutorial Exists");
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists" : "No Tutorial Exists");
          }
          pprint("</TD>");


          if (array_key_exists($this_course, $tutorial_student_response) && array_key_exists($this_concept_bank, $tutorial_student_response[$this_course]) && array_key_exists($this_user, $tutorial_student_response[$this_course][$this_concept_bank])) {
            foreach ($tutorial_student_response[$this_course][$this_concept_bank][$this_user] as $this_date_viewed => $tut_viewing_data) {
              if ($this_date_viewed >= $this_date) {
                pprint("<TD>No: $this_date_viewed</TD>");
                $sheet->write($r, 26, "No");
                pprint("<TD></TD>");
                pprint("<TD></TD>");
                pprint("<TD></TD>");
                pprint("<TD></TD>");
                pprint("<TD>");
                pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists Not Yet Triggered" : "Tutorial DNE Not Yet Triggered");
                pprint("</TD>");
                $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists Not Yet Triggered" : "Tutorial DNE Not Yet Triggered");
              }
              else {
                pprint("<TD>Yes: $this_date_viewed</TD>");
                $sheet->write($r, 26, "Yes");
                $tutorial_id = $tutorial_available_for_concept_bank[$this_concept_bank];
                pprint("<TD>" . $tut_viewing_data['response']['parts_viewed'] . "</TD>");
                pprint("<TD>" . $tutorial_tree[$tutorial_id]['prompt_parts'] . "</TD>");

                $sheet->write($r, 27, $tut_viewing_data['response']['parts_viewed']);
                $sheet->write($r, 28, $tutorial_tree[$tutorial_id]['prompt_parts']);

                $thinking_time = 0;
                $total_viewing_time = 0;
                for ($i = 1; $i <= $tutorial_tree[$tutorial_id]['prompt_parts']; $i++) {
                  if (array_key_exists($i, $tut_viewing_data['response'])) {
                    $thinking_time = $tut_viewing_data['response'][$i]['thinking_time'] == "No Interaction" ? $thinking_time : ($thinking_time + $tut_viewing_data['response'][$i]['thinking_time']);
                    $total_viewing_time = $total_viewing_time + $tut_viewing_data['response'][$i]['total_time'];
                  }
                }
                pprint("<TD>" . round($thinking_time/1000, 1) . "</TD>");
                pprint("<TD>" . round($total_viewing_time/1000, 1) . "</TD>");

                $sheet->write($r, 29, round($thinking_time/1000, 1));
                $sheet->write($r, 30, round($total_viewing_time/1000, 1));

                pprint("<TD>");
                pprint($tut_viewing_data['response']['parts_viewed'] == $tutorial_tree[$tutorial_id]['prompt_parts'] ? "Tutorial Exists Full Viewing" : "Tutorial Exists Partial Viewing");
                pprint("</TD>");

                $sheet->write($r, 31, $tut_viewing_data['response']['parts_viewed'] == $tutorial_tree[$tutorial_id]['prompt_parts'] ? "Tutorial Exists Full Viewing" : "Tutorial Exists Partial Viewing");
              }
            }
          }
          else {
            pprint("<TD></TD>");
            pprint("<TD></TD>");
            pprint("<TD></TD>");
            pprint("<TD></TD>");
            pprint("<TD></TD>");
            pprint("<TD>");

            if ($this_course == "Math160_F2009_awangberg") {
              if ($incorrect_attempts_at_concept >= 4) {
                pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF should have been viewed" : "Tutorial DNE should have been viewed");
                $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF should have been viewed" : "Tutorial DNE should have been viewed");
              }
              else {
                if ($total_incorrect_attempts_at_concept >= 4) {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF not yet triggered" : "Tutorial DNE not yet triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF not yet triggered" : "Tutorial DNE not yet triggered");
                }
                else {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF would not be triggered" : "Tutorial DNE would not be triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial EIF would not be triggered" : "Tutorial DNE would not be triggered");
                }
              }
            }
            else if ($this_course == "Math160_S2010_eerrthum") {
              if ($incorrect_attempts_at_concept >= 4) {
                pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA should have been viewed" : "Tutorial DNE should have been viewed");
                $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA should have been viewed" : "Tutorial DNE should have been viewed");
              }
              else {
                if ($total_incorrect_attempts_at_concept >= 4) {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA not yet triggered" : "Tutorial DNE not yet triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA not yet triggered" : "Tutorial DNE not yet triggered");
                }
                else {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA would not be triggered" : "Tutorial DNE would not be triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial NA would not be triggered" : "Tutorial DNE would not be triggered");
                }
              }
            }
            else if (($this_course == "Math160_S2010_awangberg_05") || ($this_course == "Math160_F2010_awangberg")) {
              if ($incorrect_attempts_at_concept >= 4) {
                pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists should have been viewed (ignored tutorial)" : "Tutorial DNE should have been viewed");
                $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists should have been viewed (ignored tutorial)" : "Tutorial DNE should have been viewed");
              }
              else {
                if ($total_incorrect_attempts_at_concept >= 4) {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists not yet triggered (ignored tutorial)" : "Tutorial DNE not yet triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists not yet triggered (ignored tutorial)" : "Tutorial DNE not yet triggered");
                }
                else {
                  pprint(array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists would not be triggered" : "Tutorial DNE would not be triggered");
                  $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists would not be triggered" : "Tutorial DNE would not be triggered");
                }
              }
            }
            pprint("</TD>");
          }
          //print "<TD>" . "viewed tutorial" . "</TD>"
          //print "<TD>" . "tutorial parts" . "</TD>";
          //print "<TD>" . "parts viewed" . "</TD>";
          //print "<TD>" . "Think Time" . "</TD>";

          if (array_key_exists($no_group_cb, $skill_area['general'])) {
            foreach ($skill_area['general'][$no_group_cb] as $this_concept_area => $index) {
              $tmp_index_here = 32 + $index;
              $sheet->write($r, $tmp_index_here, 1);
              //$sheet->write($r, $tmp_index_here, "x $index. A:  $this_concept_area:  $no_group_cb");
ddprint("putting general skill for $no_group_cb with $this_concept_area at index 32 + $index\n"); 
            }
          }

          $offset = count($skill_area['general']['index']);
          if (array_key_exists($no_group_cb, $skill_area['specific'])) {
            foreach($skill_area['specific'][$no_group_cb] as $this_content_specific => $index) {
ddprint("putting specific skill for $no_group_cb with $this_content_specific at index 32 + 1 + $offset + $index\n");
              $tmp_index_here = 32 + 1 + $index + $offset;
              $sheet->write($r, $tmp_index_here, 1);
              //$sheet->write($r, $tmp_index_here, "x $index. S: $this_content_specific: $no_group_cb.  o=$offset");
            }
          }
          pprint("</TR>");
        }
      }
    }
  }
}

pprint("</TABLE>");

dprint_r($skill_area);


$csv_tut_data = array();

$csv_tut_data[] = array('tutorial_id', 'Concept', 'Part of Tutorial', 'Total Tutorial Parts', 'Prompt Name', 'Prompt Data', 'Type', 'Blooms Classification', 'Bloom Question Word', 'Viewed in Course', 'Concept', 'User', 'Date Viewed', 'Answer', 'Ask for a Reason', 'Was a Reason Given', 'Reason', 'Points Earned', 'Possible Points', 'Time to See Problem', 'Time to Submit Answer', 'Time to Submit Reason', 'Time to View Answer', 'Time to View Reason', 'Time to View All Answers', 'Time to View All Reasons', 'Time to Draw', 'Time for Thinking', 'Total Time on Tutorial Part');  

$c = 0;
foreach ($csv_tut_data as $akey => $val_arr) {
  foreach ($val_arr as $afield) {
    echo "<TD>$afield</TD>";
    $tut_sheet->write(0,$c, $afield);
    $c++;
  }
}

$r = 0;
foreach ($tutorial_student_response as $this_course => $tut_concept_data) {
  foreach ($tut_concept_data as $this_concept => $user_data) {
    foreach ($user_data as $this_user => $date_data) {
      foreach ($date_data as $this_date => $data) {
        if (is_valid_user($this_user)) {
          $tutorial_id = $data['response']['tutorial_id'];
          $parts_should_have_watched = array_key_exists($tutorial_id, $tutorial_tree) ? $tutorial_tree[$tutorial_id]['prompt_parts'] : 0;
          for ($i = 1; $i <= $parts_should_have_watched; $i++) {
            $r++;
echo "tut_row: $r\n";
            $tut_sheet->write($r,  0, $tutorial_id);
            $tut_sheet->write($r,  1, $this_concept);
            $tut_sheet->write($r,  2, $i);
            $tut_sheet->write($r,  3, $parts_should_have_watched);
            if (array_key_exists($i, $tutorial_tree[$tutorial_id]['prompt_id'])) {
              $tut_sheet->write($r,  4, $tutorial_tree[$tutorial_id]['prompt_id'][$i]['name']);
              $tut_sheet->write($r,  5, $tutorial_tree[$tutorial_id]['prompt_id'][$i]['data']);
              $tut_sheet->write($r,  6, $tutorial_tree[$tutorial_id]['prompt_id'][$i]['type']);
              $tut_sheet->write($r,  7, $tutorial_tree[$tutorial_id]['prompt_id'][$i]['classification']);
              $tut_sheet->write($r,  8, $tutorial_tree[$tutorial_id]['prompt_id'][$i]['blooms_word']);
            }
ddprint("printing out tutorial_tree: tutorial_tree[$tutorial_id]['prompt_id']\n");
dprint_r($tutorial_tree[$tutorial_id]['prompt_id']);

            $tut_sheet->write($r,  9, $this_course);
            $tut_sheet->write($r, 10, $this_concept);
            $tut_sheet->write($r, 11, clean_data($this_user));
            $tut_sheet->write($r, 12, $this_date);
            if (array_key_exists($i, $data['response'])) {
ddprint("we have data:\n");
dprint_r($data);
              $tut_sheet->write($r, 13, $data['response'][$i]['answer']);
              $tut_sheet->write($r, 14, $data['response'][$i]['askForReason']);
              $was_reason_provided = "";
              if ($data['response'][$i]['askForReason'] == "N") {
                $was_reason_provided = "No Request for Reason";
                $tut_sheet->write($r, 21, "No Reason Requested");
              }
              else if ($data['response'][$i]['askForReason'] == "Y") {
                if ($data['response'][$i]['reason'] == "") {
                  $was_reason_provided = "Reason requested but not given";
                }
                else {
                  $was_reason_provided = "Reason requested and given";
                }
                $tut_sheet->write($r, 21, is_numeric($data['response'][$i]['to_submit_reason']) ? round($data['response'][$i]['to_submit_reason']/1000, 3) : "No Reason Given");
              }
              else if ($data['response'][$i]['askForReason'] == "C") {
                if ($data['response'][$i]['points'] == $data['response'][$i]['possible_points']) {
                  $was_reason_provided = "Reason not requested for correct answer";
                  $tut_sheet->write($r, 21, "No Reason Requested");
                }
                else {
                  if ($data['response'][$i]['reason'] == "") {
                    $was_reason_provided = "Reason requested for incorrect answer but not given";
                  }
                  else {
                    $was_reason_provided = "Reason requested for incorrect answer and was given";
                  }
                  $tut_sheet->write($r, 21, is_numeric($data['response'][$i]['to_submit_reason']) ? round($data['response'][$i]['to_submit_reason']/1000, 3) : "No Reason Given");
                }
              }
              $tut_sheet->write($r, 15, $was_reason_provided);
              $tut_sheet->write($r, 16, $data['response'][$i]['reason']);
              $tut_sheet->write($r, 17, $data['response'][$i]['points']);
              $tut_sheet->write($r, 18, $data['response'][$i]['possible_points']);
ddprint("printing to_see_problem => " . $data['response'][$i]['to_see_problem'] . " became " . is_numeric($data['response'][$i]['to_see_problem']) ? round($data['response'][$i]['to_see_problem']/1000,3) : $data['response'][$i]['to_see_problem']);
              $tut_sheet->write($r, 19, is_numeric($data['response'][$i]['to_see_problem']) ? round($data['response'][$i]['to_see_problem']/1000, 3) : $data['response'][$i]['to_see_problem']);
              $tut_sheet->write($r, 20, is_numeric($data['response'][$i]['to_submit_answer']) ? round($data['response'][$i]['to_submit_answer']/1000, 3) : $data['response'][$i]['to_submit_answer']);
//              $tut_sheet->write($r, 21, is_numeric($data['response'][$i]['to_submit_reason']) ? round($data['response'][$i]['to_submit_reason']/1000, 3) : $data['response'][$i]['to_submit_reason']);
              $tut_sheet->write($r, 22, is_numeric($data['response'][$i]['to_view_answer']) ? round($data['response'][$i]['to_view_answer']/1000, 3) : $data['response'][$i]['to_view_answer']);
              $tut_sheet->write($r, 23, is_numeric($data['response'][$i]['to_view_reason']) ? round($data['response'][$i]['to_view_reason']/1000, 3) : $data['response'][$i]['to_view_reason']);
              $tut_sheet->write($r, 24, is_numeric($data['response'][$i]['to_view_all_answers']) ? round($data['response'][$i]['to_view_all_answers']/1000,3): $data['response'][$i]['to_view_all_answers']);
              $tut_sheet->write($r, 25, is_numeric($data['response'][$i]['to_view_all_reasons']) ? round($data['response'][$i]['to_view_all_reasons']/1000, 3) : $data['response'][$i]['to_view_all_reasons']);
              $tut_sheet->write($r, 26, is_numeric($data['response'][$i]['to_draw']) ? round($data['response'][$i]['to_draw']/1000, 3) : $data['response'][$i]['to_draw']);
              $tut_sheet->write($r, 27, is_numeric($data['response'][$i]['thinking_time']) ? round($data['response'][$i]['thinking_time']/1000, 3) : $data['response'][$i]['thinking_time']);
              $tut_sheet->write($r, 28, is_numeric($data['response'][$i]['total_time']) ? round($data['response'][$i]['total_time']/1000, 3) : $data['response'][$i]['total_time']);
            }
            else {
ddprint("data array:\n");
dprint_r($data);
            }
          }
        }
      }
    }
  }
}

$xls->close();

//print_r($tutorial_student_response);

?>
