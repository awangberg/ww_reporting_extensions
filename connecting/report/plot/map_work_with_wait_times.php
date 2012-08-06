<?php

include("../common.php");

ini_set("memory_limit","999000M");

function eliminate_duplicate_draw_ids($print_out_xml, $xml, $times_for_draw_ids) {
  $tmp = array();

  if ($print_out_xml) {
    print "<P><FONT COLOR='BLUE'>IN eliminate_duplicate_draw_ids</FONT><BR>";
    print "<P><FONT COLOR='BLUE'>we got initial times_for_draw_ids: <BR><PRE>";
    print_r($times_for_draw_ids);
    print "</PRE><P>";

    print "<BR> Go through each draw_id in the replay parts.  Make a tmp array that has array[timestamp]['ids'][draw_id] = draw_data<BR>";
  }
  foreach ($xml->ReplayPart as $replayPart) {
    $draw_id = $replayPart->draw_id;
    if ($print_out_xml) {
      print "We got draw_id: $draw_id<BR>";
      print_r($times_for_draw_ids["$draw_id"]);
    }
    $timestamp = array_key_exists("$draw_id", $times_for_draw_ids) ? $times_for_draw_ids["$draw_id"]['timestamp'] : "";
    if ($print_out_xml) {
      print "<BR>USING TIMESTAMP: $timestamp<BR>";
    }
    $tmp["$timestamp"]['ids']["$draw_id"] = strlen($replayPart->Draw);
    if ($print_out_xml) {
      print "<BR>now, tmp[$timestamp]['ids'][$draw_id] = " . $tmp["$timestamp"]['ids']["$draw_id"] . "<BR>";
    }
  }

  $valid_draw_ids = array();

  if ($print_out_xml) {
    print "<BR>The tmp array is <BR><PRE>";
    print_r($tmp);
    print "</PRE><BR>\n";
  }

  foreach ($tmp as $timestamp => $ids) {
if ($print_out_xml) {
  print "<P> Choosing the best draw_id for timestamp: $timestamp<BR>";
  print_r($ids);
  print "It is the one with the longest length.<BR>\n";
}

    $max_len = -1;
    $best_id = -1;
    foreach ($ids['ids'] as $draw_id => $len) {
      if ($max_len == -1) {
        $best_id = $draw_id;
        $max_len = $len;
      }
      else {
        if ($len > $max_len) {
          $best_id = $draw_id;
          $max_len = $len;
        }
      }
    }
    $valid_draw_ids["$best_id"] = $max_len;
if ($print_out_xml) {
  print "$best_id was chosen since its length was longest.<BR>";
}
  }
if ($print_out_xml) {
  print "<P>returning valid_draw_ids: ";

  print_r($valid_draw_ids);
  print "</FONT><P>";
}
  return $valid_draw_ids;
}


