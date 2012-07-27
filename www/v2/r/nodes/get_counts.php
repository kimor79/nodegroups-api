<?php

$drivers_needed = array(
	'v2_nodes' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$data = array();
$default_params = array(
	'sortDir' => 'asc',
	'sortField' => 'node',
);
$defaults = array();
$driver_params = array();
$errors = array();
$fields = array(
	'inherited',
	'node',
	'nodegroups',
);
$input = array();
$optional = array(
	'inherited' => '_array_digit',
	'inherited_ge' => 'digit',
	'inherited_le' => 'digit',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroups' => '_array_digit',
	'nodegroups_ge' => 'digit',
	'nodegroups_le' => 'digit',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'inherited' => '_array_int',
	'inherited_ge' => 'int',
	'inherited_le' => 'int',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroups' => '_array_int',
	'nodegroups_ge' => 'int',
	'nodegroups_le' => 'int',
);

list($input, $params) = $api['input']->getInput();
$params = array_merge($default_params, $params);

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$driver_params['v2_nodes'] = array_intersect_key($params,
	$drivers['v2_nodes']->getParameters());
$errors = $drivers['v2_nodes']->setParameters($driver_params['v2_nodes']);
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

$data = $drivers['v2_nodes']->buildQuery($input, $fields);

$records = $drivers['v2_nodes']->getNodegroupCounts($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_nodes']->getError());
	exit(0);
}

$api['output']->sendData($records, $drivers['v2_nodes']->getCount(),
	200, 'OK');

?>
