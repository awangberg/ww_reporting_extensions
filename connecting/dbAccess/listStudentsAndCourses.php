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

$str = $_REQUEST['ListStudentsForCourse'];

$str_lines = explode("\n", $str);

$str_result = "";

for ($i = 0; $i < count($str_lines); $i++) {
	print "str_lines[$i] = $str_lines[$i]<P>";
	$parts_of_line = explode(",", str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]));
	$courseID = str_replace(array("\r\n", "\n", "\r"), "", $parts_of_line[0]);

	$query = 'SELECT peerGroupName, listOfStudentIDs '
		.' FROM `peerGroups` '
		.' WHERE forCourseID = '.$courseID;

	$result = mysql_query($query, $con);

	$groupArray = array();

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $student_numbers = explode(" ", $row['listOfStudentIDs']);
	//print "<BR>student_numbers = " . $row['listOfStudentIDs'] . "<BR>\n";

	  for ($i = 0; $i < count($student_numbers); $i++) {
	    if (array_key_exists($student_numbers[$i], $groupArray)) {
	      $groupArray[$student_numbers[$i]] .= " " . $row['peerGroupName'];
	    }
	    else {
	      $groupArray[$student_numbers[$i]] = " " . $row['peerGroupName'];
	    }
	  }
	}


	$query =   'SELECT userID, userName '
		  .' FROM `users` '
		  .' WHERE userPermissions=' . "'student'"
		  .' AND userCourseID ='.$courseID;

	$result = mysql_query($query, $con);

	$studentList = "<TABLE BORDER=1>";
	$studentList .= "<TR><TD>ID</TD><TD>Name</TD><TD>Group(s)</TD></TR>\n";

	while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	  $studentList   .= "<TR><TD>" . $row['userID'] . "</TD><TD>" . $row['userName'] . "</TD><TD>" . $groupArray[$row['userID']] . "</TD></TR>\n";
	}

	$studentList .= "</TABLE>\n";

	//add this user:
	$result = mysql_query($query, $con);

	print "<P>$query</P>";
	print $studentList;


}

//close connection
mysql_close($con);


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
courseID
<TEXTAREA NAME="ListStudentsForCourse", ROWS=100, COLS=300>
</TEXTAREA>
</form>
<?php
} 



?>