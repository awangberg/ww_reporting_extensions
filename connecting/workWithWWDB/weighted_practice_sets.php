<?php

include("access.php");

//include("is_valid_users.php");
//include("common.php");

//$weight_string = 9_1_6_1.2_5_1.4_4_1.5_3_1.75_2_2_1_3;

function score($o_level, $m_level, $n_matches, $weight_string) {
  $weights_big = explode("__", $weight_string);
  //print_r($weights_big);
  $weight = 0;
  foreach ($weights_big as $num => $val) {
    $weights_small = explode("_", $val);
    $k = $weights_small[0];
    $c = $weights_small[1];
    if (($n_matches >= $k) && ($weight == 0) ) {
      $weight = $c;
    }
  }
  $score = 0;
  if ($m_level == $o_level) $score = 5;
  if ($o_level - $m_level == 1) $score = 3;
  if ($m_level - $o_level == 1) $score = 2;
  if ($o_level - $m_level == 2) $score = 1;
  //print "o_level: $o_level, m_level: $m_level, n_matches: $n_matches, $weight:  $score * $weight = ";
  //print $score * $weight;
  //print "<BR>";
  return $score * $weight;
}


$original_list = array();

if (isset($_REQUEST['basic_concept']) || isset($_REQUEST['do_all'])) {
  $basic_concept = $_REQUEST['basic_concept'];
  $do_all = isset($_REQUEST['do_all']) ? 1 : 0;
}
else if (isset($_SERVER['argv'][1])) {
  $basic_concept = $_SERVER['argv'][1];
  $do_all = 0;
  if (isset($_SERVER['argv'][2])) {
    $original_list = array_flip(explode("_AA_", $_SERVER['argv'][2]));
  }
}

//$weightings = $_REQUEST['val_wght_list'];
//$num_to_report = $_REQUEST['num_to_report'];

//$basic_concept = 'Basics_simplify_distribute';
$weights = "9_1__6_1.2__5_1.4__4_1.5__3_1.75__2_2__1_3";
$num_to_report = 4;

//connect to the wwSession database
$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = "wwSession";
$result = mysql_select_db("$db", $con);

if ($do_all == 1) {
  $query = "SELECT concept_bank FROM `conceptBankContentConcepts`";
  $result = mysql_query($query, $con);
  $all_concepts = array();
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $all_concepts[$row['concept_bank']] = $row['concept_bank'];
  }
}
else {
  $all_concepts["$basic_concept"] = "$basic_concept";
}

foreach ($all_concepts as $basic_concept => $blah) {

$original_concept_content_array = array();

//get the original concept_content, stage, and level for the concept_bank.
$query = "SELECT concept_content, stage, level FROM `conceptBankContentConcepts` WHERE concept_bank='" . $basic_concept . "'";
//print "query: $query<BR>";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $this_concept_content = $row['concept_content'];
  $this_stage = $row['stage'];
  $this_level = $row['level'];
  $original_concept_content_array[$this_concept_content]['concept_content'] = $this_concept_content;
  $original_concept_content_array[$this_concept_content]['stage'] = $this_stage;
  $original_concept_content_array[$this_concept_content]['level'] = $this_level;
}




//for each concept_content and stage from the original concept_content,
//Get the matching_concept_bank and the matching_level that match
//also, get a count of how many match.

$matching_concept_bank_array = array();
$score_for_concept_bank_array = array();

foreach ($original_concept_content_array as $this_concept_content => $data) {
  $original_concept_content = $data['concept_content'];
  $original_stage = $data['stage'];
  $original_level = $data['level'];

  $query = "SELECT concept_bank, level FROM `conceptBankContentConcepts` WHERE concept_content='" . $original_concept_content . "' AND stage='" . $original_stage . "'";
//print "query: $query<BR>";
  $result = mysql_query($query, $con);
  $num_matches = mysql_num_rows($result);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $matching_concept_bank = $row['concept_bank'];
    $matching_level = $row['level'];
    $matching_concept_bank_array[$matching_concept_bank]['concept_bank'] = $matching_concept_bank;
    $matching_concept_bank_array[$matching_concept_bank]['level'] = $matching_level;
    $matching_concept_bank_array[$matching_concept_bank]['matches_for_weighting'] = $num_matches;

    //for each match, add a weight * match_value to the match_array with key concept_bank.
    if (!array_key_exists($matching_concept_bank, $score_for_concept_bank_array)) {
      $score_for_concept_bank_array[$matching_concept_bank] = 0;
    }
    $score_for_concept_bank_array[$matching_concept_bank] += score($original_level, $matching_level, $num_matches, $weights);
  }

}

//sort $score_for_concept_bank_array according to cumulative match_value.
arsort($score_for_concept_bank_array);
//print out the highest $num_to_report matching concept banks.
//print "Concept: <B>$basic_concept</B> <BR>";
$i = 1;
$max = 1;
foreach ($score_for_concept_bank_array as $k => $v) {
//  $i++;
  if ($max == 1) { $max = $v; }
//  if ($i <= $num_to_report) {
    $in_original_list = (!($k == $basic_concept) && isset($original_list[$k])) ? "in list" : "";
  if (($i <= $num_to_report) && ($in_original_list == "")) {
//array_key_exists($original_list, $k) ? "in_list" : "NA";
    print "$basic_concept $i: $k ($v  " . ($v/$max) . "% " . $in_original_list . " )<BR>";
    $i++;
  }
}

if (0) {
  print "<HR><PRE>";
  print_r($score_for_concept_bank_array);
  print "</PRE><BR>";
}

}
?>


