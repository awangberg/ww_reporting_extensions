<?php

if ($argc != 7) {
  die("Usage:  recordConceptBankFor_courseID_userID_practiceSet_problemID_pgSourcefile.php <conceptBank> <course_name> <user_id> <practice_set_id> <problem_id> <source_file>\n");
}

//remove first argument
array_shift($argv);

//get and use remaining arguments:
$concept_bank = $argv[0];
$course_name = $argv[1];
$user_name = $argv[2];
$practice_set_id = $argv[3];
$problem_id = $argv[4];
$source_file = $argv[5];

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

// make sure concept bank is in usersConceptBank

$query = "SELECT id FROM `usersConceptBanks` WHERE course_name='$course_name' "
       . "AND user_name='$user_name' AND webwork_practice_set='$practice_set_id' "
       . "AND webwork_problem_set_number=$problem_id "
       . "AND concept_bank='$concept_bank' "
       . "AND pg_sourcefile='$source_file'";

$existing_id = -1;

print "Query: $query\n";

$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $existing_id = $row['id'];
}

if ($existing_id == -1) {
  $query = "INSERT INTO usersConceptBanks (course_name, user_name, webwork_practice_set, webwork_problem_set_number, concept_bank, pg_sourcefile) VALUE ('" . $course_name . "', '" . $user_name . "', '" . $practice_set_id . "', " . $problem_id . ", '" . $concept_bank . "', '" . $source_file . "')";


  $result = mysql_query($query,$con);
}

// make sure to initialize num_of_incorrect_attempts in conceptBankIncorrectAttempts

$id = -1;
$incorrect_attempts = -1;
$query = "SELECT id, num_of_incorrect_attempts FROM `conceptBankIncorrectAttempts` "
       . " WHERE course_name='$course_name' AND user_name='$user_name' "
       . " AND concept_bank='$concept_bank' AND webwork_practice_set='$practice_set_id'";


$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $id = $row['id'];
  $incorrect_attempts = $row['num_of_incorrect_attempts'];
}


if ($id == -1) {
  $query = "INSERT INTO conceptBankIncorrectAttempts (course_name, user_name, concept_bank, num_of_incorrect_attempts, webwork_practice_set) "
         . " VALUE ('" . $course_name . "', '" . $user_name . "', '" . $concept_bank . "', 0, '" . $practice_set_id . "')";
  $result = mysql_query($query,$con);

}



//make sure to initialze num_of_incorrect_attempts in pgProblemIncorrectAttempts

$id = -1;
$incorrect_attempts = -1;
$query = "SELECT id, num_of_incorrect_attempts FROM `pgProblemIncorrectAttempts` "
       . " WHERE course_name='$course_name' AND user_name='$user_name' "
       . " AND pg_sourcefile='$source_file' AND webwork_practice_set='$practice_set_id'";


$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $id = $row['id'];
  $incorrect_attempts = $row['num_of_incorrect_attempts'];
}

if ($id == -1) {
  $query = "INSERT INTO pgProblemIncorrectAttempts (course_name, user_name, pg_sourcefile, num_of_incorrect_attempts, webwork_practice_set) "
         . " VALUE ('" . $course_name . "', '" . $user_name . "', '" . $source_file . "', 0, '" . $practice_set_id . "')";
  $result = mysql_query($query,$con);

}

mysql_close($con);

?>
