my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/modify_nodegroup.php';
my $del = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/delete_nodegroup.php';
my $get = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/get_node_counts.php';

$TESTS = [

{
	'description' => 'v2/r/get_node_counts.php - Extra fields',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'foo' => 'test',
				'node' => 'test',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'records' => any({}, []),
				'message' => re('Extra'),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 400,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - No params',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'noparams1' . $UNIQUE,
				'expression' => 'noparams1' . $UNIQUE,
				'nodegroup' => 'noparams1' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'noparams1' . $UNIQUE,
					'expression' => 'noparams1' . $UNIQUE,
					'nodegroup' => 'noparams1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => superbagof({
					'inherited' => 0,
					'node' => 'noparams1' . $UNIQUE,
					'nodegroups' => 1,
				}),
				'message' => ignore(),
				'recordsReturned' => re('\d+'),
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - Invalid node',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'node' => 'space space',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'records' => any({}, []),
				'message' => re('Invalid'),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 400,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - Good 1',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good1' . $UNIQUE,
				'expression' => 'good1' . $UNIQUE,
				'nodegroup' => 'good1' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'node' => 'good1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good1' . $UNIQUE,
					'expression' => 'good1' . $UNIQUE,
					'nodegroup' => 'good1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => [{
					'inherited' => 0,
					'node' => 'good1' . $UNIQUE,
					'nodegroups' => 1,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'startIndex' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - no-exist 1',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'node' => 'noexist' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - No exist 2',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foobar',
				'expression' => 'exist2' . $UNIQUE,
				'nodegroup' => 'exist2' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'node' => 'noexist2' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foobar',
					'expression' => 'exist2' . $UNIQUE,,
					'nodegroup' => 'exist2' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - Good 3',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good3a' . $UNIQUE,
				'expression' => 'good3a' . $UNIQUE,
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good3a' . $UNIQUE,
				'expression' => 'good3a' . $UNIQUE,
				'nodegroup' => 'good3b' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'node' => 'good3a' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good3a' . $UNIQUE,
					'expression' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good3a' . $UNIQUE,
					'expression' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => [{
					'inherited' => 0,
					'node' => 'good3a' . $UNIQUE,
					'nodegroups' => 2,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - Good 4',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good4' . $UNIQUE,
				'expression' => 'good4' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'node_re' => $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good4' . $UNIQUE,
					'expression' => 'good4' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => superbagof({
					'inherited' => 0,
					'node' => 'good4' . $UNIQUE,
					'nodegroups' => 1,
				}),
				'message' => ignore(),
				'recordsReturned' => re('\d+'),
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

{
	'description' => 'v2/r/get_node_counts.php - Good 5',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good5a' . $UNIQUE,
				'expression' => 'good5a' . $UNIQUE,
				'nodegroup' => 'good5a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good5a' . $UNIQUE,
				'expression' => '@good5a' . $UNIQUE,
				'nodegroup' => 'good5b' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'node' => 'good5a' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good5a' . $UNIQUE,
					'expression' => 'good5a' . $UNIQUE,
					'nodegroup' => 'good5a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good5a' . $UNIQUE,
					'expression' => '@good5a' . $UNIQUE,
					'nodegroup' => 'good5b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => [{
					'inherited' => 1,
					'node' => 'good5a' . $UNIQUE,
					'nodegroups' => 2,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

];
