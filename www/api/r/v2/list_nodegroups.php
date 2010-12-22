<?php

include('Nodegroups/includes/ro.inc');

$data = array();
$priority = false;
$search = array_merge($_GET, $_POST);

if(!$ops->isBlank($search['priority'])) {
	$priority = $search['priority'];
	unset($search['priority']);
}

$data = $ng->getNodegroupDetails($search);

if($priority) {
	$p_data = array();
	foreach($data as $nodegroup) {
		$t_data = $nodegroup;

		$t_data['priority'] = 50;
		foreach($nodegroup['priority'] as $app) {
			if($app['app'] == $priority) {
				$t_data['priority'] = $app['priority'];
				break;
			}
		}

		$p_data[] = $t_data;
	}

	$data = $p_data;
}

print($ops->formatOutput($data));
?>
