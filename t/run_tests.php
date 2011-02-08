#!/usr/bin/php
<?php

require_once('./nodegroups_data.php');
require_once('./tests.php');

require_once('../php/v1/classes/expression.php');
$ngexpr = new NodegroupsApiExpression();

require_once('../php/v1/drivers/test_inmemory.php');
$driver = new NodegroupsApiDriverTestInMemory();

foreach($nodegroups as $nodegroup => $data) {
	$driver->addNodegroup($nodegroup, $data);

	$parsed = $ngexpr->parseExpression($data['expression']);

	$driver->setNodes($nodegroup, $parsed['nodes']);
}

$pass = 0;

foreach($tests as $pos => $test) {
	printf("NOW ON %d: %s\n", $pos, $test['expr']);
	$results = array(
		'nodegroups' => array('expected' => array(), 'got' => array()),
		'nodes' => array('expected' => array(), 'got' => array()),
	);
	$parsed = $ngexpr->parseExpression($test['expr']);

	$count = array(
		'nodegroups' => array(
			'expected' => count($test['nodegroups']),
			'got' => count($parsed['nodegroups']),
		),
		'nodes' => array(
			'expected' => count($test['nodes']),
			'got' => count($parsed['nodes']),
		),
	);

	$t_nodegroups = array_unique(array_merge(
		$parsed['nodegroups'], $test['nodegroups']));

	foreach($t_nodegroups as $ng) {
		if(!in_array($ng, $parsed['nodegroups'])) {
			$results['nodegroups']['got'][$ng] = $ng;
		}

		if(!in_array($ng, $test['nodegroups'])) {
			$results['nodegroups']['expected'][$ng] = $ng;
		}
	}

	$t_nodes = array_unique(array_merge(
		$parsed['nodes'], $test['nodes']));

	foreach($t_nodes as $n) {
		if(!in_array($n, $parsed['nodes'])) {
			$results['nodes']['got'][$n] = $n;
		}

		if(!in_array($n, $test['nodes'])) {
			$results['nodes']['expected'][$n] = $n;
		}
	}

	if(empty($results['nodegroups']['got'])
			&& empty($results['nodegroups']['expected'])
			&& empty($results['nodes']['expected'])
			&& empty($results['nodes']['got'])
			&& $count['nodegroups']['got'] ===
				$count['nodegroups']['expected']
			&& $count['nodes']['got'] ===
				$count['nodes']['expected']
			) {
		$pass++;

		printf("PASS (%d): %s\n", $pos, $test['expr']);
		continue;
	}

	fwrite(STDERR, sprintf("FAIL (%d): %s\n", $pos, $test['expr']));

	fwrite(STDERR, sprintf("  ng got\t(%d): %s\n",
		$count['nodegroups']['got'],
		implode(', ', $parsed['nodegroups'])));
	fwrite(STDERR, sprintf("  ng expected\t(%d): %s\n",
		$count['nodegroups']['expected'],
		implode(', ', $test['nodegroups'])));

	fwrite(STDERR, sprintf("  got\t\t(%d): %s\n",
		$count['nodes']['got'],
		implode(', ', $parsed['nodes'])));
	fwrite(STDERR, sprintf("  expected\t(%d): %s\n",
		$count['nodes']['expected'],
		implode(', ', $test['nodes'])));
}

printf("\nPASS: %d/%d\n", $pass, count($tests));

?>
