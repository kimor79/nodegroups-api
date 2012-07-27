<?php

$drivers_needed = array(
	'v2_nodegroups' => 'ro',
	'v2_nodes' => 'ro',
);

include 'nodegroups/api/v2/includes/init_details.php';

$defaults = array();
$errors = array();
$input = array();
$optional = array();
$output_params = array();
$params = array();
$parsed = array();
$required = array(
	'expression' => 'expression',
);
$sanitize = array(
	'expression' => 'expression',
);

list($input, $params) = $api['input']->getInput();

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

$input = array_merge($defaults, $input);
$input = $api['input']->removeValues($input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

$input = $api['input']->sanitizeInput($input, $sanitize);

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

	if(count($parsed['nodegroups']) !=
			$drivers['v2_nodegroups']->getCount()) {
		$api['output']->sendData(424, 'Non-existent nodegroups', array(
			'details' => array_diff($parsed['nodegroups'], $exists)
		));
		exit(0);
	}
}

$api['output']->sendData(200, 'OK', array('details' => $parsed));

?>
