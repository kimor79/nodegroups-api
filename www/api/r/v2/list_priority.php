<?php

include('Nodegroups/includes/ro.inc');

$data = array();
$priority = false;
$search = array_merge($_GET, $_POST);

$data = $ng->getNodegroupPriority($search);

print($ops->formatOutput($data));
?>
