<?php

include("access.php");
include("common.php");

ini_set("memory_limit","300M");

function javascript() {
  print "
    <script src='../../flot/jquery.js'></script>
    <script>
    $(document).ready(function(){
      $('#gen_table').click(function(event) {
        event.preventDefault();

        var arr_concs = new Array();
        var arr_peops = new Array();
        var arr_groups = new Array();

        var concs;
        var peops;
        var tab_string;
        var retstring;
	var progress_string;
        var n = 0;
        var max_concepts = 0;
        var max_groups = 0;

	$('#concepta option:selected').each(function () {
	  arr_concs[n] = $(this).val();
	  max_concepts = n;
	  n++;
	});
	n = 0;
	$('#groups option').each(function () {
	  arr_peops[n] = $(this).val();
	  arr_groups[n] = $(this).text();
	  max_groups = n;
	  n++;
	});

        var tmp_s;
        var tmp_q;
        var tmp_u_c = new Array();
        var tmp_i = 0;
        //set up the top row of the table:
        tab_string = '<table>';
        tab_string += '<tr><th>Week</th><th>Problem</th><th align=\'right\'>  Student Group:</th>';

        for (var i = 0; i <= max_groups; i++) {
          tab_string += '<th>' + i + ') ';
//          var the_peops = arr_peops[i].split('_____');
//          for (var j = 0; j < the_peops.length; j++) {
//            //username ___ course
//           tmp_s = the_peops[j];
//           tmp_u_c = tmp_s.split('___');
//           if ((tmp_u_c[1] == 'Array') || (tmp_u_c[1] == undefined)) { }
//           else {
//             tab_string += tmp_u_c[0] + ' ' + tmp_u_c[1];
//           }
//          }
	  tab_string += arr_groups[i];
          tab_string += '</th>';
        }
        tab_string += '</tr>';

        for (var h = 0; h <= max_concepts; h++) {
          tab_string += '<tr><td>';
	  var tmp_q = arr_concs[h].split(' ');
	  tab_string += tmp_q[0] + '</td><td colspan=2>' + tmp_q[1];
          tab_string += '</td>';
	  for (var i = 0; i <= max_groups; i++) {
            retstring = 'http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_quiz_work.php?';
            retstring += 'print_user_name=users&map_width=';
	    retstring += $('#img_width').val();
	    retstring += '&pixels_per_second=';
	    retstring += $('#img_pps').val();
	    retstring += '&path=';
	    retstring += arr_groups[i].split(' ',1);

	    progress_string =  'http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/annotating_progress.php?';

	    var the_peops = arr_peops[i].split('_____');
            tab_string += '<td>';
            for (var j = 0; j < the_peops.length; j++) {
              //username ___ course
	      tmp_s = the_peops[j];
	      tmp_u_c = tmp_s.split('___');
              if ((tmp_u_c[1] == 'Array') || (tmp_u_c[1] == undefined)) { }
              else {
		var tmpstring = '';
                tmpstring += '&do_these_students[' + j + ']=';
                tmpstring += tmp_u_c[1] + '___' + tmp_u_c[0];
                tmpstring += '___';
		tmpstring += tmp_q[1];
		tmpstring += '___';
		tmpstring += tmp_q[0];
		retstring += tmpstring;
		progress_string += tmpstring;
                //tab_string += tmp_u_c[0] + ' ' + tmp_u_c[1];
	      }
	    }
 	    tab_string += 'Analyze: ';
	    tab_string += '\<a href=\'' + retstring + '\' target=\'other\'><img src=\'' + progress_string + '&commenter=NE_gk_ADW\'>\</a>';
            tab_string += '</td>';
          }
          tab_string += '</tr>';
        }
        tab_string += '</table>';

        $('#generated_table').replaceWith('\<span id=\'generated_table\'>' + tab_string + '\</span>');
      });

      $('#g').click(function(event){
        event.preventDefault();
        var arr_concs = new Array();
        var arr_peops = new Array();
        var arr_groups = new Array();

        var concs;
        var peops;
        var retstring;
        var n = 0;
        var max_concepts = 0;
        var max_groups = 0;
        $('#concepta option:selected').each(function () {
          arr_concs[n] = $(this).val();
//          retstring += n + ': ' + $(this).val() + ' ';
          max_concepts = n;
          n++;
        });
        n = 0;
        $('#groups option:selected').each(function () {
          arr_peops[n] = $(this).val();
          arr_groups[n] = $(this).text();
          max_groups = n;
          n++;
        });
        //now, split arr_peops[n] by using arr_peops[i].split('______');
	if (0) {
	        alert(arr_concs);
        	alert(arr_peops);
        	alert(arr_groups);
	}

        retstring = 'http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_quiz_work.php?';
        retstring += 'print_user_name=users&map_width=';
        retstring += $('#img_width').val();
        retstring += '&pixels_per_second=';
        retstring += $('#img_pps').val();
        var tmp_s;
        var tmp_q;
        var tmp_u_c = new Array();
        var tmp_i = 0;
        for (var i = 0; i <= max_groups; i++) {
          retstring += '&path=';
          retstring += arr_groups[i].split(' ',1);
          var the_peops = arr_peops[i].split('_____');
//alert(the_peops);
          for (var j = 0; j < the_peops.length; j++) {
            for (var k = 0; k <= max_concepts; k++) {
              //username ___ course
              tmp_s = the_peops[j];
              tmp_u_c = tmp_s.split('___');
              if ((tmp_u_c[1] == 'Array') || (tmp_u_c[1] == undefined)) { }
              else {
                retstring += '&do_these_students[' + tmp_i + ']=';
                retstring += tmp_u_c[1] + '___' + tmp_u_c[0];
                retstring += '___';
                tmp_q = arr_concs[k].split(' ');
                retstring += tmp_q[1];
                retstring += '___';
                retstring += tmp_q[0];
                tmp_i++;
              }
            }
          }         
        }
        $('#sorted_group_link').replaceWith('\<span id=\'sorted_group_link\'>\<a href=\'' + retstring + '\' target=\'other\'>Analyze this group\</a>\</span>');
        //$('#sorted_group_link').append(retstring);
        //alert(retstring);
      });

    });
    </script>
  ";
}

function attach_end($str) {
  $ret = trim($str);
  if ($ret == "") { return "False;"; }
  if ($ret == ";") { return "False;"; }
  if(substr($str, -1) != ";") {$ret .= ";"; }
  return $ret;
}

function min_of_one($total) {
  $tmp_total = isset($total) ? $total : 1;
  return $tmp_total == 0 ? 1 : $tmp_total;
}

function xmlPreOrder($xml, $key, $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch = "Wrong:", $rightBranch = "Right:", $decorations = array("")) {

  $maxLine = 10;
  $rightColor = "#375b4e";
  $wrongColor = "#876c52";

  $wrongColor = "#871d00";
  $rightColor = "#00325e";
  $baseColor = "#526388";


  $connectionName = "";
  $nodeColorInfo = ' namecolor="#f" ';
  $nodeColorInfo .= ' bgcolor="#d9e3ed" bgcolor2="#f" namealign="center" width="60" align="center" margintop="16" ';

//  $nodeColorInfo = ' namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88" namealign="center" width="60" align="center" margintop="16" ';

  $borderConnection = '';
  $connectioncolor = ' connectioncolor="#526e88" ';

  $updown = "up";
  $amt = 0;

  if ($key == "") {
    $nodeName = $shortConcepts[1];
    //$nodeName = substr($nodeName, 0, -1);
    $description = $concepts[1];
    $concept = 1; 
    $description = "";
    $updown = "down";
    $amt = 25;
  }
  else if (strlen($key) == $maxLength) {
    $nodeName = (substr($key, -1) == 1) ? substr($rightBranch, 0, -1) : substr($wrongBranch, 0, -1);
    $description = array_key_exists($key, $PartRoute) ? $PartRoute[$key] : "";
//. $decorations[$key] . ' (' . round(100*$PartRoute[$key]/$total) . '%)' ;
    $description .= array_key_exists($key, $decorations) ? $decorations[$key] : "";
    if (array_key_exists($key, $PartRoute)) {
      $description .= ' (' . round(100*$PartRoute[$key]/min_of_one($total)) . '%)';
    }
    else {
      $description .= " (0%)";
    }   
//    $nodeName .= ": " . $PartRoute[$key] . ' (' . round(100*$PartRoute[$key]/min_of_one($total)) . '%)' ;
    if (!array_key_exists($key, $decorations) || ($decorations[$key] == "")) {
      $borderSize = (array_key_exists($key, $PartRoute) && $PartRoute[$key] >= 1) ? round(1 + $maxLine*$PartRoute[$key]/min_of_one($total)) : 1;
    }
    else {
      // (S+L)/(1+S-L):  $tmpBorderSize = "(" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . $total . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " - " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . $total . ")";
      // (S+2L)/(1+S-2L):
      //$tmpBorderSize = "(" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . $total . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " - 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . $total . ")";
      // (S+2L)/(1+S+2L)
      $tmpBorderSize = "(" . 
                       ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . 
                       $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . min_of_one($total) . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . min_of_one($total) . ")";

      eval("\$borderSize = 0 + $maxLine*$tmpBorderSize;");
      $borderSize = round($borderSize);
    }
    $borderConnection = ' borderconnection="' . $borderSize . '" ';

    $connectioncolor = ' connectioncolor="';
    $amt = array_key_exists($key, $PartRoute) ? 
            round(25/$maxLine)*(round($maxLine*$PartRoute[$key]/min_of_one($total)) - 2/3*$maxLine) :
            round(25/$maxLine)*(round($maxLine*1/min_of_one($total)) - 2/3*$maxLine);
    $updown = $amt >= 0 ? "down" : "up";
    $amt = abs($amt);
    if (isset($_REQUEST['colorConnection']) && ($_REQUEST['colorConnection'] == 'colorConnection')) {
      $connectioncolor .= (substr($key, -1) == 1) ? hex_shift($rightColor, $updown , $amt) : hex_shift($wrongColor, $updown, $amt) ;
    }
    else {
      $connectioncolor .= hex_shift($baseColor, $updown, $amt);
    }
    $connectioncolor .= '" ';
  }
  else { 
    $concept = strlen($key) + 1;
    $nodeName = $shortConcepts[$concept]; 
    //$nodeName = substr($nodeName, 0, -1);
    $word = (substr($key, -1) == 1) ? "$rightBranch " : "$wrongBranch ";
    if (isset($_REQUEST['colorConnection']) && ($_REQUEST['colorConnection'] == 'colorConnection')) {
      $connectioncolor = ' connectioncolor="';
      $connectioncolor .= (substr($key, -1) == 1) ? $rightColor : $wrongColor;
      $connectioncolor .= '" ';
    }
    $connectionName = ' connectionName="' . $word;
    $connectionName .= array_key_exists($key, $PartRoute) ? $PartRoute[$key] : "";
    $connectionName .= array_key_exists($key, $decorations) ? $decorations[$key] : "";
    $connectionName .= '"';
    $description = "Description: ";
    $description .= array_key_exists($concept, $concepts) ? $concepts[$concept] : "";
    //$borderSize = $PartRoute[$key] >= 1 ? round(1 + $maxLine*$PartRoute[$key]/min_of_one($total)) : 1;
    if (!array_key_exists($key, $decorations) || ($decorations[$key] == "")) {
      if (array_key_exists($key, $PartRoute)) {
        $borderSize = $PartRoute[$key] >= 1 ? round(1 + $maxLine*$PartRoute[$key]/min_of_one($total)) : 1;
      }
      else {
        $borderSize = 1;
      }
    }
    else {

      // (S+L)/(1+S-L):  $tmpBorderSize = "(" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . min_of_one($total) . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " - " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . min_of_one($total) . ")";
      // (S+2L)/(1+S-2L):
      //$tmpBorderSize = "(" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . min_of_one($total) . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " - 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . min_of_one($total) . ")";

      // (S+2L)/(1+S+2L)
      $tmpBorderSize = "(" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key])  . "/" . min_of_one($total) . ")/( 1 + " . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . $decorations[$key] . " + 2*" . ($PartRoute[$key] == "" ? 0 : $PartRoute[$key]) . "/" . min_of_one($total) . ")";


      eval("\$borderSize = 0 + $maxLine*$tmpBorderSize;");
      $borderSize = round($borderSize);
    }





    $borderConnection = ' borderconnection="' . $borderSize . '" ';
    $description = "";

    $connectioncolor = ' connectioncolor="';
    $amt = array_key_exists($key, $PartRoute) ? 
           round(25/$maxLine)*(round($maxLine*$PartRoute[$key]/min_of_one($total)) -2/3*$maxLine) : 
           round(25/$maxLine)*(round($maxLine*1/min_of_one($total)) - 2/3*$maxLine);
    $updown = $amt >= 0 ? "down" : "up";
    $amt = abs($amt);
    if (isset($_REQUEST['colorConnection']) && ($_REQUEST['colorConnection'] == 'colorConnection')) {
      $connectioncolor .= (substr($key, -1) == 1) ? hex_shift($rightColor, $updown , $amt) : hex_shift($wrongColor, $updown, $amt) ;
    }
    else {
      $connectioncolor .= hex_shift($baseColor, $updown, $amt);
    }
    $connectioncolor .= '" ';
  }

  $bgWeight = $key == "" ? 1 : round(strlen($key)/2) + 1;
  $bgWeight = $key == "" ? 1 : 3.5;
