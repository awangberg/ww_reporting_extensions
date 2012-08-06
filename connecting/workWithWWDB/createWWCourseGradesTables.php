<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "wwSession";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table sessionComments:
//DROP TABLE 'sessionComments';

echo "<P>Select the database $db";

if (mysql_select_db("$db", $con)) {
  echo "<BR>selected database $db";
}
else {
  echo "<BR>Error selecting database $db: " . mysql_error();
}

echo "<P>Create the table: course_gradeItem";

//things like:  quiz 1, exam 1, final exam, overall_course_grade, etc.


$sql = "CREATE TABLE IF NOT EXISTS course_gradeItem
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_gradeItem text
)";

if (mysql_query($sql, $con)) {
  echo "<BR>Table course_gradeItem created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: course_wwValidCourses";

//things like:  course, validForStatistics: true/false, validForCourseContainer: true/false, ...
//  note:  a course container is a course that contains base assignments which are then customized for students
//         and assigned into a real course.

$sql = "CREATE TABLE IF NOT EXISTS course_wwValidCourses
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
ww_course text,
validCourse BOOL,
validForStatistics BOOL,
validForCourseContainer BOOL
)";

if (mysql_query($sql, $con)) {
  echo "<BR>Table course_wwValidCourses created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: course_wwUserPermissions";

//things like:  course, username, validUserForStatistics: true/false, ...

$sql = "CREATE TABLE IF NOT EXISTS course_wwUserPermissions
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_wwValidCourses_id INT,
ww_user_id text,
validUser BOOL,
validForStatistics BOOL,
finishedCourse BOOL
)";

if (mysql_query($sql, $con)) {
  echo "<BR>Table course_wwUserPermissions created.";
}
else {
  echo "<BR>Error creating table: " . $mysql_error();
  echo "<BR><BR>query: $sql";
}


echo "<P>Create the table: course_grades";

// things like:  98% A, 72.1743% C for a student's quiz 1, exam 1, final exam, or overall_course_grade.

$sql = "CREATE TABLE IF NOT EXISTS course_grades
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_wwUserPermissions_id INT,
course_gradeItem_id INT,
gradeLetter text,
gradePercent DECIMAL(8,5)
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table course_grades created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: course_examProblems";

// things like: Problem [problemNumber: 1]  (max of [pointsPossible: 5] points) is a [description: function composition with two linear functions] problem on [course_gradeItem_id: 1, linked to quiz1 or Exam 1] for course [ww_course]  

$sql = "CREATE TABLE IF NOT EXISTS course_examProblems
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_wwValidCourses_id INT,
course_gradeItem_id INT,
problemNumber INT,
pointsPossible INT,
description text
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table course_examProblems created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: course_examProblemsMetaData";

//things like:  examProblems_id is associated with various taxonomy_id id's.

$sql = "CREATE TABLE IF NOT EXISTS course_examProblemsMetaData
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_examProblems_id INT,
taxonomy_id INT
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table course_examProblemsMetaData created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: course_examProblemScores";

//things like:  examProblems_id for wwUserPermissions_id has score pointsEarned
//  		and the id for a table like sessionCommentKeys
// 		and a possible link (image_link) to the uploaded exam problem for that student

$sql = "CREATE TABLE IF NOT EXISTS course_examProblemScores
(
key_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(key_id),
course_wwUserPermissions_id INT,
course_examProblems_id INT,
pointsEarned INT,
comment_key INT,
image_link text
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table course_examProblemScores created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

mysql_close($con);
?>
