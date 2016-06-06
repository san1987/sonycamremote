<?
$refresh_preview_timer=$tpl["refresh_preview_timer"];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<style>
.controls{ float:left}
.preview{ float:left; margin-left: 20px;}
.button{ padding-left: 10px; padding-right: 10px; color: white; background: green; margin:10px; display: block; float: left; text-decoration: none; font-size: 30pt; font-weight: bold}


.button.zoom{ background: rgb(0, 50, 0); margin:1px; }
.button.sel{ background: rgb(100,200,0);}
.button.blue{ background: blue}
.button.red{ background: red}
.button:hover{ background: gray; }
.button.zoom_values{ margin-top: 40px;}
.clear{clear: both}
.info{ float:left}
.postview{ float:left}
.error{color: red; font-weight: bold}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<script src="jquery.js" type="text/javascript"></script>





<title>Sony Camera Remote API viewer</title>

</head>
<body>
<div class=controls>
Show preview <input type=checkbox id=is_show_preview  checked><br>
Camera IP: <input type=text value='<?=$tpl["ip"]?>' size=16 id=camera_ip>
 Port: <input type=text value='<?=$tpl["port"]?>'  size=4 id=camera_port>
Preview Port: <input type=text value='<?=$tpl["preview_port"]?>'  size=4 id=camera_preview_port>
<input type=button onclick='set_connection()' value=Set>
<br>
Preview refresh: <input type=text id=refresh_preview_timer size=3 value='<?=$refresh_preview_timer?>'>
fps (from 0.01 to 20)
<input type=button onclick='refresh_preview_timer()' value=Set><br>
Preview digital zoom:<br>
<a href='javascript:digital_zoom_in(true)' class=button>+</a> <a href='javascript:digital_zoom_in(false)' class=button>-</a>

<div class=clear></div>
Optical zoom:<br>
<a href='javascript:optical_zoom_in(false)' class='button zoom'>-</a>
<?
for ($i=0; $i<=10; $i++) echo "<a href='javascript:optical_zoom($i)' class='button zoom zoom_values zoom".$i."'>$i</a>";
?>
<a href='javascript:optical_zoom_in(true)' class='button zoom'>+</a>

<div class='clear req_status'></div>       <br><br>

<input name="Name" type="radio" value="0" id='is_still' class=shootMode onchange='toggle_still()'> Still
<input name="Name" type="radio" value="1" id='is_movie' class=shootMode onchange='toggle_still()' > Movie


<div class=clear></div>

<a href='javascript:shoot();' class='button blue'>Take picture</a>
<a href='javascript:toggleRecord();' class='button red record'>Start Rec</a>



<div class=clear></div>

Update info<input type=checkbox id=is_update_info  checked><br>
<div class=info></div>
<div class=postview></div>


</div>

<div class=preview>
<div class=error></div>

<img src=''>
</div>

<script>
	function digital_zoom_in(_in){          preview_width+=_in?100:-100;
          if (preview_width<100) preview_width=100;
          $(".preview img").width(preview_width);	}
	var rpt;
	var counter=0;
</script><script>
	function set_connection(){
		$('.req_status').html('request..');

		 $.getJSON( ".?ajax=1&a=set_connection"+
		 "&camera_ip="+$("#camera_ip").val()+
		 "&camera_port="+$("#camera_port").val()+
		 "&camera_preview_port="+$("#camera_preview_port").val()
		 	, function( data ) {
									$('.req_status').html('');

		});
	}
</script><script>
	function show_preview(){
		counter+=50;
		if ($("#is_show_preview").prop("checked"))
			$(".preview img").show();
		else
			$(".preview img").hide();

		if (counter>rpt)	{
			counter=0;
			$(".preview img").attr("src", ".?ajax=1&a=pic&t="+Date.now());
		}
	}
</script><script>
	var toggle_still_started=0;
	function toggle_still(){
		toggle_still_started=10; // disable toggle by received state for 10 times
		$('.req_status').html('request..');
		$.getJSON( ".?ajax=1&a=shoot_mode&still="+($("#is_still").prop("checked")?"1":"0"), function( data ) {			$('.req_status').html('');
			if(0)  if (data["recording"])
	              $(".button.record").html("Stop");
	          else
	          	  $(".button.record").html("Start Rec");
		});	}
</script><script>
	function toggleRecord(){
		$('.req_status').html('request..');
		$.getJSON( ".?ajax=1&a=toggle_record", function( data ) {
              $('.req_status').html('');
		});	}

</script><script>
	function shoot(){
		$('.req_status').html('request..');
		$.getJSON( ".?ajax=1&a=shoot", function( data ) {
			   $('.req_status').html('');
               $(".postview").html("<a href='"+data+"'><img width=300 src='"+data+"'></a>");
		});	}
