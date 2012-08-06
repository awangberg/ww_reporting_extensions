<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "goteam";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table users:
//DROP TABLE 'users';  


//create table problems in goteam database:
if (mysql_select_db("goteam", $con)) {
	echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$sql = "CREATE TABLE IF NOT EXISTS users 
(
userID INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(userID),
userName VARCHAR(30),
userPassword VARCHAR(10),
userCourseID MEDIUMINT UNSIGNED,
userPermissions VARCHAR(10),
userLastLogIn TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysql_query($sql,$con)) {
	echo "Table users  created";
}
else {
	echo "Error creating table: " . mysql_error();
	echo "<BR>query: $sql";
}

mysql_close($con);
?>