//print "key is $key and bgWeight is $bgWeight<BR>";
  $nodeColorInfo .= ' namebgcolor="';
  $nodeColorInfo .= hex_shift("#d9e3ed", $updown, min(85, $bgWeight*$amt));
  $nodeColorInfo .= '" namebgcolor2="';
  $nodeColorInfo .= hex_shift("#526e88", $updown, min(85, $bgWeight*$amt));
  $nodeColorInfo .= '" ';
  $nodeColorInfo .= ' bordercolor="';
  $nodeColorInfo .= hex_shift("#526e88", $updown, min(85, $bgWeight*$amt));
  $nodeColorInfo .= '" ';


  if (strlen($key) > $maxLength) { return $xml; }
 $xml .= '  <node name="' . $nodeName . '" ' . $connectionName . $connectioncolor . $nodeColorInfo . $borderConnection . '>' . $description;
  if ((isset($_REQUEST['prune_below']) && ($_REQUEST['prune_below'] == 'prune_below')) || (isset($_REQUEST['prune']) && ($_REQUEST['prune'] == 'prune'))) {
    if (isset($_REQUEST['prune']) && ($PartRoute[$key . "0"] == 0)) {}
    else if (isset($_REQUEST['prune_below']) && (isset($_REQUEST['prune_cutoff']) && ($PartRoute[$key . "0"] <= $_REQUEST['prune_cutoff']))) { }
    else { 
      $xml = xmlPreOrder($xml, $key . "0", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
    }
    if (isset($_REQUEST['prune']) && ($PartRoute[$key . "1"] == 0)) {}
    else if (isset($_REQUEST['prune_below']) && (isset($_REQUEST['prune_cutoff']) && ($PartRoute[$key . "1"] <= $_REQUEST['prune_cutoff']))) { }
    else {
      $xml = xmlPreOrder($xml, $key . "1", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
    }
  }
  else if (isset($_REQUEST['onlyRight']) && ($_REQUEST['onlyRight'] == 'onlyRight')) {
    if (strpos($key, "0") === false) {
      $xml = xmlPreOrder($xml, $key . "0", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
      $xml = xmlPreOrder($xml, $key . "1", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
    }
  }
  else {
    $xml = xmlPreOrder($xml, $key . "0", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
    $xml = xmlPreOrder($xml, $key . "1", $maxLength, $total, $shortConcepts, $concepts, $PartRoute, $wrongBranch, $rightBranch, $decorations);
  }
  $xml .= '  </node>';
  return $xml;
}


      /**
       * Color shift a hex value by a specific percentage factor
       *
       * @param string $supplied_hex Any valid hex value. Short forms e.g. #333 accepted.
       * @param string $shift_method How to shift the value e.g( +,up,lighter,>)
       * @param integer $percentage Percentage in range of [0-100] to shift provided hex value by
       * @return string shifted hex value
       * @version 1.0 2008-03-28
       */

      function hex_shift($supplied_hex,$shift_method,$percentage=50)  {
      $shifted_hex_value = null;
      $valid_shift_option = FALSE;
      $current_set = 1;
      $RGB_values = array();
      $valid_shift_up_args = array('up','+','lighter','>');
      $valid_shift_down_args = array('down','-','darker','<');
      $shift_method = strtolower(trim($shift_method));
       
      // Check Factor
      if(!is_numeric($percentage)||($percentage = (int) $percentage)<0||$percentage>100) {
        if ($percentage > 100) { 
          print "error: percentage $percentage > 100<BR>";
          $percentage = 100; 
        }
        if ($percentage < 0) {
          trigger_error( "Invalid factor", E_USER_ERROR );
        }
      }
       
      // Check shift method
      foreach(array($valid_shift_down_args,$valid_shift_up_args) as $options){
      foreach($options as $method) {
      if($method == $shift_method) {
      $valid_shift_option = !$valid_shift_option;
      $shift_method = ($current_set === 1) ? '+' : '-';
      break 2;
      }
      }
      ++$current_set;
      }
       
      if(!$valid_shift_option) {
      trigger_error( "Invalid shift method", E_USER_ERROR );
      }
       
      // Check Hex string
      switch(strlen($supplied_hex=(str_replace('#','',trim($supplied_hex))))) {
      case 3:
      if(preg_match('/^([0-9a-f])([0-9a-f])([0-9a-f])/i',$supplied_hex)) {
      $supplied_hex = preg_replace('/^([0-9a-f])([0-9a-f])([0-9a-f])/i',
      '\\1\\1\\2\\2\\3\\3',$supplied_hex);
      } else {
      trigger_error( "Invalid hex value", E_USER_ERROR );
      }
      break;
      case 6:
      if(!preg_match('/^[0-9a-f]{2}[0-9a-f]{2}[0-9a-f]{2}$/i',$supplied_hex)) {
      trigger_error( "Invalid hex value", E_USER_ERROR );
      }
      break;
      default:
      trigger_error( "Invalid hex length", E_USER_ERROR );
      }
       
      // Start shifting
      $RGB_values['R'] = hexdec($supplied_hex{0}.$supplied_hex{1});
      $RGB_values['G'] = hexdec($supplied_hex{2}.$supplied_hex{3});
      $RGB_values['B'] = hexdec($supplied_hex{4}.$supplied_hex{5});
       
      foreach($RGB_values as $c => $v) {
      switch($shift_method) {
      case '-':
      $amount = round(((255-$v)/100)*$percentage)+$v;
      break;
      case '+':
      $amount = $v-round(($v/100)*$percentage);
      break;
      default:
      trigger_error( "Oops. Unexpected shift method", E_USER_ERROR );
      }
       
      $shifted_hex_value .= $current_value = (
      strlen($decimal_to_hex = dechex($amount)) < 2
      ) ? '0'.$decimal_to_hex : $decimal_to_hex;
      }
       
      return '#'.$shifted_hex_value;
      }
//echo hex_shift('#000','up',$i),"\n";


function echo_map(&$node, $selected, $qs, $yOffset, $xOffset, $problem_info = array(), $my_path = "") {
  $left = $node['x'] + $xOffset;
  $top = $node['y'] + $yOffset;

  $right = $node['x'] + $xOffset + $node['w'];
  $bottom = $node['y'] + $yOffset + $node['h'];

  $height = $node['h'];

  //taking care of this node down below.
  //print "<a href=\"?$qs&name={$node['name']}\"><div style=\"position:absolute;left:{$left};top:{$top};width:{$node['w']};height:{$node['h']};" . ($selected == $node['name'] ? "background-color:blue;filter:alpha(opacity=40);-moz-opacity:0.4;" : "") . "\"></div></a>\n";

  if (array_key_exists('childs', $node) && array_key_exists(1, $node['childs'])) {
    $rnode = $node['childs'][1];
    $rnode_right = $rnode['x'] + $xOffset + $rnode['w'];
    $rnode_left = $rnode['x'] + $xOffset + $rnode['w']/2;
    $rnode_top = $rnode['y'] + $yOffset;
    $rnode_start_left = $right - $node['w']/2;
  }
  else {
    $rnode_right = 0;
    $rnode_left = 0;
    $rnode_top = 0;
    $rnode_start_left = 0;
  }
  $rnode_there_to_here = $rnode_left - $rnode_start_left;
  $rnode_there_to_here_h = $rnode_top - $bottom;

  if (array_key_exists('childs', $node) && array_key_exists(0, $node['childs'])) {
    $lnode = $node['childs'][0];
    $lnode_left = $lnode['x'] + $xOffset;
    $lnode_right = $lnode['x'] + $xOffset + $lnode['w']/2;
    $lnode_top = $lnode['y'] + $yOffset;
  }
  else {
    $lnode_left = 0;
    $lnode_right = 0;
    $lnode_top = 0;
  }
  $lnode_there_to_here = $left - $lnode_right + $node['w']/2;
  $lnode_there_to_here_h = $lnode_top - $bottom;

  if (0) {
  print "
    <BR>
    path is $my_path;
    " . $node['name'] . " l,t,r,b, h: $left, $top, $right, $bottom, $height; 
    " . $lnode['name'] . " lr, lt, lw, lh: $lnode_right, $lnode_top, $lnode_there_to_here, $lnode_there_to_here_h;
    " . $rnode['name'] . " rl, rt, rw, rh: $rnode_left; $rnode_top: $rnode_there_to_here; $rnode_there_to_here_h; 
    <BR>";
  }


  $all_course_student_problem_string = "print_user_name=users&map_width=" . $_REQUEST['data_map_width'] . "&pixels_per_second=" . $_REQUEST['pps'];

  if (($rnode_there_to_here > 0) && ($lnode_there_to_here > 0)) {

    //we have both a left and a right tree.
    //down to right:
    $to_this_path = "$my_path" . "1";
    $course_student_problem_string = "print_user_name=users&map_width=" . $_REQUEST['data_map_width'] . "&pixels_per_second=" . $_REQUEST['pps'];
    $i = 0;
    if (isset($to_this_path) &&
        array_key_exists("$to_this_path", $problem_info) &&
        array_key_exists('student_ids', $problem_info["$to_this_path"]) &&
        array_key_exists('source_file', $problem_info["$to_this_path"])) {
      foreach ($problem_info["$to_this_path"]['student_ids'] as $u_id => $u_c) {
        $course_student_problem_string .= "&do_these_students[$i]=";
        $course_student_problem_string .="$u_c" . "___" . $u_id . "___";
        $all_course_student_problem_string .= "&do_these_students[$i]=";
        $all_course_student_problem_string .= "$u_c" . "___" . $u_id . "";

        if (array_key_exists($u_id, $problem_info["$to_this_path"]['source_file'])) {
          foreach ($problem_info["$to_this_path"]['source_file'][$u_id] as $s_f => $this_q) {
            $course_student_problem_string .= "$s_f" . "___" . $this_q;
            $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
          }
          $future_path = "";
          while((array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) && 
                 array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) ||
                 (array_key_exists("$to_this_path" . "$future_path" . "1", $problem_info) &&
                 array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "1"]['source_file']))) {
            if (array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
                 array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) {
               $future_path .= "0";
            }
            else {
               $future_path .= "1";
            }

            foreach ($problem_info["$to_this_path" . "$future_path"]['source_file'][$u_id] as $s_f => $this_q) {
              $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
            }
          }
        }
        $i++;
      }
    }

    //path down to right:
    echo "<a href=\"student_quiz_work.php?path=$to_this_path" . "&" . $course_student_problem_string . "\" target='student_work'><div style=\"position:absolute;left:{$rnode_start_left}px;top:{$bottom}px;width:{$rnode_there_to_here}px;height:{$rnode_there_to_here_h}px;\"></div></a>\n";

    $to_this_path = "$my_path" . "0";
    $course_student_problem_string = "print_user_name=users&map_width=" . $_REQUEST['data_map_width'] . "&pixels_per_second=" . $_REQUEST['pps'];
    $ii = $i;
    $i = 0;
    if (isset($to_this_path) &&
        array_key_exists("$to_this_path", $problem_info) &&
        array_key_exists("student_ids", $problem_info["$to_this_path"]) &&
        array_key_exists('source_file', $problem_info["$to_this_path"])) {
      foreach ($problem_info["$to_this_path"]['student_ids'] as $u_id => $u_c) {
        $course_student_problem_string .= "&do_these_students[$i]=";
        $course_student_problem_string .="$u_c" . "___" . $u_id . "___";
        $all_course_student_problem_string .= "&do_these_students[" . $ii . "]=";
        $all_course_student_problem_string .= "$u_c" . "___" . $u_id . "";
          if (($u_id != "") && 
              array_key_exists($u_id, $problem_info["$to_this_path"]['source_file']) &&
              is_array($problem_info["$to_this_path"]['source_file']["$u_id"])) {
            foreach ($problem_info["$to_this_path"]['source_file']["$u_id"] as $s_f => $this_q) {
              $course_student_problem_string .= "$s_f" . "___" . $this_q;
              $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
            }
            $future_path = "";
            while((array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
                   array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) ||
                   (array_key_exists("$to_this_path" . "$future_path" . "1", $problem_info) &&
                   array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "1"]['source_file']))) {
              if (array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
                   array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) {
                $future_path .= "0";
              }
              else {
                $future_path .= "1";
              }

              foreach ($problem_info["$to_this_path" . "$future_path"]['source_file'][$u_id] as $s_f => $this_q) {
                $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
              }
            }
          //}
        }
        $i++;
        $ii++;
      }
    }
    //this node:
    echo "<a href=\"student_quiz_work.php?path=$to_this_path" . "&" . $all_course_student_problem_string . "\" target='student_work'><div style=\"position:absolute;left:{$left}px;top:{$top}px;width:{$node['w']}px;height:{$node['h']}px;\"></div></a>\n";

    //down to left:
    echo "<a href=\"student_quiz_work.php?path=$to_this_path" . "&" . $course_student_problem_string . "\" target='student_work'><div style=\"position:absolute;left:{$lnode_right}px;top:{$bottom}px;width:{$lnode_there_to_here}px;height:{$lnode_there_to_here_h}px;\"></div></a>\n";
  }
  else {
    if ($lnode_there_to_here == 0) {
      //use the left-node data and go straight down.
      $to_this_path = "$my_path" . "0";
      $course_student_problem_string = "print_user_name=users&map_width=" . $_REQUEST['data_map_width'] . "&pixels_per_second=" . $_REQUEST['pps'];
      $i = 0;
      foreach ($problem_info["$to_this_path"]['student_ids'] as $u_id => $u_c) {
        $follow_next_path = "0";
        $course_student_problem_string .= "&do_these_students[$i]=";
        $course_student_problem_string .="$u_c" . "___" . $u_id . "___";
        $all_course_student_problem_string .= "&do_these_students[$i]=";
        $all_course_student_problem_string .= "$u_c" . "___" . $u_id . "";
        foreach ($problem_info["$to_this_path"]['source_file'][$u_id] as $s_f => $this_q) {
          $course_student_problem_string .= "$s_f" . "___" . $this_q;
          $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
        }
        $future_path = "";
        while((array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) ||
               (array_key_exists("$to_this_path" . "$future_path" . "1", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "1"]['source_file']))) {
          if (array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) {
             $future_path .= "0";
          }
          else {
             $future_path .= "1";
          }
  
          foreach ($problem_info["$to_this_path" . "$future_path"]['source_file'][$u_id] as $s_f => $this_q) {
            $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
          }
         }

        $i++;
      }
 
      $to_this_path = "$my_path" . "1";
      foreach ($problem_info["$to_this_path"]['student_ids'] as $u_id => $u_c) {
        $follow_next_path = "1";
        $course_student_problem_string .= "&do_these_students[$i]=";
        $course_student_problem_string .="$u_c" . "___" . $u_id . "___";
        $all_course_student_problem_string .= "&do_these_students[$i]=";
        $all_course_student_problem_string .= "$u_c" . "___" . $u_id . "";
        foreach ($problem_info["$to_this_path"]['source_file'][$u_id] as $s_f => $this_q) {
          $course_student_problem_string .= "$s_f" . "___" . $this_q;
          $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
        }
        $future_path = "";
        while((array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) ||
               (array_key_exists("$to_this_path" . "$future_path" . "1", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "1"]['source_file']))) {
          if (array_key_exists("$to_this_path" . "$future_path" . "0", $problem_info) &&
               array_key_exists($u_id, $problem_info["$to_this_path" . "$future_path" . "0"]['source_file'])) {
             $future_path .= "0";
          }
          else {
             $future_path .= "1";
          }

          foreach ($problem_info["$to_this_path" . "$future_path"]['source_file'][$u_id] as $s_f => $this_q) {
            $all_course_student_problem_string .= "___" . "$s_f" . "___" . $this_q;
          }
        }

        $i++;
      }
      //this node:
      echo "<a href=\"student_quiz_work.php?path=$to_this_path" . "&" . $all_course_student_problem_string . "\" target='student_work'><div style=\"position:absolute;left:{$left}px;top:{$top}px;width:{$node['w']}px;height:{$node['h']}px;\"></div></a>\n";

      echo "<a href=\"student_quiz_work.php?path=$to_this_path" . "&" . $course_student_problem_string . "\" target='student_work'><div style=\"position:absolute;left:{$left}px;top:{$bottom}px;width:{$node['w']}px;height:{$lnode_there_to_here_h}px;\"></div></a>\n";
    }
    else {
    }
  }
  if (count($node['childs']) == 1) {
    echo_map($node['childs'][0], $selected, $qs, $yOffset, $xOffset, $problem_info, "$my_path" . "$follow_next_path");
  }
  else {
    for ($i = 0; $i < count($node['childs']); $i++) {
      echo_map($node['childs'][$i], $selected, $qs, $yOffset, $xOffset, $problem_info, "$my_path" . "$i");
    }
  }
}



