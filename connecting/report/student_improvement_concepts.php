<?php

include("../access.php");
include("common.php");

$quizName = get_quizNames();

$color = get_colors();

$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

$max_num_courses = count($courses);
$max_num_quizName = count($quizName);

$print_out_debug = 0;

if (isset($_REQUEST['report_for_this_course'])) {
  $do_these_courses = $_REQUEST['report_for_this_course'];
  $max_num_courses = count($do_these_courses);

  $do_time = isset($_REQUEST['time']) ? $_REQUEST['time'] : false ;
  $do_review_sessions = isset($_REQUEST['review_sessions']) ? $_REQUEST['review_sessions'] : false ;
  $do_single_problems = isset($_REQUEST['single_problems']) ? $_REQUEST['single_problems'] : false ;
  $do_retest_problems = isset($_REQUEST['retest_problems']) ? $_REQUEST['retest_problems'] : false ;

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }


  $user = array();
  $concept = array();

  $conceptsOnQuiz = array();
  $preConceptRight = array();

  $preNumConcept = array();
  $preDenConcept = array();
  $postNumConcept = array();
  $postDenConcept = array();

  $problemNumConcept = array();
  $problemDenConcept = array();

  $retestNumConcept = array();
  $retestDenConcept = array();

  $concept_time_post_success = array();
  $concept_time_post_failure = array();
  $concept_time_pre_success = array();
  $concept_time_pre_failure = array();


  //The user has asked for this course, so let's give them the results.
  
  for ($c = 0; $c < $max_num_courses; $c++) {
    $course = $do_these_courses[$c];
    $valid_user = valid_users($con, $course, 'validForStatistics="1"');

    $user = get_users_from_course($con, $course, $user, true);

    $db = "webwork";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);  

    //get all of the initial performance on each of the concepts:

    for ($q = 0; $q < $max_num_quizName; $q++) {
      $quiz = $quizName[$q];
      $query = 'SELECT ' . $course . '_problem_user.user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "' . $quiz .'"';
      $result = mysql_query($query, $con);

      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_score = $row['status'];
        $this_attempt = $row['attempted'];
        $this_concept = $row['concept'];
        $this_problem_source = $row['problem_file'];

        if ($valid_user[$this_user]) {      
          //initialize data?
          $concept[$this_concept] = $quiz;
          $preDenConcept[$this_concept]++;
          $conceptsOnQuiz[$quiz][$this_concept] = 1;
          if ($this_score == 1) {
            $preNumConcept[$this_concept]++;
            $postNumConcept[$this_concept]++;
            $postDenConcept[$this_concept]++;
            $preConceptRight[$this_concept][$course][$this_user] = 1;
            $concept_time_pre_success[$this_concept][$course][$this_user] = 1;
          }
          if ($this_attempt == 1) {
            $problemNumConcept[$this_concept][$this_problem_source] += $this_score == 1 ? 1 : 0;
            $problemDenConcept[$this_concept][$this_problem_source]++;
          }
          if (($this_score < 1) || ($this_attempt != 1)) {
            $concept_time_pre_failure[$this_concept][$course][$this_user] = 1;
          }
        }
      }
    }

    //get all the final quiz scores for the users in this course:
    foreach ($user[$course] as $this_user) {

      for ($q = 0; $q < $max_num_quizName; $q++) {
        $quiz = $quizName[$q];
        $query = 'SELECT user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "finalQuiz_' . $this_user . '_' . $quiz . '"';
        $result = mysql_query($query, $con);

        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $this_user = $row['user_id'];
          $this_score = $row['status'];
          $this_attempt = $row['attempted'];
          $this_concept = $row['concept'];
          $this_problem_file = $row['problem_file'];
          if ($valid_user[$this_user]) {
            $postNumConcept[$this_concept] += ($this_score == 1) ? 1 : 0;
            $postDenConcept[$this_concept]++;
            if ($this_attempt == 1) {
              $problemNumConcept[$this_concept][$this_problem_file] += ($this_score == 1) ? 1 : 0;
              $problemDenConcept[$this_concept][$this_problem_file]++;
            }
            if ($this_score == 1) {
              $concept_time_post_success[$this_concept][$course][$this_user] = 0;
            }
            else {
              $concept_time_post_failure[$this_concept][$course][$this_user] = 0;
            }
          }
        }
      }
    }

    //get all the practice time for users in this course:
    $db = 'wwSession';
    $result = mysql_select_db("$db", $con);
    $query = 'SELECT user_name, concept_bank, attempted_date, submitted_date FROM `attempts` WHERE course_name="' . $course . '" AND submitted_date != "0000-00-00 00:00:00"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_name'];
      if ($valid_user[$this_user]) {
        $t1 = 0; $t2 = 0;
        $t1 = $row['attempted_date'];
        $t2 = $row['submitted_date'];
        $this_c = "group:" . $row['concept_bank'];
        $acc_time = date(strtotime($t2) - strtotime($t1));
        if (($acc_time > 0) && ($acc_time < 20*60)) {
          if (array_key_exists($this_user, $concept_time_post_success[$this_c][$course])) {
            $concept_time_post_success[$this_c][$course][$this_user] += $acc_time;
          }
          else {
            $concept_time_post_failure[$this_c][$course][$this_user] += $acc_time;
          }
        }
      }
    }

  }  // end of the course loop


  foreach ($user as $course => $tmp_array) {
    foreach ($tmp_array as $this_user => $val) {
      foreach ($concept as $this_c => $val) {
        if (array_key_exists($this_user, $concept_time_post_success[$this_c][$course])) {
          //do nothing.  they are in the post time data
        }
        else if (array_key_exists($this_user, $concept_time_post_failure[$this_c][$course])) {
          //do nothing.  they are in the post failure data
        }
        else if (array_key_exists($this_user, $preConceptRight[$this_c][$course])) {
          //do nothing.  They got the initial question right, so they won't need to practice.
        }
        else {
          //put them in the post practie data with a time of 0:
          $concept_time_post_failure[$this_c][$course][$this_user] = 0;
        }
      }
    }
  }
  //get the overall performance data for each student for each quiz:
  $post_student_by_quiz_num_score = array();
  $post_student_by_quiz_den_score = array();
  $pre_student_by_quiz_num_score = array();
  $pre_student_by_quiz_den_score = array();

  foreach ($concept as $this_c => $this_quiz) {
    foreach ($user as $course => $tmp_array) {
      $post_student_by_quiz_num_score[$this_quiz][$course] = array();
      $post_student_by_quiz_den_score[$this_quiz][$course] = array();
      $pre_student_by_quiz_num_score[$this_quiz][$course] = array();
      $pre_student_by_quiz_den_score[$this_quiz][$course] = array();
      foreach ($tmp_array as $key2 => $this_user) {
        $post_student_by_quiz_num_score[$this_quiz][$course][$this_user] = 0;
        $post_student_by_quiz_den_score[$this_quiz][$course][$this_user] = 0;
        $post_student_by_quiz_num_score['Overall'][$course][$this_user] = 0;
        $post_student_by_quiz_den_score['Overall'][$course][$this_user] = 0;

        $pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] = 0;
        $pre_student_by_quiz_den_score[$this_quiz][$course][$this_user] = 0;
        $pre_student_by_quiz_num_score['Overall'][$course][$this_user] = 0;
        $pre_student_by_quiz_den_score['Overall'][$course][$this_user] = 0;
      }
    }
  }



  foreach ($concept as $this_c => $this_quiz) {
    foreach ($user as $course => $tmp_array) {
      foreach ($tmp_array as $this_u => $this_user) {
        if (array_key_exists($this_user, $concept_time_post_success[$this_c][$course])) {
          $post_student_by_quiz_num_score[$this_quiz][$course][$this_user] += 1;
          $post_student_by_quiz_den_score[$this_quiz][$course][$this_user] += 1;
          $post_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
          $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
        }
        if (array_key_exists($this_user, $concept_time_post_failure[$this_c][$course])) {
          $post_student_by_quiz_den_score[$this_quiz][$course][$this_user] += 1;
          $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
        }
        if (array_key_exists($this_user, $concept_time_pre_success[$this_c][$course])) {
          $pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] += 1;
          $pre_student_by_quiz_den_score[$this_quiz][$course][$this_user] += 1;
          $post_student_by_quiz_num_score[$this_quiz][$course][$this_user] += 1;
          $post_student_by_quiz_den_score[$this_quiz][$course][$this_user] += 1;
          $pre_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
          $pre_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
          $post_student_by_quiz_num_score['Overall'][$course][$this_user] += 1;
          $post_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
        }
        if (array_key_exists($this_user, $concept_time_pre_failure[$this_c][$course])) {
          $pre_student_by_quiz_den_score[$this_quiz][$course][$this_user] += 1;
          $pre_student_by_quiz_den_score['Overall'][$course][$this_user] += 1;
        }
      }
    }
  }

  //get the pre score and change in score:
  $pre_score_on_quiz = array();
  $change_in_score_on_quiz = array();


  foreach ($quizName as $key1 => $this_quiz) {
    foreach ($user as $course => $tmp_array) {
      foreach ($tmp_array as $key2 => $this_user) {
//      $pre_score_on_quiz[$this_quiz][$this_user] = $pre_student_by_quiz_num_score[$this_quiz][$this_user] / $pre_student_by_quiz_den_score[$this_quiz][$this_user];
        $pre_score_on_quiz[$this_quiz][$course][$this_user] = $pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] / $post_student_by_quiz_den_score[$this_quiz][$course][$this_user];
        $change_in_score_on_quiz[$this_quiz][$course][$this_user] = (($post_student_by_quiz_num_score[$this_quiz][$course][$this_user] / $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]) - ($pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] / $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]));
        if ($print_out_debug) {
          print "<BR>computing for quiz $this_quiz and this_user $this_user: (" . 
	  	$post_student_by_quiz_num_score[$this_quiz][$course][$this_user] . 
		" / " . 
		$post_student_by_quiz_den_score[$this_quiz][$course][$this_user] . 
		") - (" . 
		$pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] . 
		" / " . 
		$post_student_by_quiz_den_score[$this_quiz][$course][$this_user] . ")";
          print (($post_student_by_quiz_num_score[$this_quiz][$course][$this_user] / $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]) - ($pre_student_by_quiz_num_score[$this_quiz][$course][$this_user] / $post_student_by_quiz_den_score[$this_quiz][$course][$this_user]));
          print " = " . $change_in_score_on_quiz[$this_quiz][$course][$this_user] . ".";
	}
      }
    }

    foreach ($user as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $val2) {
//      $pre_score_on_quiz['Overall'][$this_user] = $pre_student_by_quiz_num_score['Overall'][$this_user] / $pre_student_by_quiz_den_score['Overall'][$this_user];
        $pre_score_on_quiz['Overall'][$course][$this_user] = $pre_student_by_quiz_num_score['Overall'][$course][$this_user] / $post_student_by_quiz_den_score['Overall'][$course][$this_user];
        $change_in_score_on_quiz['Overall'][$course][$this_user] = ($post_student_by_quiz_num_score['Overall'][$course][$this_user] / $post_student_by_quiz_den_score['Overall'][$course][$this_user]) - ($pre_student_by_quiz_num_score['Overall'][$course][$this_user] / $post_student_by_quiz_den_score['Overall'][$course][$this_user]);
      }
    }
  }

  print "<H2>Overall Change in Performance</H2>\n";
  print "<TABLE>";
  print "<TH>Quiz</TH><TH>Initial and Change in Score</TH><TH></TH><TH></TH><TH></TH></TR>";

  array_unshift($quizName, 'Overall');

  $height = "14px";
  $halfHeight = "7px";
  $threeQHeight = "10px";
  $oneQHeight = "5px";

  foreach ($quizName as $this_key => $this_quiz) {
    if ($print_out_debug) {
	print "<P>quiz is $this_quiz.  pre_score_on_quiz array is: <pre>";
	print_r($pre_score_on_quiz[$this_quiz]);
	print "</pre><BR>";

	print "<P>Change in Score for quiz: $this_quiz: <PRE>";
	print_r($change_in_score_on_quiz[$this_quiz]);
	print "</PRE><P>";
    }
    print "<TR><TD>$this_quiz</TD>";
    print "<TD>";
    $link = "http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_improvement_concepts_scatter.php?";
    $link .= "report_for_this_quiz[0]=" . $this_quiz . "&";
    $link .= "time=time&";
    $link .= "reg1=reg1&reg2=reg2&reg3=reg3&";
    $link .= http_build_query($_POST);

    $pre_mean = compute_mean_val_2($pre_score_on_quiz[$this_quiz]);
//    print "Avg: " . round(100*$pre_mean, 1) . "% +/- ";
    $pre_std_dev = compute_std_dev_val_2($pre_mean, $pre_score_on_quiz[$this_quiz]);
    $pre_error = round(1.96*$pre_std_dev/sqrt(count_2($pre_score_on_quiz[$this_quiz])), 3);
//    print $pre_error . "%</TD><TD>";
    $change_mean = compute_mean_val_2($change_in_score_on_quiz[$this_quiz]);
    $change_std_dev = compute_std_dev_val_2($change_mean, $change_in_score_on_quiz[$this_quiz]);
    //$error = 1.96*compute_std_dev_val($change_mean, $change_std_dev/sqrt(count($change_in_score_on_quiz[$this_quiz]));
    $error = 1.96*sqrt($change_std_dev*$change_std_dev/(count_2($change_in_score_on_quiz[$this_quiz])) + $pre_std_dev*$pre_std_dev/(count_2($pre_score_on_quiz[$this_quiz])));
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
    print "<a href='$link'>";
    //The initial score box:
    print "<div style='width:" . (2*100*$pre_mean) . "px; height:$height; background-color:" . $color[$this_quiz] . ";' title='Initial Score: " . round(100*$pre_mean,1) . " +/- " . round(100*$pre_error, 2) . "%'>";

    //The Change in score box;
    print "<div style='position:relative; top:-1px; left:" . (2*100*$pre_mean) . "px; width:" . (-2 + 2*100*$change_mean) . "px; height:14px; background-color:#FFF; border:1px " . $color[$this_quiz] . " solid;' title='Average Change: " . round(100*$change_mean,1) . " +/- " . round(100*$error,2) . "%'></div>";

    //The Average Change error bar:
    print "<div style='position:relative; top:" . (-14+3) . "px; left:" . (-1 + 2*100*$pre_mean + 2*100*$change_mean - 2*100*$error) . "px; width:" . (4*100*$error) . "px; height:3px; background-color:" . $color[$this_quiz] . "; ' title='Average Change: " . round(100*$change_mean,1) . " +/- " . round(100*$error,2) . "%'></div>";

    //The grey initial error bar:
    print "<div style='position:relative; top:" . (-14+0) . "px; left:" . (2*100*$pre_mean - 2*100*$pre_error) . "px; width:" . (4*100*$pre_error) . "px; height:3px; background-color:" . $color[$this_quiz] . ";'></div>";

    //The white initial error bar:
    print "<div style='position:relative; top:" . (-14-3) . "px; left:" . (2*100*$pre_mean - 2*100*$pre_error) . "px; width:" . (2*100*$pre_error) . "px; height:3px; background-color:#FFF;'></div>";
    print "</div>";
    print "</a>";
    print "</div>";
    print "</TD><TD>";
    print "</TD><TD>";
//    print "pre_std_dev = $pre_std_dev; count(pre) = " . count($pre_score_on_quiz[$this_quiz]) . "; change_std_dev = $change_std_dev; count(change) = " . count($change_in_score_on_quiz[$this_quiz]) . ""; 
    print "</TD><TD></TD>";
    print "</TR>";
  }

  shift($quizName);

  //get the overall performance data:
  //and the overall performance for each quiz:
  $overall_pre_num_quiz = array();
  $overall_pre_den_quiz = array();
  $overall_post_num_quiz = array();
  $overall_post_den_quiz = array();
  foreach ($concept as $this_c => $this_quiz) {
    $overall_pre_num_quiz['Overall'] += $preNumConcept[$this_c];
    $overall_pre_den_quiz['Overall'] += $preDenConcept[$this_c];
    $overall_post_num_quiz['Overall'] += $postNumConcept[$this_c];
    $overall_post_den_quiz['Overall'] += $postDenConcept[$this_c];

    $overall_pre_num_quiz[$this_quiz] += $preNumConcept[$this_c];
    $overall_pre_den_quiz[$this_quiz] += $preDenConcept[$this_c];
    $overall_post_num_quiz[$this_quiz] += $postNumConcept[$this_c];
    $overall_post_den_quiz[$this_quiz] += $postDenConcept[$this_c];

    $conceptsOnQuiz['Overall'][$this_c] = 1;

    foreach ($concept_time_post_failure[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $time) {
        $overall_test_post_failure[$this_quiz][] = $time;
        $overall_test_post_failure['Overall'][] = $time;
      }
    }
    foreach ($concept_time_post_success[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $time) {
        $overall_test_post_success[$this_quiz][] = $time;
        $overall_test_post_success['Overall'][] = $time;
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
    foreach ($overall_test_post_success[$this_quiz] as $this_entry => $this_time) {
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
    foreach ($overall_test_post_failure[$this_quiz] as $this_entry => $this_time) {
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

    foreach ($overall_test_post_success[$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
      }
    }

    foreach ($overall_test_post_failure[$this_quiz] as $this_entry => $this_time) {
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
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:12px; background-color:#0f0; border-right:1px #FFF solid; ' title='95% Confidence (time): " . formatTime(count($conceptsOnQuiz[$this_quiz])*1.96*$standard_deviation_success/sqrt($successful_time_count)) . "'>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:10px; background-color:#FFF; border-bottom:1px #FFF solid; '>";

    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$mean_successful_time/3600) . "px; height:$height; background-color:#0f0; border-right:1px #FFF solid; ' title='Avg Time: " . formatTime(count($conceptsOnQuiz[$this_quiz])*$mean_successful_time) . "'>";


    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:$halfHeight; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:5px; background-color:#f00; border-right:1px #FFF solid; 'title='95% Confidence (time): " . formatTime(count($conceptsOnQuiz[$this_quiz])*1.96*$standard_deviation_failure/sqrt($failure_time_count)) . "'>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:3px; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$mean_failure_time/3600) . "px; height:$halfHeight; background-color:#f00; border-right:1px #FFF solid; ' title='average time: " . formatTime(count($conceptsOnQuiz[$this_quiz])*$mean_failure_time) . "'>";

    print "</div></div></div></div>";
    print "</div></div></div></div>";
    print "</TD>";


    print "<TD></TD>";
    print "</TR>";

  }


  print "<TR><TD COLSPAN=4><H2>Concept Performance</H2></TD></TR>\n";
  //print "<TABLE>";
  $prev_quiz = "";

print "<PRE>";
print_r($concept);
print "</PRE>";

  foreach ($concept as $this_c => $this_quiz) {

    if (($this_quiz == $prev_quiz)) {
     //do nothing, as this is the previous quiz! 
    }
    else {
      $prev_quiz = $this_quiz;
      print "<TR><TH>$prev_quiz Concepts</TH><TH>Performance</TH><TH>Practice Time / Student (20 min)</TH><TH>";
      print isset($_REQUEST['single_problems']) ? "Problems" : "";
      print "</TH></TR>";
    }
    print "<TR><TD>" . preg_replace("/group\:/", "", $this_c) . ": </TD>";
    print "<TD>";
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
    $post = $postDenConcept[$this_c] > 0 ? round(100*$postNumConcept[$this_c]/$postDenConcept[$this_c]) : 0;
    $pres = $postDenConcept[$this_c] > 0 ? round(100*$preNumConcept[$this_c]/$postDenConcept[$this_c]) : 0;

// print "pre is |$pres|; post is |$post|\n";
    print "<div style='width:" . 2*$post . "px; height:$height; background-color:#123; border-right:1px #FFF solid; ' title='post: " . $post . "% (" . $postNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")'>";
    print "<div style='width:" . 2*$pres . "px; height:$height; background-color:#456; border-right:1px #FFF solid; ' title='pre: " . $pres . "% (" . $preNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")' >";

    print "</div></div></div>";
//<TD>Pre: " . $preNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . "      Post: " . $postNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . "</TD>";
    $problemNum = $problemNumConcept[$this_c];
    $problemDen = $problemDenConcept[$this_c];


    $successful_time = 0;
    $failure_time = 0;
    $successful_time_count = 0;
    $failure_time_count = 0;

    if (isset($_REQUEST['show_data'])) {
      print "success time on concept: $this_c ";
    }

print_r($concept_time_post_success);


    foreach ($concept_time_post_success[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $this_time) {
print "HERE!";
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
    foreach ($concept_time_post_failure[$this_c] as $course => $tmp_array) {
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
    foreach ($concept_time_post_success[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $this_time) {
        if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
          //do not take 'no practice time' into account
        }
        else {
          $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
        }
      }
    }
    foreach ($concept_time_post_failure[$this_c] as $course => $tmp_array) {
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
      foreach ($problemNum as $file => $success) {

        $query = 'SELECT problem_id FROM `SessionExperiment_problem` WHERE set_id="' . preg_replace("/group\:/", "", $this_c) . '" AND source_file="' . $file . '"';
        $result = mysql_query($query, $con);
        $problem_id = -1;
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $problem_id = $row['problem_id'];
        }
        if ($problem_id > -1) {
          $this_p = $problemDen[$file] > 0 ? round(30*$success/$problemDen[$file]) : 0;
          $line1 .= "<TD>";
          $link_start = "<a href='http://" . $_SERVER['SERVER_NAME'] . "/webwork2/SessionExperiment/" . preg_replace("/group\:/", "", $this_c) . "/" . $problem_id . "/?user=s&effectiveUser=s' target='new'>";
          $link_end = "</a>";

          $line1 .= $link_start . "$success/$problemDen[$file]" . $link_end . "</TD>";
          $line2 .= "<TD>";
          $line2 .= "<div style='width:30px; height:$halfHeight; background-color:#FFF; border:1px #CCC solid; ' title='Correct: $success/$problemDen[$file]'>";
          $line2 .= "$link_start<div style='width:" . $this_p . "px; height:$halfHeight; background-color:#0f0; border-right:1px #FFF solid; '>";
          $line2 .= "</div>" . $link_end . "</div>";
          //$line2 .=  $success . "/" . $problemDen[$file] . "</TD>";
          $line2 .= "</TD>";
        }
      }
      //print $line1 . "</TR>";
      print $line2 . "</TR>";
      print "</TABLE>";
    }
    print "</TD></TR>";
    
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
  //ask the user for the course:

  print "<form method='post'>";

  print "<P>Select Courses</P>";
  
  for ($c = 0; $c < $max_num_courses; $c++) {
    print "<input name='report_for_this_course[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
  }

  print "<P>Select Options</P>";
  print "<input name='time' type='checkbox' value='time'>time<BR>\n";
  print "<input name='review_sessions' type='checkbox' value='review_sessions'>Review Sessions<BR>\n";
  print "<input name='single_problems' type='checkbox' value='single_problems' checked>Single Problem Success<BR>\n";
  print "<input name='retest_problems' type='checkbox' value='retest_problems'>Retest Problems<BR>\n";
  print "<input name='show_data' type='checkbox' value='show_data'>Show Data<BR>\n";
  //print "<input name='print_user_name' type='text' id='print_user_name' value='print_user_name'>\n";
  print "<input name='no_zero_times' type='checkbox' value='no_zero_times'>Remove times of zero from average times<BR>\n";
  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";

}
