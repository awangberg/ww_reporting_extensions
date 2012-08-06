<?php


include("../access.php");
include("common.php");



print "<html>";
print "<head><title></title>\n";
print "<style type='text/css'>\n";
print "<!--\n";
print "#container{\n";
print "  width:485px;\n";
print "  height:800px;\n";
print "  border:1px solid #088;\n";
print "  overflow:hidden;\n";
print "  margin:auto;\n";
print "}\n";
print "#container iframe {\n";
print "  width:1200px;\n";
print "  height: 1000px;\n";
print "  margin-left:-1px;\n";
print "  margin-top:-225px;\n";
print "  border: 0 solid;\n";
print "}\n";
print "-->\n";
print "</style>\n";
print "</head>\n";
print "<body>";
//print http_build_query(my_array_merge(my_array_merge($_GET, $_REQUEST), $_POST));




//$student_id = $_REQUEST['student_id'];
////$course = $_REQUEST['course_id'];
//$quiz_set = $_REQUEST['quiz_set'];
//$problem_number = $_REQUEST['problem_number'];
$theKey = "showInformation";
$this_course = "Math160_F2010_awangberg";

//$weekly_quiz_sets = $_REQUEST['do_these_quiz_sets'];
$courses = get_courses_make_con($db_host, $db_user, $db_pass);

if (isset($_REQUEST['courses']) && isset($_REQUEST['do_these_quiz_sets'])) {

  $student_id = $_REQUEST['student_id'];
  $quiz_set = $_REQUEST['quiz_set'];
  $problem_number = $_REQUEST['problem_number'];
  $weekly_quiz_sets = $_REQUEST['do_these_quiz_sets'];

  $do_these_courses = $_REQUEST['courses'];

  for ($c = 0; $c < count($do_these_courses); $c++) {
    $course = $do_these_courses[$c];

    //get the users selected, and the selected quizzes:
    $process_these_students = $_REQUEST['studentID'];

    //first, get the problem ID for the Session data for each user and each quiz problem:

    //Determine the session_problem_id nubmer for this quiz_set, problem_number, and student_id:
    $con = mysql_connect($db_host, $db_user, $db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = "session";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    $all_user_data = array();

    for ($p = 0; $p < count($process_these_students); $p++) {
      for ($q = 0; $q < count($weekly_quiz_sets); $q++) {
        $query = 'SELECT problem_id, user.user_id, user_name, ww_problem_number, ww_set_id FROM `wwStudentWorkForProblem` LEFT JOIN `course` ON course.course_id = wwStudentWorkForProblem.course_id LEFT JOIN `user` ON user.user_id = wwStudentWorkForProblem.user_id WHERE course_name="' . $course . '" AND ww_set_id="' . $weekly_quiz_sets[$q] . '" AND user_name="' . $process_these_students[$p] . '"';

        $result = mysql_query($query, $con);
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $this_problem_id = $row['problem_id'];
          $this_user_id = $row['user_id'];
          $this_user_name = $row['user_name'];
          $this_ww_problem_number = $row['ww_problem_number'];
          $this_ww_set_id = $row['ww_set_id'];

          $all_user_data['course'][$course]['user'][$this_user_name]['quiz'][$this_ww_set_id]['problem'][$this_ww_problem_number]['session_problem_ids'][$this_problem_id] = $this_problem_id;
        }
      }
    }
    //now, work with the webwork database.  Close the previous connection, and re-open it to webwork:
    mysql_close($con);

    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . $mysql_error());
    }

    $db = "webwork";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    //$all_user_data = array();

    //get all the users for this course:
    for ($p = 0; $p < count($process_these_students); $p++) {
      $query = 'SELECT user_id FROM `' . $course . '_user` WHERE user_id="' . $process_these_students[$p] . '"';
      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $all_user_data['course'][$course]['user'][$this_user]['exists'] = 1;
      }
    }

    //get all the quiz scores for the users in the course:
    for ($p = 0; $p < count($process_these_students); $p++) {
      for ($q = 0; $q < count($weekly_quiz_sets); $q++) {
        $this_quiz = $weekly_quiz_sets[$q];
        $query = 'SELECT status, attempted, user_id, problem_id, problem_seed, num_correct, num_incorrect FROM `' . $course . '_problem_user` WHERE set_id="' . $this_quiz . '" AND user_id="' . $process_these_students[$p] . '"';
        $result = mysql_query($query, $con);

        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $this_user = $row['user_id'];
	  $this_attempt = $row['attempted'];
	  $this_score = $row['status'];
	  $this_problem_id = $row['problem_id'];
	  $this_problem_seed = $row['problem_seed'];
	  $this_num_correct = $row['num_correct'];
	  $this_num_incorrect = $row['num_incorrect'];
	  $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['attempted'] = $this_attempt;
	  $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['final_score'] = $this_score;
	  $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['problem_seed'] = $this_problem_seed;
	  $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['num_correct'] = $this_num_correct;
	  $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['num_incorrect'] = $this_num_incorrect;
        }
      }
    }
  }
  mysql_close($con);


  //$height = "601px";
  //$width = "761px";
  //$height = "650px";
  $height = "400px";
  $width = "506.5px";
  $width = "450px";

