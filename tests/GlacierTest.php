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
	* Last Modified : Thu Oct 18 17:04:50 2012
	*/
require('src/Glacier.php');
use core\connectors\amazon\AWSGlacier as AWSGlacier;

class GlacierTest extends PHPUnit_Framework_TestCase {
	public function testListVaults() {
		$glacier = new AWSGlacier();
		$response = $glacier->listVault();
		$vaultCount = 0;
		try {
			$vaults = json_decode($response);
			$vaultCount = count($vaults->VaultList);
		} catch(Exception $e) {
			$vaultCount = 0;
		}
		$this->assertTrue($vaultCount>0, 'No Vaults found');
	}

}
?>

