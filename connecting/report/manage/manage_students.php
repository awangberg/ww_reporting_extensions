<?php

include("../../access.php");
include("../common.php");

if (isset($_REQUEST['record_user_permissions'])) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $course = $_REQUEST['list_for_course'];
  $course_id = $_REQUEST['course_id'];

  $db = 'wwSession';
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $user_ids = array();
  foreach ($_REQUEST as $k => $v) {
    $key = substr($k, 0, 3);
    if ($key == 'vfs') {
      $user_ids[] = substr($k, 4);
    }
  }

  foreach ($user_ids as $k => $user_id) {
    $id = $_REQUEST['id_' . $user_id ];
    $vu = $_REQUEST['vu_' . $user_id ];
    $vfs = $_REQUEST['vfs_' . $user_id ];
    $fc = $_REQUEST['fc_' . $user_id ];
    if ($id >= 0) {
      //update the record
      $query = 'UPDATE course_wwUserPermissions '
	     . 'SET validUser="' . $vu . '", '
	     . 'validForStatistics="' . $vfs . '", '
	     . 'finishedCourse="' . $fc . '" '
	     . 'WHERE id="' . $id . '"';
      $result = mysql_query($query, $con);
      //echo '<BR>query: $query<BR>';
      echo 'Updated User ' . $user_id . ' with record id <b>' . $id . '</b> to have ';
      echo 'validUser=' . $vu . ' and ';
      echo 'validForStatistics=' . $vfs . '<BR>';
    }
    else {
      //insert a new record
     $query = 'INSERT INTO course_wwUserPermissions(course_wwValidCourses_id, ww_user_id, validUser, validForStatistics) VALUES ("' . $course_id . '", "' . $user_id . '", "' . $vu . '", "' . $vfs . '")';
     $result = mysql_query($query, $con);
     //echo '<BR>query: $query<BR>';
     echo 'Inserted user ' . $user_id . ' with wwValidCourses_id ' . $course_id . ' ';
     echo 'validUser=' . $vu . ' and ';
     echo 'validForStatistics=' . $vfs . '<BR>';
    }
  }

  mysql_close($con);

  //echo 'RECORD USER PERMISSIONS';
  //echo '<pre>';
  //print_r($_REQUEST);
  //echo '</pre>';
  echo '<BR><BR><a href="">Go back to manage students page</a>';
}
else if (isset($_REQUEST['list_for_course'])) {
  //echo 'LIST FOR COURSE';
  //echo '<pre>';
  //print_r($_REQUEST);
  //echo '</pre>';

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $course = $_REQUEST['list_for_course'];

  $db = 'wwSession';
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT id FROM `course_wwValidCourses` WHERE ww_course="' . $course . '"';
  $result = mysql_query($query, $con);
  $course_id = -1;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['id'];
  }
  $users = array();

  if ($course_id >= 0) {
    $query = 'SELECT id, ww_user_id, validUser, validForStatistics, finishedCourse FROM `course_wwUserPermissions` WHERE course_wwValidCourses_id="' . $course_id . '"';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $id = $row['id'];
      $user_id = $row['ww_user_id'];
      $vu = $row['validUser'];
      $vfs = $row['validForStatistics'];
      $fc = $row['finishedCourse'];
      $users[$user_id] = array();
      $users[$user_id]['id'] = $id;
      $users[$user_id]['validUser'] = $vu;
      $users[$user_id]['validForStatistics'] = $vfs;
      $users[$user_id]['finishedCourse'] = $fc;
    }
  }

  $db = 'webwork';
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $course = $_REQUEST['list_for_course'];

  $query = 'SELECT user_id, first_name, last_name FROM `' . $course . '_user` ORDER BY last_name, first_name';
  $result = mysql_query($query, $con);

  echo '
	<form method=\'post\'>
       ';
  echo '<fieldset>
	<legend>Set the Permissions of the users</legend>';
  echo '<TABLE>';
  echo '<TR><TD>ID</TD><TD>User name</TD><TD>Valid User</TD><TD>Valid for Statistics</TD><TD>Finished the Course</TD></TR>';

  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $fn = $row['first_name'];
    $ln = $row['last_name'];
    $user_id = $row['user_id'];
    $id = isset($users[$user_id]) ? $users[$user_id]['id'] : -1;
    $vu = isset($users[$user_id]) ? $users[$user_id]['validUser'] : 0;
    $vfs = isset($users[$user_id]) ? $users[$user_id]['validForStatistics'] : 0;
    $fc = isset($users[$user_id]) ? $users[$user_id]['finishedCourse'] : 0;
    $color = $vfs ? 'red' : 'blue';

    echo '<TR>';
    echo '<TD>' . $id . '<input id=\'id_' . $user_id . '\' name=\'id_' . $user_id . '\' type=\'hidden\' value=\'' . $id . '\' ></TD><TD><font color="' . $color . '">' . $fn . ' ' . $ln . '</font></TD>';
    echo '<TD> Valid User: ';
    echo '<input id=\'vu_' . $user_id . '\' name=\'vu_' . $user_id . '\' type=\'radio\' value=\'1\' ' . ($vu == 1 ? 'CHECKED' : '') . ' > Yes ';
    echo '<input id=\'vu_' . $user_id . '\' name=\'vu_' . $user_id . '\' type=\'radio\' value=\'0\' ' . ($vu == 0 ? 'CHECKED' : '') . ' > No. ';
    echo '</TD>';
    echo '<TD> Valid For Statistics: ';
    echo '<input id=\'vfs_' . $user_id . '\' name=\'vfs_' . $user_id . '\' type=\'radio\' value=\'1\' ' . ($vfs == 1 ? 'CHECKED' : '') . ' > Yes';
    echo '<input id=\'vfs_' . $user_id . '\' name=\'vfs_' . $user_id . '\' type=\'radio\' value=\'0\' ' . ($vfs == 0 ? 'CHECKED' : '') . ' > No.';
    echo '</TD>';
    echo '<TD> Finished Course: ';
    echo '<input id=\'fc_' . $user_id . '\' name=\'fc_' . $user_id . '\' type=\'radio\' value=\'1\' ' . ($fc == 1 ? 'CHECKED' : '') . ' > Yes';
    echo '<input id=\'fc_' . $user_id . '\' name=\'fc_' . $user_id . '\' type=\'radio\' value=\'0\' ' . ($fc == 0 ? 'CHECKED' : '') . ' > No.';
    echo '</TD>';

    echo '</TR>';
  }
  echo '</TABLE>';
  echo '</fieldset>';
  echo '<input name=\'list_for_course\' id=\'list_for_course\' type=\'hidden\' value=\'' . $course . '\'>';
  echo '<input name=\'course_id\' id=\'course_id\' type=\'hidden\' value=\'' . $course_id . '\'>';
  echo '<input name=\'record_user_permissions\' type=\'submit\' id=\'record_user_permissions\' valud=\'Record User Permissions\'>
	</form>';
  mysql_close($con);

}
else {
  //list the valid courses

  echo '
    <form method=\'post\'>
    ';

  echo '<fieldset>
	<legend>Select the WeBWorK course for listing student permissions</legend>';

  $courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

  foreach ($courses as $k => $cc) {
    echo '<input name=\'list_for_course\' type=\'radio\' value=\'' . $cc . '\'> ' . $cc . ' <BR>';
  }

  echo '<input name=\'send\' type=\'submit\' id=\'list_students\' value=\'List Students\'>
	</form>';
}