function getPreScoreForUserConceptTest($course, $user, $conceptTest, $quiz, $ww_db_host, $ww_db_user, $ww_db_pass) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "webwork";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  
  //get the problem_id from awangberg_problem as determined by $user and "group:$conceptTest

  $problem_id = -1;

  $query = 'SELECT problem_id FROM `' . $course . '_problem` WHERE set_id="' . $quiz . '" AND source_file = "' . $conceptTest . '"';

  $result = mysql_query($query, $con);

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problem_id = $row['problem_id'];
  }

  if ($problem_id >= 0) {
  

    $query = 'SELECT status, attempted FROM `' . $course . '_problem_user` WHERE set_id="' . $quiz . ',v1" AND problem_id=' . $problem_id . ' AND user_id = "' . $user . '"';

    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $status = $row['status'];
      $attempted = $row['attempted'];
    }
  }

  mysql_close($con);

  if ($problem_id == -1) {
    return "Not taken";
  }

  if ($attempted == 1) {
    if ($status == 1) {
      return $status;
    }
    return "0 ($status)";
  }
  return "";
}

function getPostScoreForUserConceptTest($course, $user, $conceptTest, $quiz, $ww_db_host, $ww_db_user, $ww_db_pass) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "webwork";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  // get the problem_id from awangberg_problem as determined by $user, "group:$conceptTest", and finalQuiz_$user_$quiz
  $query = 'SELECT problem_id FROM `' . $course . '_problem` WHERE set_id="finalQuiz_' . $user . '_' . $quiz . '" AND source_file="' . $conceptTest . '"';
  $result = mysql_query($query, $con);

  $problem_id = -1;

  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $problem_id = $row['problem_id'];
  }

  if ($problem_id >= 0) {

    $query = 'SELECT status, attempted FROM `' . $course . '_problem_user` WHERE set_id="finalQuiz_' . $user . '_' . $quiz . ',v1" AND problem_id=' . $problem_id;
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $status = $row['status'];
      $attempted = $row['attempted'];
    }
  }

  mysql_close($con);

  if ($problem_id == -1) {
    return "Not Taken";
  }

  if ($attempted == 1) {
    if ($status == 1) {
      return $status;
    }
    return "0 ($status)";
  }
  return "";

}

function getPracticeAttemptsForUserConceptTest($user, $conceptTest, $quiz, $db_host, $db_user, $db_pass) {
  $con = mysql_connect($db_host, $db_user, $db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "wwSession";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $conceptTest = preg_replace("/group\:/", "", $conceptTest);

  $ww_problem_set_number = array();
  $pg_sourcefile = array();

  //get the pg_sourcefile from the usersConceptBanks associated with this conceptTest:
  $query = 'SELECT pg_sourcefile, webwork_problem_set_number FROM `usersConceptBanks` WHERE concept_bank="' . $conceptTest . '" AND user_name="' . $user . '"';
  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $ww_problem_set_number[] = $row['webwork_problem_set_number'];
    $pg_sourcefile[] = $row['pg_sourcefile'];
  }


  //get the number of incorrect attempts per problem from pgProblemIncorrectAttempts:
  $incorrectAttempts_sourceFile = array();

  for ($i = 0; $i < count($pg_sourcefile); $i++) {
    $incorrectAttempts_sourceFile[$i] = 0;
    $query = 'SELECT num_of_incorrect_attempts FROM `pgProblemIncorrectAttempts` WHERE user_name="' . $user . '" AND pg_sourcefile="' . $pg_sourcefile[$i] . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $incorrectAttempts_sourceFile[$i] = $row['num_of_incorrect_attempts'];
    }
  }

  $timeFor_sourceFile = array();
  $wasSuccessful_sourceFile = array();

  //get the amount of time per problem from attempts:
  for ($i = 0; $i < count($pg_sourcefile); $i++) {
    $timeForThisProblem = 0;
    $was_successful = -1;
    $query = 'SELECT submitted_date, attempted_date, was_successful FROM `attempts` WHERE user_name="' . $user . '" AND concept_bank = "' . $conceptTest . '" AND pg_sourcefile="' . $pg_sourcefile[$i] . '"';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $initial_time = $row['attempted_date'];
      $final_time = $row['submitted_date'];
      if ($row['was_successful'] == 1) { $was_successful = 1; }
      if ($final_time == "0000-00-00 00:00:00") {
	//do nothing - no final time...
      }
      else {
        $accumulated_time = date(strtotime($final_time)) - date(strtotime($initial_time));
	$timeForThisProblem = $timeForThisProblem + $accumulated_time;
      }
    }    
    $timeFor_sourceFile[$i] = $timeForThisProblem;
    $wasSuccessful_sourceFile[$i] = $was_successful;
  }
  mysql_close($con);


  $str = "";
  for ($i = 0; $i < count($pg_sourcefile); $i++) {
    $str .= "<TR>";
    $str .= "<TD>" . $pg_sourcefile[$i] . "</TD>";
    $str .= "<TD>" . $incorrectAttempts_sourceFile[$i] . "</TD>";
    $str .= "<TD>" . $timeFor_sourceFile[$i] . "</TD>";
    $str .= "<TD>" . $wasSuccessful_sourceFile[$i] . "</TD>";
    $str .= "</TR>";
  }

  return "<TABLE BORDER=1>" . $str . "</TABLE>";
  return "practiceAttempts for $user on $conceptTest";
}

