<?PHP

include("access.php");

$db = "session";



$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

if (isset($_REQUEST['Submit'])) {
  // INSERT DATA FROM FORM ONCE THE FORM HAS BEEN SUBMITTED
  $str_result = "";

  $course_id = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['course_id']);
  $user_name = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['user_name']);
  $first_name = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['first_name']);
  $last_name = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['last_name']);
  $email_address = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['email_address']);
  $student_id = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['student_id']);
  $status = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['status']);
  $section = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['section']);
  $recitation = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['recitation']);
  $comment = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['comment']);

  $password1 = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['password1']);
  $password2 = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['password2']);

  $permission = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['permission']);;

  if ($user_name == "") { $tmp = explode('@', $email_address); $user_name = $tmp[0]; }

  if (($user_name != "") && ($first_name != "") && ($last_name != "") && ($email_address != "")) {
    $query  = "INSERT INTO user (course_id, user_name, first_name, last_name, email_address, student_id, status, section, recitation, comment) ";
    $query .= "VALUES ('"
	 	     . $course_id
		     . "', '"
		     . $user_name
		     . "', '"
		     . $first_name
		     . "', '"
		     . $last_name
		     . "', '"
		     . $email_address
		     . "', '"
		     . $student_id
		     . "', '"
		     . $status
		     . "', '"
		     . $section
		     . "', '"
		     . $recitation
		     . "', '"
		     . $comment
		     . "')";

    $result = mysql_query($query, $con);
    print "Inserted user $first_name $last_name ($user_name) into the user table\n<BR>";
    print "<P>Query: $query</P>";
    $user_id = mysql_insert_id();

    if (($password1 != "") && ($password2 != "") && ($password1 == $password2)) {
      $password = $password1;
    }
    else {
      $password = $student_id;
    }
    $query = "INSERT INTO password (user_id, course_id, password) ";
    $query .= "VALUES ('" 
		      . $user_id
		      . "', '"
		      . $course_id
		      . "', '"
		      . $password
		      . "')";

    $result = mysql_query($query, $con);
    print "Updated password for user $user_id\n<BR>";
    print "<P>Query: $query</P>";

    $query = "INSERT INTO permission (user_id, course_id, permission) ";
    $query .= "VALUES ('" 
		      . $user_id
		      . "', '"
		      . $course_id
		      . "', '"
		      . $permission
		      . "')";

    $result = mysql_query($query, $con);
    print "Set permission for user $user_id to $permission\n<BR>";
    print "<P>Query: $query</P>";

  }
  print $str_result;

//close connection
mysql_close($con);

}

else {

  $query   = 'SELECT course.course_id, course.course_name, course.section_name, user.first_name, user.last_name '
	   . ' FROM `course` '
	   . ' LEFT JOIN `user` '
	   . ' ON user.user_id = course.instructor_id ';
  $result = mysql_query($query, $con);

  $possible_courses = "<select name='course_id'>\n";

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $possible_courses .= "<option value='" . $row['course_id'] . "'>" . $row['course_name'] . " " . $row['section_name'] . " - " . $row['first_name'] . " " . $row['last_name'] . "</option>\n";
  }
  $possible_courses .= "</select>\n";


  $query   = 'SELECT id, name '
	   . ' FROM `permission_value` ';
  $result = mysql_query($query, $con);

  $possible_permissions = "<select name='permission'>\n";

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $possible_permissions .= "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>\n";
  }
  $possible_permissions .= "</select>\n";

  mysql_close($con);

  // DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

?>
<form method="post" action="">
Insert a Student:
<input type="submit" name="Submit" value="Submit"> <BR>
<TABLE>
<TR><TD>Course Name:</TD><TD>
<?
print $possible_courses
?>
</TD></TR>
<TR><TD>User Name: (email)</TD><TD><input type="text" name="user_name" size="25" value=""></TD></TR>
<TR><TD>First Name:</TD><TD><input type="text" name="first_name" size="25" value=""></TD></TR>
<TR><TD>Last Name: </TD><TD><input type="text" name="last_name" size="25" value=""></TD></TR>
<TR><TD>Email Address: </TD><TD><input type="text" name="email_address" size="25" value=""></TD></TR>
<TR><TD>Student ID: </TD><TD><input type="text" name="student_id" size="25" value=""></TD></TR>
<TR><TD>Status: (C)</TD><TD><input type="text" name="status" size="25" value=""></TD></TR>
<TR><TD>Section: (Optional) </TD><TD><input type="text" name="section" size="25" value=""></TD></TR>
<TR><TD>Recitation: (Optional) </TD><TD><input type="text" name="recitation" size="25" value=""></TD></TR>
<TR><TD>Comment: </TD><TD><input type="text" name="comment" size="25" value=""></TD></TR>
<TR><TD>Permission:</TD><TD>
<?
print $possible_permissions
?>
</TD></TR>
<TR></TR>
<TR><TD>Password: (default: student_id) </TD><TD><input type="password" name="password1" size="25" value=""></TD></TR>
<TR><TD>Retype Password: (default: student_id) </TD><TD><input type="password" name="password2" size="25" value=""></TD></TR>
</TABLE>
</form>

<?php
} 



?>
