<?php

include("../access.php");
include("common.php");
include("plot/scatterplot.php");

ini_set('max_execution_time', 300);

$print_out_timings = 1;

$studentCondition = "validForStatistics";  
$studentCondition = "finishedCourse";

$studentCondition = implode($_REQUEST['studentConditionGlue'], $_REQUEST['studentCondition']);

print "studentCondition is $studentCondition.<BR>";

$start = getTime();
$quizName = get_quizNames();
$end = getTime();
if ($print_out_timings) { echo 'time to get_quizNames(): ' . round($end - $start,4) . '<BR>'; }


$start = getTime();
$color = get_colors();
$end = getTime();
if ($print_out_timings) { echo 'time to get_colors(): ' . round($end - $start, 4) . '<BR>'; }

$start = getTime();
$reviewColor = get_reviewColor();
$end = getTime();
if ($print_out_timings) { echo 'time to get_reviewColor(): ' . round($end - $start, 4) . '<BR>'; }

$start = getTime();
$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass );
$end = getTime();
if ($print_out_timings) { echo 'time to get_course_make_con(): ' . round($end - $start, 4) . '<BR>'; }

//error print out:
$tmp_string_out = "";

print_r($_REQUEST);

if (isset($_REQUEST['report_for_this_course'])) {

  print_javascript_toggle_display();

  $report_on_these_courses = $_REQUEST['report_for_this_course'];
  $report_on_these_quizzes = $_REQUEST['report_for_this_quiz'];
  $report_on_these_skills = array();
  if (isset($_REQUEST['report_for_this_skill'])) {
    $report_on_these_skills = array_values($_REQUEST['report_for_this_skill']);
  }
  $report_on_this_combo = array();
  
  $start = getTime();
  foreach ($_REQUEST as $key => $value) {
    if (strpos($key, "Color_For_") == 0) {
      if ($value != "FFFFFF") {
        $tmp_key_skill = strtr(substr($key, 10), "_", " ");
        $color["$tmp_key_skill"] = "#" . $value;
      }
      else {
        unset($_POST[$key]);
        unset($_GET[$key]);
        unset($_REQUEST[$key]);
      }
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to process $_REQUEST for "Color_For_": ' . round($end - $start, 4) . '<BR>'; }


  $start = getTime();
  for($i = 0; $i < 5; $i++) {
    $this_combo_name = "combined" . $i . "_name"; 
    if ((isset($_REQUEST[$this_combo_name])) && ($_REQUEST[$this_combo_name] != "")) {
      $the_combo_skills = "combined" . $i . "_skills";
      $the_users_combo_name = $_REQUEST[$this_combo_name];
      $report_on_this_combo[$the_users_combo_name] = array_values($_REQUEST[$the_combo_skills]);
      $color["$the_users_combo_name"] = $_REQUEST["combined" . $i . "_color"];
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to process combined$i_name loop: ' . round($end - $start, 4) . '<BR>'; }



  $start = getTime();

  //initialize variables:
  $do_time = isset($_REQUEST['time']) ? $_REQUEST['time'] : false ;
  $do_review_sessions = isset($_REQUEST['review_sessions']) ? $_REQUEST['review_sessions'] : false ;
  $do_single_problems = isset($_REQUEST['single_problems']) ? $_REQUEST['single_problems'] : false ;
  $do_retest_problems = isset($_REQUEST['retest_problems']) ? $_REQUEST['retest_problems'] : false ;

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $user = array();
  $conceptBank_to_quizSkill = array();
  $quizSkill_to_conceptBank = array();

  $concept = array();
  $concept['pre'] = array();
  $concept['pre']['num'] = array();
  $concept['pre']['den'] = array();
  $concept['pre']['right'] = array();
  $concept['concept'] = array();

  $concept['post'] = array();
  $concept['post']['num'] = array();
  $concept['post']['den'] = array();


  //$problemNumConcept = array();
  //$problemDenConcept = array();

  $practice_time = array();

  $did_review_session = array();

  $courseGrades = array();

  $end = getTime();
  if ($print_out_timings) { echo 'time to initialize variables: ' . round($end - $start, 4) . '<BR>'; }


  //The user has asked for this course, so let's give them the results.
  $start2 = getTime();
  $num_of_courses_to_report = count($report_on_these_courses);
  for ($c = 0; $c < $num_of_courses_to_report; $c++) {

    $start1 = getTime();
    $course = $report_on_these_courses[$c];  //'Math160_F2009_awangberg';

    //get all the users for this course:
    $start = getTime();
    $user = get_users_from_course($con, $course, $user, true, $studentCondition);

    $end = getTime();
    if ($print_out_timings) { echo '..time to get users from course ' .$course. ': ' . round($end - $start,4). '<BR>';}

    //get all of the initial performance on each of the conceptBanks:
    //we gather all the data for all the quizzes, since some conceptBanks might be used for cross-quiz skills.


    $start = getTime();
    $ret = get_initial_scores_from_course($con, $course, $quizName, $concept, $practice_time, $quizSkill_to_conceptBank, $conceptBank_to_quizSkill, $studentCondition);
    $concept = $ret[0];
    $practice_time = $ret[1];
    $quizSkill_to_conceptBank = $ret[2];
    $conceptBank_to_quizSkill = $ret[3];
    $end = getTime();
    if ($print_out_timings) { echo '..time to get_initial_scores_from_course(): ' . round($end - $start,4) . '<BR>'; }

    if (0) { //debugging:
	print "<BR>CONCEPT IS <BR><PRE>";
	print_r($concept);
        print "</PRE><BR>quizSkill_to_conceptBank IS <BR><PRE>";
        print_r($quizSkill_to_conceptBank);
        print "</PRE><BR>conceptBank_to_quizSkill IS <BR><PRE>";
	print_r($conceptBank_to_quizSkill);
	print "</PRE><BR>Practice_Time is <BR><PRE>";
	print_r($practice_time);
	print "</PRE><BR>";
    }

    //get all the final quiz scores for the users in this course:
    $start = getTime();
    $ret = get_final_scores_from_course($con, $course, $user, $quizName, $concept, $practice_time, $studentCondition);
    $concept = $ret[0];
    $practice_time = $ret[1];
    $end = getTime();
    if ($print_out_timings) { echo '..time to get_final_scores_from_course(): ' . round($end - $start, 4) . '<BR>'; }

    //get all the retest quiz scores for the users in this course:
    $start = getTime();
    $ret = get_retest_scores_from_course($con, $course, $user, $quizName, $concept, $practice_time, $studentCondition);
    $concept = $ret[0];
    $practice_time = $ret[1];
    $end = getTime();
    if ($print_out_timings) { echo '..time to get_retest_scores_from_course(): ' . round($end - $start, 4) . '<BR>'; }


    //get all the practice time for users in this course:
    $start = getTime();
    $practice_time = get_practice_time_for_users($con, $course, $practice_time, $studentCondition);
    $end = getTime();
    if ($print_out_timings) { echo '..time to get_practice_time_for_users(): ' . round($end - $start, 4) . '<BR>'; }

    if (0) {  //debug:
	print '<BR>PRACTICE TIME: <BR> <PRE>';
	print_r($practice_time);
	print '</PRE>';
    }

    //get the attendence at the review sessions for users in this course:
    $start = getTime();
    $did_review_session = get_attendence_at_review_sessions($con, $course, $did_review_session);
    $end = getTime();
    if ($print_out_timings) { echo '..time to get_attendence_at_review_sessions(): ' . round($end - $start,4). '<BR>';}

    //practice times aren't associated to the reduced list of quiz problems.
    //go through and make that association.

    $start = getTime();

    echo 'REMOVE HARD CODE REFERENCE TO COURSES AND QUIZ HERE:<BR>';
    if ((($course == "Math160_F2011_awangberg") || ($course == "Math160_S2012_awangberg"))
         && (array_search("pcb", $report_on_these_quizzes))) {
      $db = "webwork";
      $result = mysql_select_db("$db", $con);
      $query = "SELECT source_file from `" . $course . "_problem` WHERE set_id='pcb' ORDER BY problem_id";

      $result = mysql_query($query, $con);
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        list($this_group, $this_concept) = explode(":", $row['source_file']);
        $list_of_quiz_concepts[$this_concept] = $this_concept;
      }

      $original_quiz_list = join("_AA_", array_keys($list_of_quiz_concepts));

      $already_used_time_for_concept = array();

      //for each in that list, get out the associated practice problems.
      foreach ($list_of_quiz_concepts as $k) {
        $this_concept = $k;
        $str = `php ../workWithWWDB/weighted_practice_sets.php $k $original_quiz_list`;
        $l = explode("<BR>", $str);
        $concept_list = array();
        $percent_list = array();
        foreach ($l as $tmp_k => $val) {
          list($b11, $n1, $c1, $p1, $rank1, $per1, $p2) = explode(" ", $val);
          if ($c1 != "") { $concept_list[] = $c1; $percent_list[] = sprintf("%01.2f", $per1); }
        }
        //now, for each student in the the course with pcb,
        //calculate the time practiced for this pcb question:
        foreach ($concept_list as $assoc_concept) {
          $acc_time = 0;
          if (!array_key_exists($assoc_concept, $already_used_time_for_concept)) {
            foreach ($user[$course] as $this_user)  {
              $acc_time = 0;
  //print "getting time for $assoc_concept for $this_user associated with $this_concept<BR>";
              if (array_key_exists($this_user, $practice_time['post']['success']["group:" . $assoc_concept][$course])) {
                $acc_time += $practice_time['post']['success']["group:" . $assoc_concept][$course][$this_user];
              }
              else {
                $acc_time += $practice_time['post']['failure']["group:" . $assoc_concept][$course][$this_user];
              }

              if (($acc_time > 0) && ($acc_time < 20*60)) {
                if (array_key_exists($this_user, $practice_time['post']['success']["group:" . $this_concept][$course])) {
                  $practice_time['post']['success']["group:" . $this_concept][$course][$this_user] += $acc_time;
                  //print "adding time $acc_time for SUCCESSFUL concept: $this_concept, user $this_user<BR>";
                }
                else {
                  $practice_time['post']['failure']["group:" . $this_concept][$course][$this_user] += $acc_time;
                  //print "adding time $acc_time for FAILURE concept: $this_concept, user $this_user<BR>";
                }
              }
              else if ($acc_time == 0) {
                //no time for that user.
                //print "no time: $acc_time for concept: $this_concept, user $this_user<BR>";
              }
              else {
                //print "timed out:  $acc_time for concept: $this_concept, user $this_user<BR>";
              }
            }
            $already_used_time_for_concept[$assoc_concept] = true;
          }
        }
      }
      //mysql_close($con);
    }
    $end = getTime();
    if ($print_out_timings) { echo '..time to make associations for pcb quizzes: ' . round($end - $start,4) . '<BR>'; } 
    $end1 = getTime();
    if ($print_out_timings) { echo '.time to make practice time associations for course ' . $course . ': ' . round($end1 - $start1,4) . '<BR>'; } 
  }
  $end2 = getTime();
  if ($print_out_timings) { echo 'time to get practice time associations: ' . round($end2 - $start2,4) . '<BR>'; }

  if (0) { //debug
	print "practice_time";
	print "<PRE>";
	print_r($practice_time['post']['failure']);
	print "</PRE>";
  }

  //get the different skills from wwSession
  $start = getTime();
  $db = 'wwSession';
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT concept_bank, concept_content, stage, level FROM `conceptBankContentConcepts`';
  $result = mysql_query($query, $con);


  $concepts_skill = array();

  //get the data associated with the skills:
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $concept_bank = "group:" . $row['concept_bank'];
    $skill = $row['concept_content'];
    $stage = $row['stage'];
    $level = $row['level'];

    if (in_array($skill, $report_on_these_skills)) {
      //$concepts_skill[$skill][] = $concept_bank;
      $conceptBank_to_quizSkill[$concept_bank][$skill] = $skill;
      $quizSkill_to_conceptBank[$skill][$concept_bank] = $concept_bank;
    }
    foreach($report_on_this_combo as $combo_name => $combo_skills) {
      if (in_array($skill, $combo_skills)) {
        $conceptBank_to_quizSkill[$concept_bank][$combo_name] = $combo_name;
        $quizSkill_to_conceptBank[$combo_name][$concept_bank] = $concept_bank;
      }
    }
    foreach($quizName as $key => $this_quiz) {
      foreach($report_on_this_combo as $combo_name => $combo_skills) {
        if ((in_array($this_quiz, $combo_skills)) && ($quizSkill_to_conceptBank[$this_quiz][$concept_bank] == $concept_bank)) {
          $conceptBank_to_quizSkill[$concept_bank][$combo_name] = $combo_name;
          $quizSkill_to_conceptBank[$combo_name][$concept_bank] = $concept_bank;
        }
      }
    } 
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to get different skills from wwSession: ' . round($end - $start, 4) . '<BR>'; }

  $start = getTime();
  foreach($report_on_this_combo as $combo_name => $val) {
    $report_on_these_skills[] = $combo_name;
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to populate report_on_these_skills[]: ' . round($end - $start, 4) . '<BR>'; }


  //Go through and fill out the post_failure data for each quiz
  //THERE's AN ISSUE HERE:  DEBUG:
  //If the student was given a pared-down PRE-TEST, THEN
  //their denominator for the conceptBanks reports should be lower.
  //Currently, the maximum number of conceptBanks that use that quizSkill is being used.

  $start = getTime();
  foreach ($conceptBank_to_quizSkill as $this_c => $val) {
    foreach ($report_on_these_courses as $key => $course) {
      foreach ($user[$course] as $this_user => $val2) {
        if (array_key_exists($this_user, $practice_time['post']['success'][$this_c][$course])) {
          //do nothing.  they are in the post time data
        }
        else if (array_key_exists($this_c, $practice_time['post']['failure']) &&
		 array_key_exists($course, $practice_time['post']['failure'][$this_c]) &&
                 array_key_exists($this_user, $practice_time['post']['failure'][$this_c][$course])) {
          //do nothing.  they are in the post failure data
        }
        else if (array_key_exists($this_c, $concept['pre']['right']) &&
		 array_key_exists($course, $concept['pre']['right'][$this_c]) &&
                 array_key_exists($this_user, $concept['pre']['right'][$this_c][$course])) {
          //do nothing.  They got the initial question right, so they won't need to practice.
        }
        else {
          //put them in the post practie data with a time of 0:
          if (!array_key_exists('post', $practice_time)) $practice_time['post'] = array();
          if (!array_key_exists('failure', $practice_time['post'])) $practice_time['post']['failure'] = array();
          if (!array_key_exists($this_c, $practice_time['post']['failure'])) $practive_time['post']['failure'][$this_c] = array();
	  if (!array_key_exists($course, $practice_time['post']['failure'][$this_c]))  $practice_time['post']['failure'][$this_c][$course] = array();
          $practice_time['post']['failure'][$this_c][$course][$this_user] = 0;
        }
      }
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to populate post_failure data for quizzes: ' . round($end - $start,4). '<BR>';}

  //get the overall performance data for each student for each quiz:
  //also, treat each skill requested as a quiz (so we don't need a separate batch of array variables for skills!

  $post_student_by_quiz_num_score = array();
  $post_student_by_quiz_den_score = array();
  $pre_student_by_quiz_num_score = array();
  $pre_student_by_quiz_den_score = array();

  $retest_student_by_quiz_num_score = array();
  $retest_student_by_quiz_den_score = array();

  $time_student_by_quiz = array();
  $time_student_by_quiz['Overall'] = array();

  //initialize all of the pre, post, retest, and time data for each quizSkill
  $start = getTime();
  foreach ($conceptBank_to_quizSkill as $this_c => $list_of_quizSkills) {
    foreach ($list_of_quizSkills as $this_quizSkill => $val) {
      foreach ($report_on_these_courses as $k => $course) {
        foreach ($user[$course] as $key2 => $this_user) {
          $post_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] = 0;
          $post_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] = 0;
          $post_student_by_quiz_num_score['Overall'][$course][$this_user] = 0;
          $post_student_by_quiz_den_score['Overall'][$course][$this_user] = 0;

          $pre_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] = 0;
          $pre_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] = 0;
          $pre_student_by_quiz_num_score['Overall'][$course][$this_user] = 0;
          $pre_student_by_quiz_den_score['Overall'][$course][$this_user] = 0;

          $retest_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] = 0;
          $retest_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] = 0;
          $retest_student_by_quiz_num_score['Overall'][$course][$this_user] = 0;
          $retest_student_by_quiz_den_score['Overall'][$course][$this_user] = 0;

          $time_student_by_quiz[$this_quizSkill][$course][$this_user] = 0;
          $time_student_by_quiz['Overall'][$course][$this_user] = 0;
 
          foreach ($report_on_this_combo as $combo_name => $skills) {
            $post_student_by_quiz_num_score[$combo_name][$course][$this_user] = 0;
            $post_student_by_quiz_den_score[$combo_name][$course][$this_user] = 0;
            $pre_student_by_quiz_num_score[$combo_name][$course][$this_user] = 0;
            $pre_student_by_quiz_den_score[$combo_name][$course][$this_user] = 0;
            $retest_student_by_quiz_num_score[$combo_name][$course][$this_user] = 0;
            $retest_student_by_quiz_den_score[$combo_name][$course][$this_user] = 0;
            $time_student_by_quiz[$combo_name][$course][$this_user] = 0;
          }
        }
      }
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to initialize all arrays: ' . round($end - $start,4) . '<BR>'; }

  //determine the pre-score and post-scores for the students:
  $start = getTime();
  foreach ($conceptBank_to_quizSkill as $this_c => $list_of_quizSkills) {
    foreach ($list_of_quizSkills as $this_quizSkill => $val) {
      foreach ($report_on_these_courses as $k => $course) {
        foreach ($user[$course] as $this_u => $this_user) {

	 //
	 //TAKE CARE OF THE POST-TEST SITUATIONS:
	 //

         //if the student got the problem right or wrong on the post-test,
         //  add one to the numerator if the the answer was right, and
         //  add one to the denominator in either case.
$KSD = 0;
if (($this_user == 'ksmerud10') && ($this_quizSkill == "Arithmetic")) {
  print "19, not 25 post-test den:";
  $KSD = 0;
}
if (array_key_exists($this_user, $practice_time['post']['assigned'][$this_c][$course])) {
         if (array_key_exists($this_user, $practice_time['post']['success'][$this_c][$course])) {
            $post_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] += 1;
            $post_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
if ($KSD) { print "A (post success)<BR>"; }
//print "$this_user did posttest on $this_quizSkill correct  ($this_c). <BR>";
            if (in_array($this_quizSkill, $quizName)) {
              $post_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
              $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
            }
          }
          else if (array_key_exists($this_user, $practice_time['post']['failure'][$this_c][$course])) {
            $post_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
if ($KSD) { print "B (post failure)<BR>"; }
//print "$this_user did posttest on $this_quizSkill incorrect<BR>";
            if (in_array($this_quizSkill, $quizName)) { 
              $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
            }
          }
          else {
if ($KSD) { print "AB:  Didn't do the post-test.... this_c is: $this_c .<BR>"; }
//print "GOT HERE:  $this_user didn't do posttest on $this_quizSkill. <BR>";
            if ($this_quizSkill == "pca") {
              $post_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
	      if (in_array($this_quizSkill, $quizName)) {
		$post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
	      }
	    }
          }
}
	  //
	  //TAKE CARE OF THE PRE-TEST SITUATIONS:
	  //
          //if the student got the problem right on the pre-test,
          //then they didn't see it on the post-test.
          //so add one to the pre-test num and den, and post-test num and den counts.
if (array_key_exists($this_user, $practice_time['pre']['assigned'][$this_c][$course])) {
          if (array_key_exists($this_c, $practice_time['pre']['success']) &&
              array_key_exists($this_user, $practice_time['pre']['success'][$this_c][$course])) {
            $pre_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] += 1;
            $pre_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
if ($KSD) { print "C (pre success)<BR>"; }
	    //HARDCODE FIX FOR PCA QUIZ:  A FULL PRE-QUIZ and FUL POST-QUIZ,
	    //RATHER THAN A CUSTOMIZED POST-QUIZ FOR EACH STUDENT.  FIX THIS.
            if ($this_quizSkill == "pca") { }
            else {
if ($KSD) { print "D (shouldn't be here!:  this_quizSkill == $this_quizSkill. <BR>"; }
              $post_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] += 1;
              $post_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
            }
            if (in_array($this_quizSkill, $quizName)) {
              $pre_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
              $pre_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
              $post_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
              $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
            }
          }

          //if the student got the problem wrong on the pre-test,
          //add one to the den score for the pre-test.
          //We've already added one to the post-test den score above.
          else if (array_key_exists($this_user, $practice_time['pre']['failure'][$this_c][$course])) {
            $pre_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
if ($KSD) { print "E (pre failure)<BR>"; }
            if (in_array($this_quizSkill, $quizName)) {
              $pre_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
            }
          }

	  else {
if ($KSD) { print "CDE: NO PRE-SCORE!  this_c is: $this_c.<BR>"; }
//print "GOT HERE:  $this_user didn't do pretest on $this_quizSkill. <BR>";
	    if ($this_quizSkill == "pca") {
	      $pre_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += 1;
	      if (in_array($this_quizSkill, $quizName)) {
		$pre_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
	      }
	    }
	  }
}
          //if this is a retested problem,
          //and it was correct, add one to the retest num score:
if (array_key_exists($this_user, $practice_time['retest']['assigned'][$this_c][$course])) {
          if (array_key_exists($this_c, $practice_time['retest']['success']) &&
              array_key_exists($this_user, $practice_time['retest']['success'][$this_c][$course])) {
            $retest_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] += $practice_time['retest']['success'][$this_c][$course][$this_user];
if ($KSD) { print "F (retest success)<BR>"; }
            if (in_array($this_quizSkill, $quizName)) {
              $retest_student_by_quiz_num_score['Overall'][$course][$this_user] += $practice_time['retest']['success'][$this_c][$course][$this_user];
            }
          }
          //if this was a retested problem,
          //and it was incorrect, add 0 to the retest num score:
          if (array_key_exists($this_c, $practice_time['retest']['failure']) &&
              array_key_exists($this_user, $practice_time['retest']['failure'][$this_c][$course])) {
            $retest_student_by_quiz_num_score[$this_quizSkill][$course][$this_user] += 0;
if ($KSD) { print "G (retest failure)<BR>"; }
            if (in_array($this_quizSkill, $quizName)) {
              $retest_student_by_quiz_num_score['Overall'][$course][$this_user] += 0;
            }
          }

          //if this was a retested problem,
          //add the number of times it was retested to the retest den score:
          if (array_key_exists($this_c, $practice_time['retest']['den']) &&
              array_key_exists($this_user, $practice_time['retest']['den'][$this_c][$course])) {
            $retest_student_by_quiz_den_score[$this_quizSkill][$course][$this_user] += $practice_time['retest']['den'][$this_c][$course][$this_user];
if ($KSD) { print "H (retested problem - den score<BR>"; }
            if (in_array($this_quizSkill, $quizName)) {
              $retest_student_by_quiz_den_score['Overall'][$course][$this_user] += $practice_time['retest']['den'][$this_c][$course][$this_user];
            }
          }
}
          //account for time:
          $ttt = 0;
          $ttt = array_key_exists($this_user, $practice_time['post']['success'][$this_c][$course]) 
                   ? $practice_time['post']['success'][$this_c][$course][$this_user] 
                   : ( array_key_exists($this_user, $practice_time['post']['failure'][$this_c][$course]) 
                       ? $practice_time['post']['failure'][$this_c][$course][$this_user] 
                       : 0);
          if (!array_key_exists($this_quizSkill, $time_student_by_quiz)) $time_student_by_quiz[$this_quizSkill] = array();
	  if (!array_key_exists($course, $time_student_by_quiz[$this_quizSkill]))  $time_student_by_quiz[$this_quizSkill][$course] = array();
          if (!array_key_exists($this_user, $time_student_by_quiz[$this_quizSkill][$course])) $time_student_by_quiz[$this_quizSkill][$course][$this_user] = 0;
          $time_student_by_quiz[$this_quizSkill][$course][$this_user] += $ttt;

          if (in_array($this_quizSkill, $quizName)) {
            $time_student_by_quiz['Overall'][$course][$this_user] += $ttt;
          }
        }
      }
    }
  }
  $end = getTime();

  if (0) {  //DEBUG
	print "<PRE>";
	print_r($post_student_by_quiz_den_score);
	print "</PRE>";
  }

  if ($print_out_timings) { echo 'time to process student scores: ' . round($end - $start, 4) . '<BR>'; }

  //get the pre score and change in score:
  $pre_score_on_quiz = array();
  $change_in_score_on_quiz = array();

  $avg_pre = array();
  $avg_change = array();
  $std_dev_pre = array();
  $std_dev_change = array();


