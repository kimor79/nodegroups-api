<?php

$drivers_needed = array(
	'v2_nodegroups' => 'ro',
	'v2_order' => 'rw',
);

include 'nodegroups/api/v2/includes/init_details.php';

$current = array();
$defaults = array();
$details = array();
$errors = array();
$exists = array();
$history = array();
$input = array();
$optional = array();
$output_params = array();
$params = array();
$required = array(
	'app' => 'app',
	'nodegroup' => 'nodegroup_name',
);
$sanitize = array(
	'nodegroup' => 'nodegroup_name',
);

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

$input = array_merge($defaults, $input);
$input = $api['input']->removeValues($input);
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

if(empty($exists)) {
	$api['output']->sendData(404, 'No such nodegroup');
	exit(0);
}

$current = $drivers['v2_order']->getOrder($input);
if(!is_array($current)) {
	$api['output']->sendData(500, $drivers['v2_order']->getError());
	exit(0);
}

if(empty($current)) {
	$api['output']->sendData(404, 'No such app/nodegroup');
	exit(0);
}

if(!$drivers['v2_order']->removeOrder($input)) {
	$api['output']->sendData(500, $drivers['v2_order']->getError());
	exit(0);
}

$api['output']->sendData(200, 'Order removed');

$history = array(
	'action' => 'REMOVE',
	'app' => $current['app'],
	'nodegroup' => $current['nodegroup'],
	'old_order' => $current['order'],
	'timestamp' => time(),
	'user' => $api['authn']->getUser(),
);

$drivers['v2_order']->setOrderHistory($history);

?>
