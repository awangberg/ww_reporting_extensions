<?php

include("access.php");

header("content-type: text/xml");

$wwCourseName = $_REQUEST['wwCourseName'];
$wwUserName = $_REQUEST['wwUserName'];
$wwEffectiveUserName = $_REQUEST['wwEffectiveUserName'];
$wwSetName = $_REQUEST['wwSetName'];
$wwProblemNumber = $_REQUEST['wwProblemNumber'];
//$wwMode = $_REQUEST['mode'];

//$problemTable = $wwCourseName . "_problem";

$reportQuery = "";
$query_error = "";

//Get the concept bank associated with this set and problem
//using the wwSession userConceptBanks table:

$con = mysql_connect($db_host, $db_user, $db_pass);
$db = 'wwSession';

//select the database $db:
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else{
  echo "1.  Error selecting database $db: " . mysql_error();
}

//select pg_sourcefile and concept bank for this problem
$query = 'SELECT pg_sourcefile, concept_bank FROM `usersConceptBanks` WHERE course_name="' . $wwCourseName . '" AND user_name="' . $wwUserName . '" AND webwork_practice_set="' . $wwSetName . '" AND webwork_problem_set_number=' . $wwProblemNumber;
$pg_sourcefile = "";
$conceptBank = "";
$result = mysql_query($query, $con);

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $pg_sourcefile = $row['pg_sourcefile'];
  $conceptBank = $row['concept_bank'];
}
$reportQuery .= "<query1>$query</query1>\n";
$reportQuery .= "<query1Results>\n<pg_sourcefile>$pg_sourcefile</pg_sourcefile>\n<conceptBank>$conceptBank</conceptBank>\n</query1Results>\n";


//pg_sourcefile and conceptBank and stored in $pg_sourcefile and $conceptBank
//Now, get the tutorials associated with this conceptBank and/or pg_sourcefile
$db = $_REQUEST['userDatabaseName'];

//select the database $db:
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "2.  Error selecting database $db: " . mysql_error();
}

$tutorialCourse = "SessionExperiment";

$query = 'SELECT ww_set_and_course_id FROM `wwSetInSession` WHERE ww_course_name="' . $tutorialCourse . '" AND ww_set_id="' . $conceptBank . '"';
$ww_set_and_course_id = -1;
$result = mysql_query($query, $con);

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $ww_set_and_course_id = $row['ww_set_and_course_id'];
}
$reportQuery .= "<query2>$query</query2>\n";
$reportQuery .= "<query2Results>\n<ww_set_and_course_id>$ww_set_and_course_id</ww_set_and_course_id>\n</query2Results>\n";

//$ww_set_and_course_id contains the set and course for the conceptBank.
//Now, get the tutorial associated with that conceptBank.

$query = 'SELECT session_problem_id FROM `wwSetToSessionTutorial` WHERE ww_set_and_course_id=' . $ww_set_and_course_id . ' AND valid_tutorial="YES"';
$result = mysql_query($query, $con);

$tutorial_problem_ids = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $tutorial_problem_ids[] = $row['session_problem_id'];
}

$reportQuery .= "<query3>$query</query3>\n";
$reportQuery .= "<query3Results>\n<tutorial_problem_ids>";
foreach ($tutorial_problem_ids as $i => $val) {
  $reportQuery .= "<tutorial_problem_id>$val</tutorial_problem_id>\n";
}
$reportQuery .= "</tutorial_problem_ids>\n</query3Results>\n";


$return_string = "";
if (count($tutorial_problem_ids) == 0) {
  //No tutorials exist.
  $return_string  = "<Results>\n";
  $return_string .= "  <Code>LOGGED_IN</Code>\n";
  $return_string .= "  <Tutorial_exists>false</Tutorial_exists>\n";
  $return_string .= $reportQuery;
  $return_string .= "</Results>\n";
}
else {
  //One or more tutorials exist.
  //Get the name of each tutorial.
  //How many times has the student seen each tutorial?
  //Have they seen the t


  $return_string  = "<Results>\n";
  $return_string .= "  <Code>LOGGED_IN</Code>\n";
  $return_string .= "  <Tutorial_exists>true</Tutorial_exists>\n";
  $return_string .= "  <Num_of_possible_tutorials>" . count($tutorial_problem_ids) . "</Num_of_possible_tutorials>\n";
  $return_string .= "  <Tutorials>\n";

  //is the student in the Session database?
  //if so, find their ID and use it to check if they've viewed relevant tutorials
  //if not, just report they haven't viewed the tutorials.

  $student_in_system = 0;
  $query = 'SELECT user_id course.course_id FROM `user` LEFT JOIN `course` ON user.course_id = course.course_id WHERE user_name="' . $wwEffectiveUserName . '" AND course_name="' . $wwCourseName . '"';
  $user_id = -1;
  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $user_id = $row['user_id'];
    $course_id = $row['course_id'];
  }

  $user_in_system = ($user_id == -1) ? 0 : 1;

  foreach ($tutorial_problem_ids as $i => $tutorial_problem_id) {
    //go to the Session database
    $db = $_REQUEST['userDatabaseName'];

    //select the database $db:
    if (mysql_select_db("$db", $con)) {
      //echo "selected database $db";
    }
    else {
      echo "10. Error selecting database $db: " . mysql_error();
    }    

    //get the name of the tutorial
    $query = 'SELECT name FROM `problem` WHERE problem_id=' . $tutorial_problem_id;

    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $tutorial_name = $row['name'];
    }
    
    //go to the wwSession database
    $db = 'wwSession';
    if (mysql_select_db("$db", $con)) {
      //echo "selected database $db";
    }
    else {
      echo "11. Error selecting database $db: " . mysql_error();
    }


    //get the last visit, and number of visits, to this tutorial for the student:
    $query = 'SELECT id, date_viewed FROM `sawTutorialForConceptBank` WHERE course_name="' . $wwCourseName . '" AND user_name="' . $wwEffectiveUserName . '" AND session_problem_id=' . $tutorial_problem_id;
    $result = mysql_query($query, $con);
    $visits = 0;
    $last_visit = -1;
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $visits++;
      if ($visits == 1) { $last_visit = $row['date_viewed']; }
      $last_visit = (strtotime($last_visit.' + 0 hours') > strtotime($row['date_viewed'].' + 0 hours')) ? $last_visit : $row['date_viewed'];
    }    

    
    $return_string .= "    <Tutorial>\n";
    $return_string .= "      <Title>$tutorial_name</Title>\n";
    $return_string .= "      <ID>$tutorial_problem_id</ID>\n";
    $return_string .= "      <Num_of_views>$visits</Num_of_views>\n";
    $return_string .= "      <Last_view>$last_visit</Last_view>\n";
    $return_string .= "    </Tutorial>\n";
  }
  $return_string .= "  </Tutorials>\n";
  $return_string .= $reportQuery;
  $return_string .= "</Results>\n";
}

mysql_close($con);

print $return_string;

?>
