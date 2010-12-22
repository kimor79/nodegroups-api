<?php

include('Nodegroups/includes/ro.inc');

$data = array();
$req = array_merge($_GET, $_POST);

$data = $ng->getEvents($req);

print($ops->formatOutput($data));
?>
