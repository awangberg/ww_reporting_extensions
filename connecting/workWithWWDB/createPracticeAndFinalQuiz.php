<?php

if ($argc != 10) {
  die("Usage: createPracticeAndFinalQuiz.php <courseName> <studentID> <quizName> <startPracticeDate> <endPracticeDate> <answerPracticeDate> <startFinalQuizDate> <endFinalQuizDate> <answerFinalQuizDate>");
}

//remove first argument
array_shift($argv);

//get and use remaining arguments:
$courseName = $argv[0];
$studentID = $argv[1];
$quizName = $argv[2];
$startDate_practice = $argv[3];
$endDate_practice = $argv[4];
$answerDate_practice = $argv[5];
$startDate_finalQuiz = $argv[6];
$endDate_finalQuiz = $argv[7];
$answerDate_finalQuiz = $argv[8];

include("access.php");

$con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

$db = 'webwork';

if (mysql_select_db("$db", $con)) {
  //echo "selected database $db";
}
else {
  echo "Error selecting database $db: " . mysql_error();
}


//get the problems the user needs to practice, i.e. got wrong on the quiz:
$problem_user_table = $courseName . "_problem_user";
$set_id = $quizName . ",v1";

$query = 'SELECT problem_id, status FROM ' . $problem_user_table . ' WHERE set_id="' . $set_id . '" AND user_id="' . $studentID . '"';

$practiceConceptNumbers;
$finalCorrectNumbers;
$result = mysql_query($query, $con);
$testWasTakenByStudent = 0;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
  if ($row['status'] < 1) {
    $practiceConceptNumbers[] = $row['problem_id'];
    $testWasTakenByStudent = 1;
  }
  else {
    $finalCorrectNumbers[] = $row['problem_id'];
    $testWasTakenByStudent = 1;
  }
}

//If the user didn't actually take the initial quiz, then they don't have a version
//of the test to use.  All problems go into the practiceConceptNumbers array.
if ($testWasTakenByStudent == 0) {
  print "STUDENT $studentID did not take quiz $set_id\n";
  print "We are giving them all of the problems from that quiz\n";
  $set_id = $quizName;
  $query = 'SELECT problem_id FROM ' . $problem_user_table . ' WHERE  set_id="' . $set_id . '" AND user_id="' . $studentID . '"';
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    print "Giving them problem " . $row['problem_id'];
    $practiceConceptNumbers[] = $row['problem_id'];
  }
  print ".....query was $query.....\n";
}



$problem_table = $courseName . "_problem";

//for each of the practice concepts, get the conceptBank:
$conceptBankForProblemIndex;
$conceptBankForAllPracticeProblems = array();
$practiceProblems = array();
for ($i = 0; $i < count($practiceConceptNumbers); $i++) {
  $conceptBank = "";
  $query = 'SELECT source_file FROM ' . $problem_table . ' WHERE set_id="' . $quizName . '" AND problem_id=' . $practiceConceptNumbers[$i];
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $conceptBank = $row['source_file'];
  }

  $pattern = "/group\:/i";
  $replacement = "";
  $conceptBank = preg_replace($pattern, $replacement, $conceptBank);
  $conceptBankForProblemIndex[] = $conceptBank;
  $conceptBankForAllPracticeProblems = array_merge($conceptBankForAllPracticeProblems, explode(" ", "$conceptBank $conceptBank $conceptBank"));

  $sourceFileList = `php /var/www/html/connecting/workWithWWDB/get_n_ValidProblemsFromConceptBank.php 3 $courseName $conceptBank`;
  
  $tmp_practiceProblems = explode(" ", $sourceFileList);

  $practiceProblems = array_merge($practiceProblems, $tmp_practiceProblems);
}

// make a homework set with these practice problems for the user $studentID
// the practice set name is 'practice_$studentID_$quizName'

$set_table = $courseName . "_set";

$practiceSet = 'practice_' . $studentID . '_' . $quizName;

$query = "INSERT INTO $set_table (set_id, set_header, hardcopy_header, open_date, due_date, answer_date, published, assignment_type) ";
$query .= " VALUES ('$practiceSet', '', '', $startDate_practice, $endDate_practice, $answerDate_practice, 1, '')";

$result = mysql_query($query, $con);

// add these problems to the practice set for set practice_$studentID_$quizName
$problem_table = $courseName . "_problem";
// add these problems to the problem_user set for set practice_$studentID_$quizName
$problem_user_table = $courseName . "_problem_user";

