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

$splitter = $_REQUEST['splitter'];
$user_id = $_REQUEST['user_id'];
$course_id = $_REQUEST['course_id'];
$problemName = check_input($_REQUEST['problemName']);

$recordDrawData = explode($splitter, $_REQUEST['recordDrawData']);
$recordPromptData = explode($splitter, $_REQUEST['recordPromptData']);

$recordDrawData_name = explode($splitter, $_REQUEST['recordDrawData_name']);
$recordPromptData_name = explode($splitter, $_REQUEST['recordPromptData_name']);
$recordPromptData_type = explode($splitter, $_REQUEST['recordPromptData_type']);
$recordPromptData_BloomClassificationID = explode($splitter, $_REQUEST['recordPromptData_BloomClassificationID']);
$recordPromptData_BloomQuestionID = explode($splitter, $_REQUEST['recordPromptData_BloomQuestionID']);

$problem_for_wwSet = explode($splitter, $_REQUEST['problem_for_wwSet']);
$wwSetCount = $_REQUEST['wwSetCount'];

$problem_for_wwProblem = explode($splitter, $_REQUEST['problem_for_wwProblem']);
$wwProblemCount = $_REQUEST['wwProblemCount'];

$learning_major_minor_skills = explode($splitter, $_REQUEST['learning_major_minor_skills']);
$learning_major_minor_count = $_REQUEST['learning_major_minor_count'];

$assumed_major_minor_skills = explode($splitter, $_REQUEST['assumed_major_minor_skills']);
$assumed_major_minor_count = $_REQUEST['assumed_major_minor_count'];

//Now, all the data has been separated!

$ret_query = "";

//Insert the problem into the problem table:

$query = "INSERT INTO problem (name, type) VALUES ($problemName, 'selfExplain')";
$result = mysql_query($query, $con);
$problem_id = mysql_insert_id();

$ret_query .= "Query: |$query  -> $result|";

$draw_id = -1;
$initial_draw_id = -1;
$previous_draw_id = -1;

$initial_prompt_decision_key = -1;
$react_to_prompt_null_id = -1;

if ($react_to_prompt_null_id == -1) {
  $query = "SELECT id FROM react_to_prompt WHERE react_key=-1";
  $result = mysql_query($query, $con);

  $ret_query .= "Query: |$query -> $result|";

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $react_to_prompt_null_id = $row['id'];
  }
      
  if ($react_to_prompt_null_id == -1) {
    $query = "INSERT INTO react_to_prompt (react_key, internal_order, true_condition, override_next_draw_id, override_next_prompt_decision_key) "
           . " VALUES(-1, 1, 'true', -1, -1)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    $react_to_prompt_null_id = mysql_insert_id();
  }
}

$prompt_decision_null_id = -1;
if ($prompt_decision_null_id == -1) {
  $query = "SELECT id FROM prompt_decision WHERE prompt_decision_key=-1";
  $result = mysql_query($query, $con);

  $ret_query .= "Query: |$query -> $result|";
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $prompt_decision_null_id = $row['id'];
  }
      
  if ($prompt_decision_null_id == -1) {
    $query = "INSERT INTO prompt_decision (prompt_decision_key, internal_order, true_condition, prompt_id) "
           . " VALUES(-1, 1, 'true', -1)";
    $result = mysql_query($query, $con);

    $ret_query .= "Query: |$query -> $result|";
    $prompt_decision_null_id = mysql_insert_id();
  }
}


