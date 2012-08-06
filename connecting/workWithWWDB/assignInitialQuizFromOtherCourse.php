<?php

include("access.php");

$db = "webwork";

if (isset($_REQUEST['CourseWithStudents']) && isset($_REQUEST['CourseWithQuizzes']) && isset($_REQUEST['finalStartDate']) && isset($_REQUEST['finalDueDate']) && isset($_REQUEST['finalAnswerDate'])) {
  //for each user and quiz, create the practice and final quiz:

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $result = mysql_select_db("$db", $con);


  $students = $_REQUEST['studentID'];
  $tests = $_REQUEST['setID'];

  $finalStartDate = strtotime($_REQUEST['finalStartDate']);
  $finalDueDate = strtotime($_REQUEST['finalDueDate']);
  $finalAnswerDate = strtotime($_REQUEST['finalAnswerDate']);

  print "final startDate " . $_REQUEST['finalStartDate'] . " is " . strtotime($_REQUEST['finalStartDate']) . "<BR>";
  print "final DueDate " . $_REQUEST['finalDueDate'] . " is " . strtotime($_REQUEST['finalDueDate']) . "<BR>";
  print "final AnswerDate " . $_REQUEST['finalAnswerDate'] . " is " . strtotime($_REQUEST['finalAnswerDate']) . "<BR>";


  $courseWithQuizzes = $_REQUEST['CourseWithQuizzes'];
  $courseWithStudents = $_REQUEST['CourseWithStudents'];
  $courseWithQuizzes = preg_replace('/\s*/', '', $courseWithQuizzes);
  $courseWithStudents = preg_replace('/\s*/', '', $courseWithStudents);

//ADW:  Fix this logic!
  //make sure the quizzes exist in the $courseWithStudents_set table.  If not, add them
  $quiz_is_present = array();
  for ($j = 0; $j < count($tests); $j++) {
    $thisQuiz = $tests[$j];
    $query = "SELECT * FROM `" . $courseWithStudents . "_set` WHERE set_id='$thisQuiz'";
print "A: query is $query<BR>";
    $quiz_is_present[$thisQuiz] = 0;
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $quiz_is_present[$thisQuiz] = 1;
print "        quiz was present.";
    }
  }

  //assign the quizzes:
  for ($j = 0; $j < count($tests); $j++) {

    $thisQuiz = $tests[$j];

    //If the quiz is not there....
    if ($quiz_is_present[$thisQuiz] == 0) {
      $query = "INSERT INTO `" . $courseWithStudents . "_set` SELECT * FROM `" . $courseWithQuizzes . "_set` WHERE set_id='" . $thisQuiz . "'";
print "B:  query is $query<BR>";
      $result = mysql_query($query, $con);
print "        Result is $result.<BR>";
print "        Error is " . mysql_error() . "<BR>";
      $query = "UPDATE `" . $courseWithStudents . "_set` SET open_date=" . $finalStartDate . ", due_date=" . $finalDueDate . ", answer_date=" . $finalAnswerDate . " WHERE set_id='$thisQuiz'";
print "C:  query is $query<BR>";
      $result = mysql_query($query, $con); 
print "     result is $result.<BR>";
print "     error is " . mysql_error() . "<BR>";

      //now, update the studentCourse_problem entries to make sure it has the necessary problems for the quiz.
      $query = "INSERT INTO `" . $courseWithStudents . "_problem` SELECT * FROM `" . $courseWithQuizzes . "_problem` WHERE set_id='" . $thisQuiz . "'";
      $result = mysql_query($query, $con);
print "D:  query is $query<BR>";
print "     result is $result.<BR>";
print "     error is " . mysql_error() . "<BR>";


      //now, get the concept banks for that particular quiz:
      $query = "SELECT source_file FROM `" . $courseWithStudents . "_problem` WHERE set_id='" . $thisQuiz . "'";
      $result = mysql_query($query, $con);

print "E:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";

      $conceptBanks = array();
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $conceptBanks[] = preg_replace('/group\:/', '', $row['source_file']);
      }

      //for each concept in that bank,
      //populate the $courseWithStudents_problem with the sourcefiles from that conceptBank
      //(we also need to add "$courseWithQuizzes/" to the start of each sourcefile.
      for ($k = 0; $k < count($conceptBanks); $k++) {

        $thisConceptBank = $conceptBanks[$k];

        $query = "INSERT INTO `" . $courseWithStudents . "_problem` SELECT * FROM `" . $courseWithQuizzes . "_problem` WHERE set_id='" . $thisConceptBank . "'";
        $result = mysql_query($query, $con);

print "F:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";


        //now, update the source_file to make sure it points to the courseWithQuizzes course template directory.
        $query = "UPDATE `" . $courseWithStudents . "_problem` set source_file=concat('" . $courseWithQuizzes . "/', source_file) WHERE set_id='" . $thisConceptBank . "'";      
        $result = mysql_query($query, $con);

print "G:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";

        //also, add $thisConceptBank info to the $courseWithQuizzes_set table:
        $query = "INSERT INTO `" . $courseWithStudents . "_set` SELECT * FROM `" . $courseWithQuizzes . "_set` WHERE set_id='" . $thisConceptBank . "'";
        $result = mysql_query($query, $con);

print "H:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";

      }
    }

    //get the problem_id numbers from $courseWithStudents_problem for $thisQuiz.
    $problem_ids = array();
    $query = "SELECT problem_id FROM `" . $courseWithStudents . "_problem` WHERE set_id='" . $thisQuiz . "'";
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $problem_ids[] = $row['problem_id'];
    }
