<?php

include("../access.php");

//include("is_valid_users.php");
include("common.php");

require_once 'Spreadsheet/Excel/Writer.php';


function dprint($line) {
  $do = 0;
  if ($do) { print $line; }
}

function ddprint($line) {
  $do = 1;
  if ($do) { print $line; }
}

function dprint_r($array) {
  $do = 0;
  if ($do) { print_r($array); }
}

//formatTime($seconds);
//cleanData($line);

$quizNames = get_quizNames();
$courseNames = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);
$user = array();

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

print "<a href='#pre_post_retest'>pre / post / retest</a>";
print "<a href='#tutorial_info'>tutorial_info</a>";
print "<a href='#all_info'>practice info</a>";


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

$limit = " LIMIT 1000";  //" LIMIT 16000";

//get the users in webwork for each course:
foreach ($courseNames as $k => $courseName) {
  //get the users in webwork for each course:
  $user = get_users_from_course($con, $courseName, $user);

  $valid_user = valid_users($con, $courseName, 'validForStatistics="1"');

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

      if ($valid_user[$this_user]) {
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
        if (($this_concept == "group:MasterPlottingProblems") || ($this_concept == "group:ExtraPractice_kdufour08_Basics") || ($this_concept == "ExtraPractice_jtanderson06_Graphs")) {
          //do nothing.  Not a valid concept bank.
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

if (0) {

print"<a name='pre_post_retest'></a>";
print "<TABLE>";
print "<TR><TD>Course</TD><TD>User</TD><TD>Concept</TD><TD>Pre N</TD><TD>Pre D</TD><TD>Pre %</TD><TD>Pre Attempt</TD><TD>Post N</TD><TD>Post D</TD><TD>Post %</TD><TD>Post Attempt</TD><TD>Retest N1</TD><TD>Retest D1</TD><TD>Retest %1</TD><TD>Retest Attempt1</TD><TD>Retest N2</TD><TD>Retest D2</TD><TD>Retest %2</TD><TD>Retest Attemp2</TD></TR>";
foreach($quiz_scores as $this_course => $userData) {
  foreach($userData as $this_user => $cb_data) {
    foreach($cb_data as $this_concept => $data) {
      //if (is_valid_user($this_user) AND !(preg_match("/tcerroc/", $this_concept))) {
        if ($valid_user[$this_user] AND !(preg_match("/tcerroc/", $this_concept))) {
        print "<TR><TD>$this_course</TD><TD>$this_user</TD><TD>$this_concept</TD>";
        print "<TD>" . $data['pre']['num'] . "</TD>";
        print "<TD>" . $data['pre']['den'] . "</TD>";
        print "<TD>" . $data['pre']['percent'] . "</TD>";
        print "<TD>" . $data['pre']['attempted'] . "</TD>";
        print "<TD>" . $data['post']['num'] . "</TD>";
        print "<TD>" . $data['post']['den'] . "</TD>";
        print "<TD>" . $data['post']['percent'] . "</TD>";
        print "<TD>" . $data['post']['attempted'] . "</TD>";
        if (array_key_exists('retest', $data)) {
          for ($i = 0; $i <= 1; $i++) {
            print "<TD>";
            print array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "";
            print "</TD>";
            print "<TD>";
            print array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "";
            print "</TD>";
            print "<TD>";
            print array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "";
            print "</TD>";
            print "<TD>";
            print array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "";
            print "</TD>";
          }
        }
        else { print "<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>"; }
        print "</TR>";
      }
    }
  }
}
print "</TABLE>";

}

dprint("<P>DONE WITH pretest/posttest data</P>");

$db = "wwSession";
//select the database $db:
$result = mysql_select_db("$db", $con);

$saw_tutorials = array();
$practice = array();

$tmp_course = " AND course_name='Math160_S2010_awangberg_05'";

foreach ($courseNames as $keyk => $courseName) {
  $tmp_course = " AND course_name='" . $courseName . "'";

  //get the practice information for each user from wwSession.
  $query = "SELECT course_name, user_name, concept_bank, pg_sourcefile, submitted_date, attempted_date, submitted_answer, was_successful  FROM `attempts` WHERE submitted_date != '0000-00-00 00:00:00'" . $tmp_course . $limit;
  $result = mysql_query($query, $con);

  dprint($query . "\n<BR>\n");
  $count_of_records = 0;

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $count_of_records++;
    if ($count_of_records % 300 == 0) { print $count_of_records;  print "\n"; }
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


print "<P>";
print "<a name='tutorial_info'></a>";
print "<P>";

print "<TABLE><TR><TD>Course</TD><TD>User</TD><TD>Concept Bank</TD><TD>Shown?</TD><TD>Date</TD><TD>Tutorial_id</TD><TD>Answer_id</TD><TD></TR>\n";
foreach ($saw_tutorials as $this_course => $val_user) {
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept_bank => $tut_view_data) {
      $shown = $tut_view_data['shown_tutorial'];
      foreach ($tut_view_data['date_viewed'] as $this_date => $viewed_data) {
        print "<TR><TD>$this_course</TD><TD>$this_user</TD><TD>$this_concept_bank</TD><TD>$shown</TD><TD>$this_date</TD>";
        print "<TD>" . $viewed_data['tutorial_id'] . "</TD>";
        print "<TD>" . $viewed_data['student_answer_id'] . "</TD>";
        print "</TR>";
      }
    }
  }
}


print "</TABLE>\n";

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
            $tutorial_tree[$this_tutorial_id]['name'] = $this_title;
            $tutorial_tree[$this_tutorial_id]['draw_parts'] = 0;
            $tutorial_tree[$this_tutorial_id]['prompt_parts'] = 0;
            $tutorial_tree[$this_tutorial_id]['draw'][1] = $this_draw_id;
            $tutorial_tree[$this_tutorial_id]['prompt_decision'][1] = $this_prompt_key;
            $tutorial_tree[$this_tutorial_id]['review_decision'][1] = $this_review_key;
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
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part] = $this_prompt_id;
              }

              $query = "SELECT name, data, type, classification, question_word FROM `prompt` LEFT JOIN `blooms_classification ON prompt.blooms_classification_id = blooms_classification.id LEFT JOIN `blooms_question` ON prompt.blooms_question_id = blooms_question.blooms_question_id WHERE prompt_id = $this_prompt_id";
