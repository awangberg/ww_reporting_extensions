<?php

if ($argc != 4) {
  die("Usage: get_n_ValidWeBWorKProblemsFromConceptSet.php <number> <CourseName> <Concept_Set>");
}


// remove first argument
array_shift($argv);

//get and use remaining arguments:
$valid_n = $argv[0];
$courseName = $argv[1];
$conceptSet = $argv[2];


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

$setTable = $courseName . "_problem";

#$query = 'SELECT CAST(set_id AS CHAR) AS set_id, source_file FROM ' . $setTable . ' WHERE ;
$query = 'SELECT source_file FROM ' . $setTable . ' WHERE set_id="' . $conceptSet . '" AND value>0';

//print "query is $query";

//print "$query";
$sourceFiles = array();
$result = mysql_query($query,$con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $sourceFiles[] = $row['source_file'];
}

mysql_close($con);

if (count($sourceFiles) > 1) {
  shuffle($sourceFiles);
}

if (count($sourceFiles) < $valid_n ) {
  $add_more = $valid_n - count($sourceFiles);
  for ($i = 0; $i < $add_more; $i++) {
    $tmp = $sourceFiles[$i];
    $sourceFiles[] = $tmp;
  }
}


$return_string = "";
$blankSpace = "";

for ($i = 0; $i < $valid_n; $i++) {
  $return_string .= $blankSpace . $sourceFiles[$i] ;
  $blankSpace = " ";
}

print $return_string;

?>
