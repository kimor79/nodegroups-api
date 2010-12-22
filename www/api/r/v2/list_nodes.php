<?php

include('Nodegroups/includes/ro.inc');

$data = array();
$req = array_merge($_GET, $_POST);

if(isset($req['expression'])) {
	$expression = (get_magic_quotes_gpc()) ? stripslashes($req['expression']) : $req['expression'];

	$details = array(
		array(
			'nodegroup' => 'dynamic_expression',
			'expression' => $expression,
		),
	);

	$use_cache = false;
}

if(isset($req['nodegroup'])) {
	$details = $ng->getNodegroupDetails(array('nodegroup' => $req['nodegroup']));

	$use_cache = true;
	if(!$ops->isYesNo($req['use_cache'], true)) {
		$use_cache = false;
	}
}

if(empty($details)) {
	print($ops->formatOutput($data));
	exit(0);
}

$ops->setContentType($details[0]['nodegroup']);

if($use_cache == true) {
	if(count($details) > 1) {
		foreach($details as $r_details) {
			$data = array_merge($data, $ng->getNodeCache($r_details['nodegroup']));
		}
	} else {
		$data = $ng->getNodeCache($details[0]['nodegroup']);
	}
} else {
	if(count($details) > 1) {
		foreach($details as $r_details) {
			$data = array_merge($data, $ng->parseExpression($r_details['expression']));
		}
	} else {
		$data = $ng->parseExpression($details[0]['expression']);
	}
}

print($ops->formatOutput($data));
?>
