<?php

/*
 * PHP script to turn a LaTeX string into PNG, GIF, EPS, or JPEG
 *
 * Copyright (c) 2005 - 2008 David Hausheer
 * 
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this script; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
 * 
 */

function HexToRBG($hex) {
  $hex = ereg_replace("#", "", $hex);
  $color = array();

  if(strlen($hex) == 3) {
    $color['r'] = hexdec(substr($hex, 0, 1) . $r);
    $color['g'] = hexdec(substr($hex, 1, 1) . $g);
    $color['b'] = hexdec(substr($hex, 2, 1) . $b);
  }
  else if(strlen($hex) == 6) {
    $color['r'] = hexdec(substr($hex, 0, 2));
    $color['g'] = hexdec(substr($hex, 2, 2));
    $color['b'] = hexdec(substr($hex, 4, 2));
  }

  return $color;
}

$LATEX    = "openin_any=p /usr/bin/latex";
$DVIPNG   = "/usr/bin/dvipng";
//$TEMPDIR  = "png";
$TEMPDIR = "/tmp";
$TEMPNAM  = "eqn";

$source      = $_GET['source'];
$type = "png";
$density = $_GET['height'];
$preview = false;
$transparent = $_GET['transparent'];
$bgcolor = $_GET['bgColor'];
//$bgcolor = "D9D9F3";
//$fgcolor = $_GET['fgColor'];
$fgcolor = "000099";
$width = $_GET['textWidth'];

preg_match('/^(\d+)(\w+)/', $width, $matches);
$widthNum = $matches[1];
$widthUnits = $matches[2];

//$width = (130*$widthNum/100) . $widthUnits;
$width = round($widthNum,2);
$widthString = "$width$widthUnits";
//$width = '\setlength\textwidth{' . $width . '}';

$width = '\setlength\textwidth{' . $widthString . '}';

$height = $_GET['textHeight'];

preg_match('/^(\d+)(\w+)/', $height, $matches);
$heightNum = $matches[1];
$heightUnits = $matches[2];

//$height = (130*$heightNum/100) . $heightUnits;

$height = '\setlength\textheight{' . round($heightNum,2) . $heightUnits . '}';
//$height = '\setlength\textheight{' . $height . '}';


if($source && get_magic_quotes_gpc()) $source = stripslashes($source);

//$tmpname = tempnam($TEMPDIR, $TEMPNAM);
//$tmpname = "$TEMPDIR/$TEMPNAM";
//$tmpname = "$TEMPNAM";
$tmpname = tempnam($TEMPDIR, $TEMPNAM);
$density = 5*$density;

$density = 140;
$density = 100;

$source = strtr($source,"\n\r\t\0","    ");

//$source = "\\fbox{ $source }";

//$blah = HexToRBG($bgcolor);
//$source = $source . "bgcolor = " . $bgcolor . " goes to " . $blah['r'] . " and " . $blah['g'] . " and " . $blah['b'] . "]";

//$tex = "\documentclass[15pt,fleqn]{article} \usepackage{amssymb,amsmath,bm,color} \usepackage[latin1]{inputenc} $width $height \begin{document} \\"."thispagestyle{empty} \mathindent0cm \parindent0cm $source  \end{document}";

$tex = "\documentclass[10pt,fleqn]{article} \usepackage{amssymb,amsmath,bm,color} \usepackage{helvet} $width $height \begin{document} \\"."thispagestyle{empty} \mathindent0cm \parindent0cm {\bf $source}  \end{document}";

$handle = fopen($tmpname.".tex", "w");
fputs($handle, $tex);
fclose($handle);


exec("cd $TEMPDIR; $LATEX -interaction=nonstopmode ".substr($tmpname,5));

//header('Content-Type: image/png');
header('Content-Type: image/gif');

if ($transparent) $bg = "Transparent"; 
else $bg = "White";

if ($bgcolor) {
  $bg = "'rgb ";
  $blah = HexToRBG($bgcolor);
  $bgr = $blah['r']/256;
  $bgg = $blah['g']/256;
  $bgb = $blah['b']/256;
  $bg = "'rgb $bgr $bgg $bgb'";
}

$fg = "Blue";

if ($fgcolor) {
  $blah = HexToRBG($fgcolor);
  $fgr = $blah['r']/256;
  $fgg = $blah['g']/256;
  $fgb = $blah['b']/256;
  $fg = "'rgb $fgr $fgg $fgb'";
}

$tight = "tight";
//$tight = "20cm,14cm";
//$tight = "bbox";


passthru("$DVIPNG -q -T $tight --gif -bg $bg -fg $fg -D $density --gamma 2.0 -o /dev/stdout $tmpname.dvi");

exec("rm $tmpname*");
?>
