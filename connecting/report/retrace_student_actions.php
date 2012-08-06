<?

include("../access.php");
include("common.php");

function get_appropriate_time($last_time, $xml_draw_data) {
  $parts = explode(';~;', $xml_draw_data);
//print "<P>CALLED get_appropriate_time($last_time, draw_data)<BR>";
  $first_time = -1;
  foreach ($parts as $part) {
//print "<P>first_time is $first_time<BR>";
    if ($first_time < 0) {
      $details = explode(';#;', $part);
//print "details[0] = " . $details[0] . ", [1] = " . $details[1] . ", [2] = _" . $details[2] . "_<BR>";
      if (count($details) > 2) {
//print "A";
        if ($details[2] == 'NaN') {
//print "we got NaN<BR>";
        }
        else if ($details[2] >= 0) {
//print "B:  Setting first_time($first_time) to $details[2]";
          $first_time = $details[2];
        }
        else {
//print "C: We're doing nothing:  $details[2]<BR>";
        }
      }
    }
  }
  if ($first_time < 0) { $first_time = $last_time; }
//print "Returning first_time = $first_time<P>";
  return $first_time;
}



function get_last_time($prev_last_time, $xml_draw_data) {
  $parts = explode(';~;', $xml_draw_data);
  $last_time = -1;
  $parts = array_reverse($parts);
  foreach ($parts as $part) {
    if ($last_time < 0) {
      $details = explode(';#;', $part);
      if (count($details) > 2) {
        if ($details[0] == 'draw') {
          $draw_pixels = explode(',', $details[9]);
          foreach ($draw_pixels as $dp) {
            $txy = explode('_', $dp);
            if ($txy[0] == 'NaN') { }
            else if ($txy[0] > 0) { $last_time = $txy[0]; }
            else { }
          }
        }
        else {
          if ($details[2] == 'NaN') { }
           else if ($details[2] >= 0) { $last_time = $details[2]; }
           else { }
        }
      }
    }
  }
  if ($last_time < 0) { $last_time = $prev_last_time; }
  return $last_time;
}


function pod($t,$m) {
  return '<' . $t . '>' . $m . '</' . $t . '>' . "\n";
}


//Overall Plan:
//
//First, get the quiz problems and session problem_id for the quiz problems.
//
//Second, for each quiz problem, get the xml_data from ../replaySessionProblem.php session $session_id awangberg.
//
//Third, get the timestamps on each of the files.
//



//if (isset($_REQUEST['session_id'])) {
//  
//}
//else if (isset($_SERVER['argv'][1])) {
//
//}

if (0) {
  $quiz_sets_available = array();
  $quiz_sets_available[0] = 'quiz_wk_1';
  $quiz_sets_available[1] = 'quiz_wk_3';
  $quiz_sets_available[2] = 'quiz_wk_5';
  $quiz_sets_available[3] = 'quiz_wk_7';
  $quiz_sets_available[4] = 'quiz_wk_10';
  $quiz_sets_available[5] = 'quiz_wk_11';
  $quiz_sets_available[6] = 'quiz_wk_13';
}

if (0) {
  $problems_for_quiz = array();
  $problems_for_quiz[0] = array(1, 2, 3, 4);
  $problems_for_quiz[1] = array(1, 2, 4);
  $problems_for_quiz[2] = array(1,2,3,4);
  $problems_for_quiz[3] = array(1,2,6,8,7);
  $problems_for_quiz[4] = array(1,2,3,4,5);
  $problems_for_quiz[5] = array(1,2,3,4,5);
  $problems_for_quiz[6] = array(1,2,4,6,8);
}

header('content-type: text/xml');

$extra_path = '';
$tmp_extra_path = 0;

if (isset($_REQUEST['session_id'])) {
  $course = $_REQUEST['course'];
  if (isset($_REQUEST['student_id'])) {
    $this_student_id = $_REQUEST['student_id'];
    $selected_quiz = $_REQUEST['quiz'];
    //we hand back all interactions for the quiz, and let
    //the client decide which quiz problem information to use.
  }
  if (isset($_REQUEST['session_id'])) {
    $this_session_id = $_REQUEST['session_id'];
  }
  $extra_path = "";
}
else if (isset($_SERVER['argv'][1])) {
  $course = $_SERVER['argv'][1];
  $this_student_id = $_SERVER['argv'][2];
  $selected_quiz = $_SERVER['argv'][3];
  $session_id = $_SERVER['argv'][4];
  $tmp_extra_path = $_SERVER['argv'][5];
  if ($tmp_extra_path == 1) $extra_path = "../";
  if ($tmp_extra_path == 2) $extra_path = "../../";
}