//  //fix the counts of post den that is wrong somehow!
//  //FIX THIS!!!!!
//echo '<FONT COLOR="RED">FIX THIS ERROR</FONT><BR>';
//  if (in_array("Math160_F2011_awangberg", $report_on_these_courses)) {
//    if (in_array("pcb", $report_on_these_quizzes)) {
//      $course = "Math160_F2011_awangberg";
//      $this_quiz = "pcb";
//      foreach ($user["Math160_F2011_awangberg"] as $key2 => $this_user) {
//        $post_student_by_quiz_den_score[$this_quiz][$course][$this_user] = 25;
////print "post_student_by_quiz_den_score[$this_quiz][$this_user] was " . $post_student_by_quiz_den_score[$this_quiz][$this_user] . ", but after taking off " . $retest_student_by_quiz_num_score[$this_quiz][$this_user] . "(num) and " . $retest_student_by_quiz_den_score[$this_quiz][$this_user] . "(den) we have";
//        //$post_student_by_quiz_den_score[$this_quiz][$this_user] -= $retest_student_by_quiz_num_score[$this_quiz][$this_user];
//        //$post_student_by_quiz_den_score[$this_quiz][$this_user] -= $retest_student_by_quiz_den_score[$this_quiz][$this_user];
////        print $post_student_by_quiz_den_score[$this_quiz][$this_user] . "<BR>";
//      }
//    }
//  }
//  if (in_array("Math160_S2012_awangberg", $report_on_these_courses)) {
//    if (in_array("pcb", $report_on_these_quizzes)) {
//      $course = "Math160_S2012_awangberg";
//      $this_quiz = "pcb";
//      foreach ($user[$course] as $key2 => $this_user) {
//	$post_student_by_quiz_den_score[$this_quiz][$course][$this_user] = 25;
//      }
//    }
//  }

  $start = getTime();
  foreach ($quizName as $key1 => $this_quiz) {
    foreach ($report_on_these_courses as $k => $course) {
      foreach ($user[$course] as $key2 => $this_user) {
        $pre_score_on_quiz[$this_quiz][$course][$this_user] = 
		$pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] / 
		$post_student_by_quiz_den_score[$this_quiz][$course][$this_user];
      //  $final_score = ($this_quiz == "pcb") ?
      //                 ($post_student_by_quiz_num_score[$this_quiz][$this_user])/($post_student_by_quiz_den_score[$this_quiz][$this_user]) :
        $final_score = 
                      ($post_student_by_quiz_num_score[$this_quiz][$course][$this_user] + 
			$retest_student_by_quiz_num_score[$this_quiz][$course][$this_user]) / 
		      ($post_student_by_quiz_den_score[$this_quiz][$course][$this_user] + 
			$retest_student_by_quiz_den_score[$this_quiz][$course][$this_user]);
        $change_in_score_on_quiz[$this_quiz][$course][$this_user] = $final_score - $pre_score_on_quiz[$this_quiz][$course][$this_user];
//        print "quiz: $this_quiz, user $this_user: pre: " . $pre_score_on_quiz[$this_quiz][$this_user] . " and final score: $final_score <BR>";
      }
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to populate student score arrays (quiz): ' . round($end - $start, 4) . '<BR>'; }

  $start = getTime();
  foreach ($report_on_these_courses as $k => $course) {
    foreach ($user[$course] as $this_user => $val2) {
      $pre_score_on_quiz['Overall'][$course][$this_user] = 
			$pre_student_by_quiz_num_score['Overall'][$course][$this_user] / 
			$post_student_by_quiz_den_score['Overall'][$course][$this_user];
      $final_score = 
			($post_student_by_quiz_num_score['Overall'][$course][$this_user] +
			 $retest_student_by_quiz_num_score['Overall'][$course][$this_user]) / 
			($post_student_by_quiz_den_score['Overall'][$course][$this_user] + 
			 $retest_student_by_quiz_den_score['Overall'][$course][$this_user]);
      $change_in_score_on_quiz['Overall'][$course][$this_user] = 
			$final_score - $pre_score_on_quiz['Overall'][$course][$this_user];
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to populate student score arrays (Overall): ' . round($end - $start,4). '<BR>';}

  $start = getTime();
  foreach ($report_on_these_skills as $key => $this_skill) {
    foreach ($report_on_these_courses as $k => $course) {
      foreach ($user[$course] as $this_user => $val2) {
        $pre_score_on_quiz[$this_skill][$course][$this_user] = 
			$pre_student_by_quiz_num_score[$this_skill][$course][$this_user] / 
			$post_student_by_quiz_den_score[$this_skill][$course][$this_user];
        $final_score = 
			($post_student_by_quiz_num_score[$this_skill][$course][$this_user] + 
			 $retest_student_by_quiz_num_score[$this_skill][$course][$this_user]) / 
			($post_student_by_quiz_den_score[$this_skill][$course][$this_user] + 
			 $retest_student_by_quiz_den_score[$this_skill][$course][$this_user]);
        $change_in_score_on_quiz[$this_skill][$course][$this_user] =
			 $final_score - $pre_score_on_quiz[$this_skill][$course][$this_user];
//print "skill: $this_skill, user $this_user:  pre: " . $pre_score_on_quiz[$this_skill][$this_user] . " and final score: $final_score <BR>";
      }
    }
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to populate student score arrays (skills): ' . round($end - $start,4) . '<BR>';}


  $start = getTime();
  foreach ($_REQUEST['report_for_this_course'] as $ind => $course) {
    if (isset($_REQUEST['gradeItemForCourse_' . $course])) {
      $gradeItem_id = $_REQUEST['gradeItemForCourse_' . $course];

      $query = 'SELECT id FROM `course_wwValidCourses` WHERE ww_course="' . $course . '"';
      $course_id = -1;
      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $course_id = $row['id'];
      }

      $query = 'SELECT gradeLetter, gradePercent, ww_user_id, course_gradeItem FROM `course_grades` LEFT JOIN `course_wwUserPermissions` ON course_wwUserPermissions_id=course_wwUserPermissions.id  LEFT JOIN `course_gradeItem` ON course_gradeItem_id=course_gradeItem.id WHERE validUser=1 AND ' . $studentCondition . '=1 AND course_wwValidCourses_id="' . $course_id . '"';

      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $courseGrades[$course][$row['ww_user_id']]['gradePercent'] = $row['gradePercent'] / 100;
        $courseGrades[$course][$row['ww_user_id']]['gradeLetter'] = $row['gradeLetter'];
      }
    }
  }

  if (0) {  //debug
	print "courseGrades is: <pre>";
	print_r($courseGrades);
	print "</pre><BR>DONE.<BR>";
  }

  print "<H2>UPDATE ME!!! Change in Performance on ";
  $comma = "";
  foreach ($report_on_these_quizzes as $key => $this_quiz) {
    print $comma;
    print "<span style='color: " . $color[$this_quiz] . ";'>" . $this_quiz . "</span>";
    $comma = ", ";
  }

  print count($report_on_these_quizzes) > 0 ? " Quizzes " : "";
  print ((count($report_on_these_quizzes) > 0) AND (count($report_on_these_skills) > 0)) ? " and " : "";
  $comma = "";

  foreach ($report_on_these_skills as $key => $this_skill) {
    print $comma;
    //instead of using a random color, use the color that was chosen by the user:
    //$this_color = "";
    //    while (strlen($this_color) < 7) {
    //  $this_color = "#" . dechex(mt_rand(0,255)) . dechex(mt_rand(0,255)) . dechex(mt_rand(0,255));
    //}
    //$color[$this_skill] = $this_color;
    print "<span style='color: " . $color["$this_skill"] . ";'>" . $this_skill . "</span>";
    $comma = ", ";
  }

  print count($report_on_these_skills) > 0 ? " Skills " : "";

  print "</H2>";

  $start2 = getTime();

  $do_these_reports = array();
  $ind = 0;
  foreach ($report_on_these_quizzes as $key => $this_quiz) {
    $do_these_reports[$ind]['name'] = $this_quiz;
    $do_these_reports[$ind]['color'] = $color[$this_quiz];
    $do_these_reports[$ind]['color2'] = $reviewColor[$this_quiz];
    $do_these_reports[$ind]['n'] = '';
    $do_these_reports[$ind]['size_key2'] = isset($_REQUEST['review_sessions']) ? 'practice_time2' : 'standard';
    $do_these_reports[$ind]['size_key'] = isset($_REQUEST['time']) ? 'practice_time' : 'standard';
    $do_these_reports[$ind]['regress_type'] = isset($_REQUEST['reg3']) ? 3 :
						(isset($_REQUEST['reg2']) ? 2 :
						(isset($_REQUEST['reg1']) ? 1 : 0));
    $do_these_reports[$ind]['regress_show'] = $do_these_reports[$ind]['regress_type'] > 0 ? true : false;
    $do_these_reports[$ind]['regress_show_eq'] = $do_these_reports[$ind]['regress_type'];
    $do_these_reports[$ind]['legend_string'] = $this_quiz . ' (n = ' . '' . ')';
    $do_these_reports[$ind]['show_data_string'] = true;
    $do_these_reports[$ind]['linkable'] = false;
    $ind++;
  }
  foreach ($report_on_these_skills as $key => $this_skill) {
    $do_these_reports[$ind]['name'] = $this_skill;
    $do_these_reports[$ind]['color'] = $color[$this_skill];
    $do_these_reports[$ind]['color2'] = $reviewColor[$this_skill];
    $do_these_reports[$ind]['n'] = '';
    $do_these_reports[$ind]['size_key'] = isset($_REQUEST['time']) ? 'practice_time' : 'standard';
    $do_these_reports[$ind]['size_key2'] = isset($_REQUEST['review_sessions']) ? 'practice_time2' : 'standard';
    $do_these_reports[$ind]['regress_type'] = isset($_REQUEST['reg3']) ? 3 : 
						(isset($_REQUEST['reg2']) ? 2 : 
						(isset($_REQUEST['reg1']) ? 1 : 0));
    $do_these_reports[$ind]['regress_show'] = $do_these_reports[$ind]['regress_type'] > 0 ? true : false;
    $do_these_reports[$ind]['regress_show_eq'] = $do_these_reports[$ind]['regress_show'];
    $do_these_reports[$ind]['legend_string'] = $this_skill . ' (n = ' . '' . ')';
    $do_these_reports[$ind]['show_data_string'] = true;
    $do_these_reports[$ind]['linkable'] = false;
    $ind++;
  }

  $graph_options = array();
  $graph_options['width'] = 400;
  $graph_options['height'] = 400;
  $graph_options['legend']['x'] = 40;
  $graph_options['legend']['y'] = 40;
  $graph_options['legend']['width'] = 100;
  $graph_options['legend']['height'] = 100;
  $graph_options['show_usernames'] = true;
  $graph_options['jitter_data'] = true;
  $graph_options['title'] = 'Post-test vs. Pre-test Success Rate (%)';
  

  $legend_vertical_offset = 0;  //-30 for each report.
  $this_user1 = 0;		//
//  $regression_line = '';	// in the make_scatter_plot function


  $data = array();

  //options:  pre score, post score, change in score, percent change in score, time
  $horizontal = $_REQUEST['h_axis']; 
  $vertical = $_REQUEST['v_axis']; 
  //$vertical = "cp";  // change / (1 - pre)
  $size = "practice time"; "post score";
  $size = $_REQUEST['size_level'];
  $opacity = $_REQUEST['opacity_level'];

  $graph_options['title'] = $vertical . ' vs. ' . $horizontal . 
			    //originally, wanted horizontal, vertical to be arrays:
			    //join(", ", $vertical) . ' vs. ' . join(", ", $horizontal) .
			    ((($size != "off") || ($opacity != "off")) ? ' with ' : '') .
			    (($size == "off") ? '' : $size . ' indicated by size') . 
			    ((($size != "off") && ($opacity != "off")) ? ' and ' : '') . 
			    (($opacity == "off") ? '' : $opacity . ' given by opacity') . 
			    '.';

  if ($size == "practice time") {
    $graph_options['size1_circle'] = 2 + round(((60*60)/(5*60))^M_EULER, 0);
    $graph_options['size1_title']  = '1 hour practice time.';
    $graph_options['size2_circle'] = 2 + round(((2*60*60)/(5*60))^M_EULER, 0);
    $graph_options['size2_title']  = '2 hours practice time.';
  }
  else if (($size == "post score") || ($size == "pre score")) {
    $graph_options['size1_circle'] = 2 + round(((.50)*100/5)^M_EULER, 0);
    $graph_options['size1_title']  = 'score of 50% on ' . $size;
    $graph_options['size2_circle'] = 2 + round(((1.00)*100/5)^M_EULER, 0);
    $graph_options['size2_title']  = 'score of 100% on ' . $size;
  }

  $max_count_of_reports = count($do_these_reports);
  for ($ind = 0; $ind < $max_count_of_reports; $ind++) {
    $this_quiz = $do_these_reports[$ind]['name'];
    $this_user1 = 0;
//print "horizontal is " . $horizontal . " and vertical is " . $vertical . "<BR>";
    foreach ($report_on_these_courses as $k => $course) {
      foreach ($user[$course] as $this_user => $val2) {
        $this_user1++;
        $pre_score = $pre_score_on_quiz[$this_quiz][$course][$this_user];
        $change_in_score = $change_in_score_on_quiz[$this_quiz][$course][$this_user];
        if ($horizontal == "pre score") {
          $data[$this_quiz][$this_user1]['h'] = $pre_score;
        }
        else if ($horizontal == "post score") {
          $data[$this_quiz][$this_user1]['h'] = $pre_score + $change_in_score;
        }
        else if ($horizontal == "change in score") {
          $data[$this_quiz][$this_user1]['h'] = $change_in_score;
        }
        else if ($horizontal == "percent change in score") {
           $data[$this_quiz][$this_user1]['h'] = $change_in_score / (1 - $pre_score);
        }
	else if ($horizontal == "grade item") {
	   $data[$this_quiz][$this_user1]['h'] = $courseGrades[$course][$this_user]['gradePercent'];
        }
        else if ($horizontal == "practice time") {
          $data[$this_quiz][$this_user1]['h'] = round(($time_student_by_quiz[$this_quiz][$course][$this_user])/(100*5*60),2);
        }
        else {}

        if ($vertical == "pre score") {
          $data[$this_quiz][$this_user1]['v'] = $pre_score;
        }
        else if ($vertical == "post score") {
          $data[$this_quiz][$this_user1]['v'] = $pre_score + $change_in_score;
        }
        else if ($vertical == "change in score") {
          $data[$this_quiz][$this_user1]['v'] = $change_in_score;
        }
        else if ($vertical == "percent change in score") {
          $data[$this_quiz][$this_user1]['v'] = $change_in_score / (1 - $pre_score);
        }
	else if ($vertical == "grade item") {
	  $data[$this_quiz][$this_user1]['v'] = $courseGrades[$course][$this_user]['gradePercent'];
//          if ($this_user == "pburandt09") {
//		echo 'USER pburandt09 (' . $this_user1 . '), course: ' . $course . ' and gradePercent: ' . $data[$this_quiz][$this_user1]['v'] . '<BR>';
//	  }
	}
        else if ($vertical == "practice time") {
          $data[$this_quiz][$this_user1]['v'] = round(($this_student_by_quiz[$this_quiz][$course][$this_user])/(100*60),2);
        }
        else { }

        if ($size == "pre score") {
          $data[$this_quiz][$this_user1]['size']['standard'] = 2; // $pre_score;
          $data[$this_quiz][$this_user1]['size']['practice_time'] = round((($pre_score*100)/(5))^M_EULER, 0);
          $data[$this_quiz][$this_user1]['size']['practice_time_with_review'] = 2; //$pre_score;
        }
        else if ($size == "post score") {
          $data[$this_quiz][$this_user1]['size']['standard'] = 2; //$pre_score + $change_in_score;
          $data[$this_quiz][$this_user1]['size']['practice_time'] = round((($pre_score + $change_in_score)*100/5)^M_EULER, 0); //$pre_score + $change_in_score;
          $data[$this_quiz][$this_user1]['size']['practice_time_with_review'] = 2; //$pre_score + $change_in_score;
        }
        else if ($size == "practice time") {
          $data[$this_quiz][$this_user1]['size']['standard'] = 2;
          $data[$this_quiz][$this_user1]['size']['practice_time'] = 2 + round((($time_student_by_quiz[$this_quiz][$course][$this_user])/(5*60))^M_EULER, 0);
          $data[$this_quiz][$this_user1]['size']['practice_time_with_review'] = 2 + round((($time_student_by_quiz[$this_quiz][$course][$this_user] + $did_review_session[$this_quiz][$course][$this_user])/(5*60))^M_EULER, 0);
        }
        $data[$this_quiz][$this_user1]['username'] = $this_user;
        $info = 
  	  $this_quiz . ':' 
	. ' pre_num: ' . $pre_student_by_quiz_num_score[$this_quiz][$course][$this_user]
	. ' pre_den: ' . $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]
	. ' post_num: ' . $post_student_by_quiz_num_score[$this_quiz][$course][$this_user]
	. ' retest_num: ' . $retest_student_by_quiz_num_score[$this_quiz][$course][$this_user]
	. ' post_den + retest_den: (' . $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]
	. ' + ' . $retest_student_by_quiz_den_score[$this_quiz][$course][$this_user]
	. ' = ' . ($post_student_by_quiz_den_score[$this_quiz][$course][$this_user] +
		   $retest_student_by_quiz_den_score[$this_quiz][$course][$this_user])
	. ')';

        $data[$this_quiz][$this_user1]['data_string'] = 
	  $this_quiz . ': student ' . $this_user . ', course: ' . $course . ':'
	. ': pre_score: ' . round(100*$pre_score,1) 
	. ' and change_in_score: ' . round(100*$change_in_score,1) 
	. ' and time: ' . round((1/60)*$time_student_by_quiz[$this_quiz][$course][$this_user],1) . 'min.'
	. $info . '.';

        $data[$this_quiz][$this_user1]['data_string2'] = 
	  $this_quiz . ': student ' . $this_user1 . ':'
	. ' pre_score: ' . round(100*$pre_score,1)
	. ' and change_in_score: ' . round(100*$change_in_score,1)
	. ' and time: ' . round((1/60)*$time_student_by_quiz[$this_quiz][$course][$this_user],1) . ' min.'
	. $info . '.'
	. 'review_session: ' . round((1/60)*$did_review_session[$this_quiz][$course][$this_user],1) . ' min ';

      }
    }
    $do_these_reports[$ind]['n'] = $this_user1; 
  }
  $end2 = getTime();
  if ($print_out_timings) { echo 'time to prepare scatter_plot parameters: ' . round($end2 - $start2, 4) . '<BR>'; }

  //$tmp_string_out .= "class: $course quiz: $this_quiz pre: " . round(100*$pre_score, 1) . " change_in_score: " . round(100*$change_in_score, 1) . " time: " . round((1/60)*$time_student_by_quiz[$this_quiz][$this_user],1) . "<BR>";

  $start = getTime();

  make_scatter_plot($do_these_reports, $data, $graph_options);

  if (0) {
    print "<BR>Do_these_reports: <BR><pre>";
    print_r($do_these_reports);
    print "</pre>";
    print "<BR>data: <BR><pre>";
    print_r($data);
    print "</pre>";
    print "<BR>graph_options: <BR><pre>";
    print_r($graph_options);
    print "</pre>";
  }
  $end = getTime();
  if ($print_out_timings) { echo 'time to make_scatter_plot(): ' . round($end - $start, 4) . '<BR>'; }

  print "<P>Generating url:";
  print "<BR>http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_improvement_concepts_scatter.php?";
  print http_build_query(my_array_merge($_POST, $_GET));
  print "</BR>";

