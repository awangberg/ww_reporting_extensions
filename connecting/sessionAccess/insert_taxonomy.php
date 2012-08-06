<?php

include("access.php");

$db = "wwSession";

$con = mysql_connect($db_host, $db_user, $db_pass);

if (!$con) {
  die('Could not connect: ' . mysql_error());
}

//select the database '$db'
$sql = mysql_select_db("$db", $con);

print "<P>Adding R1";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES ('R1', 'Process view of function', 'View a function as a generalized process that accepts input and produces output.  Appropriate coordination of multiple function processes.')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";


print "<P>Adding R2";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES ('R2', 'Covariational reasoning', 'Coordinate two varying quantities that change in tandem while attending to how the quantities change in relation to each other')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";


print "<P>Adding R3";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES ('R3', 'Computational abilities', 'Identify and apply appropriate algebraic manipulations and procedures to support creating and reasoning about function models')";

$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";


print "<P>Adding ME";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('ME', 'Function evaluation meaning', 'Understand meaning of function concepts related to function evaluation')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding MR";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('MR', 'Rate of change meaning', 'Understand meaning of function concepts related to rate of change')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding MC";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('MC', 'Function composition meaning', 'Understand meaning of function concepts related to function composition')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding MI";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('MI', 'Function inverse meaning', 'Understand meaning of function concepts related to function inverse')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding GL";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('GL', 'Linear growth', 'Understand growth rates of linear functions')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding GE";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('GE', 'Exponential growth', 'Understand growth rates of exponential functions')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding GR";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('GR', 'Rational growth', 'Understand growth rates of rational functions')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding GN";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('GN', 'General non-linear growth', 'Understand growth rates of general non-linear functions')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding RG";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('RG', 'Graphical representations', 'Understand graphical function representations (interpret, use, construct, connect)')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding RA";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('RA', 'Algebraic representations', 'Understand algebraic function representations (interpret, use, construct, connect)')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</P>";

print "<P>Adding RN";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('RN', 'Numerical representations', 'Understanding numerical function representations (interpret, use, construct, connect)')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</p>";

print "<P>Adding RC";
$sql = "INSERT INTO taxonomy (tax_key, short_desc, description) VALUES('RC', 'Contextual representations', 'Understanding contextual function representations (interpret, use, construct, connect)')";
$result = mysql_query($sql, $con);
print "<P>Query: $sql</p>";

mysql_close($con);

?>
