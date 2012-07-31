<?php

$drivers_needed = array(
	'v2_nodegroups' => 'ro',
	'v2_nodes' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$default_params = array(
	'listKey' => 'node',
	'sortField' => 'node',
);
$defaults = array();
$errors = array();
$exists = array();
$input = array();
$optional = array();
$output_params = array();
$params = array();
$parsed = array();
$records = array();
$required = array(
	'expression' => 'expression',
);
$sanitize = array(
	'expression' => 'expression',
);
$sort = array();
$total = 0;

list($input, $params) = $api['input']->getInput();
$params = array_merge($default_params, $params);

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$input = array_merge($defaults, $input);
$input = $api['input']->removeValues($input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
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
		$api['output']->sendData(array(), 0, 500,
			'Checking children: ' .
			$drivers['v2_nodegroups']->getError());
		exit(0);
	}

	if(count($parsed['nodegroups']) !=
			$drivers['v2_nodegroups']->getCount()) {
		$exists = array_diff($parsed['nodegroups'], $exists);
		$api['output']->sendData(array(), 0, 424,
			'Non-existent nodegroups: ' .
			implode("\n", $exists));
		exit(0);
	}
}

while(list($key, $node) = each($parsed['nodes'])) {
	$records[$key] = array(
		'inherited' => (in_array($node, $parsed['inherited_nodes'])) ?
			1 : 0,
		'node' => $node,
		'nodegroup' => '',
	);

	$sort[$key] = $records[$key][$params['sortField']];
}
reset($parsed['nodes']);

$total = count($records);

if(array_key_exists('sortDir', $params) &&
		$params['sortDir'] === 'desc') {
	$sort_dir = SORT_DESC;
}

array_multisort($sort, $sort_dir, $records);

if(empty($params['numResults'])) {
	$params['numResults'] = $total;
}

if(empty($params['startIndex'])) {
	$params['startIndex'] = 0;
}

$records = array_slice($records, $params['startIndex'], $params['numResults']);

$api['output']->sendData($records, $total, 200, 'OK');

?>
