<?PHP

include("access.php");

$db = "session";



$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

print "<P>Adding (1. REMEMBERING:  Recall or remember the information) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('1. REMEMBERING:  Recall or remember the information', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (2. UNDERSTANDING:  explain ideas or concepts) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('2. UNDERSTANDING:  Explain ideas or concepts', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (3. APPLYING:  Use the information in a new way) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('3. APPLYING: use the information in a new way', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (4. ANALYZING:  Break info into components and describe relationship) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('4. ANALYZING:  Break info into components and describe relationship', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (5. EVALUATING: justify a stand or decision) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('5. EVALUATING: justify a stand or decision', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (6. CREATING: generate new ideas, products, or point of view) to blooms_classification\n<BR>";
$query = "INSERT INTO blooms_classification (classification, displayOrder) VALUES ('6. CREATING: generate new ideas, products, or point of view', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";


print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Define', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Duplicate', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Identify', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Label', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'List', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Locate', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Match', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Memorize', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Name', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Recall', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Reproduce', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Spell', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'State', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Tell', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('1', 'Underline', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Calculate', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Convert', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Describe', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Discuss', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Expand', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Explain', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Identify', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Interpret', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Locate', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Outline', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Paraphrase', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Put in order', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Report', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Restate', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Retell in your own words', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Rewrite', '16')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Summarize', '17')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Trace', '18')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('2', 'Translate', '19')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Apply', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Classify', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Compute', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Conclude', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Construct', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Demonstrate', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Determine', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Dramatize', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Draw', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Find out', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Give an example', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Illustrate', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Make', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Operate', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Practice', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Show', '16')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Solve', '17')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'State a rule or principle', '18')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('3', 'Use', '19')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Analyze', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Appraise', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Categorize', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Classify', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Compare', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Contrast', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Debate', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Deduct', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Determine the factors', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Diagnose', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Diagram', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Differentiate', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Dissect', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Distinguish', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Examine', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Infer', '16')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Outline', '17')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Sequence', '18')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Specify', '19')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('4', 'Test', '20')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Appraise', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Choose', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Compare', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Conclude', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Decide', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Defend', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Dispute', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Editorialize', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Evaluate', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Give your opinion', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Judge', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Justify', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Prioritize', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Rank', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Rate', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Select', '16')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Support', '17')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Value', '18')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('5', 'Verify', '19')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Change', '1')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Combine', '2')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Compose', '3')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Construct', '4')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Create', '5')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Design', '6')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Find an unusual way', '7')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Formulate', '8')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Generate', '9')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Hypothesize', '10')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Improve', '11')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Invent', '12')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Originate', '13')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Plan', '14')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Predict', '15')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Pretend', '16')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Produce', '17')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Rearrange', '18')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Reconstruct', '19')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Reorganize', '20')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Revise', '21')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Suggest', '22')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Suppose', '23')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Visualize', '24')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";

print "<P>Adding (1, Identify) to blooms_question\n<BR>";
$query = "INSERT INTO blooms_question (blooms_classification_id, question_word, displayOrder) VALUES ('6', 'Write', '25')";
$result = mysql_query($query, $con);
print "<P>Query: $query</P>";




mysql_close($con);

?>