for ($i = 0; $i < count($practiceProblems); $i++) {
  $sourceFile = $practiceProblems[$i];
  $tmpi = $i + 1;


  // add these problems to the practice set for set practice_$studentID_$quizName
  $query = "INSERT INTO $problem_table (set_id, problem_id, source_file, value, max_attempts) ";
  $query .= " VALUES('$practiceSet', $tmpi, '$sourceFile', 1, -1)";
  $result = mysql_query($query, $con);
  print "add problems to practice set practice_" . $studentID . "_" . $quizName . "\n";
  print $query . "\n";
  print "result: $result\n";


  // add these problems to the problem_user set for set practice_$studentID_$quizName
  $randomSeed = rand(0,9999);
  $query = "INSERT INTO $problem_user_table (user_id, set_id, problem_id, problem_seed, status, attempted, num_correct, num_incorrect) ";
  $query .= " VALUES('$studentID', '$practiceSet', $tmpi, $randomSeed, 0, 0, 0, 0)";
  $result = mysql_query($query, $con);
  print "add problem to _problem_user practice_" . $studentID . "_" . $quizName . "\n";
  print $query . "\n";
  print "result: $result\n";

  //record that this problem comes from this problem bank in the external database table to use with Session:
  $a = `php /var/www/html/connecting/workWithWWDB/recordConceptBankFor_courseID_userID_practiceSet_problemID_pgSourcefile.php $conceptBankForAllPracticeProblems[$i] $courseName $studentID $practiceSet $tmpi $sourceFile`;

  print "==================> $a";
}

//add the set to the _set_user table for this student:

$set_user_table = $courseName . "_set_user";
$query = "INSERT INTO $set_user_table (user_id, set_id) VALUES('$studentID', '$practiceSet') ";
$result = mysql_query($query, $con);
print "Added $practiceSet to homework sets for user $studentID\n";








//now, make the correct sets for the user and the final quiz:

if (count($finalCorrectNumbers) >= 1) {

  $practiceProblems = array();
  for ($i = 0; $i < count($finalCorrectNumbers); $i++) {
    $conceptBank = "";
    $query = 'SELECT source_file FROM ' . $problem_table . ' WHERE set_id="' . $quizName . '" AND problem_id=' . $finalCorrectNumbers[$i];
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $conceptBank = $row['source_file'];
    } 

    $pattern = "/group\:/i";
    $replacement = "";
    $conceptBank = preg_replace($pattern, $replacement, $conceptBank);

    $sourceFileList = `php /var/www/html/connecting/workWithWWDB/get_n_ValidProblemsFromConceptBank.php 1 $courseName $conceptBank`;
  
    $tmp_practiceProblems = explode(" ", $sourceFileList);

    $practiceProblems = array_merge($practiceProblems, $tmp_practiceProblems);
  }

  // make a correct homework sets with these practice problems for the user $studentID
  // the practice set name is 'final_$studentID_$quizName_tcerroc'

  $set_table = $courseName . "_set";

  $practiceSet = 'final_' . $studentID . '_' . $quizName . '_tcerroc1';

  $query = "INSERT INTO $set_table (set_id, set_header, hardcopy_header, open_date, due_date, answer_date, published, assignment_type) ";
  $query .= " VALUES ('$practiceSet', '', '', $startDate_practice, $endDate_practice, $answerDate_practice, 1, '')";

  $result = mysql_query($query, $con);

  $practiceSet = 'final_' . $studentID . '_' . $quizName . '_tcerroc2';

  $query = "INSERT INTO $set_table (set_id, set_header, hardcopy_header, open_date, due_date, answer_date, published, assignment_type) ";
  $query .= " VALUES ('$practiceSet', '', '', $startDate_practice, $endDate_practice, $answerDate_practice, 1, '')";

  $result = mysql_query($query, $con);

  // add these problems to the practice set for set practice_$studentID_$quizName
  $problem_table = $courseName . "_problem";
  for ($i = 0; $i < count($practiceProblems); $i++) {
    $sourceFile = $practiceProblems[$i];
    $practiceSet = 'final_' . $studentID . '_' . $quizName . '_tcerroc1';
    $tmpi = $i + 1;
    $query = "INSERT INTO $problem_table (set_id, problem_id, source_file, value, max_attempts) ";
    $query .= " VALUES('$practiceSet', $tmpi, '$sourceFile', 1, -1)";
    $result = mysql_query($query, $con);
  }

  for ($i = 0; $i < count($practiceProblems); $i++) {
    $sourceFile = $practiceProblems[$i];
    $practiceSet = 'final_' . $studentID . '_' . $quizName . '_tcerroc2';
    $tmpi = $i + 1;
    $query = "INSERT INTO $problem_table (set_id, problem_id, source_file, value, max_attempts) ";
    $query .= " VALUES('$practiceSet', $tmpi, '$sourceFile', 1, -1)";
    $result = mysql_query($query, $con);
  }

  $useCorrect1 = 1;
  $useCorrect2 = 1;
}
else {
  $useCorrect1 = 0;
  $useCorrect2 = 0;
}


