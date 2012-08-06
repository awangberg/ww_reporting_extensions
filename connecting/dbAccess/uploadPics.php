<?php

$uploads_dir = 'uploads';
move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $uploads_dir . "/" . $_FILES['uploadedFile']['name']);
echo $_FILES['uploadedFile']['name'];
?>