ddprint("\n 489: prompt query: $query<BR>\n");
              $result = mysql_query($query, $con);
              while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $this_prompt_name = $row['name'];
                $this_prompt_data = $row['data'];
                $this_prompt_type = $row['type'];
                $this_prompt_classification = $row['classification'];
                $this_prompt_blooms_word = $row['question_word'];
                $tutorial_tree[$this_tutorial_id]['prompt_id'][$part]['name'] = $this_prompt_name;
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
print_r($tutorial_tree[$this_tutorial_id]);
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
            $to_see_problem = $row['to_see_problem'];
            $to_submit_answer = $row['to_submit_answer'];
            $to_submit_reason = $row['to_submit_reason'];
            $to_view_answer = $row['to_view_answer'];
            $to_view_reason = $row['to_view_reason'];
            $to_view_all_answers = $row['to_view_all_answers'];
            $to_view_all_reasons = $row['to_view_all_reasons'];
            $to_draw = $row['to_draw'];
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

            $tutorial_for_concept = $tutorial_tree[$tutorial_id]['webwork_concept_bank'];

            //record the information
ddprint("recording information in tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]<BR>\n");
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
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_submit_answer'] = $to_submit_answer > 0 ? $to_submit_answer : ($to_submit_answer == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_submit_reason'] = $to_submit_reason > 0 ? $to_submit_reason : ($to_submit_reason == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_answer'] = $to_view_answer > 0 ? $to_view_answer : ($to_view_answer == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_reason'] = $to_view_reason > 0 ? $to_view_reason : ($to_view_reason == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_all_answers'] = $to_view_all_answers > 0 ? $to_view_all_answers : ($to_view_all_answers == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_view_all_reasons'] = $to_view_all_reasons > 0 ? $to_view_all_reasons : ($to_view_all_reasons == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['to_draw'] = $to_draw > 0 ? $to_draw : ($to_draw == -1) ? "Declined Option" : "No Option";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['thinking_time'] = $student_thinking_interaction_time > 0 ? $student_thinking_interaction_time : "No Interaction";
            $tutorial_student_response[$this_course][$tutorial_for_concept][$this_user][$this_date_viewed]['response'][$part]['total_time'] = $student_thinking_interaction_time + $to_see_problem;

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

//mysql_close($con);

//now have tutorial information in $tutorial_tree array
//and student viewing information in $tutorial_student_response array.


print "<P></P><a name='all_info'></a>";

$csv_data = array();

$csv_data[] = array('Course', 'User', 'Concept Bank', 'Pre Num', 'Pre Den', 'Pre %', 'Pre Attempt', 'Post Num', 'Post Den', 'Post %', 'Post Attempt', 'Retest Num1', 'Retest Den1', 'Retest %1', 'Retest Attempt1', 'Retest Num2', 'Retest Den2', 'Retest %2', 'Retest Attempt2', 'Practice Data', 'Seconds Practiced', 'Answer', 'Success', 'Practice Num', 'Practice Den', 'Tutorial Available', 'Viewed Tutorial', 'Parts Viewed', 'Tutorial Parts', 'Tutorial Think Time', 'Total Tutorial Time', 'Tutorial State Group');


$xls = new Spreadsheet_Excel_Writer('student_interactions.xls');
$sheet =& $xls->addWorkSheet("Student_Interactions");
$tut_sheet =& $xls->addWorkSheet("Tutorial_Interactions");

print "<TABLE><TR>";
$c = 0;
foreach ($csv_data as $akey => $val_arr) {
  foreach ($val_arr as $afield) {
    print "<TD>$afield</TD>";
    $sheet->write(0,$c, $afield);
    $c++;
  }
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
print "<TD>Tutorial State Group</TD>";
}
print "</TR>";

$r = 0;
foreach ($quiz_scores as $this_course => $val_user) {
  $valid_user = valid_users($con, $this_course, 'validForStatistics="1"');
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept => $data) {
      $no_group_cb = preg_replace("/group\:/", "", $this_concept);
      //if (is_valid_user($this_user) AND !(preg_match("/tcerroc/", $this_concept))) {
      if ($valid_user[$this_user] AND !(preg_match("/tcerroc/", $this_concept))) { 
        if (($data['pre']['percent'] == 1) || (!array_key_exists($no_group_cb, $practice[$this_course][$this_user]))) {
          $r++;
          print "<TR><TD>$this_course</TD>";
          $sheet->write($r,0,$this_course);
          print "<TD>$this_user</TD>";
          $sheet->write($r,1,$this_user);
          print "<TD>$no_group_cb</TD>";
          $sheet->write($r,2,$no_group_cb);
          print "<TD>" . $data['pre']['num'] . "</TD>";
          print "<TD>" . $data['pre']['den'] . "</TD>";
          print "<TD>" . $data['pre']['percent'] . "</TD>";
          print "<TD>" . $data['pre']['attempted'] . "</TD>";
          print "<TD>" . $data['post']['num'] . "</TD>";
          print "<TD>" . $data['post']['den'] . "</TD>";
          print "<TD>" . $data['post']['percent'] . "</TD>";
          print "<TD>" . $data['post']['attempted'] . "</TD>";

          $sheet->write($r,3, $data['pre']['num']);
          $sheet->write($r,4, $data['pre']['den']);
          $sheet->write($r,5, $data['pre']['percent']);
          $sheet->write($r,6, $data['pre']['attempted']);

          $sheet->write($r,7, $data['post']['num']);
          $sheet->write($r,8, $data['post']['den']);
          $sheet->write($r,9, $data['post']['percent']);
          $sheet->write($r,10, $data['post']['attempted']);

          if (array_key_exists('retest', $data)) {
            for ($i = 0; $i <= 1; $i++) {
              print "<TD>";
              print array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "";
              print "</TD>";

              $sheet->write($r,11 + 4*$i, array_key_exists($i, $data['retest']['num']) ? $data['retest']['num'][$i] : "");
              $sheet->write($r,12 + 4*$i, array_key_exists($i, $data['retest']['den']) ? $data['retest']['den'][$i] : "");
              $sheet->write($r,13 + 4*$i, array_key_exists($i, $data['retest']['percent']) ? $data['retest']['percent'][$i] : "");
              $sheet->write($r,14 + 4*$i, array_key_exists($i, $data['retest']['attempted']) ? $data['retest']['attempted'][$i] : "");

            }
          }
          else {
            print "<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>";
          }
          print "<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>";
          print "<TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD><TD></TD>";
          if ($data['pre']['percent'] == 1) {
            print "<TD>No need to practice or see tutorial</TD>";
            $sheet->write($r,31, "No need to practice or see tutorial");
          }
          else {
            print "<TD>Did not practice so could not see tutorial</TD>";
            $sheet->write($r,31, "Did not practice so could not see tutorial");
          }
          print "</TR>";
        }
        else {
          //student practiced, so do this work later.
        }
      }
    }
  }
}