$print_out_time_testing = 0;
$print_out_debugging = 1;

//$course = "Math160_F2010_awangberg";
////$this_student_name = "gvelez09";
//$this_student_id = 435;
//$selected_quiz = "quiz_wk_5";
//$selected_quiz_index = 2;

//$list_of_quiz_problems = $problems_for_quiz[$selected_quiz_index];
//print_r($list_of_quiz_problems);


//get the problem_ids for that student on the problems that were on that quiz

//$all_user_data = array();
$problem_to_session_ids = array();

$start = getTime();

//connect to the session database to get the problem_id for each question for the student.
$con = mysql_connect($db_host, $db_user, $db_pass);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'session';
$result = mysql_select_db("$db", $con);

//foreach ($list_of_quiz_problems as $k => $v) {

//get the user_name for this student id from the session database:
if ($this_student_id > 0) {
  $query = 'SELECT user_name FROM `user` WHERE user_id=' . $this_student_id;
  $result = mysql_query($query, $con);
  $this_student_name = '';
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_student_name = $row['user_name'];
  }
}
else {
  echo '<ReplayPart><wwProblem></wwProblem></ReplayPart>';
  exit;
}

$query = 'SELECT problem_id, ww_problem_number FROM `wwStudentWorkForProblem` LEFT JOIN `course` ON course.course_id = wwStudentWorkForProblem.course_id LEFT JOIN `user` ON user.user_id = wwStudentWorkForProblem.user_id WHERE course_name="' . $course . '" AND ww_set_id="' . $selected_quiz . '" AND user_name="' . $this_student_name . '"';

$result = mysql_query($query, $con);

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $tpi = $row['problem_id'];
  $twwpn = $row['ww_problem_number'];
  $problem_to_session_ids[$twwpn]['session_problem_ids'][$tpi] = $tpi;
}

mysql_close($con);

$end = getTime();

$debug_info = '';

$time_testing_xml = '';

$time_testing_xml .= '<test_timing>mysql_call to get problem ids timed at ' . round($end - $start, 4) . '</test_timing>
' . "\n";

//now we have the session problem ids.

//Second, for each quiz problem, get the xml_data from ../replaySessionProblem.php session $session_id awangberg.

$end_time = array();

$i = 0;

$this_ww_problem = array();

$start = getTime();

foreach ($problem_to_session_ids as $this_ww_problem_number => $data) {
  foreach ($data['session_problem_ids'] as $this_session_id => $v) {
    //print "$this_ww_problem_number => $this_session_id <BR>";

$start1 = getTime();
//error_log("in retrace_student_actions.php: php $extra_path../replaySessionProblem.php session $this_session_id awangberg");
    $tmp_e_p = $tmp_extra_path + 1;
    $xml_string = `php $extra_path../replaySessionProblem.php session $this_session_id awangberg $tmp_e_p`;

    $xml = simplexml_load_string($xml_string);
//error_log("returned from replaySessionProblem.php to retrace_student_actions.php");
$end1 = getTime();
$time_testing_xml .= '<test_timing_xml>time for replaySessionProblem.php ' . round($end1 - $start1, 4) . '</test_timing_xml>' . "\n";
    $last_time = -1;
$start2 = getTime();
    foreach ($xml->ReplayPart as $replayPart) {
      $draw_id = $replayPart->draw_id;
      $draw_end = $replayPart->Draw_end;
$start3 = getTime();
      $first_time_number = get_appropriate_time($last_time, $replayPart->Draw);
$end3 = getTime();
$start4 = getTime();
      $last_time = get_last_time($last_time, $replayPart->Draw);
$end4 = getTime();

$start5 = getTime();
      //DON'T DO THIS:  IT IS VERY SLOW!
      //$cmd = 'ls -al --full-time /opt/session/drawings/draw' . $draw_id . '_' . $draw_end;
      ////print "cmd: $cmd<P>";
      //$file_info = `$cmd`;
      //$file_timestamp = explode(' ', $file_info);
      //$file_time = explode('.', $file_timestamp[6]);
      //$this_non_strtotime = $file_timestamp[5] . ' ' . $file_time[0];

      //DO THIS.  It is very fast.
      $this_non_strtotime = date("Y-m-d H:i:s", filemtime('/opt/session/drawings/draw' . $draw_id . '_' . $draw_end));

$end5 = getTime();
$time_testing_xml .= '<test_timing_parts_iii_iv_v>time get_appropriate_time: ' . round($end3 - $start3, 4) . '. get_last_time: ' . round($end4 - $start4, 4) . '. ls -al --full-time: ' . round($end5 - $start4, 4) . '</test_timing_parts_iii_iv_v>' . "\n";

      $tmp_strtotime_this_non_strtotime = strtotime($this_non_strtotime);

      $this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]['date_time'] = $this_non_strtotime;
      $this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]['timestamp'] = $tmp_strtotime_this_non_strtotime;
      $this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]['first_time'] = $first_time_number;
      $this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]['last_time'] = $last_time;
      $this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]['work_length'] = $last_time - $first_time_number;


