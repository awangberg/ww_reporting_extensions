<?php

include("common.php");
include("../access.php");




if (isset($_REQUEST['do_these_students']) && isset($_REQUEST['commenter'])) {
  $do_these_students = $_REQUEST['do_these_students'];
  $commenter = $_REQUEST['commenter'];
}
else {
  echo 'call with a do_these_students string and a commenter list';
  exit;
}
$print_out_xml = 0;







$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = "webwork";
//select the database '$db'
$result = mysql_select_db("$db", $con);

//$do_these_courses = array();
//$list_these_students = array();
$do_quiz_problems = array();

ksort($do_these_students);

foreach ($do_these_students as $k => $v) {
  //list($tmp_course, $tmp_user_name, $tmp_source_file, $tmp_quiz_name) = explode("___", $v);
  $tmp_c_un_sf_qn_array = explode("___", $v);
  $tmp_course = array_shift($tmp_c_un_sf_qn_array);
  $tmp_user_name = array_shift($tmp_c_un_sf_qn_array);

//  $do_these_courses[$tmp_course] = $tmp_course;
//  $list_these_students[$tmp_user_name] = $tmp_user_name;

  while (count($tmp_c_un_sf_qn_array) > 1) {
    $tmp_source_file = array_shift($tmp_c_un_sf_qn_array);
    $tmp_quiz_name = array_shift($tmp_c_un_sf_qn_array);
    $weekly_quiz_sets[$tmp_quiz_name] = $tmp_quiz_name;
    //get the assignment problem for this student:
    $query = 'SELECT problem_id FROM `' . $tmp_course . '_problem` WHERE set_id="' . $tmp_quiz_name . '" AND source_file="' . $tmp_source_file . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $do_quiz_problems[$tmp_course][$tmp_user_name][$tmp_quiz_name][$row['problem_id']] = "Y";
    }
  }
}
mysql_close($con);

if ($print_out_xml) {
  echo "<PRE>";
  print_r($do_quiz_problems);
  echo "</PRE>";
}









//connect to the session database, to get the session id for each problem:
$con = mysql_connect($db_host, $db_user, $db_pass);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = "session";
//select the database '$db'
$result = mysql_select_db("$db", $con);

$session_ids = array();
$count_of_session_ids = 0;

foreach ($do_quiz_problems as $course => $user_name_array) {
  foreach ($user_name_array as $user_name => $quiz_name_array) {
    foreach ($quiz_name_array as $quiz_name => $ww_problem_number_array) {
      foreach ($ww_problem_number_array as $ww_problem_number => $y) {
        $query = 'SELECT problem_id FROM `wwStudentWorkForProblem` LEFT JOIN `user` ON wwStudentWorkForProblem.user_id = user.user_id LEFT JOIN `course` ON wwStudentWorkForProblem.course_id = course.course_id WHERE course_name="' . $course . '" AND user_name="' . $user_name . '" AND ww_set_id="' . $quiz_name . '" AND ww_problem_number="' . $ww_problem_number. '"';
        $result = mysql_query($query, $con);
        $session_ids[$count_of_session_ids] = 'none';
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $session_ids[$count_of_session_ids] = $row['problem_id'];
        }
        $count_of_session_ids++;
      }
    }
  }
}
mysql_close($con);

if ($print_out_xml) {
  echo "<PRE>";
  print_r($session_ids);
  echo "</PRE>";
}

//now for each session problem id, get the list of comment codes from the session database.
//first, though, we set up the image:



$width = 90;
$w = $width - 1;
$oh = 30;
$h = $oh;
$oh3 = $h/3;

