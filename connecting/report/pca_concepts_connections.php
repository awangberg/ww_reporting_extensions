<?php

ini_set("memory_limit", "45M");

include("../access.php");
include("common.php");

function pc_array_power_set($array) {
print_r($array);
  //initialize by adding the empty set
  $results = array(array( ));
  foreach ($array as $element) 
    foreach ($results as $combination)
      array_push($results, array_merge(array($element), $combination));

  return array_values($results);
}

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'wwSession';
$result = mysql_select_db("$db", $con);

$table = 'taxonomy';

$sql = "SELECT id, tax_key, short_desc, description FROM `$table`";

$result = mysql_query($sql, $con);

$tax_ids = '';
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $id = $row['id'];
  $tax_ids["$id"][] = "$id";
  $tax_ids["$id"]['tax_key'] = $row['tax_key'];
  $tax_ids["$id"]['short_desc'] = $row['short_desc'];
}

$table = 'pca_taxonomy';

$sql = "SELECT taxonomy_id, webwork_set FROM `$table`";

print "<P>$sql";

$result = mysql_query($sql, $con);

$tax_ids_for_question = '';
$question_ids_for_tax = '';

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $tax_id = $row['taxonomy_id'];
  $ww_set = $row['webwork_set'];
  $tax_ids_for_question[$ww_set][] = $tax_id;
  $question_ids_for_tax[$tax_id][] = $ww_set;
}

print "<P>Taxonomy ids for question array:<BR>";
print_r($tax_ids_for_question);
print "<P>Questions for taxonomy id array:<BR>";
print_r($question_ids_for_tax);
print "<P>";

$count_of_concept_scenarios = '';

for ($i = 0; $i <= 25; $i++) {
  $pca_q = "pca_q$i";
  $tmp_a = pc_array_power_set(array_values($tax_ids_for_question["$pca_q"]));
  foreach ($tmp_a as $key => $val) {
    asort($val);
//    print "key is $key and val is " . join(", ", $val) . "<P>";
    $label = join(", ", $val);
    $count_of_concept_scenarios[$label][] = $i;
  }
}

print "<P>tax ids<P>";

print_r($tax_ids);

print "<P><P>";

foreach ($count_of_concept_scenarios as $key => $val) {
  $keys = explode(", ", $key);
  print "Concept(s): ";
  $comma = "";
  foreach ($keys as $this_key => $this_val) {
    print $comma;
    $tmp_array = $tax_ids[$$this_val];
    print $tax_ids["$this_val"]['short_desc'];
    $comma = ", ";
  }
  print " => pca questions " . join(", ", $val) . " <P>";
}

print "<P>PCA questions paired mapped to concepts: <P>";

$count_of_question_scenarios = '';

for ($i = 1; $i <= 15; $i++) {
  $tax_id = $i;
  print "<P>working on $tax_id: ";
  print join(", ", $question_ids_for_tax[$tax_id]);

//  $tmp_a = pc_array_power_set($question_ids_for_tax[$tax_id]);
//  foreach ($tmp_a as $key => $val) {
////    asort($val);
//    print "key is $key and val is " . join(", ", $val) . "<P>";
//    $label = join(", ", $val);
//    $count_of_question_scenarios[$label][] = $i;
//  }
}

foreach ($count_of_question_scenarios as $key => $val) {
  print "pca question $key => tax concepts " . join(", ", $val) . " <P>";
}

?>
