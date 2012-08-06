
<?php

include("access.php");

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_REQUEST['userDatabaseName'];

//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$query = 'SELECT * '
	.' FROM `blooms_classification` '
	.' LEFT JOIN `blooms_question` '
	.' ON blooms_classification.id = blooms_question.blooms_classification_id '
	.' ORDER BY blooms_classification.displayOrder';

$result = mysql_query($query, $con);

$xmlData = "";
$xmlData .= "<ListOfBloomsQuestions>\n";

$prevClassification = "";
$closeWords = "";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $classification = $row['classification'];
  $classification_id = $row['id'];
  $question_word = $row['question_word'];
  $question_id = $row['blooms_question_id'];

  if ($classification != $prevClassification) {
    $xmlData .= $closeWords;
    $xmlData .= "  <Class>\n";
    $xmlData .= "    <Classification>$classification</Classification>\n";
    $xmlData .= "    <ID>$classification_id</ID>\n";
    $xmlData .= "    <LISTOFWORDS>\n";
    $closeWords = "    </LISTOFWORDS>\n  </Class>\n";
  }
  $prevClassification = $classification;
  $xmlData .= "      <WORDS>\n";
  $xmlData .= "        <WORD>$question_word</WORD>\n";
  $xmlData .= "        <DATA>$question_id</DATA>\n";
  $xmlData .= "      </WORDS>\n";
}
$xmlData .= $closeWords;


$xmlData .= "</ListOfBloomsQuestions>\n";

mysql_close($con);

print $xmlData;

?>
