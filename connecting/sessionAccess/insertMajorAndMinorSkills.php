<?php

include("access.php");

$db = "session";

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database $db

$res = mysql_select_db("$db", $con);

$action = "";

$major_selected_skill_id = $_REQUEST['MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS'];
$major_skill_selected_name= str_replace(array("\r\n", "\n", "\r"), "", $_REQUEST['MAJOR_SKILL_SELECTED_NAME']);

if (isset($_REQUEST['Add_Major_Skill_To_Bank'])) {
  $major_skill_to_add = $_REQUEST['major_skill_to_add'];

  $query = "SELECT id, is_current FROM `major_skill` WHERE name='" . $major_skill_to_add . "'";
  $result = mysql_query($query, $con);
  $isInThere = false;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if ($row['is_current'] == 1) {
      $isInThere = true;
    }
  }

  if (!$isInThere) {
    $query = "INSERT INTO major_skill (name) VALUE ('" . $major_skill_to_add . "')";
    $result = mysql_query($query, $con);
    $action .= "<P>Trying to add $major_skill_to_add to List of Major Skills</P>";
    $action .= "<P>query is $query</P>";
    $action .= "<P>Added $major_skill_to_add to List of Major Skills</P>";
  }
  else {
    $action .= "<P>$major_skill_to_add was already in the table major_skill</P>";
  }
}

else if (isset($_REQUEST['Add_Minor_Skill_To_Bank'])) {
  $minor_skill_to_add = $_REQUEST['minor_skill_to_add'];

  $query = "SELECT id, is_current FROM `minor_skill` WHERE name='" . $minor_skill_to_add . "'";
  $result = mysql_query($query, $con);
  $isInThere = false;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if ($row['is_current'] == 1) {
      $isInThere = true;
    }
  }

  if (!$isInThere) {
    $query = "INSERT INTO minor_skill (name) VALUE ('" . $minor_skill_to_add . "')";
    $result = mysql_query($query, $con);
    $action .= "<P>Trying to add $minor_skill_to_add to List of Minor Skills</P>";
    $action .= "<P>query is $query</P>";
    $action .= "<P>Added $minor_skill_to_add to List of Minor Skills</P>";
  }
  else {
    $action .= "<P>$minor_skill_to_add was already in the table minor_skill</P>";
  }
}

else if (isset($_REQUEST['List_Minor_Skills_For_Major_Skill'])) {
  $major_selected_skill_id = $_REQUEST['Major_Skill'];

  //Get the name of the major skill selected:
  $query = 'SELECT name FROM `major_skill` WHERE id=' . $major_selected_skill_id;
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $major_skill_selected_name = $row['name'];
  }

  $action .= "<P>Selected Major Skill $major_skill_selected_name</P>\n";
}

else if (isset($_REQUEST['Connect_Minor_Skills_To_Major_Skill'])) {
  if ($major_selected_skill_id > 0) {  
    $add_minor_skills = $_REQUEST['Minor_Skill_To_Add'];
    foreach ($add_minor_skills as $minor_skill_id) {
      $query = "SELECT id, is_current FROM `connect_major_minor_skill` WHERE major_skill_id=$major_selected_skill_id AND minor_skill_id=$minor_skill_id";
      $result = mysql_query($query, $con);
      $existing_id = -1;
      $is_current = -1;
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$existing_id = $row['id'];
	$is_current = $row['is_current'];
      }
      if ($existing_id != -1) {
	if ($is_current == 1) {
          $action .= "Pair (major_skill = $major_selected_skill_id, minor_skill=$minor_skill_id) already exists<BR>\n";
        }
	else {
	  $query = "UPDATE `connect_major_minor_skill` SET is_current=1 WHERE id=$existing_id";
          $result = mysql_query($query, $con);
          $action .= "Pair (major_skill = $major_selected_skill_id, minor_skill=$minor_skill_id) already exists, but was not currently allowed<BR>\n";
	  $action .= ".... it is now allowed\n<BR>";
	}
      }
      else {
	$query = "INSERT INTO connect_major_minor_skill (major_skill_id, minor_skill_id) "
	       . " VALUE (" . $major_selected_skill_id . ", " . $minor_skill_id . ")";
	$result = mysql_query($query, $con);
	$action .= "<P>query is $query</P>\n";
	$action .= "<P>Added $minor_skill_id to List of Minor Skills for Major Skill $major_selected_skill_id</P>\n";
      }
    }
  }
}

