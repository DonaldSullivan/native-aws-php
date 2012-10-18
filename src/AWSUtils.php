<?php
/*
 *  Copyright 2012 Native5. All Rights Reserved 
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *	You may not use this file except in compliance with the License.
 *		
 *	You may obtain a copy of the License at
 *	http://www.apache.org/licenses/LICENSE-2.0
 *  or in the "license" file accompanying this file.
 *
 *	Unless required by applicable law or agreed to in writing, software
 *	distributed under the License is distributed on an "AS IS" BASIS,
 *	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *	See the License for the specific language governing permissions and
 *	limitations under the License.
 *
 */

/**
 * 
 * @version 
 * @license See attached NOTICE.md for details
 * @copyright See attached LICENSE for details
 *
 * Created : 18-10-2012
 * Last Modified : Thu Oct 18 15:58:38 2012
 */
namespace core\connectors\amazon;
class AWSUtils {
	
	public static function computeSHA256TreeHash($file) {
		$hashChunks = self::getSHAChunks($file);
		$hashValue = self::computeTreeHashValue($hashChunks);	
		return $hashValue;	
	}

	public static function getAuthorizationHeader($service_config, $awsConfig, $data="") {
		$canonicalReq = $service_config['HTTP_METHOD']."\n"
			.$service_config['ENDPOINT']."\n"
			."\n".self::getCanonicalHeader($service_config['HTTP_HEADERS'])."\n"
			.self::getSignedHeader($service_config['HTTP_HEADERS'])."\n"
			.hash('sha256', $data);

		//echo "Canonical Request = "."\n";
		//echo "Endpoint : ".$service_config['ENDPOINT']."\n";
		//echo "==========================================="."\n";
		//echo $canonicalReq."\n";
		//echo "==========================================="."\n";

		$currTime = strtotime($service_config['HTTP_HEADERS']['Date']);
		$reqDate = gmdate('Ymd\THis\Z', $currTime);
		
		$credScope = date('Ymd', $currTime)."/".$service_config['REGION']."/".$service_config['NAME']."/aws4_request";
		$strToSign = $service_config['ALGO']."\n".$reqDate."\n".$credScope."\n".hash('sha256',$canonicalReq);
		//echo "String to Sign = "."\n";
		//echo "==========================================="."\n";
		//echo $strToSign."\n";
		//echo "==========================================="."\n";
		$credKeys = explode('/', $credScope);
		$derivedKey = "AWS4".$awsConfig['SecretKey'];
		foreach($credKeys as $key) {
			$derivedKey = self::hextostr(hash_hmac("sha256", $key, $derivedKey));	
		}
		$signature = hash_hmac('sha256', $strToSign, $derivedKey);

    $authHeader = $service_config['ALGO']." Credential=".
			$awsConfig['AccessKey']."/".date('Ymd', $currTime).
			"/".$service_config['REGION']."/".$service_config['NAME']."/aws4_request,".
			"SignedHeaders=".self::getSignedHeader($service_config['HTTP_HEADERS']).
			",Signature=".$signature;
		return $authHeader;
	}
	
	public static function computeSignature($service_config, $awsConfig) {
		$canonicalReq = $service_config['HTTP_METHOD']."\n"
			.$awsConfig['Endpoint']."\n"
			."\n".self::getCanonicalHeader($service_config['HTTP_HEADERS'])."\n"
			.self::getSignedHeader($service_config['HTTP_HEADERS'])."\n"
			.hash('sha256', '');

		
		$reqDate = gmdate("Ymd\THis\Z", time()); 
		
		$credScope = date('Ymd', $currTime)."/".$service_config['REGION']."/".$service_config['NAME']."/aws4_request";
		$strToSign = $service_config['ALGO']."\n".$reqDate."\n".$credScope."\n".hash('sha256',$canonicalReq);
		$credKeys = explode('/', $credScope);
		$derivedKey = "AWS4".$awsConfig['SecretKey'];
		foreach($credKeys as $key) {
			$derivedKey = self::hextostr(hash_hmac("sha256", $key, $derivedKey));	
		}
		$signature = hash_hmac('sha256', $strToSign, $derivedKey);
		return $signature;	
	}
	
	private static function getCanonicalHeader($headers) {
		$canonicalStr = "";
		foreach($headers as $key=>$val) {
			if(trim(strtolower($key))=="date")
				$canonicalStr = $canonicalStr.'x-amz-date'.":"."\n";
				//":".trim($val)."\n";
			else 
				$canonicalStr = $canonicalStr.strtolower($key).":".trim($val)."\n";
		}
		return $canonicalStr;
	}

	private static function getSignedHeader($headers) {
		$signedHeader = "";
		foreach($headers as $key=>$val) {
			if(trim(strtolower($key)) == "date") {
				$signedHeader = $signedHeader."x-amz-date".";";
			} else {
				$signedHeader = $signedHeader.strtolower($key).";";
			}
		}
		$signedHeader = substr($signedHeader, 0, strlen($signedHeader)-1);
		return $signedHeader;
	}

  private static function hextostr($hex) {
	  $str='';
		for ($i=0; $i < strlen($hex)-1; $i+=2) {
			$str .= chr(hexdec($hex[$i].$hex[$i+1]));
		}   
		return $str;
	}
	

	private static function computeTreeHashValue($chunks) {
		while(count($chunks)>1) {
			$currChunks = array();
			$len = intval(count($chunks)/2);
			if($chunks%2 !=0) {
				$len++;
			}
			//echo "PHP # of Chunks ".$len." - ".count($chunks)."\n";
			for($i=0;$i<count($chunks);$i=$i+2) {
				if(count($chunks)-$i >1 ) { // > 1 element
					$ctx = hash_init('sha256');
					$str1 = self::hextostr($chunks[$i]);
					$str2 = self::hextostr($chunks[$i+1]);
					hash_update($ctx, $str1); 
					hash_update($ctx, $str2);
					$computedHash = hash_final($ctx);
					//echo "Recomputed Hash = ".count($currChunks)." - ".$computedHash."\n";
					$currChunks[]=$computedHash;
				} else {
					$currChunks[] = $chunks[$i];
				}
			}
			$chunks = $currChunks;
		}
		return $chunks[0];
	}

	private static function getSHAChunks($file) {
		$ONE_MB = 1024 * 1024;	
		$computedHashes = array();
		$chunks = intval(filesize($file)/$ONE_MB)+1;
		//echo "# Chunks ".$chunks."\n";	
		if(filesize($file) % $ONE_MB > 0) {
			$chunks++;
		}

		if ($chunks == 0) {
		 	return hash('sha256', "");
		}
		
		try {
			$handle = fopen($file, 'r');
			$data = fread($handle, $ONE_MB);
			while(strlen($data) >0) {
				$hashVal = hash("sha256", $data);
				$computedHashes[] = $hashVal; 
				$data = fread($handle, $ONE_MB);
			}
		} catch(Exception $e) {
			$logger->log($e->getMessage);
		}
		return $computedHashes;		
	}

	public static function invoke($service) {
		$ch = curl_init();
		$headers = array();
		foreach($service['HTTP_HEADERS'] as $k=>$v) {
			$headers[] = $k.":".$v;
		}
		curl_setopt($ch, CURLOPT_URL, $service['URL']);
		if(isset($service['HTTP_METHOD']) && $service['HTTP_METHOD']!='POST')
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $service['HTTP_METHOD']);
		else {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $service['DATA']);
		}
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$resp = curl_exec($ch);
		$response = array();
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$response['HEADER']= substr($resp, 0, $header_size);
		$response['BODY']= substr($resp, $header_size);
		return $response;
	}
}
?>

