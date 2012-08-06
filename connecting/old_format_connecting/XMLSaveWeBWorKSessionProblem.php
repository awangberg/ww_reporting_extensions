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


if(isset($_POST['userName']) && isset($_POST['problemNumber']) && isset($_POST['problemSet']) && isset($_POST['courseName']) && isset($_POST['drawData'])) {

  $wwUserName = check_input($_POST['userName']);
  $wwProblemNumber = check_input($_POST['problemNumber']);
  $wwSet = check_input($_POST['problemSet']);
  $wwCourse = check_input($_POST['courseName']);
  $drawData = check_input($_POST['drawData']);
  $problemName = check_input(WeBWorK);

  //If there is a record for the user,
  //retrieve the information for the user.

  $query = "SELECT wwTableID, problemsID FROM `WeBWorK` "
	 . "WHERE wwUserName=$wwUserName "
	 . "AND wwProblemNumber=$wwProblemNumber "
	 . "AND wwSet=$wwSet "
	 . "AND wwCourse=$wwCourse";
  $result = mysql_query($query,$con);
  $problemsID = 0;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $wwTableID = $row['wwTableID'];
    $problemsID = $row['problemsID'];
  }

  if ($problemsID > 0) {
    //save the drawData.  
    //No Need to update the WeBWorK table, except
    //to update the submittedOnDate field.
    $query = "UPDATE problems SET drawData=$drawData WHERE problemID=$problemsID";
    $query1 = $query;
    $result = mysql_query($query, $con);
  }
  else {
    $query = "INSERT INTO problems (problemName, drawData, submittedBy) "
           . "VALUES ('WeBWorK', $drawData, -1)";
    $result = mysql_query($query, $con);
    $problemsID = mysql_insert_id();

    $query = "INSERT INTO WeBWorK (wwUserName, wwProblemNumber, wwSet, wwCourse, problemsID) "
	   . "VALUES ($wwUserName, $wwProblemNumber, $wwSet, $wwCourse, $problemsID)";
    $query1 = $query;
    $result = mysql_query($query,$con);
  }

}

print "resultCode=SENT&query=".$query1."&result=".$result;

?>

