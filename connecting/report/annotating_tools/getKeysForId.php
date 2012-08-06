<?php

include("../../access.php");
include("../common.php");

$db = "wwSession";

$con = mysql_connect($db_host, $db_user, $db_pass);
if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$result = mysql_select_db("$db", $con);

$problem_id = $_REQUEST['problem_id'];
$comment_id = $_REQUEST['comment_id'];

$query = "SELECT sessionCommentKeysPossible.shortkey FROM `sessionCommentKeys` LEFT JOIN `sessionCommentKeysPossible` ON sessionCommentKeys.key_id = sessionCommentKeysPossible.key_id WHERE sessionCommentKeys.session_problem_id=" . $problem_id . " AND sessionCommentKeys.comment_id=" . $comment_id . " AND sessionCommentKeys.record_valid=TRUE";

$result = mysql_query($query, $con);
$count = 0;
$ret;

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $desc = $row['shortkey'];
  $ret[] = $desc;
}

mysql_close($con);

//$ret[$count] = array('id' => '2', 'label' => 'value2', 'value' => 'value2', 'desc' => 'this is desc2');

//print "query is $query";
print implode(", ", $ret);

?>
