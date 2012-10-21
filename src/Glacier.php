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
 * Last Modified : Thu Oct 18 16:36:05 2012
 */
namespace core\connectors\amazon;
require('AWSUtils.php');
class AWSGlacier {
	private $awsConfig;
	private $service;

	public function __construct($cfgFile="aws.cfg") {
		// TODO: Read from config file
		$this->awsConfig = array();
		$this->awsConfig['AccessKey'] = "YOUR_ACCESS_KEY"; 
		$this->awsConfig['SecretKey'] = "YOUR_SECRET_KEY";
		$this->awsConfig ['TimeOffset'] = 6;//America/Chicago
		
		$this->service['ALGO']="AWS4-HMAC-SHA256";
		$this->service['NAME']		=	"glacier";
		$this->service['REGION']	=	"us-east-1";
		$this->service['ENDPOINT'] = "/-/vaults/";
		$this->service['HTTP_METHOD'] = 'GET'; 
		
		$this->service['HTTP_HEADERS'] = array();
		$this->service['HTTP_HEADERS']['Host'] = $this->service['NAME'].".".$this->service['REGION'].".amazonaws.com";
		$this->service['HTTP_HEADERS']['Date'] = gmdate('Ymd\THis\Z', time()); 
		$this->service['HTTP_HEADERS']['x-amz-glacier-version'] = "2012-06-01";
	}
	
	public function createVault($vault) {
		$service = $this->service;
		$service['HTTP_HEADERS']['Authorization'] = AWSUtils::getAuthorizationHeader($service, $this->awsConfig); 
		$service['URL']="https://".$service['NAME'].".".$service['REGION'].".amazonaws.com".$service['ENDPOINT'];
		$service['HTTP_METHOD']='PUT';
		$response = AWSUtils::invoke($service);
		return $response['BODY'];
	}

	public function listVault($vault=null) {
		$service = $this->service;
		if(!empty($vault))
			$service['ENDPOINT'] = "/-/vaults"."/".$vault;
		$service['HTTP_HEADERS']['Authorization'] = AWSUtils::getAuthorizationHeader($service, $this->awsConfig); 
		$service['URL']= "https://".$service['NAME'].".".$service['REGION'].".amazonaws.com".$service['ENDPOINT'];
		$response = AWSUtils::invoke($service);
		return $response['BODY'];
	}

	public function deleteArchive($vault, $archiveID) {
		$service = $this->service;
		$service['ENDPOINT'] = "/-/vaults/".$vault."/archives/".$archiveID;
		$service['HTTP_HEADERS']['Authorization'] = AWSUtils::getAuthorizationHeader($service, $this->awsConfig); 
		$service['HTTP_METHOD']='DELETE';
		$service['URL'] = "https://".$service['NAME'].".".$service['REGION'].".amazonaws.com".$service['ENDPOINT'];
		$response = AWSUtils::invoke($service);
		return $response;
	}
	
	public function uploadArchive($archiveFile, $vault="Native5") {
		$service = $this->service;
		if(!file_exists($archiveFile)) {
			return FALSE;
		}
		$service ['HTTP_METHOD'] = 'POST';//needs to be above the getAuthorizationHeader call.
		$service['ENDPOINT'] = "/-/vaults/".$vault."/archives";
		$service['HTTP_HEADERS']['Authorization'] = AWSUtils::getAuthorizationHeader($service, $this->awsConfig, file_get_contents($archiveFile)); 
		$service['HTTP_HEADERS']['x-amz-archive-description'] = "Application Backup"; 
		$service['HTTP_HEADERS']['x-amz-sha256-tree-hash'] = AWSUtils::computeSHA256TreeHash($archiveFile); 
		$service['HTTP_HEADERS']['x-amz-content-sha256'] = hash('sha256', file_get_contents($archiveFile));
		$service['URL'] = "https://".$service['NAME'].".".$service['REGION'].".amazonaws.com".$service['ENDPOINT'];
		
		$service['DATA']= file_get_contents($archiveFile);
		$response = AWSUtils::invoke($service);
		$hdrArr = explode("\n",$response['HEADER']);
		return $hdrArr;
	}
	
	public function listJobs($vault=null) {
		$service = $this->service;
		if(!empty($vault)) {
			$service['ENDPOINT'] = "/-/vaults"."/".$vault."/jobs";
		}
		$service['HTTP_HEADERS']['Authorization'] = AWSUtils::getAuthorizationHeader($service, $this->awsConfig); 
		$service['URL'] = "https://".$this->service['NAME'].".".$this->service['REGION'].".amazonaws.com".$service['ENDPOINT'];
		$response = AWSUtils::invoke($service);
		return $response['BODY'];
	}
	
	//
	public function getJobOutput($vault, $jobid) {
		$service = $this->service;
		$service ['ENDPOINT'] = "/-/vaults" . "/" . $vault . "/jobs/$jobid/output";
		$service ['HTTP_HEADERS'] ['Authorization'] = AWSUtils::getAuthorizationHeader ( $service, $this->awsConfig );
		$service ['URL'] = "https://" . $this->service ['NAME'] . "." . $this->service ['REGION'] . ".amazonaws.com" . $service ['ENDPOINT'];
		$response = AWSUtils::invoke ( $service );
		return $response ['BODY'];
	}
	//
	public function requestDownload($vault, $archiveid, $description="Download Request") {
		$data = array ("Type" => "archive-retrieval", "ArchiveId" => $archiveid, "Description" => $description );
		$service = $this->service;
		$service ['HTTP_METHOD'] = 'POST';
		$service ['ENDPOINT'] = "/-/vaults" . "/" . $vault . "/jobs";
		$service ['HTTP_HEADERS'] ['Authorization'] = AWSUtils::getAuthorizationHeader ( $service, $this->awsConfig, json_encode ( $data ) );
		$service ['URL'] = "https://" . $this->service ['NAME'] . "." . $this->service ['REGION'] . ".amazonaws.com" . $service ['ENDPOINT'];
		
		$service ['DATA'] = json_encode ( $data );
		
		$response = AWSUtils::invoke ( $service );
		return $response ['BODY'];
	}
}
?>
