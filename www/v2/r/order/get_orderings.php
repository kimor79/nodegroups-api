<?php

$drivers_needed = array(
	'v2_order' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$data = array();
$default_params = array(
	'sortDir' => 'asc',
	'sortField' => 'app',
);
$defaults = array();
$driver_params = array();
$errors = array();
$fields = array(
	'app',
	'nodegroup',
	'order',
);
$input = array();
$optional = array(
	'app' => '_array_app',
	'app_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
	'order' => '_array_digit',
	'order_ge' => 'digit',
	'order_le' => 'digit',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'app' => '_array_app',
	'app_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
	'order' => '_array_int',
	'order_ge' => 'int',
	'order_le' => 'int',
);

list($input, $params) = $api['input']->getInput();
$params = array_merge($default_params, $params);

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$driver_params['v2_order'] = array_intersect_key($params,
	$drivers['v2_order']->getParameters());
$errors = $drivers['v2_order']->setParameters($driver_params['v2_order']);
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

$data = $drivers['v2_order']->buildQuery($input, $fields);

$records = $drivers['v2_order']->getOrderings($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_order']->getError());
	exit(0);
}

$api['output']->sendData($records, $drivers['v2_order']->getCount(),
	200, 'OK');

?>
