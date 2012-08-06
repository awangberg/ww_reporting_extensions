<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "wwSession";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table sessionComments:
//DROP TABLE 'sessionComments';

echo "<P>Select the database $db";

if (mysql_select_db("$db", $con)) {
  echo "<BR>selected database $db";
}
else {
  echo "<BR>Error selecting database $db: " . mysql_error();
}

echo "<P>Create the table: sessionComments";


$sql = "CREATE TABLE IF NOT EXISTS sessionComments
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
session_problem_id INT,
commenter text,
comment text,
replay_time_for_comment_ms INT,
replay_time_for_comments_human_seconds INT,
comment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
response_to_comment_id INT
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table sessionComments created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: sessionCommentKeys";

$sql = "CREATE TABLE IF NOT EXISTS sessionCommentKeys
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
session_problem_id INT,
comment_id INT,
key_id INT,
record_valid BOOL
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table sessionCommentKeys created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}


echo "<P>Create the table: sessionCommentKeysPossible";

$sql = "CREATE TABLE IF NOT EXISTS sessionCommentKeysPossible
(
key_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(key_id),
shortkey text,
key_description text
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table sessionCommentKeys created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

mysql_close($con);
?>
