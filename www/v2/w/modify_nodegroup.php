<?php

$consumers_needed = array(
	'v2_self',
);

$drivers_needed = array(
	'v2_events' => 'rw',
	'v2_nodegroups' => 'rw',
	'v2_nodes' => 'rw',
);

include 'nodegroups/api/v2/includes/init_details.php';

$current = array();
$defaults = array();
$details = array();
$do_parents = array();
$errors = array();
$event_id = new MongoId() . '';
$exists = array();
$force = false;
$force_history = false;
$h_desc = '';
$h_expr = '';
$input = array();
$optional = array(
	'description' => NULL,
	'expression' => 'expression',
	'force' => 'bool',
);
$old = array();
$output_params = array();
$params = array();
$parents = array();
$parsed = array();
$required = array(
	'nodegroup' => 'nodegroup_name',
);
$sanitize = array(
	'force' => 'bool_false',
	'nodegroup' => 'nodegroup_name',
);
$time = time();

list($input, $params) = $api['input']->getInput(array('input' => 'PJ'));

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

if(!$api['authn']->isAuthenticated()) {
	$api['output']->sendData(401, 'Not authenticated');
	exit(0);
}

if(array_key_exists('expression', $input)) {
	// This is a very hack way to allow an empty expression.
	// If the expression is empty (''), it will be removed by
	// removeValues() then by having a default expression of '', it will
	// be added back to $input by the array_merge().
	$defaults['expression'] = '';
}

$input = $api['input']->removeValues($input);
$input = array_merge($defaults, $input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

$input = $api['input']->sanitizeInput($input, $sanitize);

if(array_key_exists('force', $input)) {
	$force = $input['force'];
	unset($input['force']);
}

$current = $drivers['v2_nodegroups']->getNodegroupByID($input['nodegroup']);
if(!is_array($current)) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(empty($current)) {
	$api['output']->sendData(404, 'No such nodegroup');
	exit(0);
}

$old['nodes'] =
	$drivers['v2_nodes']->getNodesFromNodegroup($current['nodegroup']);
if(!is_array($old['nodes'])) {
	$api['output']->sendData(500, 'Current nodes: ' .
		$drivers['v2_nodes']->getError());
	exit(0);
}

$old['children'] =
	$drivers['v2_nodegroups']->getChildren($current['nodegroup']);
if(!is_array($old['children'])) {
	$api['output']->sendData(500, 'Current children: ' .
		$drivers['v2_nodegroups']->getError());
	exit(0);
}

$parents = $drivers['v2_nodegroups']->getParents($current['nodegroup']);
if(!is_array($parents)) {
	$api['output']->sendData(500, 'Current parents: ' .
		$drivers['v2_nodegroups']->getError());
	exit(0);
}

$input = array_merge($current, $input);

if($input['expression'] != $current['expression']) {
	$force = true;
	$force_history = true;
}

if($input['description'] != $current['description']) {
	$force_history = true;
}

$parsed = $ngexpr->parseExpression($input['expression']);
if(empty($parsed)) {
	$api['output']->sendData(500, 'Unable to parse expression');
	exit(0);
}

if(!empty($parsed['nodegroups'])) {
	$exists = $drivers['v2_nodegroups']->getNodegroups(
		array('nodegroup' => array('eq' => $parsed['nodegroups'])),
		array('outputFields' => array('nodegroup' => true)));
	if(!is_array($exists)) {
		$api['output']->sendData(500, 'Checking children: ' .
			$drivers['v2_nodegroups']->getError());
		exit(0);
	}

	if(in_array($input['nodegroup'], $parsed['nodegroups'])) {
		$api['output']->sendData(418, 'In order to understand ' .
			'recursion, you must first understand recursion');
		exit(0);
	}

	if(count($parsed['nodegroups']) !=
			$drivers['v2_nodegroups']->getCount()) {
		$api['output']->sendData(424, 'Non-existent nodegroups', array(
			'details' => array_diff($parsed['nodegroups'], $exists)
		));
		exit(0);
	}
}

$details = $drivers['v2_nodegroups']->modifyNodegroup($input);
if(!$details) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if($force) {
	if(!$drivers['v2_nodes']->setNodes($details['nodegroup'],
			$parsed['nodes'], $parsed['inherited_nodes'])) {
		$errors = $drivers['v2_nodes']->getError();

		$drivers['v2_nodegroups']->modifyNodegroup($current);

		$api['output']->sendData(500,
			'Unable to set nodes: ' . $errors);
		exit(0);
	}

	if(!$drivers['v2_nodegroups']->setChildren($details['nodegroup'],
			$parsed['nodegroups'],
			$parsed['inherited_nodegroups'])) {
		$errors = $drivers['v2_nodegroups']->getError();

		$drivers['v2_nodegroups']->modifyNodegroup($current);
		$drivers['v2_nodes']->setNodes($details['nodegroup'],
			$old['nodes']['nodes'], $old['nodes']['inherited']);

		$api['output']->sendData(500,
			'Unable to set children: ' . $errors);
		exit(0);
	}
}

$api['output']->sendData(200, 'Nodegroup modified',
	array('details' => $details));

if($force_history) {
	// Add a newline to the diff so as not to get
	// the '\ No newline at end of file'
	$h_desc = rtrim(xdiff_string_diff($current['description'] . "\n",
		$details['description'] . "\n"));
	$h_expr = rtrim(xdiff_string_diff($current['expression'] . "\n",
		$details['expression'] . "\n"));

	$drivers['v2_nodegroups']->addNodegroupHistory(array(
		'action' => 'MODIFY',
		'description' => $h_desc,
		'expression' => $h_expr,
		'nodegroup' => $details['nodegroup'],
		'timestamp' => $time,
		'user' => $api['authn']->getUser(),
	));

	$drivers['v2_events']->addEvent(array(
		'event' => 'MODIFY',
		'id' => $event_id,
		'nodegroup' => $details['nodegroup'],
		'timestamp' => $time,
		'user' => $api['authn']->getUser(),
	));
}

if($force) {
	$add_nodes = array_diff($parsed['nodes'], $old['nodes']['nodes']);
	$rm_nodes = array_diff($old['nodes']['nodes'], $parsed['nodes']);

	$drivers['v2_events']->addEvent(array(
		'event' => 'ADD',
		'id' => $event_id,
		'nodegroup' => $details['nodegroup'],
		'nodes' => $add_nodes,
		'timestamp' => $time,
		'user' => $api['authn']->getUser(),
	));

	$drivers['v2_events']->addEvent(array(
		'event' => 'REMOVE',
		'id' => $event_id,
		'nodegroup' => $details['nodegroup'],
		'nodes' => $rm_nodes,
		'timestamp' => $time,
		'user' => $api['authn']->getUser(),
	));

	$do_parents = array_diff($parents['nodegroups'], $parents['inherited']);
	foreach ($do_parents as $parent) {
		$consumers['v2_self']->getDetails('/v2/w/modify_nodegroup.php',
			array(
				'json_post' => array(
					'force' => 1,
					'nodegroup' => $parent,
				),
			));
	}
}

?>
