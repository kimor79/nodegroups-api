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

require_once('nodegroups_api/v1/includes/init_details.php');

$api->setParameters();
$input = $_POST;

$required = array(
	'description' => NULL,
	'expression' => 'expression',
	'nodegroup' => 'nodegroup_name',
);

$optional = array();

$errors = $api->validateInput($input, $required, $optional);

if(!empty($errors)) {
	$api->sendHeaders();
	$api->showOutput(400, implode("\n", $errors));
	exit(0);
}

$sanitize = array(
	'description' => 'gpcSlash',
	'expression' => 'gpcSlash',
	'nodegroup' => 'gpcSlash',
);

$input = $api->sanitizeInput($input, $sanitize);

$nodegroup = $input['nodegroup'];
unset($input['nodegroup']);

$existing = $driver->getNodegroup($nodegroup);
if(!is_array($existing)) {
	$api->sendHeaders();
	$api->showOutput(500, 'Checking for existence: ' . $driver->error());
	exit(0);
}

if(!empty($existing)) {
	$api->sendHeaders();
	$api->showOutput(400, 'Nodegroup already exists');
	exit(0);
}

$parsed = $ngexpr->parseExpression($input['expression']);
if(empty($parsed)) {
	$api->sendHeaders();
	$api->showOutput(500, 'Unable to parse expression');
	exit(0);
}

if(!empty($parsed['nodegroups'])) {
	$children = $driver->listNodegroups(
		array('nodegroup' => array('eq' => $parsed['nodegroups'])),
		array('outputFields' => array('nodegroup' => true))
	);

	if(count($parsed['nodegroups']) != $driver->count()) {
		$api->sendHeaders();
		$api->showOutput(400, 'Non-existent nodegroups in expression');
		exit(0);
	}
}

if(!$driver->addNodegroup($nodegroup, $input)) {
	$api->sendHeaders();
	$api->showOutput(500, 'Adding nodegroup: ' . $driver->error());
	exit(0);
}

if(!$driver->setNodes($nodegroup, $parsed['nodes'])) {
	$error = $driver->error();
	$driver->deleteNodegroup($nodegroup);
	$api->sendHeaders();
	$api->showOutput(500, 'Setting Nodes: ' . $error);
	exit(0);
}

if(!$driver->setChildren($nodegroup, $parsed['nodegroups'])) {
	$error = $driver->error();
	$driver->deleteNodegroup($nodegroup);
	$api->sendHeaders();
	$api->showOutput(500, 'Setting Children: ' . $error);
	exit(0);
}

$data = $driver->getNodegroup($nodegroup);

// Add a newline to the diff so as not to get the '\ No newline at end of file'
$h_description = rtrim(xdiff_string_diff('', $data['description'] . "\n"));
$h_expression = rtrim(xdiff_string_diff('', $data['expression'] . "\n"));

$driver->addHistoryNodegroup($nodegroup, array(
	'action' => 'CREATE',
	'c_time' => $time,
	'description' => $h_description,
	'expression' => $h_expression,
	'user' => $user,
));

$driver->addEvent($nodegroup, array(
	'c_time' => $time,
	'event' => 'CREATE',
	'user' => $user,
));

$driver->addEvent($nodegroup, array(
	'c_time' => $time,
	'event' => 'ADD',
	'node' => $parsed['nodes'],
	'user' => $user,
));

$api->sendHeaders();
$api->showOutput(200, 'Added', $data);

?>
