<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "Error selecting database $db: " . mysql_error();
}

$splitter = $_REQUEST['splitter'];

$user_id = $_REQUEST['user_id'];
//$course_id = $_REQUEST['course_id'];
$wwCourseName = $_REQUEST['wwCourseName'];
$wwSetName = $_REQUEST['wwSetName'];
$wwProblemNumber = $_REQUEST['wwProblemNumber'];
$problem_id = $_REQUEST['problem_id'];

$recordDrawData = $_REQUEST['recordDrawData'];

//Now, all the data has been separated!

$ret_query = "";

$queries = "";

//If problem_id is -1, then we need to make a new record for this draw data.
if ($problem_id == -1) {

  //get the course_id from the session course table:
  $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
  $result = mysql_query($query, $con);

  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['course_id'];
  }

  //create the new drawing record:
  $query = "INSERT INTO draw (name) VALUE ('work submitted by " . $user_id . "')";
  $result = mysql_query($query, $con);

  $draw_id = mysql_insert_id();

  //write the contents of $recordDrawData:
  if (strlen($recordDrawData)) {
    $tmpfname = tempnam("/opt/session/drawings", "draw" . $draw_id . "_");

    $handle = fopen($tmpfname, "w");
    fwrite($handle, $recordDrawData);
    fclose($handle);

    //insert the drawing information into the draw table:
    $query = "UPDATE draw SET filename=" . check_input($tmpfname) . " WHERE draw_id=$draw_id";
    $result = mysql_query($query, $con);
  }

  //create the new problem record:
  $query = "INSERT INTO problem (name, type, initial_draw_id) VALUES ('webwork work for course " . $wwCourseName . " and set " . $wwSetName . " and problem " . $wwProblemNumber . " by user " . $user_id . "', 'webwork work', $draw_id)";
  $result = mysql_query($query, $con);
  $problem_id = mysql_insert_id();

  //create the new wwStudentWorkForProblem record:
  $query = "INSERT INTO wwStudentWorkForProblem (course_id, user_id, ww_set_id, ww_problem_number, problem_id) VALUES ($course_id, $user_id, '" . $wwSetName . "', $wwProblemNumber, $problem_id)";
  $result = mysql_query($query, $con);

  //give back the new problem_id, so that a subsequent "submit" can update, not create a new, problem record.
  print "<Results>\n";
  print $problem_id > 0 ? "  <Code>Success</Code>\n" : "  <Code>Failure</Code>\n";
  print "  <query>$query</query>\n";
  print "  <Problem_ID>$problem_id</Problem_ID>\n";
  print "</Results>\n";
}
else {
  //Otherwise, a record exists and we need to
  //create a new drawing record and 
  //update the pointer in the previous drawing record.

  //get the course_id from the session course table:
  $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
  $result = mysql_query($query, $con);

  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['course_id'];
  }


  //get the record from wwStudentWorkForProblem, including the problem_id
  $query = 'SELECT problem_id FROM `wwStudentWorkForProblem` WHERE course_id=' . $course_id . ' AND user_id=' . $user_id . ' AND ww_set_id="' . $wwSetName . '" AND ww_problem_number=' . $wwProblemNumber;
  $result = mysql_query($query, $con);

  $problem_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problem_id = $row['problem_id'];
  }

  //get the draw_id from the record in the problem table
  $query = 'SELECT initial_draw_id FROM `problem` WHERE problem_id=' . $problem_id;
  $result = mysql_query($query, $con);

  $initial_draw_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $initial_draw_id = $row['initial_draw_id'];
  }

  //walk through the draw records until we come to a record with a blank next drawing field.
  $previous_draw_id = -1;
  $default_next_draw_id = $initial_draw_id;
  $count = 1;
  $showThis = "initial_draw_id = $initial_draw_id and default_next_draw_id = $default_next_draw_id";

  while (($count < 100) && ($default_next_draw_id != "")) {
    $count++;
    $previous_draw_id = $default_next_draw_id;
    $query = 'SELECT default_next_draw_id FROM `draw` WHERE draw_id=' . $default_next_draw_id;
    $result = mysql_query($query, $con);

    $queries .= "<query" . $count . ">$query</query" . $count . ">\n";

    $default_next_draw_id = -1;
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $default_next_draw_id = $row['default_next_draw_id'];
    }
  }

  //the previous_draw_id is $previous_draw_id.
  //write the contents of $recordDrawData:
  
  //create the new drawing record:
  $query = "INSERT INTO draw (name) VALUE ('work submitted by " . $user_id . "')";
  $result = mysql_query($query, $con);

  $draw_id = mysql_insert_id();

  //write the contents of $recordDrawData:
  if (strlen($recordDrawData)) {
    $tmpfname = tempnam("/opt/session/drawings", "draw" . $draw_id . "_");

    $handle = fopen($tmpfname, "w");
    fwrite($handle, $recordDrawData);
    fclose($handle);

    //insert the drawing information into the draw table:
    $query = "UPDATE draw SET filename=" . check_input($tmpfname) . " WHERE draw_id=$draw_id";
    $result = mysql_query($query, $con);
  }

  //update the pointer in the previous drawing record to point to the new drawing record:
  $query = 'UPDATE draw SET default_next_draw_id=' . $draw_id . ' WHERE draw_id=' . $previous_draw_id;
  $result = mysql_query($query, $con);

  mysql_close($con);

  print "<Results>\n";
  print $problem_id > 0 ? "  <Code>Success</Code>\n" : "<Code>Failure</Code>\n";
  print "  <query>$query</query>\n";
  print "  <queries>$queries</queries>\n";
  print "  <showThis>$showThis</showThis>\n";
  print "  <Problem_ID>$problem_id</Problem_ID>\n";
  print "</Results>\n";
}

?>
