<?php
/*************************************************************************

    This file is part of Heaver-it.

    Heaver-it is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Heaver-it is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Heaver-it. If not, see <http://www.gnu.org/licenses/>.
    
    Author : moins52 (moins52@yahoo.fr)
    Project home: http://www.k-metamodule.com/heaver-it/
    
*************************************************************************/

// Save action
if (true == isset($_GET['s'])) {
  // Open file
  $file = fopen($_POST['file'], 'w');

  // Write in file
  fputs($file, $_POST['data']);

  // Close file
  fclose($file);
}
// Default action
else if (false == isset($_GET['f']) || true == empty($_GET['f'])) {
  echo "Usage: heaver-it/?f=filename";
}
else {
  // File data
  $file = $_GET['f'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  
  <title>Heaver-it</title>
  
  <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.9.custom.css" rel="stylesheet" />	
  <style>
  body {
    font-family:Trebuchet MS,Tahoma,Verdana,Arial,sans-serif;
    color:#333333;
  }
  
  #todo {
    left:15%;
  }
  
  #running {
    left:45%;
  }
  
  #done {
    left:75%;
  }
  
  .title {
    position:fixed;
    top:10px;
    position: absolute;
    color: #1C94C4;
    font-size:16px;
    font-weight:bold;
  }
  
  .task { 
    width:146px;
    height:146px;
    padding:2px;
    margin:0px;
    overflow:auto;
    position: absolute;
    background: #FBFB4D;
    border: 1px solid #C6C759;
    font-size:13px;
  }
  
  .hidden {
    visibility: hidden;
  }
  
  textarea { 
    width:150px;
    height:150px;
    background:#FBFB4D;
    margin:0px;
    border:0px;
    color:#333333;
    font-size:13px;
    padding:2px;
    font-family:Trebuchet MS,Tahoma,Verdana,Arial,sans-serif;
  }

  #wall {
    background:#FFF;
    width:100%;
    height:100%;
    position:fixed;
    top: 0;
    left: 0;
    z-index: -1;
  }
  
  #toolbar {
    height:42px;
    position:fixed;
    top: 100%;
    left: 0;
    margin:-42px 0 0 -128px;
    width:100%;
  }
  
  #save {
    padding-left:128px;
    height: 42px;
  }
  
  #feedback {
    padding:0.5em;
    color:#333333;    
  }
    
  #trash {
   background:#FFF;
   width:128px;
   height:128px;
   position:fixed;
   top: 100%;
   left: 100%;
   margin:-128px 0 0 -128px;
  }
     
  #trashContent {
    background:#DADADA;
    width:380px;
    height:128px;
    position:fixed;
    top:100%;
    left:100%;
    margin:-128px 0 0 -508px;
    display:none;
    overflow-y:auto;
    font-size:13px;
  }
  
  #trashContent ul {
    list-style: none;
    padding: 3px;
    margin:0px;
  }
  
  #trashContent li:nth-child(odd) {
    background: #F5F5F5;
  }
  </style>
  
  <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui-1.8.9.custom.min.js"></script>
	<script type="text/javascript" src="js/jquery.editable-1.3.3.js"></script>
	<script type="text/javascript" src="js/jquery.taskify.js"></script>
  <script> 
  // ignicial ?
  var originLeft, originTop;
  var dataLoaded = false;
    
  $(function() {
    // The trash
    var $trash = $( "#trash" );
        
    // Creates a new task when double-click on the wall
    $( "#wall" ).dblclick(function(e) {
      putTaskOnBoard("My new task",e.pageX,e.pageY,$(".task").length);
      $( ".actionlistener" ).trigger("modification");
    });
    
    $( "#save" ).bind("modification", function () { $( "#save span" ).html("Save Now"); });
    
    // Makes save button
    $( "#save" ).button()
                .click(function() {  
      // Build data
      var data = '[';
      var allTasks = $(".task");
      for(var i=0 ; i < allTasks.length ; i++)
      {
        var id = '#' + allTasks[i].id;
        
        // Excludes hidden tasks
        if (false == $(id).hasClass("hidden")) {
        
          // Adds separator if it's not the first task
          if (data != '[') {
            data += ',';        
          }
          
          data += '{"content":"' + $(id).html() 
                + '", "left":' + $(id).css("left").substring(0,$(id).css("left").length-2) 
                + ', "top":' + $(id).css("top").substring(0,$(id).css("top").length-2) 
                + ', "zIndex":' + $(id).css("z-index") + '}';
        }
      }
      data += ']';
      
      // Builds the json object
      json = { 
        "data": data,
        "file": "<?php echo $file; ?>.json"
      };
      
      // Send AJAX request to save data
      $.ajax({
        url: '?s=1',
        type: "POST",
        dataType: json,
        data: json,
        success : function(data){
          $( "#save span" ).html("Saved");
        },
        error : function(data){
          $("#save span").html("Error");
          setTimeout(function() {
            $( "#save span" ).html("Save Now");
          }, 1000 );
        }
      });
      
      // We don't want to follow the link
      return false;
    });
	
    // Loads tasks from JSON file
    $.getJSON('<?php echo $file; ?>.json', function(data) {
      if (null != data)
      {
        $.each(data, function(key, val) {
          putTaskOnBoard(val.content,val.left,val.top,val.zIndex);
        });
      }

      // Puts a task on board if none was loaded
      if ($(".task").length == 0) putTaskOnBoard("Doubleclick where you want to create a new note.",200,200,0);
      
      dataLoaded = true;
    });  
    
    // Manage Ajax errors
    $('body').ajaxError(function(e, xhr, settings, exception) {
      // Error while loading data
      if (false == dataLoaded)
      {
        putTaskOnBoard("Doubleclick where you want to create a new note.",200,200,0);
        
        dataLoaded = true;
      }
      // Error while saving data
      else {
        // Nothing
      }
    });
        
    // Makes tasks clickable
    $(".task").taskify();
        
    // Makes the trash a button droppable and clickable
    $trash.droppable({
			drop: function( event, ui ) {
				deleteTask( ui.draggable );
			}
    });
    
    // Makes trash a button
    $( "#trashButton" ).button()
                        .click(function() {
        $( "#trashContent" ).toggle( "blind", {}, 500 );
        
      // We don't want to follow the link
      return false;
    });
    
    /**
     * Recycle given task
     * @param $item the task
     */
    function recycleTask( $item ) {
    
      var id = $item.attr('id').substring(1,$item.attr('id').length);
      
      // Puts task on top
      $(".task").trigger("putTaskOnTop", [$( "#" + id).css('zIndex')]);
      
			$( "#" + id).removeClass("hidden", 100);
      $item.remove();
      
      // Changes trash icon if now empty
      if($( "li", $trash ).length == 0)
      {
        $( "#trashButton" )[0].src = "img/Recycle-Bin-Empty.png";
      }
      
      $( ".actionlistener" ).trigger("modification");
		}
    
    /**
     * Delete given task
     * @param $item the task
     */     
    function deleteTask( $item ) {
      // Hides the task
      $item.addClass("hidden", 100, function() {
      
        var $list = $( "ul", "#trashContent" );
        
        $( "#trashButton" )[0].src = "img/Recycle-Bin-Full.png";
        
        var text = $item.html().substring(0,30);
        if ($item.html().length > 30) {
          text += "[...]";
        }
        
				$( "<li id='d" +$item.attr('id')+ "'>" + text + "</li>" )
          .click(function() {    
            recycleTask($( this ));   
          }).appendTo( $list );
        
        // Keep the task's last visible postion
        $item.css({
          left: originLeft,
          top: originTop
        });
      });
      
      $( ".actionlistener" ).trigger("modification");
		}
  });
    
  /**
   * Put given task in taskset
   */
  function putTaskOnBoard(content,left,top,zIndex) {
    // Puts a new task in the taskset
    newId = $(".task").length;
    $("#taskset").append("<div id='t"+newId+"' class='task'>"+content+"</div>");
    
    // Makes the task clickable
    $("#t"+newId).taskify();
    
    // Places the task in the taskset
    $("#t"+newId).css({
      position: "absolute",
      left: left,
      top: top,
      zIndex: zIndex
    });
    
    // Keeps the toolbar higher than any task
    $("#toolbar").css({ zIndex : newId + 1});
  }
    
  /**
   * Trace function for debug
   * @param text to trace
   */
  function trace(text)
  {
    if (typeof console !== 'undefined') {
        console.log(text);   
    }
  }
  </script>
</head>
<body>
<div id="todo" class="title">ToDo</div>
<div id="running" class="title">Running</div>
<div id="done" class="title">Done</div>
<div id="wall"></div>
<div id="taskset"></div>
<div id="toolbar"><a href="#" id="save" class="actionlistener">Save</a></div>
<div id="trashContent"><ul /></div>
<div id="trash"><img src="img/Recycle-Bin-Empty.png" id="trashButton" width="128" height="128">Trash is empty</a></div>
</body>
</html>
<?php
}