<?php

include("../access.php");
include("common.php");

$quizName = get_quizNames();

$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

if (isset($_REQUEST['courses'])) {
  $do_these_courses = $_REQUEST['courses'];

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

  $concept_test_post_success = array();
  $concept_test_post_failure = array();


  //get the description of the concept banks
  $conceptBank_description = get_conceptBankDescription($con);


  //The user has asked for this course, so let's give them the results.
  $max_num_courses = count($do_these_courses);
  $max_num_quizzes = count($quizName);
  for ($c = 0; $c < $max_num_courses; $c++) {

    $course = $do_these_courses[$c]; 

    $user = get_users_from_course($con, $course, $user, true);

    $db = 'webwork';
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    //get all of the initial performance on each of the concepts:
    for ($q = 0; $q < $max_num_quizzes; $q++) {
      $quiz = $quizName[$q];
      $query = 'SELECT ' . $course . '_problem_user.user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "' . $quiz .'"';
      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_score = $row['status'];
        $this_attempt = $row['attempted'];
        $this_concept = $row['concept'];
        $this_problem_source = $row['problem_file'];

        if (isset($user[$course][$this_user])) {
          //initialize data?

          $concept[$this_concept] = $quiz;
          $preDenConcept[$this_concept]++;
          $conceptsOnQuiz[$quiz][$this_concept] = 1;
          if ($quiz == "pca") {
            if ($this_score == 1) {
              $preNumConcept[$this_concept]++;
              $preConceptRight[$this_concept][$course][$this_user] = 1;
            }
            if ($this_attempt == 1) {
              $problemNumConcept[$this_concept][$this_problem_source] += $this_score == 1 ? 1 : 0;
              $problemDenConcept[$this_concept][$this_problem_source]++;
            }
            $preDenConcept[$this_concept]++;
          }
          else {
            if ($this_score == 1) {
              $preNumConcept[$this_concept]++;
              $postNumConcept[$this_concept]++;
              $postDenConcept[$this_concept]++;
              $preConceptRight[$this_concept][$course][$this_user] = 1;
            }
            if ($this_attempt == 1) {
              $problemNumConcept[$this_concept][$this_problem_source] += $this_score == 1 ? 1 : 0;
              $problemDenConcept[$this_concept][$this_problem_source]++;
            }
          }
        }
      }
    }


    //get all the final quiz scores for the users in this course:
    foreach ($user[$course] as $this_user) {

      for ($q = 0; $q < $max_num_quizzes; $q++) {
        $quiz = $quizName[$q];
        if ($quiz == "pca") {
          $query = 'SELECT user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "finalQuiz_' . $quiz . '" AND user_id="' . $this_user . '"';
        }
        else {
          $query = 'SELECT user_id, ' . $course . '_problem.source_file as concept, status, attempted, ' . $course . '_problem_user.source_file as problem_file FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON CONCAT(' . $course . '_problem.set_id, ",v1") = ' . $course . '_problem_user.set_id AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id WHERE ' . $course . '_problem.set_id = "finalQuiz_' . $this_user . '_' . $quiz . '"';
        }
        $result = mysql_query($query, $con);

        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $this_user = $row['user_id'];
          $this_score = $row['status'];
          $this_attempt = $row['attempted'];
          $this_concept = $row['concept'];
          $this_problem_file = $row['problem_file'];

 	  if (isset($user[$course][$this_user])) {
            $postNumConcept[$this_concept] += $this_score == 1 ? 1 : 0;
            $postDenConcept[$this_concept]++;
            if ($this_attempt == 1) {
              $problemNumConcept[$this_concept][$this_problem_file] += $this_score == 1 ? 1 : 0;
              $problemDenConcept[$this_concept][$this_problem_file]++;
            }
            if ($this_score == 1) {
              $concept_test_post_success[$this_concept][$course][$this_user] = 0;
            }
            else {
              $concept_test_post_failure[$this_concept][$course][$this_user] = 0;
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
      if (isset($user[$course][$this_user])) {
        $t1 = 0; $t2 = 0;
        $t1 = $row['attempted_date'];
        $t2 = $row['submitted_date'];
        $this_c = "group:" . $row['concept_bank'];
        $acc_time = date(strtotime($t2) - strtotime($t1));
        if (($acc_time > 0) && ($acc_time < 20*60)) {
          if (array_key_exists($this_user, $concept_test_post_success[$this_c][$course])) {
            $concept_test_post_success[$this_c][$this_user][$course] += $acc_time;
          }
          else {
            $concept_test_post_failure[$this_c][$course][$this_user] += $acc_time;
          }
        }
      }
    }
  }  //end the course loop.


  foreach ($user as $course => $user_course) {
    foreach ($user_course as $this_user => $val) {
      foreach ($concept as $this_c => $val) {
        if (array_key_exists($this_user, $concept_test_post_success[$this_c][$course])) {
          //do nothing.  they are in the post time data
        }
        else if (array_key_exists($this_user, $concept_test_post_failure[$this_c][$course])) {
          //do nothing.  they are in the post failure data
        }
        else if (array_key_exists($this_user, $preConceptRight[$this_c][$course])) {
          //do nothing.  They got the initial question right, so they won't need to practice.
        }
        else {
          //put them in the post practie data with a time of 0:
          $concept_test_post_failure[$this_c][$course][$this_user] = 0;
        }
      }
    }
  }

  //get the overall performance data:
  //and the overall performance for each quiz:
  $overall_pre_num_quiz = array();
  $overall_pre_den_quiz = array();
  $overall_post_num_quiz = array();
  $overall_post_den_quiz = array();
  foreach ($concept as $this_c => $this_quiz) {
    $overall_pre_num_quiz['All'] += $preNumConcept[$this_c];
    $overall_pre_den_quiz['All'] += $preDenConcept[$this_c];
    $overall_post_num_quiz['All'] += $postNumConcept[$this_c];
    $overall_post_den_quiz['All'] += $postDenConcept[$this_c];

    $overall_pre_num_quiz[$this_quiz] += $preNumConcept[$this_c];
    $overall_pre_den_quiz[$this_quiz] += $preDenConcept[$this_c];
    $overall_post_num_quiz[$this_quiz] += $postNumConcept[$this_c];
    $overall_post_den_quiz[$this_quiz] += $postDenConcept[$this_c];

    $conceptsOnQuiz['All'][$this_c] = 1;

    foreach ($concept_test_post_failure[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $time) {
        $overall_test_post_failure[$this_quiz][] = $time;
        $overall_test_post_failure['All'][] = $time;
      }
    }
    foreach ($concept_test_post_success[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $time) {
        $overall_test_post_success[$this_quiz][] = $time;
        $overall_test_post_success['All'][] = $time;
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

  array_unshift($quizName, 'All');  

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

    $successful_time_no_zero = 0;
    $failure_time_no_zero = 0;
    $successful_time_count_no_zero = 0;
    $failure_time_count_no_zero = 0;

    if (isset($_REQUEST['show_data'])) {
      print "success times on quiz $this_quiz: ";
    }
    foreach ($overall_test_post_success[$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['show_data'])) {
        print "$this_entry => $this_time, ";
      }
      $successful_time += $this_time;
      if ($this_time == 0) {
      }
      else {
        $successful_time_no_zero += $this_time;
      }
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $successful_time_count++;
        if ($this_time == 0) {
        }
        else {
          $successful_time_count_no_zero += $this_time;
        }
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
      if ($this_time == 0) {

      }
      else {
        $failure_time_no_zero += $this_time;
      }
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $failure_time_count++;
        if ($this_time == 0) {

        }
        else {
          $failure_time_count_no_zero++;
        }
      }
    }

    $mean_successful_time = $successful_time / $successful_time_count;
    $mean_failure_time = $failure_time / $failure_time_count;

    $mean_successful_time_no_zero = $successful_time_no_zero / $successful_time_count_no_zero;
    $mean_failure_time_no_zero = $failure_time_no_zero / $failure_time_count_no_zero;

    $s2 = 0;
    $f2 = 0;

    $s2_no_zero = 0;
    $f2_no_zero = 0;

    foreach ($overall_test_post_success[$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
        if ($this_time == 0) {
          
        }
        else {
          $s2_no_zero += ($this_time - $mean_successful_time_no_zero)*($this_time - $mean_successful_time_no_zero);
        }
      }
    }

    foreach ($overall_test_post_failure[$this_quiz] as $this_entry => $this_time) {
      if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
        //do not take 'no practice time' into account
      }
      else {
        $f2 += ($this_time - $mean_failure_time)*($this_time - $mean_failure_time);
        if ($this_time == 0) {

        }
        else {
          $f2_no_zero += ($this_time - $mean_failure_time_no_zero)*($this_time - $mean_failure_time_no_zero);
        }
      }
    }

    $standard_deviation_success = $successful_time_count > 2 ? sqrt($s2/($successful_time_count - 1)) : 0;
    $standard_deviation_failure = $failure_time_count > 2 ? sqrt($f2/($failure_time_count - 1)) : 0;

    $standard_deviation_success_no_zero = $successful_time_count_no_zero > 2 ? sqrt($s2_no_zero/($successful_time_count_no_zero - 1)) : 0;
    $standard_deviation_failure_no_zero = $failure_time_count_no_zero > 2 ? sqrt($f2_no_zero/($failure_time_count_no_zero - 1)) : 0;

    //95% confidence interval:
    $s_mtime_plus_stddev = $mean_successful_time + 1.96*$standard_deviation_success/sqrt($successful_time_count);
    $f_mtime_plus_stddev = $mean_failure_time + 1.96*$standard_deviation_failure/sqrt($failure_time_count);

    $s_mtime_plus_stddev_no_zero = $mean_successful_time_no_zero + 1.96*$standard_deviation_success_no_zero/sqrt($successful_time_count_no_zero);
    $f_mtime_plus_stddev_no_zero - $mean_failure_time_no_zero + 1.96*$standard_deviation_failure_no_zero/sqrt($failure_time_count_no_zero);

    //$s_mtime_plus_stddev = $mean_successful_time + $standard_deviation_success;
    //$f_mtime_plus_stddev = $mean_failure_time + $standard_deviation_failure;


    if (isset($_REQUEST['show_data'])) {
      print "success stddev = $standard_deviation_success and mean is $mean_successful_time";
      print "failure stddev = $standard_devation_failure and mean is $mean_failure_time";
      print "success stddev (no zeros) = $standard_deviation_success_no_zero and mean is $mean_successful_time_no_zero";
      print "failure stddev (no zeros) = $standard_deviation_failure_no_zero and mean is $mean_failure_time_no_zero";
    }

    print "<TD>";

    $height = "14px";
    $halfHeight = "7px";

    //print out the time and error bars on the times:
    //put in the full rectangle border:
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";

    //put in the time bar on the successful tries:
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:12px; background-color:#0f0; border-right:1px #FFF solid; ' title='95% Confidence (time): " . formatTime(count($conceptsOnQuiz[$this_quiz])*1.96*$standard_deviation_success/sqrt($successful_time_count)) . "'>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:10px; background-color:#FFF; border-bottom:1px #FFF solid; '>";

    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$mean_successful_time/3600) . "px; height:$height; background-color:#0f0; border-right:1px #FFF solid; ' title='Avg Success Time: " . formatTime(count($conceptsOnQuiz[$this_quiz])*$mean_successful_time) . "'>";


    //print out the time bar on the unsuccessful tries:
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$s_mtime_plus_stddev/3600) . "px; height:$halfHeight; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:5px; background-color:#f00; border-right:1px #FFF solid; 'title='95% Confidence (time): " . formatTime(count($conceptsOnQuiz[$this_quiz])*1.96*$standard_deviation_failure/sqrt($failure_time_count)) . "'>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$f_mtime_plus_stddev/3600) . "px; height:3px; background-color:#FFF; border-bottom:1px #FFF solid; '>";
    print "<div style='width:" . round(10*count($conceptsOnQuiz[$this_quiz])*$mean_failure_time/3600) . "px; height:$halfHeight; background-color:#f00; border-right:1px #FFF solid; ' title='Avg Failure time: " . formatTime(count($conceptsOnQuiz[$this_quiz])*$mean_failure_time) . "'>";



    print "</div></div></div></div>";
    print "</div></div></div>";

    print "</div>"; 
    print "</TD>";


    print "<TD></TD>";
    print "</TR>";

  }


  print "<TR><TD COLSPAN=4><H2>Concept Performance</H2></TD></TR>\n";
  //print "<TABLE>";
  $prev_quiz = "";
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
    $concept_bank_no_group = preg_replace("/group\:/", "", $this_c);
    print "<TR><TD>" . $concept_bank_no_group . ": <font size='-2'>" . $conceptBank_description[$concept_bank_no_group] . "</font></TD>";
    print "<TD>";
    print "<div style='width:200px; height:$height; background-color:#FFF; border:1px #CCC solid; '>";
    $post = $postDenConcept[$this_c] > 0 ? round(100*$postNumConcept[$this_c]/$postDenConcept[$this_c]) : 0;
    $pres = $postDenConcept[$this_c] > 0 ? round(100*$preNumConcept[$this_c]/$postDenConcept[$this_c]) : 0;

