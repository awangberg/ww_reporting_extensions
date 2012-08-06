<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$tmp_extra_path = 0;
$extra_path = '';

if (isset($_REQUEST['userDatabaseName'])) {
$db = $_REQUEST['userDatabaseName'];
$problem_id = $_REQUEST['problem_id'];
$user_id = $_REQUEST['user_id'];
}
else if (isset($_SERVER['argv'][1])) {
$db = $_SERVER["argv"][1];
$problem_id = $_SERVER["argv"][2];
$user_id = $_SERVER["argv"][3];
$tmp_extra_path = $_SERVER["argv"][4];
if ($tmp_extra_path == 1) $extra_path = "../";
if ($tmp_extra_path == 2) $extra_path = "../../";
}
else {print 'oops'; }

//select the database $db
if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "Error selecting database $db: " . mysql_error();
}

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
$xml .= "<PROBLEM_QUERY>$query</PROBLEM_QUERY>\n";
$xml .= isset($problem_name) ? "  <Name>" . $problem_name . "</Name>\n" : "  <Name></Name>\n";
$xml .= "  <Problem_ID>" . $problem_id . "</Problem_ID>\n";

$done = false;

//if ((is_null($draw_id) ||($draw_id < 0)) &&
//    (is_null($prompt_key) || ($prompt_key < 0)) && 
//    (is_null($review_key) || ($review_key < 0))) {
//    $done = true;
//}
if ( (!isset($draw_id) || (isset($draw_id) && ($draw_id < 0))) &&
     (!isset($prompt_key) || (isset($prompt_key) && ($prompt_key < 0))) &&
     (!isset($review_key) || (isset($review_key) && ($review_key < 0))) ) {
     $done = true;
}


while (!$done) {
  //(ADW):  Only put in the ReplayPart if it is valid - has a Draw or a Prompt section.
  $tmp_xml = "  <ReplayPart>\n";
  $did_print_this_ReplayPart = false;
  //$xml .= "  <ReplayPart>\n";

  //get the drawing information
  if (!is_null($draw_id) &&($draw_id > 0)) {

     $filename = null;
     $next_draw_id = -1;
     $next_prompt_decision_key = -1;

    $query = "SELECT filename, default_next_draw_id, default_next_prompt_decision_key "
	   . " FROM `draw` WHERE draw_id=" . $draw_id;
//(ADW):  Only include if this is a valid Draw or Prompt section.
$tmp_xml .= "    <Q>" . $query . "</Q>\n";
$tmp_xml .= "    <draw_id>" . $draw_id . "</draw_id>";
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $filename = $row['filename'];
      $next_draw_id = $row['default_next_draw_id'];
      $next_prompt_decision_key = $row['default_next_prompt_decision_key'];
    }

    //get the drawing data from the file $filename
    if (!is_null($filename)) {
      $xml .= $tmp_xml;
      $did_print_this_ReplayPart = true;
      $tmp_xml = "";
      $xml .= "  <Draw>\n<![CDATA[";
      $xml .= file_get_contents($filename);
      $xml .= "]]>\n  </Draw>\n";
      $xml .= "  <Draw_end>" . substr($filename, -6) . "</Draw_end>\n";
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
        //(ADW):  Only include if valid:
        $tmp_xml .= "    <prompt_id>" . $use_this_prompt_id . "</prompt_id>\n";
	$tmp_xml .= "    <Prompt>\n";
      $query = "SELECT type, data, filename, answer_filename, reactToPrompt_key, override_draw_pace "
	     . " FROM `prompt` WHERE prompt_id=" . $use_this_prompt_id;
      $result = mysql_query($query, $con);

      $prompt_xml = "";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$tmp_xml .= "      <Type>" . $row['type'] . "</Type>\n";
	$prompt_xml .= $row['data'];
	if (!is_null($row['override_draw_pace'])) $tmp_xml .= "      <Override_draw_pace>" . $row['override_draw_pace'] . "</Override_draw_pace>\n";
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
      if (!is_null($prompt_xml)) {
        $xml .= $tmp_xml;
        $did_print_this_ReplayPart = true;
        $tmp_xml = "";
        $xml .= "      <Data><![CDATA[" . $prompt_xml . "]]></Data>\n";
        $xml .= "    </Prompt>\n";
      }
    }
  }

  //if (!is_null($reactToPrompt_key) &&($reactToPrompt_key >= -1)) {
  if (isset($reactToPrompt_key) && ($reactToPrompt_key >= -1)) {
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
  if ($did_print_this_ReplayPart) {
    $xml .= "  </ReplayPart>\n";
  }
}

mysql_close($con);

$xml .= "</Problem>\n";
print $xml;

?>