else if (isset($_REQUEST['Remove_Minor_Skills_From_Major_Skill'])) {
  if ($major_selected_skill_id > 0) {
    $remove_minor_skills = $_REQUEST['Minor_Skill_To_Delete'];
    foreach ($remove_minor_skills as $minor_skill_id) {
      $query = "UPDATE `connect_major_minor_skill` SET is_current=0 WHERE "
	     . " major_skill_id=" . $major_selected_skill_id
	     . " AND minor_skill_id=" . $minor_skill_id;
      $result = mysql_query($query, $con);
      $action .= "Pair (major_skill = $major_selected_skill_id, minor_skill = $minor_skill_id) is now not allowed<BR>\n";
    }
  }
}

//List all of the major skills available:
$query = 'SELECT name, id FROM `major_skill` WHERE is_current=1';
$result = mysql_query($query, $con);
$major_list = "<TABLE><TR><TD VALIGN='TOP'>\n";
$count = 0;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  if (($major_selected_skill_id > 0) && ($row['id'] == $major_selected_skill_id)) {
    $major_list .= "<input type='radio' name='Major_Skill' value=" . $row['id'] . " CHECKED>" . $row['name'] . "<BR>\n";
  }
  else {
    $major_list .= "<input type='radio' name='Major_Skill' value=" . $row['id'] . ">" . $row['name'] . "<BR>\n";
  }
  $count++;
  if ($count == 12) {
    $major_list .= "</TD><TD VALIGN='TOP'>\n";
    $count = 0;
  }
}
$major_list .= "</TD></TR></TABLE>\n";

//List all of the minor skills available:
$query = 'SELECT name, id FROM `minor_skill` WHERE is_current=1';
$result = mysql_query($query, $con);
$minor_list = "";
$minor_id_name_array;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  $minor_id_name_array[$row['id']] = $row['name'];  
}

if ($major_selected_skill_id > 0) {
  $count = 0;
  $minor_list = "<TABLE><TR><TD VALIGN='TOP'>\n";
  foreach ($minor_id_name_array as $minor_id => $minor_name) {
    $query = "SELECT id, is_current FROM `connect_major_minor_skill` "
	   . " WHERE major_skill_id=$major_selected_skill_id "
	   . " AND minor_skill_id=$minor_id ";
    $result = mysql_query($query, $con);
    $is_current = -1;
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $is_current = $row['is_current'];
    }
    if ($is_current == 1) {
      $minor_list .= "X $minor_name<BR>";
    }
    else {
      $minor_list .= "<input type='checkbox' name='Minor_Skill_To_Add[]' value=" . $minor_id . ">" . $minor_name . "<BR>\n";
    }    
    $count++;
    if ($count == 12) {
      $minor_list .= "</TD><TD VALIGN='TOP'>";
      $count = 0;
    }
  }
  $minor_list .= "</TD></TR></TABLE>\n";
}
else {
  $count = 0;
  $minor_list = "<TABLE><TR><TD VALIGN='TOP'>\n";
  foreach ($minor_id_name_array as $minor_id => $minor_name) {
//    $minor_list .= "<input type='checkbox' name='Minor_Skill[]' value=" . $row['id'] . ">" . $row['name'] . "<BR>\n";
    $minor_list .= "X $minor_name<BR>\n";
    $count++;
    if ($count == 12) {
      $minor_list .= "</TD><TD VALIGN='TOP'>";
      $count = 0;
    }
  }
  $minor_list .= "</TD></TR></TABLE>\n";
}


