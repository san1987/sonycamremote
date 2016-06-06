<?


class Camera{	public $ip="10.0.0.1";
	public $port=10000;
	public $preview_port=601521;
	public $is_log=false;
	public $log="";
	public $id=0;
	public $last_error;
	public $last_error_no;
	public $last_error_text;
	public $timeout=5;


    function setDeafultConnection(){
    }

    function getPicture(){    								$fp = @fsockopen($this->ip, $this->preview_port, $errno, $errstr, $this->timeout);
									if (!$fp) {										$this->last_error_text=$errstr;
										$this->last_error_no=$errno;
										$this->last_error= $errno." ".$errstr;
									    if ($this->is_log) $this->log.="$this->last_error<br />\n";

									    return false;
									} else {
									    $out = "GET /liveview.JPG?%211234%21http%2dget%3a%2a%3aimage%2fjpeg%3a%2a%21%21%21%21%21 HTTP/1.1\r\n";
									    $out .= "Host: $this->ip\r\n";
									    $out .= "Connection: Close\r\n\r\n";
									    fwrite($fp, $out);

									    /*
									    searching in stream payload signature  chr(0x24).chr(0x35).chr(0x68).chr( 0x79)
									    than reading JPEG file size in  3 next bytes

									    2 states:
									    	1) serarching payload sign
										    2) reading JPEG

									    */


														    $payload_sign=chr(0x24).chr(0x35).chr(0x68).chr( 0x79);
														    $payload_size=128;
														    $jpeg_size=0; $contents1=false; $contents2=false;  $contents="";
														    $block_size=8192   ;
														    while (!feof($fp)  ) {
														    	if ($jpeg_size){
														    		if(($size=strlen($contents2))<($need=($jpeg_size+$payload_size))){
														    			$contents2.=($r=fread($fp, $need-$size));
														    		}else{
														    			$contents2=substr($contents2, $payload_size+8);  //Skip payload header
														    			break;
														    		}
														    	}else{
															    	$contents = $last_r.($r=fread($fp, $block_size));
															    	$contents2=strstr($contents, $payload_sign);
															    	if ($contents2){															    		$contents2.=($r=fread($fp, $block_size));
															    		$jpeg_size=substr($contents2, 4, 3);
															    		$jpeg_size=ord($jpeg_size{0})*256*256+ord($jpeg_size{1})*256+ord($jpeg_size{2})*256;															    	}
															    	$last_r=$r;
														    	}
															}
									    fclose($fp);
									    return $contents2;
									}
    }

	function apiCall($method, $params=array(), $version="1.0"){
 					$this->id++;
					$api_url="http://$this->ip:$this->port/sony/camera";
					$method=trim($method);
					$req=array(
								"method"=> $method,
								"params"=>  $params,
								"id"=> $this->id,
								"version"=> $version
							 );
					$post=json_encode($req);
				    $context = stream_context_create(array(
				         'http' => array(
				             'method' =>  "POST",
				             'timeout' => $this->timeout,
				             'content' => $post

				         )
				     ));

					$s=@file_get_contents($api_url, $use_include_path = false,  $context );


					$json_result=json_decode($s, true);

					$this->last_error_no=$json_result["error"][0];
					$this->last_error_text=$json_result["error"][1];
					$this->last_error=$json_result["error"];



					if ($this->is_log) $this->log.= "<table border=1 style='min-width: 50%'>
														<tr><td style='width: 100px'>ID</td><td>$this->id</td></tr>
														<tr style='background: yellow'><td style='width: 100px'>method</td><td>$method</td></tr>
														<tr><td style='width: 100px'>params</td><td><pre>".print_r($req, true)."</td></tr>
														<tr><td style='width: 100px'>response</td><td>$s</td></tr>
														<tr><td style='width: 100px'>response</td><td><pre>".print_r($json_result, true)."</td></tr>

														</table>";



				    return $json_result;
	}
}