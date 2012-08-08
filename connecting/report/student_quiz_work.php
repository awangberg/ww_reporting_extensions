<?php

include("../access.php");
include("common.php");


function javascript() {

  $ret = "
    <head>
    <script src='../../flot/jquery.js'></script>
    <link type='text/css' href='../../jquery-ui/css/smoothness/jquery-ui-1.8.18.custom.css' rel='Stylesheet' />
    <link type='text/css' href='jquery/tagify-style.css' rel='Stylesheet' />
    <script type='text/javascript' src='../../jquery-ui/js/jquery-1.7.1.min.js'></script>
    <script type='text/javascript' src='../../jquery-ui/js/jquery-ui-1.8.18.custom.min.js'></script>
    <script type='text/javascript' src='jquery/jquery.tagify.js'></script>
    <style>
      .ui-autocomplete-loading { background: white right center no-repeat; }
      .ui-autocomplete { font-size: .8em; max-height: 100px; overflow-y: auto; padding-right: 20px; overflow-x: hidden;}
      /*  IE 6 doesn't support max-height.  */
      * html .ui-autocomplete { height: 100px; }

    </style>
    <script>
      $(document).ready(function(){
	var screenCssPixelRatio = (window.outerWidth - 8) / window.innerWidth;
	if (screenCssPixelRatio < .98 || screenCssPixelRatio > 1.02) { alert('Current Browser Zoom level prevents clicks on image maps to produce student work.  Please set Zoom Level to 100% in Chrome.'); }
        $('#replayWindow').hide();
        $('#marker').hide();
	$('#tracer1').hide();
	$('#tracer2').hide();
        $('#dialog-form').hide();
        $('#dialog:ui-dialog').dialog('destroy');

        var key_name = $('#key');
        var key_desc = $('#desc');
        var all_key_fields = $( [] ).add(key_name).add(key_desc);
        var tips = $( '.validateTips' );

        function secToHR(tis) {
          var min = Math.floor(tis / 60);
          var sec = Math.round(tis - 60*min);
          min = (min < 10) ? '0' + min : min;
          sec = (sec < 10) ? '0' + sec : sec;
          return '' + min + ':' + sec;
        }

        function updateTips(t) {
	  tips
	 	.text(t)
		.addClass('ui-state-highlight');
		setTimeout(function() {
			tips.removeClass('ui-state-highlight',1500);
		}, 500 );
	}
	function checkLength( o, n, min, max ) {
		if (o.val().length > max || o.val().length < min) {
			o.addClass( 'ui-state-error');
			updateTips( 'Length of ' + n + ' must be between ' + min + ' and ' + max + '.');
			return false;
		} else {
			return true;
		}
	}

	function checkRegexp( o, regexp, n) {
		if ( !(regexp.test( o.val() ) ) ) {
			o.addClass( 'ui-state-error' );
			updateTips(n);
			return false;
		} else {
			return true;
		}
	}


        function split(val) {
          return val.split(/,\s*/ );
        }

        function extractLast( term ) {
          return split( term ).pop();
        }
	
	$( '#dialog-form' ).dialog({
		autoOpen: false,
		height: 350,
		width: 450,
		modal: true,
		buttons: {
		  'Create Key': function() {
			var bValid = true;
			all_key_fields.removeClass('ui-state-error');

			bValid = bValid && checkLength( key_name, 'Key Name', 2, 40 );
			bValid = bValid && checkLength( key_desc, 'Key Description', 5, 80);

			bValid = bValid && checkRegexp( key_name, /^[a-z]([0-9a-z_ .?()\'])+$/i, 'Key name may consist of a-z, 0-9, underscores, spaces, . ? ( ) and \'.');
			bValid = bValid && checkRegexp( key_desc, /^[a-z]([0-9a-z_ .?()\'])+$/i, 'Description may consist of a-z, 0-9, underscores, spaces, .?(), and \'.');

			if (bValid) {
			  //submit the key and description to the database,
			  //and add that key to the list of keys.

			  $.ajax({ type: 'POST', url: 'annotating_tools/submit_key.php', 
				   data: $('#dialog-form :input').serialize(),
				   dataType: 'xml',
				   success: function(xml) {
//alert($(xml).text());
				     var thisCommentKeyID = $(xml).find('commentKeyID').text();
				     var thisKeyName = $(xml).find('keyName').text();
				     var thisKeyDesc = $(xml).find('keyDesc').text();

				     myTextArea.tagify('remove');
				     myTextArea.tagify('add', thisKeyName);
			           }
			  });
			  $(this).dialog('close');
			}
		  },
		  Cancel: function() {
			$( this ).dialog( 'close');
		  }
		},
		close: function() {
			all_key_fields.val( '' ) .removeClass('ui-state-error');
		}
	});
    
	var myTextArea = $('#keyField').tagify();

	myTextArea.tagify('inputField').autocomplete({
		source: function(request,response) {
		  $.getJSON('annotating_tools/keys.php', {
		    term: extractLast(request.term)
		  }, response );
		},
		search: function() {
		  //custom minLength
		  var term = extractLast( this.value );
		  if (term.length < 2 ) {
		    return false;
		  }
		},
		position: { of: myTextArea.tagify('containerDiv') },
		select: function(event, ui) { 
		  var terms = split( this.value );
		  //remove the current input
		  terms.pop();
		  //add the selected item
		  if (ui.item.id == 0) {
		    $('#dialog-form').dialog('open');
		  }
		  else {
		    terms.push( ui.item.label + '');
		  }
		  //add placeholder to get the comma-and-space at the end
		  terms.push( '' );
		  myTextArea.tagify('add', terms.join( '' ));
		  return false; 
		},
	})
	.data( 'autocomplete' )._renderItem = function (ul, item) {
	  return $( '<li></li>' )
		.data( 'item.autocomplete', item)
		.append( '<a>' + item.label + ': <font size=-2>' + item.desc + '</font></a>')
		.appendTo( ul );
	};


        $('#closeReplayWindow').click(function(event){
           $('#replayWindow').hide();
           $('#marker').hide();
        });

        $('#closeCommentForm').click(function(event){
	  $('#replayWindow').hide();
	  $('#marker').hide();  
return false;
	});

        //$('#CommentForm').submit(function() {


        $('#modifyCommentForm').click(function(event) {
//alert('modify comment form clicked');
	   theCommenter = $('#commenter').val();
	   if (theCommenter == '') { $('#commenter').addClass( 'ui-state-error' ); 
				     return false; 
				   }
           else {
	     var theData = $('#CommentForm :input').serialize();
	     theData = theData + '&keyFields=' + $('#keyField').tagify('serialize');
	     $.ajax({ 
			type: 'POST', 
			url: 'annotating_tools/modify_comment.php', 
			data: theData,
			dataType: 'xml',
			success: function(xml) {
				var thisCommentID = $(xml).find('problemCommentID').text();
				//alert('update the table entry');
				//var min = Math.floor($('#time').val() / 60);
				//var sec = Math.round($('#time').val() - 60*min);
				//min = (min < 10) ? '0' + min : min;
				//sec = (sec < 10) ? '0' + sec : sec;

				updateThisTable.replaceWith('<TR BGCOLOR=\'#aaaaaa\'><TD><a class=\'replay_to_time_on_whiteboard\' href=\'' + $('#href_string').val() + '\' target=\'Session\' sessionCommentID=\'' + thisCommentID + '\' displayReplayTime=\'' + $('#time').val() + '\' replayToSessionTime=\'' + $('#replayToSessionTime').val() + '\' sessionProblemID=\'' + $('#sessionProblemID').val() + '\'>' + secToHR($('#time').val()) + '</a></TD><TD>' + $('#commenter').val() + '</TD><TD>' + $('#keyField').tagify('serialize') + '</TD><TD>' + $('#comment').val() + '</TD></TR>');
			}
		 });
	     $('#replayWindow').hide();
	     //$('#marker').hide();
	     $('#commenter').removeClass('ui-state-error');
	     return false;
	   }
	});

	$('#submitCommentForm').click(function(event) {
//alert('submit comment form clicked');
//alert('updateThisTable is ' + updateThisTable.html());
           theCommenter = $('#commenter').val();
           if (theCommenter == '') { $('#commenter').addClass( 'ui-state-error' );
                                     return false;
                                   }
           else {
             var theData = $('#CommentForm :input').serialize();
  	     theData = theData + '&keyFields=' + $('#keyField').tagify('serialize');
	     var newRecord = $('#responseToSessionCommentID').val();
             $.ajax({ 
			type: 'POST', 
			url: 'annotating_tools/new_comment.php', 
			data: theData, 
			dataType: 'xml', 
			success: function(xml) {
             			var thisCommentID = $(xml).find('problemCommentID').text();
             			//alert('xml returned is ' + $(xml).text());
             			//alert('replayToSessionTime is ' + $('#replayToSessionTime').val());
             			//update the table entry in the webpage:
	     			$('#href_string').val('http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&replayPaceForAdmin=1000&problem_id=' + $('#sessionProblemID').val() + '&userDatabaseName=session&replayModeAsInstructor=yes&overRidePaceForAdmin=1000');
				//var min = Math.floor($('#time').val() / 60);
				//var sec = Math.round($('#time').val() - 60*min);
				//min = (min < 10) ? '0' + min : min;
				//sec = (sec < 10) ? '0' + sec : sec;
				var bgc = '#aaaaaa';
				if (newRecord == 'NEW') { bgc = '#ffffff'; }
				updateThisTable.after('<TR bgcolor=\'' + bgc + '\'><TD><a class=\'replay_to_time_on_whiteboard\' href=\'' + $('#href_string').val() + '\' target=\'Session\' sessionCommentID=\'' + thisCommentID + '\' displayReplayTime=\'' + $('#time').val() + '\' replayToSessionTime=\'' + $('#replayToSessionTime').val() + '\' sessionProblemID=\'' + $('#sessionProblemID').val() + '\'>' + secToHR($('#time').val()) + '</a></TD><TD>' + $('#commenter').val() + '</TD><TD>' + $('#keyField').tagify('serialize') + '</TD><TD>' + $('#comment').val() + '</TD></TR>');
			}
	      });
	      $('#replayWindow').hide();
	      //$('#marker').hide();
	      $('#commenter').removeClass('ui-state-error');
              return false;
	   }
        });


        $('a').click(function(event){
          event.preventDefault();
        });

        $('.graphXML').click(function(event){
          window.open(event.currentTarget, 'XML', 'width=600,height=400,scrollbars=yes');
        });

        $('.quizProblem').click(function(event){
          event.preventDefault();
          var s_str = $(this).attr('href');
          
          var offset = $(this).offset();
          var repx = event.pageX;
          var repy = event.pageY - 355 - (event.pageY - offset.top);
          if (repy < 0) {
            repy = offset.top + 85;
          }
          $.get(s_str, function(data) {
            //alert($(data).find('.problem').html());
            var h_str = $(data).find('.problem').html();
            $('#theProblem').replaceWith('<div id=\'theProblem\' style=\'position:absolute;left:' + offset.left + 'px;top:' + repy + 'px;background-color:#000000;width:487px;height:352px;\'><div id=\'blah\' style=\"position:absolute;left:1px;background-color:#EEEEEE;top:21px;width:485px;height:330px;overflow:scroll;\">' + h_str + '</div><div id=\'closeTheProblem\' style=\'position:absolute;left:1px;top:1px;width:485px;height:20px;background-color:#BBBBBB;\'>Click here to close the WeBWorK Problem.</div>');
          $('#closeTheProblem').click(function(event){
            $('#theProblem').replaceWith('<div id=\'theProblem\'><div id=\'closeTheProblem\'></div></div>');
          });

          });
          //window.open(s_str, w_name);

        });

        $('.quizWork').click(function(event){
          event.preventDefault();
          var s_str = $(this).attr('href');
          var w_name = $(this).attr('target');

          var offset = $(this).offset();
          var repx = event.pageX;
          var repy = event.pageY - 465 - (event.pageY - offset.top);
          if (repy < 0) {
            repy = offset.top + 85;
          }

//left:' + offset.left + '
          $('#theEntireProblem').replaceWith('<div id=\'theEntireProblem\' style=\'position:absolute;left:150px;top:' + repy + 'px;background-color:#000000;width:1002px;height:452px;overflow:scroll;\'><iframe style=\"background-color:#EEEEEE;left:2px;top:22px;width:999px;height:2000px;\" src=\"' + s_str + '\"></iframe><div id=\'closeTheEntireProblem\' style=\'position:absolute;left:2px;top:2px;width:999px;height:20px;background-color:#BBBBBB;\'>Click here to close the WeBWorK Problem.</div>');
          $('#closeTheEntireProblem').click(function(event){
            $('#theEntireProblem').replaceWith('<div id=\'theEntireProblem\'><div id=\'closeTheEntireProblem\'></div></div>');
          });
          //window.open(s_str, w_name);

        });

        $('.playWhiteboard').click(function(event){
          event.preventDefault();
          var s_str = $(this).attr('href');
          var w_name = $(this).attr('target');
          window.open(s_str, w_name);
        });

        $('a').dblclick(function(event){
          event.preventDefault();
        });
 
        var updateThisTable;
        var theCommenter = '';

        $('.replay_to_time_on_whiteboard').live('click', function(event){
	  event.preventDefault();
	  //put the comment directly below the clicked-upon comment.
	  updateThisTable = $(this).parent().parent();
	  var pps = " . $_REQUEST['pixels_per_second'] . ";
	  var s_str_original = $(this).attr('href');
          var s_str = s_str_original;
	  //replace the overRidePaceForAdmin=1000 with 0:
	  // and the replayPaceForAdmin=1000 with 0:
          s_str = s_str.replace('1000', '0');
          s_str = s_str.replace('1000', '0');
	  s_str = s_str + '&startTime=0&stopTime=';
	  s_str = s_str + $(this).attr('replayToSessionTime');
          //alert('s_str is ' + s_str);
	  var problem_id = $(this).attr('sessionProblemID');
          //alert('problem_id is ' + $(this).attr('sessionProblemID'));
	  var offset = $(this).offset();
	  var pps = 1;

	  var repx = event.pageX;
	  var repy = event.pageY - 445 - (event.pageY - offset.top);
	  if (repy < 0) {
	    repy = offset.top + 65;
          }
	  repy = repy - 45;
	  var img_x = offset.left;

          $('#replayWindow').show(500);
	  $('#modifyCommentForm').show(500);
          $('#marker').show(50);
	  //$('#responseToSessionCommentID').value='responseToSessionCommentID';
          $('#playingUntilTimeSpanHR').replaceWith('<span id=\'playingUntilTimeSpanHR\'>' + secToHR($(this).attr('displayReplayTime')) + ' </span>');
          $('#playingUntilTimeSpan').replaceWith('<span id=\'playingUntilTimeSpan\'>' + $(this).attr('displayReplayTime') + ' </span>');
          $('#replayWindow').css('left', offset.left);
          $('#replayWindow').css('top', repy);
          $('#responseToSessionCommentID').val($(this).attr('sessionCommentID'));
          $('#sessionCommentID').val($(this).attr('sessionCommentID'));
          $('#sessionProblemID').val($(this).attr('sessionProblemID'));
          $('#replayToSessionTime').val($(this).attr('replayToSessionTime'));
          $('#time').val($(this).attr('displayReplayTime'));
          $('#commenter').val(theCommenter);

	  //remove all existing keys from the keyField:
	  var empty_key_list = split($('#keyField').tagify('serialize'));
	  $.each(empty_key_list, function(key, value) { myTextArea.tagify('remove'); });
	  
	  $('#keyField').load('annotating_tools/getKeysForId.php?problem_id=' + problem_id + '&comment_id=' + $('#responseToSessionCommentID').val(), function(e){
		//alert('responseToSessionCommentID is ' + $('#responseToSessionCommentID').val());
		var terms = split(e); $.each(terms, function(key, value) { myTextArea.tagify('add', value);}); });
          //$('#keyField').val('Get the keys from sessionCommentID ' + $(this).attr('sessionCommentID'));
          //$('#keyField').val(updateThisTable.find('td').eq(2).html());
          $('#comment').val(updateThisTable.find('td:last').html());
          $('#href_string').val(s_str_original);
          
	  var img_top =  $(this).parent().parent().parent().parent().parent().parent().find('.whiteboard').find('img').offset().top;
	  var img_left = $(this).parent().parent().parent().parent().parent().parent().find('.whiteboard').find('img').offset().left;
	  var img_height = $(this).parent().parent().parent().parent().parent().parent().find('.whiteboard').find('img').height();
//alert('img_height is ' + img_height);
          $('#marker').css('top', img_top);
          $('#marker').css('left', 2 + img_left + ($(this).attr('displayReplayTime') * " . $_REQUEST['pixels_per_second'] . "));
	  $('#marker').height(img_height);

	  $('#replayWindow').css('top', img_top + img_height + 3);
	  $('#replayWindow').css('left', img_left);

          $('#whiteboardFrame').html('<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width=\"506.5px\" height=\"400px\" id=\"NOTSESSION\" align=\"middle\">         <param name=\"allowScriptAccess\" value=\"sameDomain\" />       <param name=\"allowFullScreen\" value=\"false\" />      <param name=\"wmode\" value=\"transparent\" />  <param name=\"movie\" value=\"'+ s_str + '\" />     <param name=\"quality\" value=\"high\" /><param name=\"bgcolor\" value=\"#ffffff\" />      <embed src=\"' + s_str + '\" quality=\"high\" bgcolor=\"#ffffff\" wmode=\"transparent\" width=\"506.5px\" height=\"400px\" name=\"NOTSESSION\" align=\"middle\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"false\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /> </object>');

        });

        $('.whiteboard').click(function(event){
	  event.preventDefault();
	  //alert($(this).parent().html());
	  //$(this).parent().parent().children().each(function(index) {
	  //	alert(index + ': ' + $(this).html());
          //});
          //alert($(this).parent().parent().find('.these_comments').html());
	  updateThisTable = $(this).parent().parent().find('.these_comments').find('table tr:last');
          var offset = $(this).offset()
          var pps = " . $_REQUEST['pixels_per_second'] . ";
          //var x = event.pageX - this.offsetLeft;
          //var y = event.pageY - this.offsetTop;

          var repx = event.pageX;
          var repy = event.pageY - 485 - (event.pageY - offset.top);
          if (repy < 0) {
            repy = offset.top + 65;
          }
          repy = repy - 45;
          //alert('x = ' + x + ' and y = ' + y);

          var s_str_original = $(this).attr('href');
	  var s_str = s_str_original;
          var img_x = offset.left;

          //replace the overRidePaceForAdmin=1000 with 0:
          //and the replayPaceForAdmin=1000 with 0:
          s_str = s_str.replace('1000', '0');
          s_str = s_str.replace('1000', '0');
          //add an upper limit to replay to:
          s_str = s_str + '&startTime=0&stopTime=';

          var time_clicked = 1000*(event.pageX - offset.left - 2)/pps;

//alert('clientX: ' + event.clientX + ' and offset.left: ' + offset.left + ' and event.pageX: ' + event.pageX);
//alert('time_clicked: ' + time_clicked);
          var play_to_time = 0;
       ";


//var $img_data = $('a > div', this).attr('id');
//alert('a');
//alert($('a', this).html());
//alert('b');

    if (isset($_REQUEST['link_to_session']) && ($_REQUEST['link_to_session'] == 1)) { //timing data included
        $ret .= " 
          var problem_id = 0;

          $(this).children('.timeData').each(function() {
            var t = $(this);
            problem_id = t.attr('data.problem_id');
            if (time_clicked > 0) {
              //alert('time_clicked: ' + time_clicked + ' total_view_time: '+t.attr('data.total_view_time') + 'initial_idle_time: '+t.attr('data.initial_idle_time'));
              time_clicked = time_clicked - t.attr('data.initial_idle_time');
              if (time_clicked > 0) {
                if (time_clicked > (t.attr('data.total_view_time') - t.attr('data.initial_idle_time'))) {
                  play_to_time = play_to_time + (t.attr('data.total_view_time') - t.attr('data.initial_idle_time'));
                }
                else {
                  play_to_time = play_to_time + time_clicked;
                }
              }
              time_clicked = time_clicked - (t.attr('data.total_view_time') - t.attr('data.initial_idle_time'));
            }
          })
//alert('play_to_time is: ' + play_to_time);
          if (play_to_time == 0) { play_to_time = 0.001; }
          //alert('play_to_time is: ' + play_to_time);
          s_str = s_str + play_to_time;

          $('#replayWindow').show(500);
	  $('#modifyCommentForm').hide();
          $('#marker').show(500);
          $('#responseToSessionCommentID').val('');
          $('#playingUntilTimeSpanHR').replaceWith('<span id=\'playingUntilTimeSpanHR\'>' + secToHR(((event.pageX - offset.left)/pps).toFixed(2)) + ' </span>');
          $('#playingUntilTimeSpan').replaceWith('<span id=\'playingUntilTimeSpan\'>' + ((event.pageX - offset.left)/pps).toFixed(2) + ' </span>');
          $('#replayWindow').css('left', offset.left);
          $('#replayWindow').css('top', repy);
          $('#responseToSessionCommentID').val('NEW');
          $('#sessionProblemID').val(problem_id);
          $('#replayToSessionTime').val(play_to_time);
          $('#time').val($(this).attr(((event.pageX - offset.left)/pps).toFixed(2)));
          $('#commenter').val(theCommenter);

          //remove all existing keys from the keyField:
          var empty_key_list = split($('#keyField').tagify('serialize'));
          $.each(empty_key_list, function(key, value) { myTextArea.tagify('remove'); });
          //$('#keyField').val('');

          $('#comment').val('');
          $('#href_string').val(s_str_original);
          $('#marker').css('left', event.pageX);
          $('#marker').css('top', (offset.top - 48));

          $('#whiteboardFrame').html('<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width=\"506.5px\" height=\"400px\" id=\"NOTSESSION\" align=\"middle\">         <param name=\"allowScriptAccess\" value=\"sameDomain\" />       <param name=\"allowFullScreen\" value=\"false\" />      <param name=\"wmode\" value=\"transparent\" />  <param name=\"movie\" value=\"' + s_str + '\" />     <param name=\"quality\" value=\"high\" /><param name=\"bgcolor\" value=\"#ffffff\" />      <embed src=\"' + s_str + '\" quality=\"high\" bgcolor=\"#ffffff\" wmode=\"transparent\" width=\"506.5px\" height=\"400px\" name=\"NOTSESSION\" align=\"middle\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"false\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /> </object>');
          ";
     }
     else {  // get the xml data that contains the timing information via ajax.
       $ret .= "
          $(this).children('.getTimeData').each(function() {
             var t = $(this);
             problem_id = t.attr('data.problem_id');
             if (time_clicked > 0) {
               var xml_src = t.attr('data.callhref');
               var this_Problem = t.attr('data.quizProblem');
//alert(xml_src);
               $.ajax({ type: 'GET', url: xml_src, dataType: 'xml', success: function(xml) {
//alert('b');
		 var prev_dt = '';
		 var prev_ft = -1;
		 var prev_tvt = -1;
		 var prev_wl = -1;
		 //alert('we need to process ' + time_clicked + 'ms, which was clicked on the map.');
                 $(xml).find('wwProblem').each(function() {
                   if ($(this).find('wwProblem_number').text() == this_Problem) {
                     $(this).find('ReplayPart').each(function() {
                       //alert('processing draw_id: ' + $(this).find('draw_id').text());
                       var tvt = $(this).find('total_view_time').text();
                       var wl = $(this).find('work_length').text();
		       var ft = $(this).find('first_time').text();
		       var dt = $(this).find('date_time').text();
		       if ((tvt == prev_tvt) && (wl == prev_wl) && (ft == prev_ft) && (dt = prev_dt)) {
			//alert('skipping draw_id: ' + $(this).find('draw_id').text() + ' since it is a duplicate record.');
                       }
                       else {
			 prev_dt = dt;
			 prev_ft = ft;
			 prev_tvt = tvt;
			 prev_wl = wl;
		         //alert('this draw_id had a total_view_time of ' + tvt + ', of which ' + (tvt - wl) + ' was waiting time followed by ' + wl + ' work time');
                         var iit = tvt - wl;
			 var show_time_clicked = time_clicked > 0 ? time_clicked : 'no more';
                         //alert('we have ' + show_time_clicked + ' ms left to process, and have accounted for ' + play_to_time + ' ms of actual student drawing.');
                       if (time_clicked > 0) {
			 //take off the 'paused' time:
                         time_clicked = time_clicked - iit;
			 //alert('we want to show this draw_id.');

			 //if time_clicked > 0, then we still need to process this work time:
                         if (time_clicked > 0) {
			   //if time_clicked > time worked for the segment, add all of the time worked to play_to_time.
                          if (time_clicked > (tvt - iit)) {
                             play_to_time = play_to_time + (tvt - iit); // use (tvt - iit) because + wl uses concatenation.
			     //alert('the desired work to display is further than this draw_id.');
                           }
                           else {
			     //the remaining part of time_clicked falls within this worked time, so just add it to play_to_time:
                             play_to_time = play_to_time + time_clicked;
			     //alert('the desired work to display occurs in this draw_id.');
                           }
                         }
			 //decrease time_clicked by work_length:
                         time_clicked = time_clicked - (tvt - iit); //use (tvt - iit) since - wl uses concatenation.
                       }
                     }});
                       if (play_to_time == 0) { play_to_time = 0.001; }
                       s_str = s_str + play_to_time;
                       //$('#replayWindow').append('localized x = (' + event.clientX + ' - ' + offset.left + ')/' + pps + ' = ' + (event.clientX - offset.left)/pps + 'seconds and y = ' + event.pageY + ' - ' + offset.top  + ' and href = ' + s_str);
//
//  To get the non-white background behind the flash player, we can not use an iframe due to transparency issues.
//  Instead, we load the flash player as html in the whiteboardFrame div, which was the iframe element.
//
//                       $('#replayWindow').replaceWith('<div id=\'replayWindow\' style=\'position:absolute;left:' + offset.left + 'px;top:' + repy + 'px;background-color:#EEEEEE;width:508px;height:420px;\'><iframe id=\'whiteboardFrame\' style=\"background-color:#EEEEEE;top:-40px;width:506.5px;height:400px;\" allowTransparency=\"true\" src=\"' + s_str + '\"></iframe><div id=\'closeReplayWindow\' style=\'position:absolute;left:0px;top:400px;width:508px;height:20px;background-color:#BBBBBB;\'>Click here to close student work.  Playing until ' + ((event.pageX - offset.left)/pps).toFixed(2) + ' seconds</div>');

          	     $('#replayWindow').show(500);
		     $('#modifyCommentForm').hide();
		     $('#marker').show(500);
      		     $('#responseToSessionCommentID').val('');
		     $('#playingUntilTimeSpanHR').replaceWith('<span id=\'playingUntilTimeSpanHR\'>' + secToHR(((event.pageX - offset.left - 2)/pps). toFixed(2)) + ' </span>');
      		     $('#playingUntilTimeSpan').replaceWith('<span id=\'playingUntilTimeSpan\'>' + ((event.pageX - offset.left - 2)/pps). toFixed(2) + ' </span>');
      		     $('#replayWindow').css('left', offset.left);
      		     $('#replayWindow').css('top', repy);
      		     $('#responseToSessionCommentID').val('NEW');
      		     $('#sessionProblemID').val(problem_id);
      		     $('#replayToSessionTime').val(play_to_time);
      		     $('#time').val((((event.pageX - offset.left - 2)/pps).toFixed(2)));
      		     $('#commenter').val(theCommenter);

		     //remove all existing keys from the keyField:
		     var empty_key_list = split($('#keyField').tagify('serialize'));
		     $.each(empty_key_list, function(key, value) { myTextArea.tagify('remove'); });
      		     //$('#keyField').val('');

      		     $('#comment').val('');
                     $('#href_string').val(s_str_original);
                     $('#marker').css('left', event.pageX);
		     $('#marker').css('top', (offset.top - 48));
		     $('#whiteboardFrame').html('<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0\" width=\"506.5px\" height=\"400px\" id=\"NOTSESSION\" align=\"middle\"> 	<param name=\"allowScriptAccess\" value=\"sameDomain\" />	<param name=\"allowFullScreen\" value=\"false\" />	<param name=\"wmode\" value=\"transparent\" />	<param name=\"movie\" value=\"' + s_str + '\" />     <param name=\"quality\" value=\"high\" /><param name=\"bgcolor\" value=\"#ffffff\" />	<embed src=\"' + s_str + '\" quality=\"high\" bgcolor=\"#ffffff\" wmode=\"transparent\" width=\"506.5px\" height=\"400px\" name=\"NOTSESSION\" align=\"middle\" allowScriptAccess=\"sameDomain\" allowFullScreen=\"false\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" /> </object>');


		     }
                     });

                   }
                 });
               }
             })
           });
          //});
     ";

     }

     $ret .= "
//          $('#marker').replaceWith('<div id=\'marker\' style=\'position:absolute;left:' + event.pageX + 'px;top:' + (offset.top - 45) + 'px;background-color:#FF0000;width:1px;height:64px;\'></div>');
        //});

	//var hovering_over_img;

	$('img').mousemove(function(event) {
	  $('#tracer1').show();
	  $('#tracer1').css('left', event.pageX);
	  $('#tracer1').css('top', $(this).offset().top);
	  $('#tracer1').height(event.pageY - $(this).offset().top - 1);
	  $('#tracer2').show();
	  $('#tracer2').css('left', event.pageX);
	  $('#tracer2').css('top', event.pageY + 1);
	  $('#tracer2').height(64 - (event.pageY + 1 - $(this).offset().top));
	  
	  //hovering_over_img = $(this).parent().parent().find('.whiteboard');
	  //alert(hovering_over_img.offset().left);
	});
	//$('#tracer').click( function(e) { alert('hi'); alert(hovering_over_img.offset().top); hovering_over_img.mouseup(); alert('done'); } );
    });
    </script>
    <div id='replayWindow' style='position:absolute;left:0;top:0;background-color:#EEEEEE;width:509.5px;height:420px;'>
      <div id='whiteboardFrame' style='background-color:#EEEEEE;top:-40px;width:506.5px;height:400px;' allowTransparency='true'>
      </div>
      <div id='gatherData' style='position:absolute;left:509.5px;top:0px;width:300px;height:420px;background-color:#888888;'>
        <form id='CommentForm' method='post' action=''>
          <input type='hidden' name='responseToSessionCommentID' id='responseToSessionCommentID' value=''>
          <input type='hidden' name='sessionProblemID' id='sessionProblemID' value=''>
	  <input type='hidden' name='sessionCommentID' id='sessionCommentID' value=''>
          <input type='hidden' name='replayToSessionTime' id='replayToSessionTime' value=''>
          <input type='hidden' name='href_string' id='href_string' value=''>
          Keys:<textarea name='keyField' id='keyField' rows='3' cols='33'></textarea>
          <BR>Note:<textarea name='comment' id='comment' rows='12' cols='33'></textarea>
          <BR>Time: <input type='text' name='time' id='time' size='2' value=''>
          Commenter: <input type='text' name='commenter' id='commenter' size='4' value=''>
          <BR>
	  <button id='modifyCommentForm'>Modify Record</button>
          <button id='submitCommentForm'>Submit </button>
          <button id='closeCommentForm'>Cancel </button>
        </form>
      </div>
      <div id='closeReplayWindow' style='position:absolute;left:0px;top:400px;width:508px;height:20px;background-color:#BBBBBB;'>
        Click here to close student work. Playing until <span id='playingUntilTimeSpanHR'>thisMany</span> (<span id='playingUntilTimeSpan'>thisMany</span> seconds).
      </div>
    </div>

    <div id='marker' style='position:absolute;left:1px;top:1px;width:1px;height:64px;background-color:#FF0000;'></div>
    <div id='tracer1' style='position:absolute;left:1px;top:1px;width:1px;height:64px;background-color:#00FF00;'></div>
    <div id='tracer2' style='position:absolute;left:1px;top:1px;width:1px;height:64px;background-color:#00FF00;'></div>
    <div id='theProblem' style='position:absolute;left:1px;top:1px;width:1px;height:1px;background-color:#FF0000;'>
      <div id='closeTheProblem' style='position:absolute;left:1px;top:1px;width:1px;height:1px;background-color:#00FF00;'>
      </div>
    </div>
    <div id='theEntireProblem' style='position:absolute;left:1px;top:1px;width:1px;height:1px;background-color:#FF0000;'>
      <div id='closeTheEntireProblem' style='position:absolute;left:1px;top:1px;width:1px;height:1px;background-color:#00FF00;'>
      </div>
    </div>
    <div id='dialog-form' title='Add New Key'>
      <p>All fields are required.</p>
      <form>
      <fieldset>
        <label for='key'>Key: </label>
        <input type='text' name='key' id='key' class='text ui-widget-content ui-corner-all' />
        <BR><label for='desc'>Desc:</label>
        <textarea name='desc' id='desc' rows='4' cols='25' class='text ui-widget-content ui-corner-all' ></textarea>
      </fieldset>
      </form>
    </div>
  ";
  return $ret;
}

function data_map_legend() {
    $table = "<TABLE BORDER=1><TR><TH>Work Feature</TH><TH>Event</TH></TR>";
    $a = html2rgb("#6D1699");
    $table .= "<TR><TD BGCOLOR='#6D1699'>Short Purple Columns</TD><TD>Student drawing on session board.</TD></TR>";
    $a = html2rgb("#BC6BE6");
    $table .= "<TR><TD BGCOLOR='#BC6BE6'>Short Light Purple</TD><TD>Student drawing on session board in second color</TD></TR>";
    $a = html2rgb("#4E2D60");
    $table .= "<TR><TD BGCOLOR='#4E2D60'>Medium Grey Columns</TD><TD>Student typing on session board.</TD></TR>";
    $table .= "<TR HEIGHT='20px'><TD COLSPAN=2></TD></TR>";
    $table .= "<TR><TD><B>Erasing Feature</B></TD><TD></TD></TR>";

    $a = html2rgb("#EE68B1");
    $table .= "<TR><TD BGCOLOR='#EE68B1'>Medium Pink Columns</TD><TD>Student erasing on session board.</TD></TR>";
    $a = html2rgb("#BA136E");
    $table .= "<TR><TD BGCOLOR='#BA136E'>Long Red Column</TD><TD>Student erased entire session board.</TD></TR>";

    $table .= "<TR HEIGHT='20px'><TD COLSPAN=2></TD></TR>";
    $table .= "<TR><TD><B>Graph / Image Feature</B><TD></TD></TR>";
    $a = html2rgb("#93D615");
    $table .= "<TR><TD BGCOLOR='#93D615'>Tall Green Column</TD><TD>Student put a graph on the session board.</TD></TR>";
    $a = html2rgb("#436503");
    $table .= "<TR><TD BGCOLOR='#436503'>Tall Evergreen Column</TD><TD>Student put an image on the session board.</TD></TR>";

    $table .= "<TR HEIGHT='20px'><TD COLSPAN=2></TD></TR>";
    $table .= "<TR><TD><B>Navigation Feature</B></TD><TD></TD></TR>";
    $a = html2rgb("#3F209E");
    $table .= "<TR><TD BGCOLOR='#3F209E'>Long Blue Columns</TD><TD>Student used slider to move session board</TD></TR>";

    $a = html2rgb("#E2E517");
    $table .= "<TR><TD BGCOLOR='#E6C317'>Tall Yellow Column</TD><TD>Student navigated away from this problem.</TD></TR>";
    $table .= "<TR><TD>Black Column</TD><TD>Student submitted answers.</TD></TR>";
    $table .= "<TR><TD>Black Circle Columns</TD><TD>Answer Submission.  Solid <-> Correct, Hollow <-> Incorrect</TD></TR>";
    $a = html2rgb("#580231");
    $table .= "<TR><TD BGCOLOR='#580231'>Brick Red Circle</TD><TD>Possible student submission, but more likely computer error in recording of student actions</TD></TR>";

    $table .= "<TR HEIGHT='20px'><TD COLSPAN=2></TD></TR>";
    $table .= "<TR><TD><B>Time Feature</B><TD></TD></TR>";
    $table .= "<TR><TD>Full Length Black Column</TD><TD>End of student's work</TD></TR>";
    $table .= "<TR><TD>Blue Time</TD><TD>Time student spend working on problem</TD></TR>";
    $table .= "</TABLE>";

    return $table;
}



function strstrb($h, $n) {
  return array_shift(explode($n, $h, 2));
  //sample:  echo strstrb('qwe, rty, uio', ',');
  //output:  qwe
}


function sortArrayByArray($array, $orderArray) {
  $ordered = array();
  foreach($orderArray as $key => $value) {
    if(array_key_exists($key, $array)) {
      $ordered[$key] = $array[$key];
      unset($array[$key]);
    }
  }
  return $ordered + $array;
}



//$weekly_quiz_sets = get_weekly_quiz_sets();
$weekly_quiz_sets = isset($_REQUEST['do_these_quiz_sets']) ? $_REQUEST['do_these_quiz_sets'] : array();
//print_r($weekly_quiz_sets);
$do_these_problems = isset($_REQUEST['do_these_problems']) ? $_REQUEST['do_these_problems'] : array();
//print_r($do_these_problems);
$map_width = $_REQUEST['map_width'];
$pixels_per_second = $_REQUEST['pixels_per_second'];
$courses = get_courses_make_con($ww_db_host, $ww_db_user, $ww_db_pass);

$list_these_students = array();
$use_list_of_students = false;

if (isset($_REQUEST['limit_to_these_students']) && ($_REQUEST['limit_to_these_students'] != "")) {
  $tmp_list = explode(",", $_REQUEST['limit_to_these_students']);
  foreach ($tmp_list as $k => $v) {
    $list_these_students[$v] = $v;
  }
  $use_list_of_students = true;
}

if ((isset($_REQUEST['courses']) && isset($_REQUEST['do_these_quiz_sets'])) ||
    (isset($_REQUEST['do_these_students']))) {
  //$list_these_students = array();
  //$use_list_of_students = false;
  $do_quiz_problems = array();

  if (isset($_REQUEST['do_these_students'])) {
    $print_user_name = $_REQUEST['print_user_name'] == "users";
    $do_these_students = $_REQUEST['do_these_students'];
    $use_list_of_students = true;
    
    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = "webwork";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    foreach ($do_these_students as $k => $v) {
      //list($tmp_course, $tmp_user_name, $tmp_source_file, $tmp_quiz_name) = explode("___", $v);
      $tmp_c_un_sf_qn_array = explode("___", $v);
      $tmp_course = array_shift($tmp_c_un_sf_qn_array);
      $tmp_user_name = array_shift($tmp_c_un_sf_qn_array);

      $do_these_courses[$tmp_course] = $tmp_course;
      $list_these_students[$tmp_user_name] = $tmp_user_name;

      while (count($tmp_c_un_sf_qn_array) > 1) {
        $tmp_source_file = array_shift($tmp_c_un_sf_qn_array);
        $tmp_quiz_name = array_shift($tmp_c_un_sf_qn_array);
        $weekly_quiz_sets[$tmp_quiz_name] = $tmp_quiz_name;
        //get the assignment problem for this student:
        $query = 'SELECT problem_id FROM `' . $tmp_course . '_problem` WHERE set_id="' . $tmp_quiz_name . '" AND source_file="' . $tmp_source_file . '"';
        $result = mysql_query($query, $con);
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $do_quiz_problems[$tmp_quiz_name][$row['problem_id']] = "Y";
        }
      }
    }  
    mysql_close($con);
  }

  if (isset($_REQUEST['courses']) && isset($_REQUEST['do_these_quiz_sets'])) {
    $do_these_courses = $_REQUEST['courses'];
    $print_user_name = $_REQUEST['print_user_name'] == "users";

    foreach ($do_these_problems as $k => $v) {
      list($q, $p) = explode("___", $v);
      $do_quiz_problems[$q][$p] = "Y";
    }
  }


  //The user has asked for this course, so let's give the results.
  $all_user_data = array();
  foreach ($do_these_courses as $tmpkk => $course) {
  //for ($c = 0; $c < count($do_these_courses); $c++) {
  //$course = $do_these_courses[$c];
    //first, get the problem ID for the Session data for each user and each quiz problem:
  
    //connect to the session database:
    $con = mysql_connect($db_host, $db_user, $db_pass);
    if (!$con) { 
      die('Could not connect: ' . mysql_error());
    }
  
    $db = "session";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);
 
    //$all_user_data = array();
    $user_id_for_user_name = array();
 
    //for ($q = 0; $q < count($weekly_quiz_sets); $q++) {
    foreach ($weekly_quiz_sets as $tmpkkk => $weekly_quiz_set) {
      $query = 'SELECT problem_id, user.user_id, user_name, ww_problem_number, ww_set_id FROM `wwStudentWorkForProblem` LEFT JOIN `course` ON course.course_id = wwStudentWorkForProblem.course_id LEFT JOIN `user` ON user.user_id = wwStudentWorkForProblem.user_id WHERE course_name="' . $course . '" AND ww_set_id="' . $weekly_quiz_set . '"';

      $result = mysql_query($query, $con);
      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_problem_id = $row['problem_id'];
        $this_user_id = $row['user_id'];
        $this_user_name = $row['user_name'];
        $this_ww_problem_number = $row['ww_problem_number'];
        $this_ww_set_id = $row['ww_set_id'];
        if (array_key_exists($this_ww_set_id, $do_quiz_problems) &&
            array_key_exists($this_ww_problem_number, $do_quiz_problems[$this_ww_set_id]) &&
            ($do_quiz_problems[$this_ww_set_id][$this_ww_problem_number] == "Y")) {
          $all_user_data['course'][$course]['user'][$this_user_name]['quiz'][$this_ww_set_id]['problem'][$this_ww_problem_number]['session_problem_ids'][$this_problem_id] = $this_problem_id;
          $user_id_for_user_name[$this_user_name] = $this_user_id;
        }
      }
    }
    //now, work with the webwork database.  Close the previous connection, and re-open it to webwork:
    mysql_close($con);

    $con = mysql_connect($ww_db_host, $ww_db_user, $ww_db_pass);
    if (!$con) {
      die('Could not connect: ' . mysql_error());
    }

    $db = "webwork";
    //select the database '$db'
    $result = mysql_select_db("$db", $con);

    //get all the users for this course:
    $query = 'SELECT user_id FROM `' . $course . '_user`';
    $result = mysql_query($query, $con);
    while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $this_user = $row['user_id'];
      $all_user_data['course'][$course]['user'][$this_user]['exists'] = 1;
    }
    //get all the quiz scores for the users in the course:
    foreach ($weekly_quiz_sets as $tmp_kkkk => $this_quiz) {
    //for ($q = 0; $q < count($weekly_quiz_sets); $q++) {
      //$this_quiz = $weekly_quiz_sets[$q];
      $query = 'SELECT status, attempted, user_id, problem_id, problem_seed, num_correct, num_incorrect FROM `' . $course . '_problem_user` WHERE set_id="' . $this_quiz .'"';
      $result = mysql_query($query, $con);

      while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $this_user = $row['user_id'];
        $this_attempt = $row['attempted'];
        $this_score = $row['status'];
        $this_problem_id = $row['problem_id'];
        $this_problem_seed = $row['problem_seed'];
        $this_num_correct = $row['num_correct'];
        $this_num_incorrect = $row['num_incorrect'];
        if (array_key_exists($this_quiz, $do_quiz_problems) &&
            array_key_exists($this_problem_id, $do_quiz_problems[$this_quiz]) &&
            ($do_quiz_problems[$this_quiz][$this_problem_id] == "Y")) {
          $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['attempted'] = $this_attempt;
          $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['final_score'] = $this_score;
          $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['problem_seed'] = $this_problem_seed;
          $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['num_correct'] = $this_num_correct;
          $all_user_data['course'][$course]['user'][$this_user]['quiz'][$this_quiz]['problem'][$this_problem_id]['num_incorrect'] = $this_num_incorrect;
        }
      }
    }   
  }

  //get the pca data for the users in this course:
  $list_of_pca_questions_to_use = isset($_REQUEST['sort_by_pca_questions']) ? $_REQUEST['sort_by_pca_questions'] : array();
  $sort_order_for_students = array();
  foreach ($list_of_pca_questions_to_use as $pca_problem => $v) {
    $query = 'SELECT user_id, problem_id, num_correct, num_incorrect FROM `' . $course . '_problem_user` WHERE set_id="finalQuiz_pca,v1" AND problem_id=' . $pca_problem . '';
    $result = mysql_query($query, $con);
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      $x_user_id = $row['user_id'];
      $x_num_correct = $row['num_correct'];
      $x_num_incorrect = $row['num_incorrect'];
      if (!(isset($sort_order_for_students[$user_id]))) { $sort_order_for_students[$user_id] = 0;  }
      $sort_order_for_students[$x_user_id] += $x_num_correct;
    }
  }

  mysql_close($con);

  $table = "";

  foreach ($all_user_data['course'] as $this_course => $this_course_data) {
    $table .= "<H3>$this_course</H3>";

    $table .= "<P>";
    $table .= "<TABLE BORDER=1>";
    $table_problem_header = "";
    $table_problem_header_complete = 0;
    $table .= "<TR><TH>Student</TH>XXXTABLEHEADERXXX</TR>";
    $u = 1;


    if (count($list_of_pca_questions_to_use) > 0) {
      arsort($sort_order_for_students);

      $this_course_data['user'] = sortArrayByArray($this_course_data['user'], $sort_order_for_students);
    }

    $prev_pca_score = -1;
 
    ksort($this_course_data['user']);

    foreach ($this_course_data['user'] as $this_user => $this_users_data) {
      if (!($use_list_of_students) || 
           (array_key_exists($this_user, $list_these_students) && ($list_these_students[$this_user] == $this_user))) {
        $tmp_user_name = $print_user_name ? $this_user : "user $u";
//if ($tmp_user_name == "dscholin08") {
        $u++;
        if ((count($list_of_pca_questions_to_use) > 0) && ($sort_order_for_students[$this_user] != $prev_pca_score)) {
          $prev_pca_score = $sort_order_for_students[$this_user];
          $table .= "<TR><TD BGCOLOR='#BBBBBB' COLSPAN=2>PCA SCORE: $prev_pca_score/" . count($list_of_pca_questions_to_use) . "</TD></TR>";
        }
        $table .= "<TR><TD VALIGN='TOP'>$tmp_user_name</TD>";
        $start_time = "";
        foreach ($this_users_data['quiz'] as $this_quiz => $this_problem_data) {
          $kk = 0;
          $start_time = "";
          foreach ($this_problem_data['problem'] as $this_problem => $this_problem_performance) {
            $kk++;
            if ($table_problem_header_complete < 1) { $table_problem_header .= "<TH>" . $this_quiz . ": " . $this_problem . "</TH>"; }
            //$this_problem is the problem number, i.e 1 or 3
            $table .= "<TD VALIGN='TOP'>";
            $table .= " (";
            $table .= "$u" . "_" . "$kk";
            $table .= " Score: " . $this_problem_performance['final_score'] . ".  Attempts:"; 
            if ($start_time == "") {
              $line_to_get = "|" . $this_user . "|" . $this_quiz . "|";
              $start_log_file = "/opt/webwork/courses/" . $this_course . "/logs/answer_log";
              $start_data = `grep '$line_to_get' $start_log_file`;
//if ($this_user == "gvelez09") { print "<PRE>$start_data</PRE>\n"; }
              $activity_before_problem = strstrb( $start_data, "$line_to_get" . "$this_problem" . "|");
              $activities_array = explode("\n", $activity_before_problem);
              $start_time = array_pop($activities_array);
              $start_time = preg_replace('/\[/', '', $start_time);
              $start_time = preg_replace('/\]/', '', $start_time);
              $start_time = preg_replace('/ /', '_', $start_time);
//              print "<P>---<P> we got start_time: $start_time <P>";
            }
//            print "start_time is now $start_time for ";
            $line_to_get = "|" . $this_user . "|" . $this_quiz . "|" . $this_problem . "|";
            $log_file = "/opt/webwork/courses/" . $this_course . "/logs/answer_log";
            $answer_data = `grep '$line_to_get' $log_file`;
            $answer_data_entries = explode("\n", $answer_data);
            $submission_string = "";
            for ($i = 0; $i < count($answer_data_entries); $i++) {
              $this_data_entry = explode("\t", $answer_data_entries[$i]);
              $tmp_info = explode("|", $this_data_entry[0]);
              $this_date = $tmp_info[0];
              $this_score = array_pop($tmp_info);
              //$this_score = array_pop(explode("|", $this_data_entry[1]));
              array_shift($this_data_entry);
              array_shift($this_data_entry);
              array_pop($this_data_entry);
              $this_answers = "Answer: " . implode("\nAnswer: " , $this_data_entry);
              $table .= "<a class='quizWork' href='http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_multiple_work_with_time.php?courses[]=$this_course&studentID[]=" . $this_user . "&do_these_quiz_sets[]=$this_quiz&no_html_for_students=1&problem_id=$this_problem' title='$this_date\n $this_answers'>$this_score</a> ";
              $this_submission_time = str_replace("[", "", $this_date);
              $this_submission_time = str_replace("]", "", $this_submission_time);
              $this_submission_time = str_replace(" ", "_", $this_submission_time);
              $submission_string .= $this_score . "_" . $this_submission_time;
            }
            //$table .= " " . $this_problem_performance['attempted'] . ",";
            //$table .= " " . $this_problem_performance['final_score'] . ", ";
            $table .= ".  Problem Seed: " . $this_problem_performance['problem_seed'] . " ";
            //$table .= " " . $this_problem_performance['num_correct'] . ", ";
            //$table .= " " . $this_problem_performance['num_incorrect'] ;
            $table .= ")";
            $max_session_id_number = -1;
            $count_of_session_ids = 0;
            $tmp_session_id_string = "";
            if (array_key_exists('session_problem_ids', $this_problem_performance)) {
              foreach ($this_problem_performance['session_problem_ids'] as $session_id_number) {
                $max_session_id_number = $max_session_id_number > $session_id_number ? $max_session_id_number : $session_id_number;
                $count_of_session_ids++;
                $tmp_session_id_string .= ' ' . $session_id_number;
                //print "$max_session_id_number <P>";
                //$table .= "<BR>$session_id_number: <img src='plot/map_work.php?session_id=$session_id_number" . "&attempts=" . $submission_string . "&map_width=$map_width&pixels_per_second=$pixels_per_second'></BR>";
              }
            }
            //$table .= "ST: $max_session_id_number $start_time. AT: $submission_string<BR>";

            //Add the link to the specific WeBWorK Problem:
            $table .= "($count_of_session_ids $max_session_id_number from session_ids: $tmp_session_id_string) <a class='quizProblem' href='http://" . $_SERVER['SERVER_NAME'] . "/webwork2/" . $this_course . "/" . $this_quiz . "/" . $this_problem . "/?effectiveUser=" . $this_user . "&displayMode=images&key=showInformation&user=awangberg' scrolling='no'>[WeBWorK Problem]</a>";

            //Add the link for the specific WeBWorK problem and work for each submission:
            $table .= " <a class='quizWork' href='student_multiple_work_with_time.php?do_these_quiz_sets[]=" . $this_quiz . "&courses[]=" . $this_course . "&studentID[]=" . $this_user . "&problem_id=" . $this_problem . "&no_html_for_students=1&Submit=Submit' TARGET='New'>[Work for Each Submission ]</a>";

	    //Add the link for generating the xml of the image
	    $table .= " <a class='graphXML' href='plot/map_work_with_wait_times.php?session_id=$max_session_id_number" . "&startTime=" . $start_time . "&attempts=" . $submission_string . "&map_width=$map_width&pixels_per_second=$pixels_per_second&quiz_name=" . $this_quiz . "&course=" . $this_course . "&student_id=" . (array_key_exists($this_user, $user_id_for_user_name) ? $user_id_for_user_name[$this_user] : "NO_WORK_FOR_USER") . "&key=" . $u . "_" . $kk . "&output=xml' target='xml'>picture xml</a>";




            if (array_key_exists('session_problem_ids', $this_problem_performance)) {
              foreach ($this_problem_performance['session_problem_ids'] as $session_id_number) {
                $max_session_id_number = $session_id_number;
              }
	      // add the purple play button
              $table .= "<BR><div><a class='playWhiteboard' href='http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&replayPaceForAdmin=1000&problem_id=$max_session_id_number&userDatabaseName=session&replayModeAsInstructor=yes&overRidePaceForAdmin=1000' target='Session'><img src='purple_play_button.png' width='40px' height='40px' style='position:relative;top:-10px;z-index:-1;'></img></a>";
	      // add the workmap timeline:
	      $table .= "<a class='whiteboard' href='http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&replayPaceForAdmin=1000&problem_id=$max_session_id_number&userDatabaseName=session&replayModeAsInstructor=yes&overRidePaceForAdmin=1000' target='Session'><img src='plot/map_work_with_wait_times.php?session_id=$max_session_id_number" . "&startTime=" . $start_time . "&attempts=" . $submission_string . "&map_width=$map_width&pixels_per_second=$pixels_per_second&quiz_name=" . $this_quiz . "&course=" . $this_course . "&student_id=" . (array_key_exists($this_user, $user_id_for_user_name) ? $user_id_for_user_name[$this_user] : "NO_WORK_FOR_USER") . "&key=" . $u . "_" . $kk . "'>";
              //work on this part here!!! (ADW)
              if (isset($_REQUEST['link_to_session']) && ($_REQUEST['link_to_session'] == 1)) {
                $time_xml = `php retrace_student_actions.php $this_course $user_id_for_user_name[$this_user] $this_quiz $max_session_id_number`;
                $t_xml = simplexml_load_string($time_xml);
                foreach ($t_xml->wwProblem as $wwProblem) {
                  if ($wwProblem->wwProblem_number == $this_problem) {
                    //$table .= "<div id='data'>";
                    $iii = 0;
                    foreach ($wwProblem->Replay as $replay) {
                      foreach ($replay->ReplayPart as $replayPart) {
                        $iii = $iii + 1;
                        $total_view_time = $replayPart->total_view_time;
                        $work_length = $replayPart->work_length;
                        $idle_time = $total_view_time - $work_length;
                        $table .= "<div id='data_$iii' class='timeData' data.total_view_time='$total_view_time' data.initial_idle_time='$idle_time' data.problem_id='$max_session_id_number'></div>";
                      }
                    }
                    //$table .= "</div";
                  }
                }
              }
              else {
                $table .= "<div id='data' class='getTimeData' data.callhref='retrace_student_actions.php?course=$this_course&student_id=" . 
                (array_key_exists($this_user, $user_id_for_user_name) ? $user_id_for_user_name[$this_user] : "") . 
                "&quiz=$this_quiz&session_id=$max_session_id_number' data.quizProblem='" . $this_problem . "' data.problem_id='" . $max_session_id_number . "'></div>";
              }
              //print "<div> here's the xml for $this_course " . $user_id_for_user_name[$this_user] . " $this_quiz $max_session_id_number : " . $time_xml . "</div>";
              $table .= "</a>";
              $table .= "</div></BR>";
            }
            $table .= "<BR>";
            $table .= "Click to manually add Annotation<BR>";
            $table .= "<div class='these_comments'>";
            $table .= "<TABLE border=1><TR bgcolor='#787878'><TH>Time</TH><TH>From</TH><TH>Key</TH><TH>Comment</TH></TR>";
           
            //connect to the session database:
            $con = mysql_connect($db_host, $db_user, $db_pass);
            if (!$con) {
              die('Could not connect: ' . mysql_error());
            }

            $db = "wwSession";
            //select the database '$db'
            $result = mysql_select_db("$db", $con);

            $query = 'SELECT sessionComments.id, sessionComments.session_problem_id, commenter, comment, replay_time_for_comment_ms, replay_time_for_comments_human_seconds, comment_date, response_to_comment_id, sessionCommentKeys.key_id, sessionCommentKeysPossible.shortkey, sessionCommentKeysPossible.key_description FROM `sessionComments` LEFT JOIN sessionCommentKeys ON sessionComments.session_problem_id=sessionCommentKeys.session_problem_id AND sessionComments.id = sessionCommentKeys.comment_id LEFT JOIN sessionCommentKeysPossible ON sessionCommentKeys.key_id=sessionCommentKeysPossible.key_id WHERE sessionComments.session_problem_id="' . $max_session_id_number . '" AND sessionCommentKeys.record_valid=TRUE ORDER BY replay_time_for_comments_human_seconds, response_to_comment_id';
            $result = mysql_query($query, $con);
            $prev_rows_are_this_id = -1;
            $end_of_row = "";
            while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
              if ($prev_rows_are_this_id == $row['id']) {
                $table .= ", " . $row['shortkey'] . "<font size=-2><sup><a href='' title='" . $row['key_description'] . "'>?</a></sup></font>";
                //$end_of_row = "</TD><TD>" . $row['comment'] . "</TD></TR>";
              }
              else {
                if ($prev_rows_are_this_id > -1) {
                  $table .= $end_of_row;
                }
                $prev_rows_are_this_id = $row['id'];
		$row_color = ($row['response_to_comment_id'] > -1 ) ? "#AAAAAA" : "#FFFFFF";
                $table .= "<TR bgcolor='$row_color'><TD><a class='replay_to_time_on_whiteboard' href='http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&replayPaceForAdmin=1000&problem_id=" . $max_session_id_number . "&userDatabaseName=session&replayModeAsInstructor=yes&overRidePaceForAdmin=1000' target='Session' sessionCommentID='" . $row['id'] . "' displayReplayTime='" . $row['replay_time_for_comments_human_seconds'] . "' replayToSessionTime='" . $row['replay_time_for_comment_ms'] . "' sessionProblemID='" . $max_session_id_number . "'>" . shortFormatTime($row['replay_time_for_comments_human_seconds']) . "</a></TD>";
                 $table .= "<TD>" . $row['commenter'] . "</TD>";
                 $table .= "<TD>" . $row['shortkey'] . "<font size=-2><sup><a href='' title='" . $row['key_description'] . "'>?</a></sup></font>";
               //$table .= "<TD>" . "key1 <font size=-2><a href='' title='Description of key'>?</a></font> " . "</TD>";
               //$table .= "<TD>" . $row['comment'] . "</TD></TR>";
		 $end_of_row = "</TD><TD>" . $row['comment'] . "</TD></TR>";
              }
            }
	    $table .= $end_of_row;
	    mysql_close($con);

            //$b = "<TR><TD><a class='replay_to_time_on_whiteboard' href='http://" . $_SERVER['SERVER_NAME'] . "/homework/session.swf?wwUserName=awangberg&replayPaceForAdmin=1000&problem_id=" . $max_session_id_number . "&userDatabaseName=session&replayModeAsInstructor=yes&overRidePaceForAdmin=1000' target='Session' sessionCommentID='1' displayReplayTime='140.00' replayToSessionTime='67766' sessionProblemID='" . $max_session_id_number . "'>140</a></TD><TD>AW</TD><TD>???</TD><TD>This is my comment!</TD></TR>";
	    //$table .= $b . $b . $b;
            $table .= "</TABLE>";
            $table .= "</div>";
            $table .= "</BR>";
            $table .= "</TD>";
          }
          //$table_problem_header_complete = 1;
        }
        $table_problem_header_complete = 1;
        $table .= "</TR>";
      }
//} //dscholin09 loop
    }
    $table .= "</TABLE>";
    $table .= "<P>";
    $table = preg_replace("/XXXTABLEHEADERXXX/", "$table_problem_header", $table);
  }
  print javascript();
  print $table;
  print "<H3>Data Map Legend</H3><P>";
  print data_map_legend();
  print "<BR>http://" . $_SERVER['SERVER_NAME'] . "/connecting/report/student_quiz_work.php?" . http_build_query(my_array_merge($_POST, $_GET)) . "</BR>";

}


