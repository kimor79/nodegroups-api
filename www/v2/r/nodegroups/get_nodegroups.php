<?php

$drivers_needed = array(
	'v2_nodegroups' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$data = array();
$default_params = array(
	'sortField' => 'nodegroup',
);
$defaults = array();
$driver_params = array();
$errors = array();
$fields = array(
	'description',
	'expression',
	'nodegroup',
);
$input = array();
$optional = array(
	'description' => '_array_',
	'description_re' => '_array_',
	'expression' => '_array_expression',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'description' => '_array_',
	'description_re' => '_array_',
	'expression' => '_array_expression',
	'expression_re' => '_array_',
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

$driver_params['v2_nodegroups'] = array_intersect_key($params,
	$drivers['v2_nodegroups']->getParameters());
$errors = $drivers['v2_nodegroups']->setParameters(
	$driver_params['v2_nodegroups']);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$input = array_merge($defaults, $input);
//$input = $api['input']->removeValues($input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$input = $api['input']->sanitizeInput($input, $sanitize);

$data = $drivers['v2_nodegroups']->buildQuery($input, $fields);

$records = $drivers['v2_nodegroups']->getNodegroups($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

$api['output']->sendData($records, $drivers['v2_nodegroups']->getCount(),
	200, 'OK');

?>