function getPracticeTimeForUserConceptTest($user, $conceptTest) {

  return "practiceTime for $user on $conceptTest";
}


function getReTestScoreForUserConceptTest($course, $user, $conceptTest, $quiz, $ww_db_host, $ww_db_user, $ww_db_pass) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if(!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = 'webwork';

  //select the database '$db'
  $result = mysql_select_db("$db", $con);


  $conceptTest = preg_replace("/group\:/", "", $conceptTest);

  //get all of the filenames associated with this conceptTest:
  $query = 'SELECT source_file FROM `' . $course . '_problem` WHERE set_id="' . $conceptTest . '"';
  $conceptBankSourceFiles = array();

  $result = mysql_query($query, $con);
  while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $conceptBankSourceFiles[] = $row['source_file'];
  }

  $str = "";

  //for each sourcefile, see if it was in the sourcefile of the finalQuiz_$user_$quiz,v1
  for ($i=0; $i < count($conceptBankSourceFiles); $i++) {
    $query = 'SELECT status, attempted FROM `' . $course . '_problem_user` WHERE set_id="finalQuiz_' . $user . '_' . $quiz . ',v1" AND source_file="' . $conceptBankSourceFiles[$i] . '"';

    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $attempt = $row['attempted'];
      $status = $row['status'];

      if ($attempt == 1) {
        if ($status ==1) {
          $str .= "$status;";
        }
        else {
          $str .= "0 ($status); ";
        }
      }
      else {
        $str .= "Not Attempted;";
      }
    }

  }
  mysql_close($con);
  return $str;
}

if (!isset($_REQUEST['pps'])) { $_REQUEST['pps'] = 1; }
if (!isset($_REQUEST['data_map_width'])) { $_REQUEST['data_map_width'] = 400; }


$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

$tmp_quizName = get_quizNames();
foreach ($tmp_quizName as $i => $quiz) {
  $quizName["Math160_F2009_awangberg"][] = $quiz;
  $quizName["Math160_S2010_awangberg_05"][] = $quiz;
  $quizName["Math160_S2010_eerrthum"][] = $quiz;
  $quizName["Math160_F2010_awangberg"][] = $quiz;
  $quizName["Math160_F2011_awangberg"][] = $quiz;
}
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_1";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_3";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_5";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_7";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_10";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_11";
$quizName["Math160_F2010_awangberg"][] = "quiz_wk_13";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_2";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_3";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_4";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_5";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_6";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_7";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_9";
$quizName["Math160_F2011_awangberg"][] = "quiz_wk_10";

if (isset($_REQUEST['courses']) && isset($_REQUEST['concepta1'])) {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }


  //get the description of the concept banks:
  $db = "wwSession";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $conceptBank_description = array();
  $query = 'SELECT concept_bank, description FROM `conceptBankDescription`';
  $result = mysql_query($query, $con);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_c = $row['concept_bank'];
    $this_d = $row['description'];
    $conceptBank_description[$this_c] = $this_d;
  }


  $db = "webwork";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);



  $conceptBank = array();
  $abbrConceptBank = array();

  $do_these_courses = $_REQUEST['courses'];