$my_img = imagecreate($width, $h);
$background_color       = imagecolorallocate( $my_img, 255, 255, 255);


  //light pink  (2nd row upper right)
  $a = html2rgb("#EE68B1");
  $light_pink           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //maroon red  (2nd row middle)
  $a = html2rgb("#BA136E");
  $maroon_red       = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //purple (1st row middle)
  $a = html2rgb("#6D1699");
  $purple          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //light purple (1st row upper left)
  $a = html2rgb("#BC6BE6");
  $light_purple    = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //purple grey (1st row upper right)
  $a = html2rgb("#4E2D60");
  $purple_grey           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //lime green:  (tetrad, 3rd row, middle)
  $a = html2rgb("#93D615");
  $lime_green            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //evergreen:  (tetrad, 3rd row, lower left)
  $a = html2rgb("#436503");
  $evergreen            = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //blue:  (accented analogic, 3rd row, middle)
  $a = html2rgb("#3F209E");
  $blue           = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //yellow: (4th row, middle)
  //(#"E2E517")
  //yellow:  (triad, 3rd row, middle)
  $a = html2rgb("#E6C317"); //("#E2E517");
  $yellow          = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  //black
  $black       = imagecolorallocate( $my_img, 0, 0, 0);

  //brick red (2nd row, lower left)
  $a = html2rgb("#580231");
  $brick_red = imagecolorallocate( $my_img, $a[0], $a[1], $a[2]);

  $white =  imagecolorallocate( $my_img, 255, 255, 255);

  $light_grey_a = imagecolorallocate( $my_img, 190, 190, 190);
  $light_grey_b = imagecolorallocate( $my_img, 160, 160, 160);

  imagesetthickness ($my_img, 1);


  //put a thin border around the image:
  imageline( $my_img, 1, 0, $w, 0, $black);
  imageline( $my_img, 1, $oh, $w, $oh, $black);
  imageline( $my_img, 1, 0, 1, $h, $black);
  imageline( $my_img, 1, $h-1, $w, $h-1, $black);
  imageline( $my_img, $w, 0,  $w, $h, $black);

$this_width = ($w - 2) / $count_of_session_ids;

//connect to the wwSession database, to get the session id for each problem:
$con = mysql_connect($db_host, $db_user, $db_pass);
if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = "wwSession";
//select the database '$db'
$result = mysql_select_db("$db", $con);

$use_color = array();

foreach ($session_ids as $k => $session_id) {
  $use_color["AW"] = $light_pink;
  $use_color["GK"] = $light_pink;
  $use_color["NE"] = $light_pink;
  $comments = 0;
  $query = '';
  if ($session_id != "none") {
    $query = 'SELECT shortkey, sessionComments.commenter from `sessionCommentKeys` LEFT JOIN `sessionCommentKeysPossible` ON sessionCommentKeys.key_id = sessionCommentKeysPossible.key_id LEFT JOIN `sessionComments` ON sessionCommentKeys.comment_id = sessionComments.id WHERE record_valid="1" AND sessionCommentKeys.session_problem_id="' . $session_id . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $shortkey = $row['shortkey'];
      $commenter = strtoupper($row['commenter']);
      $use_color[$commenter] = $yellow;
      if ($shortkey == "done") { $use_color[$commenter] = $lime_green; }
      $comments++;
    }
  }
  if ($print_out_xml) {
    print $query . "<BR>";
    print "comments: $comments. this_width = $this_width.  k is $k and session_id is $session_id.  color is ";
    print_r($use_color);
    print "<BR>";
  }
  $l = 1 + $k*$this_width;
  $r = $l + $this_width;
  imagefilledrectangle( $my_img, $l+1, 0, $r-1, 9, $use_color['AW'] );
  imagefilledrectangle( $my_img, $l+1, 10, $r-1, 19, $use_color['GK'] );
  imagefilledrectangle( $my_img, $l+1, 20, $r-1, 29, $use_color['NE'] );
  //imagefilledrectangle( $my_img, $l+1, 0, $r-1, $h, $use_color);

  imageline( $my_img, $r, 0, $r, $h, $black);
}

imageline( $my_img, 0, 0, 0, $h-1, $black);
imageline( $my_img, 0, 0, $w, 0, $black);
imageline( $my_img, $w, 0, $w, $h-1, $black);
imageline( $my_img, 0, $h-1, $w, $h-1, $black);

//imagestring( $my_img, 3, 5, 40, "$pixels_needed", $label_color);
if (!$print_out_xml) {
  header( "Content-type: image/png" );
  imagepng( $my_img) ;
}
imagecolordeallocate($my_img, $black);
imagecolordeallocate($my_img, $light_pink);
imagecolordeallocate($my_img, $maroon_red);
imagecolordeallocate($my_img, $purple);
imagecolordeallocate($my_img, $light_purple);
imagecolordeallocate($my_img, $purple_grey);
imagecolordeallocate($my_img, $lime_green);
imagecolordeallocate($my_img, $evergreen);
imagecolordeallocate($my_img, $blue);
imagecolordeallocate($my_img, $yellow);
imagecolordeallocate($my_img, $brick_red);
imagecolordeallocate($my_img, $white);
imagecolordeallocate($my_img, $light_grey_a);
imagecolordeallocate($my_img, $light_grey_b);
imagedestroy($my_img);

?>