</script><script>
	function optical_zoom_in(_in){
		$('.req_status').html('request..');		$.getJSON( ".?ajax=1&a=zoom_in&in="+(_in?1:0), function( data ) {			  $('.req_status').html('');
              show_zoom(data["currentZoom"]);
		});	}

	var current_zoom=0;
</script><script>
	function optical_zoom(zoom){
		  $('.req_status').html('request..');		  $.getJSON( ".?ajax=1&a=zoom_set&set="+(zoom*10-current_zoom), function( data ) {		  	  $('.req_status').html('');
		  	  if (data["currentZoom"])
	              show_zoom(data["currentZoom"]);
		  });	}
</script><script>
	function show_zoom(zoom){
			  zoom=parseInt(zoom);
			  current_zoom=zoom;
			  zoom=Math.floor(zoom/10);
			  $(".button.zoom").removeClass("sel");
              $(".button.zoom"+zoom).addClass("sel");	}
</script><script>
	function get_event(){

		$.getJSON( ".?ajax=1&a=last_error", function( data ) {
                    if(data)
                     if (data[0])
						$( ".error").html("Last error #"+data[0]+":"+data[1]);
		});
		$.getJSON( ".?ajax=1&a=get_event", function( data ) {
                  if (!$("#is_update_info").prop("checked"))  return;

				 if(data["zoomInformation"])
	                  show_zoom(data["zoomInformation"]["zoomPosition"]);

				  if(toggle_still_started>0)
				  	toggle_still_started--;
				  else{				  	 $("#is_still").prop(
                  		"checked",  (data["shootMode"]["currentShootMode"]=="still")
	                  );

    	              $("#is_movie").prop(
                  		"checked",  (data["shootMode"]["currentShootMode"]=="movie")
        	          );
        	      }

                if(data["cameraStatus"]){
					if (data["cameraStatus"]["cameraStatus"]=="IDLE")
		          	  $(".button.record").html("Start Rec");
	     	  	    else if (data["cameraStatus"]["cameraStatus"]=="MovieRecording")
		          	  $(".button.record").html("Stop");
		         }



				  $(".info").html("<table>"+
				  					(data["cameraStatus"]?"<tr><td align=right>cameraStatus</td><td>  "+data["cameraStatus"]["cameraStatus"]+"</td></tr>":"")+
				  					(data["zoomInformation"]?"<tr><td align=right>zoomPosition</td><td>  "+data["zoomInformation"]["zoomPosition"]+"</td></tr> ":"")+
				  					(data["liveviewStatus"]?"<tr><td align=right>liveviewStatus</td><td>  "+data["liveviewStatus"]["liveviewStatus"]+"</td></tr> ":"")+
				  					(data["liveviewOrientation"]?"<tr><td align=right>liveviewOrientation</td><td>  "+data["liveviewOrientation"]["liveviewOrientation"]+" </td></tr>":"")+
				  					(data["storageInformation"]?"<tr><td align=right>storageInformation</td><td>  "+data["storageInformation"]["storageID"]+"</td></tr> ":"")+
				  					(data["beepMode"]?"<tr><td align=right>beepMode</td><td>  "+data["beepMode"]["currentBeepMode"]+"</td></tr> ":"")+
				  					(data["stillSize"]?"<tr><td align=right>stillSize</td><td>  "+data["stillSize"]["currentAspect"]+" "+data["stillSize"]["currentSize"]+"</td></tr> ":"")+
				  					(data["exposureMode"]?"<tr><td align=right>exposureMode</td><td>  "+data["exposureMode"]["currentExposureMode"]+" </td></tr>":"")+

				  					(data["postviewImageSize"]?"<tr><td align=right>postviewImageSize</td><td>  "+data["postviewImageSize"]["currentPostviewImageSize"]+" </td></tr>":"")+
				  					(data["selfTimer"]?"<tr><td align=right>selfTimer</td><td>  "+data["selfTimer"]["currentSelfTimer"]+"</td></tr> ":"")+
				  					(data["shootMode"]?"<tr><td align=right>shootMode</td><td>  "+data["shootMode"]["currentShootMode"]:"")
                                    +"</td></tr></table> last update: "+Date.now()


				  );
		});	}
 </script><script>
	setInterval(show_preview, 50);
	setInterval(get_event, 500);


	function refresh_preview_timer(){
		_rpt=parseInt($('#refresh_preview_timer').val());
		if (_rpt==0) _rpt=5;		rpt=1000/(_rpt);	}
</script><script>
	var preview_width=600;
    digital_zoom_in(true);
	refresh_preview_timer();
</script>
