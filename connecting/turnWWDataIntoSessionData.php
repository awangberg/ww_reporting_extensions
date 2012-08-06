<?php

include("access.php");

header("content-type: text/xml");

$wwCourseName = $_REQUEST['wwCourseName'];
$wwUserName = $_REQUEST['wwUserName'];
$wwEffectiveUserName = $_REQUEST['wwEffectiveUserName'];
$wwSetName = $_REQUEST['wwSetName'];
$wwProblemNumber = $_REQUEST['wwProblemNumber'];
$wwMode = $_REQUEST['mode'];

$problemTable = $wwCourseName . "_problem";

$reportQuery = "";
$query_error = "";

if ($wwMode == "tutorial") {
  //Get the concept bank associated with this set and problem
  //using the wwSession userConceptBanks table:

  //$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  $con = mysql_connect($db_host, $db_user, $db_pass);

  $db = 'wwSession';
  //select the datbase $db:
  if (mysql_select_db("$db", $con)) {
    //echo "selected database $db";
  }
  else {
    echo "1. Error selecting database $db: " . mysql_error();
  }

  $query = 'SELECT pg_sourcefile, concept_bank FROM `usersConceptBanks` WHERE course_name="' . $wwCourseName . '" AND user_name="' . $wwUserName . '" AND webwork_practice_set="' . $wwSetName . '" AND webwork_problem_set_number="' . $wwProblemNumber . '"'; 
  $pg_sourcefile = "";
  $conceptBank = "";
  $result = mysql_query($query, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $pg_sourcefile = $row['pg_sourcefile'];
    $conceptBank = $row['concept_bank'];
  }
  $reportQuery .= "<query1>$query</query1>\n";
  $reportQuery .= "  <query1Results>\n<pg_sourcefile>$pg_sourcefile</pg_sourcefile>\n<conceptBank>$conceptBank</conceptBank>\n</query1Results>\n";

  //mysql_close($con);

  //$con = mysql_connect($db_host, $db_user, $db_pass);

  //if(!$con) {
  //  die('Could not connect: ' . mysql_error());
  //}

  //the pg_sourcefile and conceptBank are stored in pg_sourcefile and conceptBank.
  //Now, go get the tutorial associated with this conceptBank and/or pg_sourcefile

  $db = $_REQUEST['userDatabaseName'];

  //select the database $db:
  if (mysql_select_db("$db", $con)) {
    //echo "selected database $db";
  }
  else {
    echo "2. Error selecting database $db: " . mysql_error();
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
  //Now, get the tutorial associated with that conceptBank

  $query = 'SELECT session_problem_id FROM `wwSetToSessionTutorial` WHERE ww_set_and_course_id=' . $ww_set_and_course_id . ' AND valid_tutorial="YES"';
  $result = mysql_query($query, $con);

  $tutorial_problem_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $tutorial_problem_id = $row['session_problem_id'];
  }
  $reportQuery .= "<query3>$query</query3>\n";
  $reportQuery .= "<query3Results>\n<tutorial_problem_id> $tutorial_problem_id </tutorial_problem_id>\n</query3Results>\n";

  $wwMode = "whiteboard";
  if ($tutorial_problem_id >= 0) {
    //check to see if the student has seen the tutorial and if the difference between the date viewed and now is more than three hours:
    $wwMode = "tutorial";
    $db = 'wwSession';
    if (mysql_select_db("$db", $con)) {
      //echo "selected database $db";
    }
    else {
      echo "11. Error selecting database $db: " . mysql_error();
      $reportQuery .= "11. Error selecting database $db: " . mysql_error();
    }

    $id = -1;
    $date_viewed = "";
    //check to see if the user has already seen the tutorial:
    $query = 'SELECT id, date_viewed FROM `sawTutorialForConceptBank` WHERE course_name="' . $wwCourseName . '" AND user_name="' . $wwUserName . '" AND concept_bank="' . $conceptBank . '"';
    $result = mysql_query($query, $con);

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $id = $row['id'];
      $reportQuery .= "<my_id>$id ->" . $row['id'] . "</my_id>\n";
      $date_viewed = $row['date_viewed'];
    }
    $reportQuery .= "<query3001>$query</query3001>\n";
    $reportQuery .= "<query3001Results>\n";
    $reportQuery .= "<id>$id</id>\n <date_viewed>$date_viewed</date_viewed>\n";
    

//    if (($id >= 0) && (strtotime($date_viewed.' + 3 hours') > time() )) {
    //line above prevented automatic running of tutorial if previously viewed in last 3 hours.
    //line below eliminates this catch.
    if (($id >= 0) && (strtotime($date_viewed.' + 0 hours') > time() )) {
      //The user saw the tutorial already, but it was within the previous 3 hours.
      $wwMode = "whiteboard";
    }
    $reportQuery .= "<strtotimeResult>" . strtotime($date_viewed.' + 3 hours') . "</strtotimeResult>\n";
    $reportQuery .= "<evaluate> id = $id >= 0 AND AND (strtotime($date_viewed.' + 3 hours') > " . time() . " ))";
    $reportQuery .= "</evaluate>\n</query3001Results>\n";

  }

  //if $tutorial_problem_id == -1, then we don't have a tutorial available.
  //In this case, just show the student the whiteboard -- that is, change showing_mode to 'whiteboard'.
  //Or, if the student has seen the tutorial before... then just go to the whiteboard mode.

}
   //end loop above.
   //Then, if mode is tutorial, continue here....
   //otherwise, continue down below.....

