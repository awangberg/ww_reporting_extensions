<?php

include("access.php");

function check_input($value) {
  //Stripslashes
  if (get_magic_quotes_gpc()) {
	$value = stripslashes($value);
  }

  // Quote if not a number
  if (!is_numeric($value)) {
	$value = "'" . mysql_real_escape_string($value) . "'";
  }
  return $value;
}

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_POST['userDatabaseName'];

//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}


if(isset($_POST['userName']) && isset($_POST['problemNumber']) && isset($_POST['problemSet']) && isset($_POST['courseName'])) {

  $wwUserName = check_input($_POST['userName']);
  //$wwProblemNumber = check_input($_POST['problemNumber']);
  $wwProblemNumber = check_input($_POST['problemNumber']);
  $wwSet = check_input($_POST['problemSet']);
  $wwCourse = check_input($_POST['courseName']);

  //If there is a record for the user,
  //retrieve the information for the user.

  $query = "SELECT wwTableID, problemsID FROM `WeBWorK` "
	 . "WHERE wwUserName=$wwUserName "
	 . "AND wwProblemNumber=$wwProblemNumber "
	 . "AND wwSet=$wwSet "
	 . "AND wwCourse=$wwCourse";
  $result = mysql_query($query,$con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $wwTableID = $row['wwTableID'];
    $problemsID = $row['problemsID'];
  }

  if ($wwTableID > 0) {
    //get the problemsID from problem
    $query = "SELECT drawData, problemName "
	   . "FROM `problems` "
	   . "WHERE problemID=$problemsID";
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $problemName = $row['problemName'];
      $drawData = $row['drawData'];
    }

    $xmlData  = "";
    $xmlData .= "<problem>\n";
    $xmlData .= "  <problemName>$problemName</problemName>\n";
    $xmlData .= "  <drawData>$drawData</drawData>\n";
    $xmlData .= "  <problemType>studentsubmission</problemType>";
    $xmlData .= "  <problemID></problemID>\n";
    $xmlData .= "  <assignmentID></assignmentID>\n";
    $xmlData .= "  <submissionPossiblePoints></submissionPossiblePoints>\n";
    $xmlData .= "  <completedOnDate>0000-00-00 00:00:00</completedOnDate>\n";
    $xmlData .= "</problem>\n";
  }
}

else {
  $xmlData  = "";

  $xmlData .= "<problem>\n";
  $xmlData .= "  <problemName></problemName>\n";
  $xmlData .= "  <drawData></drawData>\n";
  $xmlData .= "  <problemType>studentsubmission</problemType>";
  $xmlData .= "  <problemID></problemID>\n";
  $xmlData .= "  <assignmentID></assignmentID>\n";
  $xmlData .= "  <submissionPossiblePoints></submissionPossiblePoints>\n";
  $xmlData .= "  <completedOnDate>0000-00-00 00:00:00</completedOnDate>\n";
  $xmlData .= "</problem>\n";

}

print $xmlData;

?>

