<?php

include("../../access.php");
include("../common.php");

$db = "wwSession";

$con = mysql_connect($db_host, $db_user, $db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$result = mysql_select_db("$db", $con);

$searchTerm = addslashes($_REQUEST['term']);

$query = "SELECT key_id, shortkey, key_description FROM `sessionCommentKeysPossible` WHERE shortkey LIKE '%$searchTerm%'";

$result = mysql_query($query, $con);
$count = 0;
$ret[$count] = array('id' => '0', 'label' => 'new', 'value' => 'new', 'desc' => 'Create a new Key');
$count++;


while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $id = $row['key_id'];
  $key = $row['shortkey'];
  $desc = $row['key_description'];
  $ret[$count] = array('id' => "$id", 'label' => "$key", 'value'=>"$key", 'desc' =>"$desc");
  $count++;
}
mysql_close($con);

//$ret[$count] = array('id' => '2', 'label' => 'value2', 'value' => 'value2', 'desc' => 'this is desc2');


print json_encode($ret);

?>
