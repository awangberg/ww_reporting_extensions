<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
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

$course_id = $_REQUEST['course_id'];


$query = "SELECT course_name FROM `course` WHERE course_id=" . $course_id;
$result = mysql_query($query, $con);
$course_name = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $course_name = $row['course_name'];
}

$prompt_decision_key_to_prompt_type = array();
$prompt_decision_key_to_prompt_decorations = array();
$query = "SELECT prompt.type, prompt.data, prompt_decision.id FROM `prompt_decision` LEFT JOIN `prompt` ON prompt_decision.prompt_id = prompt.prompt_id WHERE 1";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $prompt_decision_key_to_prompt_type[$row['id']] = $row['type'];
  if (preg_match("/MovableButton/", $row['data'])) {
    $prompt_decision_key_to_prompt_decorations[$row['id']] = "s";
  }
  else {
    $prompt_decision_key_to_prompt_decorations[$row['id']] = " ";
  }

  if (preg_match("/;\^;Y;_;/", $row['data'])) {
    $prompt_decision_key_to_prompt_decorations[$row['id']] .= "y";
  }
  else if (preg_match("/;\^;C;_;/", $row['data'])) {
    $prompt_decision_key_to_prompt_decorations[$row['id']] .= "c";
  }
  else {
    $prompt_decision_key_to_prompt_decorations[$row['id']] .= " ";
  }
}


$draw_id_to_prompt_type_letter = array();
$query = "SELECT draw_id, default_next_prompt_decision_key FROM `draw` WHERE 1";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $draw_id_to_prompt_type_letter[$row['draw_id']] = $prompt_decision_key_to_prompt_type[$row['default_next_prompt_decision_key']][0];
  $draw_id_to_prompt_type_letter[$row['draw_id']] .= $prompt_decision_key_to_prompt_decorations[$row['default_next_prompt_decision_key']];
}

$next_draw_part_for_draw_id = array();
$query = "SELECT draw_id, default_next_draw_id FROM `draw` WHERE 1";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $next_draw_part_for_draw_id[$row['draw_id']] = $row['default_next_draw_id'];
}



$xml = "<Problems>\n";
$query =  "SELECT DISTINCT problem.initial_draw_id, problem.initial_prompt_decision_key, problem.problem_id, problem.name, wwSetInSession.ww_set_id FROM `problem` "
	. "LEFT JOIN wwSetToSessionTutorial ON problem.problem_id = wwSetToSessionTutorial.session_problem_id "
	. "LEFT JOIN wwSetInSession ON wwSetToSessionTutorial.ww_set_and_course_id = wwSetInSession.ww_set_and_course_id "	
	. " WHERE ww_course_name='" . $course_name . "'"
	. " ORDER BY problem_id ASC";
$result = mysql_query($query, $con);
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $xml .= "  <Problem>\n";
  $xml .= "    <id>" . $row['problem_id'] . "</id>\n";
  $xml .= "    <name>" . $row['name'] . "</name>\n";
  $xml .= "    <wwSet>" .  $row['ww_set_id'] . "</wwSet>\n";
  $xml .= "    <question_attribute>";
  $question_attribute = "";
  $question_attribute = $prompt_decision_key_to_prompt_type[$row['initial_prompt_decision_key']][0] . $prompt_decision_key_to_prompt_decorations[$row['initial_prompt_decision_key']] . " ";
  $get_next_attribute_for_draw_id = $row['initial_draw_id'];
  while ($get_next_attribute_for_draw_id > 0) {
    $question_attribute .= $draw_id_to_prompt_type_letter[$get_next_attribute_for_draw_id] . " ";
    $get_next_attribute_for_draw_id = $next_draw_part_for_draw_id[$get_next_attribute_for_draw_id];
  }
  $xml .= $question_attribute . "</question_attribute>\n";
  $xml .= "  </Problem>\n";
}
$xml .= "</Problems>\n";

mysql_close($con);

print $xml;

?>
