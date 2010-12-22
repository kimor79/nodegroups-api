<?php

include('Nodegroups/includes/ro.inc');

$data = array();

$query = 'SELECT nodegroups.nodegroup AS name,
	nodegroups.description,
	nodegroups.expression
	FROM nodegroups';
$query_opts = array();

if($ng->validateNodeName($_GET['node'])) {
	$query .= ' LEFT JOIN nodes USING (nodegroup)';
	$query_opts[] = sprintf("nodes.node='%s'", $_GET['node']);
}

if($ng->validateNodegroupName($_GET['nodegroup'])) {
	$query_opts[] = sprintf("nodegroups.nodegroup LIKE '%%%s%%'", $_GET['nodegroup']);
}

if(!empty($query_opts)) {
	$query .= sprintf(" WHERE %s", implode(' AND ', $query_opts));
}

$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatOutput($result[1]));
	exit(0);
}

while($line = mysql_fetch_assoc($result[1])) {
	$data[] = $line;
}

print($ops->formatOutput($data));
?>
