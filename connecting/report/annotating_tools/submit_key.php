<?

include("../../access.php");
include("../common.php");

//Overall Plan:
  //called with following data:
	//key_name			name of key
	//key_desc			description of key
//First, get the data to store in the comment key database.
//Store the comment key in the comment database table:  sessionCommentKeysPossible.
//Return the id of the new comment.

header("content-type: text/xml");

if (isset($_REQUEST['key']) && 
    isset($_REQUEST['desc'])) {

  //connect to the wwSession database:
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $db = "wwSession";
  $result = mysql_select_db("$db", $con);

  $query = 'INSERT INTO sessionCommentKeysPossible(shortkey, key_description) ';
  $query .= " VALUES ('"
			. mysql_real_escape_string($_REQUEST['key'])
			. "', '"
			. mysql_real_escape_string($_REQUEST['desc']);
  $query .= "')";
  //add this key to the database:
  $result = mysql_query($query, $con);
  $key_id = mysql_insert_id();

  //now, associate the comment keys to this comment:

  mysql_close($db);  
  print "<response><commentKeyID>" . $comment_id . "</commentKeyID><keyName>" . $_REQUEST['key'] . "</keyName><keyDesc>" . $_REQUEST['desc'] . "</keyDesc><query>" . $query . "</query></response>\n";

}
else {
  print "<response><error>Not enough fields</error><requestInputs>";
  print_r($_REQUEST);
  print "</requestInputs><postInputs>";
  print_r($_POST);
  print "</postInputs></response>\n";
}


?>
