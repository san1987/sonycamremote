<?
ini_set('default_socket_timeout', 5);

require_once "Camera.php";

class  MyCamera extends Camera{	function apiCall($method, $params=array(), $version="1.0")	{		//return  false;		$f=parent::apiCall($method, $params , $version);
		if ($this->last_error) {			$_SESSION["last_error"]=$this->last_error;
			$_SESSION["last_error"][1].=":".$method." ".date("H:i:s");

		}
		return  $f;	}
	function zoomPos(){			$f=self::apiCall("getEvent", array(false));
			$f=$f["result"];

			return ($f[2]["zoomPosition"]);	}

	function cameraStatus(){
			$f=self::apiCall("getEvent", array(false));
			$f=$f["result"];

			return ($f[1]["cameraStatus"]);
	}
}

$camera=new MyCamera();
$camera->is_log=$debug;
if ($a!="pic")
	session_start();

$connection_settings=@file_get_contents("connection_settings");
$connection_settings=@unserialize($connection_settings);
if ($connection_settings){			 $camera->ip=$connection_settings["camera_ip"];
			 $camera->port=$connection_settings["camera_port"];
			 $camera->preview_port=$connection_settings["camera_preview_port"];}

if ($ajax){
	$res=array();	switch($a){

		case "pic":
			header( 'Content-Type: image/jpeg' );
			$f=$camera->getPicture();
			if ($f==false){					$im = imagecreatetruecolor(300, 100);
					$black = imagecolorallocate($im, 0x00, 0x00, 0x00);
					$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
					imagefilledrectangle($im, 0, 0, 299, 99, $white);
					imagettftext($im,10,0,20,20,$black,"arial.ttf", "Error #".$camera->last_error_no);
                    imagejpeg($im);
					imagedestroy($im);			}
			else
				echo $f;
			die;
		break;
		case "set_connection":
             $connection_settings["camera_ip"]=$camera_ip;
             $connection_settings["camera_port"]=$camera_port;
             $connection_settings["camera_preview_port"]=$camera_preview_port;

             file_put_contents("connection_settings", serialize($connection_settings));
		break;
		case "shoot_mode":
			if ($still)
				$f=$camera->apiCall("setShootMode", array("still"));
			else
				$f=$camera->apiCall("setShootMode", array("movie"));
		break;
		case "toggle_record":
			  $status=$camera->cameraStatus(); if ($debug) echo "<h1>$status</h1>";
			  if ($status=="IDLE"){

			  	$f=$camera->apiCall("startMovieRec");

			  }else{
			    if ($status=="MovieRecording"){
				    $f=$camera->apiCall("stopMovieRec");

				}			  }


		break;
		case "shoot":
      		  $f=$camera->apiCall("actTakePicture");
      		  $res=$f["result"][0][0];
		break;
		case "zoom_set":
			$f=$camera->apiCall("actZoom", array($set>0?"in":"out", "start"));
			usleep(abs($set)/25*1000000);
			$f=$camera->apiCall("actZoom", array($set>0?"in":"out", "stop"));
			$res["currentZoom"]=$camera->zoomPos();

		break;
		case "zoom_in":
			$f=$camera->apiCall("actZoom", array($in?"in":"out", "1shot"));
			//usleep (1000);
			$res["currentZoom"]=$camera->zoomPos();
		break;
		case "last_error":

			$res=$_SESSION["last_error"];
		break;
		case "get_event":
			$f=$camera->apiCall("getEvent", array(false));

			$f=$f["result"];
			if($f)
			foreach ($f as $s)  {
				if ($s["type"])					$res[$s["type"]]=$s;
				else
					if (is_array($s))
					foreach ($s as $ss)
						if ($ss["type"])
							$res[$ss["type"]]=$ss;
			}
		break;	}



	if (!$debug) header( 'Content-Type: application/json' );  else  echo $camera->log;
	echo json_encode($res);
	die;}

$tpl["refresh_preview_timer"]=10;
$tpl["ip"]=$camera->ip;
$tpl["port"]=$camera->port;
$tpl["preview_port"]=$camera->preview_port;


include "template.php";

?>

<div class=clear></div><?



//for debug:
if(0){
	$f=$camera->apiCall("actZoom", array("out", "1shot"));
	print_r($f);
}

if(0){
	$f=$camera->apiCall("getAvailableApiList" );
	print_r($f);

	$f=$camera->apiCall("getEvent", array(false));
?><pre><?
	print_r($f["result"]);
}


if(0){
	$f=$camera->apiCall("getAvailableApiList");
	?><pre><?
		print_r($f["result"]);

	echo $camera->log;

}