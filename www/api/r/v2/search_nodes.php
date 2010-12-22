<?php

include('Nodegroups/includes/ro.inc');

$data = array();
$req = array_merge($_GET, $_POST);

$query = 'SELECT node, COUNT(nodegroup) AS nodegroups
	FROM nodes';

if(!$ops->isBlank($req['node'])) {
	$query .= sprintf(" WHERE node LIKE '%s'", mysql_real_escape_string($req['node']));
}

$query .= ' GROUP BY node';
$result = do_mysql_query($query);

if($result[0] === true) {
	while($line = mysql_fetch_assoc($result[1])) {
		$data[] = $line;
	}
}

print($ops->formatOutput($data));
?>