foreach ($quizName as $course => $quiz_list) {
    foreach($quiz_list as $tmp_i => $quiz) {
      $query = 'SELECT source_file FROM `' . $course . '_problem` WHERE set_id = "' . $quiz . '"';
      $result = mysql_query($query, $con);
      $count = 1;
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $concept =$row['source_file'];
        $abbrConceptBank[$concept] = substr($quiz, 0, 1) . $count;
        $count = $count + 1;
      }
    }
  }


  $preConceptsByStudentID = array();
  $postConceptsByStudentID = array();

  $maxLength = 0;

  $concepts = array();

  $conditions = array();
  $evalToTrue = array();

  print "<HTML>";
  print "<HEAD>";
  print javascript();
  print "</HEAD>";

  print "<H2>Concept Banks</H2><BR>";
  
  print "<TABLE><TR><TD>ID</TD><TD>Concept</TD><TD>Description</TD></TR>\n";
  //get the results for each concept by student
  for ($p = 1; $p <= 9; $p++) {
    for ($q = 1; $q <= 3; $q++) {
      if ($q == 1) { $qa = "a"; }
      if ($q == 2) { $qa = "b"; }
      if ($q == 3) { $qa = "c"; }

      $conceptRequest = "concept" . $qa . $p . "";
      $concept = $_REQUEST[$conceptRequest];

      if ($q == 1) {
        $conditionPart = "";
        $conditions[$p] = "";
      }
      else {
        $conditionRequest = "condition" . $qa . $p . "";
        $conditionPart = $_REQUEST[$conditionRequest];
      }

      if ($concept == "") {

      }
      else {

        if ($q == 2) { 
          $conditions[$p] .= $conditionPart == "&&" ? " and " : " or ";

          $validThirdCondition = $_REQUEST["conditionc" . $p . ""];
          $conditions[$p] .= ($validThirdCondition == "" ? "" : "(") . $abbrConceptBank[$concept];
          $evalToTrue[$p] .= " " . $conditionPart . " ( " . $concept;
        }
        else if ($q == 3) {
          $conditions[$p] .= $conditionPart == "&&" ? " and " : " or ";
          $conditions[$p] .= $abbrConceptBank[$concept] . ")";
          $evalToTrue[$p] .= " " . $conditionPart . " " . $concept . " )";
        }
        else {
          $conditions[$p] = $abbrConceptBank[$concept];
          $evalToTrue[$p] = $concept;
        }

        $concepts[$p] = "$concept";
        $this_temp_c = preg_replace("/group\:/", "", $concept);
        print "<TR><TD><B>$abbrConceptBank[$concept]: " . 
               substr($abbrConceptBank[$concept], 6, strlen($abbrConceptBank[$concept])) . 
               "</TD><TD>$concept</B><TD> ";
        print array_key_exists($this_temp_c, $conceptBank_description) ? $conceptBank_description[$this_temp_c] : "";
        print "</TD></TR>";

        foreach ($do_these_courses as $tmp_k => $course) {

          //get the valid students:
          $valid_users = valid_users($con, $course, 'validForStatistics="1"');

          $db = 'webwork';
          //select the database '$db'
          mysql_select_db("$db", $con);
          

          //find out if this was really in a quiz using the quiz Module.
          //if it was, then we have a pre-test and a post-test.
          //if it wasn't, then the student just did it once.  Include their performance in both pre-test and post-test.
          $query = 'SELECT *, ' . $course . '_problem_user.source_file as use_this_sourcefile FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON '
                 . 'CONCAT(' . $course . '_problem.set_id,",v1") = ' . $course . '_problem_user.set_id '
                 . 'AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id '
                 . 'WHERE ' . $course . '_problem.source_file = "' . $concept . '"';
//print $query . "<BR>";

          $result = mysql_query($query, $con);
          $not_a_pre_post_quiz = 0;

          if (mysql_num_rows($result) <= 1) {
          //There was just one result, so this wasn't done using the gateway quiz system.
          //get the results from homework.
          //we rename the first source_file since this query returns multiple source_files, but we need the first one here.
          $query = 'SELECT *, ' . $course . '_problem.source_file as use_this_sourcefile FROM `' . $course . '_problem` LEFT JOIN `' . $course . '_problem_user` ON '
                 . $course . '_problem.set_id = ' . $course . '_problem_user.set_id '
                 . 'AND ' . $course . '_problem.problem_id = ' . $course . '_problem_user.problem_id '
                 . 'WHERE ' . $course . '_problem.source_file = "' . $concept . '"';
          $result = mysql_query($query, $con);
            $not_a_pre_post_quiz = 1;
          }

//print "<P>" . $query . "</P>";

          while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $status = $row['status'];
            $set_id = $row['set_id'];
            $user_id = $row['user_id'];
            $attempted = $row['attempted'];
            $source_file = $row['use_this_sourcefile'];

	    if ($valid_users[$user_id]) {
              if (!(array_key_exists($user_id, $preConceptsByStudentID))) { $preConceptsByStudentID[$user_id][$concept] = ""; }
              if (!(array_key_exists($user_id, $postConceptsByStudentID))) { $postConceptsByStudentID[$user_id][$concept] = ""; }
              if (!(array_key_exists('level', $postConceptsByStudentID[$user_id])) || !(array_key_exists($p, $postConceptsByStudentID[$user_id]['level']))) { $postConceptsByStudentID[$user_id]['level']["$p"] = array(); }
              $preConceptsByStudentID[$user_id]['course'] = $course;
              $postConceptsByStudentID[$user_id]['course'] = $course;
              if ($not_a_pre_post_quiz == 1) {
                if ($attempted == "0") {
                  $preConceptsByStudentID[$user_id][$concept] = ".";
                  $postConceptsByStudentID[$user_id][$concept] = ".";
                  $preConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                }
                else if ($status == "1") {
                  $preConceptsByStudentID[$user_id][$concept] = "True";
                  $postConceptsByStudentID[$user_id][$concept] = "True";
                  $preConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                }
                else {
                  $preConceptsByStudentID[$user_id][$concept] = "False";
                  $postConceptsByStudentID[$user_id][$concept] = "False";
                  $preConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                }
              }
              else {
                if (($set_id == "Basics,v1") || ($set_id == "Graphs,v1") || ($set_id == "LinearRational,v1") || ($set_id == "ExpLog,v1") || ($set_id == "Trig,v1") || ($set_id == "pca,v1") || ($set_id == "pcb,v1")) {
                  if ($attempted == "0") { 
                    $preConceptsByStudentID[$user_id][$concept] = "."; }
                  else if ($status == "1") {  
                    $preConceptsByStudentID[$user_id][$concept] = "True";
                    $preConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                    $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";

                    //record their pre-score as their post-score if the quizzes are set up that way.
                    //The student takes the full pca and pcb quizzes both pre-test and post-test.
                    if (!(($set_id == "pca,v1") || ($set_id == "pcb,v1"))) { 
                      $postConceptsByStudentID[$user_id][$concept] = "True";
                      $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                   }
                  }
                  else {  
                    $preConceptsByStudentID[$user_id][$concept] = "False";
                    $preConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                 }
                }
                else {
                  if ($attempted == "0") {
                    $postConceptsByStudentID[$user_id][$concept] = ".";
                    $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  }
                  else if ($status == "1") {
                    $postConceptsByStudentID[$user_id][$concept] = "True";
                    $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  }
                  else {
                    $postConceptsByStudentID[$user_id][$concept] = "False";
                    $postConceptsByStudentID[$user_id]['level']["$p"]["$source_file"] = "$set_id";
                  }
                }
              }
            }
          }
        } //end of the $course loop
      }
    }
  }
  print "</TABLE>";

  //create the pre, post, and change-tree:

  $pretree = array();
  $posttree = array();
  $changetree = array();

  $maxLength = 0;
  for ($p = 1; $p <= 9; $p++) {
    $conditionParts = array_key_exists($p, $evalToTrue) ? explode(" ", $evalToTrue[$p]) : explode(" ", '     ');
    if (($conditionParts[0] == "") && ($conditionParts[3] == "") && ($conditionParts[5] == "")) {

    }
    else {
      $maxLength++;
      //go through each studentID and see if they meet the condition:
      foreach($preConceptsByStudentID as $user_id => $performance) {


        $boola = (array_key_exists(0, $conditionParts) && array_key_exists($conditionParts[0], $performance)) ?
                 $performance[$conditionParts[0]] :
                 "";
        $boolb = (array_key_exists(3, $conditionParts) && array_key_exists($conditionParts[3], $performance)) ?
                 $performance[$conditionParts[3]] :
                 "";
        $boolc = (array_key_exists(5, $conditionParts) && array_key_exists($conditionParts[5], $performance)) ?
                 $performance[$conditionParts[5]] :
                 "";
        $storeValue;
        if ((array_key_exists(4, $conditionParts) && ($conditionParts[4] == "||")) ||
            (array_key_exists(4, $conditionParts) && ($conditionParts[4] == "&&"))) {
          if (($performance[$conditionParts[0]] == "") || 
              ($performance[$conditionParts[0]] == ".") ||
              ($performance[$conditionParts[3]] == "") ||
              ($performance[$conditionParts[3]] == ".") ||
              ($performance[$conditionParts[5]] == "") ||
              ($performance[$conditionParts[5]] == ".")) {
              if (($conditionParts[4] == "||") && ($conditionParts[1] == "||")) {
                 $truthString = "(" .
                       ((($boola == "") || ($boola == ".")) ? 'False' : $boola) .
                       " || ( False || False)) || ( False || ( " .
                       ((($boolb == "") || ($boolb == ".")) ? 'False' : $boolb) . 
                       " || False)) || ( False || ( False || " .
                       ((($boolc == "") || ($boolc == ".")) ? 'False' : $boolc) .
                       "))";
                eval("\$storeValue = " . attach_end($truthString));
              }
              else if (($conditionParts[4] == "||") && ($conditionParts[1] == "&&")) {
                if (($boola == "") || ($boola == ".")) {
                  $storeValue = "x";
                }
                else {
                  $truthString = "( $boola && (" .
                        ((($boolb == "") || ($boolb == ".")) ? 'False' : $boolb) . 
                        " || False )) || ( $boola && ( False || " .
                        ((($boolc == "") || ($boolc == ".")) ? 'False' : $boolc) .
                        " ))";
                  eval("\$storeValue = " . attach_end($truthString));
                }
              }
              else if (($conditionParts[4] == "&&") && ($conditionParts[1] == "||")) {
                if (($boolb == "") || ($boolb == ".") || ($boolc == "") || ($boolc == ".")) {
                  $storeValue = "x";
                }
                else {
                  $truthString = "(" .
                        ((($boola == "") || ($boola == ".")) ? 'False' : $boola) . 
                        " || ($boolb && $boolc))";
                  eval("\$storeValue = " . attach_end($truthString));
                }
              }
              else {
                $storeValue = "x";
              }
          }
          else {
            $truthString = "$boola" . $conditionParts[1] . "($boolb" . $conditionParts[4] . "$boolc)";
            eval("\$storeValue = " . attach_end($truthString));
          }
        }
        else if ((array_key_exists(1, $conditionParts) && ($conditionParts[1] == "||")) ||
                 (array_key_exists(1, $conditionParts) && ($conditionParts[1] == "&&"))) {
          if ((array_key_exists($conditionParts[0], $performance) && 
              (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == "."))) ||
              (array_key_exists($conditionParts[3], $performance) &&
              (($performance[$conditionParts[3]] == "") || ($performance[$conditionParts[3]] == ".")))) {

              if ($conditionParts[1] == "||") {
                $truthString = "(" . 
                       ((($boola == "") || ($boola == ".")) ? 'False' : $boola) .
                       " || False) || (False || " . 
                       ((($boolb == "") || ($boolb == ".")) ? 'False' : $boolb) . ")";
                eval("\$storeValue = " . attach_end($truthString));
              }
              else {
                $storeValue = "x";
              }
          }
          else {
            //$truthString = "$boola" . $conditionParts[1] . "$boolb";
            //$performance only cantains data in either the 0 or the 3 slot,
            // since the two quiz questions are coming from different courses.
            //In this case, evaluate boola and boolb as true or false depending on the conditionParts[1]
            //since the student wasn't allowed to do that problem.
            if ($conditionParts[1] == "||") {
              $truthString = ((($boola == "") || ($boola == ".")) ? "False" : $boola) .
                             $conditionParts[1] . 
                             ((($boolb == "") || ($boolb == ".")) ? "False" : $boolb);
            }
            else {
              //conditionParts[1] == "&&";
              $truthString = ((($boola == "") || ($boola == ".")) ? True : $boola) .
                             $conditionParts[1] . 
                             ((($boolb == "") || ($boolb == ".")) ? True : $boolb);
            }
            eval("\$storeValue = " . attach_end($truthString));
          } 
        }
        else {
          if ((array_key_exists(0, $conditionParts) && array_key_exists($conditionParts[0], $performance)) &&
              (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == "."))) {
            $storeValue = "x";
          }
          else {
//            $storeValue = ($boola) ? 1 : 0;
            $truthString = "$boola";
            eval("\$storeValue = " . attach_end($truthString));
          }
        }
        if (!array_key_exists($user_id, $pretree)) { $pretree[$user_id] = ""; }
        while(strlen($pretree[$user_id]) < $p - 1) {  $pretree[$user_id] .= "x"; }
//print "pretree[$user_id] was " . $pretree[$user_id] . " and is now:";
        $pretree[$user_id] .= $storeValue === True ? "1" : ($storeValue === False ? "0" : "");
//print "$pretree[$user_id]<P>";
//        print "<BR>id is $user_id ---> $str<P> =====> $storeValue";
      }
      foreach ($pretree as &$string) {
        if (strlen($string) < $p) { $string .= "x"; }
      }
    }    
  }

  //Go through and create the routes for the posttree:
  $maxLength = 0;
  $students_on_post_route = array();
  $students_on_pre_route = array();
  for ($p = 1; $p <= 9; $p++) {
    $conditionParts = array_key_exists($p, $evalToTrue) ? explode(" ", $evalToTrue[$p]) : explode(" ", '     ');
    if (($conditionParts[0] == "") && ($conditionParts[3] == "") && ($conditionParts[5] == "")) {

    }
    else {
      $maxLength++;
      //go through each studentID and see if they meet the condition:
      foreach($postConceptsByStudentID as $user_id => $performance) {
        $boola = (array_key_exists(0, $conditionParts) && 
                  array_key_exists($conditionParts[0], $performance) &&
                  ($performance[$conditionParts[0]] == "True")) ? True : False;
        $boolb = (array_key_exists(3, $conditionParts) &&
                  array_key_exists($conditionParts[3], $performance) &&
                  ($performance[$conditionParts[3]] == "True")) ? True : False;
        $boolc = (array_key_exists(5, $conditionParts) &&
                  array_key_exists($conditionParts[5], $performance) &&
                  ($performance[$conditionParts[5]] == "True")) ? True : False;

        $storeValue;
        if ((array_key_exists(4, $conditionParts) && ($conditionParts[4] == "||")) || 
            (array_key_exists(4, $conditionParts) && ($conditionParts[4] == "&&"))) {
          if (($performance[$conditionParts[0]] == "") ||
              ($performance[$conditionParts[0]] == ".") ||
              ($performance[$conditionParts[3]] == "") ||
              ($performance[$conditionParts[3]] == ".") ||
              ($performance[$conditionParts[5]] == "") ||
              ($performance[$conditionParts[5]] == ".")) {
            $storeValue = "x";
          }
          else if (($conditionParts[1] == "&&") && ($conditionParts[4] == "&&")) {
            $storeValue = ($boola && ($boolb && $boolc)) ? 1 : 0;
          }
          else if (($conditionParts[1] == "&&") && ($conditionParts[4] == "||")) {
            $storeValue = ($boola && ($boolb || $boolc)) ? 1 : 0;
          }
          else if (($conditionParts[1] == "||") && ($conditionParts[4] == "&&")) {
            $storeValue = ($boola || ($boolb && $boolc)) ? 1 : 0;
          }
          else if (($conditionParts[1] == "||") && ($conditionParts[4] == "||")) {
            $storeValue = ($boola || ($boolb || $boolc)) ? 1 : 0;
          }
          else {
          }
        }
        else if ((array_key_exists(1, $conditionParts) && ($conditionParts[1] == "||")) ||
                 (array_key_exists(1, $conditionParts) && ($conditionParts[1] == "&&"))) {
          if ((array_key_exists($conditionParts[0], $performance) && 
               (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == "."))) ||
              (array_key_exists($conditionParts[3], $performance) &&
               (($performance[$conditionParts[3]] == "") || ($performance[$conditionParts[3]] == ".")))) {
             $storeValue = "x";
          }
          else {
            if ($conditionParts[1] == "||") {
              $truthString = ($boola == "" ? "False" : $boola) .
                              $conditionParts[1] .
                             ($boolb == "" ? "False" : $boolb);
            }
            else {
              //$conditionParts[1] == "&&";
              $truthString = ($boola == "" ? "True" : $boola) .
                              $conditionParts[1] .
                             ($boolb == "" ? "True" : $boolb);
            }
            eval("\$storeValue = " . attach_end($truthString));
          }
        }
        else {
          if ((array_key_exists(0, $conditionParts) && array_key_exists($conditionParts[0], $performance)) &&
              (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == "."))) {
            $storeValue = "x";
          }
          else {
            $storeValue = ($boola) ? 1 : 0;
          }
        }
        if (!array_key_exists($user_id, $posttree)) { $posttree[$user_id] = ""; }
        while(strlen($posttree[$user_id]) < $p - 1) {  $posttree[$user_id] .= "x"; }
        $posttree[$user_id] .= $storeValue;
//        while(strlen($pretree[$user_id]) < $p - 1) {   $pretree[$user_id] .= "x"; }
//print "user_id is $user_id<P>";
//print "storeValue is " . $storeValue;
//print "pretree[$user_id] was " . $pretree[$user_id] . " and is now ";
//        $pretree[$user_id] .= $storeValue;  
//print "$pretree[$user_id]<P>";
        //$students_on_post_route[$storeValue][] .= $user_id;      
        //print "<BR>id is $user_id ---> $str<P> =====> $storeValue";
      }
      foreach ($posttree as &$string) {
        if (strlen($string) < $p) { $string .= "x"; }
      }
    }
  }


foreach ($pretree as $user_id => $path) {
//  print "We got $user_id -> $path<P>";
  $tmp_string = str_split($path);
  $pre_path = "";
  //needs to be level 1, since the initial node in the tree is level 1.
  $level = 1;
  foreach ($tmp_string as $k => $c) {
    $pre_path .= "$c";
//print "pre_path is now: $pre_path<P>";

    if (!array_key_exists("$pre_path", $students_on_pre_route) ||
        !array_key_exists('student_ids', $students_on_pre_route["$pre_path"]) ||
        !array_key_exists("$user_id", $students_on_pre_route["$pre_path"]['student_ids'])) {
        $students_on_pre_route["$pre_path"]['student_ids']["$user_id"] = "";
        $students_on_pre_route["$pre_path"]['source_file']["$user_id"] = "";
    }
    if (!array_key_exists("$user_id", $preConceptsByStudentID) ||
        !array_key_exists('level', $preConceptsByStudentID["$user_id"]) ||
        !array_key_exists("$level", $preConceptsByStudentID["$user_id"]['level'])) {
      $preConceptsByStudentID["$user_id"]['level']["$level"] = "";
    }
    $students_on_pre_route["$pre_path"]['student_ids']["$user_id"] = $preConceptsByStudentID["$user_id"]['course'];
    $students_on_pre_route["$pre_path"]['source_file']["$user_id"] = $preConceptsByStudentID["$user_id"]['level']["$level"];
    $level++;
  }
}


