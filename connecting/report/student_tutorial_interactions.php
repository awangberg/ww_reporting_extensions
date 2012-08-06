<?php

include("../access.php");
include("common.php");

function tablize($a) {
  $s = "";
  foreach($a as $akey => $avalue) {
    $s .= "$akey: $avalue;\\n";
  }
  return $s;
}

if ($_REQUEST['main_key']) {
  $con = mysql_connect($db_host, $db_user, $db_pass);
  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "session";

  //select the database $db
  $result = mysql_select_db("$db", $con);

  $student_ids = array();

  $query = 'SELECT user_id, course_id, user_name, first_name, last_name FROM `user`';
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $student_ids[$row['user_id']] = array(	'course_id' => $row['course_id'],
						'user_name' => $row['user_name'],
						'first_name' => $row['first_name'],
						'last_name' => $row['last_name']
					 );
  }


  $all_student_answer_data = array();
  $answer_id_keys_into_all_student_answer_data = array();
  $problem_id_keys_into_all_student_answer_data = array();
  $student_id_keys_into_all_student_answer_data = array();
  $tutorial_keys_into_all_student_answer_data = array();

  $all_viewed_data = array();

  $query = 'SELECT answer_id, answer_key, tutorial_key, internal_order, problem_id, draw_id, prompt_id, student_id, viewed_key, reviewer_user_id, review_key, answer, filename, askForReason, reason, points, possible_points, next_answer_id FROM `student_answer`';

  $result = mysql_query($query, $con);


  $my_key = -1;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_answer_id = $row['answer_id'];
    $this_problem_id = $row['problem_id'];
    $this_student_id = $row['student_id'];
    $this_tutorial_key = $row['tutorial_key'];
    $my_key++;

    $all_student_answer_data[$my_key] = array( 'answer_id' => $row['answer_id'],
					'answer_key' => $row['answer_key'],
					'tutorial_key' => $row['tutorial_key'],
					'internal_order' => $row['internal_order'],
					'problem_id' => $row['problem_id'],
					'draw_id' => $row['draw_id'],
					'prompt_id' => $row['prompt_id'],
					'student_id' => $row['student_id'],
					'viewed_key' => $row['viewed_key'],
					'reviewer_user_id' => $row['reviewer_user_id'],
					'review_key' => $row['review_key'],
					'answer' => $row['answer'],
					'filename' => $row['filename'],
					'askForReason' => $row['askForReason'],
					'reason' => $row['reason'],
					'points' => $row['points'],
					'possible_points' => $row['possible_points'],
					'next_answer_id' => $row['next_answer_id']
				      );
   
    //array_push($problem_id_keys_into_all_student_answer_data[$this_problem_id], $my_key);
    //array_push($student_id_keys_into_all_student_answer_data[$this_student_id], $my_key);
    //array_push($answer_id_keys_into_all_student_answer_data[$this_answer_id], $my_key);
    $problem_id_keys_into_all_student_answer_data[$this_problem_id][] = $my_key;
    $student_id_keys_into_all_student_answer_data[$this_student_id][] = $my_key;
    $answer_id_keys_into_all_student_answer_data[$this_answer_id][] = $my_key;
    $tutorial_keys_into_all_student_answer_data[$this_tutorial_key][] = $my_key;
  }

  $query = 'SELECT view_id, prompt_id, problem_id, draw_id, view_key, internal_order, date, to_see_problem, to_submit_answer, to_submit_reason, to_view_answer, to_view_reason, to_view_all_answers, to_view_all_reasons, to_draw FROM `viewed`';

  $my_key = -1;
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $my_key++;
    $my_view_key = $row['view_key'];
    $all_viewed_data[$my_view_key] = array(	'view_id' => $row['view_id'],
						'prompt_id' => $row['prompt_id'],
						'problem_id' => $row['problem_id'],
						'draw_id' => $row['draw_id'],
						'view_key' => $row['view_key'],
						'internal_order' => $row['internal_order'],
						'date' => $row['date'],
						'to_see_problem' => $row['to_see_problem'],
						'to_submit_answer' => $row['to_submit_answer'],
						'to_submit_reason' => $row['to_submit_reason'],
						'to_view_answer' => $row['to_view_answer'],
						'to_view_reason' => $row['to_view_reason'],
						'to_view_all_answers' => $row['to_view_all_answers'],
						'to_view_all_reasons' => $row['to_view_all_reasons'],
						'to_draw' => $row['to_draw']
					  );
  }

  



  if ($_REQUEST['main_key'] == "tutorial_id") {
    //Report on a per tutorial basis:
    $report_on_this_array = $problem_id_keys_into_all_student_answer_data;
    $report_on_this_key = "Tutorial ID";
    $min_count_to_report = 0;
  }
  else if ($_REQUEST['main_key'] == "student") {
    //Report on a per-student basis:
    $report_on_this_array = $student_id_keys_into_all_student_answer_data;
    $report_on_this_key = "Student ID";
    $min_count_to_report = 0;
  }
  else if ($_REQUEST['main_key'] == "tutorial_viewing") {
    //Report on a per-tutorial viewing basis:
    $report_on_this_array = $tutorial_keys_into_all_student_answer_data;
    $report_on_this_key = "Tutorial key";
    $min_count_to_report = 1;
  }

