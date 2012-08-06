<?php

include("access.php");

header("content-type: text/xml");

//error_reporting(0); //disable all error reporting

//if(!empty($_POST)) {

  $con = mysql_connect($db_host, $db_user, $db_pass);

  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $user_name = check_input($_REQUEST['user_name']);
  $course_id = check_input($_REQUEST['course_id']);
  $password = check_input($_REQUEST['pass']);
  $db = $_REQUEST['userDatabaseName'];

  //select the database $db
  if (mysql_select_db("$db", $con)) {
    //echo "selected database $db";
  }
  else {
    echo "Error selecting database $db: " . mysql_error();
  }

  $sql = 'SELECT user.user_id, user.course_id, permission.permission '
	.' FROM `user` '
	.' LEFT JOIN `password` ON user.user_id = password.user_id '
	.' LEFT JOIN `permission` ON user.user_id = permission.user_id '
	.' WHERE user.user_name=' . $user_name 
	.' AND password.password=' . $password 
	.' AND password.course_id=' . $course_id
	.' AND user.course_id=' . $course_id
	.' AND permission.course_id = ' . $course_id;

  $result = mysql_query($sql, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $user_id = $row['user_id'];
    $permission = $row['permission'];
  }

  mysql_close($con);

  if ($user_id >= 1) {
    print "<Results>\n";
    print "  <Code>LOGGED_IN</Code>\n";
    print "  <User_ID>" . $user_id . "</User_ID>\n";
    print "  <Permission>" . $permission . "</Permission>\n";
    print "</Results>\n";
  }
  else {
    print "<Results>\n";
    print "  <Code>NOT_LOGGED_IN</Code>\n";
    print "  <Result>$result</Result>\n";
    print "  <Query>$sql</Query>\n";
    print "</Results>\n";
  }
//}

?>
