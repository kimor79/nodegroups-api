<?php

$drivers_needed = array(
	'v2_nodes' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$data = array();
$default_params = array(
	'sortField' => 'node',
);
$defaults = array();
$driver_params = array();
$errors = array();
$fields = array(
	'inherited',
	'nodegroup',
	'node',
);
$input = array();
$optional = array(
	'inherited' => 'bool',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'inherited' => 'bool_false',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
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

$records = $drivers['v2_nodes']->getNodes($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_nodes']->getError());
	exit(0);
}

$api['output']->sendData($records, $drivers['v2_nodes']->getCount(), 200, 'OK');

?>