for ($i = 0; $i < count($recordDrawData); $i++) {

  //Insert the drawing information into the draw table:
  $query = "INSERT INTO draw (name) VALUE (" . check_input($recordDrawData_name[$i]). ")";
  $result = mysql_query($query, $con);

  $ret_query .= "Query: |$query -> $result|";
  $draw_id = mysql_insert_id();
  if ($initial_draw_id == -1) $initial_draw_id = $draw_id;

  //update the previous draw_id default_next_draw_id to point to this one:
  if ($previous_draw_id > -1) {
    $query = "UPDATE draw SET default_next_draw_id=$draw_id WHERE draw_id=$previous_draw_id";
    $result = mysql_query($query, $con);
    $ret_query .= "Query |$query -> $result|";
  }

  //write the contents of $recordDrawData[$i], and store in draw $draw_id
  if (strlen($recordDrawData[$i])>4) {
    $tmpfname = tempnam("/opt/session/drawings", "draw" . $draw_id . "_");

    $handle = fopen($tmpfname, "w");
    fwrite($handle, $recordDrawData[$i]);
    fclose($handle);

    //Insert the drawing information into the draw table:
    $query = "UPDATE draw SET filename=" . check_input($tmpfname) . " WHERE draw_id=$draw_id";
    $result = mysql_query($query, $con);
    $ret_query .= "Query |$query -> $result|";
  }

  if (strlen($recordPromptData[$i])>4) {
    //write the prompt_decision information
    $query = "INSERT INTO prompt_decision (true_condition, internal_order) VALUES ('true', 1)";
    $result = mysql_query($query, $con);
    $prompt_decision_id = mysql_insert_id();
    //we'll update the prompt_id later.

    $prompt_decision_key = $prompt_decision_id;
    //put in the prompt_decision_key
    $query = "UPDATE prompt_decision SET prompt_decision_key=$prompt_decision_key WHERE id=$prompt_decision_id";
    $result = mysql_query($query, $con);
    $ret_query .= "Query |$query -> $result|";

    if ($initial_prompt_decision_key == -1) $initial_prompt_decision_key = $prompt_decision_key;
    if ($previous_draw_id > -1) {
      //update the previous draw record so the default_next_prompt_decision_key is correct.
      $query = "UPDATE draw SET default_next_prompt_decision_key=$prompt_decision_key WHERE draw_id=$previous_draw_id";
      $result = mysql_query($query, $con);
      $ret_query .= "Query |$query -> $result|";
    }

    $query = "INSERT INTO prompt (name, type, blooms_question_id, blooms_classification_id, reactToPrompt_key) "
           . " VALUES (" . check_input($recordPromptData_name[$i]) . ", " . check_input($recordPromptData_type[$i]) 
           . ", $recordPromptData_BloomQuestionID[$i], $recordPromptData_BloomClassificationID[$i], -1)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query |$query -> $result|";
    $prompt_id = mysql_insert_id();

    if (preg_match("/^Draw/", $recordPromptData[$i]) || preg_match("/Graph/", $recordPromptData[$i])) {

      $setup_index = -1;
      $answer_index = -1;

      if (preg_match("/^Draw/", $recordPromptData[$i])) {
        $setup_index = 1;
        $answer_index = 2;
      }
      if (preg_match("/^Graph/", $recordPromptData[$i])) {
        $setup_index = 4;
        $answer_index = 5;

	if (preg_match("/GraphAnalyzeGraphically/", $recordPromptData[$i])) {
	  $answer_index = -1;
        }
	if (preg_match("/GraphAnalyzeAlgebraically/", $recordPromptData[$i])) {
	  $answer_index = -1;
	  $setup_index = -1;
        }
      }


      $major_prompt_parts = explode(';_;', $recordPromptData[$i]);
      $draw_parts = explode(';^;', $major_prompt_parts[2]);

      if (($setup_index >= 0) && (strlen($draw_parts[$setup_index]) > 4)) {
	$draw_setup = $draw_parts[$setup_index];
        $tmp_setup_fname = tempnam("/opt/session/drawings", "prompt" . $prompt_id . "_setup_");

        $handle = fopen($tmp_setup_fname, "w");
        fwrite($handle, $draw_setup);
        fclose($handle);

        //Insert the drawing information into the prompt table:
        $query = "UPDATE prompt SET filename=" . check_input($tmp_setup_fname) . " WHERE prompt_id=$prompt_id";
        $result = mysql_query($query, $con);
        $ret_query .= "Query |$query -> $result|";
        $draw_parts[$setup_index] = "USE_FILENAME";
      }

      if (($answer_index >= 0) && (strlen($draw_parts[$answer_index]) > 4)) {
	$draw_answer = $draw_parts[$answer_index];
        $tmp_answer_fname = tempnam("/opt/session/drawings", "prompt" . $prompt_id . "_answer_");

        $handle = fopen($tmp_answer_fname, "w");
        fwrite($handle, $draw_answer);
        fclose($handle);

        //Insert the drawing information into the prompt table:
        $query = "UPDATE prompt SET answer_filename=" . check_input($tmp_answer_fname) . " WHERE prompt_id=$prompt_id";
        $result = mysql_query($query, $con);
        $ret_query .= "Query |$query -> $result|";
        $draw_parts[$answer_index] = "USE_ANSWER_FILENAME";
      }

      $major_prompt_parts[2] = implode(";^;", $draw_parts);
      $recordPromptData[$i] = implode(";_;", $major_prompt_parts);
    }

    //update the data in prompt record with id $prompt_id
    $query = "UPDATE prompt SET data=" . check_input($recordPromptData[$i]) . " WHERE prompt_id=$prompt_id";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    //update the prompt_id in prompt_decision:
    $query = "UPDATE prompt_decision SET prompt_id=$prompt_id WHERE id=$prompt_decision_id";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    //set up the react_to_prompt table
    //if we need a new react_to_prompt entry, then
    //we need to also modify the reactToPrompt_key in the prompt record.

    //order would be:
	// 1.  put new data into react_to_prompt table.
	//   1.a.  if the prompt has a reactTo_prompt_key other than -1, use it.
	//   1.b.  otherwise, use the new react_to_prompt id for reactToPrompt_key
        // 2.  update the reactToPrompt_key field in the $prompt_id record
	// 3.  adjust the internal_order for all records in react_to_prompt with the correct react_key

    $previous_draw_id = $draw_id;
  }
}