//      $end_time[$i]['end_time'][strtotime($this_non_strtotime)]['work_length'] = $last_time - $first_time_number;
      $end_time["$tmp_strtotime_this_non_strtotime"]['work_length'] = $last_time - $first_time_number;
      $end_time["$tmp_strtotime_this_non_strtotime"]['index_to_this_ww_problem_1'][] = $this_ww_problem_number;
      $end_time["$tmp_strtotime_this_non_strtotime"]['index_to_this_ww_problem_2'][] = "$draw_id";
      //print "Data for WW problem Number $this_ww_problem_number and session draw_id $draw_id<P>";
      //print_r($this_ww_problem["$this_ww_problem_number"]['this_draw_id_info']["$draw_id"]);
      $i++;

    }
$end2 = getTime();
$time_testing_xml .= '<test_timing_foreach>time for foreach loop of xml ' . round($end2 - $start2, 4) . '</test_timing_foreach>' . "\n";

  }
}
$end = getTime();
$time_testing_xml .= '<test_timing>getting last times of draw ids: ' . round($end - $start, 4) . '</test_timing>' . "\n";


//print "<P><BR><P>";
ksort($end_time);
$pre_end_time = 0;
////////$actual_start_time_for_end_time = array();

$last_date_time = '';
$last_timestamp = -1;
$last_first_time = -1;
$last_last_time = -1;
$last_work_length = -1;
$last_first_view_time = -1;
$last_draw_id = -1;

foreach ($end_time as $k => $v) {
  $debug_info .= pod('k', "loop through end_time: \$k = $k");
  if ($pre_end_time == 0) {
    //get when the user logged in.
    $the_date_part = date('D M j', $k);
    //print "WE GOT $the_date_part DONE";

    $log_in_file = '/opt/webwork/courses/' . $course . '/logs/login.log';
    $password_string = 'password accepted user_id=' . $this_student_name;
    $logged_in_data = `grep '$the_date_part' $log_in_file | grep '$password_string'`;

    //$logged_in_data = `grep '$the_date_part' /opt/webwork/courses/Math160_F2010_awangberg/logs/login.log | grep 'password accepted user_id="$this_student_name"'`;

    //print "<B>$logged_in_data </B>";
    $log_ins = explode("\n", $logged_in_data);
    //print_r($log_ins);


//print "<P>";

    foreach ($log_ins as $ind => $line) {
      $s = substr($line, 1, strpos($line, ']') - 1);
      //print "WE GOT login time $s, which is " . strtotime($s) . ".  Comparing it to pre_end_time $pre_end_time and end_time $k<BR>";
      if ((strtotime($s) <= $k) && (strtotime($s) > $pre_end_time)) {
        $pre_end_time = strtotime($s);
$debug_info .= pod('pre_end_time', "A: $pre_end_time");
        //print "UPDATED pre_end_time to " . strtotime($s) . "<BR>";
      }
    }

    //get when the quiz assignment opened.
    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }
    $db = 'webwork';
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

$debug_info .= pod('pre_end_time', $pre_end_time);
$debug_info .= pod('query', $query);

    $query = 'SELECT open_date FROM `' . $course . '_set` WHERE set_id="' . $selected_quiz . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $open_time = $row['open_date'];
$debug_info .= pod('open_time', $open_time);

      if (($open_time <= $k) && ($open_time > $pre_end_time)) {
        $pre_end_time = $open_time;
$debug_info .= pod('pre_end_time', "B: $pre_end_time");
        //print "The set didn't open until $open_time, so now \$pre_end_time = " . $pre_end_time . "<P>";
      }
    }
    //use the later of the two timestamps for $pre_end_time.

  }
  //print "[$k] => ['work_length'] => " . ($v['work_length'] / 1000);
  //print " ok? ";
  //print (($k - $pre_end_time) > ($v['work_length']) / 1000) ? "." : "NO!";
  //print "( $k - $pre_end_time = " . ($k - $pre_end_time) . ")<P>";
