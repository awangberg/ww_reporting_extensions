<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$session_course_id = check_input($_REQUEST['course_id']);

$major_skill_name = check_input($_REQUEST['major_skill']);
$major_skill_id;

$query = 'SELECT name, id FROM `major_skill` WHERE name=' . $major_skill_name .' AND is_current=1';
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $major_skill_id = $row['id'];
}

//List all of the minor skills available:
$query = 'SELECT name, id FROM `minor_skill` WHERE is_current=1';
$result = mysql_query($query, $con);
$minor_list = "";
$minor_id_name_array;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $minor_id_name_array[$row['id']] = $row['name'];  
}

$return_string = "<Minor_Skills>\n";
$query = 'SELECT id, minor_skill_id FROM `connect_major_minor_skill` WHERE major_skill_id='. $major_skill_id .' AND is_current=1';
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $return_string .= "  <Minor_Skill>\n";
  $return_string .= "    <name>" . $minor_id_name_array[$row['minor_skill_id']] . "</name>\n";
  $return_string .= "    <major_id>" . $major_skill_id . "</major_id>\n";
  $return_string .= "    <minor_id>" . $row['minor_skill_id'] . "</minor_id>\n";
  $return_string .= "    <connect_major_minor_skill_id>" . $row['id'] . "</connect_major_minor_skill_id>\n";
  $return_string .= "  </Minor_Skill>\n";
}
$return_string .= "</Minor_Skills>\n";
mysql_close($con);

print $return_string;

?>
