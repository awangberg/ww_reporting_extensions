<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "wwSession";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table users:
//DROP TABLE 'users';  

echo "<P>Select the database $db";

if (mysql_select_db("$db", $con)) {
	echo "<BR>selected database $db";
}
else {
  	echo "<BR>Error selecting database $db: " . mysql_error();
}

echo "<P>Create the table: usersConceptBanks";

$sql = "CREATE TABLE IF NOT EXISTS usersConceptBanks
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
webwork_practice_set text,
webwork_problem_set_number text,
pg_sourcefile text,
concept_bank text,
latestAttemptID INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table usersConceptBanks created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the table: conceptBankIncorrectAttempts";

$sql = "CREATE TABLE IF NOT EXISTS conceptBankIncorrectAttempts
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
concept_bank text,
num_of_incorrect_attempts INT,
webwork_practice_set text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table conceptBankIncorrectAttempts created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the table: pgProblemIncorrectAttempts";
$sql = "CREATE TABLE IF NOT EXISTS pgProblemIncorrectAttempts
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
pg_sourcefile text,
num_of_incorrect_attempts INT,
webwork_practice_set text
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table pgProblemIncorrectAttempts created";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: attempts";

$sql = "CREATE TABLE IF NOT EXISTS attempts
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
concept_bank text,
pg_sourcefile text,
attempted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
submitted_date TIMESTAMP,
submitted_answer text,
was_successful BOOL DEFAULT 0
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table attempts created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: sawTutorialForConceptBank";

$sql = "CREATE TABLE IF NOT EXISTS sawTutorialForConceptBank
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
concept_bank text,
session_problem_id INT,
answer_id INT,
date_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table sawTutorialForConceptBank created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: sawTutorialForPGProblem";

$sql = "CREATE TABLE IF NOT EXISTS sawTutorialForPGProblem
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
concept_bank text,
pg_sourcefile text,
session_problem_id INT,
answer_id INT,
date_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table sawTutorialForPGProblem created.";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: attendedReviewSession";

$sql = "CREATE TABLE IF NOT EXISTS attendedReviewSession
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_name text,
user_name text,
quizName text,
date_attended TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
timeSpentOnReviewInSeconds INT
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table attendedReviewSession created.";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query: $sql";
}

echo "<P>Create the table: conceptBankDescription";

$sql = "CREATE TABLE IF NOT EXISTS conceptBankDescription
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
concept_bank text,
description text
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table conceptBankDescription created.";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query $sql";
}


echo "<P>Create the table: conceptBankContentConcepts";

$sql = "CREATE TABLE IF NOT EXISTS conceptBankContentConcepts
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
concept_bank text,
concept_content text,
stage text,
level INT
)";

if (mysql_query($sql,$con)) {
  echo "<BR>Table conceptBankContentConcepts created.";
}
else {
  echo "<BR>Error creating table: " . mysql_error();
  echo "<BR><BR>query $sql";
}

mysql_close($con);
?>
