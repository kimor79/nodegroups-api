<?php

$drivers_needed = array(
	'v2_events' => 'rw',
	'v2_nodegroups' => 'rw',
	'v2_nodes' => 'rw',
	'v2_order' => 'rw',
);

include 'nodegroups/api/v2/includes/init_details.php';

$current = array();
$defaults = array();
$errors = array();
$event_id = new MongoId() . '';
$exists = array();
$h_desc = '';
$h_expr = '';
$input = array();
$nodes = array();
$optional = array();
$orderings = array();
$output_params = array();
$params = array();
$parents = array();
$required = array(
	'nodegroup' => 'nodegroup_name',
);
$sanitize = array(
	'nodegroup' => 'nodegroup_name',
);
$time = time();

list($input, $params) = $api['input']->getInput(array('input' => 'PJ'));

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

$current = $drivers['v2_nodegroups']->getNodegroupByID($input['nodegroup']);
if(!is_array($current)) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(empty($current)) {
	$api['output']->sendData(404, 'No such nodegroup');
	exit(0);
}

$parents = $drivers['v2_nodegroups']->getParents($input['nodegroup']);
if(!is_array($parents)) {
	$api['output']->sendData(500, 'Current parents: ' .
		$drivers['v2_nodegroups']->getError());
	exit(0);
}

if(!empty($parents['nodegroups'])) {
	$api['output']->sendData(409, 'Nodegroup still in use', array(
		'details' => array_diff(
			$parents['nodegroups'], $parents['inherited']),
	));
	exit(0);
}

$children = $drivers['v2_nodegroups']->getChildren($input['nodegroup']);
if(!is_array($children)) {
	$api['output']->sendData(500, 'Current children: ' .
		$drivers['v2_nodegroups']->getError());
	exit(0);
}

$nodes = $drivers['v2_nodes']->getNodesFromNodegroup($input['nodegroup']);
if(!is_array($nodes)) {
	$api['output']->sendData(500, 'Current nodes: ' .
		$drivers['v2_nodes']->getError());
	exit(0);
}

$orderings = $drivers['v2_order']->getOrderings(array(
	'nodegroup' => array('eq' => array($input['nodegroup']))));
if(!is_array($orderings)) {
	$api['output']->sendData(500, 'Current orderings: ' .
		$drivers['v2_order']->getError());
	exit(0);
}

if(!$drivers['v2_nodegroups']->deleteNodegroup($input['nodegroup'])) {
	$api['output']->sendData(500, $drivers['v2_nodegroups']->getError());
	exit(0);
}

if(!$drivers['v2_nodes']->setNodes($input['nodegroup'],
		array(), array())) {
	$errors = $drivers['v2_nodes']->getError();

	$drivers['v2_nodegroups']->addNodegroup($current);

	$api['output']->sendData(500, 'Unable to set nodes: ' . $errors);
	exit(0);
}

if(!$drivers['v2_nodegroups']->setChildren($input['nodegroup'],
		array(), array())) {
	$errors = $drivers['v2_nodegroups']->getError();

	$drivers['v2_nodegroups']->addNodegroup($current);
	$drivers['v2_nodes']->setNodes($input['nodegroup'],
		$nodes['nodes'], $nodes['inherited']);

	$api['output']->sendData(500, 'Unable to set children: ' . $errors);
	exit(0);
}

if(!$drivers['v2_order']->removeOrderings(
		array('nodegroup' => $input['nodegroup']))) {
	$errors = $drivers['v2_order']->getError();

	$drivers['v2_nodegroups']->addNodegroup($current);
	$drivers['v2_nodes']->setNodes($input['nodegroup'],
		$nodes['nodes'], $nodes['inherited']);
	$drivers['v2_nodegroups']->setChildren($input['nodegroup'],
		$children['nodegroups'], $children['inherited']);

	$api['output']->sendData(500, 'Unable to remove orderings: ' . $errors);
	exit(0);
}

$api['output']->sendData(200, 'Nodegroup deleted');

// Add a newline to the diff so as not to get
// the '\ No newline at end of file'
$h_desc = rtrim(xdiff_string_diff($current['description'] . "\n", ''));
$h_expr = rtrim(xdiff_string_diff($current['expression'] . "\n", ''));

$drivers['v2_nodegroups']->addNodegroupHistory(array(
	'action' => 'DELETE',
	'description' => $h_desc,
	'expression' => $h_expr,
	'nodegroup' => $input['nodegroup'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

$drivers['v2_events']->addEvent(array(
	'event' => 'DELETE',
	'id' => $event_id,
	'nodegroup' => $input['nodegroup'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

$drivers['v2_events']->addEvent(array(
	'event' => 'REMOVE',
	'id' => $event_id,
	'nodegroup' => $input['nodegroup'],
	'nodes' => $nodes['nodes'],
	'timestamp' => $time,
	'user' => $api['authn']->getUser(),
));

?>
