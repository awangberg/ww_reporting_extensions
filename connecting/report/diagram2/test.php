<?php
    include 'class.diagram.php';
    
    $id = (isset($_GET['id']) ? (int) $_GET['id'] : 1);
    if ($id < 1 && $id > 2) $id = 1;
    
    $diagram = new Diagram(realpath('test' . $id . '.xml'));
    $diagram->Draw();
?>
