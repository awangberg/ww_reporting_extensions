<?php

include("../common.php");

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
        if ($details[2] == "NaN") {
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
  $parts = explode(";~;", $xml_draw_data);
  $last_time = -1;
  $parts = array_reverse($parts);
  foreach ($parts as $part) {
    if ($last_time < 0) {
      $details = explode(";#;", $part);
      if (count($details) > 2) {
        if ($details[0] == "draw") {
          $draw_pixels = explode(",", $details[9]);
          foreach ($draw_pixels as $dp) {
            $txy = explode("_", $dp);
            if ($txy[0] == "NaN") { }
            else if ($txy[0] > 0) { $last_time = $txy[0]; }
            else { }
          }
        }
        else {
          if ($details[2] == "NaN") { }
           else if ($details[2] >= 0) { $last_time = $details[2]; }
           else { }
        }
      }
    }
  }
  if ($last_time < 0) { $last_time = $prev_last_time; }
  return $last_time;
}

if (isset($_REQUEST['session_id'])) {
  $session_id = $_REQUEST['session_id'];
  $attempt_list = $_REQUEST['attempts'];
  $map_width = $_REQUEST['map_width'];
  $pixels_per_second = $_REQUEST['pixels_per_second'];
  $this_key = $_REQUEST['key'];
  $start_time = $_REQUEST['startTime'];
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

$xml_string = `php ../../replaySessionProblem.php session $session_id awangberg`;

$xml = simplexml_load_string($xml_string);

$pixels_per_seconds = $pixels_per_second;
$pps = 1000/$pixels_per_seconds;
$width = $map_width;
$w = $width - 1;

$show_times = 0;

if (1) {
$my_img = imagecreate($width, 60);
$background_color       = imagecolorallocate( $my_img, 255, 255, 255);


//COLORS CHOSEN FROM http://colorshemedesigner.com
//RGB:  DC162D
//Scheme ID: 5z61TsOsOFfFf
//Adjust Scheme: More Contrast

//RGB:  6D1699
//Scheme ID:  4I61TsOsOK-K-
//Adjust Scheme:  High Contrast

//light pink  (2nd row upper right)
$a = html2rgb("#EE68B1");
$erase_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$erase_height = 15;

//maroon red  (2nd row middle)
$a = html2rgb("#BA136E");
$erase_all_color        = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$erase_all_height = 20;



//purple (1st row middle)
$a = html2rgb("#6D1699");
$drawing_color          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$drawing_height = 10;

//light purple (1st row upper left)
$a = html2rgb("#BC6BE6");
$drawing_color_2        = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$drawing_2_height = 10;

//purple grey (1st row upper right)
$a = html2rgb("#4E2D60");
$typing_color           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$typing_height = 15;



//lime green:  (tetrad, 3rd row, middle)
$a = html2rgb("#93D615");
$graph_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$graph_height = 20;

//evergreen:  (tetrad, 3rd row, lower left)
$a = html2rgb("#436503");
$image_color            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$image_height = 20;


//blue:  (accented analogic, 3rd row, middle)
$a = html2rgb("#3F209E");
$slider_color           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$slider_height = 20;

//yellow: (4th row, middle)
$a = html2rgb("#E2E517");
$leaving_color          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);
$leaving_height = 20;

//black
$submission_color       = imagecolorallocate( $my_img, 0, 0, 0);

//brick red (2nd row, lower left)
$a = html2rgb("#580231");
$error_submission_color = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

$submission_height = 20;
$final_submission_height = 59;

$label_color = imagecolorallocate( $my_img, 0, 0, 255);
$black = $submission_color;
$white =  imagecolorallocate( $my_img, 255, 255, 255);

imagesetthickness ($my_img, 1);

//put a thin border around the image:
imageline( $my_img, 0, 0, $w, 0, $black);
imageline( $my_img, 0, 0, 0, 59, $black);
imageline( $my_img, 0, 59, $w, 59, $black);
imageline( $my_img, $w, 0, $w, 59, $black);

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

//$this_draw_id_info = array();
$order_of_draw_ids = array();

$last_time = -1;

$add_seconds_at_start = -1;

foreach ($xml->ReplayPart as $replayPart) {
  $draw_id = $replayPart->draw_id;
  $draw_end = $replayPart->Draw_end;

  $first_time_number = get_appropriate_time($last_time, $replayPart->Draw);
  $last_time = get_last_time($last_time, $replayPart->Draw);
//  print "draw_id is $draw_id, first_time_number is $first_time_number, last_time is $last_time<BR>";
  //$first_time = explode(";~;", $replayPart->Draw);
  //$first_time_number_array = explode(";#;", $first_time[0]);
  //$first_time_number = $first_time_number_array[2];
  $order_of_draw_ids[] = $draw_id;
  //This is a very slow way to do this:
  //  $cmd = "ls -al --full-time /opt/session/drawings/draw" . $draw_id . "_*";
  //  $file_info = `$cmd`;
  //  $file_timestamp = explode(" ", $file_info);
  //  $file_time = explode(".", $file_timestamp[6]);
  //  $this_non_strtotime = $file_timestamp[5] . " " . $file_time[0];
  //This is a much much faster way to do this:
  $this_non_strtotime = date("Y-m-d H:i:s", filemtime('/opt/session/drawings/draw' . $draw_id . '_' . $draw_end));
  $this_draw_id_info["$draw_id"]['date_time'] = $this_non_strtotime;
  $this_draw_id_info["$draw_id"]['timestamp'] =  strtotime($this_non_strtotime);
  $this_draw_id_info["$draw_id"]['first_time'] = $first_time_number;
  print 'draw_id ' . $draw_id . ' has first_time = ' . $first_time_number . ' and has timestamp ' . $this_non_strtotime . '  <BR>';
  print "\n";
  if ($start_time != "") {
    if ($add_seconds_at_start == -1) {
print "A:  strtotime(" . $this_non_strtotime . ") - strtotime(" . $start_time . ") = ";
print " " . strtotime($this_non_strtotime) . " - " . strtotime($start_time) . " = " ;
      $add_seconds_at_start = strtotime($this_non_strtotime) - strtotime($start_time);
print "$add_seconds_at_start \n";
    }
    else if (strtotime($this_non_strtotime) - strtotime($start_time) < $add_seconds_at_start) {
      $add_seconds_at_start = strtotime($this_non_strtotime) - strtotime($start_time);
print "B:  strtotime(" . $this_non_strtotime . ") - strtotime(" . $start_time . ") = ";
print " " . strtotime($this_non_strtotime) . " - " . strtotime($start_time) . " = " ;
print "$add_seconds_at_start \n";
    }
  } 
}


if (1) {

$attempt_number = array();
$attempt_count = 0;
$start_timestamp = strtotime($start_time);

while (count($attempts) > 2) {
  $attempt_number[$attempt_count]['score'] = array_shift($attempts);
  array_shift($attempts);
  $attempt_number[$attempt_count]['time'] .= array_shift($attempts);
  $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
  $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
  $attempt_number[$attempt_count]['time'] .= " " . array_shift($attempts);
  $attempt_number[$attempt_count]['timestamp'] = strtotime($attempt_number[$attempt_count]['time']);
  //print "attempt $attempt_count at time: " . $attempt_number[$attempt_count]['time'] . "<BR>";
  $attempt_count++;
}

$reverse_draw_ids = array_reverse($order_of_draw_ids);

$this_draw_id_info["$draw_id"]['first_time'] = $first_time_number;

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

if (1) {

$show_times = 0;
$ccc = 0;
$bbb = 0;

$length_of_first_act = 0;

foreach ($xml->ReplayPart as $replayPart) {
  $draw_id = $replayPart->draw_id;

  $draw_data = $replayPart->Draw;

  if (get_appropriate_time(-1, $draw_data) == -1 ) {
    $appropriate_time = $this_draw_id_info["$draw_id"]['first_time'];
    $appropriate_time = $appropriate_time > 0 ? $appropriate_time : 10;
    if ($bbb == 1) {
      print "we have appropriate_time $appropriate_time <BR>\n";
      print "no time, so draw_data:\n $draw_data \n is now\n";
    }
    $r = "webworkSubmission;#;0;#;NaN";
    $n = "webworkSubmission;#;0;#;" . $appropriate_time;
    $draw_data = str_replace($r, $n, $draw_data);
    $r = "webworkNavigateAway;#;0;#;NaN";
    $n = "webworkNavigateAway;#;0;#;" . $appropriate_time;
    $draw_data = str_replace($r, $n, $draw_data);
    if ($bbb == 1) {
      print "$draw_data\n";
    }
  }

//  $actions_for_this_student = explode(";~;", $replayPart->Draw);

  $actions_for_this_student = explode(";~;", $draw_data);


//print "this_time is $this_time for draw_id $draw_id\n";

  $previous_time = -1;
  $can_process_this_part = true;
  $start_i = 0;

  if ($show_times) {
    imagestring( $my_img, 1, 2 + 0 + $ccc*40, 30, "$this_time", $label_color);
    imagestring( $my_img, 1, 2 + 0 + $ccc*40, 50, "$draw_id", $label_color);
    $ccc++;
  }

  while ($actions_for_this_student[$start_i] == " undefined") {
    imagestring( $my_img, 1, 2 + 300, 30, "$start_i", $label_color);
    $start_i++;
  }

  for ($i = $start_i; $i < count($actions_for_this_student); $i++) {
    $this_action_data = explode(";#;", $actions_for_this_student[$i]);
//print "this_action_data[0] is ___" . trim($this_action_data[0]) . "___";
    if ((count($this_action_data) >= 2) && ($this_action_data[2] >= 0) && $can_process_this_part) {
      $this_action = trim($this_action_data[0]);
//print "---> Good!<BR>\n";
      $this_time = $this_action_data[2];

      if ($previous_time < 0) { 
        $previous_time = $this_time;

        if ($show_times) {imagestring( $my_img, 1, 2 + $running_time/$pps, 30, "$this_time", $slider_color); }

        if ($saw_these_first_times_of_parts["$this_time"] == "seen") {
          $can_process_this_part = false;
        }
        else {
          $saw_these_first_times_of_parts["$this_time"] = "seen";
        }
      }

      if ($this_time == "NaN") {  }
      else if ($this_action == "draw") {
        $this_color = $this_action_data[4];
        $this_drawing = array_pop($this_action_data);
        $tmp_data = explode(",", $this_drawing);
        for ($j = 0; $j < count($tmp_data); $j++) {
          $tmp_tmp_data = explode("_", $tmp_data[$j]);
          if ($tmp_tmp_data[0] > 0) { 
            $this_time = $tmp_tmp_data[0];
            $dTime = $this_time - $previous_time;
            $running_time += $dTime;
            $previous_time = $this_time;
            $this_x = $running_time/$pps;
            if ($this_color == 16777215) {
              imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $erase_height, $erase_color);
            }
            else if ($this_color == 0) {
              imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $drawing_height, $drawing_color);
            }
            else {  //writing with a color other than white or black:
              imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $drawing_2_height, $drawing_color_2);
            }
          }
        }
      }

      else if ($this_action == "webworkSubmission") {
        if ($saw_webworkSubmission_at_this_time[$this_time] == "yes") {
          //do nothing.  We already say this time (i.e. this is a double recording of student work)
        }
        else if ($this_time == "NaN") {
          //do nothing.  Not a valid time.
        }
        else {
          if ($this_draw_id_info["$draw_id"]["attempt"] >= 0) {
            //$this_attempt = $this_draw_id_info["$draw_id"]["attempt"];
            $this_score = $attempt_number[$this_draw_id_info["$draw_id"]["attempt"]]['score'];
            $dTime = $this_time - $previous_time;
            $running_time += $dTime;
            $previous_time = $this_time;
            $this_x = $running_time/$pps;
 
            imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $submission_height, $submission_color);
//            $this_submission = $attempts[$webworkSubmissions];
          
//          $webworkSubmissions++;
//          $these_submissions = str_split($this_submission);
            $these_submissions = str_split($this_score);
            for ($p = 0; $p < count($these_submissions); $p++) {
              $this_y = 20 + 10*$p;
              if ($these_submissions[$p] == "0") {
                imagearc( $my_img, 2 + $this_x, $this_y, 10, 10, 0, 360, $submission_color);
              }
              else if ($these_submissions[$p] == "1") {
                imagefilledarc( $my_img, 2 + $this_x, $this_y, 10, 10, 0, 360, $submission_color, IMG_ARC_PIE);
              }
              else {
                imagefilledarc( $my_img, 2 + $this_x, $this_y/2, 10, 10, 0, 360, $error_submission_color, IMG_ARC_PIE);
              }
              if ($showtimes) {
                if ($webworkSubmissions % 2 == 1) {
                  imagestring( $my_img, 1, 2 + $this_x, 50, $this_time, $graph_clor);
                }
                else {
                  imagestring( $my_img, 1, 2 + $this_x, 40, $this_time, $graph_color);
                }
              }
            }
            imagestring( $my_img, 1, 2 + $this_x, 50, shortFormatTime($running_time/1000), $label_color);
//print "setting length_of_first_act ($length_of_first_act) to (running_time: $running_time / 1000) : ";
            $length_of_first_act = ($length_of_first_act > 0) ? $length_of_first_act : ($running_time/1000);
//print "$length_of_first_act" . ".\n";
            $saw_webworkSubmission_at_this_time["$this_time"] = "yes";
          }      
        }
      }
      else if ($this_action == "webworkNavigateAway") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time/$pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $leaving_height, $leaving_color);
      }
      else if ($this_action == "graph") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time/$pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $graph_height, $graph_color);
      }
      else if ($this_action == "ClearThisPartImage") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time / $pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $erase_all_height, $erase_all_color );
      }
      else if ($this_action == "text") {
        $this_texting = array_pop($this_action_data);
        $tmp_data = explode(",", $this_texting);
        for ($j = 0; $j < count($tmp_data); $j++) {
          $tmp_tmp_data = explode("_", $tmp_data[$j]);
          $this_time = $tmp_tmp_data[0];
          $dTime = $this_time - $previous_time;
          $running_time += $dTime;
          $previous_time = $this_time;
          $this_x = $running_time/$pps;
          imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $typing_height, $typing_color);
        }
      }
      else if ($this_action == "AddImage") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time / $pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $image_height, $image_color );