///////  $actual_start_time_for_end_time[$k] = $pre_end_time;

  //print_r($v);
  $count_v_index_to_this_ww_problem_1 = count($v['index_to_this_ww_problem_1']);
  $preserve_last_end_time = false;
  for ($i = 0; $i < $count_v_index_to_this_ww_problem_1; $i++) {
    $ww_p1 = $v['index_to_this_ww_problem_1'][$i];
    $ww_p2 = $v['index_to_this_ww_problem_2'][$i];
    $debug_info .= pod('i_of_these', "" . ($i + 1) . " of $count_v_index_to_this_ww_problem_1");
    $debug_info .= pod('k_p1_p2_fvt_tvt', "k = $k, ww_p1 = $ww_p1, ww_p2 = $ww_p2, fvt = $pre_end_time, tvt = 1000*(\$k - \$fvt) = " . (1000*($k  - $pre_end_time)) . ".");
    //As we go through to calculate the first_view_time for this work and the total_view_time,
    //decide if this was a 'double submit' or 'double click' record.
    //  if it was, then the submit time may be a bit different
    //  but the recorded first_time, last_time, and work_length will be the same as the previous record.
    //  in this case, use the first_view_time from the previous time through this loop of data.
    //  and, since we're using the previous data, hold it over for another iteration (preserve_last_end_time = true)
    //  
    //  if it wasn't, meaning it is really a new record, then
    //  process the first_view_time and the total_view_time using the values calculated for this time
    //  through the loop of data.
    if ((abs($this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['first_time'] - $last_first_time) < 1) && 
        (abs($this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['last_time']  - $last_last_time) < 1) && 
        (abs($this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['work_length'] - $last_work_length) < 1 )) {
      $debug_info .= pod('DO_NOT_UPDATE', "fvt = $last_first_view_time, tvt = " . (1000*($k - $last_first_view_time))); 
      $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['first_view_time'] = $last_first_view_time;
      $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['total_view_time'] = 1000*($k - $last_first_view_time);
      $preserve_last_end_time = true;
    }
    else {
      $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['first_view_time'] = $pre_end_time;
      $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['total_view_time'] = 1000*($k - $pre_end_time);
    }
  }
  $debug_info .= pod('END_OF_k_LOOP', "$k");
  $last_date_time = $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['date_time'];
  $last_timestamp = $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['timestamp'];
  $last_first_time = $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['first_time'];
  $last_last_time = $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['last_time'];
  $last_work_length = $this_ww_problem[$ww_p1]['this_draw_id_info'][$ww_p2]['work_length'];
  $last_first_view_time = $preserve_last_end_time ? $last_first_view_time : $pre_end_time;
  $debug_info .= pod('LAST_DATA', "dt = $last_date_time, t = $last_timestamp, ft = $last_first_time, lt = $last_last_time, wl = $last_work_length, fvt = $last_first_view_time");
  $debug_info .= pod('END_OF_k_LOOP', "$k");
  //$last_draw_id = -1;

  $pre_end_time = $k;
}

//print_r($this_ww_problem);



$ret_xml = '';

foreach ($this_ww_problem as $this_ww_problem_id => $ww_v) {
  $ret_xml .= '<wwProblem>'
            . '<wwProblem_number>' . $this_ww_problem_id . '</wwProblem_number>' . "\n"
            . '<Replay>';
  //print "ww_problem: $this_ww_problem_id.  Data: <P>";

  foreach ($ww_v['this_draw_id_info'] as $draw_id => $data) {
    $ret_xml .= '<ReplayPart>';
    $ret_xml .= '<draw_id>' . $draw_id . '</draw_id>';

    //print " . . . draw_id: $draw_id => <BR>";
    $warn = 1;
    $ret_xml .= '<date_time>' . $data['date_time'] . '</date_time>'
              . '<timestamp>' . $data['timestamp'] . '</timestamp>'
              . '<first_time>' . $data['first_time'] . '</first_time>'
              . '<last_time>' . $data['last_time'] . '</last_time>'
              . '<work_length>' . $data['work_length'] . '</work_length>'
              . '<first_view_time>' . $data['first_view_time'] . '</first_view_time>'
              . '<total_view_time>' . $data['total_view_time'] . '</total_view_time>'
              . '</ReplayPart>';

//    foreach ($data as $k => $v) {
//     //print ".......  $k => $v <BR>";
//      if ($k == "first_view_time") { $warn = 0; }
//    }
//    if ($warn == 1) { print "CHECK THIS ONE<P>"; }
  }
  if ($print_out_time_testing) {
    $ret_xml .= '<TIME_TESTING>' . "$time_testing_xml" . '</TIME_TESTING>';
  }
  if ($print_out_debugging) {
    $ret_xml .= '<DEBUG_INFO>' . $debug_info . '</DEBUG_INFO>';
  }
  $ret_xml .= '</Replay></wwProblem>';
  //print_r($v);
  //print "<P>";
}

echo '<ReplayQuiz>' . $ret_xml . '</ReplayQuiz>';
?>
