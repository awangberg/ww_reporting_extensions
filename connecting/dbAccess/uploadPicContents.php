<?php

if ($handle = opendir('uploads')) {
  echo "<ListOfPictures>\n";
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != "..") {
      echo "<picture>\n<Name>$file</Name>\n<fileName>$file</fileName>\n</picture>\n";
    }
  }
  closedir($handle);
  echo "</ListOfPictures>\n";
}
?>