// print "pre is |$pres|; post is |$post|\n";
    if ($this_quiz == "pca") {
      print "<div style='width:" . 2*$post . "px; height:" . $height/2 . "; background-color:#123; border-right:1px #FFF solid; ' title='post: " . $post . "% (" . $postNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")'>";
      print "</div>";
      print "<div style='width:" . 2*$pres . "px; height:" . $height/2 . "; background-color:#456; border-right:1px #FFF solid; ' title='pre: " . $pres . "% (" . $preNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")' >";
      print "</div>";
    }
    else {
      print "<div style='width:" . 2*$post . "px; height:$height; background-color:#123; border-right:1px #FFF solid; ' title='post: " . $post . "% (" . $postNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")'>";
      print "<div style='width:" . 2*$pres . "px; height:$height; background-color:#456; border-right:1px #FFF solid; ' title='pre: " . $pres . "% (" . $preNumConcept[$this_c] . "/" . $postDenConcept[$this_c] . ")' >";
      print "</div></div>";
    }
    print "</div>";
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
    foreach ($concept_test_post_success[$this_c] as $course => $tmp_array) {
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
    foreach ($concept_test_post_failure[$this_c] as $course => $tmp_array) {
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
    foreach ($concept_test_post_success[$this_c] as $course => $tmp_array) {
      foreach ($tmp_array as $this_user => $this_time) {
        if (isset($_REQUEST['no_zero_times']) && ($this_time == 0)) {
          //do not take 'no practice time' into account
        }
        else {
          $s2 += ($this_time - $mean_successful_time)*($this_time - $mean_successful_time);
        }
      }
    }
    foreach ($concept_test_post_failure[$this_c] as $course => $tmp_array) {
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
      $too_variable = "";
      $min_score = 1;
      $max_score = 0;
      $order_of_problemNum = array();
      foreach ($problemNum as $file => $success) {
        $query = 'SELECT problem_id FROM `SessionExperiment_problem` WHERE set_id="' . preg_replace("/group\:/", "", $this_c) . '" AND source_file="' . $file . '"';
        $result = mysql_query($query, $con);
        $problem_id = -1;
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $problem_id = $row['problem_id'];
        }
        $order_of_problemNum["$file"] = $problem_id;
        if ($problem_id > -1) {
          $max_score = ($success/$problemDen[$file] > $max_score) ? $success/$problemDen["$file"] : $max_score;
          $min_score = ($success/$problemDen[$file] < $min_score) ? $success/$problemDen["$file"] : $min_score;
        }
      }

      $too_variable = ($max_score - $min_score > .33) ? "<font size='-2' color='purple'>(" . round($max_score - $min_score, 2) . " >.33)</font>" : "";
      $line2 .= "<TD><div style='width:50px; height:$halfHeight; background-color:#FFF;'>$too_variable</div></TD>";
      asort($order_of_problemNum);


      foreach ($order_of_problemNum as $file => $problem_id) {
        $success = $problemNum[$file] + 0;
        if ($problem_id > -1) {
          $this_p = $problemDen[$file] > 0 ? round(30*$success/$problemDen[$file]) : 0;
          $link_start = "<a href='http://" . $_SERVER['SERVER_NAME'] . "/webwork2/SessionExperiment/" . preg_replace("/group\:/", "", $this_c) . "/" . $problem_id . "/?user=s&effectiveUser=s' target='new'>";
          $link_end = "</a>";

          $line2 .= "<TD>";
          $line2 .= "<div style='width:30px; height:$halfHeight; background-color:#FFF; border:1px #CCC solid; ' title='Correct: $success/$problemDen[$file]'>";
          $line2 .= "$link_start<div style='width:" . $this_p . "px; height:$halfHeight; background-color:#0f0; border-right:1px #FFF solid; '>";
          $line2 .= "</div>" . $link_end . "</div>";
          $line2 .= "</TD>";
        }
      }
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
  for ($c = 0; $c < count($courses); $c++) {
    print "<input name='courses[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
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
