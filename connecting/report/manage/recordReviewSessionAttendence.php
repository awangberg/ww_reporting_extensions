<?php

include("../../access.php");

if (isset($_REQUEST['quizID']) && isset($_REQUEST['Submit']) && isset($_REQUEST['studentID']) && isset($_REQUEST['Course']) && isset($_REQUEST['dateOfReviewSession']) && isset($_REQUEST['lengthOfReviewSession'])) {

  $con = mysql_connect($db_host, $db_user, $db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }


  //select the database wwSession:
  $db = "wwSession";
  $result = mysql_select_db("$db", $con);

  //Insert the data into the table attendedReviewSession
  $course = $_REQUEST['Course'];
  $students = $_REQUEST['studentID'];
  $quizzes = $_REQUEST['quizID'];
  $durationSec = 60*$_REQUEST['lengthOfReviewSession'];
  $theDate = $_REQUEST['dateOfReviewSession'];

  //record that these users were at this review session:
  for ($s = 0; $s < count($students); $s++) {
    for ($q = 0; $q < count($quizzes); $q++) {
      $query  = "INSERT INTO attendedReviewSession (course_name, user_name, quizName, date_attended, timeSpentOnReviewInSeconds) ";
      $query .= " VALUES ('"
                       . $course
                       . "', '"
                       . $students[$s]
                       . "', '"
                       . $quizzes[$q]
                       . "', '"
                       . $theDate
                       . "', "
                       . $durationSec
                       . ")";

      //add this user:
      $result = mysql_query($query, $con);

      print "<P>$query</P>";

      $str_result .= "Adding " . $student[$s] . " for quiz " . $quizzes[$q] . " for review session for course " . $course . "..... $result<BR>\n";
    }
  }
  print $str_result;

  //close connection
  mysql_close($con);

}
else if (isset($_REQUEST['Submit'])) {
  //get the users from the webwork database, and 
  //allow the user to put in a time for each user:

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $course = $_REQUEST['Course'];

  $db = "webwork";
  $result = mysql_select_db("$db", $con);

  $query = 'SELECT user_id, first_name, last_name, section, recitation FROM `' . $course . '_user` ORDER BY last_name';
  $result = mysql_query($query, $con);

  $userList = "";
  print "query is $query";
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $userList .= "<TR><TD><input type='checkbox' name='studentID[]' id='studentID[]' value='" . $row['user_id'] . "'/></TD>";
    $userList .= "<TD>" . $row['user_id'] . "</TD><TD>" . $row['first_name'] . " " . $row['last_name'] . "</TD>";
    $userList .= "<TD>" . $row['section'] . "</TD><TD>" . $row['recitation'] . "</TD>";
  }

  $quizzes = array("Basics", "Graphs", "LinearRational", "ExpLog", "Trig");

  $quizList = "";
  for ($q = 0; $q < count($quizzes); $q++) {
    $quizList .= "<TR><TD><input type='checkbox' name='quizID[]' id='quizID[]' value='" . $quizzes[$q] . "'/></TD>";
    $quizList .= "<TD>" . $quizzes[$q] . "</TD></TR>";
  }
  mysql_close($con);
?>
<HTML>
<BODY>
<H2>Provide information about the participants in the review session</H2>
<form method="post" action="">
<?php
print "<input name='Course' value='" . $course . "' type='hidden'>";
?>
<TABLE>
<TR><TD>Select</TD><TD>Review Content</TD></TR>
<?php
print $quizList;
?>
</TABLE>

<TABLE>
<TR><TD>Date of Review Session:</TD><TD><INPUT TYPE="text" NAME="dateOfReviewSession"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Length of Review Session:</TD><TD><INPUT TYPE="text" NAME="lengthOfReviewSession"></TD><TD>(In minutes)</TD></TR>
</TABLE>

<TABLE>
<TR><TD>Was Present</TD><TD>UserID</TD><TD>Name</TD><TD>Section</TD><TD>Recitation</TD></TR>
<?php
print $userList;
?>
</TABLE>
<input type="submit" name="Submit" value="Submit"><BR>
</form>

<?php

}
else {

  $con = mysql_connect($db_host, $db_user, $db_pass);

  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "session";

  $result = mysql_select_db("$db", $con);

  $query = 'SELECT course_name, section_name, user.first_name, user.last_name FROM `course` '
         . ' LEFT JOIN `user` ON course.instructor_id = user.user_id';

  $result = mysql_query($query, $con);

  $available_courses = "<select name='Course'>";

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $available_courses .= "<option value='" . $row['course_name'] . "'>" . $row['course_name'] . " (" . $row['section_name'] . " " . $row['first_name'] . " " . $row['last_name'] . ")</option>\n";
  }
  $available_courses .= "</select>\n";
  mysql_close($con);

  // DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

?>
<form method="post" action="">
Select A Course:
<input type="submit" name="Submit" value="Submit"> <BR>
<?
print $available_courses;
print $query;
?>
</form>

<?php
} 



?>
