<?php


include("access.php");

$db = "wwSession";



$con = mysql_connect($db_host, $db_user, $db_pass);

if(!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$res = mysql_select_db("$db", $con);

print "<P>Adding questions for R1";
$qs = array(1, 2, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 17, 20, 22, 23);
$tax_id = 1;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}



print "<P>Adding questions for R2";
$qs = array(15, 18, 19, 24, 25);
$tax_id = 2;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for R3";
$qs = array(1, 3, 4, 10, 11, 14, 16, 17, 21);
$tax_id = 3;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for ME";
$qs = array(1, 5, 6, 11, 12, 16, 20);
$tax_id = 4;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for MR";
$qs = array(8, 10, 11, 15, 19, 22);
$tax_id = 5;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for MC";
$qs = array(4, 5, 12, 16, 17, 20, 23);
$tax_id = 6;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for MI";
$qs = array(2, 4, 9, 10, 13, 14, 23);
$tax_id = 7;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for GL";
$qs = array(3, 10, 22);
$tax_id = 8;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for GE";
$qs = array(7);
$tax_id = 9;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for GR";
$qs = array(18, 25);
$tax_id = 10;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for GN";
$qs = array(15, 19, 24);
$tax_id = 11;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for RG";
$qs = array(2, 5, 6, 8, 9, 10, 15, 19, 24);
$tax_id = 12;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for RA";
$qs = array(1, 4, 7, 10, 11, 14, 16, 17, 18, 21, 22, 23, 25);
$tax_id = 13;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for RN";
$qs = array(3, 12, 13);
$tax_id = 14;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}

print "<P>Adding questions for RC";
$qs = array(3, 4, 7, 8, 10, 11, 15, 17, 18, 20, 22);
$tax_id = 15;
foreach ($qs as $qi) {
  $ww_set = "pca_q" . $qi;
  $sql = "INSERT into pca_taxonomy (taxonomy_id, webwork_set) VALUES ('$tax_id', '$ww_set')";
  $result = mysql_query($sql, $con);
  print "<P>Query: $sql</P>";
}
mysql_close($con);

?>