foreach ($practice as $this_course => $val_user) {
  $valid_user = valid_users($con, $this_course, 'validForStatistics="1"');
  foreach ($val_user as $this_user => $val_cb) {
    foreach ($val_cb as $this_concept_bank => $val_date) {
      foreach ($val_date as $this_date => $data) {
        //if (is_valid_user($this_user)) {
        if ($valid_user[$this_user]) {
          $r++;
          $group_cb = "group:" . $this_concept_bank;
          print "<TR><TD>$this_course</TD>";
          print "<TD>$this_user</TD>";
          print "<TD>$this_concept_bank</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['num'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['den'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['percent'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['pre']['attempted'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['num'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['den'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['percent'] . "</TD>";
          print "<TD>" . $quiz_scores[$this_course][$this_user][$group_cb]['post']['attempted'] . "</TD>";

          $sheet->write($r,0, $this_course);
          $sheet->write($r,1, $this_user);
          $sheet->write($r,2, $this_concept_bank);
          $sheet->write($r,3, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['num']);
          $sheet->write($r,4, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['den']);
          $sheet->write($r,5, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['percent']);
          $sheet->write($r,6, $quiz_scores[$this_course][$this_user][$group_cb]['pre']['attempted']);
          $sheet->write($r,7, $quiz_scores[$this_course][$this_user][$group_cb]['post']['num']);
          $sheet->write($r,8, $quiz_scores[$this_course][$this_user][$group_cb]['post']['den']);
          $sheet->write($r,9, $quiz_scores[$this_course][$this_user][$group_cb]['post']['percent']);
          $sheet->write($r,10, $quiz_scores[$this_course][$this_user][$group_cb]['post']['attempted']);




          if (array_key_exists('retest', $quiz_scores[$this_course][$this_user][$group_cb])) {
            for ($i = 0; $i <= 1; $i++) {
              print "<TD>AAA";
              print array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'][$i] : "";
              print "</TD>";
              print "<TD>";
              print array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted']) ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'][$i] : "";
              print "</TD>";
              $sheet->write($r, 11+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['num'][$i] : ""));
              $sheet->write($r, 12+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['den'][$i] : ""));
              $sheet->write($r, 13+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['percent'][$i] : ""));
              $sheet->write($r, 14+4*$i, array_key_exists($i, $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'] ? $quiz_scores[$this_course][$this_user][$group_cb]['retest']['attempted'][$i] : ""));

            }
          }
          else {
            print "<TD></TD><TD></TD><TD></TD><TD></TD>";
            print "<TD></TD><TD></TD><TD></TD><TD></TD>";
          }


          print "<TD>" . $this_date . "</TD>";
          print "<TD>" . $data['time_practiced'] . "</TD>";
          print "<TD>" . $data['answer'] . "</TD>";
          print "<TD>" . $data['boolean_success'] . "</TD>";
          print "<TD>" . $data['num_score'] . "</TD>";
          print "<TD>" . $data['den_score'] . "</TD>";


          $sheet->write($r, 19, $this_date);
          $sheet->write($r, 20, $data['time_practiced']);
          $sheet->write($r, 21, $data['answer']);
          $sheet->write($r, 22, $data['boolean_success']);
          $sheet->write($r, 23, $data['num_score']);
          $sheet->write($r, 24, $data['den_score']);

          print "<TD>";
          if ($this_course == "Math160_F2009_awangberg") {
            print array_key_exists($this_concept_bank, $tutorial_avaiable_for_concept_bank) ? "Did not exist yet" : "No Tutorial Exists";
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Did not exist yet" : "No Tutorial Exists");
          }
          else if ($this_course == "Math160_S2010_eerrthum") {
            print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists";
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists");
          }
          else {
            print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists" : "No Tutorial Exists";
            $sheet->write($r, 25, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists" : "No Tutorial Exists");
          }
          print "</TD>";


          if (array_key_exists($this_course, $tutorial_student_response) && array_key_exists($this_concept_bank, $tutorial_student_response[$this_course]) && array_key_exists($this_user, $tutorial_student_response[$this_course][$this_concept_bank])) {
            foreach ($tutorial_student_response[$this_course][$this_concept_bank][$this_user] as $this_date_viewed => $tut_viewing_data) {
              if ($this_date_viewed >= $this_date) {
                print "<TD>No: $this_date_viewed</TD>";
                $sheet->write($r, 26, "No");
                print "<TD></TD>";
                print "<TD></TD>";
                print "<TD></TD>";
                print "<TD></TD>";
                print "<TD>";
                print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Not Shown Tutorial Yet" : "Not Shown Tutorial - Does not Exist";
                print "</TD>";
                $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Not Shown Tutorial Yet" : "Not Shown Tutorial - Tutorial DNE");
              }
              else {
                print "<TD>Yes: $this_date_viewed</TD>";
                $sheet->write($r, 26, "Yes");
                $tutorial_id = $tutorial_available_for_concept_bank[$this_concept_bank];
                print "<TD>" . $tut_viewing_data['response']['parts_viewed'] . "</TD>";
                print "<TD>" . $tutorial_tree[$tutorial_id]['prompt_parts'] . "</TD>";

                $sheet->write($r, 27, $tut_viewing_data['response']['parts_viewed']);
                $sheet->write($r, 28, $tutorial_tree[$tutorial_id]['prompt_parts']);

                $thinking_time = 0;
                $total_viewing_time = 0;
                for ($i = 1; $i <= $tutorial_tree[$tutorial_id]['prompt_parts']; $i++) {
                  $thinking_time = $tut_viewing_data['response'][$i]['thinking_time'] == "No Interaction" ? $thinking_time : ($thinking_time + $tut_viewing_data['response'][$i]['thinking_time']);
                  $total_viewing_time = $total_viewing_time + $tut_viewing_data['response'][$i]['total_time'];
                }
                print "<TD>" . round($thinking_time/1000, 1) . "</TD>";
                print "<TD>" . round($total_viewing_time/1000, 1) . "</TD>";

                $sheet->write($r, 29, round($thinking_time/1000, 1));
                $sheet->write($r, 30, round($total_viewing_time/1000, 1));

                print "<TD>";
                print $tut_viewing_data['response']['parts_viewed'] == $tutorial_tree[$tutorial_id]['prompt_parts'] ? "Viewed Full Tutorial" : "Viewed Incomplete Tutorial";
                print "</TD>";

                $sheet->write($r, 31, $tut_viewing_data['response']['parts_viewed'] == $tutorial_tree[$tutorial_id]['prompt_parts'] ? "Viewed Full Tutorial" : "Viewed Incomplete Tutorial");
              }
            }
          }
          else {
            print "<TD></TD>";
            print "<TD></TD>";
            print "<TD></TD>";
            print "<TD></TD>";
            print "<TD></TD>";
            print "<TD>";

            if ($this_course == "Math160_F2009_awangberg") {
              print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial did not exist yet" : "No Tutorial Exists to Show Student";
              $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial did not exist yet" : "No Tutorial Exists to Show Student");
            }
            else if ($this_course == "Math160_S2010_eerrthum") {
              print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists to Show Student";
              $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial exists but is turned off" : "No Tutorial Exists to Show Student");
            }
            else if ($ths_course == "Math160_S2010_awangberg_05") {
              print array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists but Student will not need it" : "No Tutorial Exists to Show Student";
              $sheet->write($r, 31, array_key_exists($this_concept_bank, $tutorial_available_for_concept_bank) ? "Tutorial Exists but Student will not need it" : "No Tutorial Exists to Show Student");
            }
            print "</TD>";
          }
          //print "<TD>" . "viewed tutorial" . "</TD>"
          //print "<TD>" . "tutorial parts" . "</TD>";
          //print "<TD>" . "parts viewed" . "</TD>";
          //print "<TD>" . "Think Time" . "</TD>";
          print "</TR>";
        }
      }
    }
  }
}

