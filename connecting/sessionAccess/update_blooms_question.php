<?php

include("access.php");

$db = "session";

if (isset($_REQUEST['Submit'])) {
// INSERT DATA FROM FORM ONCE WHEN IT HAS BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

$res = mysql_select_db("$db", $con);

$str = $_REQUEST['AddThisInfo'];
$str_lines = explode("\n", $str);

$str_result = "";

$blooms_classification_id = $_REQUEST['classification_id'];
for ($i = 0; $i < count($str_lines); $i++) {
//	print "str_lines[$i] = $str_lines[$i]<P>";
	$question_word = str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]);

	if ($question_word != "") {
	  $query  = "INSERT INTO blooms_question (blooms_classification_id, question_word) ";
	  $query .= "VALUES ('";
	  $query .= $blooms_classification_id;
	  $query .= "', '";
	  $query .= $question_word;
	  $query .= "')";

	  $result = mysql_query($query, $con);
	  print "<P>Query: $query</P>";

	  $str_result .= "Inserted question word $question_word... $result<BR />";
	}
}

//close connection
mysql_close($con);

print $str_result;

}
else
{
//DISPLAY FORM IF IT HAS NOT BEEN SUBMITTED

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

$query  = 'SELECT id, classification '
	. ' FROM `blooms_classification` ';
$result = mysql_query($query, $con);


$select_options = "<select name='classification_id'>\n";
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $select_options .= "<option value='" . $row['id'] . "'>" . $row['classification'] . "</option>\n";  
}
$select_options .= "</select>\n";

mysql_close($con);


?>

<form method="post" action="">
<BR><HR><BR>
Insert These Blooms Question Words for the Blooms Classifications:
<input type="submit" name="Submit" value="Submit"><BR>
<B>Format:</B>One Blooms question word on each line<BR>
Blooms Question Word<BR>

<?
print "<TABLE><TR><TD>Blooms Classification</TD><TD>Blooms Question Words</TD></TR>\n";
print "<TR><TD>$select_options</TD><TD>";
?>

<TEXTAREA NAME="AddThisInfo", ROWS=10, COLS=40></TEXTAREA>
</TD></TR>
</TABLE>


</TEXTAREA>
</form>
<?php

}

?>
