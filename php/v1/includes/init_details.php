<?php

/**

Copyright (c) 2010, Kimo Rosenbaum and contributors
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the owner nor the names of its contributors
      may be used to endorse or promote products derived from this
      software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

**/

require_once('nodegroups_api/v1/classes/api_details.php');

$api = new NodegroupsApiDetails();

$config = @parse_ini_file('/usr/local/etc/nodegroups_api/config.ini', true);

if(empty($config)) {
	$api->sendHeaders();
	$api->showOutput('500', 'Error with config file');
	exit(0);
}

if(!array_key_exists('driver', $config)) {
	$api->sendHeaders();
	$api->showOutput('500', 'No driver configured');
	exit(0);
}

require_once('nodegroups_api/v1/includes/drivers.php');

if(!array_key_exists($config['driver'], $drivers)) {
	$api->sendHeaders();
	$api->showOutput('500', 'No such driver: ' . $config['driver']);
	exit(0);
}

require_once('nodegroups_api/v1/drivers/' . $drivers[$config['driver']]);

try {
	$driver = new NodegroupsApiDriver();
} catch (Exception $e) {
	$api->sendHeaders();
	$api->showOutput('500', $e->getMessage());
	exit(0);
}

?>
