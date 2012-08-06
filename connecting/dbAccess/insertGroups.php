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

$str = $_REQUEST['AddTheseGroups'];

$str_lines = explode("\n", $str);

$str_result = "";

for ($i = 0; $i < count($str_lines); $i++) {
	print "str_lines[$i] = $str_lines[$i]<P>";
	$parts_of_line = explode(",", str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]));
	$peerGroupName = $parts_of_line[0];
	$listOfStudentIDs = $parts_of_line[1];
	$userCourseID = $parts_of_line[2];
	$submittedByID = $parts_of_line[3];
	$startDate = $parts_of_line[4];
	$endDate = str_replace(array("\r\n", "\n", "\r"), "", $parts_of_line[5]);

	$startDate .= " 00:00:00";
	$endDate .= " 23:59:59";

	//insert the information into the database table 'users'
	$query = "INSERT INTO peerGroups (peerGroupName, listOfStudentIDs, forCourseID, submittedByID, startDate, endDate) ";
	$query .= "VALUES ('";

	$query .= $peerGroupName;
	$query .= "', '";

	$query .= $listOfStudentIDs;
	$query .= "', '";

	$query .= $userCourseID;
	$query .= "', '";

	$query .= $submittedByID;
	$query .= "', '";

	$query .= $startDate;
	$query .= "', '";

	$query .= $endDate;
	$query .= "')";

	//add this user:
	$result = mysql_query($query, $con);

	print "<P>$query</P>";

	$str_result .= "peerGroupName: $peerGroupName... $result<BR />";


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
peerGroupName, listOfStudentIDs (space separated), UserCourseID, submittedByID, startDate (0000-00-00), endDate (0000-00-00)<BR>
<TEXTAREA NAME="AddTheseGroups", ROWS=100, COLS=300>
</TEXTAREA>
</form>
<?php
} 



?>