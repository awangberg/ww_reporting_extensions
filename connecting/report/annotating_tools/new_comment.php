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
	//responseToSessionCommentID	provided if this comment was a follow-up comment to another comment
//First, get the data to store in the comment database.
//Store the comment in the comment database.
//Return the id of the new comment.

header("content-type: text/xml");

if (isset($_REQUEST['replayToSessionTime']) && 
    isset($_REQUEST['sessionProblemID']) &&
    isset($_REQUEST['time']) &&
    isset($_REQUEST['commenter']) &&
    isset($_REQUEST['keyFields']) &&
    isset($_REQUEST['comment']) &&
    isset($_REQUEST['responseToSessionCommentID'])) {

  //connect to the wwSession database:
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }
  $db = "wwSession";
  $result = mysql_select_db("$db", $con);

  //make a new record in the sessionComments table:
  $query = 'INSERT INTO sessionComments(session_problem_id, commenter, comment, replay_time_for_comment_ms, replay_time_for_comments_human_seconds, response_to_comment_id) ';
  $query .= " VALUES ("
			. $_REQUEST['sessionProblemID']
			. ", '"
			. mysql_real_escape_string($_REQUEST['commenter'])
			. "', '"
			. mysql_real_escape_string($_REQUEST['comment'])
			. "', "
			. $_REQUEST['replayToSessionTime']
			. ", "
			. $_REQUEST['time']
			. ", ";
  $query .= $_REQUEST['responseToSessionCommentID'] > 0 ? $_REQUEST['responseToSessionCommentID'] : -1;
  $query .= ")";
  //add this comment to the database:
  $result = mysql_query($query, $con);

  //remember the comment_id of this new entry:
  $comment_id = mysql_insert_id();



  //now, associate the comment keys to this comment:
  $theKeys = explode(",", $_REQUEST['keyFields']);

  foreach ($theKeys as $k => $keyString) {
    //get the key id from the sessionCommentKeysPossible table
    //then, insert that key id into the sessionCommentKeys table and associate
    //that key id with the comment_id which we just entered.
    if ($keyString == "") {

    }
    else {
      $query = "SELECT key_id FROM `sessionCommentKeysPossible` WHERE shortkey='" . mysql_real_escape_string($keyString) . "'";
      $result = mysql_query($query, $con);
      $key_id = -1;
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $key_id = $row['key_id'];
      }

      if ($key_id > -1) {
        $query = "INSERT INTO sessionCommentKeys(session_problem_id, comment_id, key_id, record_valid) VALUES ("
		. $_REQUEST['sessionProblemID']
		. ", "
		. $comment_id
		. ", "
		. $key_id
		. ", "
		. "TRUE"
		. ")";
        $result = mysql_query($query, $con);
      }
    }
  }


  mysql_close($db);  
  print "<response><problemCommentID>" . $comment_id . "</problemCommentID><query>" . $query . "</query></response>\n";

}
else {
  print "<response><error>Not enough fields</error><requestInputs>";
  print_r($_REQUEST);
  print "</requestInputs><postInputs>";
  print_r($_POST);
  print "</postInputs></response>\n";
}


?>
