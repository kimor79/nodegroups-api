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

$nodegroup = $_POST['nodegroup'];

$query = sprintf("DELETE FROM nodegroups WHERE nodegroup='%s'", mysql_real_escape_string($nodegroup));
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

$ng->updateNodegroupHistory($nodegroup, $details, array());

$message = sprintf("%s deleted", $nodegroup);
print($ops->formatWriteOutput('200', $message));
?>
