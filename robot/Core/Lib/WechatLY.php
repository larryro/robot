<?php
class WechatLY
{
    /**
     * 微信公众账号Id
     * @var string
     */	
	private static $appId = 'wxbc4f41fe5abf92d1';

	/**
     * 微信公众账号秘钥
     * @var string
     */	
	private static $appSecret = '5fb7ac7074323bfe018b60379a215d23';

	/**
     * 微信文本发送接口
     * @var string
     */	
	private static $textSendUrl= "https://api.weixin.qq.com/cgi-bin/message/custom/send";

	/**
     * 微信accessToken获取接口
     * @var string
     */	
	private static $accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential";

	/**
     * 微信文件上传接口
     * @var string
     */	
	private static $fileUploadUrl = 'http://file.api.weixin.qq.com/cgi-bin/media/upload';

	/**
     * 微信音频，图片发送接口
     * @var string
     */	
	private static $mediaSendUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send';

	/**
     * memcacheHost
     * @var string
     */	
	private static $memHost = 'localhost';

	/**
     * memcache缓存端口号
     * @var int
     */	
	private static $memPort = 11211;

    /**
     * 主动发送语音或者图片消息
     * @param string $fromUsername,$media_id,$type
     * @return Result
     */
	private static function sendMediaMsg($fromUsername,$url,$type='voice'){
		$access_token = self::getAccessToken(self::$appId,self::$appSecret);
		
		//获取媒体ID 
		$media_id = self::uploadMedia($access_token,$url,$type);
		
		$url=self::$mediaSendUrl.'?access_token='.$access_token;
		$postData='{
			"touser":"'.$fromUsername.'",
			"msgtype":"'.$type.'",
			"'.$type.'":{"media_id":"'.$media_id.'"   }
			}';
		echo date('Y-m-d H:i:s',time()).' sendMediaMsg '.$postData."\n";
		$result= self::http_request($url,$postData);
		$resultObj = json_decode($result);
		echo date('Y-m-d H:i:s',time()).' sendMediaMsg '.$result."\n";
		if ($resultObj->errcode == 0) {
			return true;
		}else{
			return false;
		}
	}

	/**
     * 消息发送接口，对外暴露
     * @param string $fromUsername,$url,$type
     * @return Result
     */
	public static function sendMsg($fromUsername,$url,$type='text')
	{
		if ($type == 'text') {
			return self::sendTextMsg($fromUsername,$url);
		}
		else{
			return self::sendMediaMsg($fromUsername, $url, $type);
		}
	}
	

	/**
     * 主动发送文本消息
     * @param string $fromUsername,$content
     * @return Result
     */
	public static function sendTextMsg($fromUsername,$content){
		$access_token = self::getAccessToken(self::$appId,self::$appSecret);
		$url=self::$textSendUrl.'?access_token='.$access_token;
		$postData='{
			"touser":"'.$fromUsername.'",
			"msgtype":"text",
			"text":{"content":"'.$content.'"   }
			}';
		echo date('Y-m-d H:i:s',time()).' sendTextMsg '.$postData."\n";
		$result= self::http_request($url,$postData);
		$resultObj = json_decode($result);
		echo date('Y-m-d H:i:s',time()).' sendMediaMsg '.$result."\n";
		if ($resultObj->errcode == 0) {
			return true;
		}else{
			return false;
		}
	}

	/**
     * 初始化参数
     * @param string $appId,$appSecret,$textSendUrl,$accessTokenUrl,$fileUploadUrl
     * @param string $textSendUrl,$mediaSendUrl,$memHost,$memPort
     * @return void
     */
	public static function ini($appId,$appSecret,$textSendUrl,$accessTokenUrl,
		$fileUploadUrl,$textSendUrl,$mediaSendUrl,$memHost,$memPort)
	{
		self::$appId = $appId;
		self::$appSecret = $appSecret;
		self::$textSendUrl = $textSendUrl;
		self::$accessTokenUrl = $accessTokenUrl;
		self::$fileUploadUrl = $fileUploadUrl;
		self::$textSendUrl = $textSendUrl;
		self::$mediaSendUrl = $mediaSendUrl;
		self::$memHost = $memHost;
		self::$memPort = intval($memPort);
	}

	/**
     * 获取AccessToken
     * @param none
     * @return string accessToken
     */
	public static function getAccessToken(){
		// 设置缓存，100分钟更新一次access_token
		$mem = new Memcache;
		$mem->connect(self::$memHost, self::$memPort) or die ("Could not connect");
		$access_token = $mem->get('access_token');
		echo date('Y-m-d H:i:s',time()).' access_token: '.$access_token;
		if (empty($access_token)){
			$url = self::$accessTokenUrl.'&appid='.self::$appId."&secret=".self::$appSecret;
			$res = self::http_request($url);
			$result = json_decode($res, true);
			$access_token = $result["access_token"];
			$mem->set('access_token', $access_token, 0, 6000);
		}
		$mem->close();
		return $access_token;
	}

	/**
     * 上传图片或语音
     * @param $access_token,$filepath,$type
     * @return string media_id
     */
	public static function uploadMedia($access_token,$filepath,$type){
		//过时的方法 $filedata = array("media" => "@".$filepath);
		$url = self::$fileUploadUrl.'?access_token='.$access_token.'&type='.$type;
		$result = self::https_request($url, $filepath);
		echo $result."\n";
		$jsoninfo = json_decode($result, true);
		$media_id = $jsoninfo["media_id"];
		return $media_id;
	}


	/**
     * 上传文件专用
     * @param $url,$path
     * @return string $output
     */
	private static function https_request($url,$path){
		//path貌似只要相对路径即可
		$curl=curl_init();
		// Create a CURLFile object
		$cfile = curl_file_create($path);
		$filedata = array('media' => $cfile);
		// Log::info("    path---$path filedata--  ".var_dump($filedata).'cfile ---'.var_dump($cfile));
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($curl,CURLOPT_POST,1);
		curl_setopt($curl,CURLOPT_POSTFIELDS,$filedata);
		curl_setopt($curl, CURLOPT_INFILESIZE,filesize($path)); 
		// Log::info(" path---$path filesize(path)--  ".filesize($path));
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$output=curl_exec($curl);
		curl_close($curl);
		return $output;
	}

	/**
     * HTTP请求（支持HTTP/HTTPS，支持GET/POST）
     * @param $url,$data
     * @return string $output
     */
	protected static function http_request($url, $data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}

}
