<?php

if ($argc != 8) {
  die("Usage: recordEndOfAttempt.php <CourseName> <User_Name> <WeBWorK_Practice_Set> <WeBWorK_Practice_Set_Number> <was_successful> <submitted_answer> <timeStamp yyyy-mm-dd hh:mm:ss>\n");
}


// remove first argument
array_shift($argv);

//get and use remaining arguments:
$courseName = $argv[0];
$userName = $argv[1];
$practiceSet = $argv[2];
$practiceSetNumber = $argv[3];
$wasSuccessful = $argv[4];
$answer = $argv[5];
$recordTime = $argv[6];

include("access.php");

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'wwSession';
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$query = 'SELECT id, pg_sourcefile, concept_bank, latestAttemptID FROM usersConceptBanks WHERE course_name="' . $courseName . '" AND user_name="' . $userName . '" AND webwork_practice_set="' . $practiceSet . '" AND webwork_problem_set_number=' . $practiceSetNumber;
$result = mysql_query($query,$con);
$usersConceptBanks_id = -1;
$sourceFile = "";
$conceptBank = "";
$attemptsID = -1;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $usersConceptBanks_id = $row['id'];
  $sourceFile = $row['pg_sourcefile'];
  $conceptBank = $row['concept_bank'];
  $attemptsID = $row['latestAttemptID'];
}


if ($attemptsID < 0) {
  print "No Record matching in usersConceptBanks\n";
  print "Query was: $query\n";
  die('No Record');
}

//Use this information to insert a new record into attempts:
$todaysDate = date ("Y-m-d H:m:s");
//$query  = "UPDATE attempts SET submitted_date='$todaysDate', submitted_answer='$answer', was_successful=$wasSuccessful WHERE id=$attemptsID";
$query  = "UPDATE attempts SET submitted_date='$recordTime', submitted_answer='$answer', was_successful=$wasSuccessful WHERE id=$attemptsID";

$result = mysql_query($query, $con);

print "query: $query\n";
print "-----> $result\n";
//update conceptBankIncorrectAttempts and pgProblemIncorrectAttempts if $answer == 0

if ($wasSuccessful == 0) {

  $query = "SELECT id, num_of_incorrect_attempts FROM conceptBankIncorrectAttempts "
         . "WHERE course_name='$courseName' AND user_name='$userName' "
         . " AND concept_bank='$conceptBank' AND webwork_practice_set='$practiceSet'";
  $conceptBankIncorrect_ID = -1;
  $num_of_incorrect_attempts = 0;

  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $conceptBankIncorrect_ID = $row['id'];
    $num_of_incorrect_attempts = $row['num_of_incorrect_attempts'];

  }

  if ($conceptBankIncorrect_ID < 0) {
    $query = "INSERT INTO conceptBankIncorrectAttempts "
           . "(course_name, user_name, concept_bank, webwork_practice_set, num_of_incorrect_attempts) "
           . "VALUES ('$courseName', '$userName', '$conceptBank', '$practiceSet', 0)";
print $query . "\n";

    $result = mysql_query($query, $con);
    $conceptBankIncorrect_ID = mysql_insert_id();
  }

  $num_of_incorrect_attempts = $num_of_incorrect_attempts + 1;

  $query = "UPDATE conceptBankIncorrectAttempts SET num_of_incorrect_attempts=$num_of_incorrect_attempts WHERE id=$conceptBankIncorrect_ID";
print $query . "\n";

  $result = mysql_query($query, $con);


  $query = "SELECT id, num_of_incorrect_attempts FROM pgProblemIncorrectAttempts WHERE course_name='$courseName' AND user_name='$userName' AND pg_sourcefile='$sourceFile' AND webwork_practice_set='$practiceSet'";
  $num_of_incorrect_attempts = 0;
  $pgIncorrect_ID = -1;

  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $num_of_incorrect_attempts = $row['num_of_incorrect_attempts'];
    $pgIncorrect_ID = $row['id'];
  }


  if ($pgIncorrect_ID < 0) {
    $query  = "INSERT INTO pgProblemIncorrectAttempts ";
    $query .= "(course_name, user_name, pg_sourcefile, webwork_practice_set, num_of_incorrect_attempts) ";
    $query .= "VALUES ('$courseName', '$userName', '$sourceFile', '$practiceSet', 0)";

    $result = mysql_query($query, $con);
    $pgIncorrect_ID = mysql_insert_id();
  }

  $num_of_incorrect_attempts = $num_of_incorrect_attempts + 1;

  $query = "UPDATE pgProblemIncorrectAttempts SET num_of_incorrect_attempts=$num_of_incorrect_attempts WHERE id=$pgIncorrect_ID";
  $result = mysql_query($query, $con);
}

mysql_close($con);

print "";

?>
