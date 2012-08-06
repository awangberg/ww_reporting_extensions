<?php
    include 'class.diagram.php';
    include 'class.diagram-ext.php';

    $diagram = new DiagramExtended(dirname(__FILE__) . '/test2.xml');
    $data = $diagram->getNodePositions();
?>
<html>
    <head>
        <title>Diagram Extended</title>
    </head>
    <body>
        <img src="test.php?id=2" border="0" style="position:absolute;left:0;top:0;" />
<?php
    $selected = (isset($_GET['name']) ? $_GET['name'] : null);
    echo_map($data, $selected);

    function echo_map(&$node, $selected) {
        echo "<a href=\"?name={$node['name']}\"><div style=\"position:absolute;left:{$node['x']};top:{$node['y']};width:{$node['w']};height:{$node['h']};" . ($selected == $node['name'] ? "background-color:red;filter:alpha(opacity=40);-moz-opacity:0.4;" : "") . "\">&nbsp;</div></a>\n";
//      echo "<a href=\"http://www.startribune.com\"><div style=\"position:absolute;left:{$node['x']};top:{$node['y']};width:{$node['w']};height:{$node['h']};" . ($selected == $node['name'] ? "background-color:red;filter:alpha(opacity=40);-moz-opacity:0.4;" : "") . "\">&nbsp;</div></a>\n";
        for ($i = 0; $i < count($node['childs']); $i++) {
            echo_map($node['childs'][$i], $selected);
        }
    }
?>
    </body>
</html>