print "Doing this report: " . $_REQUEST['main_key'] . "<BR>";

  foreach($report_on_this_array as $report_key => $these_data_keys) {
    if (count($these_data_keys) > $min_count_to_report) {
      print "<P><HR></P>  $report_on_this_key: $report_key: ";
      $table_header = "";
      $table_row = array();
      $k = -1;
      foreach($these_data_keys as $data_key) {
        $k++;
        foreach ($all_student_answer_data[$data_key] as $this_key => $this_value) {
          if ($_REQUEST[$this_key] == "show") {
            if ($k == 0) {
                $table_header .= "<TD>$this_key</TD>";
            }
	    if ($this_key == "student_id") {
              $table_row[$k] .= "<TD>" . $student_ids[$this_value]['first_name'] . " " . $student_ids[$this_value]['last_name'] . "</TD>";
	    }
            else if ($this_key == "viewed_key") {
              if ($this_value > 0) {
                $tmp_viewed_data = tablize($all_viewed_data[$this_value]);
                $table_row[$k] .= "<TD><form name=myform><input type=button value='$this_value' onClick=\"alert('$tmp_viewed_data');\"></TD>";
	      }
	      else {
                $table_row[$k] .= "<TD>$this_value</TD>";
              }
            }
	    else {
              $table_row[$k] .= "<TD>$this_value</TD>";
            }
          }
        }
        //if ($all_student_answer_data[$data_key]['viewed_key'] > 0) {
        //  print_r($all_viewed_data[$all_student_answer_data[$data_key]['viewed_key']]);
        //}
        //print "<BR>";
      }
      print "<TABLE BORDER=1><TR>$table_header</TR>";
      foreach($table_row as $row) {
        print "<TR>$row</TR>";
      }
      print "</TABLE>";
    }
  }
 
}
else {

  $options = array('answer_id', 'answer_key', 'tutorial_key', 'internal_order', 'problem_id', 'draw_id', 'prompt_id', 'student_id', 'viewed_key', 'reviewer_user_id', 'review_key', 'answer', 'filename', 'askForReason', 'reason', 'points', 'possible_points', 'next_answer_id');

  $main_key = array('tutorial_id', 'student', 'tutorial_viewing');

  print "<FORM method='post'>";
  
  print "<BR>Report on:<BR>";
  foreach ($main_key as $the_key) {
    print "<input name='main_key' type='checkbox' value='" . $the_key . "'>" . $the_key . "<BR>\n";
  }

  print "<BR>Display Options</BR>";
  foreach ($options as $the_key) {
    print "<input name='" . $the_key . "' type='checkbox' value='show'>" . $the_key . "<BR>\n";
  }

  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";

}

?>

