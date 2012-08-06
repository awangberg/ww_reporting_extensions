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

//error_reporting(0); //disable all error reporting

if(!empty($_POST)) {

	$con = mysql_connect($db_host, $db_user, $db_pass);

	if(!$con) {
	  die('Could not connect: ' . mysql_error());
	}

	$userName = check_input($_POST['user']);
	$userCourseID = check_input($_POST['courseID']);
	$passWord = check_input($_POST['pass']);
	$db 	  = $_POST['userDatabaseName'];

	//select the database $db
	//create table assignments in $db database:
	if (mysql_select_db("$db", $con)) {
		//echo "selected database $db";
	}
	else {
	  	echo "Error selecting database $db: " . mysql_error();
	}
	

	$sql = "SELECT userID, userPermissions, userLastLogIn "
	        . " FROM `users` "
	        . " WHERE userName=".$userName."" 
		. " AND userPassword=".$passWord.""
		. " AND userCourseID=".$userCourseID."";


	$result = mysql_query($sql, $con);

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$userID = $row['userID'];
		$userPermissions = $row['userPermissions'];
		$userLastLogIn = $row['userLastLogIn'];	
	}
  if ($userID >= 1) {
    print "resultCode=LOGGED_IN";
    print "&userID=$userID";
    print "&userLastLogIn=$userLastLogIn";
    print "&userPermissions=$userPermissions";
  }
  else {
    print "resultCode=NOT_LOGGED_IN";
    print "&result=$result";
    print "&query=$sql";
  }
}

?>