if ($major_selected_skill_id > 0) {
  $query = 'SELECT connect_major_minor_skill.minor_skill_id, minor_skill.name '
	 . ' FROM `connect_major_minor_skill` '
	 . ' LEFT JOIN `minor_skill` ON minor_skill.id = connect_major_minor_skill.minor_skill_id '
	 . ' WHERE major_skill_id=' . $major_selected_skill_id 
	 . ' AND connect_major_minor_skill.is_current=1';

  //print "query to populate the minor list of skills for major skill is <P>$query\n";
  $result = mysql_query($query, $con);
  $list_of_connected_minor_skills = "<TABLE><TR><TD VALIGN='TOP'>";
  $count = 0;
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $list_of_connected_minor_skills .= "<input type='checkbox' name='Minor_Skill_To_Delete[]' value='" . $row['minor_skill_id'] . "'>" . $row['name'] . "<BR>\n";
    $count++;
    if ($count == 12) {
	$list_of_connected_minor_skills .= "</TD><TD VALIGN='TOP'>";
	$count = 0;
    }
  }
  $list_of_connected_minor_skills .= "</TR></TABLE>";
}

//close connection
mysql_close($con);
print $action;
?>
<TABLE>
<TR><TD VALIGN="TOP"Major Skill</TD><TD VALIGN="TOP"Minor Skill</TD></TR>
<TR><TD VALIGN="TOP"
<form method="post" action="">
<input type="hidden" name="MAJOR_SKILL_SELECTED_NAME" value="
<?
print $major_skill_selected_name;
?>
">
<input type="hidden" name="MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS" value="
<?
print $major_selected_skill_id;
?>
">
<input type="text" name="major_skill_to_add" size="25" value="">
<input type="submit" name="Add_Major_Skill_To_Bank" value="Add Major Skill To Bank">
</form>
</TD><TD VALIGN="TOP"
<form method="post" action="">
<input type="hidden" name="MAJOR_SKILL_SELECTED_NAME" value="
<?
print $major_skill_selected_name;
?>
">
<input type="hidden" name="MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS" value="
<?
print $major_selected_skill_id;
?>
">
<input type="text" name="minor_skill_to_add" size="25" value="">
<input type="submit" name="Add_Minor_Skill_To_Bank" value="Add Minor Skill To Bank">
</form>
</TD>
</TR>
<TR>
<TD VALIGN="TOP"
List of All Major Skills
<BR><HR>
<form method="post" action="">
<input type="hidden" name="MAJOR_SKILL_SELECTED_NAME" value="
<?
print $major_skill_selected_name;
?>
">
<input type="hidden" name="MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS" value="
<?
print $major_selected_skill_id;
?>
">
<input type="submit" name="List_Minor_Skills_For_Major_Skill" value="List Minor Skills for This Major Skill">
<BR>
<?
print $major_list;
?>
</form>
</TD>
<TD VALIGN="TOP"
List of All Minor Skills
<BR><HR>
<form method="post" action="">
<input type="hidden" name="MAJOR_SKILL_SELECTED_NAME" value="
<?
print $major_skill_selected_name;
?>
">
<input type="hidden" name="MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS" value="
<?
print $major_selected_skill_id;
?>
">
<?
if ($major_selected_skill_id > 0) {
  print '<input type="submit" name="Connect_Minor_Skills_To_Major_Skill" value="Connect Minor Skill To Major Skill ';
  print $major_skill_selected_name;
  print '">';
}
else {
  print "Please select a major skill to add minor skills to it.\n";
}
?>
<BR>
<?
print $minor_list;
?>
<BR><HR>
</form>
</TD>
</TR>
<TD COLSPAN=2>
<form method="post" action="">
<input type="hidden" name="MAJOR_SKILL_SELECTED_NAME" value="
<?
print $major_skill_selected_name;
?>
">
<input type="hidden" name="MAJOR_SKILL_SELECTED_FOR_MINOR_SKILLS" value="
<?
print $major_selected_skill_id;
?>
">

<BR>Minor Skills for Major Skill: 
<?
print $major_skill_selected_name;
?>


<input type="submit" name="Remove_Minor_Skills_From_Major_Skill" value="Remove Minor Skill From Major Skill 
<?
print $major_skill_selected_name;
?>
">
<BR>
<?
print $list_of_connected_minor_skills;
?>
<HR>
</form>
</TD>
</TR>
</TABLE>
</HTML>


