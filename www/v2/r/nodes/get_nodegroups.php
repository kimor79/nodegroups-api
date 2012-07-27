<?php

$drivers_needed = array(
	'v2_nodes' => 'ro',
	'v2_order' => 'ro',
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
$nodegroups = array();
$optional = array(
	'app' => 'app',
	'inherited' => 'bool',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
);
$order_fields = array(
	'app',
	'nodegroup',
);
$output_params = array();
$params = array();
$records = array();
$required = array();
$sanitize = array(
	'app' => 'app',
	'inherited' => 'bool_false',
	'node' => '_array_node',
	'node_re' => '_array_',
	'nodegroup' => '_array_nodegroup_name',
	'nodegroup_re' => '_array_',
);
$sort = array();
$sort_dir = SORT_ASC;
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

if(!array_key_exists('app', $input)) {
	$driver_params['v2_nodes'] = array_intersect_key($params,
		$drivers['v2_nodes']->getParameters());
	$errors = $drivers['v2_nodes']->setParameters(
		$driver_params['v2_nodes']);
	if($errors) {
		$api['output']->sendData(array(), 0,
			400, implode("\n", $errors));
		exit(0);
	}
} else {
	$t_driver = clone $drivers['v2_nodes'];
	$driver_params['t_driver'] = array_intersect_key($params,
		$t_driver->getParameters());
	$errors = $t_driver->setParameters($driver_params['t_driver']);
	if($errors) {
		$api['output']->sendData(array(), 0,
			400, implode("\n", $errors));
		exit(0);
	}
}

$data = $drivers['v2_nodes']->buildQuery($input, $fields);

$records = $drivers['v2_nodes']->getNodes($data);
if(!is_array($records)) {
	$api['output']->sendData(array(), 0,
		500, $drivers['v2_nodes']->getError());
	exit(0);
}

$total = $drivers['v2_nodes']->getCount();

if(array_key_exists('app', $input)) {
	while(list($junk, $record) = each($records)) {
		$nodegroups[$record['nodegroup']] = 100; // Default order
	}
	reset($records);

	$order_input = array(
		'app' => $input['app'],
		'nodegroup' => array_keys($nodegroups),
	);

	$data = $drivers['v2_order']->buildQuery($order_input, $order_fields);

	$orderings = $drivers['v2_order']->getOrderings($data);
	if(!is_array($orderings)) {
		$api['output']->sendData(array(), 0,
			500, $drivers['v2_order']->getError());
		exit(0);
	}

	while(list($junk, $order) = each($orderings)) {
		$nodegroups[$order['nodegroup']] = $order['order'];
	}
	reset($orderings);

	while(list($key, $record) = each($records)) {
		$records[$key]['app'] = $input['app'];
		$records[$key]['order'] = $nodegroups[$record['nodegroup']];

		$sort[$key] = $records[$key][$params['sortField']];
	}
	reset($records);

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

	$records = array_slice($records,
		$params['startIndex'], $params['numResults']);
}

$api['output']->sendData($records, $total, 200, 'OK');

?>