foreach ($posttree as $user_id => $path) {
//  print "We got $user_id -> $path<P>";
  $tmp_string = str_split($path);
  $post_path = "";
  //needs to be level 1, since the initial node in the tree is level 1.
  $level = 1;
  foreach ($tmp_string as $k => $c) {
    $post_path .= "$c";

    if (!array_key_exists("$post_path", $students_on_post_route) ||
        !array_key_exists('student_ids', $students_on_post_route["$post_path"]) ||
        !array_key_exists("$user_id", $students_on_post_route["$post_path"]['student_ids'])) {
        $students_on_post_route["$post_path"]['student_ids']["$user_id"] = "";
        $students_on_post_route["$post_path"]['source_file']["$user_id"] = "";
    }
    if (!array_key_exists("$user_id", $postConceptsByStudentID) ||
        !array_key_exists('level', $postConceptsByStudentID["$user_id"]) ||
        !array_key_exists("$level", $postConceptsByStudentID["$user_id"]['level'])) {
      $postConceptsByStudentID["$user_id"]['level']["$level"] = "";
    }
    $students_on_post_route["$post_path"]['student_ids']["$user_id"] = $postConceptsByStudentID["$user_id"]['course'];
    $students_on_post_route["$post_path"]['source_file']["$user_id"] = $postConceptsByStudentID["$user_id"]['level']["$level"];
    $level++;
  }
}




if(0) {
  print "<div style='position:absolute;top:4000px'>";

  foreach ($students_on_post_route as $k => $v) {
    print "<P>$k: ";
    print_r($v['source_file']);
    print_r(array_values($v['student_ids']));
    print "<P>";
  //print_r($posttree);
  }
  print "<div>HERE WE GO with postConceptsByStudentID<P>";
  print_r($postConceptsByStudentID);
  print "</div>";
  print "</div>";
}

  //Go through and create the routes for the changetree:
  $maxLength = 0;
  for ($p = 1; $p <= 9; $p++) {
    $conditionParts = array_key_exists($p, $evalToTrue) ? explode(" ", $evalToTrue[$p]) : explode(" ", '     ');
    if (($conditionParts[0] == "") && ($conditionParts[3] == "") && ($conditionParts[5] == "")) {

    }
    else {
      $maxLength++;
      //go through each studentID and see if they meet the condition:
      foreach($postConceptsByStudentID as $user_id => $performance) {
        $postboola = (array_key_exists(0, $conditionParts) && array_key_exists($conditionParts[0], $performance)) ? 
                     $performance[$conditionParts[0]] :
                     "";
        $postboolb = (array_key_exists(3, $conditionParts) && array_key_exists($conditionParts[3], $performance)) ?
                     $performance[$conditionParts[3]] :
                     "";
        $postboolc = (array_key_exists(5, $conditionParts) && array_key_exists($conditionParts[5], $performance)) ?
                     $performance[$conditionParts[5]] :
                     "";

        $preboola = (array_key_exists($user_id, $preConceptsByStudentID) &&
                     array_key_exists(0, $conditionParts) &&
                     array_key_exists($conditionParts[0], $preConceptsByStudentID[$user_id])) ?
                    $preConceptsByStudentID[$user_id][$conditionParts[0]] : "";
        $preboolb = (array_key_exists($user_id, $preConceptsByStudentID) &&
                     array_key_exists(3, $conditionParts) &&
                     array_key_exists($conditionParts[3], $preConceptsByStudentID[$user_id])) ?
                    $preConceptsByStudentID[$user_id][$conditionParts[3]] : "";
        $preboolc = (array_key_exists($user_id, $preConceptsByStudentID) &&
                     array_key_exists(5, $conditionParts) &&
                     array_key_exists($conditionParts[5], $preConceptsByStudentID[$user_id])) ?
                    $preConceptsByStudentID[$user_id][$conditionParts[5]] : "";

        $preStoreValue;
        $postStoreValue;
        if ((array_key_exists(4, $conditionParts) && ($conditionParts[4] == "||")) || 
            (array_key_exists(4, $conditionParts) && ($conditionParts[4] == "&&"))) {
          if (($performance[$conditionParts[0]] == "") ||
              ($performance[$conditionParts[0]] == ".") ||
              ($performance[$conditionParts[3]] == "") ||
              ($performance[$conditionParts[3]] == ".") ||
              ($performance[$conditionParts[5]] == "") ||
              ($performance[$conditionParts[5]] == ".")) {
            $postStoreValue = "x";
            $preStoreValue = "x";
          }
          else {
            $postTruthString = "$postboola" . $conditionParts[1] . "($postboolb" . $conditionParts[4] . "$postboolc)";
            eval("\$postStoreValue = " . attach_end($postTruthString));
            $preTruthString  = "$preboola"  . $conditionParts[1] . "($preboolb"  . $conditionParts[4] . "$preboolc)";
            eval("\$preStoreValue = " . attach_end($preTruthString));
          }
        }
        else if ((array_key_exists(1, $conditionParts) && ($conditionParts[1] == "||")) ||
                 (array_key_exists(1, $conditionParts) && ($conditionParts[1] == "&&"))) {
          if ((array_key_exists($conditionParts[0], $performance) && 
               (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == ".")))  ||
              (array_key_exists($conditionParts[3], $performance) &&
               (($performance[$conditionParts[3]] == "") || ($performance[$conditionParts[3]] == ".")))) {
             $postStoreValue = "x";
             $preStoreValue = "x";
          }
          else {
            if ($conditionParts[1] == "||") {
              $postTruthString = ($postboola == "" ? "False" : $postboola) . 
                                 $conditionParts[1] .
                                 ($postboolb == "" ? "False" : $postboolb);
              $preTruthString  = ($preboola == "" ? "False" : $preboola) . 
                                 $conditionParts[1] .
                                 ($preboolb == "" ? "False" : $preboolb);
            }
            else {
              //$conditionParts[1] == "&&";
              $postTruthString = ($postboola == "" ? "True" : $postboola) .
                                 $conditionParts[1] .
                                 ($postboolb == "" ? "True" : $postboolb);
              $preTruthString  = ($preboola == "" ? "True" : $preboola) .
                                 $conditionParts[1] .
                                 ($preboolb == "" ? "True" : $preboolb);
            }
            eval("\$postStoreValue = " . attach_end($postTruthString));
            eval("\$preStoreValue = " . attach_end($preTruthString));
          }
        }
        else {
          if ((array_key_exists(0, $conditionParts) && array_key_exists($conditionParts[0], $performance)) &&
              (($performance[$conditionParts[0]] == "") || ($performance[$conditionParts[0]] == "."))) {
            $postStoreValue = "x";
            $preStoreValue = "x";
          }
          else {
            if (($postboola == "") || ($postboola == ".")) {
              $postTruthString = "False";
            }
            else {
              $postTruthString = $postboola;
            }
//print "eval(\"\\\$postStoreValue = $postTruthString;\");";
            eval("\$postStoreValue = " . attach_end($postTruthString));
//print "postStoreValue get back " . attach_end($postTruthString) . " =>  $postStoreValue<BR>";
            if (($preboola == "") || ($preboola == ".")) {
               $preTruthString = "False";
            }
            else {
               $preTruthString = $preboola;
            }
            eval("\$preStoreValue = " . attach_end($preTruthString));
          }
        }
        if (!array_key_exists($user_id, $changetree)) { $changetree[$user_id] = ""; }
        while(strlen($changetree[$user_id]) < $p -1) {  $changetree[$user_id] .= "x"; }
	//turn all invalid preScores into 0 for purposes of calcuting change.
        $tmpPreValue = $preStoreValue == 1 ? 1 : 0;
        //We use a value of 4 to indicate that the user had a positive change on their score.
        $changetree[$user_id] .= $postStoreValue > $preStoreValue ? 4 : $tmpPreValue;
          //print "<BR>id is $user_id (slot $p) ---> $postStoreValue > $preStoreValue =====>" . $changetree[$user_id];
      }
      foreach ($posttree as &$string) {
        if (strlen($string) < $p) { $string .= "x"; }
      }
    }
  }




//  print "<P>Pre-Tree:  Correct<->1 Incorrect<->0 notAttempted<->. notAssigned<->x <BR>";

//  print_r($pretree);

//  print "<P><HR><P>Post-Tree: Correct<->1 Incorrect<->0 notAttempted<->. notAssigned<->x <BR>";

//  print_r($posttree);

//  print "<P>Change-Tree: Improvement<->4, Was Correct<->1, Was Incorrect<-> (0 or 4)<BR>";
//  print_r($changetree);

  $preRouteCount = array();
  $postRouteCount = array();
  $changeRouteCount = array();

  $prePartRoute = array();
  $postPartRoute = array();
  $changePartRoute = array();


  foreach ($pretree as &$route) {
    if (!(array_key_exists($route, $preRouteCount))) { $preRouteCount[$route] = 0; }
    $preRouteCount[$route] = $preRouteCount[$route] + 1;
  }

  foreach ($posttree as &$route) {
    if (!(array_key_exists($route, $postRouteCount))) { $postRouteCount[$route] = 0; }
    $postRouteCount[$route] = $postRouteCount[$route] + 1;
  }
  foreach ($changetree as &$route) {
      if (!(array_key_exists($route, $changeRouteCount))) { $changeRouteCount[$route] = 0; }
      $changeRouteCount[$route] = $changeRouteCount[$route] + 1;
  }

  foreach ($preRouteCount as $route => $count) {
    $partialRoute = "";
    $partsOfRoute = str_split($route);
    for ($j = 0; $j < count($partsOfRoute); $j++) {
      $partialRoute = $partialRoute . $partsOfRoute[$j];
      if (!(array_key_exists($partialRoute, $prePartRoute))) { $prePartRoute[$partialRoute] = 0; }
     $prePartRoute[$partialRoute] = $prePartRoute[$partialRoute] + $count;
    }
  }

  foreach ($postRouteCount as $route => $count) {
    $partialRoute = "";
    $partsOfRoute = str_split($route);
    for ($j = 0; $j < count($partsOfRoute); $j++) {
      $partialRoute = $partialRoute . $partsOfRoute[$j];
      if (!(array_key_exists($partialRoute, $postPartRoute))) { $postPartRoute[$partialRoute] = 0; }
      $postPartRoute[$partialRoute] = $postPartRoute[$partialRoute] + $count;
    }
  }

  foreach ($changeRouteCount as $route => $count) {
    $partsOfRoute = str_split($route);
    $OrigPartialRoute = "";
    $tmpPartialRoute = "";

    for ($j = 0; $j < count($partsOfRoute); $j++) {
      if ($partsOfRoute[$j] == 4) {
        $tmpPartialRoute = $OrigPartialRoute . 1;
        if (!(array_key_exists($tmpPartialRoute, $changePartRoute))) { $changePartRoute[$tmpPartialRoute] = 0; }
        $changePartRoute[$tmpPartialRoute] = $changePartRoute[$tmpPartialRoute] + $count;
        $OrigPartialRoute = $OrigPartialRoute . 0;
      }
      else {
        $OrigPartialRoute = $OrigPartialRoute . $partsOfRoute[$j];
        if (($j == $maxLength - 1) && ($partsOfRoute[$j] == 1)) {
          if (!(array_key_exists($OrigPartialRoute, $changePartRoute))) { $changePartRoute[$OrigPartialRoute] = 0; }
        }
      }
    }
  }