function get_appropriate_time($last_time, $xml_draw_data) {
  $parts = explode(";~;", $xml_draw_data);
//print "<P>CALLED get_appropriate_time($last_time, draw_data)<BR>";
  $first_time = -1;
  foreach ($parts as $part) {
//print "<P>first_time is $first_time<BR>";
    if ($first_time < 0) {
      $details = explode(";#;", $part);
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
  echo "Returning first_time = $first_time<P>";
  return $first_time;
}

function get_last_time($prev_last_time, $xml_draw_data) {
  $parts = explode(";~;", $xml_draw_data);
  $last_time = -1;
  $parts = array_reverse($parts);
  foreach ($parts as $part) {
    if ($last_time < 0) {
      $details = explode(";#;", $part);
      if (count($details) > 2) {
        if ($details[0] == 'draw') {
          $draw_pixels = explode(",", $details[9]);
          foreach ($draw_pixels as $dp) {
            $txy = explode("_", $dp);
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

$start = getTime();

if (isset($_REQUEST['session_id'])) {
  $session_id = $_REQUEST['session_id'];
  $attempt_list = $_REQUEST['attempts'];
  $map_width = $_REQUEST['map_width'];
  $pixels_per_second = $_REQUEST['pixels_per_second'];
  $this_key = $_REQUEST['key'];
  $start_time = $_REQUEST['startTime'];
  $student_id = $_REQUEST['student_id'];
  $course = $_REQUEST['course'];
  $quiz = $_REQUEST['quiz_name'];
}
else if (isset($_SERVER['argv'][1])) {
  $session_id = $_SERVER['argv'][1];
  $attempt_list = $_SERVER['argv'][2];
  $map_width = $_SERVER['argv'][3];
  $pixels_per_second = $_SERVER['argv'][4];
  $this_key = $_SERVER['argv'][5];
  $start_time = $_SERVER['argv'][6];
}

//$session_id = 1685;

//$attempt1 = "010";
//$attempt2 = "010";
//$attempt3 = "110";

$attempts = array();
$attempts = split("_", $attempt_list);

$tmp_start_time_array = split("_", $start_time);
array_shift($tmp_start_time_array);
$start_time = join(" ", $tmp_start_time_array);
//print "start_time is $start_time" . ". \n";

//$student_id = 435;
//$quiz = "quiz_wk_5";
//$quiz_problem = 2;
//$course = "Math160_F2010_awangberg";

$pixels_per_seconds = $pixels_per_second;
$pps = 1000/$pixels_per_seconds;
$uber_width = 100*$map_width;
$w = $uber_width - 1;
$oh = 14;
$uber_height = 450;
$h = $uber_height + $oh;
$max_height = 50 + $oh;
$max_width = 0.25*$map_width;

$show_times = 0;

$print_out_xml = 0;
$print_out_timings = 0;
$print_out_running_time = 0;

if (isset($_REQUEST['output']) && ($_REQUEST['output'] == "xml")) {
  $print_out_xml = 1;
  $print_out_timings = 0;
  $print_out_running_time = 1;
}

$extra_debug_info = 1;
$tmp_down = 0;

if ($print_out_xml) {   echo '<HTML>  <BODY>';  }
if ($print_out_xml) {   echo '<BR> Attempting to get <BR> ... session_id: ' . $session_id . '<BR> ... student_id: ' . $student_id . '<BR> ... course: ' . $course . '<BR> ... quiz: ' . $quiz . '<BR>'; }

$end = getTime();

if ($print_out_timings) { echo 'time to initialize variables: ' . round($end - $start, 4) . '<BR>'; }


$start = getTime();

if ($print_out_xml) {
  print "<a href='../../replaySessionProblem.php?userDatabaseName=session&problem_id=" . $session_id . "&user_id=awangberg' TARGET='NEW'>Link to xml:  ../../replaySessionProblem.php</a><BR>";
}

//error_log("in map_work_with_wait_times.php:  php ../../replaySessionProblem.php session $session_id awangberg");
$xml_string = `php ../../replaySessionProblem.php session $session_id awangberg 2`;

$xml = simplexml_load_string($xml_string);
//error_log("returned from replaySessionProblem.php to map_work_with_wait_times.php");

$end = getTime();

if ($print_out_timings) { echo 'time to get xml_string: ' . round($end - $start,4) . '<BR>'; }

$start = getTime();

if ($print_out_xml) {
  print "<a href='../retrace_student_actions.php?course=$course&student_id=$student_id&quiz=$quiz&session_id=$session_id' TARGET='NEW'>Link to t_xml: retrace_student_actions.php</a><BR>";
}

//error_log("in map_work_with_wait_times.php: php ../retrace_student_actions.php $course $student_id $quiz $session_id"); 
$time_string = `php ../retrace_student_actions.php $course $student_id $quiz $session_id 1`;


$t_xml = simplexml_load_string($time_string);
//error_log("returned from ../retrace_student_actions.php to map_work_with_wait_times.php");

$end = getTime();
if ($print_out_timings) { echo 'time to get time_string: ' . round($end - $start,4) . '<BR>'; }


if ($print_out_xml) {

  echo '<H2>xml</H2><P>
<PRE>';
echo str_replace("xml version", '', $xml->asXML());

echo '</PRE>
<P>
<H2>time_string</H2><P>
<PRE>
';
echo str_replace('xml version', 'blah', $t_xml->asXML());

echo '</PRE>';
}


//if (!$print_out_xml) {
if (1) {


  $start = getTime();
  $my_img = imagecreate($w, $h);
  $background_color       = imagecolorallocate( $my_img, 255, 255, 255);


  //COLORS CHOSEN FROM http://colorshemedesigner.com
  //RGB:  DC162D
  //Scheme ID: 5z61TsOsOFfFf
  //Adjust Scheme: More Contrast

  //RGB:  6D1699
  //Scheme ID:  4I61TsOsOK-K-
  //Adjust Scheme:  High Contrast

  //light pink  (2nd row upper right)
  $a = html2rgb('#EE68B1');
  $erase_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  //$erase_height = $oh + 5; // $oh + 15;

  //maroon red  (2nd row middle)
  $a = html2rgb('#BA136E');
  $erase_all_color        = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  $erase_all_height = $h; //$oh + 20;



  //purple (1st row middle)
  $a = html2rgb('#6D1699');
  $drawing_color          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  //$drawing_height = $oh + 5; //$oh + 10;

  //light purple (1st row upper left)
  $a = html2rgb('#BC6BE6');
  $drawing_color_2        = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  //$drawing_2_height = $oh + 5; //$oh + 10;

  //purple grey (1st row upper right)
  $a = html2rgb('#4E2D60');
  $typing_color           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  //$typing_height = $oh + 5; //$oh + 15;



  //lime green:  (tetrad, 3rd row, middle)
  $a = html2rgb('#9BED00'); //("#93D615");
  $graph_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  //$graph_height = $h; //$oh + 20;

  //evergreen:  (tetrad, 3rd row, lower left)
  $a = html2rgb('#436503');
  $image_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  $image_height = $h; //$oh + 20;


  //blue:  (accented analogic, 3rd row, middle)
  $a = html2rgb('#3F209E');
  $slider_color           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  $slider_height = $oh + 20;

  //yellow: (4th row, middle)
  //(#"E2E517")
  //yellow:  (triad, 3rd row, middle)
  $a = html2rgb('#E6C317'); //("#E2E517");
  $leaving_color          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
  $leaving_height = $h;

  //black
  $submission_color       = imagecolorallocate( $my_img, 0, 0, 0);

  //brick red (2nd row, lower left)
  $a = html2rgb('#580231');
  $error_submission_color = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  $submission_height = $oh + 10;  //$oh + 20;
  $final_submission_height = $h;

  $label_color = imagecolorallocate( $my_img, 0, 0, 255);
  $black = $submission_color;
  $white =  imagecolorallocate( $my_img, 255, 255, 255);

  $light_grey_a = imagecolorallocate( $my_img, 190, 190, 190);
  $light_grey_b = imagecolorallocate( $my_img, 160, 160, 160);

  imagesetthickness ($my_img, 1);


  //put the alternating light / dark grey border for minutes at the top:
  //put in the minute segments:
  $minute = 0;
  $alt_color = '';
  while ($minute*60*(1000/$pps) < $w) {
//  for ($minute = 1; $minute <= $total_time_minutes; $minute++) {
    $minute++;
    //print "minute is $minute<BR>";
    //print " line at $minute*60*(1000/$pps) = " . $minute*60*(1000/$pps) . "<BR>";
    //imageline(   $my_img, 2 + $minute*60*(1000/$pps), 1, 2 + $minute*60*(1000/$pps), $oh, $black);
    $minute_length = 15;
    if ($minute < 10) { $minute_length = $minute_length + 10; }
    if ($minute >= 10) { $minute_length = $minute_length + 20; }
    $alt_color = ($alt_color == $light_grey_a) ? $light_grey_b : $light_grey_a;
    $alt_color_b = ($alt_color == $light_grey_a) ? $light_grey_b : $light_grey_a;
    imagefilledrectangle($my_img, 2 + ($minute - 1)*60*1000/$pps, 1, 2 + $minute*60*1000/$pps, $h-2, $alt_color);
    imagefilledrectangle($my_img, 2 + ($minute - 1)*60*1000/$pps, $h-2, 2 + $minute*60*1000/$pps, $h-4, $alt_color);
    imageline($my_img, 2 + ($minute + 0.333 - 1)*60*1000/$pps, $oh+1, 2 + ($minute + 0.333 - 1)*60*1000/$pps, $h-2, $alt_color_b);
    imageline($my_img, 2 + ($minute + 0.667 - 1)*60*1000/$pps, $oh+1, 2 + ($minute + 0.667 - 1)*60*1000/$pps, $h-2, $alt_color_b);
    //imagestring( $my_img, 2, 2 + $minute*60*(1000/$pps)-$minute_length, 0, "$minute:00", $black);
  }


  //put a thin border around the image:
  imageline( $my_img, 1, 0, $w, 0, $black);
  imageline( $my_img, 1, $oh, $w, $oh, $black);
  imageline( $my_img, 1, 0, 1, $h, $black);
  imageline( $my_img, 1, $h-1, $w, $h-1, $black);
  imageline( $my_img, $w, 0,  $w, $h, $black);


  $end = getTime();

  if ($print_out_timings) { echo '<BR>time to initialize image: ' . round($end - $start, 4) . '<BR>'; }

  //imageline( $my_img, 20, 0, 20, 20, $line_color);

  //loop through each node of the Problem
  //$running_time is the accumulated time.  It starts from 0.
  //$dTime is the amount of time added to $running_time.
  //$dTime is calculated by taking ($this_time - $previous_time)
  //and adding it to $running_time.
  //
  //The tricky thing is that on each new replayPart, we need 
  //to set $previous_time to the new $this_time so that $dTime will be 0 for that new piece.

  $running_time = 0;
  $previous_time = -1;
  $dTime = -1;
  $this_time = -1;

  $webworkSubmissions = 0;
  $saw_webworkSubmissions_at_this_time = array();

  $saw_these_first_times_of_parts = array();

  //find the valid timestamps for webwork submissions:
  $use_file_for_submission = array();

  $attempt_number = array();
  $attempt_count = 0;

  $this_draw_id_info = array();
  $order_of_draw_ids = array();

  $last_time = -1;

  $add_seconds_at_start = -1;

  //get the actual time spent for each part that a student did into the array:  $true_time_spent_on_draw_id.
  //true_time_spent_on_draw_id[draw_id]['first_draw'] = how long until student actually started working for this part.
  //true_time_spent_on_draw_id[draw_id]['work_length'] = how long student worked, once they started working on this part.
  //true_time_spent_on_draw_id[draw_id]['total_view_time'] = total time spent by student for this part.
  //                                                       = first_draw + work_length

  $true_time_spent_on_draw_id = array();



  $start = getTime();
  foreach ($t_xml->wwProblem as $wwProblem) {
    $wwProblem_number = $wwProblem->wwProblem_number;
    if ($print_out_xml) { echo '<BR><font color=\'brown\'>t_xml: process wwProblem: ' . $wwProblem_number . '</font><BR>'; }
    foreach ($wwProblem->Replay as $replay) {
      foreach ($replay->ReplayPart as $replayPart) {
        $draw_id = $replayPart->draw_id;
        if ($print_out_xml) { echo "<BR>t_xml:  draw_id is $draw_id<BR>"; }
        $work_length = $replayPart->work_length;
        $total_view_time = $replayPart->total_view_time;
        $date_time = $replayPart->date_time;
        $timestamp = $replayPart->timestamp;
        if ($print_out_xml) { echo 't_xml:  first_draw = $total_view_time - $work_length = ' . $total_view_time . ' - ' . $work_length . ' = ' . ($total_view_time - $work_length) . '<BR>'; }
        $true_time_spent_on_draw_id["$draw_id"]['first_draw'] = $total_view_time - $work_length;
        $true_time_spent_on_draw_id["$draw_id"]['work_length'] = "$work_length";
        $true_time_spent_on_draw_id["$draw_id"]['total_view_time'] = "$total_view_time";

        $true_time_spent_on_draw_id["$draw_id"]['date_time'] = "$date_time";
        $true_time_spent_on_draw_id["$draw_id"]['timestamp'] = "$timestamp";
      }
    }
  }

  $end = getTime();

  if ($print_out_timings) { echo '<BR>time to get true_time_spend_on_draw_id: ' . round($end - $start, 4) . '<BR>'; }

  //go through the $xml->ReplayPart data.
  //get an array of the draw_ids that we should use for this replay.

  $start = getTime();
  $use_these_draw_ids = eliminate_duplicate_draw_ids($print_out_xml, $xml, $true_time_spent_on_draw_id);
  $end = getTime();
  if ($print_out_timings) { echo '<BR>time to eliminate_duplicate_draw_ids: ' . round($end - $start, 4) . '<BR>'; }

  if ($print_out_xml) { echo '<BR> we are using the draw ids: '; print_r($use_these_draw_ids); echo '<BR>'; }


//get the order of the draw_ids coming from the replay data.
//we use the draw_id whose Draw data is longest.

$running_time = 0;
$start = getTime();
foreach ($xml->ReplayPart as $replayPart) {
  $draw_id = $replayPart->draw_id;

  if (isset($use_these_draw_ids["$draw_id"])) {
    if (isset($true_time_spent_on_draw_id["$draw_id"])) {
      if ($print_out_xml) {
        echo '<BR>Using draw id: ' . $draw_id . '<BR>';
        echo $replayPart->Draw;
      }
      $order_of_draw_ids[] = $draw_id;
      $this_draw_id_info["$draw_id"]['date_time'] = $true_time_spent_on_draw_id["$draw_id"]['date_time'];
      $this_draw_id_info["$draw_id"]['timestamp'] = $true_time_spent_on_draw_id["$draw_id"]['timestamp'];
      $this_draw_id_info["$draw_id"]['first_time'] = $running_time;
      $this_draw_id_info["$draw_id"]['work_length'] = $true_time_spent_on_draw_id["$draw_id"]['work_length'];
      $this_draw_id_info["$draw_id"]['total_view_time'] = $true_time_spent_on_draw_id["$draw_id"]['total_view_time'];
      $this_draw_id_info["$draw_id"]['first_draw'] = $true_time_spent_on_draw_id["$draw_id"]['first_draw'];
      $running_time = $running_time + $this_draw_id_info["$draw_id"]['total_view_time']; //+ $total_view_time;
//      $running_time = $running_time + $true_time_spent_on_draw_id["$draw_id"]['total_view_time'];
//      $running_time = $running_time + $true_time_spent_on_draw_id["$draw_id"]['work_length'];

    }
    else {
      $order_of_draw_ids[] = $draw_id;
      $this_draw_id_info["$draw_id"]['date_time'] = "";
      $this_draw_id_info["$draw_id"]['timestamp'] = "";
      $this_draw_id_info["$draw_id"]['first_time'] = "";
      $this_draw_id_info["$draw_id"]['work_length'] = "";
      $this_draw_id_info["$draw_id"]['total_view_time'] = "";
      $this_draw_id_info["$draw_id"]['first_draw'] = "";
    }
    if ($print_out_xml) {
	echo '<BR>Using draw id: ' . "$draw_id" . '  Info:' . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['date_time'] = " . $this_draw_id_info["$draw_id"]['date_time'] . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['timestamp'] = " . $this_draw_id_info["$draw_id"]['timestamp'] . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['first_time'] = " . $this_draw_id_info["$draw_id"]['first_time'] . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['work_length'] = " . $this_draw_id_info["$draw_id"]['work_length'] . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['total_view_time'] = " . $this_draw_id_info["$draw_id"]['total_view_time'] . "<BR>\n";
	print " ... -> \$this_draw_id_info['\$draw_id = $draw_id]['first_draw'] = " . $this_draw_id_info["$draw_id"]['first_draw'] . "<BR>\n";
	print " ... and running time is now: $running_time. \n<P>";
    }
  }
  else {
    if ($print_out_xml) {
      echo '<BR>DO NOT USE draw_id: ' . "$draw_id" . '<BR>';
      echo $replayPart->Draw;
      echo '<P>';
    }
  }
}
$end = getTime();
if ($print_out_timings) { echo '<BR>time to determine which draw_ids to use: ' . round($end - $start, 4) . '<BR>'; }

//  $first_time_number = get_appropriate_time($last_time, $replayPart->Draw);
//  $last_time = get_last_time($last_time, $replayPart->Draw);

$start = getTime();
if (1) {

  $attempt_number = array();
  $attempt_count = 0;
  $start_timestamp = strtotime($start_time);

  while (count($attempts) > 2) {
    $attempt_number[$attempt_count]['score'] = array_shift($attempts);
    array_shift($attempts);
    if (!(array_key_exists('time', $attempt_number[$attempt_count]))) {  $attempt_number[$attempt_count]['time'] = ""; }
    $attempt_number[$attempt_count]['time'] .= array_shift($attempts);
    $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
    $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
    $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
    $attempt_number[$attempt_count]['timestamp'] = strtotime($attempt_number[$attempt_count]['time']);
    //print "attempt $attempt_count at time: " . $attempt_number[$attempt_count]['time'] . "<BR>";
    $attempt_count++;
  }

  $reverse_draw_ids = array_reverse($order_of_draw_ids);

  //$this_draw_id_info["$draw_id"]['first_time'] = $first_time_number;

  for ($k = 0; $k < $attempt_count; $k++) {
    //go backward through the $this_draw_id_info array.
    //the very first $this_draw_id_info[$draw_id] with a timestamp which satisfies
    //      $this_draw_id_info[$draw_id]['timestamp'] <= $attempt_number[$k]['timestamp']
    //should have the answer attempt associated with it.
    $claimed = -1;
    $claimed_timestamp = -1;
    $claimed_first_time = -1;
    foreach ($reverse_draw_ids as $key => $a_draw_id) {

    //print "this_draw_id_info['$a_draw_id']['timestamp'] = " . $this_draw_id_info['$a_draw_id']['timestamp'] . "<BR>";

      if (($claimed > 0) && ($this_draw_id_info["$a_draw_id"]["timestamp"] == $claimed_timestamp)) {
        //print 'draw_id ' . $a_draw_id . ' also associated with attempt ' . $k . ', since has same timestamp.<BR>';
        $attempt_number[$attempt_count]["b_draw_id"] = $a_draw_id;
        $this_draw_id_info["$a_draw_id"]["attempt"] = $k;
      }
      if (($claimed > 0) && ($this_draw_id_info["$a_draw_id"]["first_time"] == $claimed_first_time)) {
        //print 'draw_id ' . $a_draw_id . ' also associated with attempt ' . $k . ', since has same first time.<BR>';
        $attempt_number[$attempt_count]["c_draw_id"] = $a_draw_id;
        $this_draw_id_info["$a_draw_id"]["attempt"] = $k;
      }

      if (($claimed < 0) && ($this_draw_id_info["$a_draw_id"]["timestamp"] <= $attempt_number[$k]["timestamp"])) {
        //print 'draw_id ' . $a_draw_id . ' associated with attempt ' . $k . ': ' . $this_draw_id_info["$a_draw_id"]["timestamp"] . ' <= ' . $attempt_number[$k]["timestamp"] . '<BR>';
        $this_draw_id_info["$a_draw_id"]["attempt"] = $k;
        $attempt_number[$attempt_count]["a_draw_id"] = $a_draw_id;
        $claimed_timestamp = $this_draw_id_info["$a_draw_id"]["timestamp"];
        $claimed_first_time = $this_draw_id_info["$a_draw_id"]["first_time"];
        $claimed = 1;
      }
    }
  }
}
$end = getTime();
if ($print_out_timings) { print "<BR>time to match attempts to draw_ids: " . round($end - $start, 4) . "<BR>"; }



if ($print_out_xml) {
  echo 'attempt_number array is : ';
  echo '<PRE>';
  print_r($attempt_number);
  echo '</PRE>';
}

if (1) {

$show_times = 0;
$ccc = 0;
$bbb = 0;

$length_of_first_act = 0;

$running_time = 0;
$running_time_of_start_actions = 0;
$saw_webworkSubmission_at_this_time = array();

$process_submission_times = array();

$start = getTime();
foreach ($xml->ReplayPart as $replayPart) {
  $draw_id = $replayPart->draw_id;

  if (isset($use_these_draw_ids["$draw_id"])) {
    $draw_data = $replayPart->Draw;

    if (0) {
      if (get_appropriate_time(-1, $draw_data) == -1 ) {
        $appropriate_time = $this_draw_id_info["$draw_id"]['first_time'];
        $appropriate_time = $appropriate_time > 0 ? $appropriate_time : 10;
        if ($bbb == 1) {
          echo "we have appropriate_time $appropriate_time <BR>\n";
          echo "no time, so draw_data:\n $draw_data \n is now\n";
        }
        $r = "webworkSubmission;#;0;#;NaN";
        $n = "webworkSubmission;#;0;#;" . $appropriate_time;
        $draw_data = str_replace($r, $n, $draw_data);
        $r = "webworkNavigateAway;#;0;#;NaN";
        $n = "webworkNavigateAway;#;0;#;" . $appropriate_time;
        $draw_data = str_replace($r, $n, $draw_data);
        if ($bbb == 1) {
          echo "$draw_data\n";
        }
      }
    }

//  $actions_for_this_student = explode(";~;", $replayPart->Draw);

    $actions_for_this_student = explode(";~;", $draw_data);


//print "this_time is $this_time for draw_id $draw_id\n";

    if ($print_out_xml) { echo '<P>processing draw_id ' . $draw_id . ', running_time was ' . $running_time . ' and is now '; }
    if (1) {
      //show the amount of time spent at start before actual student writing:
//      imageline( $my_img, 2 + $running_time/$pps, $oh-1, 2 + ($running_time + $this_draw_id_info["$draw_id"]['first_draw'])/$pps, $oh-1, $label_color);
	$tmp_left = 2 + $running_time/$pps + 1;
	$tmp_right = 2 + ($running_time + $this_draw_id_info["$draw_id"]['first_draw'])/$pps - 1;
	$tmp_time_string = round(($this_draw_id_info["$draw_id"]['first_draw'] / 1000), 1) . "s";
	$tmp_time_string_length = strlen($tmp_time_string) * imagefontwidth(1);
	//check if the spacing is right on the top line of times.
        //if ($print_out_xml) {
	//  print "tmp_time_string_length: $tmp_time_string_length > (($tmp_right - $tmp_left) + 30)): ";
	//  print $tmp_time_string_length > (($tmp_right - $tmp_left) + 30) ? " yes " : " no ";
	//}
	if ($tmp_time_string_length > (($tmp_right - $tmp_left) - 4) ) {
	  $tmp_time_string = round(($this_draw_id_info["$draw_id"]['first_draw'] / 1000), 0);
	  $tmp_time_string_length = strlen($tmp_time_string) * imagefontwidth(1);
	}

	imageline( $my_img, $tmp_left, $oh-5, $tmp_right, $oh-5, $label_color);
	imageline( $my_img, $tmp_left, $oh-6, $tmp_left, $oh-4, $label_color);
	imageline( $my_img, $tmp_right, $oh-4, $tmp_right, $oh-6, $label_color);
	//imagestring( $my_img, 1, 2 + ($running_time + $running_time + $this_draw_id_info["$draw_id"]['first_draw'])/(2*$pps) - 12, $oh-10, shortFormatTime($this_draw_id_info["$draw_id"]['first_draw'] / 1000), $label_color);
	imagestring($my_img, 1, 1 + ($tmp_left + $tmp_right - $tmp_time_string_length)/2, $oh-13, $tmp_time_string, $label_color);
    }
    if ($print_out_xml) { echo 'this_draw_id_info[' . $draw_id . '][first_draw] = ' . $this_draw_id_info["$draw_id"]['first_draw'] . '<BR>'; }
    $running_time += $this_draw_id_info["$draw_id"]['first_draw'];
    $running_time_of_start_actions = $running_time;

    if ($print_out_xml) { echo $running_time . ";\n"; }
    if ($print_out_running_time) { echo ' (Added ' . $this_draw_id_info["$draw_id"]['first_draw'] . ' to running_time.<BR>'; }

    $previous_time = -1;
    $can_process_this_part = true;
    $start_i = 0;

    if (0 || ($show_times)) {
      imagestring( $my_img, 1, 2 + $running_time/$pps, 40, "$this_time", $label_color);
      imagestring( $my_img, 1, 2 + $running_time/$pps, 50, "$draw_id", $label_color);
      $ccc++;
    }

    //get past the initial 'undefined' recorded actions at the start of the xml Draw data:
    if ($print_out_xml) { echo "<BR>actions_for_this_student[$start_i] == _" . $actions_for_this_student[$start_i] . "_..\n"; }

    while ($actions_for_this_student[$start_i] == "\nundefined") {
      //imagestring( $my_img, 1, 2 + $running_time/$pps, 30, "$start_i", $label_color);
      if ($print_out_xml) { echo "<BR>skipping action $start_i: " . $actions_for_this_student[$start_i] . " since it is undefined.<BR>"; }
      $start_i++;
    }


    $number_of_actions_to_process = count($actions_for_this_student) - $start_i;

    for ($i = $start_i; $i < count($actions_for_this_student); $i++) {
      $this_action_data = explode(";#;", $actions_for_this_student[$i]);
//print "this_action_data[0] is ___" . trim($this_action_data[0]) . "___";

      if ($print_out_xml) { echo '<BR>working on processing action ' . $i . ': ' . trim($this_action_data[0]) . '.'; }

      if ((count($this_action_data) >= 2) && ($this_action_data[2] >= 0) && $can_process_this_part) {
        if ($print_out_xml) { echo '<BR>HERE, with previous_time = ' . $previous_time . '.</BR>'; }

        $this_action = trim($this_action_data[0]);
//print "---> Good!<BR>\n";
        $this_time = $this_action_data[2];

        if ($previous_time < 0) { 
          $previous_time = $this_time;
if ($print_out_xml) { print 'in (\$previous_time < 0) loop, so set \$previous_time to \$this_time = ' . $this_time . '<BR>'; }

          if ($show_times) {imagestring( $my_img, 1, 2 + $running_time/$pps, 30, "$this_time", $slider_color); }

//          if (array_key_exists("$this_time", $saw_these_first_times_of_parts) && ($saw_these_first_times_of_parts["$this_time"] == "seen")) {
          if (array_key_exists("$this_time", $saw_these_first_times_of_parts)) {
            $can_process_this_part = true;
            foreach ($saw_these_first_times_of_parts["$this_time"] as $int_cnt => $val_action) {
if ($print_out_xml) { 
  echo 'B:_' . $val_action . '__<BR>';
  echo 'B:_' . substr("$actions_for_this_student[$i]", 0, 150) . '__<BR>';
}
              if ("$val_action" == substr("$actions_for_this_student[$i]", 0, 150)) {
                $can_process_this_part = false;
                if ($print_out_xml) { echo 'B: can_process_this_part is false, since \$this_time = ' . $this_time . ' AND the trimmed first actions were in the array \$saw_these_first_times_of_parts[\$this_time]:<BR>'; }
              }
              else {
                if ($print_out_xml) { echo 'B/ no match: <BR>'; }
              }
if ($print_out_xml) {
  echo 'B:_' . $val_action . '___' . substr($actions_for_this_student[$i], 0, 150) . '___<BR>'; 
}
            }
            if ($can_process_this_part) {
              if ($print_out_xml) { echo 'BB: can_process_this_part is true, even though \$this_time = ' . $this_time . ' was in the array \$saw_these_first_times_of_part:<BR>'; print_r($saw_these_first_times_of_parts); echo '<BR> but the first trimmed action was not matching the key to the array.<BR>'; }
              $saw_these_first_times_of_parts["$this_time"][] = substr("$actions_for_this_student[$i]", 0, 150);
            }
          }
//OLD, NOT WORKING:
//            $can_process_this_part = false;
//if ($print_out_xml) { echo 'B:  can_process_this_part is false, since \$this_time = ' . $this_time . ' was in the array \$saw_these_first_times_of_parts:<BR>'; print_r($saw_these_first_times_of_parts); }
//          }
          else {
//OLD STYLE. NOT WORKING IF DUPLICATE this_time:
//            $saw_these_first_times_of_parts["$this_time"] = "seen";
              $saw_these_first_times_of_parts["$this_time"] = array();
              $saw_these_first_times_of_parts["$this_time"][] = substr("$actions_for_this_student[$i]", 0, 150);
if ($print_out_xml) { echo 'C: this_time = ' . $this_time; }

          }
        }
if ($print_out_xml) { echo 'previous_time = ' . $previous_time . "<BR>"; }

        if (($number_of_actions_to_process > $i + 1) && ($this_time == "NaN") && ($this_action != "webworkSubmission") && ($this_action != "webworkNavigateAway")) {
	  if ($print_out_xml) { 
	    print "this_time = NaN, and number_of_actions_to_process = $number_of_actions_to_process, so not processing draw_id: $draw_id.  running time is: $running_time. \n";
	  }

	  if ($this_action == "webworkSubmission") {
if ($print_out_xml) { print "<BR><FONT COLOR='RED'>We Should process this webworkSubmission!</FONT><BR>"; }
	  }
	  else if ($this_action == "webworkNavigateAway") {
	    if ($print_out_xml) { print "<BR><FONT COLOR='PURPLE'>We Should process this webworkNavigateAway!</FONT><BR>"; }

	  }

        }
        else if ($this_action == "draw") {
          $this_color = $this_action_data[4];
          $this_drawing = array_pop($this_action_data);
          $tmp_data = explode(",", $this_drawing);
          for ($j = 0; $j < count($tmp_data); $j++) {
            $tmp_tmp_data = explode("_", $tmp_data[$j]);
            if ($tmp_tmp_data[0] > 0) { 
              $this_time = $tmp_tmp_data[0];
	      $draw_x = $tmp_tmp_data[1] / 30 + 2;
              $draw_y = $tmp_tmp_data[2] / 20;
              $dTime = $this_time - $previous_time;
              $running_time += $dTime;
              $previous_time = $this_time;
              $this_x = $running_time/$pps;
              if ($this_color == 16777215) {
                //imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_y + $erase_height, $erase_color);
                if ($print_out_running_time) { print "<BR>$i draw (erase): running_time = $running_time.<BR>"; }
		imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_x + $draw_y + $oh, $erase_color);
		imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $erase_color);
              }
              else if ($this_color == 0) {
		//imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_y + $drawing_height, $drawing_color);
                if ($print_out_running_time) { print "<BR>$i draw: running_time = $running_time.<BR>"; }
                imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_x + $oh + $draw_y, $drawing_color);
		imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $drawing_color);
              }
              else {  //writing with a color other than white or black:
		//imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_y + $drawing_2_height, $drawing_color_2);
                if ($print_out_running_time) { print "<BR>$i draw (other color): running_time = $running_time.<BR>"; }
                imageline( $my_img, 2 + $this_x, 1 + $oh + $draw_y, 2 + $this_x, $draw_x + $draw_y + $oh, $drawing_color_2);
		imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $drawing_color_2);
              }
              $max_height = $max_height > $draw_x + $draw_y + $oh ? $max_height : $draw_x + $draw_y + $oh;
              $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
              if ($print_out_xml) { print "A: max_width is $max_width<BR>"; }
            }
          }
        }

        else if ($this_action == "webworkSubmission") {
	  if ($print_out_xml) {
	    print "<BR><FONT COLOR='RED'>We saw a webworkSubmission! (draw_id: $draw_id).</FONT>\n";
	  }
          if (($this_time != "NaN") && array_key_exists($this_time, $saw_webworkSubmission_at_this_time) && ($saw_webworkSubmission_at_this_time[$this_time] == "yes")) {
	    if ($print_out_xml) { 
		print "We do nothing - we already saw this time!\n";
		print "saw_webworkSubmission_at_this_time[\$this_time = $this_time] == 'yes'\n"; 
		print_r($saw_webworkSubmission_at_this_time); print "\n"; 
	    }
            //do nothing.  We already saw this time (i.e. this is a double recording of student work)
          }
          else if (($this_time == "NaN") && ($number_of_actions_to_process > $i + 1)) {
	    if ($print_out_xml) { print "We do nothing - not a valid time! and more than 1 or more actions to process!!\n"; }
            //do nothing.  Not a valid time.
          }
          else {
            if (array_key_exists("$draw_id", $this_draw_id_info) && array_key_exists("attempt", $this_draw_id_info["$draw_id"]) && ($this_draw_id_info["$draw_id"]["attempt"] >= 0)) {
              //$this_attempt = $this_draw_id_info["$draw_id"]["attempt"];
              $this_score = $attempt_number[$this_draw_id_info["$draw_id"]["attempt"]]['score'];
 	      if ($print_out_xml) {
		print "webworkSubmission: this_time = $this_time; running_time = $running_time; previous_time = $previous_time\n";
	      }

              if ($this_time == "NaN") {
                $dTime = 0;
                $running_time += $dTime;
                $previous_time = $previous_time;
                $this_x = $running_time/$pps;
              }
              else {
                $dTime = $this_time - $previous_time;
                $running_time += $dTime;
                $previous_time = $this_time;
                $this_x = $running_time/$pps;
              }

	      if ($print_out_xml) {
		print "webworkSubmission:  dTime = $dTime; running_time = $running_time; previous_time = $previous_time; this_x = $this_x;\n";
	      } 
              if ($print_out_running_time) { print "<BR>$i webworkSubmission: running_time = $running_time.<BR>"; }

              imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $submission_color);
              imageline( $my_img, 2 + $this_x, 1 + $oh, 2 + $this_x, $submission_height, $submission_color);

//              $this_submission = $attempts[$webworkSubmissions;
          
//            $webworkSubmissions++;
//            $these_submissions = str_split($this_submission);
              $these_submissions = str_split($this_score);
              for ($p = 0; $p < count($these_submissions); $p++) {
                $this_y = $submission_height + 10*$p;
                if ($these_submissions[$p] == "0") {
                  imagearc( $my_img, 2 + $this_x, $this_y, 10, 10, 0, 360, $submission_color);
                }
                else if ($these_submissions[$p] == "1") {
                  imagefilledarc( $my_img, 2 + $this_x, $this_y, 10, 10, 0, 360, $submission_color, IMG_ARC_PIE);
                }
                else {
                  imagefilledarc( $my_img, 2 + $this_x, $this_y/2, 10, 10, 0, 360, $error_submission_color, IMG_ARC_PIE);
                }
                if ($show_times) {   //currently not used at all
                  if ($webworkSubmissions % 2 == 1) {
                    imagestring( $my_img, 1, 2 + $this_x - 25, 50 + 0*$oh, $this_time, $graph_clor);
                    $max_height = $max_height > 50 + 0*$oh ? $max_height : 50 + 0*$oh;
                    $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
                    if ($print_out_xml) { print "B: max_width is $max_width<BR>"; }
                  }
                  else {
                    imagestring( $my_img, 1, 2 + $this_x - 25, 40 + 0*$oh, $this_time, $graph_color);
		    $max_height = $max_height > 40 + 0*$oh ? $max_height : 40 + 0*$oh;
                    $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
		    if ($print_out_xml) { print "C: max_width is $max_width<BR>"; }
                  }
                }
              }
              //imagestring( $my_img, 2, 2 + $this_x - 12, 35 + $oh, shortFormatTime($running_time/1000), $submission_color);
              $tmp_x_coord = 2 + $this_x - 12;
              $process_submission_times[$tmp_x_coord] = shortFormatTime($running_time/1000);

	      $max_height = $max_height > 35 + $oh ? $max_height : 35 + $oh;
	      $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
	      if ($print_out_xml) { print "D: max_width is $max_width<BR>"; }
//print "setting length_of_first_act ($length_of_first_act) to (running_time: $running_time / 1000) : ";
              $length_of_first_act = ($length_of_first_act > 0) ? $length_of_first_act : ($running_time/1000);
//print "$length_of_first_act" . ".\n";
              $saw_webworkSubmission_at_this_time["$this_time"] = "yes";
            }      
          }
        }
        else if ($this_action == 'webworkNavigateAway') {
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time/$pps;
          //$max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
          if ($print_out_xml) { echo "E: max_width is $max_width<BR>"; }
          if ($print_out_running_time) { echo "<BR>$i webworkNavigateAway: running_time = $running_time.<BR>"; }

          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $leaving_color);
          imageline( $my_img, 2 + $this_x, 1 + $oh, 2 + $this_x, $leaving_height, $leaving_color);
        }
        else if ($this_action == 'graph') {
          if ($print_out_xml) { echo "processing graph.  previous_time = $previous_time; this_time = $this_time; this_x = $this_x; <BR>"; }
          if ($print_out_xml) { echo 'GRAPH: 3:' . $this_action_data[3] . ' 4: ' . $this_action_data[4] . '<BR>'; }
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time/$pps;
          $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
	  if ($print_out_xml) { echo "F: max_width is $max_width<BR>"; }
          $graph_x = $this_action_data[7] / 30 + 2;
	  $graph_y = $this_action_data[6] / 20;
          if ($print_out_running_time) { echo "<BR>$i graph: running_time = $running_time.<BR>"; }

          //imageline( $my_img, 2 + $this_x, 1 + $oh, 2 + $this_x, $graph_height, $graph_color);
	  imageline( $my_img, 2 + $this_x, 1 + $oh + $graph_y, 2 + $this_x, $graph_x + $graph_y + $oh, $graph_color);
          imageline( $my_img, 3 + $this_x, 1 + $oh + $graph_y, 3 + $this_x, $graph_x + $graph_y + $oh, $graph_color);
          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $graph_color);
          imageline( $my_img, 3 + $this_x, $oh - 1, 3 + $this_x, $oh - 3, $graph_color);
          if ($print_out_xml) { echo "processed graph.  this_x is now $running_time / $pps. previous_time = $previous_time; this_time = $this_time; this_x = $this_x; <BR>"; }
        }
        else if ($this_action == 'ClearThisPartImage') {
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time / $pps;
	  $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
	  if ($print_out_xml) { echo "G: max_width is $max_width<BR>"; }
          if ($print_out_running_time) { echo "<BR>$i ClearThisPartImage: running_time = $running_time.<BR>"; }

          imageline( $my_img, 2 + $this_x, 1 + $oh, 2 + $this_x, $erase_all_height, $erase_all_color );
          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $erase_all_color);
        }
        else if ($this_action == 'text') {
          $this_texting = array_pop($this_action_data);
          //if ($print_out_xml) {print "<BR> -> this_texting = <PRE>$this_texting</PRE><BR>"; }
          $tmp_data = explode(",", $this_texting);
          //if ($print_out_xml) {print "<BR> -> there are " . count($tmp_data) . " texts<BR>"; }

          $text_x = $this_action_data[7];
          $text_y = $this_action_data[8] / 20;

          for ($j = 0; $j < count($tmp_data); $j++) {
            $tmp_tmp_data = explode("_", $tmp_data[$j]);
            if ($tmp_tmp_data[0] > 0) {
              $this_time = $tmp_tmp_data[0];
              //$type_y = $tmp_tmp_data[2] / 20;
              //$type_x = $tmp_tmp_data[1] / 30 + 2;
              $dTime = $this_time - $previous_time;
              $running_time += $dTime;
	      if ($print_out_running_time) { echo "<BR> $i text: running_time = $running_time<BR>"; }
              $previous_time = $this_time;
              $this_x = $running_time/$pps;
              imageline( $my_img, 2 + $this_x, 1 + $oh + $text_y, 2 + $this_x, 1 + $text_y + $j + $oh, $typing_color);
	      imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $typing_color);
              imagestring( $my_img, 1, 2+$this_x, 1 + $oh+ $text_y + $j, "t", $typing_color);
	      $max_height = $max_height > $text_y + $j + $oh ? $max_height : $text_y + $j + $oh;
	      $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
	      if ($print_out_xml) { echo "H: max_width is $max_width<BR>"; }
            }
            else {
	      if ($print_out_xml) { echo "<BR>Not a valid time for this text.<BR>"; }
            }
          }
        }
        else if ($this_action == 'AddImage') {
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time / $pps;
	  $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
          if ($print_out_xml) { echo "I: max_width is $max_width<BR>"; }

          $tad = explode('_', $this_action_data[9]);
          $image_x = $tad[3] / 30 + 2;
          $image_y = $tad[2] / 20;

          imageline( $my_img, 2 + $this_x, 1 + $oh + $image_y, 2 + $this_x, $image_x + $image_y + $oh, $image_color);
          imageline( $my_img, 3 + $this_x, 1 + $oh + $image_y, 3 + $this_x, $image_x + $image_y + $oh, $image_color);
          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $image_color);
          imageline( $my_img, 3 + $this_x, $oh - 1, 3 + $this_x, $oh - 3, $image_color);

          if ($print_out_running_time) { echo "<BR>$i AddImage: running_time = $running_time.<BR>"; }
        }
        else if ($this_action == 'Image') {
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time / $pps;
	  $max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
          if ($print_out_xml) { echo "J: max_width is $max_width<BR>"; }

          $image_x = $this_action_data[7] / 30 + 2;
          $image_y = $this_action_data[6] / 20;

          imageline( $my_img, 2 + $this_x, 1 + $oh + $image_y, 2 + $this_x, $image_x + $image_y + $oh, $image_color);
          imageline( $my_img, 3 + $this_x, 1 + $oh + $image_y, 3 + $this_x, $image_x + $image_y + $oh, $image_color);
          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $image_color);
          imageline( $my_img, 3 + $this_x, $oh - 1, 3 + $this_x, $oh - 3, $image_color);

          if ($print_out_running_time) { echo "<BR>$i Image: running_time = $running_time.<BR>"; }
        }
        else if ($this_action == 'slide') {
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time / $pps;
          //$max_width = $max_width > 2 + $this_x ? $max_width : 2 + $this_x;
          if ($print_out_xml) { echo "K: max_width is $max_width<BR>"; }

          if ($print_out_running_time) { echo "<BR>$i slide: running_time = $running_time.<BR>"; }
          imageline( $my_img, 2 + $this_x, 1 + $oh, 2 + $this_x, $slider_height, $slider_color );
          imageline( $my_img, 2 + $this_x, $oh - 1, 2 + $this_x, $oh - 3, $slider_color);
        }
        else {
	  if ($print_out_xml) {  echo "ELSE: this_action is $this_action<BR>"; }
          if ($print_out_running_time) { echo "<BR>$i BLANK: running_time = $running_time.<BR>"; }
	}
        }
      }
    }
    if (1) {
	$tmp_left = 2 + $running_time_of_start_actions/$pps;
	$tmp_right = 2 + $running_time/$pps;
	$tmp_time_string = round(($running_time - $running_time_of_start_actions)/1000, 1) . "s";
	$tmp_time_string = $tmp_time_string == "0s" ? "0" : $tmp_time_string;
	$tmp_time_string_length = strlen($tmp_time_string) * imagefontwidth(1);
	if ($tmp_time_string_length > $tmp_right - $tmp_left) {
	  $tmp_time_string = round(($running_time - $running_time_of_start_actions)/1000, 0);
	  $tmp_time_string_length = strlen($tmp_time_string) * imagefontwidth(1);
	}


      //show the amount of time spent on actual student work:
      imageline( $my_img, $tmp_left, $oh-5, $tmp_right, $oh-5, $drawing_color);
      imageline( $my_img, $tmp_left, $oh-4, $tmp_left, $oh-6, $drawing_color);
      imageline( $my_img, $tmp_right, $oh-4, $tmp_right, $oh-6, $drawing_color);
      //imagestring( $my_img, 1, 2 + ($running_time_of_start_actions + $running_time)/(2*$pps) - 12, $oh-10, shortFormatTime(($running_time - $running_time_of_start_actions) / 1000), $drawing_color);
      imagestring( $my_img, 1, 1 + ($tmp_left + $tmp_right - $tmp_time_string_length)/2, $oh-13, $tmp_time_string, $drawing_color);

      if ($extra_debug_info) {
        $tmp_down++;
	imagestring($my_img, 1, 1 + ($tmp_left + $tmp_right - $tmp_time_string_length)/2, $oh + 30 + 5*$tmp_down, "draw_id $draw_id", $drawing_color);
      }
    }
  }
}
$end = getTime();
if ($print_out_timings) { echo '<BR>time to insert the drawing stuff into the image: ' . round($end - $start, 4) . '<BR>'; }