if ($wwMode == "tutorial") {

  $db = 'session';
  if (mysql_select_db("$db", $con)) {
    //echo "selected database $db";
  }
  else {
    echo "11. Error selecting database $db: " . mysql_error();
    $reportQuery .= "11. Error selecting database $db: " . mysql_error();
  }



  //get the user information from the table user, if it exists.
  $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
  $reportQuery .= "<query4>$query</query4>\n";
  $result = mysql_query($query,$con);

  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['course_id'];
  }
  $reportQuery .= "<query4Result>\n<course_id>$course_id</course_id>\n</query4Result>\n";

  $query = 'SELECT user_id FROM `user` WHERE user_name="' . $wwEffectiveUserName . '" AND course_id=' . $course_id;
  $result = mysql_query($query,$con);

  $user_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $user_id = $row['user_id'];
  }
  $reportQuery .= "<query5>$query</query5>\n";
  $reportQuery .= "<query5Result><user_id>$user_id</user_id></query5Result>\n";

  $query = 'SELECT permission FROM `permission` WHERE user_id=' . $user_id;
  $reportQuery .= "<query6>$query</query6>\n";

  $permission = -5;
  $result = mysql_query($query,$con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $permission = $row['permission'];
  }
  $reportQuery .= "<query6Result>$permission</query6Result>\n";

  // If the user information from the table user does not exist, then grab that information from 
  // the webwork table, put it in there, and assign a user_id and course_id

  if ($user_id == -1) {
    $reportQuery  .= "<NOTE>Add user to Session Database</NOTE>\n";

    mysql_close($con);
    
    //open connection to the webwork database:
    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = 'webwork';
    //select the databse $db
    if (mysql_select_db("$db", $con)) {
      //echo "selected databse $db";
    }
    else {
      echo "3. Error selecting database $db: " . mysql_error();
      $query_error .= "<Error5>Error selecting database $db: " . mysql_error() . "</Error5>\n";
    }

    //Get the following information from the webwork database:
    //From user table:
    //user_name
    //first_name
    //last_name
    //email_address
    //student_id
    //status
    //section
    //recitation
    //comment

    $ww_user_table = $wwCourseName . "_user";

    $query = 'SELECT user_id, first_name, last_name, email_address, student_id, status, section, recitation, comment FROM `' . $ww_user_table . '` WHERE user_id="' . $wwEffectiveUserName . '"';

    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $new_user_name = $row['user_id'];
      $new_first_name = $row['first_name'];
      $new_last_name = $row['last_name'];
      $new_email_address = $row['email_address'];
      $new_student_id = $row['student_id'];
      $new_status = $row['status'];
      $new_section = $row['section'];
      $new_recitation = $row['recitation'];
      $new_comment = $row['comment']; 
    }
    $reportQuery .= "<query10>$query</query10>\n";
    $reportQuery .= "<query10Results>\n";
    $reportQuery .= "user_name is $new_user_name, first_name is $new_first_name, last_name is $new_last_name, email_address is $new_email_address, student_id is $new_student_id, status is $new_status, section is $new_section, recitation is $new_recitation, comment is $new_comment\n";
    $reportQuery .= "</query10Results>\n";

    //We won't worry about the password issue for now.
    $ww_permission_table = $wwCourseName . "_permission";
    //permission from the permission table
    $query = 'SELECT permission FROM `' . $ww_permission_table . '` WHERE user_id="' . $new_user_name . '"';
    $result = mysql_query($query, $con);

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $new_permission = $row['permission'];
    }
    $reportQuery .= "<query11>$query</query11>\n";
    $reportQuery .= "<query11Results>\n<permission>$permission</permission>\n</query11Results>\n";

    mysql_close($con);

    //go add this user to the session database, updating user and permission
    $con = mysql_connect($db_host, $db_user, $db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = $_REQUEST['userDatabaseName'];
    //select the database $db:
    if (mysql_select_db("$db", $con)) {
      //echo "selected database $db";
    }
    else {
      echo "4. Error selecting database $db: " . mysql_error();
    }

    //get the course_id, if there is one.
    //otherwise, make a new course_id for this webwork course.
    $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
    $result = mysql_query($query, $con);
    $course_id = -1;

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $course_id = $row['course_id'];
    }

    if ($course_id == -1) {
      $query  = "INSERT INTO course (course_name, section_name, instructor_id, initial_date, final_date, status) ";
      $query .= "VALUES ('" . $wwCourseName . "', '', 1, '2010-01-01 00:00:00', '2020-01-01 00:00:00', 'C')";
      $result = mysql_query($query, $con);
      $course_id = mysql_insert_id(); 
    }


    //update the user table


    $query  = "INSERT INTO user (course_id, user_name, first_name, last_name, email_address, student_id, status, section, recitation, comment) ";
    $query .= "VALUES ('"
                     . $course_id
                     . "', '"
                     . $new_user_name
                     . "', '"
                     . $new_first_name
                     . "', '"
                     . $new_last_name
                     . "', '"
                     . $new_email_address
                     . "', '"
                     . $new_student_id
                     . "', '"
                     . $new_status
                     . "', '"
                     . $new_section
                     . "', '"
                     . $new_recitation
                     . "', '"
                     . $new_comment
                     . "')";

    $result = mysql_query($query, $con);
    $user_id = mysql_insert_id();
    $reportQuery .= "<query13>$query</query13>\n";
    $reportQuery .= "<query13Results>user_id is $user_id</query13Results>\n";

    //update the permission table:

    $query = "INSERT INTO permission (user_id, course_id, permission) ";
    $query .= "VALUES ('"
                      . $user_id
                      . "', '"
                      . $course_id
                      . "', '"
                      . $new_permission
                      . "')";

    $result = mysql_query($query, $con);
    $reportQuery .= "<query14>$query</query14>\n";
    $reportQuery .= "<query14Results>set permission to $new_permission</query14Results>\n";
    $permission = $new_permission;
  }


  $sawTutorialForConceptBank_id = -1;
  $sawTutorialForPGProblem_id = -1;
  $session_answer_id = -1;

  $effective_user_id = -1;

  if ($wwEffectiveUserName == $wwUserName) {
    //record that this user is beginning to view a tutorial, since the tutorial does exist: 
    $effective_user_id = $user_id;

    //make a record in session.student_answer that says the student is beginning to see the tutorial:
    $query = 'INSERT INTO student_answer (problem_id, student_id, internal_order) VALUES (' . $tutorial_problem_id . ', ' . $user_id . ', 1)';
    $result = mysql_query($query, $con);
    $session_answer_id = mysql_insert_id();
    $reportQuery .= "<query30>$query</query30>\n";
    $reportQuery .= "<query30Results>answer_id is $session_answer_id in student_answer session table</query30Results>\n";

    $query = 'UPDATE student_answer SET tutorial_key=' . $session_answer_id . ' WHERE answer_id=' . $session_answer_id;
    $result = mysql_query($query, $con);
    $reportQuery .= "<query300>$query</query300>\n";
    $reportQuery .= "<query300Results>set answer_key to session_answer_id, which is $session_answer_id</query300Results>\n";


    //If the student has already seen the tutorial, then get their previous data:
    //actually, don't do this the first time around....

    

    $db = 'wwSession';
    //select the database $db:
    if (mysql_select_db("$db", $con)) {
      //echo "selected database: $db";
    }
    else {
      echo "11. Error selecting database $db: " . mysql_error();
    }

    //$query = 'SELECT answer_id FROM `sawTutorialForConceptBank` WHERE course_name="' . $wwCourseName . '" AND user_name="' . $wwEffectiveUserName . '" AND conceptBank="' . $conceptBank . '"';
    //$result = mysql_query($query, $con);

    //while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    //  $answer_id = row['answer_id'];
    //}
    //$reportQuery .= "<query31>$query</query35>\n";
    //$reportQuery .= "<query31Results>answer_id is $answer_id</query31Results>\n";
 
    if ($session_answer_id >= 0) {
      //user hasn't seen the tutorial, so make a record for this tutorial and let them see it.
      $query = 'INSERT INTO sawTutorialForConceptBank (course_name, user_name, concept_bank, session_problem_id, answer_id) VALUES ("' . $wwCourseName . '", "' . $wwEffectiveUserName . '", "' . $conceptBank . '", ' . $tutorial_problem_id . ', ' . $session_answer_id . ')';
      $result = mysql_query($query, $con);
      $sawTutorialForConceptBank_id = mysql_insert_id();
      $reportQuery .= "<query33>$query</query33>\n";
      $reportQuery .= "<query33Results>got sawTutorialForConceptBank id $sawTutorialForConceptBank_id</query33Results>\n";

      $query = 'INSERT INTO sawTutorialForPGProblem (course_name, user_name, concept_bank, pg_sourcefile, session_problem_id, answer_id) VALUES ("' . $wwCourseName . '", "' . $wwEffectiveUserName . '", "' . $conceptBank . '", "' . $pg_sourcefile . '", ' . $tutorial_problem_id . ', ' . $session_answer_id . ')';
      $result = mysql_query($query, $con);
      $sawTutorialForConceptBank_id = mysql_insert_id();
      $reportQuery .= "<query35>$query</query35>\n";
      $reportQuery .= "<query35Results>got sawTutorialForConceptBank id $sawTutorialForConceptBank_id</query35Results>\n";
    }
  }

  $return_string  = "<Results>\n";
  $return_string .= "  <Code>LOGGED_IN</Code>\n";
  $return_string .= "  <User_ID>$user_id</User_ID>\n";
  $return_string .= "  <Effective_User_ID>$effective_user_id</Effective_User_ID>\n";
  $return_string .= "  <Permission>$permission</Permission>\n";
  $return_string .= "  <Problem_ID>$tutorial_problem_id</Problem_ID>\n";
  $return_string .= "  <sawTutorialForConceptBank_ID>$sawTutorialForConceptBank_id</sawTutorialForConceptBank_ID>\n";
  $return_string .= "  <sawTutorialForPGProblem_ID>$sawTutorialForPGProblem_id</sawTutorialForPGProblem_ID>\n";
  $return_string .= "  <answer_id>$session_answer_id</answer_id>\n";
  $return_string .= "  <tutorial_key>$session_answer_id</tutorial_key>\n";
  $return_string .= "  <previous_student_answer_id>-1</previous_student_answer_id>\n";
  $return_string .= "  <mode>tutorial</mode>\n";
  $return_string .= "  <Process_Notes>\n";
  $return_string .= "    $reportQuery\n";
  $return_string .= "    $query_error\n";
  $return_string .= "  </Process_Notes>\n";
  $return_string .= "</Results>\n";

  mysql_close($con);
  print $return_string;
}