//        imagestring( $my_img, 1, 2 + $this_x, 50, shortFormatTime($running_time/1000), $erase_all_color);
      }
      else if ($this_action == "Image") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time / $pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $iamge_height, $image_color );
      }
      else if ($this_action == "slide") {
        $dTime = $this_time - $previous_time;
        $running_time += $dTime;
        $previous_time = $this_time;
        $this_x = $running_time / $pps;
        imageline( $my_img, 2 + $this_x, 1, 2 + $this_x, $slider_height, $slider_color );
      }
      else {}

      }
    }
  }
}

$pixels_needed = $running_time / $pps;
$work_Time = shortFormatTime($running_time/1000);
imagestring( $my_img, 3, 5, 40, "$this_key $work_Time", $label_color);
//imagestring( $my_img, 3, 5, 40, "$pixels_needed", $label_color);
imageline( $my_img, $pixels_needed+1, 1, $pixels_needed+1, $final_submission_height, $black);

header( "Content-type: image/png" );

if ($start_timestamp == "") {
imagepng( $my_img);
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
}
else {
  $width = imagesx($my_img);
  $height = imagesy($my_img);

  print "add_seconds_at_start is $add_seconds_at_start\n";
  print "shift_over is ($add_seconds_at_start - $length_of_first_act ) * 1000 / $pps = ";

  $shift_over = ($add_seconds_at_start - $length_of_first_act) * 1000 / $pps;
  print "$shift_over\n";
  $new_width = $shift_over + $width;
print "new_width: $new_width . \n";
  $shift_img = imagecreate($new_width, 120);
  $background_color = imagecolorallocate($shift_img, 255, 255, 255);
  imagecopyresized($shift_img, $my_img, 0, 0, 0, 0, $width, $height, $width, $height);
  imagecopyresized($shift_img, $my_img, $shift_over, 60, 0, 0, $width, $height, $width, $height);
  imagestring($shift_img, 1, 2 + 0, 50, "(-" .  shortFormatTime($shift_over) . ")", $submission_color);
  imagestring($shift_img, 1, 2 + 40, 50, " [ " . shortFormatTime($running_time/1000 + $shift_over) . "]", $submission_color);
//imagestring( $my_img, 1, 2 + $this_x, 50, shortFormatTime($running_time/1000), $label_color);


  imagepng($shift_img);
  imagedestroy($my_img);
  imagedestroy($shift_img); 
}


}
?>