//update the original problem now, inserting the value for
//initial_draw_id, 
//initial_prompt_decision_key, and
//initial_review_decision_key

$query = "UPDATE problem SET initial_prompt_decision_key=$initial_prompt_decision_key, initial_draw_id=$initial_draw_id, initial_review_decision_key=-1 WHERE problem_id=$problem_id";
$result = mysql_query($query, $con);
$ret_query .= "Query: |$query -> $result|";

//Now, update the tables connecting this session problem to webwork sets or problems.

//get the coursename for this course
$query = "SELECT course_name FROM `course` WHERE course_id=" . $course_id;
$result = mysql_query($query, $con);
$ret_query .= "Query: |$query -> $result|";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $course_name = $row['course_name'];
}

$array_of_ww_set_and_course_id = array();
$array_of_ww_problem_id = array();

for ($i = 0; $i < count($problem_for_wwSet); $i++) {
  if ($problem_for_wwSet[$i] != "") {
    $ww_set_and_course_id = -1;
    //get the set id for this set from wwSetInSession
    $query = "SELECT ww_set_and_course_id FROM `wwSetInSession` "
  	   . " WHERE ww_course_name = " . check_input($course_name) . " AND ww_set_id=" . check_input($problem_for_wwSet[$i]);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $ww_set_and_course_id = $row['ww_set_and_course_id'];
    }

    if ($ww_set_and_course_id == -1) {
      $query = "INSERT INTO wwSetInSession (ww_course_name, ww_set_id) "
	     . " VALUES (" . check_input($course_name) . ", " . check_input($problem_for_wwSet[$i]) . ")";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result|";
      $ww_set_and_course_id = mysql_insert_id();
    }

    $query = "INSERT INTO wwSetToSessionTutorial (ww_set_and_course_id, session_problem_id) "
	   . " VALUES ($ww_set_and_course_id, $problem_id)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    array_push($array_of_ww_set_and_course_id, $ww_set_and_course_id);
  }
}