$page = 0;

  foreach ($all_user_data['course'] as $this_course => $this_course_data) {
    foreach ($this_course_data['user'] as $this_user => $this_users_data) {
      foreach ($this_users_data['quiz'] as $this_quiz => $this_problem_data) {
        foreach ($this_problem_data['problem'] as $this_problem => $this_problem_performance) {
	  //Do we show this problem?
	  $show_this_problem = 0;
	  if ($this_problem_performance['final_score'] == 1) { $show_this_problem = 0; }
	  if (isset($_REQUEST['problem_id']) && ($_REQUEST['problem_id'] == $this_problem)) { $show_this_problem = 1; }
	  if (!isset($_REQUEST['problem_id']) && ($this_problem_performance['final_score'] < 1)) { $show_this_problem = 1; }

//        if ($this_problem_performance['final_score'] == 1) {  }
 
//          else {
          if ($show_this_problem == 0) { }
          else {
          $max_session_id_number = -1;
          foreach ($this_problem_performance['session_problem_ids'] as $session_id_number) {
            $max_session_id_number = $max_session_id_number > $session_id_number ? $max_session_id_number : $session_id_number;
          }
          $session_problem_id = $max_session_id_number;

	  $line_to_get = "|" . $this_user . "|" . $this_quiz . "|" . $this_problem . "|";
	  $log_file = "/opt/webwork/courses/" . $this_course . "/logs/answer_log";
	  $answer_data = `grep '$line_to_get' $log_file`;
	  $answer_data_entries = explode("\n", $answer_data);
	  $submission_string = "";
	  $answer_string = "";
	  array_pop($answer_data_entries);

	  $answer_string_after_submission = array();
	  $answer_string_header = "";
	  $answer_string_tail = "";

	  for ($i = 0; $i < count($answer_data_entries); $i++) {
 	    $this_data_entry = explode("\t", $answer_data_entries[$i]);
	    $tmp_info = explode("|", $this_data_entry[0]);
	    $this_date = $tmp_info[0];
	    $this_score = array_pop($tmp_info);
	    $this_score_array = str_split($this_score);
	    //$this_score = array_pop($explode("|", $this_date_entry[1]));
	    array_shift($this_data_entry);
	    array_shift($this_data_entry);
	    array_pop($this_data_entry);
	    $this_answers = "Answer: " . implode("\nAnswer: ", $this_data_entry);
	    if ($i == 0) {
	      $answer_string_header = "<TABLE><TR><TH>Submission</TH>";
	      for ($j = 0; $j < count($this_score_array); $j++) {
	        $answer_string_header .= "<TH>Q" . ($j+1) . "</TH>";
	      }
	      $answer_string_header .= "</TR>";
	    }
	    if (($this_score_array[0] == 1) || ($this_score_array[0] == 0)) {
	      $answer_string_after_submission[$i] = "<TR><TD>" . ($i + 1) . "</TD>";
	      for ($j = 0; $j < count($this_score_array); $j++) {
	        $answer_string_after_submission[$i] .= "<TD>" . $this_data_entry[$j] . " ";
	        $answer_string_after_submission[$i] .= $this_score_array[$j] == 1 ? "(Correct)" : "(Wrong)";
	        $answer_string_after_submission[$i] .= "</TD>";
	      }
	      $answer_string_after_submission[$i] .= "</TR>";
	    }
	    $this_submission_time = str_replace("[", "", $this_date);
	    $this_submission_time = str_replace("]", "", $this_submission_time);
	    $this_submission_time = str_replace(" ", "_", $this_submission_time);
	    $submission_string .= $this_score . "_" . $this_submission_time;
	  }
	  $answer_string_tail = "</TABLE>";


          $page++;
	  print "<TABLE BORDER=1><TR><TD VALIGN='TOP'>";

	  print "<H2>" . $this_user . " " . $this_quiz . " Problem: " . $this_problem . "</H2>";
	  print "<div id='container'>";
	  print "<iframe src='http://" . $_SERVER['SERVER_NAME'] . "/webwork2/Math160_F2010_awangberg/" . $this_quiz . "/" . $this_problem . "/?effectiveUser=" . $this_user . "&displayMode=images&key=" . $theKey . "&user=awangberg' scrolling='no'></iframe>";
	  print "</div>";

	  print "<H2>Score and Answers</H2>";
          if (count($answer_string_after_submission) >= 1) {
  	    print "<P>$answer_string_header";
	    for ($j = 0; $j < count($answer_string_after_submission); $j++) {
	      print $answer_string_after_submission[$j];
	    }
	    print $answer_string_tail;
            print "</P>";
          }
          else {
            print "<P>No answers were submitted.</P>";
          }

	  print "</TD>\n<TD VALIGN='TOP'>";


	  //submission_times receives a list with a 0 in front of it.
	  $submission_times = explode(" ", `php get_submission_times.php $session_problem_id $submission_string`);

	  //$submission_times = array();
	  //array_push($submission_times, 0);
	  //array_push($submission_times, 31754);
	  //array_push($submission_times, 65457);
	  //array_push($submission_times, 90555);

	  $submissions = count($submission_times);

	  for ($jj = 1; $jj < count($submission_times); $jj++) {
	    $ii = $jj - 1;
	    print "<TABLE BORDER=1><TR><TD><H2>Work Part " . $jj . "</H2></TD>";
            if (count($answer_string_after_submission) >= $jj) {
              print "<TD>";
	      print $answer_string_header;
	      print $answer_string_after_submission[$ii];
	      print $answer_string_tail;
	      print "</TD>";
            }
            print "</TR></TABLE>";
	    print "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width=\"$width\" height=\"$height\" id=\"NOTSESSION\" align=\"middle\">\n";
	    print "	<param name=\"allowScriptAccess\" value=\"sameDomain\" />\n";
	    print "	<param name=\"allowFullScreen\" value=\"false\" />\n";
	    print "	<param name=\"wmode\" value=\"transparent\" />\n";
	    print "	<param name=\"movie\" value=\"http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&problem_id=" . $session_problem_id . "&userDatabaseName=session&replayModeAsInstructor=1&overRidePaceForAdmin=300&startTime=" . $submission_times[$ii] . "&stopTime=" . ($submission_times[$jj] - 1) . "\" />\n";
	    print "     <param name=\"quality\" value=\"high\" /><param name=\"bgcolor\" value=\"#ffffff\" />\n";
	    print "	<embed src=\"http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&problem_id=" . $session_problem_id . "&userDatabaseName=session&replayModeAsInstructor=1&overRidePaceForAdmin=300&startTime=" . $submission_times[$ii] . "&stopTime=" . ($submission_times[$jj] - 1) . "\" quality=\"high\" bgcolor=\"#ffffff\" wmode=\"transparent\" width=\"$width\" height=\"$height\" name=\"NOTSESSION\" align=\"middle\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"false\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />\n";
	    print "</object>\n";
	  }

	  print "</TD></TR></TABLE>";
	  if (isset($_REQUEST['no_html_for_students'])) { }
          else {
	    print "<p style='page-break-before: always'>Page $page.  Student: $this_user Quiz: $this_quiz Problem: $this_problem </p>";
	    print "<p style='font-size: 18px;'><B>New Work:</B>  Show the correct solution and answer the question below for 50% credit</p>";
	    print "<p style='font-size: 18px; line-height: 1200px;'><B>Analysis:</B>  Write one sentence (or more) what was wrong, and one sentence (or more) how you fixed it.  This is required for credit.</H3>";
	    print "<p style='page-break-after: always'>Page $page. Student: $this_user Quiz: $this_quiz Problem: $this_problem </p>";
	  } // end of the else corresponding to isset($_REQUEST['no_html_for_students']

          }//end of the else, to make sure we just print the incorrect answers.
        }
      }
    }
  }

  if (isset($_REQUEST['no_html_for_students'])) {}
  else {
    print "<H3>Online Quiz and WeBWorK Comments</H3>";
    print "<p style='font-size:18px;'><B>Did you find the quizzes helpful?  How could they be improved? (5 pts) </B></p>";
    print "<p style='font-size:18px; line-height: 1200px;'><B>What improvements would you make to WeBWorK? (5 pts) </B></p>";
  }
  //print "<PRE>";
  //print_r($_REQUEST);
  //print "</PRE>";
  print "</body>";
  print "</html>";
}