print "I:  query is $query<BR>";
print "      result is these problem_ids:.<BR>";
print_r($problem_ids);
print "<BR>";
print "      error is " . mysql_error() . "<BR>";


    //assign this quiz to the particular student
    for ($i = 0; $i < count($students); $i++) {
      $studentID = $students[$i];
      //put the entry into $courseWithStudents_set_user
      $set_user_table = $courseWithStudents . "_set_user";
      $query =  "INSERT INTO $set_user_table (user_id, set_id) VALUES('$studentID', '$thisQuiz')";
//do we need to do something with psvn?
      $result = mysql_query($query, $con);
print "J:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";

      for ($m = 0; $m < count($problem_ids); $m++) {
        $this_problem_id = $problem_ids[$m];
        $this_problem_seed = rand(1,9999);
        $query = "INSERT INTO `" . $courseWithStudents . "_problem_user` (user_id, set_id, problem_id, problem_seed, status, attempted, num_correct, num_incorrect) VALUES('$studentID', '$thisQuiz', $this_problem_id, $this_problem_seed, 0, 0, 0, 0)";
        $result = mysql_query($query, $con);
print "K:  query is $query<BR>";
print "      result is $result.<BR>";
print "      error is " . mysql_error() . "<BR>";

      }
    }
  }

  mysql_close($con);


//  $result = mysql_query($query, $con);

//  $resulta  = "query: $query <BR> result: $result\n";

//  $userList = "";
//  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
//    $userList .= "<TR><TD><input type='checkbox' name='studentID[]' id='studentID[]' value='" . $row['use
//TD>";
//    $userList .= "<TD>" . $row['user_id'] . "</TD><TD>" . $row['first_name'] . " " . $row['last_name'] .
//    $userList .= "<TD>" . $row['section'] . "</TD><TD>" . $row['recitation'] . "</TD>";
//    $userList .= "<TD>" . $row['status'] . "</TD></TR>\n";
//  }
//    
//  }

//  for ($i = 0; $i < count($students); $i++) {
//    for ($j = 0; $j < count($tests); $j++) {
//      $thisStudent = $students[$i];
//      $thisTest = $tests[$j];
//      $resultOfPhp = `php /var/www/html/connecting/workWithWWDB/createPracticeAndFinalQuiz.php $course $thisStudent $thisTest $practiceStartDate $practiceDueDate $practiceAnswerDate $finalStartDate $finalDueDate $finalAnswerDate`;
//      print "php#createPracticeAndFinalQuiz.php#$course#$thisStudent#$thisTest#$practiceStartDate#$practiceDueDate#$practiceAnswerDate#$finalStartDate#$finalDueDate#$finalAnswerDate#.....><PRE>$resultOfPhp</PRE><BR>";
//    }
//  }
}


//do we have the quiz course and the student course?

else if (isset($_REQUEST['CourseWithQuizzes']) && isset($_REQUEST['CourseWithStudents'])) {
  //ask the user to select users, quizzes, and enter dates:
  print "<BR>Selected Course with students: " . $_REQUEST['CourseWithStudents'] . "\n<BR>";
  print "<BR>assigning quizzes from course: " . $_REQUEST['CourseWithQuizzes'] . "\n<BR>";

  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $result = mysql_select_db("$db", $con);

  $courseWithQuizzes = $_REQUEST['CourseWithQuizzes'];
  $courseWithStudents = $_REQUEST['CourseWithStudents'];

  //get the users from the courseWithStudents course
  $user_table = $courseWithStudents . "_user";
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


  //get the quizzes from the courseWithQuizzes course
  $set_table = $courseWithQuizzes . "_set";
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
<input name="CourseWithStudents" value="
<?php
print $courseWithStudents;
?>
" type="hidden">
<input name="CourseWithQuizzes" value="
<?php
print $courseWithQuizzes;
?>
" type="hidden">
<TABLE BORDER=1>
<TR><TD>Assign</TD><TD>Set ID</TD></TR>
<?php
print $testList;
?>

<TABLE BORDER=1>
<TR><TD>Quiz Set Available:</TD><TD><INPUT TYPE="text" NAME="finalStartDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Quiz Set Due:</TD><TD><INPUT TYPE="text" NAME="finalDueDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
<TR><TD>Quiz Set Answers Available:</TD><TD><INPUT TYPE="text" NAME="finalAnswerDate"></TD><TD>yyyy-mm-dd hh:mm:ss</TD></TR>
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

//get the quiz course and the student course



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
Use this script to assign quizzes designed in one course to students in another course.<BR>
What is the course that contains the students?
<select name="CourseWithStudents">
<?php
  for ($i = 0; $i < count($courseList); $i++) {
    print '<option value="' . $courseList[$i] . '">' . $courseList[$i] . '</option>\n'; 
  }
?>
</select>
<P>
What is the course that contains the quizes?
<select name="CourseWithQuizzes">
<?php
  for ($i = 0; $i < count($courseList); $i++) {
    print '<option value="' . $courseList[$i] . '">' . $courseList[$i] . '</option>\n';
  }
?>
</select>
<P>
<input type="submit" name="Submit" value="Submit"><BR>
</form>
<?php
}
?>