print "</TABLE>";


$csv_tut_data = array();

$csv_tut_data[] = array('tutorial_id', 'Concept', 'Part of Tutorial', 'Total Tutorial Parts', 'Prompt Name', 'Prompt Data', 'Blooms Classification', 'Bloom Question Word', 'Viewed in Course', 'Concept', 'User', 'Date Viewed', 'Answer', 'Ask for a Reason', 'Reason', 'Points Earned', 'Possible Points',  

$c = 0;
foreach ($csv_tut_data as $akey => $val_arr) {
  foreach ($val_arr as $afield) {
    print "<TD>$afield</TD>";
    $sheet->write(0,$c, $afield);
    $c++;
  }
}

$r = 0;
foreach ($tutorial_student_response as $this_course => $tut_concept_data) {
  foreach ($tut_concept_data as $this_concept => $user_data) {
    foreach ($user_data as $this_user => $date_data) {
      foreach ($date_data as $this_date => $data) {
        $tutorial_id = $data['response']['tutorial_id'];
        $parts_should_have_watched = $tutorial_tree[$tutorial_id]['prompt_parts'];
        for ($i = 0; $i <= $parts_should_have_watched; $i++) {
          $r++;
          $tut_sheet->write($r,  0, $tutorial_id);
          $tut_sheet->write($r,  1, $this_concept);
          $tut_sheet->write($r,  2, $i);
          $tut_sheet->write($r,  3, $parts_should_have_watched);
          $tut_sheet->write($r,  4, $tutorial_tree[$this_tutorial_id]['prompt_id'][$i]['name']);
          $tut_sheet->write($r,  5, $tutorial_tree[$this_tutorial_id]['prompt_id'][$i]['data']);
          $tut_sheet->write($r,  6, $tutorial_tree[$this_tutorial_id]['prompt_id'][$i]['type']);
          $tut_sheet->write($r,  7, $tutorial_tree[$this_tutorial_id]['prompt_id'][$i]['classification']);
          $tut_sheet->write($r,  8, $tutorial_tree[$this_tutorial_id]['prompt_id'][$i]['blooms_word']);
          $tut_sheet->write($r,  9, $this_course);
          $tut_sheet->write($r, 10, $this_concept);
          $tut_sheet->write($r, 11, $this_user);
          $tut_sheet->write($r, 12, $this_date);
          $tut_sheet->write($r, 13, $data['response'][$i]['answer']);
          $tut_sheet->write($r, 14, $data['response'][$i]['askForReason']);
          $tut_sheet->write($r, 15, $data['response'][$i]['reason']);
          $tut_sheet->write($r, 16, $data['response'][$i]['points']);
          $tut_sheet->write($r, 17, $data['response'][$i]['possible_points']);
          $tut_sheet->write($r, 18, $data['response'][$i]['to_see_problem']);
          $tut_sheet->write($r, 19, $data['response'][$i]['to_submit_answer']);
          $tut_sheet->write($r, 20, $data['response'][$i]['to_submit_reason']);
          $tut_sheet->write($r, 21, $data['response'][$i]['to_view_answer']);
          $tut_sheet->write($r, 22, $data['response'][$i]['to_view_reason']);
          $tut_sheet->write($r, 23, $data['response'][$i]['to_view_all_answers']);
          $tut_sheet->write($r, 24, $data['response'][$i]['to_view_all_reasons']);
          $tut_sheet->write($r, 25, $data['response'][$i]['to_draw']);
          $tut_sheet->write($r, 26, $data['response'][$i]['thinking_time']);
          $tut_sheet->write($r, 27, $data['response'][$i]['total_time']);
        }
      }
    }
  }
}
mysql_close($con);

$xls->close();

//print_r($tutorial_student_response);

?>