else if (isset($_REQUEST['courses'])) {

  $do_these_courses = $_REQUEST['courses'];

  for ($c = 0; $c < count($do_these_courses); $c++) {
    $course = $do_these_courses[$c];
  }

  print "Select the student";

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $db = "webwork";
  $result = mysql_select_db("$db", $con);

  //get the users from the courseWithStudents course
  $user_table = $course . "_user";
  $query = "SELECT user_id, first_name, last_name, section, recitation, status FROM `" . $user_table . "` WHERE 1";
  $result = mysql_query($query, $con);

  $resulta = "query: $query <BR> result: $result\n";

  $userList = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $userList .= "<TR><TD><input type='checkbox' name='studentID[]' id='studentID[]' value='" . $row['user_id'] . "'/></TD>";
    $userList .= "<TD>" . $row['user_id'] . "</TD><TD>" . $row['first_name'] . " " . $row['last_name'] . "</TD>";
    $userList .= "<TD>" . $row['section'] . "</TD><TD>" . $row['recitation'] . "</TD>";
    $userList .= "<TD>" . $row['status'] . "</TD></TR>\n";
  }
  mysql_close($con);

  print "<form method='post' action=''>";
  print "<BR>\n";
  print "<input name='courses[]' value='" . $course . "' type='hidden'>\n";
  print "<TABLE BORDER=1><TR><TD>Select</TD><TD>UserID</TD><TD>Name</TD><TD>Section</TD><TD>Recitation</TD><TD>Status</TD></TR>\n";
  print $userList;
  print "</TABLE>\n";


  print "<BR><H3>Select Quiz Set</H3><BR>";

  $quiz_sets_available = array();
  $quiz_sets_available[0] = "quiz_wk_1";
  $quiz_sets_available[1] = "quiz_wk_3";
  $quiz_sets_available[2] = "quiz_wk_5";
  $quiz_sets_available[3] = "quiz_wk_7";
  $quiz_sets_available[4] = "quiz_wk_10";
  $quiz_sets_available[5] = "quiz_wk_11";
  $quiz_sets_available[6] = "quiz_wk_13";

  for ($c = 0; $c < count($quiz_sets_available); $c++) {
    print "<input name='do_these_quiz_sets[]' type='checkbox' value='" . $quiz_sets_available[$c] . "'>" . $quiz_sets_available[$c] . "<BR>\n";
  }

  print "<input type='submit' name='Submit' value='Submit'><BR></form>\n";

}

else {


  print "<H3>Select the course</H3><BR>";

  //ask the user for the course and quizzes:

  print "<form method='post'>";
  for ($c = 0; $c < count($courses); $c++) {
    print "<input name='courses[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
  }
  print "<input name='print_user_name' type='text' id='print_user_name' value='print_user_name'>\n";

  print "<BR>\n";
  print "<H3>Options</H3><BR>";
  print "Only Incorrect Problems: <input name='only_incorrect_answers' type='text' id='only_incorrect_answers' value='y'><BR>\n";

  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";
}




?>

