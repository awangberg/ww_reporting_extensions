<?

include("../../access.php");
include("../common.php");

//Overall Plan:
  //called with following data:
	//replayToSessionTime		time in milleseconds for session play (without waiting time)
	//time				human-readable time (with waiting time)
	//commenter			initials of commenter
	//keyFields			key string
	//comment			the comment to store about the work
	//sessionCommentID		the id of this comment in the database table
	//responseToSessionCommentID	provided if this comment was a follow-up comment to another comment
//First, get the data to store in the comment database.
//Store the comment in the comment database.
//Return the id of the new comment.

header("content-type: text/xml");

if (isset($_REQUEST['replayToSessionTime']) && 
    isset($_REQUEST['responseToSessionCommentID']) &&
    isset($_REQUEST['sessionCommentID']) &&
    isset($_REQUEST['sessionProblemID']) &&
    isset($_REQUEST['time']) &&
    isset($_REQUEST['commenter']) &&
    isset($_REQUEST['keyFields']) &&
    isset($_REQUEST['comment'])) {

  //connect to the wwSession database:
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $db = "wwSession";
  $result = mysql_select_db("$db", $con);

  //set each of the old key ids to invalid.
  $query = "UPDATE sessionCommentKeys "
	 . "SET record_valid=FALSE "
	 . "WHERE comment_id='" . $_REQUEST['sessionCommentID'] . "'";
  $result = mysql_query($query, $con);

  //for each of the new key ids, check to see if it exists in the table already.
  //	if it does,  just make that record valid.
  //	if it isn't, add that key to the table.
  $modified_keyFields = explode(",", $_REQUEST['keyFields']);
  foreach ($modified_keyFields as $k => $f) {
    $query = "SELECT key_id from `sessionCommentKeysPossible` WHERE shortkey='" . mysql_real_escape_string($f) . "'";
    $result = mysql_query($query, $con);
    $key_id = -1;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $key_id = $row['key_id'];
    }
    if ($key_id > 0) {
      //check to see if that key_id is in the sessionCommentKeys for this comment:
      $query = "SELECT id FROM `sessionCommentKeys` "
	     . "WHERE comment_id='" . $_REQUEST['sessionCommentID'] . "' "
	     . "AND key_id='" . $key_id . "'";
      $result = mysql_query($query, $con);
      $id = -1;
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$id = $row['id'];
      }

      if ($id > 0) {
	//set this id as a valid id
	$query  = "UPDATE sessionCommentKeys "
		. "SET record_valid=TRUE "
		. "WHERE id='" . $id . "'";
	$result = mysql_query($query, $con);
      }
      else {
	//insert this key_id into the sessionCommentKeys table.
	$query  = "INSERT INTO sessionCommentKeys(session_problem_id, comment_id, key_id, record_valid) VALUES ("
                . $_REQUEST['sessionProblemID']
                . ", "
                . $_REQUEST['sessionCommentID'] 
                . ", "
                . $key_id
                . ", "
                . "TRUE"
                . ")";
        $result = mysql_query($query, $con);
      }
    }
  }  //end of the array loop on the keyFields


  //update the existing record and comment. 

  $query = "UPDATE sessionComments "
	 . "SET "
	 //. "commenter='" . $_REQUEST['commenter'] . "', "
	 . "comment='" . mysql_real_escape_string($_REQUEST['comment']) . "' "
	 . "WHERE id=" . $_REQUEST['sessionCommentID'] . "";

  $result = mysql_query($query, $con);

  mysql_close($db);  
  print "<response><problemCommentID>" . $_REQUEST['sessionCommentID'] . "</problemCommentID><query>" . $query . "</query></response>\n";

}
else {
  print "<response><error>Not enough fields</error><requestInputs>";
  print_r($_REQUEST);
  print "</requestInputs><postInputs>";
  print_r($_POST);
  print "</postInputs></response>\n";
}


?>
