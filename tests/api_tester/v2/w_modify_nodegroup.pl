my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/modify_nodegroup.php';

$TESTS = [

{
	'description' => 'v2/w/modify_nodegroup.php - Missing fields',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'foo' => $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Extra fields',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'test',
				'expression' => 'test',
				'foo' => 'test',
				'nodegroup' => 'test',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Extra'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - GET',
	'uri' => $mod,
	'requests' => [
		{
			'get' => {
				'description' => 'test',
				'expression' => 'test',
				'nodegroup' => 'test',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Missing few',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'test',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Invalid nodegroup',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'test',
				'nodegroup' => 'space space',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Invalid expression',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'test',
				'expression' => '&&',
				'nodegroup' => 'expression1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Good 1',
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
			'uri' => $mod,
			'json' => {
				'expression' => 'foo',
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
				'details' => {
					'description' => 'good1' . $UNIQUE,
					'expression' => 'foo',
					'nodegroup' => 'good1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - no-exist 1',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'noexist',
				'expression' => 'noexist',
				'nodegroup' => 'noexist' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Multiple expression 1',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'description' => 'multi_ng',
				'expression' => 'multi_ng',
				'nodegroup' => [ 
					'the_first',
					'the_second',
				],
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => 'Multiple "nodegroup" not allowed',
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - No exist 2',
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
			'uri' => $mod,
			'json' => {
				'expression' => 'foo',
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
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - No such child',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foo',
				'expression' => '',
				'nodegroup' => 'childtest1' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'expression' => '@noexist' . $UNIQUE,
				'nodegroup' => 'childtest1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foo',
					'expression' => '',
					'nodegroup' => 'childtest1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => bag('noexist' . $UNIQUE),
				'message' => re('exist'),
				'status' => 424,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Self child',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foo',
				'expression' => '',
				'nodegroup' => 'childself1' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => 'foo',
				'expression' => '@childself1' . $UNIQUE,
				'nodegroup' => 'childself1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foo',
					'expression' => '',
					'nodegroup' => 'childself1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => any({}, []),
				'message' => re('recursion'),
				'status' => 418,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Good 3',
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
			'uri' => $mod,
			'json' => {
				'description' => 'good3b' . $UNIQUE,
				'expression' => 'good3b' . $UNIQUE,
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => 'good3b' . $UNIQUE,
				'expression' => 'good3b' . $UNIQUE,
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},
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
					'description' => 'good3b' . $UNIQUE,
					'expression' => 'good3b' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good3b' . $UNIQUE,
					'expression' => 'good3b' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Good 4',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'description',
				'expression' => 'expression',
				'nodegroup' => 'good4a' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => 'description1',
				'expression' => 'expression1',
				'force' => 1,
				'nodegroup' => 'good4a' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => 'description1',
				'expression' => 'expression1',
				'force' => 0,
				'nodegroup' => 'good4a' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'description',
					'expression' => 'expression',
					'nodegroup' => 'good4a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'description1',
					'expression' => 'expression1',
					'nodegroup' => 'good4a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'description1',
					'expression' => 'expression1',
					'nodegroup' => 'good4a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Good 5 - Empty expr',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'description',
				'expression' => 'expression',
				'nodegroup' => 'good5' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'expression' => '',
				'nodegroup' => 'good5' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'description',
					'expression' => 'expression',
					'nodegroup' => 'good5' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'description',
					'expression' => '',
					'nodegroup' => 'good5' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Good 6',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'description',
				'expression' => 'expression',
				'nodegroup' => 'good6a' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'description1',
				'expression' => '@good6a' . $UNIQUE,
				'nodegroup' => 'good6b' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'expression' => 'expression1',
				'nodegroup' => 'good6a' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'description',
					'expression' => 'expression',
					'nodegroup' => 'good6a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'description1',
					'expression' => '@good6a' . $UNIQUE,
					'nodegroup' => 'good6b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'description',
					'expression' => 'expression1',
					'nodegroup' => 'good6a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/modify_nodegroup.php - Empty description',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foo',
				'expression' => 'foo',
				'nodegroup' => 'emptydesc1' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'description' => '',
				'nodegroup' => 'emptydesc1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foo',
					'expression' => 'foo',
					'nodegroup' => 'emptydesc1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'foo',
					'expression' => 'foo',
					'nodegroup' => 'emptydesc1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

];