$total_time_minutes = ceil($w / (1000 / $pps) / 60);
//print "total_time_minutes is $total_time_minutes = ceil( $width / (1000 / $pps) / 60)<P>";

//put in the minute segments:
for ($minute = 1; $minute <= $total_time_minutes; $minute++) {
  //print "minute is $minute<BR>";
  //print " line at $minute*60*(1000/$pps) = " . $minute*60*(1000/$pps) . "<BR>";
  //imageline(   $my_img, 2 + $minute*60*(1000/$pps), 1, 2 + $minute*60*(1000/$pps), $oh, $black); 
  $minute_length = 15;
  if ($minute < 10) { $minute_length = $minute_length + 10; }
  if ($minute >= 10) { $minute_length = $minute_length + 20; }
  $alt_color = ($alt_color == $light_grey_a) ? $light_grey_b : $light_grey_a;
  //imagefilledrectangle($my_img, 2 + ($minute - 1)*60*(1000/$pps), 0, 2 + $minute*60*(1000/$pps), 4, $alt_color);
 
  //imagestring( $my_img, 2, 2 + $minute*60*(1000/$pps)-$minute_length, 0, "$minute:00", $black);
}

//exit ;



$pixels_needed = $running_time / $pps;
$work_Time = shortFormatTime($running_time/1000);
imagestring( $my_img, 3, 5, $max_height+5, "$this_key $work_Time", $label_color);
//imagestring( $my_img, 3, 5, 40, "$pixels_needed", $label_color);
imageline( $my_img, $pixels_needed+1, $oh-3, $pixels_needed+1, $final_submission_height, $black);

