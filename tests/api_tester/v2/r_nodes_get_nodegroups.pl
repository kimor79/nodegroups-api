my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/modify_nodegroup.php';
my $del = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/delete_nodegroup.php';
my $set = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/set_order.php';
my $get = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/nodes/get_nodegroups.php';

$TESTS = [

{
	'description' => 'v2/r/nodes/get_nodegroups.php - Extra fields',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'foo' => 'test',
				'nodegroup' => 'test',
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
	'description' => 'v2/r/nodes/get_nodegroups.php - No params',
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
					'nodegroup' => 'noparams1' . $UNIQUE,
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
	'description' => 'v2/r/nodes/get_nodegroups.php - Invalid nodegroup',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'space space',
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
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 1',
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
				'nodegroup' => 'good1' . $UNIQUE,
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
					'nodegroup' => 'good1' . $UNIQUE,
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
	'description' => 'v2/r/nodes/get_nodegroups.php - no-exist 1',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'noexist' . $UNIQUE,
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
	'description' => 'v2/r/nodes/get_nodegroups.php - No exist 2',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foobar',
				'expression' => '',
				'nodegroup' => 'exist2' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'nodegroup' => 'noexist2' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foobar',
					'expression' => '',
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
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 3',
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
				'nodegroup' => [
					'good3a' . $UNIQUE,
					'good3b' . $UNIQUE,
				],
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
				'records' => bag(
					{
					'inherited' => 0,
					'node' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3b' . $UNIQUE,
					},

					{
					'inherited' => 0,
					'node' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
					},
				),
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

{
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 4',
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
				'nodegroup_re' => $UNIQUE,
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
					'nodegroup' => 'good4' . $UNIQUE,
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
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 5',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good5' . $UNIQUE,
				'expression' => 'good5' . $UNIQUE,
				'nodegroup' => 'good5' . $UNIQUE,
			},
		},

		{
			'uri' => $del,
			'json' => {
				'nodegroup' => 'good5' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'nodegroup' => 'good5' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good5' . $UNIQUE,
					'expression' => 'good5' . $UNIQUE,
					'nodegroup' => 'good5' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
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
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 6',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good6a' . $UNIQUE,
				'expression' => 'good6' . $UNIQUE,
				'nodegroup' => 'good6a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good6b' . $UNIQUE,
				'expression' => 'good6' . $UNIQUE,
				'nodegroup' => 'good6b' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'app' => 'good6' . $UNIQUE,
				'node' => 'good6' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good6a' . $UNIQUE,
					'expression' => 'good6' . $UNIQUE,
					'nodegroup' => 'good6a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good6b' . $UNIQUE,
					'expression' => 'good6' . $UNIQUE,
					'nodegroup' => 'good6b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => [
					{
					'app' => 'good6' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good6' . $UNIQUE,
					'nodegroup' => 'good6a' . $UNIQUE,
					'order' => 100,
					},

					{
					'app' => 'good6' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good6' . $UNIQUE,
					'nodegroup' => 'good6b' . $UNIQUE,
					'order' => 100,
					},
				],
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

{
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 7',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good7a' . $UNIQUE,
				'expression' => 'good7' . $UNIQUE,
				'nodegroup' => 'good7a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good7b' . $UNIQUE,
				'expression' => 'good7' . $UNIQUE,
				'nodegroup' => 'good7b' . $UNIQUE,
			},
		},

		{
			'uri' => $set,
			'json' => {
				'app' => 'good7' . $UNIQUE,
				'nodegroup' => 'good7b' . $UNIQUE,
				'order' => 1,
			},
		},

		{
			'uri' => $get,
			'get' => {
				'sortField' => 'order',
			},
			'json' => {
				'app' => 'good7' . $UNIQUE,
				'node' => 'good7' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good7a' . $UNIQUE,
					'expression' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good7b' . $UNIQUE,
					'expression' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7b' . $UNIQUE,
					'order' => 1,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => [
					{
					'app' => 'good7' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7b' . $UNIQUE,
					'order' => 1,
					},

					{
					'app' => 'good7' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7a' . $UNIQUE,
					'order' => 100,
					},
				],
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'asc',
				'sortField' => 'order',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

{
	'description' => 'v2/r/nodes/get_nodegroups.php - Good 8',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good8a' . $UNIQUE,
				'expression' => 'good8' . $UNIQUE,
				'nodegroup' => 'good8a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good8b' . $UNIQUE,
				'expression' => 'good8' . $UNIQUE,
				'nodegroup' => 'good8b' . $UNIQUE,
			},
		},

		{
			'uri' => $set,
			'json' => {
				'app' => 'good8' . $UNIQUE,
				'nodegroup' => 'good8b' . $UNIQUE,
				'order' => 1,
			},
		},

		{
			'uri' => $get,
			'get' => {
				'sortField' => 'order',
				'subDetails' => 1,
			},
			'json' => {
				'app' => 'good8' . $UNIQUE,
				'node' => 'good8' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good8a' . $UNIQUE,
					'expression' => 'good8' . $UNIQUE,
					'nodegroup' => 'good8a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good8b' . $UNIQUE,
					'expression' => 'good8' . $UNIQUE,
					'nodegroup' => 'good8b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'good8' . $UNIQUE,
					'nodegroup' => 'good8b' . $UNIQUE,
					'order' => 1,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => [
					{
					'app' => 'good8' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good8' . $UNIQUE,
					'nodegroup' => {
						'description' =>
							'good8b' . $UNIQUE,
						'expression' =>
							'good8' . $UNIQUE,
						'nodegroup' =>
							'good8b' . $UNIQUE,
					},
					'order' => 1,
					},

					{
					'app' => 'good8' . $UNIQUE,
					'inherited' => 0,
					'node' => 'good8' . $UNIQUE,
					'nodegroup' => {
						'description' =>
							'good8a' . $UNIQUE,
						'expression' =>
							'good8' . $UNIQUE,
						'nodegroup' =>
							'good8a' . $UNIQUE,
					},
					'order' => 100,
					},
				],
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'asc',
				'sortField' => 'order',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

];
