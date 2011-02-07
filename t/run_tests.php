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
	$parsed = $ngexpr->parseExpression($test['expr']);

	$nodes = implode(', ', $parsed['nodes']);

	if($nodes != $test['nodes']) {
		fwrite(STDERR, sprintf("FAIL (%d): ", $pos));
		fwrite(STDERR, sprintf("%s\n\tgot: %s\n\texpected: %s\n",
			$test['expr'], $nodes, $test['nodes']));
	} else {
		printf("PASS (%d): ", $pos);
		printf("%s\n\tgot: %s\n\texpected: %s\n",
			$test['expr'], $nodes, $test['nodes']);
		$pass++;
	}
}

printf("\nPASS: %d/%d\n", $pass, count($tests));

?>
