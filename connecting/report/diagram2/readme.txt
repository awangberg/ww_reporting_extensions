    Hi,

    This class is so simple I don't think you'll need documentation. The
class draws a diagram with only one root node. You can define properties
for every node on the diagram.
    After instanciating the class you can pass the diagram structure in
2 diferent ways: xml file or xml data. If you pass the xml file it will
then call the xml data function so if you're just opening the xml file
and passing it to the function.. don't bother, just use the xml file
feature.
    The function names are very simple:

        - loadXmlFile()

        With this function you can pass a file path as an argument. It will
        open the file and send the data to loadXmlData() to be parsed.

        - loadXmlData()

        With this function you can pass xml directly to the xml parser of
        the function.

        - Draw()

        After using one of the above function you should call this one
        to draw the diagram. The optional argument can be a path to
        where the image should be saved. If no argument is set, the image
        will be sent to stdout with the apropriate header.

    Now that you know all you need to build a diagram, you might be
wondering how the xml file is. Well, it's simple. The basic structure
is like this:


<?xml version="1.0" encoding="UTF-8"?>
<diagram>
    <node name="this is the name">
        this is the data inside the node
    </node>
</diagram>


    To load this file you would do:

<?php
    include 'class.diagram.php';

    $diagram = new Diagram(realpath('myfirstdiagram.xml'));
    $diagram->Draw();
?>

    This would work if you save the previous xml in myfirstdiagram.xml
and both the file and the class are in the same directory as this php
file. Confused? Hope not.
    You have 2 examples. One simple and another a bit more complex with
two-color backgrounds and text over lines. Hope you like it.
    To view them simply view test1.html and test2.html (on your server,
not offline because it calls test.php).
    Any doubt, e-mail me at: me (at) diogoresende (dot) net

    Diogo Resende