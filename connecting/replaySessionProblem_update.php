<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

//$db = $_REQUEST['userDatabaseName'];
$db = "session";

//select the database $db
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "Error selecting database $db: " . mysql_error();
}

//$problem_id = $_REQUEST['problem_id'];
//$user_id = $_REQUEST['user_id'];
$problem_id = 537;
$user_id = 1;


//check if the user has completed the problem and has previous answers?

$query  = "SELECT initial_draw_id, initial_prompt_decision_key, initial_review_decision_key, name "
	. " FROM `problem` WHERE problem_id=" . $problem_id;

$result = mysql_query($query, $con);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $draw_id = $row['initial_draw_id'];
  $prompt_key = $row['initial_prompt_decision_key'];
  $review_key = $row['initial_review_decision_key'];
  $problem_name = $row['name'];
}

$xml = "<Problem>\n";
$xml .= "  <Name>" . $problem_name . "</Name>\n";
$xml .= "  <Problem_ID>" . $problem_id . "</Problem_ID>\n";

$done = false;

if ((is_null($draw_id) ||($draw_id < 0)) &&
    (is_null($prompt_key) || ($prompt_key < 0)) && 
    (is_null($review_key) || ($review_key < 0))) {
    $done = true;
}

while (!$done) {
  $xml .= "  <ReplayPart>\n";
  //get the drawing information
  if (!is_null($draw_id) &&($draw_id > 0)) {

     $filename = null;
     $next_draw_id = -1;
     $next_prompt_decision_key = -1;

    $query = "SELECT filename, default_next_draw_id, default_next_prompt_decision_key "
	   . " FROM `draw` WHERE draw_id=" . $draw_id;
$xml .= "    <Q>" . $query . "</Q>\n";
$xml .= "    <draw_id>" . $draw_id . "</draw_id>";
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $filename = $row['filename'];
      $next_draw_id = $row['default_next_draw_id'];
      $next_prompt_decision_key = $row['default_next_prompt_decision_key'];
    }

    //get the drawing data from the file $filename
    if (!is_null($filename)) {
      $xml .= "  <Draw>\n";
      $xml .= file_get_contents($filename);
      $xml .= "  </Draw>\n";
    }
  }

  if (!is_null($prompt_key) &&($prompt_key > 0)) {
    $use_this_prompt_id = -1;
    $true_condition = false;
    $query = "SELECT true_condition, prompt_id "
	   . " FROM `prompt_decision` WHERE prompt_decision_key=" . $prompt_key
	   . " ORDER BY internal_order ASC";

    $result = mysql_query($query, $con);
    $use_this_prompt_id = -1;
    $found_true_condition = false;
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $true_condition = $row['true_condition'];

      if ($true_condition && !$found_true_condition) {
	$use_this_prompt_id = $row['prompt_id'];
	$found_true_condition = true;
      }
    }

    $reactToPrompt_key = -1;
    $filename = null;
    $answer_filename = null;
    if ($use_this_prompt_id > -1) {
        $xml .= "    <prompt_id>" . $use_this_prompt_id . "</prompt_id>\n";
	$xml .= "    <Prompt>\n";
      $query = "SELECT type, data, filename, answer_filename, reactToPrompt_key, override_draw_pace "
	     . " FROM `prompt` WHERE prompt_id=" . $use_this_prompt_id;
      $result = mysql_query($query, $con);

      $prompt_xml = "";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$xml .= "      <Type>" . $row['type'] . "</Type>\n";
	$prompt_xml .= $row['data'];
	if (!is_null($row['override_draw_pace'])) $xml .= "      <Override_draw_pace>" . $row['override_draw_pace'] . "</Override_draw_pace>\n";
        $filename = $row['filename'];
        $answer_filename = $row['answer_filename'];
        $reactToPrompt_key = $row['reactToPrompt_key'];
      }
      if (!is_null($filename)) {
        //$xml .= "        <PromptDraw>\n";
        //$xml .= file_get_contents($filename);
        //$xml .= "        </PromptDraw>\n";
        $filename_contents = file_get_contents($filename);
        $prompt_xml = preg_replace('/USE_FILENAME/', $filename_contents, $prompt_xml);
      }
      if (!is_null($answer_filename)) {
        $answer_filename_contents = file_get_contents($answer_filename);
        $prompt_xml = preg_replace('/USE_ANSWER_FILENAME/', $answer_filename_contents, $prompt_xml);
      }

      $xml .= "      <Data>" . $prompt_xml . "</Data>\n";
      $xml .= "    </Prompt>\n";
    }
  }

  if (!is_null($reactToPrompt_key) &&($reactToPrompt_key >= -1)) {
    $query = "SELECT true_condition, override_next_draw_id, override_next_prompt_decision_key "
	   . " FROM `react_to_prompt` WHERE react_key=" . $reactToPrompt_key
	   . " ORDER BY internal_order ASC";
    $result = mysql_query($query, $con);
    $found_true_condition = false;
    $override_next_prompt_key = -1;
    $override_next_draw_id = -1;
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      if (!$found_true_condition && $row['true_condition']) {
        $override_next_draw_id = $row['override_next_draw_id'];
	$override_next_prompt_key = $row['override_next_prompt_decision_key'];
        $found_true_condition = true;
      }
    }
    if ($override_next_draw_id > -1) $next_draw_id = $override_next_draw_id;
    if ($override_next_prompt_key > -1) $next_prompt_decision_key = $override_next_prompt_key;
  }

  $draw_id = $next_draw_id;
  $prompt_key = $next_prompt_decision_key;

  if ((is_null($draw_id) ||($draw_id < 0)) &&
      (is_null($prompt_key) || ($prompt_key < 0)) && 
      (is_null($review_key) || ($review_key < 0))) {
      $done = true;
  }
  $xml .= "  </ReplayPart>\n";
}

mysql_close($con);

$xml .= "</Problem>\n";
print $xml;

?>
