<?php
    include 'class.diagram.php';
    
    $arr = array(
        'this' => array(
            'is',
            'just' => array(
                'a',
                'test'
            ),
            'to',
            'check' => array(
                'if',
                'the',
                'class' => array(
                    'works'
                )
            )
        )
    );
    
    $diagram = new Diagram();
    
    $diagram->setDefaultAlign(array('data' => 'center'));
    
    $diagram->setDefaultColor(array('connection' => '#f00', 'border' => '#f00'));
    
    $diagram->setDefaultDataColor(array('background' => '#fdd', 'color' => '#f00'));
    
    $diagram->loadFromArray($arr);
    
    $diagram->Draw();
?>
