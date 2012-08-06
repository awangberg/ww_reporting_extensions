<?php

if ($argc != 4) {
  die("Usage:  getConceptGroupFrom_courseID_setID_problemID.php <course_name> <set_id> <problem_id>\n");
}

//remove first argument
array_shift($argv);

//get and use remaining arguments:
$course_name = $argv[0];
$set_id = $argv[1];
$problem_id = $argv[2];

include("access.php");

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'webwork';
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$problemTable = $course_name . "_problem";

$query = 'SELECT source_file FROM ' . $problemTable . ' WHERE set_id="' . $set_id . '" AND problem_id=' . $problem_id;

$return_string = "";
$result = mysql_query($query,$con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $return_string .= $row['source_file'];
}

mysql_close($con);

print $return_string;

?>