else {
  //show the whiteboard - with user's drawings on the whiteboard:

  //connect to the session database:
  $con = mysql_connect($db_host, $db_user, $db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = $_REQUEST['userDatabaseName'];

  //select the database $db:
  if (mysql_select_db("$db", $con)) {
    //echo "selected database $db";
  }
  else {
    echo "5. Error selecting database $db: " . mysql_error();
  }

  //get the course from the course table:
  $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
  $result = mysql_query($query, $con);

  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['course_id'];
  }
  $reportQuery .= "<query21Result>\n<course_id>$course_id</course_id>\n</query21Result>\n";
  
  // or add this course to the course table.
  if ($course_id == -1) {
    $query  = "INSERT INTO course (course_name, section_name, instructor_id, initial_date, final_date, status) ";
    $query .= "VALUES ('" . $wwCourseName . "', '', 1, '2010-01-01 00:00:00', '2020-01-01 00:00:00', 'C')";
    $result = mysql_query($query, $con);
    $course_id = mysql_insert_id();
  }


  //get the user's information from the user table:
  $query = 'SELECT user_id FROM `user` WHERE user_name="' . $wwEffectiveUserName . '" AND course_id=' . $course_id;
  $result = mysql_query($query, $con);

  $user_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $user_id = $row['user_id'];
  }

  $reportQuery .= "<query23>$query</query23>\n";
  $reportQuery .= "<query23Result>\n<user_id>$user_id</user_id>\n</query23Result>\n";

  //get the user's permission from the permission table
  if ($user_id != -1) {
    $query = 'SELECT permission FROM `permission` WHERE user_id=' . $user_id . ' AND course_id=' . $course_id;
    $permission = -5;
    $result = mysql_query($query,$con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $permission = $row['permission'];
    }
    $reportQuery .= "<query24>$query></query24>\n";
    $reportQuery .= "<query24Result>\n<permission>$permission</permission>\n</query24Result>\n";
  }
  //or add this user to the user table.
  //
  //
  if ($user_id == -1) {
    //add the user to the Session Database.
    $reportQuery .= "<NOTE>Add user to Session Database</NOTE>\n";

    mysql_close($con);

    //open connection to the webwork database:
    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = 'webwork';
    //select the databse $db
    if (mysql_select_db("$db", $con)) {
      //echo "selected databse $db";
    }
    else {
      echo "Error selecting database $db: " . mysql_error();
      $query_error .= "<Error5>Error selecting database $db: " . mysql_error() . "</Error5>\n";
    }
    
    //Get the following information from the webwork database:
    //From user table:
    //user_name
    //first_name
    //last_name
    //email_address
    //student_id
    //status
    //section
    //recitation
    //comment

    $ww_user_table = $wwCourseName . "_user";

    $query = 'SELECT user_id, first_name, last_name, email_address, student_id, status, section, recitation, comment FROM `' . $ww_user_table . '` WHERE user_id="' . $wwEffectiveUserName . '"';

    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $new_user_name = $row['user_id'];
      $new_first_name = $row['first_name'];
      $new_last_name = $row['last_name'];
      $new_email_address = $row['email_address'];
      $new_student_id = $row['student_id'];
      $new_status = $row['status'];
      $new_section = $row['section'];
      $new_recitation = $row['recitation'];
      $new_comment = $row['comment'];
    }
    $reportQuery .= "<query25>$query</query25>\n";
    $reportQuery .= "<query25Results>\n";
    $reportQuery .= "user_name is $new_user_name, first_name is $new_first_name, last_name is $new_last_name, email_address is $new_email_address, student_id is $new_student_id, status is $new_status, section is $new_section, recitation is $new_recitation, comment is $new_comment\n";
    $reportQuery .= "</query25Results>\n";

    //We won't worry about the password issue for now.
    $ww_permission_table = $wwCourseName . "_permission";
    //permission from the permission table
    $query = 'SELECT permission FROM `' . $ww_permission_table . '` WHERE user_id="' . $new_user_name . '"';
    $result = mysql_query($query, $con);

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $new_permission = $row['permission'];
    }
    $reportQuery .= "<query26>$query</query26>\n";
    $reportQuery .= "<query26Results>\n<permission>$permission</permission>\n</query26Results>\n";

    mysql_close($con);

    //go add this user to the session database, updating user and permission
    $con = mysql_connect($db_host, $db_user, $db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = $_REQUEST['userDatabaseName'];
    //select the database $db:
    if (mysql_select_db("$db", $con)) {
      //echo "selected database $db";
    }
    else {
      echo "Error selecting database $db: " . mysql_error();
    }


    //update the user table:
    $query  = "INSERT INTO user (course_id, user_name, first_name, last_name, email_address, student_id, status, section, recitation, comment) ";
    $query .= "VALUES ('"
                     . $course_id
                     . "', '"
                     . $new_user_name
                     . "', '"
                     . $new_first_name
                     . "', '"
                     . $new_last_name
                     . "', '"
                     . $new_email_address
                     . "', '"
                     . $new_student_id
                     . "', '"
                     . $new_status
                     . "', '"
                     . $new_section
                     . "', '"
                     . $new_recitation
                     . "', '"
                     . $new_comment
                     . "')";

    $result = mysql_query($query, $con);
    $user_id = mysql_insert_id();
    $reportQuery .= "<query27>$query</query27>\n";
    $reportQuery .= "<query27Results>user_id is $user_id</query27Results>\n";

    //update the permission table:

    $query = "INSERT INTO permission (user_id, course_id, permission) ";
    $query .= "VALUES ('"
                      . $user_id
                      . "', '"
                      . $course_id
                      . "', '"
                      . $new_permission
                      . "')";

    $result = mysql_query($query, $con);
    $reportQuery .= "<query28>$query</query28>\n";
    $reportQuery .= "<query28Results>set permission to $new_permission</query28Results>\n";
    $permission = $new_permission;

  }
  //get the problem_id from the table wwStudentWorkForProblem table with
  // ww_set_id = wwSetName and ww_problem_number = wwProblemNumber

  $query = 'SELECT problem_id FROM `wwStudentWorkForProblem` WHERE course_id=' . $course_id . ' AND user_id=' . $user_id . ' AND ww_set_id="' . $wwSetName . '" AND ww_problem_number=' . $wwProblemNumber;
  $result = mysql_query($query, $con);

  $problem_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problem_id = $row['problem_id'];
  }

  $reportQuery .= "<query29>$query</query29>\n";
  $reportQuery .= "<query29Result>\n<problem_id>$problem_id</problem_id>\n</query29Result>\n";

  //show the user's previous work:
  if ($problem_id != -1) {
    $return_string  = "<Results>\n";
    $return_string .= "  <Code>LOGGED_IN</Code>\n";
    $return_string .= "  <User_ID>$user_id</User_ID>\n";
    $return_string .= "  <Effective_User_ID>$user_id</Effective_User_ID>\n";
    $return_string .= "  <Permission>$permission</Permission>\n";
    $return_string .= "  <Problem_ID>$problem_id</Problem_ID>\n";
    $return_string .= "  <mode>whiteboard</mode>\n";
    $return_string .= "  $reportQuery\n";
    $return_string .= "  $query_error\n";
    $return_string .= "</Results>\n";
  }
  else {
    // or show a blank whiteboard, as the user hasn't saved anything.
    //if the user doesn't exist, put the user into the table.
    $return_string  = "<Results>\n";
    $return_string .= "  <Code>LOGGED_IN</Code>\n";
    $return_string .= "  <User_ID>$user_id</User_ID>\n";
    $return_string .= "  <Effective_User_ID>$user_id</Effective_User_ID>\n";
    $return_string .= "  <Permission>$permission</Permission>\n";
    $return_string .= "  <Problem_ID>-1</Problem_ID>\n";
    $return_string .= "  <mode>whiteboard</mode>\n";
    $return_string .= "  $reportQuery\n";
    $return_string .= "  $query_error\n";
    $return_string .= "</Results>\n";
  }
  mysql_close($con);

  print $return_string;
}

