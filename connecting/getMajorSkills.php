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

$query = 'SELECT name, id FROM `major_skill` WHERE is_current=1';
$result = mysql_query($query, $con);
$return_string = "<Major_Skills>\n";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $return_string .= "  <Major_Skill>\n";
  $return_string .= "    <name>" . $row['name'] . "</name>\n";
  $return_string .= "    <id>" . $row['id'] . "</id>\n";
  $return_string .= "  </Major_Skill>\n";
}
$return_string .= "</Major_Skills>\n";
mysql_close($con);

print $return_string;

?>