//  print "<P>Pre-Test Route: Correct<->1 Incorrect<->0 notAttempted<->. notAssigned<->x <BR>";
//  krsort($preRouteCount);
//  print "<TABLE>";
//  foreach($preRouteCount as $key => $val) {
//    print "<TR><TD>$key => $val</TD></TR>";
//  }
//  print "</TABLE>";

//  //  print_r($preRouteCount);

//  print "<BR><HR><P>Post-Test Route: Correct<->1 Incorrect<->0 notAttempted<->. notAssigned<->x <BR>";
//  krsort($postRouteCount);
//  print "<TABLE>";
//  foreach($postRouteCount as $key => $val) {
//    print "<TR><TD>$key => $val</TD></TR>";
//  }
//  print "</TABLE>";
//  //  print_r($postRouteCount);

//  print "<BR>Route Parts<BR>";
//  print "<TABLE>";
//
  $keysOfPostPartRoute = array();
  //print "maxLength is $maxLength and 2^$maxLength is " . pow(2,$maxLength+1);
  for ($i = 2; $i <= pow(2,$maxLength+1)-1; $i++) {
    $keysOfPostPartRoute[] = substr(strrev(decbin($i)), 0, -1);
  }
//  natcasesort($keysOfPostPartRoute);

//  foreach($keysOfPostPartRoute as $key) {
//    print "<TR><TD>$key => $postPartRoute[$key]</TD></TR>";
//  }
//  print "</TABLE>";

  $preCountTotal = 0;
  $postCountTotal = 0;
  foreach($keysOfPostPartRoute as $key) {
    if (strlen($key) == $maxLength) {
      $postCountTotal = array_key_exists($key, $postPartRoute) ? $postCountTotal + $postPartRoute[$key] : $postCountTotal;
      $preCountTotal = array_key_exists($key, $prePartRoute) ? $preCountTotal + $prePartRoute[$key] : $preCountTotal;
    }
  }
//not sure what the previous code fixed, but it was broken if the postRouteKeys had "x" as a common character.
$preCountTotal = 0;
  foreach ($preRouteCount as $k => $v) {
    $preCountTotal = $preCountTotal + $v;
  }
$postCountTotal = 0;
  foreach ($postRouteCount as $k => $v) {
    $postCountTotal = $postCountTotal + $v;
  }

//    print "<img src='diagram2.png'>";

//    print "</HTML>";
  include 'diagram2/class.diagram.php';
  include 'diagram2/class.diagram-ext.php';


  $xmlStart = '<?xml version="1.0" encoding="UTF-8"?>';
  $xmlStart .= '
                <diagram bgcolor="#f" bgcolor2="#d9e3ed">
               ';

  $xmlPost = "";
  $xmlPost = xmlPreOrder($xmlPost, '', $maxLength, $postCountTotal, $conditions, $concepts, $postPartRoute);

  $xmlPre = "";
  $xmlPre = xmlPreOrder($xmlPre, '', $maxLength, $preCountTotal, $conditions, $concepts, $prePartRoute);
  $xmlEnd = '</diagram>';

  //Create the changePartRoute array by doing:
  //for each key in postPartRoute, $postPartRoute[key] - $prePartRoute[key]

  $changeCountTotal = 0;
//  $changePartRoute = array();
//print "<P>Change Part Route: ";
//print_r($changePartRoute);
  foreach($keysOfPostPartRoute as $key) {
    $changeCountTotal = array_key_exists($key, $changePartRoute) ? $changeCountTotal + $changePartRoute[$key] : $changeCountTotal;

//    $changePartRoute[$key] = $postPartRoute[$key] - $prePartRoute[$key];

//    if (strlen($key) == $maxLength) {
//      $changeCountTotal = $changeCountTotal + abs($changePartRoute[$key]);
//    }
  }


  $xmlChange = "";
//$doOnlyRight = $_REQUEST['onlyRight'];
//$_REQUEST['onlyRight'] = 'onlyRight';

  $eligibleForChange = array();
  $possibleChange = 0;
  foreach ($keysOfPostPartRoute as $key) {
    if (substr($key, -1) == 0) {
      $tmpKey = substr($key, 0, -1);
      $tmpKey .= "1";
      $eligibleForChange[$tmpKey] = (!array_key_exists($key, $prePartRoute) || ($prePartRoute[$key]  == "")) ? "/1" : "/" . $prePartRoute[$key];
      if (!(array_key_exists($tmpKey, $changePartRoute))) { $changePartRoute[$tmpKey] = 0; }
      $eligibleForChange[$key] = "";
      $possibleChange = array_key_exists($key, $prePartRoute) ? $possibleChange + $prePartRoute[$key] : $possibleChange;
    }
    else if (substr($key, -1) == 1) {
      //just check to make sure this key exists if it is of length maxLength.  If it doesn't, then make it be a 0.
      if (strlen($key) == $maxLength) {
        if (!(array_key_exists($key, $changePartRoute))) { $changePartRoute[$key] = 0; }
      }
    }
    else {
      //don't put anything here, since we've already populated the improved and failed to improve branches.
    }
  }

$oldPruneValue = isset($_REQUEST['prune']) ? $_REQUEST['prune'] : "";
$_REQUEST['prune'] = "";
$oldPruneBelow = isset($_REQUEST['prune_below']) ? $_REQUEST['prune_below'] : "";
$_REQUEST['prune_below'] = "";
$oldPruneCutoff = isset($_REQUEST['prune_cutoff']) ? $_REQUEST['prune_cutoff'] : "";
$_REQUEST['prune_cutoff'] = "";
  $xmlChange = xmlPreOrder($xmlChange, '', $maxLength, $changeCountTotal, $conditions, $concepts, $changePartRoute, "   ", "Improved:", $eligibleForChange);
$_REQUEST['prune'] = $oldPruneValue;
$_REQUEST['prune_below'] = $oldPruneBelow;
$_REQUEST['prune_cutoff'] = $oldPruneCutoff;

//$_REQUEST['onlyRight'] = $doOnlyRight;

//  print "<HR><P><pre>";
//  print htmlentities($xmlStart . $xml . $xmlEnd);
//  print "</pre><P><HR>";

  $preDiagram = new Diagram();
  $preDiagram->loadXmlData($xmlStart . $xmlPre  . $xmlEnd);
  $preDiagramExt = new DiagramExtended($xmlStart . $xmlPre . $xmlEnd);
  $preDiagramExt->loadXmlData($xmlStart . $xmlPre . $xmlEnd);
  $preData = $preDiagramExt->getNodePositions();
  $preDiagram->Draw('preDiagram2.png');

  $postDiagram = new Diagram();
  $postDiagram->loadXmlData($xmlStart . $xmlPost . $xmlEnd);
  $postDiagram->Draw('postDiagram2.png');
  $postDiagramExt = new DiagramExtended($xmlStart . $xmlPost . $xmlEnd);
  $postDiagramExt->loadXmlData($xmlStart . $xmlPost . $xmlEnd);
  $postData = $postDiagramExt->getNodePositions();

  $changeDiagram = new Diagram();
  $changeDiagram->loadXmlData($xmlStart . $xmlChange . $xmlEnd);
  $changeDiagram->Draw('changeDiagram2.png');
  $changeDiagramExt = new DiagramExtended($xmlStart . $xmlChange . $xmlEnd);
  $changeDiagramExt->loadXmlData($xmlStart . $xmlChange . $xmlEnd);
  $changeData = $changeDiagramExt->getNodePositions();

  if (isset($_REQUEST['onlyRight']) && ($_REQUEST['onlyRight'] == 'onlyRight')) {
    print "<TABLE><TR><TD><H2>Pre-Test Performance (Final Count: $preCountTotal)</H2></TD><TD><H2>Post-Test Performance (Final Count: $postCountTotal) </H2></TD></TR>";
    print "<TR><TD><img src='preDiagram2.png'></TD><TD><img src='postDiagram2.png'></TD></TR>";
    print "<TR><TD><H2>Change In Performance ($changeCountTotal of $possibleChange Possible Improvements)</H2></TD><TD></TD></TR>";
    print "<TR><TD><img src='changeDiagram2.png'></TD><TD></TD></TR>";
    print "<TR><TD>We calculate the change in performance as follows:  There is a possibility of improvement for incorrect or unanswered question in a student's pre-test.  The student's pre-test performance routes a path through the tree.  If they correctly answer a question on the post-test which was incorrect on the pre-test, then this improvement adjusts their path through the tree.  The blue lines indicate the number of students who improved on that question from their pre-test improvement.  The blue line shows the number of students who improved on that question out of the number eligible to improve on that question.  We call this percent the State Percent, or SP.  We let OP be the number of students who improved on a state divided by the total number of opportunities in the tree to improve.  The weighting on the tree is given by (SP + 2*OP)/(1+SP+2*OP)</TD><TD></TD></TR>";
    print "</TABLE>";
  }
  else {
$preTop = 180 + 20*$maxLength;
$postTop = 180 + 20*$maxLength + 75 + 50*$maxLength + 150;
$changeTop = 180 + 20*$maxLength + 75 + 50*$maxLength + 150 + 75 + 50*$maxLength + 150;
//$srcTop = $postTop + 75 + 50*$maxLength + 50;
$changeDesc = $postTop + 75 + 50*$maxLength + 150 + 75 + 50*$maxLength + 150;
$srcTop = $postTop + 75 + 50*$maxLength + 150 + 75 + 50*$maxLength + 150 + 100;

$H2PreTop = $preTop - 55 + 30 - 20;
$H2PostTop = $postTop - 55 + 30 - 20;
$H2ChangeTop = $changeTop - 55 + 30 - 20;

    print "<H2 style='position:absolute;left:0;top:$H2PreTop;'> Pre-Test Performance (Final Count: $preCountTotal)</H2>";
    print "<img src='preDiagram2.png' border='1' style='position:absolute;left:0;top:$preTop;' />";


    print "<H2 style='position:absolute;left:0;top:$H2PostTop;'>Post-Test Performance (Final Count: $postCountTotal)</H2>";
    print "<img src='postDiagram2.png' border='2' style='position:absolute;left:0;top:$postTop;' />";

    print "<div style='position:absolute;left:0;top:" . ($H2ChangeTop - 120) . ";'>";

//PREPARE THE FORM:
  $possible_courses[] = $course;
  $course = 'Math160_F2010_awangberg';
  $possible_courses[] = $_REQUEST['courses']; //$course;

  foreach ($possible_courses as $tmp_k => $course) {
    $conceptBank[] = $course;
    $abbrConceptBank[] = "";
    $quizWithConcept[] = "";
    foreach ($quizName["$course"] as $tmp_q => $quiz) {
//print "course: $course --- [$tmp_q] => $quiz<BR>";
    //for ($i = 0; $i < count($quizName); $i++) {
    //  $quiz = $quizName[$i];
      $query = 'SELECT source_file FROM `' . $course . '_problem` WHERE set_id = "' . $quiz . '"';
      $result = mysql_query($query, $con);
      $count = 1;
      $spaces = " ";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $conceptBank[] = $quiz . " " . $row['source_file'];
        $abbrConceptBank[] = substr($quiz, 0, 1) . $count . ":" . $spaces;
        $quizWithConcept[] = "$quiz";
        $count = $count + 1;
        if ($count >= 10) { $spaces = ""; }
      }
    }
  }


