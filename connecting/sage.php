<?php

$usersAnswer = $_GET['attempt'];
$correctAnswer = $_GET['Answer'];
$variable = $_GET['variables'];

//$usersAnswer = "2";

//$correctAnswer = "1 + sin(pi/2)";

print "user's Answer: $usersAnswer\n<BR>";

print "correct Answer: $correctAnswer\n<BR>";

print "variables: $variable\n<BR>";


$cmd = "sage -c '$variable = var(\"$variable\"); print generic_cmp($usersAnswer, $correctAnswer)'";

//$cmd = "sage -c 'x = var(\"x\"); print generic_cmp($usersAnswer, $correctAnswer)'";

$areTheyCorrect = exec("/usr/bin/sudo $cmd");

print "cmd = |$cmd|<BR>";

print "/usr/bin/sudo $cmd<BR>";

print "areTheyCorrect = $areTheyCorrect\n<BR>";

if ($areTheyCorrect == "") {
  print "Can't tell if they are correct.<BR>";
}

else if ($areTheyCorrect == 0) {
  print "Correct.  They are the same.<BR>";
}
else {
  print "Not Correct.<BR>";
}


print "done with sage\n";

?>
