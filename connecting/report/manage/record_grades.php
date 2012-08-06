<?php

include("../../access.php");
include("../common.php");

//process the grades for the student, and be done.
if (isset($_REQUEST['record_grades'])) {

//echo 'HELLO!';
//echo "<pre>";
//print_r($_REQUEST);
//echo "</pre>";

  $report_on_these_courses = $_REQUEST['report_for_this_course'];
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = 'wwSession';
  //select the database: $db
  mysql_select_db("$db", $con);

  if ($_REQUEST['type_of_input'] == 'as_textareas') {
    echo '<B>process the text areas</B>';
    
    foreach ($report_on_these_courses as $ind => $course) {
    //for each course,
    //get the grade_item
    if (isset($_REQUEST['new_grade_item']) && ($_REQUEST['new_grade_item'] != '')) {
      $grade_item = $_REQUEST['new_grade_item'];
      $query = 'SELECT id FROM `course_gradeItem` where course_gradeItem="' . $grade_item . '"';
      $result = mysql_query($query, $con);

      $grade_id = -1;
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $grade_id = $row['id'];
      }

      if ($grade_id < 0) {
        $query = 'INSERT INTO course_gradeItem(course_gradeItem) VALUES ("' . $grade_item . '")';
        $result = mysql_query($query, $con);

        $grade_id = mysql_insert_id();
      }
    }
    else {
      $grade_id = $_REQUEST['grade_item_id'];
    }
    //get the usernames:
    $usernames = explode("\n", $_REQUEST['grade_usernames_' . $course ]);
    $grade_letter = explode("\n", $_REQUEST['grade_letter_' . $course ]);
    $grade_percent = explode("\n", $_REQUEST['grade_percent_' . $course ]);

    $user_ids = get_users_ids_from_course($con, $course, $user, false);

    $existing_record_for_user_id = array();
    //get the users whose grades are entered for this item:
    $query = 'SELECT id, course_wwUserPermissions_id FROM `course_grades` WHERE course_gradeItem_id="' . $grade_id . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $user_id = $row['course_wwUserPermissions_id'];
      $id = $row['id'];
      $existing_record_for_user_id[$user_id] = $id;
    }

    foreach ($usernames as $ind => $username) {
      //update if there, otherwise, insert:
      $user_id = $user_ids["$course"][trim($username)];
      $letter = trim($grade_letter[$ind]);
      $percent = trim($grade_percent[$ind]);

print "USERNAME IS _" . $username . "_ and course is $course and USER_ID IS $user_id <BR>";
      if (trim($username) == "" || $user_id < 0) { 
        echo '<B>Username: ' . $username . ' is not valid, not inserting grade letter: ' . $letter . ', percent: ' . $percent . ' for them.</B><BR>';
      }
      else if (isset($existing_record_for_user_id[$user_ids[$username]])) {
        //update the record for this user
        $query = 'UPDATE course_grades '
		.' SET gradeLetter="' . $letter . '", '
		.' gradePercent="' . $percent . '" '
		.' WHERE id=' . $existing_record_for_user_id[$user_id];
        $result = mysql_query($query, $con);

print "<BR>query is $query . <BR>";

        echo 'Updated User ' . $username . ' with id ' . $user_id . ' to have ';
        echo 'grade letter: ' . $letter . ' and grade Percent: ' . $percent . ' ';
        echo 'in record ' . $existing_record_for_user_id[$user_id] . '<BR>';
      }
      else {
        //insert a new record:
        $query = 'INSERT INTO course_grades(course_wwUserPermissions_id, course_gradeItem_id, gradeLetter, gradePercent) VALUES ("' . $user_id . '", "' . $grade_id . '", "' . $letter . '", "' . $percent . '")';
        $result = mysql_query($query, $con);

print "<BR>query is $query . <BR>";

        echo 'Inserted user ' . $username . ' with id ' . $user_id . ' and grade_id ' . $grade_id . ' ';
        echo ' with grade letter: ' . $letter . ' and percent: ' . $percent . '<BR>';
      }
    }
    //and update / insert the grade percent and grade item into the database.
    }
  }
  else if ($_REQUEST['type_of_input'] == 'individual_text_boxes') {
    echo '<B>process each student individually</B>';
    //for each course, 

    //get the grade item
    //get the usernames
    //and update / insert the grade percent and grade item into the database.
  }

}

//list the users for that course, and put in the grades for them:

