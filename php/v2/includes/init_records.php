<?php

define('API_PRODUCER_MYAPP', 'nodegroups_api_v2');

if(!isset($api_loader)) {
	$api_loader = array(
		'input_file' => __DIR__ . '/../classes/input.php',
		'input_class' => 'NodegroupsAPIV2Input',
	);
}

include 'api_producer/v2/includes/init_records.php';

include __DIR__ . '/../classes/expression.php';

$ngexpr = new NodegroupsAPIV2Expression();

/**
 * Remove the @ from a nodegroup name
 * @param string $nodegroup
 * @return string
 */
function stripAt($nodegroup) {
	return ltrim($nodegroup, '@');
}

?>
