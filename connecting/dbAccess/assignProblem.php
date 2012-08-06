<?php

include("access.php");

$db = "goteam";

if (isset($_REQUEST['Submit']))
{
//INSERT DATA FROM FORM ONCE THE FORM HAS BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$forUsers = $_REQUEST['forUsers'];
$problemID = $_REQUEST['problemID'];
$promptID = $_REQUEST['promptID'];
$selfExplanationSetID = $_REQUEST['selfExplanationSetID'];
$selfExplanationDueDate = $_REQUEST['selfExplanationDueDate'];
$selfExplanationAnswer1PossiblePoints = $_REQUEST['selfExplanationAnswer1PossiblePoints'];
$selfExplanationAnswer2PossiblePoints = $_REQUEST['selfExplanationAnswer2PossiblePoints'];
$selfExplanationAnswer3PossiblePoints = $_REQUEST['selfExplanationAnswer3PossiblePoints'];

$list_of_users = explode(" ", $forUsers);
$str_result = "";

for ($i = 0; $i < count($list_of_users); $i++) {
  $query  = "INSERT INTO selfExplanation (selfExplanationProblemID, selfExplanationPromptID, selfExplanationAssignedToStudentID, selfExplanationHomeworkSetID, selfExplanationDueDate, selfExplanationAnswer1PossiblePoints, selfExplanationAnswer2PossiblePoints, selfExplanationAnswer3PossiblePoints, selfExplanationToCompleteTime, selfExplanationToCompleteVisits, selfExplanationAfterCompletedTime, selfExplanationAfterCompletedVisits) ";
  $query .= "VALUES ($problemID, $promptID, $list_of_users[$i], $selfExplanationSetID, '$selfExplanationDueDate', ";
  $query .= "$selfExplanationAnswer1PossiblePoints, $selfExplanationAnswer2PossiblePoints, $selfExplanationAnswer3PossiblePoints, ";
  $query .= "0, 0, 0, 0)";

  $query1 = $query;
  print $query1;
  $result = mysql_query($query, $con);
  $str_result .= "Added problem $problemID, prompt $promptID for user $list_of_users[$i]<BR>";
  $selfExplanationID = mysql_insert_id();
}

//close connection
mysql_close($con);

print $str_result;

}

else {
// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$query =   'SELECT courseID, courseName, sectionName '
	  .' FROM `courses` ';
$result = mysql_query($query, $con);

$courseList = "";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $courseList   .= "<BR>"
  		.  "ID: " . $row['courseID'] 
		.  " Course Name: " . $row['courseName']
		.  " Section Name: " . $row['sectionName'];
}

mysql_close($con);

print $courseList;
?>
<form method="post" action="">
<BR><HR><BR>
<input type="submit" name="Submit" value="Submit"><BR>
Assign Self-Explanation:<BR>
<TABLE BORDER=0>
<TR><TD>Problem ID: </TD><TD><TEXTAREA NAME="problemID", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Prompt ID:  </TD><TD><TEXTAREA NAME="promptID", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Assignment Set ID:</TD><TD><TEXTAREA NAME="selfExplanationSetID", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Due Date: </TD><TD><TEXTAREA NAME="selfExplanationDueDate", ROWS=1, COLS=10></TEXTAREA>(yyyy-mm-dd hh:mm:ss)</TD></TR>
<TR><TD>Answer1 Possible Points: </TD><TD><TEXTAREA NAME="selfExplanationAnswer1PossiblePoints", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Answer2 Possible Points: </TD><TD><TEXTAREA NAME="selfExplanationAnswer2PossiblePoints", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Answer3 Possible Points: </TD><TD><TEXTAREA NAME="selfExplanationAnswer3PossiblePoints", ROWS=1, COLS=10></TEXTAREA></TD></TR>
<TR><TD>Assign to User IDs (space sep.): </TD><TD><TEXTAREA NAME="forUsers", ROWS=5, COLS=40></TEXTAREA></TD></TR>
</TABLE>
</form>
<?php
}

?>