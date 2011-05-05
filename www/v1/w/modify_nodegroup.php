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
	'force' => 'bool',
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

$force = false;
if(array_key_exists('force', $input)) {
	$force = $api->trueFalse($input['force'], false);
	unset($input['force']);
}

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

if($expr != $existing['expression']) {
	$force = true;
}

if($force) {
	$parsed = $ngexpr->parseExpression($expr);
	if(empty($parsed)) {
		$api->sendHeaders();
		$api->showOutput(500, 'Unable to parse expression');
		exit(0);
	}

	if(!empty($parsed['nodegroups'])) {
		$children = $driver->listNodegroups(
			array('nodegroup' => array('eq' =>
				$parsed['nodegroups'])),
			array('outputFields' => array('nodegroup' => true))
		);

		if(count($parsed['nodegroups']) != $driver->count()) {
			$api->sendHeaders();
			$api->showOutput(400,
				'Non-existent nodegroups in expression');
			exit(0);
		}
	}

	$nodes = array(
		'old' => $driver->getNodesFromNodegroup($nodegroup),
		'new' => $parsed['nodes'],
	);
}

if(!$driver->modifyNodegroup($nodegroup, $input)) {
	$api->sendHeaders();
	$api->showOutput(500, 'Updating nodegroup: ' . $driver->error());
	exit(0);
}

if($force) {

	// See the comments at
	// http://php.net/manual/en/function.array-unique.php
	// as to why this is faster than array_unique()
	$parsed['nodes'] = array_merge(array_flip(
		array_flip($parsed['nodes'])));
	$parsed['nodegroups'] = array_merge(array_flip(
		array_flip($parsed['nodegroups'])));

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
}

$data = $driver->getNodegroup($nodegroup);

$force_history = false;

$h_description = '';
if($data['description'] !== $existing['description']) {
	$force_history = true;

	// Add a newline to the diff so as not to get the
	// '\ No newline at end of file'
	$h_description = rtrim(xdiff_string_diff(
		$existing['description'] . "\n",
		$data['description'] . "\n"));
}

$h_expression = '';
if($data['expression'] !== $existing['expression']) {
	$force_history = true;

	// Add a newline to the diff so as not to get the
	// '\ No newline at end of file'
	$h_expression = rtrim(xdiff_string_diff($existing['expression'] . "\n",
		$data['expression'] . "\n"));
}

if($force_history) {
	$h_user = ($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'] : '';

	$driver->addHistoryNodegroup($nodegroup, array(
		'action' => 'MODIFY',
		'c_time' => time(),
		'description' => $h_description,
		'expression' => $h_expression,
		'h_user' => $user,
	));
}

if($force) {
	$driver->addEvent($nodegroup, array(
		'c_time' => time(),
		'event' => 'ADD',
		'node' => array_diff($nodes['new'], $nodes['old']),
		'user' => ($_SERVER['REMOTE_USER']) ?
			$_SERVER['REMOTE_USER'] : '',
	));

	$driver->addEvent($nodegroup, array(
		'c_time' => time(),
		'event' => 'REMOVE',
		'node' => array_diff($nodes['old'], $nodes['new']),
		'user' => ($_SERVER['REMOTE_USER']) ?
			$_SERVER['REMOTE_USER'] : '',
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
