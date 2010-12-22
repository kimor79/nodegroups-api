<?php

include('Nodegroups/includes/rw.inc');

$s_message = '';

$details = array();
if(isset($_POST['nodegroup']) && $ng->validateNodegroupName($_POST['nodegroup'])) {
	$details = $ng->getNodegroupDetails(array('nodegroup' => $_POST['nodegroup']));
	$details = reset($details);
} else {
	print($ops->formatWriteOutput('400', 'Invalid nodegroup name'));
	exit(0);
}

$child_nodegroups = array();
$query_opts = array();

if(empty($details)) {
	if($ops->isBlank($_POST['description'])) {
		print($ops->formatWriteOutput('400', 'Missing description'));
		exit(0);
	}

	$query_opts[] = sprintf("nodegroup='%s'", mysql_real_escape_string($_POST['nodegroup']));

	$nodegroup = $_POST['nodegroup'];
	$query = 'INSERT INTO';
	$post_query = '';
	$s_message = 'added';
} else {
	if($ops->isYesNo($_POST['create_only'], false)) {
		print($ops->formatWriteOutput('400', 'Nodegroup already exists'));
		exit(0);
	}

	$nodegroup = $details['nodegroup'];
	$query = 'UPDATE';
	$post_query = sprintf(" WHERE nodegroup='%s'", mysql_real_escape_string($nodegroup));
	$s_message = 'updated';
}

if(!$ops->isBlank($_POST['description'])) {
	$description = (get_magic_quotes_gpc()) ? stripslashes($_POST['description']) : $_POST['description'];
	$query_opts[] = sprintf("description='%s'", mysql_real_escape_string($description));
}

if(isset($_POST['expression'])) {
	if($ops->isBlank($_POST['expression'])) {
		$query_opts[] = "expression=''";
	} elseif($ng->validateExpression($_POST['expression'])) {
		$p_expression = $ng->sanitizeExpression($_POST['expression']);
		preg_match_all('/\#?\@[-\w\.]+(\,|\)|$)/', $p_expression, $expr_nodegroups);
		foreach($expr_nodegroups[0] as $t_ng) {
			if(substr($t_ng, 0, 1) === '#') {
				continue;
			}

			$t_ng = substr($t_ng, 1);
			$t_ng = rtrim($t_ng, ',)');
			if(!$ng->validateNodegroupName($t_ng)) {
				print($ops->formatWriteOutput('400', 'Expression contains an invalid nodegroup name'));
				exit(0);
			}
			$t_ng_details = $ng->getNodegroupDetails(array('nodegroup' => $t_ng));
			if(empty($t_ng_details)) {
				print($ops->formatWriteOutput('400', "Nodegroup $t_ng does not exist"));
				exit(0);
			}

			$child_nodegroups[] = $t_ng;
		}

		$expression = (get_magic_quotes_gpc()) ? stripslashes($_POST['expression']) : $_POST['expression'];
		$query_opts[] = sprintf("expression='%s'", mysql_real_escape_string($expression));
	} else {
		print($ops->formatWriteOutput('400', 'Invalid expression'));
		exit(0);
	}
}

$query .= ' nodegroups SET ';
$query .= implode(',', $query_opts);
$query .= $post_query;
$result = do_mysql_query($query);

if($result[0] !== true) {
	print($ops->formatWriteOutput('500', $result[1]));
	exit(0);
}

$new_data = $ng->getNodegroupDetails(array('nodegroup' => $nodegroup));
$new_data = reset($new_data);
$ng->updateNodegroupHistory($nodegroup, $details, $new_data);

$nodes = $ng->parseExpression($new_data['expression']);
$ng->populateNodeCache($nodegroup, $nodes);
$ng->populateNodegroupCache($nodegroup, $child_nodegroups);

$ng->populateNodegroupParentsCache($nodegroup);

$message = sprintf("%s %s", $_POST['nodegroup'], $s_message);
print($ops->formatWriteOutput('200', $message, $new_data));
?>
