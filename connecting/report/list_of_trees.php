<?php

include("../access.php");
include("common.php");

function tree_href($cl) {

  $s =  "'http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/change_in_scores_with_pca_by_concept_pre_post2.php?";
  $s .= "prune=prune&colorConnection=colorConnection&";
  $s .= "courses%5B0%5D=";
  $s .= "Math160_F2009_awangberg&courses%5B1%5D=Math160_S2010_awangberg_05&courses%5B2%5D=Math160_S2010_eerrthum&course%5B3%5D=Math160_F2010_awangberg&";
  $s .= "concepta1=group%3A" . $cl[0];
  $s .= "&implies1=implies1&";
  $s .= "concepta2=group%3A" . $cl[1];
  $s .= "&implies2=implies1&";
  $s .= "concepta3=group%3A" . $cl[2];
  $s .= "&implies3=implies1&";
  $s .= "concepta4=group%3A" . $cl[3];
  $s .= "&implies4=implies1&implies5=implies1&implies6=implies1&implies7=implies1&implies8=implies1&implies9=implies1&Submit=Submit'";
  return $s;
}

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
  $str = `php ../workWithWWDB/weighted_practice_sets.php $k $original_quiz_list`;

  //print "php ../workWithWWDB/weighted_practice_sets.php $k $original_quiz_list<P>";

  $l = explode("<BR>", $str);
  $concept_list = array();
  $percent_list = array();
  foreach ($l as $tmp_k => $val) {
    list($b11, $n1, $c1,$p1, $rank1, $per1, $p2) = explode(" ", $val);
    if ($c1 != "") { $concept_list[] = $c1; $percent_list[] = sprintf("%01.2f", $per1); }
  }

  print "<P><B>Quiz Concept $k</B>:<BR>   " . join(" ", $percent_list) . "<BR>";
  print "<a href=" . tree_href($concept_list) . " target='treeFrame'>At Top</a> ";
  print "<a href=" . tree_href(array_reverse($concept_list)) . " target='treeFrame'>At Bottom</a>";
}



?>