//print "<P>$tmp_string_out</P>";



  //the following section prints out information on a per-concept basis.  Not sure it should be here.

  print "<div position:relative;>";

  print "<H2>Overall Change in Performance</H2>\n";
  print "<TABLE>";
  print "<TH>Quiz or Skill</TH><TH>Initial and Change in Score</TH><TH></TH><TH></TH><TH></TH></TR>";


  array_unshift($quizName, 'Overall');

  $height = "14px";
  $halfHeight = "7px";
  $threeQHeight = "10px";
  $oneQHeight = "5px";


  $do_these_reports = array();
  foreach ($quizName as $this_key => $this_quiz) {
    $do_these_reports[] = $this_quiz;
  }
  foreach ($report_on_these_skills as $key => $this_skill) {
    $do_these_reports[] = $this_skill;
  }


  foreach ($do_these_reports as $key => $this_quiz) {
  //foreach ($quizName as $this_key => $this_quiz) {
    if (in_array($this_quiz, $quizName)) {
      print "<TR><TD>Quiz: $this_quiz</TD>";
    }
    else {
      print "<TR><TD>Concept: $this_quiz</TD>";
    }
    print "<TD>";
    $pre_mean = compute_mean_val_2($pre_score_on_quiz[$this_quiz]);
//    print "Avg: " . round(100*$pre_mean, 1) . "% +/- ";
    $pre_std_dev = compute_std_dev_val_2($pre_mean, $pre_score_on_quiz[$this_quiz]);
    $pre_error = round(1.96*$pre_std_dev/sqrt(count_2($pre_score_on_quiz[$this_quiz])), 3);
//    print $pre_error . "%</TD><TD>";
    $change_mean = compute_mean_val_2($change_in_score_on_quiz[$this_quiz]);
    $change_std_dev = compute_std_dev_val_2($change_mean, $change_in_score_on_quiz[$this_quiz]);
    //$error = 1.96*compute_std_dev_val($change_mean, $change_std_dev/sqrt(count($change_in_score_on_quiz[$this_quiz]));
    $error = 1.96*sqrt($change_std_dev*$change_std_dev/(count_2($change_in_score_on_quiz[$this_quiz])) + $pre_std_dev*$pre_std_dev/(count_2($pre_score_on_quiz[$this_quiz])));
    $initial_title = "Initial Score: " . round(100*$pre_mean, 1) . " +/- " . round(100*$pre_error, 2) . "%";
    $second_title = "Average Change: " . round(100*$change_mean,1) . " +/- " . round(100*$error,2) . "%";
    $second_error_title = "Average Change: " . round(100*$change_mean,1) . " +/- " . round(100*$error,2) . "%";

    $height_px = 14;
    print_two_boxes(2*100*$pre_mean, $color[$this_quiz], $initial_title, 2*100*$pre_error, 2*100*$change_mean, $color[$this_quiz], $second_title, 2*100*$error, $second_error_title, $height_px);

    print "</TD><TD>";
    print "</TD><TD>";
//    print "pre_std_dev = $pre_std_dev; count(pre) = " . count($pre_score_on_quiz[$this_quiz]) . "; change_std_dev = $change_std_dev; count(change) = " . count($change_in_score_on_quiz[$this_quiz]) . ""; 
    print "</TD><TD></TD>";
    print "</TR>";
  }
  array_shift($quizName);

  //get the overall performance data:
  //and the overall performance for each quiz:
  $overall_pre_num_quiz = array();
  $overall_pre_den_quiz = array();
  $overall_post_num_quiz = array();
  $overall_post_den_quiz = array();
  $overall_pre_num_quiz['Overall'] = 0;
  $overall_pre_den_quiz['Overall'] = 0;
  $overall_post_num_quiz['Overall'] = 0;
  $overall_post_den_quiz['Overall'] = 0;

  foreach ($conceptBank_to_quizSkill as $this_c => $list_of_quizSkills) {
    foreach ($list_of_quizSkills as $key => $this_quizSkill) {
      $overall_pre_num_quiz['Overall'] += array_key_exists($this_c, $concept['pre']['num']) ? $concept['pre']['num'][$this_c] : 0;
      $overall_pre_den_quiz['Overall'] += array_key_exists($this_c, $concept['pre']['den']) ? $concept['pre']['den'][$this_c] : 0;
      $overall_post_num_quiz['Overall'] += array_key_exists($this_c, $concept['post']['num']) ? $concept['post']['num'][$this_c] : 0;
      $overall_post_den_quiz['Overall'] += array_key_exists($this_c, $concept['post']['den']) ? $concept['post']['den'][$this_c] : 0;

      if (!array_key_exists($this_quizSkill, $overall_pre_num_quiz)) $overall_pre_num_quiz[$this_quizSkill] = 0;
      if (!array_key_exists($this_quizSkill, $overall_pre_den_quiz)) $overall_pre_den_quiz[$this_quizSkill] = 0;
      if (!array_key_exists($this_quizSkill, $overall_post_num_quiz)) $overall_post_num_quiz[$this_quizSkill] = 0;
      if (!array_key_exists($this_quizSkill, $overall_post_den_quiz)) $overall_post_den_quiz[$this_quizSkill] = 0;
      if (!array_key_exists($this_c, $concept['pre']['num'])) $concept['pre']['num'][$this_c] = 0;
      if (!array_key_exists($this_c, $concept['pre']['den'])) $concept['pre']['den'][$this_c] = 0;
      if (!array_key_exists($this_c, $concept['post']['num'])) $concept['post']['num'][$this_c] = 0;
      if (!array_key_exists($this_c, $concept['post']['den'])) $concept['post']['den'][$this_c] = 0;
      $overall_pre_num_quiz[$this_quizSkill] += $concept['pre']['num'][$this_c];
      $overall_pre_den_quiz[$this_quizSkill] += $concept['pre']['den'][$this_c];
      $overall_post_num_quiz[$this_quizSkill] += $concept['post']['num'][$this_c];
      $overall_post_den_quiz[$this_quizSkill] += $concept['post']['den'][$this_c];

      if (!array_key_exists('Overall', $quizSkill_to_conceptBank)) $quizSkill_to_conceptBank['Overall'] = array();
      $quizSkill_to_conceptBank['Overall'][$this_c] = $this_c;

      foreach ($practice_time['post']['failure'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $time) {
          $overall_test_post['failure'][$this_quizSkill][] = $time;
          $overall_test_post['failure']['Overall'][] = $time;
        }
      }
      foreach ($practice_time['post']['success'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $time) {
          $overall_test_post['success'][$this_quizSkill][] = $time;
          $overall_test_post['success']['Overall'][] = $time;
	}
      }
    }
  }

  $db = 'webwork';
  $result = mysql_select_db("$db", $con);


  $height = "14px";
  $halfHeight = "7px";

  print "<H2>Overall Performance</H2>\n";
  print "<TABLE>";
  print "<TH>Quiz</TH><TH>Performance</TH><TH>Practice Time / Student (20 hrs)</TH><TH></TH></TR>";

  array_unshift($quizName, 'Overall');  

  foreach ($quizName as $this_key => $this_quiz) {

    print "<TR><TD>Quiz $this_quiz</TD>";
    print "<TD>";
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
    $post = $overall_post_den_quiz[$this_quiz] > 0 ? round(100*$overall_post_num_quiz[$this_quiz]/$overall_post_den_quiz[$this_quiz]) : 0;
    $pres = $overall_post_den_quiz[$this_quiz] > 0 ? round(100*$overall_pre_num_quiz[$this_quiz]/$overall_post_den_quiz[$this_quiz]) : 0;
    print "<div style='width:" . 2*$post . "px; height:$height; background-color:#123; border-right:1px #FFF solid; ' title='post: " . $post . "% (" . $overall_post_num_quiz[$this_quiz] . "/" . $overall_post_den_quiz[$this_quiz] . ")'>";
    print "<div style='width:" . 2*$pres . "px; height:$height; background-color:#456; border-right:1px #FFF solid; ' title='pre: " . $pres . "% (" . $overall_pre_num_quiz[$this_quiz] . "/" . $overall_post_den_quiz[$this_quiz] . ")'>";
    print "</div></div></div>";
    print "</TD>";


    $successful_time = 0;
    $failure_time = 0;
    $successful_time_count = 0;
    $failure_time_count = 0;

    if (isset($_REQUEST['show_data'])) {
      print "success times on quiz $this_quiz: ";
    }
    foreach ($overall_test_post['success'][$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['show_data'])) {
        print "$this_entry => $this_time, ";
      }
      $successful_time += $this_time;
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $successful_time_count++;
      }
    }

    if (isset($_REQUEST['show_data'])) {
      print "failure times on quiz $this_quiz: ";
    }
    foreach ($overall_test_post['failure'][$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['show_data'])) {
        print "$this_entry => $this_time, ";
      }
      $failure_time += $this_time;
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $failure_time_count++;
      }
    }

    $mean_successful_time = $successful_time / $successful_time_count;
    $mean_failure_time = $failure_time / $failure_time_count;

    $s2 = 0;
    $f2 = 0;

    foreach ($overall_test_post['success'][$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
      }
    }

    foreach ($overall_test_post['failure'][$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $f2 += ($this_time - $mean_failure_time)*($this_time - $mean_failure_time);
      }
    }

    $standard_deviation_success = $successful_time_count > 2 ? sqrt($s2/($successful_time_count - 1)) : 0;
    $standard_deviation_failure = $failure_time_count > 2 ? sqrt($f2/($failure_time_count - 1)) : 0;

    //95% confidence interval:
    $s_mtime_plus_stddev = $mean_successful_time + 1.96*$standard_deviation_success/sqrt($successful_time_count);
    $f_mtime_plus_stddev = $mean_failure_time + 1.96*$standard_deviation_failure/sqrt($failure_time_count);

    //$s_mtime_plus_stddev = $mean_successful_time + $standard_deviation_success;
    //$f_mtime_plus_stddev = $mean_failure_time + $standard_deviation_failure;


    if (isset($_REQUEST['show_data'])) {
      print "success stddev = $standard_deviation_success and mean is $mean_successful_time";
      print "failure stddev = $standard_devation_failure and mean is $mean_failure_time";
    }

    print "<TD>";

    $height = "14px";
    $halfHeight = "7px";

    //print out the time and error bars on the times:
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:12px; background-color:#0f0; border-right:1px #FFF solid; ' title='95% Confidence (time): " . formatTime(count($quizSkill_to_conceptBank[$this_quiz])*1.96*$standard_deviation_success/sqrt($successful_time_count)) . "'>";
    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:10px; background-color:#FFF; border-bottom:1px #FFF solid; '>";

    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$mean_successful_time/3600) . "px; height:$height; background-color:#0f0; border-right:1px #FFF solid; ' title='Avg Time: " . formatTime(count($quizSkill_to_conceptBank[$this_quiz])*$mean_successful_time) . "'>";


    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:$halfHeight; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:5px; background-color:#f00; border-right:1px #FFF solid; 'title='95% Confidence (time): " . formatTime(count($quizSkill_to_conceptBank[$this_quiz])*1.96*$standard_deviation_failure/sqrt($failure_time_count)) . "'>";
    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:3px; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($quizSkill_to_conceptBank[$this_quiz])*$mean_failure_time/3600) . "px; height:$halfHeight; background-color:#f00; border-right:1px #FFF solid; ' title='average time: " . formatTime(count($quizSkill_to_conceptBank[$this_quiz])*$mean_failure_time) . "'>";

    print "</div></div></div></div>";
    print "</div></div></div></div>";
    print "</TD>";


    print "<TD></TD>";
    print "</TR>";

  }


  print "<TR><TD COLSPAN=4><H2>Concept Performance</H2></TD></TR>\n";
  //print "<TABLE>";
  $prev_quiz = "";
  foreach ($conceptBank_to_quizSkill as $this_c => $list_of_quizSkills) {
    foreach ($list_of_quizSkills as $key => $this_quizSkill) {

      if (($this_quizSkill == $prev_quiz)) {
       //do nothing, as this is the previous quiz! 
      }
      else {
        $prev_quiz = $this_quizSkill;
        print "<TR><TH>$prev_quiz Concepts</TH><TH>Performance</TH><TH>Practice Time / Student (20 min)</TH><TH>";
        print isset($_REQUEST['single_problems']) ? "Problems" : "";
        print "</TH></TR>";
      }
      print "<TR><TD>" . preg_replace("/group\:/", "", $this_c) . ": </TD>";
      print "<TD>";
      print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
      $post = $concept['post']['den'][$this_c] > 0 ? round(100*$concept['post']['num'][$this_c]/$concept['post']['den'][$this_c]) : 0;
      $pres = $concept['post']['den'][$this_c] > 0 ? round(100*$concept['pre']['num'][$this_c]/$concept['post']['den'][$this_c]) : 0;

// print "pre is |$pres|; post is |$post|\n";
      print "<div style='width:" . 2*$post . "px; height:$height; background-color:#123; border-right:1px #FFF solid; ' title='post: " . $post . "% (" . $concept['post']['num'][$this_c] . "/" . $concept['post']['den'][$this_c] . ")'>";
      print "<div style='width:" . 2*$pres . "px; height:$height; background-color:#456; border-right:1px #FFF solid; ' title='pre: " . $pres . "% (" . $concept['pre']['num'][$this_c] . "/" . $concept['post']['den'][$this_c] . ")' >";

      print "</div></div></div>";
//<TD>Pre: " . $concept['pre']['num'][$this_c] . "/" . $concept['post']['den'][$this_c] . "      Post: " . $concept['post']['num'][$this_c] . "/" . $concept['post']['den'][$this_c] . "</TD>";

      //$problemNum = $concept['concept'][$this_concept]['source_file'];
      //$problemNum = $problemNumConcept[$this_c];
      //$problemDen = $problemDenConcept[$this_c];


      $successful_time = 0;
      $failure_time = 0;
      $successful_time_count = 0;
      $failure_time_count = 0;

      if (isset($_REQUEST['show_data'])) {
        print "success time on concept: $this_c ";
      }
      foreach ($practice_time['post']['success'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $this_time) {
          if (isset($_REQUEST['show_data'])) { 
            print "S: $this_user => $this_time, ";
          }
          $successful_time += $this_time;
          if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
            //do not take 'no practice time' into account
          }
          else {
            $successful_time_count++;
          }
        }
      }

      if (isset($_REQUEST['show_data'])) {
        print "failure time on concept: $this_c ";
      }
      foreach ($practice_time['post']['failure'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $this_time) {
          if (isset($_REQUEST['show_data'])) {
            print "F: $this_user => $this_time, ";
          }

          $failure_time += $this_time;
          if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
            //do not take 'no practice time' into account
          }
          else {
            $failure_time_count++;
          }
        }
      }

      $mean_successful_time = $successful_time / $successful_time_count;
      $mean_failure_time = $failure_time / $failure_time_count;

      $s2 = 0;
      $f2 = 0;
      foreach ($practice_time['post']['success'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $this_time) {
          if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
            //do not take 'no practice time' into account
          }
          else {
            $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
          }
        }
      }
      foreach ($practice_time['post']['failure'][$this_c] as $course => $tmp_array) {
	foreach ($tmp_array as $this_user => $this_time) {
          if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
            //do not take 'no practice time' into account
          }
          else {
            $f2 += ($this_time - $mean_failure_time)*($this_time - $mean_failure_time);
          }
        }
      }

      $standard_deviation_success = $successful_time_count > 2 ? sqrt((1/($successful_time_count - 1)) * $s2) : 0;
      $standard_deviation_failure = $failure_time_count > 2 ? sqrt((1/($failure_time_count-1)) * $f2) : 0;

      //95% confidence interval:
      $s_mtime_plus_stddev = $mean_successful_time + 1.96*$standard_deviation_success/sqrt($successful_time_count);
      $f_mtime_plus_stddev = $mean_failure_time + 1.96*$standard_deviation_failure/sqrt($failure_time_count);

      //$s_mtime_plus_stddev = $mean_successful_time + $standard_deviation_success;
      //$f_mtime_plus_stddev = $mean_failure_time + $standard_deviation_failure;

      if (isset($_REQUEST['show_data'])) {
        print "successful stddev = $standard_deviation_success and mean is $mean_successful_time";
        print "failure stddev = $standard_deviation_failrue and mean is $mean_failure_time";
      }

      print "<TD>";

      //print out the time and error bars on the times.
      print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
      print "<div style='width:" . round(10*$s_mtime_plus_stddev/60) . "px; height:12px; background-color:#0f0; border-right:1px #FFF solid; ' title='95% Confidence (time): " . formatTime(1.96*$standard_deviation_success/sqrt($successful_time_count)) . "'>";

      print "<div style='width:" . round(10*$s_mtime_plus_stddev/60) . "px; height:10px; background-color:#FFF; border-bottom:1px #FFF solid; '>";
      print "<div style='width:" . round(10*$mean_successful_time/60) . "px; height:$height; background-color:#0f0; border-right:1px #FFF solid; ' title='Avg Time: " . formatTime($mean_successful_time) . "'>";


      print "<div style='width:" . round(10*$s_mtime_plus_stddev/60) . "px; height:$halfHeight; background-color:#FFF; border-bottom:1px #FFF solid; '>";
      print "<div style='width:" . round(10*$f_mtime_plus_stddev/60) . "px; height:5px; background-color:#f00; border-right:1px #FFF solid; ' title='95% Confidence (time): " . formatTime(1.95*$standard_deviation_failure/sqrt($failure_time_count)) . "'>";
      print "<div style='width:" . round(10*$f_mtime_plus_stddev/60) . "px; height:3px; background-color:#FFF; border-bottom:1px #FFF solid; '>";
      //print "<div style='width:" . round(10*$f_mtime_plus_stddev/60) . "px; height:$halfHeight; background-color:#FFF; border-bottom:1px #FFF solid; '>";
      print "<div style='width:" . round(10*$mean_failure_time/60) . "px; height:$halfHeight; background-color:#f00; border-right:1px #FFF solid; ' title='average time: " . formatTime($mean_failure_time) . "'>";
      print "</div></div></div></div>";
      print "</div></div></div></div>";
      print "</TD>";


      print "<TD>";
      if (isset($_REQUEST['single_problems'])) {
        print "<TABLE BORDER=0>";
        $line1 = "<TR>";
        $line2 = "<TR>";
        $db = 'webwork';
        $result = mysql_select_db("$db", $con);

        foreach ($concept['concept'][$this_c]['source_file'] as $file => $success) {
        //foreach ($problemNum as $file => $success) {

          $query = 'SELECT problem_id FROM `SessionExperiment_problem` WHERE set_id="' . preg_replace("/group\:/", "", $this_c) . '" AND source_file="' . $file . '"';
echo 'query is ' . $query . '<BR>';
          $result = mysql_query($query, $con);
          $problem_id = -1;
          while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $problem_id = $row['problem_id'];
          }
          if ($problem_id > -1) {
            $this_p = $success['den'] > 0 ? round(30*$success['num']/$success['den']) : 0;
            $line1 .= "<TD>";
            $link_start = "<a href='http://" . $_SERVER['SERVER_NAME'] . "/webwork2/SessionExperiment/" . preg_replace("/group\:/", "", $this_c) . "/" . $problem_id . "/?user=s&effectiveUser=s' target='new'>";
            $link_end = "</a>";

            $line1 .= $link_start . $success['num'] . "/" . $success['den'] . $link_end . "</TD>";
            $line2 .= "<TD>";
            $line2 .= "<div style='width:30px; height:$halfHeight; background-color:#FFF; border:1px #CCC solid; ' title='Correct: " . $success['num'] . "/" . $success['den'] . "'>";
            $line2 .= "$link_start<div style='width:" . $this_p . "px; height:$halfHeight; background-color:#0f0; border-right:1px #FFF solid; '>";
            $line2 .= "</div>" . $link_end . "</div>";
            $line2 .= "</TD>";
          }
        }
        //print $line1 . "</TR>";
        print $line2 . "</TR>";
        print "</TABLE>";
      }
      print "</TD></TR>";
    
    }
  }

  unset($user);
