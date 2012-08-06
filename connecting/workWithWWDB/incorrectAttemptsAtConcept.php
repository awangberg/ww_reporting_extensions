<?php

if ($argc != 5) {
  die("Usage: attemptsAtConcept.php <CourseName> <User_Name> <WeBWorK_Practice_Set> <WeBWorK_Practice_Set_Number>\n");
}


// remove first argument
array_shift($argv);

//get and use remaining arguments:
$courseName = $argv[0];
$userName = $argv[1];
$practiceSet = $argv[2];
$practiceSetNumber = $argv[3];

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

$query = 'SELECT id, pg_sourcefile, concept_bank FROM usersConceptBanks WHERE course_name="' . $courseName . '" AND user_name="' . $userName . '" AND webwork_practice_set="' . $practiceSet . '" AND webwork_problem_set_number=' . $practiceSetNumber;
$result = mysql_query($query,$con);
$usersConceptBanks_id = -1;
$sourceFile = "";
$conceptBank = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $usersConceptBanks_id = $row['id'];
  $sourceFile = $row['pg_sourcefile'];
  $conceptBank = $row['concept_bank'];
}


if ($usersConceptBanks_id < 0) {
  print "No Record matching in usersConceptBanks\n";
  print "Query was: $query\n";
  die('No Record');
}

#return number of attempts at this concept:
$query = "SELECT num_of_incorrect_attempts FROM conceptBankIncorrectAttempts WHERE "
       . "course_name='$courseName' AND user_name='$userName' AND "
       . "concept_bank='$conceptBank' AND webwork_practice_set='$practiceSet'";
$incorrect_attempts = -1;

$result = mysql_query($query,$con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $incorrect_attempts = $row['num_of_incorrect_attempts'];
}

mysql_close($con);

print "$incorrect_attempts";

?>
