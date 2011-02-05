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

$slave_okay = true;

require_once('nodegroups_api/v1/includes/init_records.php');

$api->setParameters();
$get = $api->setInput($_GET);
$input = array_merge($get, $_POST);

$required = array(
);

$optional = array(
	'app' => NULL,
	'node' => '_multi_',
	'node_re' => '_multi_',
	'nodegroup_re' => '_multi_',
);

$sanitize = array(
	'app' => 'gpcSlash',
	'node' => '_multi_gpcSlash',
	'node_re' => '_multi_gpcSlash',
	'nodegroup_re' => '_multi_gpcSlash',
);

$errors = $api->validateInput($input, $required, $optional);

if(!empty($errors)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

if(!array_key_exists('node', $input) && !array_key_exists('node_re', $input)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0, 400, 'Missing node(s)');
	exit(0);
}

$input = $api->sanitizeInput($input, $sanitize);

$app = ($input['app']) ? $input['app'] : '';
$nodes = array('eq' => array(), 're' => array());
$options = array(
	'numResults' => $api->getParameter('numResults'),
	'sortDir' => $api->getParameter('sortDir'),
	'startIndex' => $api->getParameter('startIndex'),
);

$output_fields = $api->getParameter('outputFields');
if(!empty($output_fields)) {
	$options['outputFields'] = $output_fields;
}

if(array_key_exists('nodegroup_re', $input)) {
	$options['nodegroup_re'] = $input['nodegroup_re'];
}

if(array_key_exists('node', $input)) {
	$nodes['eq'] = $input['node'];
}

if(array_key_exists('node_re', $input)) {
	$nodes['re'] = $input['node_re'];
}

$nodegroups = $driver->getNodegroupsFromNode($nodes, $app, $options);

if(!is_array($nodegroups)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0, 500, $driver->error());
	exit(0);
}

if(empty($nodegroups)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0);
	exit(0);
}

$api->sendHeaders();
$api->showOutput($nodegroups, $driver->getCount(), 200, $driver->error());

?>
