<?php

if ($argc != 6) {
  die("Usage: recordStartOfAttempt.php <CourseName> <User_Name> <WeBWorK_Practice_Set> <WeBWorK_Practice_Set_Number> <timeStamp yyyy-mm-dd hh:mm:ss>\n");
}


// remove first argument
array_shift($argv);

//get and use remaining arguments:
$courseName = $argv[0];
$userName = $argv[1];
$practiceSet = $argv[2];
$practiceSetNumber = $argv[3];
$recordTime = $argv[4];


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

#Use this information to insert a new record into attempts:
$todaysDate = date ("Y-m-d H:m:s");
$query  = "INSERT INTO attempts (course_name, user_name, concept_bank, pg_sourcefile, attempted_date) ";
#$query .= " VALUES ('$courseName', '$userName', '$conceptBank', '$sourceFile', '$todaysDate')"; 
$query .= " VALUES ('$courseName', '$userName', '$conceptBank', '$sourceFile', '$recordTime')";

print $query;

$attempt_id = -1;
$result = mysql_query($query, $con);
$attempt_id = mysql_insert_id();

#Update the usersConceptBanks "latestAttemptID" field 
$query = "UPDATE usersConceptBanks SET latestAttemptID='$attempt_id' WHERE id=$usersConceptBanks_id";
$result = mysql_query($query, $con);

mysql_close($con);

print "";

?>
