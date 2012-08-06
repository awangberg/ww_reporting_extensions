<?php

include("access.php");

$db = "goteam";



if (isset($_REQUEST['Submit']))
{
// INSERT DATA FROM FORM ONCE THE FORM HAS BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$str = $_REQUEST['AddTheseUsers'];

$str_lines = explode("\n", $str);

$str_result = "";

for ($i = 0; $i < count($str_lines); $i++) {
	print "str_lines[$i] = $str_lines[$i]<P>";
	$parts_of_line = explode(",", str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]));
	$userName = $parts_of_line[0];
	$userPassword = $parts_of_line[1];
	$userCourseID = $parts_of_line[2];
	$userPermissions = str_replace(array("\r\n", "\n", "\r"), "", $parts_of_line[3]);

	//insert the information into the database table 'users'
	$query = "INSERT INTO users (userName, userPassword, userCourseID, userPermissions) ";
	$query .= "VALUES ('";

	$query .= $userName;
	$query .= "', '";

	$query .= $userPassword;
	$query .= "', '";

	$query .= $userCourseID;
	$query .= "', '";

	$query .= $userPermissions;
	$query .= "')";

	//add this user:
	$result = mysql_query($query, $con);

	print "<P>$query</P>";

	$str_result .= "userName: $userName... $result<BR />";


}

//close connection
mysql_close($con);



print $str_result;

}
else 
{
// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);


if(!$con) {
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
Insert These Students:
<input type="submit" name="Submit" value="Submit"> <BR>
<B>Format:</B>Each line should contain the following comma-separated information:<BR>
UserName, UserPassword, UserCourseId, userPermissions<BR>
<TEXTAREA NAME="AddTheseUsers", ROWS=100, COLS=300>
</TEXTAREA>
</form>
<?php
} 



?>