foreach ($process_submission_times as $kkk => $vvv) {
  imagestring( $my_img, 2, $kkk, $max_height+5, "$vvv", $submission_color);
}

imageline( $my_img, 1, $max_height+5, $w, $max_height+5, $black);
imageline( $my_img, 1, $max_height+20, $w, $max_height+20, $black);

if ($print_out_xml) { print "max_width is $max_width, and uber_width is $uber_width<BR>";}
$max_width = $max_width > $uber_width ? $uber_width : $max_width;

$cropped = imagecreate($max_width + 61, $max_height+20);
imagecopy($cropped, $my_img, 0, 0, 0, 0, $max_width + 61, $max_height+20);

imageline( $cropped, $max_width + 60, 0, $max_width + 60, $max_height+20, $black);
imageline( $cropped, 0, $max_height+19, $max_width + 60, $max_height+19, $black);

if (!$print_out_xml) {
header( 'Content-type: image/png' );
}

//if ($start_timestamp == "") {
if (!$print_out_xml) {
  imagepng($cropped);
}

imagecolordeallocate($my_img, $erase_color);
imagecolordeallocate($my_img, $erase_all_color);
imagecolordeallocate($my_img, $slider_color);
imagecolordeallocate($my_img, $drawing_color);
imagecolordeallocate($my_img, $drawing_color_2);
imagecolordeallocate($my_img, $typing_color);
imagecolordeallocate($my_img, $graph_color);
imagecolordeallocate($my_img, $leaving_color);
imagecolordeallocate($my_img, $error_submission_color);
imagecolordeallocate($my_img, $image_color);
imagecolordeallocate($my_img, $submission_color);
imagecolordeallocate($my_img, $black);
imagedestroy($my_img);
imagedestroy($cropped);

}
?>
