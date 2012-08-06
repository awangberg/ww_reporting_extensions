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

for ($i = 0; $i < count($str_lines); $i++) {
//	print "str_lines[$i] = $str_lines[$i]<P>";
	$classification = str_replace(array("\r\n", "\n", "\r"), "", $str_lines[$i]);

	if ($classification != "") {
	  $query  = "INSERT INTO blooms_classification (classification) ";
	  $query .= "VALUES ('";
	  $query .= $classification;
	  $query .= "')";

	  $result = mysql_query($query, $con);
	  print "<P>Query: $query</P>";

	  $str_result .= "Blooms classification: $classification... $result<BR />";
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

$blooms_classification = "<TABLE><TR><TD>ID</TD><TD>Blooms Classification</TD></TR>\n";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $blooms_classification .= "<TR><TD>" . $row['id'] . "</TD><TD>" . $row['classification'] . "</TD></TR>\n";
}
$blooms_classification .= "</TABLE>\n";

mysql_close($con);

print $blooms_classification;
?>

<form method="post" action="">
<BR><HR><BR>
Insert These Blooms Classifications:
<input type="submit" name="Submit" value="Submit"><BR>
<B>Format:</B>One Blooms Classification on each line<BR>
Blooms Classification<BR>
<TEXTAREA NAME="AddThisInfo", ROWS=10, COLS=40>
</TEXTAREA>
</form>
<?php
}


?>