<?php

include_once('mysql.inc');
include_once('Nodegroups/classes/opsapi_ext.class');
include_once('Nodegroups/classes/nodegroups.class');

$ops = new OpsApiNodegroups();
$ng = new NodeGroups();

if(!$mysql) {
	$mysql = mysql_connect($mysql_host, $mysql_user, $mysql_password);
	mysql_select_db($mysql_db);
}

?>
