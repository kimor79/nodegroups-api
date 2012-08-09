my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/modify_nodegroup.php';
my $del = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/delete_nodegroup.php';
my $get = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/nodegroups/get_history.php';

$TESTS = [

{
	'description' => 'v2/r/nodegroups/get_history.php - Extra fields',
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
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 400,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - No params',
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
				'records' => superbagof(
					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+noparams1" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+noparams1" . $UNIQUE,
					'nodegroup' => 'noparams1' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				),
				'message' => ignore(),
				'recordsReturned' => re('\d+'),
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Invalid nodegroup',
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
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 400,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 1',
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
				'records' => [
					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good1" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+good1" . $UNIQUE,
					'nodegroup' => 'good1' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				],
				'message' => ignore(),
				'recordsReturned' => 1,
				'startIndex' => 0,
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - no-exist 1',
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
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - No exist 2',
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
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 3',
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
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good3a" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+good3a" . $UNIQUE,
					'nodegroup' => 'good3b' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},

					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good3a" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+good3a" . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				),
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 4',
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
				'records' => superbagof(
					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good4" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+good4" . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				),
				'message' => ignore(),
				'recordsReturned' => re('\d+'),
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 5',
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
				'records' => bag(
					{
					'action' => 'DELETE',
					'description' => '@@ -1,1 +0,0 @@' .
						"\n-good5" . $UNIQUE,
					'expression' => '@@ -1,1 +0,0 @@' .
						"\n-good5" . $UNIQUE,
					'nodegroup' => 'good5' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},

					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good5" . $UNIQUE,
					'expression' => '@@ -0,0 +1,1 @@' .
						"\n+good5" . $UNIQUE,
					'nodegroup' => 'good5' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				),
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 6',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good6' . $UNIQUE,
				'expression' => "a\nb",
				'nodegroup' => 'good6' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'nodegroup' => 'good6' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good6' . $UNIQUE,
					'expression' => "a\nb",
					'nodegroup' => 'good6' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'records' => [{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good6" . $UNIQUE,
					'expression' => '@@ -0,0 +1,2 @@' .
						"\n+a\n+b",
					'nodegroup' => 'good6' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_history.php - Good 7',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good7' . $UNIQUE,
				'expression' => "a\nb",
				'nodegroup' => 'good7' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => 'good7' . $UNIQUE,
				'expression' => "a\nb\nc",
				'nodegroup' => 'good7' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'nodegroup' => 'good7' . $UNIQUE,
			},
		}
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good7' . $UNIQUE,
					'expression' => "a\nb",
					'nodegroup' => 'good7' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good7' . $UNIQUE,
					'expression' => "a\nb\nc",
					'nodegroup' => 'good7' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => [
					{
					'action' => 'MODIFY',
					'description' => '',
					'expression' => '@@ -1,2 +1,3 @@' .
						"\n a\n b\n+c",
					'nodegroup' => 'good7' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},

					{
					'action' => 'CREATE',
					'description' => '@@ -0,0 +1,1 @@' .
						"\n+good7" . $UNIQUE,
					'expression' => '@@ -0,0 +1,2 @@' .
						"\n+a\n+b",
					'nodegroup' => 'good7' . $UNIQUE,
					'timestamp' => re('\d+'),
					'user' => ignore(),
					},
				],
				'message' => ignore(),
				'recordsReturned' => 2,
				'sortDir' => 'desc',
				'sortField' => 'timestamp',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 2,
			},
		},
	],
},

];
