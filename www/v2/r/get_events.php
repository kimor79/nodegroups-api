<?php

$drivers_needed = array(
	'v2_events' => 'ro',
);

include 'nodegroups/api/v2/includes/init_records.php';

$data = array();
$default_params = array(
	'sortDir' => 'desc',
	'sortField' => 'timestamp',
);
$defaults = array();
$driver_params = array();
$errors = array();
$fields = array(
	'event',
	'id',
	'node',
	'nodegroup',
	'timestamp',
	'user',
);
$input = array();
$optional = array(
	'event' => '_array_',
	'event_re' => '_array_',
	'node' => '_array_',
	'node_re' => '_array_',
	'id' => '_array_',
	'id_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
	'timestamp' => '_array_timestamp',
	'timestamp_ge' => 'timestamp',
	'timestamp_le' => 'timestamp',
	'user' => '_array_',
	'user_re' => '_array_',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'event' => '_array_',
	'event_re' => '_array_',
	'node' => '_array_',
	'node_re' => '_array_',
	'id' => '_array_',
	'id_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
	'timestamp' => '_array_timestamp',
	'timestamp_ge' => 'timestamp',
	'timestamp_le' => 'timestamp',
	'user' => '_array_',
	'user_re' => '_array_',
);

list($input, $params) = $api['input']->getInput();
$params = array_merge($default_params, $params);

$output_params = array_intersect_key($params, $api['output']->getParameters());
$errors = $api['output']->setParameters($output_params);
if($errors) {
	$api['output']->sendData(array(), 0, 400, implode("\n", $errors));
	exit(0);
}

$driver_params['v2_events'] = array_intersect_key($params,
	$drivers['v2_events']->getParameters());
$errors = $drivers['v2_events']->setParameters($driver_params['v2_events']);
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

$data = $drivers['v2_events']->buildQuery($input, $fields);

$records = $drivers['v2_events']->getEvents($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_events']->getError());
	exit(0);
}

$api['output']->sendData($records, $drivers['v2_events']->getCount(),
	200, 'OK');

?>