//INSERT_FORM_PART
    print "<form method='post' action=''>";

    print "Select Problems:";
    print "<select name='concepta' id='concepta' multiple='multiple'>";
    print "<option value=''></option>";
    for ($j = 0; $j < count($conceptBank); $j++) {
      $this_c = $conceptBank[$j];
      $this_c = preg_replace("/group\:/", "", $this_c);
      if ($conceptBank[$j] == $this_c) {
        print "<option value='$conceptBank[$j]'>$abbrConceptBank[$j] $conceptBank[$j]</option>\n";
      }
    }
    print "</select>";
    print " | For Groups: ";
    print "<select name='groups' id='groups' multiple='multiple'>";
    ksort($postPartRoute);
    foreach ($postPartRoute as $routeString => $num_on_route) {
      if (strlen($routeString) == 5) {
        print "<option value='";
        $tmp_val = "";
        foreach ($students_on_post_route[$routeString] as $tmp_student_id_array) {
          foreach ($tmp_student_id_array as $tmp_student_id => $tmp_course_id) {
            $tmp_val .= "$tmp_student_id" . "___" . $tmp_course_id . "_____";
          }
        }
        print "$tmp_val'>" . $routeString . " (" . $num_on_route . ")</option>";
      }
    }
    print "</select>";
    print "| width: <input type='text' id='img_width' value='" . $_REQUEST['data_map_width'] . "' size='1'>";
    print "| pps: <input type='text' id='img_pps' value='" . $_REQUEST['pps'] . "' size=1 >";
    print "<span id='sorted_group_link'>use_this_link</span>";
    print "<span id='generated_table'>generated_table</span>";
    print "<input type='submit' id='gen_table' value='Generate Table'>";
    print "<input type='submit' id='g' value='Submit'>  </form>";
    //print "<span id='sorted_group_link'>use_this_link</span>";

print "</div>";
    print "<H2 style='position:absolute;left:0;top:$H2ChangeTop;'>Change In Performance ($changeCountTotal of $possibleChange Possible Improvements)</H2>";
    print "<img src='changeDiagram2.png' border='2' style='position:absolute;left:0;top:$changeTop;' />";


    $preSelected = (isset($_GET['name']) ? $_GET['name'] : null);
    $postSelected = (isset($_GET['name']) ? $_GET['name'] : null);
    $changeSelected = (isset($_GET['name']) ? $_GET['name'] : null);

//print "<div style='position:absolute;top:3000px'><PRE>";
//print_r($xmlPre);
//print_r($students_on_post_route);
//print "</PRE></div>";

//print "<BR><H1>Pre-Test Data</H1><BR>";
    echo_map($preData, $preSelected, http_build_query(my_array_merge($_POST, $_GET)), $preTop, 0, $students_on_pre_route);

if (1) {
    echo_map($postData, $postSelected, http_build_query(my_array_merge($_POST, $_GET)), $postTop, 0, $students_on_post_route);
    echo_map($changeData, $postSelected, http_build_query(my_array_merge($_POST, $_GET)), $changeTop, 0);
}

    print "<div style='position:absolute;left:0;top:$changeDesc;' >";
//    print "<div sytle='position:absolute;left:0;top:$changeDesc;' >";
    print "<div>We calculate the change in performance as follows:  There is a possibility of improvement for each incorrect or unanswered question in a student's pre-test.  The student's pre-test performance routes a path through the tree. If they correctly answer a question on the post-test which was incorrect on the pre-test, then this improvement adjusts their path through the tree.  The blue lines indicate the number of students who improved on that question from their pre-test improvement.  The blue line shows the number of students who improved on that question out of the number eligible to improve on that question.  We call this percent the State Percent, or SP.  We let OP be the number of students who improved on a state divided by the total number of opportunities in the tree to improve.  The weighting on the tree is given by (SP + 2*OP)/(1+SP+2*OP)</div>";

//    print "</div>";
    print "<div style='position:absolute;left:0;top:$srcTop;' ><div style='font-weight:bold'>Generating URL:</div>";

   print "<BR>http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/change_in_scores_with_pca_by_concept_pre_post2.php?" . http_build_query(my_array_merge($_POST, $_GET));

//print "</BR><BR><PRE>";
//print_r($postPartRoute);
//print_r($students_on_post_route);
//print "</PRE>";

    print "</BR></div>"; 


  }

  print "</HTML>";

  //close connection
  mysql_close($con);

}
else {
  $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);

  if (!$con) {
    die('Could not connect: ' . mysql_error());
  }

  $db = "webwork";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $possible_courses = array();
//  $course = 'Math160_F2009_awangberg';
//  $possible_courses[] = $course;
  $course = 'Math160_F2010_awangberg';
  $possible_courses[] = $course;
  $course = 'Math160_F2011_awangberg';
  $possible_courses[] = $course;


  $conceptBank = array();
  $abbrConceptBank = array();
  $quizWithConcept = array();

  foreach ($possible_courses as $tmp_k => $course) {
    $conceptBank[] = $course;
    $abbrConceptBank[] = "";
    $quizWithConcept[] = "";
    foreach ($quizName["$course"] as $tmp_q => $quiz) {
//print "course: $course --- [$tmp_q] => $quiz<BR>";
    //for ($i = 0; $i < count($quizName); $i++) {
    //  $quiz = $quizName[$i];
      $query = 'SELECT source_file FROM `' . $course . '_problem` WHERE set_id = "' . $quiz . '"';
      $result = mysql_query($query, $con);
      $count = 1;
      $spaces = " ";
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $conceptBank[] = $row['source_file'];
        $abbrConceptBank[] = substr($quiz, 0, 1) . $count . ":" . $spaces;
        $quizWithConcept[] = "$quiz";
        $count = $count + 1;
        if ($count >= 10) { $spaces = ""; }
      }
    }
  }

  $db = "wwSession";

  //select the database '$db'
  $result = mysql_select_db("$db", $con);

  $conceptBank_description = array();
  $query = 'SELECT concept_bank, description FROM `conceptBankDescription`';
  $result = mysql_query($query, $con);
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $this_c = $row['concept_bank'];
    $this_d = $row['description'];
    $conceptBank_description[$this_c] = $this_d;
  }

  mysql_close($con);

?>

<form method="post" action="">
<H2>Options</H2><BR>
<input type='checkbox' name='prune' value='prune'>Prune Empty Nodes</checkbox><BR>
<input type='checkbox' name='prune_below' value='prune_below'>Prune Nodes At or Below</checkbox>
<input type='text' name='prune_cutoff' /> (Note: links do not work for Pruning Value of 1 or greater. <BR>
<input type='checkbox' name='onlyRight' value='onlyRight'>Single Right Tree</checkbox><BR>
<input type='checkbox' name='colorConnection' value='colorConnection'>Color Connections</checkbox><P>
Width of Data Map: <input type='text' name='data_map_width' value='800' /><BR>
Pixels Per Second: <input type='text' name='pps' value='1'/><BR>

<H2>Select Courses</H2><BR>

<?php
for ($c = 0; $c < count($courses); $c++) {
  print "<input name='courses[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
}
?>

<H2>Select Concepts</H2><BR>

<BR>pca function composition (taxonomy #6):  questions <B>4, 5, 12, 16, 17, 20, 23</B></BR>
<BR>pca contextual function rep (taxonomy #15): questions <B>3, 4, 7, 8, 10, 11, 15, 17, 18, 20, 22</B></BR>
<P><B>Fall 2010 Quizzes</B><BR>
<BR>Function Composition: Quiz Wk 1 (2, 3, 4), Quiz Wk 3 (), Quiz Wk 5 (2?), Quiz Wk 7 (6, 7), Quiz Wk 10 (2, 4, 5), Quiz Wk 11 (1, 3?, 4?, 5), Quiz Wk 13 ()</BR>
<BR>Contextualized Problems: Quiz Wk 1 (4), Quiz Wk 3 (2), Quiz Wk 5 (4?), Quiz Wk 7 (), Quiz Wk 10 (2?, 3?), Quiz Wk 11 (3, 4?), Quiz Wk 13 (1, 2)</BR>
<BR>Graphing Problems: Quiz Wk 1 (2, 3), Quiz Wk 3 (1, 2, 4), Quiz Wk 5 (1?, 4), Quiz Wk 7 (1), Quiz Wk 10 (1, 3), Quiz Wk 11 (2?, 3?), Quiz Wk 13 (1, 2)</BR>
<BR>Understanding Beasts: Quiz Wk 1(2? 3? 4?), Quiz Wk 3 (1, 2), Quiz Wk 5 (4?), Quiz Wk 7 (1), Quiz Wk 10 (3, 4, 5), Quiz Wk 11 (2?, 3, 4), Quiz Wk 13 (1?, 2?)</BR>

<TABLE>
<TR><TD>Level</TD><TD>Concept 1</TD><TD>Condition</TD><TD>Concept 2</TD><TD>Condition</TD><TD>Concept3</TD><TD>Implication</TD></TR>
<?php
  for ($i = 1; $i <= 9; $i++) {
    print "<TR><TD>$i</TD>";

    print "<TD><select name='concepta" . $i . "'>";
    print "<option value=''></option>\n";
    for ($j = 0; $j < count($conceptBank); $j++) {
      $this_c = $conceptBank[$j];
      $this_c = preg_replace("/group\:/", "", $this_c);
      print "<option value='$conceptBank[$j]'>$abbrConceptBank[$j] $conceptBank[$j]</option>\n";
    }
    print "</select></TD>";
    print "<TD><select name='conditionb" . $i . "'>";
    print "<option value=''></option>\n";
    print "<option value='||'>OR</option>\n";
    print "<option value='&&'>AND</option>\n";
    print "</TD>\n";

    print "<TD><font size='+2'> ( </font> <select name='conceptb" . $i . "'>";
    print "<option value=''></option>\n";
    for ($j = 0; $j < count($conceptBank); $j++) {
      print "<option value='$conceptBank[$j]'>$abbrConceptBank[$j] $conceptBank[$j]</option>\n";
    }
    print "</select></TD>";
    print "<TD><select name='conditionc" . $i . "'>";
    print "<option value=''></option>\n";
    print "<option value='||'>OR</option>\n";
    print "<option value='&&'>AND</option>\n";
    print "</TD>\n";

    print "<TD><select name='conceptc" . $i . "'>";
    print "<option value=''></option>\n";
    for ($j = 0; $j < count($conceptBank); $j++) {
      print "<option value='$conceptBank[$j]'>$abbrConceptBank[$j] $conceptBank[$j]</option>\n";
    }
    print "</select><font size='+2'> ) </font></TD>";
    print "<TD><select name='implies" . $i . "'>";
    print "<option value='implies1'>==></option>\n";
    print "<option value='implies2'>split2</option>\n";
    print "<option value='implies3'>split3</option>\n";
    print "<option value='implies4'>split4</option>\n";
    print "</TD>\n";


    print "</TR>\n";
  }
?>
</TABLE>
<P>
<input type="submit" name="Submit" value="Submit"><BR>
</form>
<?php
  print "<BR><HR><BR><TABLE><TR><TD>Concept Bank</TD><TD>Description</TD></TR>\n";
    for ($j = 0; $j < count($conceptBank); $j++) {
      $this_c = $conceptBank[$j];
      $this_c = preg_replace("/group\:/", "", $this_c);
      print "<TR><TD>$abbrConceptBank[$j] $conceptBank[$j]</TD><TD>";
      print (array_key_exists($this_c, $conceptBank_description)) ? $conceptBank_description[$this_c] : "";
      print "</TD></TR>\n";
    }
  print "</TABLE>\n";
}
?>
