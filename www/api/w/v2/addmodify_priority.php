<?php

include('Nodegroups/includes/rw.inc');

$details = array();
if(isset($_POST['nodegroup']) && $ng->validateNodegroupName($_POST['nodegroup'])) {
	$details = $ng->getNodegroupDetails(array('nodegroup' => $_POST['nodegroup']));
	$details = reset($details);
} else {
	print($ops->formatWriteOutput('400', 'Invalid nodegroup name'));
	exit(0);
}

$query_opts = array();

if(empty($details)) {
	print($ops->formatWriteOutput('400', 'No such nodegroup'));
	exit(0);
}

$query_opts[] = sprintf("nodegroup='%s'", mysql_real_escape_string($_POST['nodegroup']));
$nodegroup = $_POST['nodegroup'];

if(isset($_POST['priority'])) {
	if(ctype_digit($_POST['priority'])) {
		$query_opts[] = sprintf("priority='%d'", $_POST['priority']);
	} else {
		print($ops->formatWriteOutput('400', 'Invalid priority'));
		exit(0);
	}
} else {
	print($ops->formatWriteOutput('400', 'Missing description'));
	exit(0);
}

if(!$ops->isBlank($_POST['app'])) {
	$app = (get_magic_quotes_gpc()) ? stripslashes($_POST['app']) : $_POST['app'];
	$query_opts[] = sprintf("app='%s'", mysql_real_escape_string($app));
} else {
	print($ops->formatWriteOutput('400', 'Missing app'));
	exit(0);
}

$query .= 'INSERT INTO priority SET ';
$query .= implode(',', $query_opts);
$query .= sprintf(" ON DUPLICATE KEY UPDATE priority='%d'", $_POST['priority']);
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

$new_data = $ng->getNodegroupDetails(array('nodegroup' => $nodegroup));
$new_data = reset($new_data);
$ng->updateNodegroupHistory($nodegroup, $details, $new_data);

print($ops->formatWriteOutput('200', 'Priority set', $new_data));
?>
