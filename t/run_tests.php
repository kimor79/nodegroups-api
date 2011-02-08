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

	fwrite(STDERR, sprintf("\tng got: %d\n", $count['nodegroups']['got']));
	fwrite(STDERR, sprintf("\tng expected: %d\n",
		$count['nodegroups']['expected']));

	fwrite(STDERR, sprintf("\tgot: %d\n", $count['nodes']['got']));
	fwrite(STDERR, sprintf("\texpected: %d\n",
		$count['nodes']['expected']));

	fwrite(STDERR, sprintf("\tng missing from got: %s\n",
		implode(', ', $parsed['nodegroups'])));
	fwrite(STDERR, sprintf("\tng missing from expected: %s\n",
		implode(', ', $test['nodegroups'])));

	fwrite(STDERR, sprintf("\tmissing from got: %s\n",
		implode(', ', $parsed['nodes'])));
	fwrite(STDERR, sprintf("\tmissing from expected: %s\n",
		implode(', ', $test['nodes'])));
}

printf("\nPASS: %d/%d\n", $pass, count($tests));

?>
