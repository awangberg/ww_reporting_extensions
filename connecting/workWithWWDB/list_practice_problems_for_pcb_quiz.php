<?php

include("access.php");
//include("is_valid_user.php");
//include("common.php");

//get the list of concept banks included in the pcb quiz:
$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = "webwork";
$result = mysql_select_db("$db", $con);

$list_of_quiz_concepts = array();

$query = "SELECT source_file from `Math160_F2011_awangberg_problem` WHERE set_id='pcb' ORDER BY problem_id";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  list($this_group, $this_concept) = explode(":", $row['source_file']);
  $list_of_quiz_concepts[$this_concept] = $this_concept;
}

//print_r($list_of_quiz_concepts);

$original_quiz_list = join("_AA_", array_keys($list_of_quiz_concepts));

//for each in that list, pring out the associated practice problems.
foreach($list_of_quiz_concepts as $k) {
 // print "$k\n";
  print `php weighted_practice_sets.php $k $original_quiz_list`;
}

?>
