<?php

include("matrix.php");

function make_scatter_plot(&$topics, &$data, &$graph_options) {
  //topics needs to have the following associated information:
    //$topics[index]['name'] = topic_name
    //$topics[index]['color'] = topic_color
    //$topics[index]['color2'] = topic_color2 color for second ring on point
    //$topics[index]['n'] = number of data points   ??
    //$topics[index]['size_key'] = key to use for size of point
    //$topics[index]['size_key2'] = key to use for second ring around point.
    //$topics[index]['regress_show'] = true / false for showing regression band
    //$topics[index]['regress_type'] = highest polynomial power of regression line to match.
    //$topics[index]['regress_show_eq'] = true / false for printing out regression equation
    //$topics[index]['legend_string'] = "string for legend"
    //$topics[index]['show_data_string'] = true/false for each data point
    //$topics[index]['linkable'] = true/false for if clicking on data point activates link

  //$data needs to have the horizontal data for each point.
    //$data[topic_name][index]['h'] = value between ... and ....
    //$data[topic_name][index]['v'] = value between ... and ....
    //$data[topic_name][index]['size'][key] = size to use for this point for this key
    //$data[topic_name][index]['data_string'] = "string for data point"
    //$data[topic_name][index]['data_string2'] = "string for outer ring on data point"
    //$data[topic_name][index]['link'] = "string for link"
    //$data[topic_name][index]['link2'] = "string for link for outer ring on data point"
    //$data[topic_name][index]['username'] = username

  //$graph_options is just key => value array of options for the plot.
    //$graph_options['width'] = width
    //$graph_options['height'] = height
    //$graph_options['legend'] = array of data:
         //$graph_options['legend']['blah'] = value
         //$graph_options['legend']['x'] = value
         //$graph_options['legend']['y'] = value
         //$graph_options['legend']['width'] = value
         //$graph_options['legend']['height'] = value
    //$graph_options['size1_circle'] = radius value
    //$graph_options['size1_title'] = "title string"
    //$graph_options['size2_circle'] = radius value
    //$graph_options['size2_title'] = "title string"
    //$graph_options['show_usernames'] = true/false if can show student user names.
    //$graph_options['jitter_data'] = true/false if can jitter the datapoints
    //$graph_options['title'] = "title string"

  //initialize the variables:
  $max_topics = count($topics);
  $do_jitter = $graph_options['jitter_data'];
  $gw = $graph_options['width'];
  $gh = $graph_options['height'];

  $regression_line_eq = '';
  $print_out_debugging = 0;
  $print_out_xml = '';

  $print_out_xml .= "max_topics is $max_topics<BR>";



  //set up the box plot:
  print "<div style='border: 1px solid; background: rgb(240,240,240) none repeat scroll 0% 0%; position: relative; width:" . ($gw + 35) . "px; height:" . ($gh + 38) . "px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial; font-size: 10px;'>";

  print "<div style='border-left: 1px solid; border-bottom: 1px solid; border-top: 1px solid; background: rgb(243, 243, 243) none repeat scroll 0% 0%; position: absolute; left: 20px; top: 5px; width: " . $gw . "px; height: " . $gh . "px; -moz-background-clip: -moz-initial; -moz-background-origin: -moz-initial; -moz-background-inline-policy: -moz-initial;'></div>";



  //go through each topic, and plot the data as a scatter plot:
  for ($i = 0; $i < $max_topics; $i++) {
    $this_quiz = $topics[$i]['name']; 
    $size_key = $topics[$i]['size_key'];
    $regress_type = $topics[$i]['regress_type'];
    $color1 = $topics[$i]['color'];
    $color2 = $topics[$i]['color2'];


    //initalize the variables:
    $X = array();
    $Y = array();
    $H = array();  //inputs
    $V = array();  //outputs
    $C = array();  //change;  output - input

    $this_user = 0;
    $h_mean = 0;
    $v_mean = 0;
    $hv_mean = 0;
    $h_squared_mean = 0;
    $count = 0;

    $jitter = array();


    foreach ($data[$this_quiz] as $sid => $pt) {
      $hsize = $pt['size'][$size_key] / 2;
      $hsize2 = $pt['size']["$size_key" . '2'] / 2;

      $h = $pt['h'];
      $v = $pt['v'];

      $h_mean += $h;
      $v_mean += $v;
      $hv_mean += $h * $v;
      $h_squared_mean += $h * $h;
      $count++;

      $X[$count][0] = 1;
      $X[$count][1] = 0;
      $X[$count][2] = 0;
      $X[$count][3] = 0;

      if ($regress_type >= 1) { $X[$count][1] = $h; }
      if ($regress_type >= 2) { $X[$count][2] = $h*$h; }
      if ($regress_type >= 3) { $X[$count][3] = $h * $h * $h; }

      $Y[$count][0] = $v;


      // no jitter effect:
      $jit_left = 0;
      $jit_up   = 0;

      //process jitter effect, if needed:
      if ($do_jitter) {
        if (isset($jitter["$h"]["$v"])) { $jitter["$h"]["$v"]++; }
        else { $jitter["$h"]["$v"] = 0; }

        $jit_angle  = $jitter["$h"]["$v"];
	$jit_radius = ceil($jit_angle/11);
	$jit_left   = 4*$jit_radius*cos(4*2*3.14*$jit_angle/11);
        $jit_up     = 4*$jit_radius*sin(4*2*3.14*$jit_angle/11);
      }

      //print out the larger second ring:
      $left = (-$hsize2 + $gw*$h + 20 + $jit_left);
      $top = (-$hsize2 + $gh + 20 - ($gh*$v + 20 + $jit_up));
      print_circle($this_quiz, $left, 5 + $top, $hsize2 * 2, $color2, $pt['data_string2']);

      //print out the smaller inner ring:
      $left = (-$hsize + $gw*$h + 20 + $jit_left);
      $top = (-$hsize + $gh + 20 - ($gh*$v + 20 + $jit_up));
      print_circle($this_quiz, $left, 5 + $top, $hsize * 2, $color1, $pt['data_string']); 

      $H[] = $h;
      $V[] = $v;
      $C[] = 0 + $v - $h;
    }


    //calculate the best fit line for the data:
    $h_mean = $h_mean / $count;
    $v_mean = $v_mean / $count;
    $hv_mean = $hv_mean / $count;
    $h_squared_mean = $h_squared_mean / $count;

    $h_mean = compute_mean_val($H);
    $h_std_dev = compute_std_dev_val($h_mean, $H);
    $h_error = round(1.96*$h_std_dev/sqrt(count($H)),3);

    $v_mean = compute_mean_val($V);
    $v_std_dev = compute_std_dev_val($v_mean, $V);
    $v_error = round(1.96*$v_std_dev/sqrt(count($V)),3);

    $c_mean = compute_mean_val($C);
    $c_std_dev = compute_std_dev_val($c_mean, $C);
    $c_error = round(1.96*$c_std_dev/sqrt(count($C)),3);


    $XX = mult(mult(inverse_m(mult(transpose($X), $X)), transpose($X)), $Y);

    $b = ($hv_mean - ($h_mean * $v_mean)) / ($h_squared_mean - ($h_mean * $h_mean));
    $a = $v_mean - $b * $h_mean;
    $regression_line_eq .= $this_quiz . ': post = ' . round($XX[0][0], 4) . ' + ' . $XX[1][0] . 'x';
    $regression_line_eq .= $regress_type >= 2 ? ' + ' . round($XX[2][0], 4) . 'x<sup>2</sup>' : '';
    $regression_line_eq .= $regress_type >= 3 ? ' + ' . round($XX[3][0], 4) . 'x<sup>3</sup>' : '';
    $regression_line_eq .= '. average improvement: ' . $c_mean . '. std_dev: ' . $c_std_dev . '. error: +/- ' . $c_error . '. <BR>';

    //draw the regression line:
    for ($j = 0; $j <= $gw; $j++) {
      $x = $j / $gw;
      $y = ($a + $b*$x);
      //FIX THIS:
      if (isset($_REQUEST['remove_std_dev_band'])) {
        $c_std_dev = 0.004;
      }

      $curve_y = $XX[0][0];
      $curve_y += $regress_type >= 1 ? $XX[1][0]*$x : 0;
      $curve_y += $regress_type >= 2 ? $XX[2][0]*$x*$x : 0;
      $curve_y += $regress_type >= 3 ? $XX[3][0]*$x*$x*$x : 0;
      if ((($curve_y + $c_std_dev) <= 1.0) AND (($curve_y - $c_std_dev) >= 0)) {
        print_band($this_quiz, 20 + $gw*$x, (5 + $gh - $gh*$curve_y - $gh*$c_std_dev), 2*$gh*$c_std_dev, $color1, $this_quiz . ': y = ' . $XX[0][0] . ' + ' . $XX[1][0] . 'x + ' . $XX[2][0] . 'x^2 + ' . $XX[3][0] . 'x^3 => (' . $j . ', ' . $curve_y . ')');
      }
      else if (($curve_y + $c_std_dev) > 1.0) {
	$mid_y = (1 + $curve_y - $c_std_dev)/2;
        $tmp_c_std_dev = 1 - $mid_y;
        print_band($this_quiz, 20+$gw*$x, (5 + $gh-$gh*$mid_y - $gh*$tmp_c_std_dev), 2*$gh*$tmp_c_std_dev, $color1, $this_quiz . ': y = ' . $XX[0][0] . ' + ' . $XX[1][0] . 'x + ' . $XX[2][0] . 'x^2 + ' . $XX[3][0] . 'x^3 => (' . $j . ', ' . $curve_y . ')');
      }
      else if (($curve_y - $c_std_dev) < 0.0) {
	$mid_y = (0 + $curve_y + $c_std_dev)/2;
        $tmp_c_std_dev = 0 + $mid_y;
        print_band($this_quiz, 20+$gw*$x, (5 + $gh-$gh*$mid_y - $gh*$tmp_c_std_dev), 2*$gh*$tmp_c_std_dev, $color1, $this_quiz . ': y = ' . $XX[0][0] . ' + ' . $XX[1][0] . 'x + ' . $XX[2][0] . 'x^2 + ' . $XX[3][0] . 'x^3 => (' . $j . ', ' . $curve_y . ')');
      }
    }

    //print out the legend part for this quiz:
    $tmp_offset++;
    print_circle($this_quiz, $gw-200, 5 + $gh - 90 + $legend_vertical_offset, 15, $color1, $this_quiz);
    //ADW MANUAL FIX:  . ($this_quiz == "pcb" ? "Tutorials (Un)available" : $this_quiz) . 
    echo "<div class='$this_quiz' style='position: absolute; left: " . ($gw - 200 + 30) . "px; top:" . (5 + $gh - 90 + $legend_vertical_offset) . "px; font-size: medium;'>" . ($this_quiz == "pcb" ? "$this_quiz" : $this_quiz) . " (n = " . $topics[$i]['n'] . ")</div>";
    $legend_vertical_offset -= 30;
  }
  //finish printing the legend:
  echo "<div class='legend' style='position: absolute; left: " . ($gw - 200 + 50) . "px; top: " . (5 + $gh - 90 + $legend_vertical_offset) . "px; font-size: large;'>Legend</div>";
  $tmp_size = $graph_options['size1_circle']; 
  //$tmp_size = 2 + round(((60*60)/(5*60))^M_EULER, 0);
  print_circle("no_erase", $gw - 200, 5 + $gh - 90 + 30, $tmp_size, "#cccccc", "circle for " . $graph_options['size1_title']);
  echo "<div class='legend' style='position: absolute; left: " . ($gw - 200 + 30) . "px; top: " . (5+$gh - 90 + 30) . "px; font-size: medium;'> Size: " . $graph_options['size1_title'] . ".</div>";
  //$tmp_size = 2 + round(((120*60)/(5*60))^M_EULER, 0);
  $tmp_size = $graph_options['size2_circle'];
  print_circle("no_erase", $gw - 200 - 7, 5+$gh - 90 + 2*30, $tmp_size, "#aaaaaa", "circle for " . $graph_options['size2_title']);
  echo "<div class='legend' style='position: absolute; left: " . ($gw - 200 + 30) . "px; top: " . (5+$gh - 90 + 2*30) . "px; font-size: medium;'> Size: " . $graph_options['size2_title'] . ".</div>";

  //put in the table title and the bars:
  echo "<div id='title' style='position: absolute; left: " . ($gw - ((strlen($graph_options['title']) > 62.5) ? $gw : 6.25*strlen($graph_options['title'])))/2 . "px; top: " . (5 + $gh + 15) . "px; font-size: large'>" . $graph_options['title'] . "</div>";

  //put in the table axes:
  for ($j = 0; $j <= 10; $j++) {
    $i = round($j/10, 1);
    $ii = $j * 10;
    echo "<div style='position: absolute; left: 0px; top: " . (5 - 7 + round($gh - $i*$gh, 1)) . "px; width: 18px; height: 128px;' align='right'>" . $ii . "</div><BR>\n";
    echo "<div style='position: absolute; top: " . (5 + $gh) . "px; left: " . (12 + round($i*$gw, 1)) . "px; width: 18px; height: 20px;' align='center'>" . $ii . "</div><BR>\n";
  }

  echo "<div style='position: absolute; left: " . ($gw + 20) . "px; top: " . (5 + 0) . "px; width: 1px; height: " . $gw . "px; background-color:#123;'></div>";

  //the diagonal line:  y=x
  for ($j = 0; $j <= $gw; $j++) {
    echo "<div style='position: absolute; left: " . (20 + $j) . "px; top: " . (5 + $gh - $j) . "px; width: 1px; height: 1px; background-color:#123;'></div>";
  }


  echo '</div>';
  echo '<P>';

  for ($i = 0; $i < $max_topics; $i++) {
    $this_quiz = $topics[$i]['name'];
    print_naked_class_link($this_quiz, $this_quiz);
  }

  echo '<P>' . $regression_line_eq . '</P>';

  if ($print_out_debugging) {
    print $print_out_xml . '<BR>';

    print "<PRE>";
    print_r($topics);
    print "</PRE>";

    print "<P>DATA:</P>";
    print "<PRE>";
    print_r($data);
    print "</PRE>";


    print "<P>Graph Options:</P>";
    print "<PRE>";
    print_r($graph_options);
    print "</PRE>";
  }
} 

?>