//Make the final quiz for the student and this test:
//find random slots for the correct questions:
if ($useCorrect1 == 1) {
  $useCorrect1Slot = rand(0, count($practiceConceptNumbers) + 1);
  $useCorrect2Slot = rand(0, count($practiceConceptNumbers) + 1);
  while ($useCorrect1Slot == $useCorrect2Slot) {
    $useCorrect2Slot = rand(0,count($practiceConceptNumbers) + 1);
  }
}

//Make the final quiz:

$set_table = $courseName . "_set";

$practiceSet = 'finalQuiz_' . $studentID . '_' . $quizName;

$query = "INSERT INTO $set_table (set_id, set_header, hardcopy_header, open_date, due_date, answer_date, published, assignment_type, attempts_per_version, time_interval, versions_per_interval, version_time_limit, problem_randorder, problems_per_page, hide_score, hide_work, time_limit_cap, restrict_ip, relax_restrict_ip) ";
$query .= " VALUES ('$practiceSet', '', '', $startDate_finalQuiz, $endDate_finalQuiz, $answerDate_finalQuiz, 1, 'gateway', 1, 0, 1, 3600, 0, 1, 'N', 'N', 1, 'No', 'No')";
$result = mysql_query($query, $con);

//Insert the problems into the problem table
$problem_table = $courseName . "_problem";
$problem_user_table = $courseName . "_problem_user";

$index = 0;
$maxIndex = $useCorrect1 == 1 ? 2 : 0;
$maxIndex = $maxIndex + count($practiceConceptNumbers);

$redoConceptsIndex = 0;

print "maxIndex = " . $maxIndex . "\n";
print "useCorrect1Slot = $useCorrect1Slot\n";
print "useCorrect2Slot = $useCorrect2Slot\n";

for ($index = 0; $index < $maxIndex; $index++) {
  print "index: $index\n ";
  $sourceFile = "";
  if ($useCorrect1 == 1) {
    if ($index == $useCorrect1Slot) {
print "$index == $useCorrect1Slot use correct1Slot\n";
      $sourceFile = 'group:final_' . $studentID . '_' . $quizName . '_tcerroc1';
    }
    else if ($index == $useCorrect2Slot) {
print "$index == $useCorrect2Slot use correct2Slot\n";
      $sourceFile = 'group:final_' . $studentID . '_' . $quizName . '_tcerroc2';      
    }
    else {
print "$index is index\n";
print "conceptBankForProblemIndex[$redoConceptsIndex] = " . $conceptBankForProblemIndex[$redoConceptsIndex] . "\n"; 
      $sourceFile = "group:" . $conceptBankForProblemIndex[$redoConceptsIndex];
      $redoConceptsIndex = $redoConceptsIndex + 1;
    }
  }
  else {
print "just use index $index\n";
    $sourceFile = 'group:' . $conceptBankForProblemIndex[$index];
  }

  //put this problem into the quiz:
  $tmpi = $index + 1;
  $query =  "INSERT INTO $problem_table (set_id, problem_id, source_file, value, max_attempts) ";
  $query .= " VALUES('$practiceSet', $tmpi, '$sourceFile', 1, 1)";
  $result = mysql_query($query, $con);

print "inserted into $problem_table\n";

  //also put the problem into the problem_user table:
  $seed = rand(0,9999);
  $tmpi = $index + 1;
  $query =  "INSERT INTO $problem_user_table (user_id, set_id, problem_id, problem_seed, status, attempted, num_correct, num_incorrect) ";
  $query .= " VALUES('$studentID', '$practiceSet', $tmpi, $seed, 0, 0, 0, 0)";
//  $query =  "INSERT INTO $problem_user_table (user_id, set_id, problem_id) ";
//  $query .= " VALUES('$studentID', '$practiceSet', $tmpi)";
  $result = mysql_query($query, $con);

print "inserted into $problem_user_table\n";
}

// add the finalQuiz to the set_user table:
$set_user_table = $courseName . "_set_user";
$query =  "INSERT INTO $set_user_table (user_id, set_id) VALUES('$studentID', '$practiceSet')";
$result = mysql_query($query, $con);

mysql_close($con);

print "inserted into $set_user_table: " . $result . "\n";


?>