//Now, update the tables connecting this session problem to webwork problems.
for ($i = 0; $i < count($problem_for_wwProblem); $i++) {
  if ($problem_for_wwProblem[$i] != "") {
    $ww_problem_id = -1;
    //get the ww_problem_id for this problem form wwProblemInSession
    $query = "SELECT ww_problem_id FROM `wwProblemInSession` "
	   . " WHERE ww_problem_file = " . check_input($problem_for_wwProblem[$i]);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $ww_problem_id = $row['ww_problem_id'];
    }

    if ($ww_problem_id == -1) {
      $query = "INSERT INTO wwProblemInSession (ww_problem_file) "
	     . " VALUE (" . check_input($problem_for_wwProblem[$i]) . ")";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result|";
      $ww_problem_id = mysql_insert_id();
    }

    $query = "INSERT INTO wwProblemToSessionTutorial (ww_problem_id, session_problem_id) "
	   . " VALUES ($ww_problem_id, $problem_id)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    array_push($array_of_ww_problem_id, $ww_problem_id);
  }
}



for ($i = 0; $i < count($learning_major_minor_skills); $i++) {
  $major_skill_id = -1;
  $minor_skill_id = -1;

  //get the major and minor skill id's from major_skill and minor_skill tables.

  list($major_skill_name, $minor_skill_name) = explode(" MinorSkill ", $learning_major_minor_skills[$i]);

  if (($major_skill_name != "") && ($minor_skill_name != "")) {
    $query = "SELECT id FROM `major_skill` WHERE name=" . check_input($major_skill_name);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $major_skill_id = $row['id'];
    }

    $query = "SELECT id FROM `minor_skill` WHERE name=" . check_input($minor_skill_name);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $minor_skill_id = $row['id'];
    }

    //now that we have the major and minor skill id's,
    //grab the connect_major_minor_skill id
    $connect_major_minor_skill_id = -1;
    $query = "SELECT id FROM `connect_major_minor_skill` WHERE major_skill_id=" . $major_skill_id . " AND minor_skill_id=" . $minor_skill_id;
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $connect_major_minor_skill_id = $row['id'];
    }

    //Now, insert this into the table for the sesion problem
    $query = "INSERT INTO learning_skills_from_problem (connect_major_minor_skill_id, problem_id) "
	   . " VALUES ($connect_major_minor_skill_id, $problem_id)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    //and the table for the webwork problem (if it doesn't exist)
    foreach ($array_of_ww_problem_id as $wwProblemId) {
      $existing_id = -1;
      $query = "SELECT id FROM `learning_skills_from_wwProblemOrSet` "
	     . " WHERE ww_problem_id = $wwProblemId AND connect_major_minor_skill_id = $connect_major_minor_skill_id";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $existing_id = $row['id'];
      }

      if ($existing_id == -1) {
        $query = "INSERT INTO learning_skills_from_wwProblemOrSet (ww_problem_id, connect_major_minor_skill_id) "
  	     . " VALUES ($wwProblemId, $connect_major_minor_skill_id)";
        $result = mysql_query($query, $con);
        $ret_query .= "Query: |$query -> $result|";
      }
    }

    //and the table for the webwork sets (if it doesn't exist)
    foreach ($array_of_ww_set_and_course_id as $wwSetAndCourse) {
      $existing_id = -1;
      $query = "SELECT id FROM `learning_skills_from_wwProblemOrSet` "
	     . " WHERE ww_set_and_course_id = $wwSetAndCourse AND connect_major_minor_skill_id = $connect_major_minor_skill_id";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result|";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $existing_id = $row['id'];
      }

      if ($existing_id == -1) {
        $query = "INSERT INTO learning_skills_from_wwProblemOrSet (ww_set_and_course_id, connect_major_minor_skill_id) "
	       . " VALUES ($wwSetAndCourse, $connect_major_minor_skill_id)";
        $result = mysql_query($query, $con);
        $ret_query .= "Query: |$query -> $result|";
      }
    }
  }
}