if (0) {
  $query = 'SELECT source_file FROM `' . $problemTable . '` WHERE set_id="' . $wwSetName . '" AND problem_id="' . $wwProblemNumber . '"';

  $reportQuery = "<query1>$query</query1>\n";

  $result = mysql_query($query, $con);

  $source_file = "";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $source_file = $row['source_file'];
  }
  $reportQuery .= "  <query1Result>$source_file</query1Result>\n";

  mysql_close($con);

  $con = mysql_connect($db_host, $db_user, $db_pass);

  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = $_REQUEST['userDatabaseName'];

  //select the database $db
  if (mysql_select_db("$db", $con)) {
  	//echo "selected database $db";
  }
  else {
    	echo "6. Error selecting database $db: " . mysql_error();
  } 

  $courseWithTutorials = "SessionExperiment";

  //$query = 'SELECT ww_set_and_course_id FROM `wwSetInSession` WHERE ww_course_name="' . $wwCourseName . '" AND ww_set_id="' . $wwSetName . '"';
  $query = 'SELECT ww_set_and_course_id FROM `wwSetInSession` WHERE ww_course_name="' . $courseWithTutorials . '" AND ww_set_id="' . $wwSetName . '"';
  $reportQuery .= "<query2>$query</query2>\n";

  $result = mysql_query($query,$con);
  $ww_set_and_course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ww_set_and_course_id = $row['ww_set_and_course_id'];
  }

  $reportQuery .= "  <query2Result>$ww_set_and_course_id</query2Result>\n";

  $query = 'SELECT session_problem_id FROM `wwSetToSessionTutorial` WHERE ww_set_and_course_id=' . $ww_set_and_course_id;

  $reportQuery .= "<query3>$query</query3>\n";

  $result = mysql_query($query,$con);
  $tutorial_id_for_set = -1;

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $tutorial_id_for_set = $row['session_problem_id'];
  }

  $reportQuery .= "<query3Result>$tutorial_id_for_set</query3Result>\n";

  //Do a similar thing for a specific problem name:
  //$query = 'SELECT ww_set_and_course_id FROM `wwSetInSession` WHERE ww_course_name="' . $wwCourseName . '" AND ww_set_id="' . $wwSetName . '"';
  //$result = mysql_query($query,$con);
  //$ww_set_and_course_id = -1;
  //while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  //  $ww_set_and_course_id = $row['ww_set_and_course_id'];
  //}
  //
  //$query = 'SELECT session_problem_id FROM `wwSetToSessionTutorial` WHERE ww_set_and_course_id=' . $ww_set_and_course_id;
  //
  //$result = mysql_query($query,$con);
  //$tutorial_id_for_set = -1;
  //
  //while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  //  $tutorial_id_for_set = $row['session_problem_id'];
  //}

  $query = 'SELECT course_id FROM `course` WHERE course_name="' . $wwCourseName . '"';
  $reportQuery .= "<query4>$query</query4>\n";

  $result = mysql_query($query,$con);
  $course_id = -1;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $course_id = $row['course_id'];
  }
  $reportQuery .= "<query4Result>$course_id</query4Result>\n";

  $query = 'SELECT user_id FROM `user` WHERE user_name="' . $wwEffectiveUserName . '" AND course_id=' . $course_id;
  $reportQuery .= "<query5>$query</query5>\n";

  $user_id = -1;
  $result = mysql_query($query,$con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $user_id = $row['user_id'];
  }

  $reportQuery .= "<query5Result>$user_id</query5Result>\n";

  $query = 'SELECT permission FROM `permission` WHERE user_id=' . $user_id . ' AND course_id=' . $course_id;
  $reportQuery .= "<query6>$query</query6>\n";
  
  $permission = -5;
  $result = mysql_query($query,$con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $permission = $row['permission'];
  }
  $reportQuery .= "<query6Result>$permission</query6Result>\n";



  $return_string  = "<Results>\n";
  $return_string .= "  <Code>LOGGED_IN</Code>\n";
  $return_string .= "  <User_ID>$user_id</User_ID>\n";
  $return_string .= "  <Effective_User_ID>$user_id</Effective_User_ID>\n";
  $return_string .= "  <Permission>$permission</Permission>\n";
  $return_string .= "  <Problem_ID>$tutorial_id_for_set</Problem_ID>\n";
  $return_string .= "  $reportQuery\n";
  $return_string .= "  $query_error\n";
  $return_string .= "</Results>\n";

  mysql_close($con);

  print $return_string;

}
?>
