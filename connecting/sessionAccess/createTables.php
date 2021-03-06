<?php

include("access.php");

$con = mysql_connect($db_host, $db_user, $db_pass);

$db = "session";

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//to delete the table users:
//DROP TABLE 'users';  

echo "<P>Select the database $db";

//create table problems in $db database:
if (mysql_select_db("$db", $con)) {
	echo "<BR>selected database $db";
}
else {
  	echo "<BR>Error selecting database $db: " . mysql_error();
}

echo "<P>Create the database: user";

$sql = "CREATE TABLE IF NOT EXISTS user 
(
user_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(user_id),
course_id INT NOT NULL,
user_name text,
first_name text,
last_name text,
email_address text,
student_id text,
status text,
section text,
recitation text,
comment text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table user created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: password";

$sql = "CREATE TABLE IF NOT EXISTS password
(
user_id INT NOT NULL,
PRIMARY KEY(user_id),
course_id INT NOT NULL,
password text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table password created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}

echo "<P>Create the database: permission";

$sql = "CREATE TABLE IF NOT EXISTS permission
(
user_id INT NOT NULL,
PRIMARY KEY(user_id),
course_id INT NOT NULL,
permission INT NOT NULL
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table permission created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: permission_value";

$sql = "CREATE TABLE IF NOT EXISTS permission_value
(
id INT NOT NULL,
PRIMARY KEY(id),
name text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table permission_vale created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}




echo "<P>Create the database: course";

$sql = "CREATE TABLE IF NOT EXISTS course
(
course_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(course_id),
course_name text,
section_name text,
instructor_id INT,
initial_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
final_date TIMESTAMP,
status text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table course created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


//////

echo "<P>Create the database: major_skill";

$sql = "CREATE TABLE IF NOT EXISTS major_skill
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
name text,
is_current BOOL DEFAULT 1
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table major_skill created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: minor_skill";

$sql = "CREATE TABLE IF NOT EXISTS minor_skill
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
name text,
is_current BOOL DEFAULT 1
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table minor_skill created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}

echo "<P>Create the database: connect_major_minor_skill";

$sql = "CREATE TABLE IF NOT EXISTS connect_major_minor_skill
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
major_skill_id INT,
minor_skill_id INT,
is_current BOOL DEFAULT 1
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table connect_major_minor_skill created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: assumed_skills_for_problem";

$sql = "CREATE TABLE IF NOT EXISTS assumed_skills_for_problem
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
problem_id INT,
connect_major_minor_skill_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assumed_skills_for_problem created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: learning_skills_from_problem";

$sql = "CREATE TABLE IF NOT EXISTS learning_skills_from_problem
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
problem_id INT,
connect_major_minor_skill_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table learning_skills_from_problem created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: blooms_classification";

$sql = "CREATE TABLE IF NOT EXISTS blooms_classification
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
classification text,
displayOrder INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table blooms_classification created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: blooms_question";

$sql = "CREATE TABLE IF NOT EXISTS blooms_question
(
blooms_question_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(blooms_question_id),
blooms_classification_id INT,
question_word text,
displayOrder INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table blooms_question created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


//////

echo "<P>Create the database: problem";

$sql = "CREATE TABLE IF NOT EXISTS problem
(
problem_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(problem_id),
initial_draw_id INT,
initial_prompt_decision_key INT,
initial_review_decision_key INT,
name text,
comment text,
type text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table problem created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: prompt_decision";

$sql = "CREATE TABLE IF NOT EXISTS prompt_decision
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
prompt_decision_key INT,
internal_order INT,
true_condition text,
prompt_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table prompt_decision created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: prompt";

$sql = "CREATE TABLE IF NOT EXISTS prompt
(
prompt_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(prompt_id),
name text,
comment text,
data text,
type text,
filename text,
answer_filename text,
blooms_question_id INT,
blooms_classification_id INT,
reactToPrompt_key INT,
override_draw_pace DECIMAL
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table prompt created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: react_to_prompt";

$sql = "CREATE TABLE IF NOT EXISTS react_to_prompt
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
react_key INT,
internal_order INT,
true_condition text,
override_next_draw_id INT,
override_next_prompt_decision_key INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table react_to_prompt created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: draw";

$sql = "CREATE TABLE IF NOT EXISTS draw
(
draw_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(draw_id),
filename text,
name text,
comment text,
default_next_draw_id INT,
default_next_prompt_decision_key INT,
override_pace DECIMAL
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table draw created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



////////

echo "<P>Create the database: viewed";

$sql = "CREATE TABLE IF NOT EXISTS viewed
(
view_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(view_id),
prompt_id INT,
problem_id INT,
draw_id INT,
view_key INT,
internal_order INT,
date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
to_see_problem INT,
to_submit_answer INT,
to_submit_reason INT,
to_view_answer INT,
to_view_reason INT,
to_view_all_answers INT,
to_view_all_reasons INT,
to_draw INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table viewed created";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



/////////

echo "<P>Create the database: student_answer";

$sql = "CREATE TABLE IF NOT EXISTS student_answer
(
answer_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(answer_id),
answer_key INT,
internal_order INT,
problem_id INT,
draw_id INT,
prompt_id INT,
student_id INT,
viewed_key INT,
reviewer_user_id INT,
review_key INT,
answer text,
filename text,
reason text,
points INT,
possible_points INT,
next_answer_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table student_answer created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


/////////

echo "<P>Create the database: class_group";

$sql = "CREATE TABLE IF NOT EXISTS class_group
(
group_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(group_id),
course_id INT,
name text,
user_ids text,
begin_valid_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
end_valid_time TIMESTAMP
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table class_group created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}




echo "<P>Create the database: assignment_set";

$sql = "CREATE TABLE IF NOT EXISTS assignment_set
(
assign_set_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(assign_set_id),
name text,
assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
due_date TIMESTAMP,
isVisible_start_date TIMESTAMP,
isVisible_end_date TIMESTAMP
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assignment_set created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: assignment_problem_set";

$sql = "CREATE TABLE IF NOT EXISTS assignment_problem_set
(
assign_problem_set_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(assign_problem_set_id),
assign_set_id INT,
problem_id INT,
max_attempts INT,
assigned_to_group INT,
reviewed_by_group INT,
reviewed_by_self text,
additional_review_decision_key INT,
additional_review_decision_code text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assignment_problem_set created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: assignment_problem_user_set";

$sql = "CREATE TABLE IF NOT EXISTS assignment_problem_user_set
(
assign_problem_user_set_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(assign_problem_user_set_id),
assign_problem_set_id INT,
assign_set_id INT,
user_id INT,
problem_id INT,
answer_key INT,
attempts INT,
points INT,
possible_points INT,
isCompleted text,
post_completed_visits_key INT,
reviewKey INT,
draw_sequence text,
prompt_sequence text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assignment_problem_user_set created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: assignment_problem_reviewer_set";

$sql = "CREATE TABLE IF NOT EXISTS assignment_problem_reviewer_set
(
assign_problem_reviewer_set_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(assign_problem_reviewer_set_id),
assign_problem_user_set_id INT,
review_key INT,
reviewer_user_id INT,
answer_key INT,
isCompleted text,
reviewer_points INT,
possible_reviewer_points INT,
post_completed_visits_by_reviewer_key INT,
post_completed_visits_by_author_key INT,
prompt_sequence text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assignment_problem_reviewer_set created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


//////////


echo "<P>Create the database: wwProblemInSession";

$sql = "CREATE TABLE IF NOT EXISTS wwProblemInSession
(
ww_problem_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(ww_problem_id),
ww_problem_file text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table wwProblemToSessionTutorial created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: wwSetInSession";

$sql = "CREATE TABLE IF NOT EXISTS wwSetInSession
(
ww_set_and_course_id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(ww_set_and_course_id),
ww_course_name text,
ww_set_id text
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table wwProblemToSessionTutorial created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: wwSetToSessionTutorial";

$sql = "CREATE TABLE IF NOT EXISTS wwSetToSessionTutorial
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
ww_set_and_course_id INT,
session_problem_id INT,
valid_tutorial text,
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table wwSetToSessionTutorial created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: wwProblemToSessionTutorial";

$sql = "CREATE TABLE IF NOT EXISTS wwProblemToSessionTutorial
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
ww_problem_id INT,
session_problem_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table wwProblemToSessionTutorial created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}



echo "<P>Create the database: assumed_skills_for_wwProblemOrSet";

$sql = "CREATE TABLE IF NOT EXISTS assumed_skills_for_wwProblemOrSet
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
ww_problem_id INT,
ww_set_and_course_id INT,
connect_major_minor_skill_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table assumed_skills_for_wwProblemOrSet created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


echo "<P>Create the database: learning_skills_from_wwProblemOrSet";

$sql = "CREATE TABLE IF NOT EXISTS learning_skills_from_wwProblemOrSet
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
ww_problem_id INT,
ww_set_and_course_id INT,
connect_major_minor_skill_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table learning_skills_from_wwProblemOrSet created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}

$sql = "CREATE TABLE IF NOT EXISTS wwStudentWorkForProblem
(
id INT NOT NULL AUTO_INCREMENT,
PRIMARY KEY(id),
course_id INT,
user_id INT,
ww_set_id text,
ww_problem_number INT,
problem_id INT
)";

if (mysql_query($sql,$con)) {
	echo "<BR>Table wwStudentWorkForProblem created.";
}
else {
	echo "<BR>Error creating table: " . mysql_error();
	echo "<BR><BR>query: $sql";
}


mysql_close($con);
?>
