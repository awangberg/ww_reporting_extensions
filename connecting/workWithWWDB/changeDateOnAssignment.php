<?php

include("access.php");

$db = "webwork";

if (isset($_REQUEST['Course']) && isset($_REQUEST['practiceStartDate']) && isset($_REQUEST['practiceDueDate']) && isset($_REQUEST['practiceAnswerDate']) && isset($_REQUEST['quizStartDate']) && isset($_REQUEST['quizDueDate']) && isset($_REQUEST['quizAnswerDate'])) {

  //get the students and assignment or test:
  $students = $_REQUEST['studentID'];
  $tests = $_REQUEST['setID'];

  $newPracticeStartDate  = strtotime($_REQUEST['practiceStartDate']);
  $newPracticeDueDate    = strtotime($_REQUEST['practiceDueDate']);
  $newPracticeAnswerDate = strtotime($_REQUEST['practiceAnswerDate']);

  $newQuizStartDate  = strtotime($_REQUEST['quizStartDate']);
  $newQuizDueDate    = strtotime($_REQUEST['quizDueDate']);
  $newQuizAnswerDate = strtotime($_REQUEST['quizAnswerDate']);

  $course = $_REQUEST['Course'];
  $course = preg_replace('/\s*/', '', $course);

  $assignment = $_REQUEST['assignment'];

  $this_assignment = "";

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $result = mysql_select_db("$db", $con);

  for ($i = 0; $i < count($students); $i++) {
    for ($j = 0; $j < count($tests); $j++) {
      $this_student = $students[$i];
      $this_quiz = $tests[$j];
      //Make $this_assignment the custom assignment for this student:
      //$this_assignment = preg_replace('/\s*/', '', $assignment);
      //$this_assignment = preg_replace('/\_USERNAME\_/', $this_student, $this_assigment);

      $this_practice_assignment = "practice_" . $this_student . "_" . $this_quiz; 

      //Change the old date to the new date for the assignment / quiz $tests[$j]
      //Change the dates for the practice set:
      $query = "UPDATE `" . $course . "_set` SET open_date='" . $newPracticeStartDate . "', due_date='" . $newPracticeDueDate . "', answer_date='" . $newPracticeAnswerDate . "' WHERE set_id='" . $this_practice_assignment . "'";
      $result = mysql_query($query, $con);

      $this_quiz_assignment = "finalQuiz_" . $this_student . "_" . $this_quiz;

      //Change the dates for the final set:
      $query = "UPDATE `" . $course . "_set` SET open_date='" . $newQuizStartDate . "', due_date='" . $newQuizDueDate . "', answer_date='" . $newQuizAnswerDate . "' WHERE set_id='" . $this_quiz_assignment . "'";
      $result = mysql_query($query, $con);

      print "<BR>Changed dates for $this_practice_assignment and $this_quiz_assignment";
    }
  }
  mysql_close($con);
}

else if (isset($_REQUEST['Course'])) {
  //ask the user to select users, quizzes, homework assignments, and enter dates:
  print "<BR>Selected Course: " . $_REQUEST['Course'] . "\n<BR>";

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $result = mysql_select_db("$db", $con);

  $course = $_REQUEST['Course'];
  $user_table = $course . "_user";

  $query = "SELECT user_id, first_name, last_name, section, recitation, status FROM `" . $user_table . "` WHERE 1";
  $result = mysql_query($query, $con);

  $userList = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $userList .= "<TR><TD><input type='checkbox' name='studentID[]' value='" . $row['user_id'] . "'/></TD>";
    $userList .= "<TD>" . $row['user_id'] . "</TD><TD>" . $row['first_name'] . " " . $row['last_name'] . "</TD>";
    $userList .= "<TD>" . $row['section'] . "</TD><TD>" . $row['recitation'] . "</TD>";
    $userList .= "<TD>" . $row['status'] . "</TD></TR>\n";
  }

  $set_table = $course . "_set";
$query = "SELECT set_id FROM `" . $set_table . "` WHERE assignment_type='gateway'";
  $result = mysql_query($query, $con);
  $testList = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $testList .= "<TR><TD><input type='checkbox' name='setID[]' id='setID[]' value='" . $row['set_id'] . "'/></TD>";
    $testList .= "<TD>" . $row['set_id'] . "</TD></TR>";
  }

  mysql_close($con);
?>
<form method="post" action="">
<input name="Course" value="
<?php
print $course;
?>
" type="hidden">
<BR>
<TABLE BORDER=1>
<TR><TD>Assign</TD></TD>Set ID</TD></TR>
<?php
print $testList;
?>

<BR>
Use _USERNAME_ to indicate username in assignment name.
<TABLE BORDER=1>
<TR><TD>Set ID</TD><TD>Specific Date</TD><TD>New Date</TD></TR>
<TR><TD><INPUT TYPE="text" NAME="assignment"></TD><TD>Practice Set Available</TD><TD><INPUT TYPE="text" NAME="practiceStartDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD></TD><TD>Practice Set Due</TD><TD><INPUT TYPE="text" NAME="practiceDueDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD></TD><TD>Practice Answers Available</TD><TD><INPUT TYPE="text" NAME="practiceAnswerDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD></TD><TD>Final Quiz Available</TD><TD><INPUT TYPE="text" NAME="quizStartDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD></TD><TD>Final Quiz Due</TD><TD><INPUT TYPE="text" NAME="quizDueDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD></TD><TD>Final Quiz Answers Available</TD><TD><INPUT TYPE="text" NAME="quizAnswerDate"> (yyyy-mm-dd hh:mm:ss)</TD></TR>
</TABLE>
<BR>
<TABLE BORDER=1>
<TR><TD>To</TD><TD>UserID</TD><TD>Name</TD><TD>Section</TD><TD>Recitation</TD><TD>Status</TD></TR>
<?php
print $userList;
?>
</TABLE>
<input type="submit" name="Submit" value="Submit"<BR>
</form>
<?php

}
else {

  //ask the user for the webwork course
  //get the list of courses from  /opt/webwork/courses
  $tmpCourses = `ls /opt/webwork/courses`;
  $courseList = preg_split('/\s+/', $tmpCourses);
?>

<form method="post" action="">
<BR>
<HR>
<BR>
Select Class:
<select name="Course">
<?php
  for ($i = 0; $i < count($courseList); $i++) {
    print '<option value="' . $courseList[$i] . '">' . $courseList[$i] . '</option>\n';
  }
?>
</select>
<input type="submit" name="Submit" value="Submit"<BR>
</form>
<?php
}

