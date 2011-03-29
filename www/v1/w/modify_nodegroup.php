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
	'nodegroup' => 'nodegroup_name',
);

$optional = array(
	'description' => NULL,
	'expression' => 'expression',
);

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

if(empty($existing)) {
	$api->sendHeaders();
	$api->showOutput(400, 'No such nodegroup');
	exit(0);
}

$expr = $existing['expression'];
if(isset($input['expression'])) {
	$expr = $input['expression'];
}

$parsed = $ngexpr->parseExpression($expr);
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

if(!$driver->modifyNodegroup($nodegroup, $input)) {
	$api->sendHeaders();
	$api->showOutput(500, 'Updating nodegroup: ' . $driver->error());
	exit(0);
}

// See the comments at
// http://php.net/manual/en/function.array-unique.php
// as to why this is faster than array_unique()
$parsed['nodes'] = array_merge(array_flip(array_flip($parsed['nodes'])));
$parsed['nodegroups'] = array_merge(array_flip(array_flip($parsed['nodegroups'])));

if(!$driver->setNodes($nodegroup, $parsed['nodes'])) {
	$api->sendHeaders();
	$api->showOutput(500, 'Setting Nodes: ' . $driver->error());
	exit(0);
}

if(!$driver->setChildren($nodegroup, $parsed['nodegroups'])) {
	$api->sendHeaders();
	$api->showOutput(500, 'Setting Children: ' . $driver->error());
	exit(0);
}

$parent_error = doParent($nodegroup);
if($parent_error !== true) {
	$api->sendHeaders();
	$api->showOutput(500, 'Updating Parents: ' . $parent_error);
	exit(0);
}

$data = $driver->getNodegroup($nodegroup);

$time = time();
$user = ($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : '';

foreach($data as $key => $value) {
	if($data[$key] === $existing[$key]) {
		continue;
	}

	// Add a newline so as not to get the '\ No newline at end of file'
	$diff = xdiff_string_diff($existing[$key] . "\n", $value . "\n");

	$driver->addHistory($nodegroup, array(
		'c_time' => $time,
		'diff' => rtrim($diff),
		'field' => $key,
		'user' => $user,
	));
}

$api->sendHeaders();
$api->showOutput(200, 'Modified', $data);

function doParent($group) {
	global $driver, $ngexpr;

	$parents = $driver->getParents($group);
	if(!is_array($parents)) {
		return $driver->error();
	}

	if(empty($parents)) {
		return true;
	}

	foreach($parents as $parent) {
		$details = $driver->getNodegroup($parent);
		if(!is_array($details)) {
			return $driver->error();
		}

		$p_parsed = $ngexpr->parseExpression($details['expression']);
		if(empty($p_parsed)) {
			return 'Unable to parse expression';
		}

		if(!$driver->setNodes($parent, $p_parsed['nodes'])) {
			return $driver->error();
		}

		$return = doParent($parent);
		if($return !== true) {
			return $return;
		}
	}

	return true;
}

?>
