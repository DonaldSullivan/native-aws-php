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
 * @version 0.0.1 
 * @license See attached NOTICE.md for details
 * @copyright See attached LICENSE for details
 *
 * Created : 18-10-2012
 * Last Modified : Thu Oct 18 15:57:17 2012
 */
namespace core\connectors\amazon;
require('AWSSDKforPHP/sdk.class.php');
class S3Bucket {
	private $s3;

	public function __construct() {
		$this->s3 = new \AmazonS3();
	}

	function fetchFiles($bucket='akzo-nobel', $maxAge=0, $maxCount=null, $download=true) {
		$time = time() - $maxAge;
		$opts = array();
		if(!empty($maxCount))
			$opts['max-keys']=$maxCount;
		$res = $this->s3->get_object_list($bucket, $opts);
		$index =0;
		$filesToArchive = array();
		foreach($res as $obj)  {
			$resp = $this->s3->get_object_metadata($bucket, $obj);
			$lMod = $resp['Headers']['last-modified'];
			$modTime = strtotime($lMod);
			$isNewFile = $modTime>$time;
			if($isNewFile) {
				$index++;
			} else if($resp['Size']>0) {
				if($download) {
					$file = $this->downloadFile($bucket, $obj);
					if($file)
						$filesToArchive[] = $file;
				}
			}
		}
		return $filesToArchive;
	}

	private function downloadFile($bucket, $obj) {
			$baseDir = "./archives";
			$fNames = explode('/', $obj);
			$archiveFolder = $baseDir."/".date('Ymd\Thi');
			$fName = $baseDir."/".$fNames[count($fNames)-1];

			$response = $this->s3->get_object($bucket, $obj, array('fileDownload'=>$fName));
			$this->s3->delete_object($bucket, $obj);
			if($response->isOK()) 
				return $fName;
			else 
				return false;
	}
}
?>