//  unset($preNum);
//  unset($preDen);
//  unset($postNum);
//  unset($postDen);
//  unset($practiceTime);
//  unset($preQuizTime);
//  unset($postQuizTime);
//  unset($reviewTime);
//  unset($tookFinalQuiz);

  mysql_close($con);

}
else {
  //get all the concept skills for the problem banks:
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $concepts_skill = get_conceptBank_concepts_skills($con);


  //ask the user for the course:

  print "<form method='post'>";

  print "<fieldset>";
  print "<legend>Select Courses</legend>";
  print_course_checkboxes($courses);
  print "</fieldset>";

  array_unshift($quizName, 'Overall');

  print "<fieldset><legend>Select a Quiz</legend>";
  print_quiz_checkboxes($quizName);
  print "</fieldset>";

  print "<fieldset><legend>and/or Select Concepts</legend>";
  print_concept_skills($concepts_skill, 2);
  print "</fieldset>";

  print "<fieldset><legend>and/or Combine Quizzes or Skills</legend>";
  print_combined_quiz_skills_concepts($quizName, $concepts_skill, 2);
  print "</fieldset>";

  print "<fieldset><legend>and/or Course Grade Items</legend>";
  print_course_grade_items($con, $courses);
  print "</fieldset>";

  print "<fieldset><legend>Format of Plot</legend>";
  echo '<TABLE><TR><TH>Feature</TH><TH>Option</TH></TR>';
  echo '<TR><TD>Horizontal Axis: </TD><TD>';
  echo '<input type="checkbox" name="h_axis" value="pre score" />Pre Score, ';
  echo '<input type="checkbox" name="h_axis" value="post score" />Post Score, ';
  echo '<input type="checkbox" name="h_axis" value="change in score" />Change in Score, ';
  echo '<input type="checkbox" name="h_axis" value="percent change in score" />Percent Change in Score, ';
  echo '<input type="checkbox" name="h_axis" value="quiz" />Quizzes, ';
  echo '<input type="checkbox" name="h_axis" value="concepts" />Concepts, ';
  echo '<input type="checkbox" name="h_axis" value="combos" />Combinations, ';
  echo '<input type="checkbox" name="h_axis" value="grade item" />Grade Items, ';
  echo '<input type="checkbox" name="h_axis" value="practice time" />Practice Time, ';
  echo '<input type="checkbox" name="h_axis" value="help sessions" />Help Sessions, ';
  echo '<input type="checkbox" name="h_axis" value="all time" />Combined Time and Help, ';
  echo '<input type="checkbox" name="h_axis" value="webwork s-score" />WeBWorK s-score. ';
  echo '</TD></TR>';
  echo '<TR><TD>Horizontal Categories:</TD><TD> Continuous.  Discrete: bucket 1, bucket 2, ... </TD></TR>';
  echo '<TR><TD>Vertical Axis: </TD><TD>';
  echo '<input type="checkbox" name="v_axis" value="pre score" />Pre Score, ';
  echo '<input type="checkbox" name="v_axis" value="post score" />Post Score, ';
  echo '<input type="checkbox" name="v_axis" value="change score" />Change in Score, ';
  echo '<input type="checkbox" name="v_axis" value="percent change in score" />Percent Change in Score, ';
  echo '<input type="checkbox" name="v_axis" value="quiz" />Quizzes, ';
  echo '<input type="checkbox" name="v_axis" value="concepts" />Concepts, ';
  echo '<input type="checkbox" name="v_axis" value="combos" />Combinations, ';
  echo '<input type="checkbox" name="v_axis" value="grade item" />Grade Items, ';
  echo '<input type="checkbox" name="v_axis" value="practice time" />Practice Time, ';
  echo '<input type="checkbox" name="v_axis" value="help sessions" />Help Sessions, ';
  echo '<input type="checkbox" name="v_axis" value="all time" />Combined Time and Help, ';
  echo '<input type="checkbox" name="v_axis" value="webwork s-score" />WeBWorK s-score. ';
  echo '</TD></TR>';
  echo '<TR><TD>Vertical Categories: </TD><TD>Continuous.  Discrete: bucket 1, bucket 2, ... </TD></TR>';
  echo '<TR><TD>Size of Dot: </TD><TD>';
  echo '<input type="radio" name="size_level" value="pre score" />Pre Score, ';
  echo '<input type="radio" name="size_level" value="post score" />Post Score, ';
  echo '<input type="radio" name="size_level" value="grade item completed" />Grade Items Completed, ';
  echo '<input type="radio" name="size_level" value="practice time" CHECKED />Practice Time, ';
  echo '<input type="radio" name="size_level" value="help sessions" />Help Sessions, ';
  echo '<input type="radio" name="size_level" value="all time" />Combined Time and Help, ';
  echo '<input type="radio" name="size_level" value="webwork s-score" />WeBWorK s-score, ';
  echo '<input type="radio" name="size_level" value="off" />None. ';
  echo '</TD></TR>';
  echo '<TR><TD>Intensity of Dot: </TD><TD>';
  echo '<input type="radio" name="opacity_level" value="quiz" />Quizzes, ';
  echo '<input type="radio" name="opacity_level" value="concepts" />Concepts, ';
  echo '<input type="radio" name="opacity_level" value="combos" />Combinations, ';
  echo '<input type="radio" name="opacity_level" value="grade item" />Grade Items, ';
  echo '<input type="radio" name="opacity_level" value="practice time" />Practice Time, ';
  echo '<input type="radio" name="opacity_level" value="help sessions" />Help Sessions, ';
  echo '<input type="radio" name="opacity_level" value="all time" />Combined Time and Help, ';
  echo '<input type="radio" name="opacity_level" value="webwork s-score" />WeBWorK s-score, ';
  echo '<input type="radio" name="opacity_level" value="off" CHECKED />None. ';
  echo '</TD></TR>';
  echo '<TR><TD>Dots Link to: </TD><TD>';
  echo 'LIST OF POSSIBLE REPORTS AND WEBWORK ACCOUNT.';
  echo '</TD></TR>';
  echo '</TABLE>';
  echo '</fieldset>';

  print "<fieldset><legend>Select Computation and Display Options</legend>";
  print_time();
  print_review_sessions();
  print_single_problems();
  print_retest_problems();
  print_show_data();
  print_print_user_name();
  print_no_zero_times();
  print_no_jitter();
  print_remove_std_dev_band();
  print_select_student_groups();
  print "</fieldset>";

  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";
  print "</body>";
  print "</html>";
  mysql_close($con);

}
