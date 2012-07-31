<?php

$drivers_needed = array(
	'v2_events' => 'rw',
	'v2_nodegroups' => 'rw',
	'v2_nodes' => 'rw',
);

include 'nodegroups/api/v2/includes/init_details.php';

$defaults = array(
	'expression' => '',
);
$details = array();
$errors = array();
$event_id = new MongoId() . '';
$exists = array();
$h_desc = '';
$h_expr = '';
$input = array();
$optional = array(
	'expression' => 'expression',
);
$output_params = array();
$params = array();
$parsed = array();
$required = array(
	'description' => NULL,
	'nodegroup' => 'nodegroup_name',
);
$sanitize = array(
	'nodegroup' => 'nodegroup_name',
);
$time = time();

list($input, $params) = $api['input']->getInput();

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

$input = $api['input']->removeValues($input);
$input = array_merge($defaults, $input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

$input = $api['input']->sanitizeInput($input, $sanitize);

$exists = $drivers['v2_nodegroups']->getNodegroupByID($input['nodegroup']);
if(!is_array($exists)) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(!empty($exists)) {
	$api['output']->sendData(409, 'Nodegroup already exists', array(
		'details' => $exists));
	exit(0);
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

$details = $drivers['v2_nodegroups']->addNodegroup($input);
if(!$details) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(!$drivers['v2_nodes']->setNodes($details['nodegroup'],
		$parsed['nodes'], $parsed['inherited_nodes'])) {
	$errors = $drivers['v2_nodes']->getError();
	$drivers['v2_nodegroups']->deleteNodegroup($details['nodegroup']);
	$api['output']->sendData(500, 'Unable to set nodes: ' . $errors);
	exit(0);
}

if(!$drivers['v2_nodegroups']->setChildren($details['nodegroup'],
		$parsed['nodegroups'], $parsed['inherited_nodegroups'])) {
	$errors = $drivers['v2_nodegroups']->getError();
	$drivers['v2_nodegroups']->deleteNodegroup($details['nodegroup']);
	$api['output']->sendData(500, 'Unable to set children: ' . $errors);
	exit(0);
}

$api['output']->sendData(201, 'Nodegroup added', array('details' => $details));

// Add a newline to the diff so as not to get the '\ No newline at end of file'
$h_desc = rtrim(xdiff_string_diff('', $details['description'] . "\n"));
$h_expr = rtrim(xdiff_string_diff('', $details['expression'] . "\n"));

$drivers['v2_nodegroups']->addNodegroupHistory(array(
	'action' => 'CREATE',
	'description' => $h_desc,
	'expression' => $h_expr,
	'nodegroup' => $details['nodegroup'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

$drivers['v2_events']->addEvent(array(
	'event' => 'CREATE',
	'id' => $event_id,
	'nodegroup' => $details['nodegroup'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

$drivers['v2_events']->addEvent(array(
	'event' => 'ADD',
	'id' => $event_id,
	'nodegroup' => $details['nodegroup'],
	'nodes' => $parsed['nodes'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

?>
