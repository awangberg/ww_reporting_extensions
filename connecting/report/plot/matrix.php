<?php

function transpose($a1) {
  $m = array();

  foreach ($a1 as $i => $row) {
    foreach ($row as $j => $val) {
      $m[$j][$i] = $val;
    }
  }
  return $m;
}

function rows($a) {
  return count($a);
}

function cols($a) {
  $max_cols = 0;
  for ($i = 0; $i < rows($a); $i++) {
    if (array_key_exists($i, $a)) {
      $max_cols = count($a[$i]) > $max_cols ? count($a[$i]) : $max_cols;
    }
  }
  return $max_cols;
}

function mult($a, $b) {
  $col_a = cols($a);
  $row_a = rows($a);
  $row_b = rows($b);
  $col_b = cols($b);
  $m = array();

  //initialize the result matrix:
  for ($i = 0; $i < $row_a; $i++) {
    $m[$i] = array();
    for ($j = 0; $j < $col_b; $j++) {
      $m[$i][$j] = 0;
    }
  }

  for ($r = 0; $r < $row_a; $r++) {
    for ($c = 0; $c < $col_b; $c++) {
      for ($s = 0; $s < $col_a; $s++) {
         if (array_key_exists($r, $a) && array_key_exists($s, $a[$r]) &&
             array_key_exists($s, $b) && array_key_exists($c, $b[$s])) {
           $m[$r][$c] += 
              $a[$r][$s] * 
              $b[$s][$c];
         }
      }
    }
  }

//  print "returning ";
//  print_m($m);
  return $m;
}


function inverse_m($a) {
  $inv = array();
  $size = rows($a);

  //set all entries in $inv to 0:
  for ($r = 0; $r < $size; $r++) {
    for ($c = 0; $c < $size; $c++) {
      $inv[$r][$c] = 0;
    }
  }

  //set 1's on diagonal of $inv
  for ($r = 0; $r < $size; $r++) {
    $inv[$r][$r] = 1;
  }

  //get 0 below diagonal:
  for ($d = 0; $d < $size; $d++) {
    $diag_ent = $a[$d][$d];
    //for each row below $d:
//    print "________\n";
//    print "diag_ent[$d] = $diag_ent\n";
//    print_m($a);
    for ($r = $d + 1; $r < $size; $r++) {
      $factor = $a[$r][$d] / $diag_ent;
//      print "entry $r,$d:  factor is $factor\n";
      //Do row entry -  factor * $d row entry to every column in the row.
      for ($i = 0; $i < $size; $i++) {
        $a[$r][$i] = $a[$r][$i] - $factor * $a[$d][$i];
        $inv[$r][$i] = $inv[$r][$i] - $factor * $inv[$d][$i];
      }
    }
//    print "a is now:\n";
//    print_m($a);
//    print "\n\n inv is now:\n";
//    print_m($inv);
  }

//  print "back substitution phase:\n";

  //get 0 above the diagonal, but this time work from $size to 0:
  for ($d = $size - 1; $d >= 0; $d--) {
    $diag_ent = $a[$d][$d];
    //for each row above $d:
//    print "^^^^^^^^^^\n";
//    print "diag_ent[$d] = $diag_ent\n";
//    print_m($a);
    for ($r = $d - 1; $r >= 0; $r--) {
      $factor = $a[$r][$d] / $diag_ent;
//      print "entry $r,$d: factor is $factor\n";
      //Do row entry - factor * $d row entry to every column in the row.
      for ($i = 0; $i < $size; $i++) {
        $a[$r][$i] = $a[$r][$i] - $factor * $a[$d][$i];
        $inv[$r][$i] = $inv[$r][$i] - $factor * $inv[$d][$i];
      }
    }
//    print "a is now: \n";
//    print_m($a);
//    print "\n\n inv is now:\n";
//    print_m($inv);
  }

  //divide each row by the quantity on the diagonal in $a:
  for ($d = 0; $d < $size; $d++) {
    $diag_ent = $a[$d][$d];
    $a[$d][$d] = $a[$d][$d] / $diag_ent;
    for ($i = 0; $i < $size; $i++) {
      $inv[$d][$i] = $inv[$d][$i] / $diag_ent;
    }
  }
  return $inv;
}

function print_m($a) {
  for ($r = 0; $r < rows($a); $r++) {
    print "( ";
    for ($c = 0; $c < cols($a); $c++) {
      print round($a[$r][$c], 2) . " ";
    }
    print " )\n";
  }
}

?>

