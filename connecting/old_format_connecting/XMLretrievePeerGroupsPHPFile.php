<?php

include("access.php");

function check_input($value) {
  //Stripslashes
  if (get_magic_quotes_gpc()) {
	$value = stripslashes($value);
  }

  // Quote if not a number
  if (!is_numeric($value)) {
	$value = "'" . mysql_real_escape_string($value) . "'";
  }
  return $value;
}

header("content-type: text/xml");

$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = $_POST['userDatabaseName'];

//select the database $db
//create table assignments in $db database:
if (mysql_select_db("$db", $con)) {
	//echo "selected database $db";
}
else {
  	echo "Error selecting database $db: " . mysql_error();
}

$forCourseID = $_POST['courseID'];

$query = 'SELECT peerGroupsID, peerGroupName, listOfStudentIDs '
	.' FROM `peerGroups` '
	.' WHERE forCourseID = '.$forCourseID;

$result = mysql_query($query, $con);

$xmlData = "";
$xmlData .= "<ListOfPeerGroups>\n";

while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $peerGroupID = $row['peerGroupsID'];
  $peerGroupName = $row['peerGroupName'];
  $listOfStudentIDs = $row['listOfStudentIDs'];

  $xmlData .= "  <PeerGroup>\n";
  $xmlData .= "    <Name>$peerGroupName</Name>\n";
  $xmlData .= "    <ID>$peerGroupID</ID>\n";
  $xmlData .= "    <Students>$listOfStudentIDs</Students>\n";
  $xmlData .= "  </PeerGroup>\n";
}


$xmlData .= "</ListOfPeerGroups>\n";

print $xmlData;

?>


