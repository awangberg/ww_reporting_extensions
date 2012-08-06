<?php

include("../../access.php");
include("../common.php");

if (isset($_REQUEST['record_course_permissions'])) {
  //echo 'RECORD COURSE PERMISSIONS';
  //echo '<pre>';
  //print_r($_REQUEST);
  //echo '</pre>';

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = 'wwSession';
  //select the database $db
  $result = mysql_select_db("$db", $con);

  //if the id exists, then update that record in the course_wwValidCourses table.
  if (isset($_REQUEST['course_id'])) {
    $query = "UPDATE course_wwValidCourses "
	   . "SET validCourse=" . (isset($_REQUEST['valid_course']) ? "TRUE" : "FALSE") . ", "
	   . "validForStatistics=" . (isset($_REQUEST['valid_for_statistics']) ? "TRUE" : "FALSE") . " "
	   . "validForCourseContainer=" . (isset($_REQUEST['valid_for_course_container']) ? 'TRUE' : 'FALSE') . " "
	   . "WHERE id='" . $_REQUEST['course_id'] . "'";
    $result = mysql_query($query, $con);
    echo 'Updated course with record id: <b> ' . $_REQUEST['course_id'] . '</b> to have ';
    echo 'Valid Course: ' . (isset($_REQUEST['valid_course']) ? 'TRUE' : 'FALSE');
    echo ', Valid for Statistics: ' . (isset($_REQUEST['valid_for_statistics']) ? 'TRUE' : 'FALSE');
    echo ', Valid for Course Container: ' . (isset($_REQUEST['valid_for_course_container']) ? 'TRUE' : 'FALSE');
  }
  //else, add the course to the table and set the permissions to validCourse and validForStatistics to the
  //values of $_REQUEST['valid_course'] and $_REQUEST['valid_for_statistics']
  else {
    $query = "SELECT id FROM `course_wwValidCourses` WHERE ww_course='" . $_REQUEST['course_name'] . "'";
    $result = mysql_query($query, $con);
    $id = -1;

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $id = $row['id'];
    }
    if ($id < 0) {
      $query = "INSERT INTO course_wwValidCourses(ww_course, validCourse, validForStatistics, validForCourseContainer) VALUES ('" . $_REQUEST['course_name'] . "', " . (isset($_REQUEST['valid_course']) ? "TRUE" : "FALSE") . ", " . (isset($_REQUEST['valid_for_statistics']) ? "TRUE" : "FALSE") . ", " . (isset($_REQUEST['valid_for_course_container']) ? "TRUE" : "FALSE") . ")";
      $result = mysql_query($query, $con);

      echo 'Inserted the course <b>' . $_REQUEST['course_name'] . '</b> into the table with attributes:  ';
      echo 'Valid Course: ' . (isset($_REQUEST['valid_course']) ? "TRUE" : "FALSE");
      echo ', Valid for Statistics: ' . (isset($_REQUEST['valid_for_statistics']) ? "TRUE" : "FALSE" );
      echo ', Valid for Course Container: ' . (isset($_REQUEST['valid_for_course_container']) ? "TRUE" : "FALSE" );
    }
    else {
      echo 'There was already a record in the table with course name . ' . $_REQUEST['course_name'] . '<BR>';
    }
  }

  echo '<BR><BR><a href="">Go back to manage courses page</a>';

  //echo "<BR>query:<BR>$query<BR>";
  mysql_close($con);
}

else if (isset($_REQUEST['editCourse'])) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) { 
    die('Could not connect: ' . mysql_error());
  }

  $db = 'wwSession';
  //select the database $db
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT id, validCourse, validForStatistics, validForCourseContainer FROM `course_wwValidCourses` WHERE ww_course="' . $_REQUEST['editCourse'] . '"';
  $result = mysql_query($query, $con);

  $id = -1;
  $vc = false;
  $vfs = false;
  $vfcc = false;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $id = $row['id'];
    $vc = $row['validCourse'];
    $vfs = $row['validForStatistics'];
    $vfcc = $row['validForCourseContainer'];
  }

  echo '
    <form method=\'post\'>
    The course <b>' . $_REQUEST['editCourse'] . '</b> is: <BR>';

  if ($id >= 0) {
    echo '<input id=\'course_id\' name=\'course_id\' type=\'hidden\' value=\'' . $id . '\'>';
  }
  echo '<input id=\'course_name\' name=\'course_name\' type=\'hidden\' value=\'' . $_REQUEST['editCourse'] . '\'>';
  echo '<input id=\'valid_course\' name=\'valid_course\' type=\'checkbox\' ' . ($vc ? 'CHECKED' : '') . '> Valid Course <BR>';
  echo '<input id=\'valid_for_statistics\' name=\'valid_for_statistics\' type=\'checkbox\' ' . ($vfs ? 'CHECKED' : '') . '> Valid for Statistics<BR>';
  echo '<input id=\'valid_for_course_container\' name=\'valid_for_course_container\' type=\'checkbox\' ' . ($vfcc ? 'CHECKED' : '') . '> Valid for Course Container<BR>';

  echo '<input name=\'record_course_permissions\' type=\'submit\' id=\'record_course_permissions\' value=\'Record Course Permissions\'>
        </form>';

  mysql_close($con);

}
else {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = 'information_schema';
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $query = "SELECT TABLE_NAME FROM `TABLES` WHERE TABLE_SCHEMA='webwork' AND TABLE_NAME LIKE '%_key'";

  $result = mysql_query($query, $con);

  $coursename = array();
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $cn = substr($row['TABLE_NAME'], 0, -4);
    $coursename["$cn"] = array();
  }

  echo '
    <form method=\'post\'>
    ';
  echo '<fieldset>
        <legend>Select the WeBWorK course to adjust permissions</legend>';

  $db = 'wwSession';
  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  echo '<TABLE BORDER=1><TR>
	<TD>Edit</TD>
	<TD>Course Name</TD>
	<TD>Valid Course</TD>
	<TD>Valid for Statistics</TD>
	<TD>Valid for Course Container</TD>
	</TR>';
  foreach ($coursename as $cc => $vv) {
    echo "<TD><input name='editCourse' type='radio' value='" . $cc . "'></TD><TD>" . $cc . '</TD>';
 
    $query = "SELECT validCourse, validForStatistics, validForCourseContainer FROM `course_wwValidCourses` WHERE ww_course='" . $cc . "'";
    //echo 'query is <BR>' . $query . '<BR>';
    $result = mysql_query($query, $con);

    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $vc = $row['validCourse'];
      $vfs = $row['validForStatistics'];
      $vfcc = $row['validForCourseContainer'];
      echo '<TD>' . ($vc == 1 ? 'Valid Course' : '.') . '</TD>';
      echo '<TD>' . ($vfs == 1 ? 'Valid for STATS' : '.') . '</TD>';
      echo '<TD>' . ($vfcc == 1 ? 'Valid for Course Container' : '.') . '</TD>';
    }
    echo '</TR>';
  }
  echo '</TABLE>';
  echo '</fieldset>';

  echo '<input name=\'send\' type=\'submit\' id=\'edit_courses\' value=\'Edit Courses\'>
        </form>';

  mysql_close($con);
}
