<?php

include("access.php");

$db = "webwork";

if (isset($_REQUEST['Course']) && isset($_REQUEST['practiceStartDate']) && isset($_REQUEST['practiceDueDate']) && isset($_REQUEST['practiceAnswerDate']) && isset($_REQUEST['finalStartDate']) && isset($_REQUEST['finalDueDate']) && isset($_REQUEST['finalAnswerDate'])) {
  //for each user and quiz, create the practice and final quiz:

  $students = $_REQUEST['studentID'];
  $tests = $_REQUEST['setID'];

  $practiceStartDate = strtotime($_REQUEST['practiceStartDate']);
  $practiceDueDate = strtotime($_REQUEST['practiceDueDate']);
  $practiceAnswerDate = strtotime($_REQUEST['practiceAnswerDate']);

  $finalStartDate = strtotime($_REQUEST['finalStartDate']);
  $finalDueDate = strtotime($_REQUEST['finalDueDate']);
  $finalAnswerDate = strtotime($_REQUEST['finalAnswerDate']);

  print "practice startDate " . $_REQUEST['practiceStartDate'] . " is " . strtotime($_REQUEST['practiceStartDate']) . "<BR>";
  print "practice DueDate " . $_REQUEST['practiceDueDate'] . " is " . strtotime($_REQUEST['practiceDueDate']) . "<BR>";
  print "practice AnswerDate " . $_REQUEST['practiceAnswerDate'] . " is " . strtotime($_REQUEST['practiceAnswerDate']) . "<BR>";

  print "final startDate " . $_REQUEST['finalStartDate'] . " is " . strtotime($_REQUEST['finalStartDate']) . "<BR>";
  print "final DueDate " . $_REQUEST['finalDueDate'] . " is " . strtotime($_REQUEST['finalDueDate']) . "<BR>";
  print "final AnswerDate " . $_REQUEST['finalAnswerDate'] . " is " . strtotime($_REQUEST['finalAnswerDate']) . "<BR>";


  $course = $_REQUEST['Course'];
  $course = preg_replace('/\s*/', '', $course);

  for ($i = 0; $i < count($students); $i++) {
    for ($j = 0; $j < count($tests); $j++) {
      $thisStudent = $students[$i];
      $thisTest = $tests[$j];
      $resultOfPhp = `php /var/www/html/connecting/workWithWWDB/createPracticeAndFinalQuiz.php $course $thisStudent $thisTest $practiceStartDate $practiceDueDate $practiceAnswerDate $finalStartDate $finalDueDate $finalAnswerDate`;
      print "php#createPracticeAndFinalQuiz.php#$course#$thisStudent#$thisTest#$practiceStartDate#$practiceDueDate#$practiceAnswerDate#$finalStartDate#$finalDueDate#$finalAnswerDate#.....><PRE>$resultOfPhp</PRE><BR>";
    }
  }
}
else if (isset($_REQUEST['Course'])) {
  //ask the user to select users, quizzes, and enter dates:
  print "<BR>Selected Course: " . $_REQUEST['Course'] . "\n<BR>";

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $result = mysql_select_db("$db", $con);

  $course = $_REQUEST['Course'];
  $user_table = $course . "_user";
  $query = "SELECT user_id, first_name, last_name, section, recitation, status FROM `" . $user_table . "` WHERE 1";
  $result = mysql_query($query, $con);

$resulta  = "query: $query <BR> result: $result\n";

  $userList = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $userList .= "<TR><TD><input type='checkbox' name='studentID[]' id='studentID[]' value='" . $row['user_id'] . "'/></TD>";
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
<BR>Results are:
<?php
print $resulta;
?>
<HR>
<BR>
<input name="Course" value="
<?php
print $course;
?>
" type="hidden">
<TABLE BORDER=1>
<TR><TD>Assign</TD><TD>Set ID</TD></TR>
<?php
print $testList;
?>

<TABLE BORDER=1>
<TR><TD>Practice Set Available:</TD><TD><INPUT TYPE="text" NAME="practiceStartDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Practice Set Due:</TD><TD><INPUT TYPE="text" NAME="practiceDueDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Practice Set Answers Available:</TD><TD><INPUT TYPE="text" NAME="practiceAnswerDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD></TD><TD></TD><TD></TD></TR>
<TR><TD>Final Quiz Set Available:</TD><TD><INPUT TYPE="text" NAME="finalStartDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Final Quiz Set Due:</TD><TD><INPUT TYPE="text" NAME="finalDueDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Final Quiz Set Answers Available:</TD><TD><INPUT TYPE="text" NAME="finalAnswerDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
</TABLE>

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
//ask the user for the webwork course:
  //get the list of courses from /opt/webwork/courses
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
?>
