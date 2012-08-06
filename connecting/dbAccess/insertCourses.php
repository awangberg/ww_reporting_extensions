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

$str = $_REQUEST['AddTheseCourses'];

$str_lines = explode("\n", $str);

$str_result = "";

for ($i = 0; $i < count($str_lines); $i++) {
	print "str_lines[$i] = $str_lines[$i]<P>";
	$parts_of_line = explode(",", str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]));
	$courseName = $parts_of_line[0];
	$sectionName = $parts_of_line[1];
	$createdBy = $parts_of_line[2];
	$startDate = $parts_of_line[3];
	$endDate = str_replace(array("\r\n", "\n", "\r"), "", $parts_of_line[4]);

	//insert the information into the database table 'users'
	$query = "INSERT INTO courses (courseName, sectionName, createdBy, startDate, endDate) ";
	$query .= "VALUES ('" 
		. $courseName 
		. "', '"
		. $sectionName 
		. "', '"
		. $createdBy
		. "', '" 
		. $startDate
		. "', '"
		. $endDate
		. "')";

	//add this user:
	$result = mysql_query($query, $con);

	print "<P>$query</P>";

	$str_result .= "courseName: $courseName... $result<BR .>";


}

//close connection
mysql_close($con);



print $str_result;

}
else 
{
// DISPLAY FORM IF FORM HAS NOT BEEN SUBMITTED

?>
<form method="post" action="">
Insert These Courses:
<input type="submit" name="Submit" value="Submit"> <BR>
<B>Format:</B>Each line should contain the following comma-separated information:<BR>
CourseName, SectionName, Your Name, Startdate (yyyy-mm-dd), EndDate (yyyy-mm-dd)<BR>
<TEXTAREA NAME="AddTheseCourses", ROWS=100, COLS=300>
</TEXTAREA>
</form>
<?php
} 



?>