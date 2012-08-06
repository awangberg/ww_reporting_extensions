<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "wwSession";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database wwSession:
if (mysql_select_db("$db", $con)) {
  echo "<BR>selected database $db";
}
else {
  echo "<BR>Error selecting database $db: " . mysql_error();
}

echo "<P>Create the database table: taxonomy";

$sql = "CREATE TABLE IF NOT EXISTS taxonomy
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
tax_key text,
short_desc text,
description text
)";

if (mysql_query($sql, $con)) {
  echo "<BR>Table taxonomy created.";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
}

echo "<P>Create the database table: pca_taxonomy";

$sql = "CREATE TABLE IF NOT EXISTS pca_taxonomy
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
taxonomy_id INT,
webwork_set text
)";

if (mysql_query($sql, $con)) {
  echo "<BR>Table pca_taxonomy created";
}
else {
  echo "<BR>Error creating table: " .  mysql_error();
}

mysql_close($con);
?>