else if (isset($_REQUEST['report_for_this_course']) && 
         (isset($_REQUEST['grade_item_id']) || isset($_REQUEST['new_grade_item']))) {
//echo '<pre>';
//print_r($_REQUEST);
//echo '</pre>';

  $report_on_these_courses = $_REQUEST['report_for_this_course'];

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $num_of_courses_to_report = count($report_on_these_courses);
  $user = array();
  for ($c = 0; $c < $num_of_courses_to_report; $c++) {
    $this_course = $report_on_these_courses[$c];
    echo 'get the list of students for course ' . $this_course . '<BR>';
    //get the list of students for the course:
    $user = get_users_from_course($con, $this_course, $user, false);
  }
  mysql_close($con);

  echo '<form method=\'post\'>';

  if (isset($_REQUEST['send'])) {
    foreach ($user as $course => $username_array) {
      echo '<fieldset><legend>Grades for Students in Course ' . $course . '</legend>';
      echo '<table><tr>
  	    <th>Username</th><th>Letter Grade</th><th>Percent</th>
            </tr>';
      foreach ($username_array as $username => $v) {
        echo '<tr><td>' . $username . '</td><td><input type=\'text\' name=\'grade_letter_' . $username . '_' . $course . '\' id=\'grade_letter_' . $username . '_' . $course . '\' /></td><td> <input type=\'text\' name=\'grade_percent_' . $username . '_' . $course . '\' id=\'grade_percent_' . $username . '_' . $course . '\' />%</td></tr>';
      }
      echo '</table>';
      echo '</fieldset>';

      echo "<input type='hidden' name='report_for_this_course[]' value='" . $course . "'>\n";
    }
    echo '<input type=\'hidden\' name=\'type_of_input\' value=\'individual_text_boxes\'>' . "\n";
  }
  else if (isset($_REQUEST['useTextfields'])) {
    foreach ($user as $course => $username_array) {
      echo '<fieldset><legend>Grades for Students in Course ' . $course . '</legend>';
      echo '<table><tr>
	    <th>Username</th><th>Letter Grade</th><th>Percent (xxx.xx%)</th>
            </tr>';
      echo '<tr><td></td><td colspan=\'2\'><center>Grade Item: <B>' . $_REQUEST['new_grade_item'] . '</B></center></td></tr>';
      $count_usernames = count($username_array) + 1;
      echo '<td><textarea id=\'grade_usernames_' . $course . '\' name=\'grade_usernames_' . $course . '\' rows=\'' . $count_usernames . '\' columns=\'20\'>';
      foreach ($username_array as $username => $v) {
        echo $username . "\n";
      }
      echo '</textarea>';
      echo '</td>';
      echo '<td><textarea id=\'grade_letter_' . $course . '\' name=\'grade_letter_' . $course . '\' rows=\'' . $count_usernames . '\' columns=\'3\'></textarea></td>';
      echo '<td><textarea id=\'grade_percent_' . $course . '\' name=\'grade_percent_' . $course . '\' rows=\'' . $count_usernames . '\' columns=\'5\'></textarea></td>';
      echo '</tr>';
      echo '</table>';
      echo '</fieldset>';

      echo '<input type=\'hidden\' name=\'report_for_this_course[]\' value=\'' . $course . '\'>' . "\n";
    }
    echo '<input type=\'hidden\' name=\'type_of_input\' value=\'as_textareas\'>' . "\n";
  }
  else { }

  if (isset($_REQUEST['new_grade_item']) && ($_REQUEST['new_grade_item'] != '')) {
    echo "<input type='hidden' name='new_grade_item' value='" . $_REQUEST['new_grade_item'] . "'>\n";
  }
  else if (isset($_REQUEST['grade_item_id'])) {
    echo "<input type='hidden' name='grade_item_id' value='" . $_REQUEST['grade_item'] . "'>\n";
  }
  else { }
  echo "<input type='hidden' name='record_grades' value='record_grades'>\n";
  echo '<input name=\'send\' type=\'submit\' id=\'send\' value=\'Get Students\'>
        </form>';
}
//list the existing grade items, or select a new one:
else if (isset($_REQUEST['report_for_this_course'])) {

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

//print_r($_REQUEST);
  $report_on_these_courses = $_REQUEST['report_for_this_course'];

  $grade_items_for_course = array();

  $num_of_courses_to_report = count($report_on_these_courses);
  for ($c = 0; $c < $num_of_courses_to_report; $c++) {
    $this_course = $report_on_these_courses[$c];
    $grade_items_for_course = get_grade_items_for_course($con, $this_course, $grade_items_for_course);
  }

  echo '
    <form method=\'post\'>
    ';

  echo '<fieldset>
	<legend>Select Grade Item for course <b>' . $this_course . '</b></legend>';

  foreach ($grade_items_for_course as $grade_item_id => $data) {
    print "<input name='grade_item_id' type='checkbox' value='" . $grade_item_id . "'>";
    print 'id: ' . $grade_item_id . '.  ' . $data['name'] . '<BR>';
    for ($c = 0; $c < $num_of_courses_to_report; $c++) {
      $course = $report_on_these_courses[$c];
      if ($data['course'][$course]) { print "*** $course"; }
      else { print " $course"; }
    }
  }
  echo '</fieldset>';
  echo '<fieldset>
        <legend>Create a new Grade Item for the courses</legend>';
  print "Item: <input type='text' name='new_grade_item' id='new_grade_item' /> for coarse(s): ";
  $comma = '';
  for ($c = 0; $c < $num_of_courses_to_report; $c++) {
    $course = $report_on_these_courses[$c];
    print "$comma  $course ";
    $comma = ",";
  }
  echo '</fieldset>';

  for ($c = 0; $c < $num_of_courses_to_report; $c++) {
    $course = $report_on_these_courses[$c];
    echo "<input type='hidden' name='report_for_this_course[]' value='" . $course . "'>\n";
  }

  echo '<input name=\'useTextfields\' type=\'submit\' id=\'useTextfields\' value=\'Use Big Textfield to List Students\'>
        <input name=\'send\' type=\'submit\' id=\'send\' value=\'Use Individual Textfields for each Student\'>
        </form>';

  mysql_close($con);
}
//Get the course:

else {
  $courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

//print_r($_REQUEST);

  echo '
    <form method=\'post\'>
    <fieldset>
    <legend>Select Courses</legend>';
  print_course_checkboxes($courses);
  echo '</fieldset>';

  echo '<input name=\'send\' type=\'submit\' id=\'send\' value=\'Get Grade Items\'>
    </form>';
}
?>