for ($i = 0; $i < count($assumed_major_minor_skills); $i++) {
 
  $major_skill_id = -1;
  $minor_skill_id = -1;

  //get the major and minor skill id's from major_skill and minor_skill tables.

  list($major_skill_name, $minor_skill_name) = explode(" MinorSkill ", $assumed_major_minor_skills[$i]);

  if (($major_skill_name != "") && ($minor_skill_name != "")) {
    $query = "SELECT id FROM `major_skill` WHERE name=" . check_input($major_skill_name);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $major_skill_id = $row['id'];
    }

    $query = "SELECT id FROM `minor_skill` WHERE name=" . check_input($minor_skill_name);
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $minor_skill_id = $row['id'];
    }

    //now that we have the major and minor skill id's,
    //grab the connect_major_minor_skill id
    $connect_major_minor_skill_id = -1;
    $query = "SELECT id FROM `connect_major_minor_skill` WHERE major_skill_id=" . $major_skill_id . " AND minor_skill_id=" . $minor_skill_id;
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $connect_major_minor_skill_id = $row['id'];
    }

    //Now, insert this into the table for the sesion problem
    $query = "INSERT INTO assumed_skills_for_problem (connect_major_minor_skill_id, problem_id) "
	   . " VALUES ($connect_major_minor_skill_id, $problem_id)";
    $result = mysql_query($query, $con);
    $ret_query .= "Query: |$query -> $result|";

    //and the table for the webwork problem (if it doesn't exist)
    foreach ($array_of_ww_problem_id as $wwProblemId) {
      $existing_id = -1;
      $query = "SELECT id FROM `assumed_skills_for_wwProblemOrSet` "
	     . " WHERE ww_problem_id = $wwProblemId AND connect_major_minor_skill_id = $connect_major_minor_skill_id";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result|";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $existing_id = $row['id'];
      }

      if ($existing_id == -1) {
        $query = "INSERT INTO assumed_skills_for_wwProblemOrSet (ww_problem_id, connect_major_minor_skill_id) "
	       . " VALUES ($wwProblemId, $connect_major_minor_skill_id)";
        $result = mysql_query($query, $con);
        $ret_query .= "Query: |$query -> $result|";
      }
    }

    //and the table for the webwork sets (if it doesn't exist)
    foreach ($array_of_ww_set_and_course_id as $wwSetAndCourse) {
      $existing_id = -1;
      $query = "SELECT id FROM `assumed_skills_for_wwProblemOrSet` "
	     . " WHERE ww_set_and_course_id = $wwSetAndCourse AND connect_major_minor_skill_id = $connect_major_minor_skill_id";
      $result = mysql_query($query, $con);
      $ret_query .= "Query: |$query -> $result|";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $existing_id = $row['id'];
      }

      if ($existing_id == -1) {
        $query = "INSERT INTO assumed_skills_for_wwProblemOrSet (ww_set_and_course_id, connect_major_minor_skill_id) "
	       . " VALUES ($wwSetAndCourse, $connect_major_minor_skill_id)";
        $result = mysql_query($query, $con);
        $ret_query .= "Query: |$query -> $result|";
      }
    }
  }
}

mysql_close($con);


print "result=" . $problemName . "ANDsplitter_is_" . $splitter . "_";
foreach ($recordDrawData_name as $name) {
  print "name--" . $name . "__________";
}

print "............................";

foreach ($assumed_major_minor_skills as $minor_skill) {
  print "minor_skill--" . $minor_skill . "__________";
}

print "&query=$ret_query\n";

?>
