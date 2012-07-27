<?php

$drivers_needed = array(
	'v2_nodegroups' => 'ro',
);

include 'nodegroups/api/v2/includes/init_details.php';

$defaults = array();
$details = array();
$errors = array();
$input = array();
$optional = array();
$output_params = array();
$params = array();
$required = array(
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

$input = $api['input']->removeValues($input);
$input = array_merge($defaults, $input);
$input = $api['input']->gpcSlashInput($input);

$errors = $api['input']->validateInput($input, $required, $optional, true);
if($errors) {
	$api['output']->sendData(400, implode("\n", $errors));
	exit(0);
}

$input = $api['input']->sanitizeInput($input, $sanitize);

$details = $drivers['v2_nodegroups']->getNodegroupByID($input['nodegroup']);
if(!is_array($details)) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(empty($details)) {
	$api['output']->sendData(404, 'No such nodegroup');
	exit(0);
}

$api['output']->sendData(200, 'OK', array('details' => $details));

?>
