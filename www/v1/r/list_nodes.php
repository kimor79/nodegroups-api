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

$required = array();

$optional = array(
	'expression' => 'expression',
	'nodegroup' => '_multi_nodegroup_name',
	'use_cache' => 'bool',
);

$sanitize = array(
	'expression' => 'gpcSlash',
	'nodegroup' => '_multi_gpcSlash',
);

$errors = $api->validateInput($input, $required, $optional);

if(!empty($errors)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

if(!array_key_exists('expression', $input)
		&& !array_key_exists('nodegroup', $input)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0, 400, 'Missing expression or nodegroup');
	exit(0);
}

$input = $api->sanitizeInput($input, $sanitize);

$nodes = array();
$use_cache = $api->trueFalse($input['use_cache'], true);

if(array_key_exists('expression', $input)) {
	$parsed = $ngexpr->parseExpression($input['expression'], $use_cache);

	if(empty($parsed)) {
		$api->sendHeaders();
		$api->showOutput(array(), 0, 500, 'Unable to parse expression');
		exit(0);
	}

	$nodes = $parsed['nodes'];
}

if(array_key_exists('nodegroup', $input)) {
	if($use_cache) {
		$t_nodes = $driver->getNodesFromNodegroup($input['nodegroup'],
			array());
		if(!is_array($t_nodes)) {
			$api->sendHeaders();
			$api->showOutput(array(), 0, 500, $driver->error());
			exit(0);
		}

		$nodes = array_merge($nodes, $t_nodes);
	} else {
		foreach($input['nodegroup'] as $nodegroup) {
			$details = $driver->getNodegroup($nodegroup);
			if(!is_array($details)) {
				$api->sendHeaders();
				$api->showOutput(array(), 0, 500,
					$driver->error());
				exit(0);
			}

			if(empty($details)) {
				continue;
			}

			$parsed = $ngexpr->parseExpression(
				$details['expression'], false);
			if(empty($parsed)) {
				$api->sendHeaders();
				$api->showOutput(array(), 0, 500,
					'Unable to parse ' . $nodegroup);
				exit(0);
			}

			$t_nodes = $parsed['nodes'];

			$nodes = array_merge($nodes, $t_nodes);
		}
	}
}

if(empty($nodes)) {
	$api->sendHeaders();
	$api->showOutput(array(), 0);
	exit(0);
}

// See the comments at
// http://php.net/manual/en/function.array-unique.php
// as to why this is faster than array_unique()
$nodes = array_merge(array_flip(array_flip($nodes)));

if($api->getParameter('sortDir') == 'asc') {
	sort($nodes);
} else {
	rsort($nodes);
}

$total = count($nodes);

if($api->getParameter('numResults') > 0) {
	$sliced = array_slice($nodes,
		$api->getParameter('startIndex'),
		$api->getParameter('numResults'));
} else {
	$sliced = array_slice($nodes, $api->getParameter('startIndex'));
}

$records = array();
while(list($junk, $node) = each($sliced)) {
	$records[] = array('node' => $node);
}

$api->sendHeaders();
$api->showOutput($records, $total);

?>
