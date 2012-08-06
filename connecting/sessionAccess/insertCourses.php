<?php

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

  $course_name = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['course_name']);
  $section_name = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['section_name']);
  $instructor_id = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['instructor_id']);
  $initial_date = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['initial_date']);
  $final_date = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['final_date']);
  $course_status = str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['course_status']);

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

  $permission = 10;

  if ($user_name == "") { $tmp = explode('@', $email_address); $user_name = $tmp[0]; }

  if (($user_name != "") && ($first_name != "") && ($last_name != "") && ($email_address != "") && ($password1 == $password2)) {
    $query  = "INSERT INTO user (course_id, user_name, first_name, last_name, email_address, student_id, status, section, recitation, comment) ";
    $query .= "VALUES ('"
	 	     . "-1"
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
		     . $recitation
		     . "', '"
		     . $section
		     . "', '"
		     . $comment
		     . "')";

    $result = mysql_query($query, $con);
    print "Inserted user $first_name $last_name ($user_name) into the user table\n<BR>";
    print "<P>Query: $query</P>";
    $user_id = mysql_insert_id();

    //insert the information into the database table 'course'
    $query = "INSERT INTO course (course_name, section_name, instructor_id, initial_date, final_date, status) ";
    $query .= "VALUES ('" 
		      . $course_name 
		      . "', '"
		      . $section_name 
		      . "', '"
		      . $user_id
		      . "', '"
		      . $initial_date
		      . "', '"
		      . $final_date
		      . "', '"
		      . $course_status
		      . "')";

    $result = mysql_query($query, $con);
    print "Inserted course $course_name $section_name into the course table\n<BR>";
    print "<P>Query: $query</P>";
    $course_id = mysql_insert_id();

    $query = "INSERT INTO password (user_id, course_id, password) ";
    $query .= "VALUES ('" 
		      . $user_id
		      . "', '"
		      . $course_id
		      . "', '"
		      . $password1
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
		      . "10"
		      . "')";

    $result = mysql_query($query, $con);
    print "Set permission for user $user_id to 10\n<BR>";
    print "<P>Query: $query</P>";

    $query = "UPDATE user SET course_id=$course_id WHERE user_id=$user_id";
    $result = mysql_query($query, $con);
    print "Updated course_id for user $user_id to $course_id\n<BR>";
    print "<P>Query: $query</P>";


  }
  else {
    //insert the information into the database table 'users'
    $query = "INSERT INTO course (course_name, section_name, instructor_id, initial_date, final_date, status) ";
    $query .= "VALUES ('" 
		    . $course_name 
		    . "', '"
		    . $section_name 
		    . "', '"
		    . $instructor_id
		    . "', '"
		    . $initial_date
		    . "', '"
		    . $final_date
		    . "', '"
		    . $course_status
		    . "')";

    //add this user:
    $result = mysql_query($query, $con);

    print "<P>$query</P>";

    $str_result .= "Adding course: $course_name $section_name by $instructor_id... $result<BR .>";
  }
  print $str_result;

//close connection
mysql_close($con);

}

else {


  $query   = 'SELECT user.user_id, user.first_name, user.last_name, permission.permission '
	   . ' FROM `user` '
	   . ' LEFT JOIN `permission` '
	   . ' ON user.user_id = permission.user_id '
	   . ' WHERE permission.permission = 10';
  $result = mysql_query($query, $con);

  $possible_instructors = "<select name='instructor_id'>\n";

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $possible_instructors .= "<option value='" . $row['user_id'] . "'>" . $row['first_name'] . " " . $row['last_name'] . "</option>\n";
  }
  $possible_instructors .= "</select>\n";
  mysql_close($con);

  // DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

?>
<form method="post" action="">
Insert A Course:
<input type="submit" name="Submit" value="Submit"> <BR>
<TABLE>
<TR><TD>Course Name:</TD><TD> <input type="text" name="course_name" size="25" value=""></TD></TR>
<TR><TD>Section Name: (optional)</TD><TD> <input type="text" name="section_name" size="25" value=""></TD></TR>
<TR><TD>Instructor:</TD><TD> 
<?
print $possible_instructors;
?>
</TD></TR>
<TR><TD>Initial Date:</TD><TD><input type="text" name="initial_date" size="20" value="YYYY-MM-DD 00:00:00"></TD></TR>
<TR><TD>Final Date:</TD><TD><input type="text" name="final_date" size="20" value="YYYY-MM-DD 23:59:59"></TD></TR>
<TR><TD>Status: (C)</TD><TD><input type="text" name="course_status" size="25" value=""></TD></TR>
</TABLE>

<BR>If not available above, you may add yourself as an Instructor:<BR>
<TABLE>
<TR><TD>User Name: (email)</TD><TD><input type="text" name="user_name" size="25" value=""></TD></TR>
<TR><TD>First Name:</TD><TD><input type="text" name="first_name" size="25" value=""></TD></TR>
<TR><TD>Last Name: </TD><TD><input type="text" name="last_name" size="25" value=""></TD></TR>
<TR><TD>Email Address: </TD><TD><input type="text" name="email_address" size="25" value=""></TD></TR>
<TR><TD>Student ID: </TD><TD><input type="text" name="student_id" size="25" value=""></TD></TR>
<TR><TD>Status: (C)</TD><TD><input type="text" name="status" size="25" value=""></TD></TR>
<TR><TD>Section: (Optional) </TD><TD><input type="text" name="section" size="25" value=""></TD></TR>
<TR><TD>Recitation: (Optional) </TD><TD><input type="text" name="recitation" size="25" value=""></TD></TR>
<TR><TD>Comment: </TD><TD><input type="text" name="comment" size="25" value=""></TD></TR>
<TR></TR>
<TR><TD>Password: </TD><TD><input type="password" name="password1" size="25" value=""></TD></TR>
<TR><TD>Retype Password: </TD><TD><input type="password" name="password2" size="25" value=""></TD></TR>
</TABLE>
</form>

<?php
} 



?>
