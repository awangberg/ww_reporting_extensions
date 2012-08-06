<?PHP

include("access.php");

$db = "session";



$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

print "<P>Adding (-5, guest) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('-5', 'guest')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (0, student) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('0', 'student')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (2, login_proctor) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('2', 'login_proctor')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (3, grade_proctor) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('3', 'grade_proctor')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (5, ta) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('5', 'ta')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (10, professor) to permission_value\n<BR>";
$query = "INSERT INTO permission_value (id, name) VALUES ('10', 'professor')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

mysql_close($con);

?>
