<?php
    include 'class.diagram.php';
    
//    $id = (isset($_GET['id']) ? (int) $_GET['id'] : 1);
//    if ($id < 1 && $id > 2) $id = 1;
    
//    $diagram = new Diagram(realpath('test' . $id . '.xml'));

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<diagram>
    <node name="this is the name">
        this is the data inside the node
    </node>
</diagram>
';


    $diagram = new Diagram();
    $diagram->loadXmlData($xml);

    $diagram->Draw();
?>
