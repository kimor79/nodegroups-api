<?php

include('Nodegroups/includes/rw.inc');

$details = array();
if(isset($_POST['nodegroup']) && $ng->validateNodegroupName($_POST['nodegroup'])) {
	$details = $ng->getNodegroupDetails(array('nodegroup' => $_POST['nodegroup']));
	$details = reset($details);
} else {
	print($ops->formatWriteOutput('400', 'Invalid nodegroup nodegroup'));
	exit(0);
}

if(empty($details)) {
	print($ops->formatWriteOutput('400', 'Nodegroup does not exist'));
	exit(0);
}

if(!$ops->isYesNo($_POST['delete'], false)) {
	print($ops->formatWriteOutput('400', 'delete=yes also needs to be passed to this api'));
	exit(0);
}

if($ops->isBlank($_POST['app'])) {
	print($ops->formatWriteOutput('400', 'Missing app'));
	exit(0);
}

$app = (get_magic_quotes_gpc()) ? stripslashes($_POST['app']) : $_POST['app'];
$query_opts[] = sprintf("app='%s'", mysql_real_escape_string($app));

$nodegroup = (get_magic_quotes_gpc()) ? stripslashes($_POST['nodegroup']) : $_POST['nodegroup'];
$query_opts[] = sprintf("nodegroup='%s'", mysql_real_escape_string($nodegroup));

$query = sprintf("DELETE FROM priority WHERE %s", implode(' AND ', $query_opts));
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

$new_data = $ng->getNodegroupDetails(array('nodegroup' => $_POST['nodegroup']));
$new_data = reset($new_data);
$ng->updateNodegroupHistory($nodegroup, $details, $new_data);

print($ops->formatWriteOutput('200', 'Priority deleted'));
?>