else {

  print "<H3>Select the course</H3><BR>";

  //ask the user for the course and quizzes:

  print "<form method='post'>";
  for ($c = 0; $c < count($courses); $c++) {
    print "<input name='courses[]' type='checkbox' value='" . $courses[$c] . "'>" . $courses[$c] . "<BR>\n";
  }
  print "Limit to user name: <input name='limit_to_these_students' type='text' id='limit_to_these_students' value=''>\n";
  print "Print User names: <input name='print_user_name' type='text' id='print_user_name' value='print_user_name'>\n";

  print "<BR><H3>Select Quiz Set</H3><BR>";

  $quiz_sets_available = array();
  $quiz_sets_available[0] = "quiz_wk_1";
  $quiz_sets_available[1] = "quiz_wk_3";
  $quiz_sets_available[2] = "quiz_wk_5";
  $quiz_sets_available[3] = "quiz_wk_7";
  $quiz_sets_available[4] = "quiz_wk_10";
  $quiz_sets_available[5] = "quiz_wk_11";
  $quiz_sets_available[6] = "quiz_wk_13";

  $quiz_sets_available[7] = "quiz_wk_2";
  $quiz_sets_available[8] = "quiz_wk_3";
  $quiz_sets_available[9] = "quiz_wk_4";
  $quiz_sets_available[10] = "quiz_wk_5";
  $quiz_sets_available[11] = "quiz_wk_6";
  $quiz_sets_available[12] = "quiz_wk_7";
  $quiz_sets_available[13] = "quiz_wk_8";
  $quiz_sets_available[14] = "quiz_wk_9";
  $quiz_sets_available[15] = "quiz_wk_10";
  $quiz_sets_available[16] = "quiz_wk_11";



  $problems_for_quiz = array();
  $problems_for_quiz[0] = array(1, 2, 3, 4);
  $problems_for_quiz[1] = array(1, 2, 4);
  $problems_for_quiz[2] = array(1,2,3,4);
  $problems_for_quiz[3] = array(1,2,6,8,7);
  $problems_for_quiz[4] = array(1,2,3,4,5);
  $problems_for_quiz[5] = array(1,2,3,4,5);
  $problems_for_quiz[6] = array(1,2,4,6,8);

  $problems_for_quiz[7] = array(1,2,3,4,5);
  $problems_for_quiz[8] = array(1,2,3,4,5,6);
  $problems_for_quiz[9] = array(1,2,3,4,5);
  $problems_for_quiz[10] = array(1,2,3,4,5);
  $problems_for_quiz[11] = array(1,2,3,4,5);
  $problems_for_quiz[12] = array(1,2,3,4,5);
  $problems_for_quiz[14] = array(1,2,3,4,5);
  $problems_for_quiz[15] = array(1,2,3,4,5);
  $problems_for_quiz[16] = array(1,2,3,4,5);

  for ($c = 0; $c < count($quiz_sets_available); $c++) {
    print "<B><input name='do_these_quiz_sets[]' type='checkbox' value='" . $quiz_sets_available[$c] . "'>" . $quiz_sets_available[$c] . ":</B> ";
    foreach ($problems_for_quiz[$c] as $k) {
      print "<input name='do_these_problems[]' type='checkbox' value='" . $quiz_sets_available[$c] . "___" . $k . "'>Problem " . $k . " ";
    }
    print "<BR>\n";
  }
  print "<BR>\n";

  print "
<P><B>Fall 2010 Quizzes</B><BR>
Function Composition: Quiz Wk 1 (2, 3, 4), Quiz Wk 3 (), Quiz Wk 5 (2?), Quiz Wk 7 (6, 7), Quiz Wk 10 (2, 4, 5), Qui
z Wk 11 (1, 3?, 4?, 5), Quiz Wk 13 ()
<BR>Contextualized Problems: Quiz Wk 1 (4), Quiz Wk 3 (2), Quiz Wk 5 (4?), Quiz Wk 7 (), Quiz Wk 10 (2?, 3?), Quiz Wk 11
 (3, 4?), Quiz Wk 13 (1, 2)
<BR>Graphing Problems: Quiz Wk 1 (2, 3), Quiz Wk 3 (1, 2, 4), Quiz Wk 5 (1?, 4), Quiz Wk 7 (1), Quiz Wk 10 (1, 3), Quiz
Wk 11 (2?, 3?), Quiz Wk 13 (1, 2)
<BR>Understanding Beasts: Quiz Wk 1(2? 3? 4?), Quiz Wk 3 (1, 2), Quiz Wk 5 (4?), Quiz Wk 7 (1), Quiz Wk 10 (3, 4, 5), Qu
iz Wk 11 (2?, 3, 4), Quiz Wk 13 (1?, 2?)";

  print "<BR>";
  print "<H3>Options</H3><BR>";
  print "Map Width: <input name='map_width' type='text' id='map_width' value='800'><BR>\n";
  print "Pixels per Second: <input name='pixels_per_second' type='text' id='pixels_per_second' value='1'><BR>\n";
  print "Include session time info: <input name='link_to_session' type='checkbox' id='link_to_session' value='1'><BR>";
  print "Sort By PCA: ";
  for ($c = 1; $c <= 25; $c++) {
    print "<input name='sort_by_pca_questions[]' type='checkbox' value='" . $c . "'>" . $c . " ";
  }
  print "<BR>pca function composition (taxonomy #6):  questions 4, 5, 12, 16, 17, 20, 23<BR>";
  print "<BR>pca contextual function rep (taxonomy #15): questions 3, 4, 7, 8, 10, 11, 15, 17, 18, 20, 22<BR>";
  print "<BR>";
  print "<input name='send' type='submit' id='send' value='Get Data!'>\n";
  print "</form>";
}
