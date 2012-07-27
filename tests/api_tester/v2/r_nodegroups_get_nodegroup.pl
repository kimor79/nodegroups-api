my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $del = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/delete_nodegroup.php';
my $get = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/nodegroups/get_nodegroup.php';

$TESTS = [

{
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Missing fields',
	'uri' => $get,
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
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Extra fields',
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
				'details' => any({}, []),
				'message' => re('Extra'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Missing few',
	'uri' => $get,
	'requests' => [
		{
			'json' => {},
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
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Invalid nodegroup',
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
				'details' => any({}, []),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Add Get Del Get',
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

		{
			'uri' => $del,
			'json' => {
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
				'details' => {
					'description' => 'good1' . $UNIQUE,
					'expression' => 'good1' . $UNIQUE,
					'nodegroup' => 'good1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
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
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_nodegroup.php - no-exist 1',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'noexist' . $UNIQUE,
			},
		},

		{
			'json' => {
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
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Multiple nodegroup 1',
	'uri' => $get,
	'requests' => [
		{
			'json' => {
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
	'description' => 'v2/r/nodegroups/get_nodegroup.php - No exist 2',
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
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/r/nodegroups/get_nodegroup.php - Good 3',
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
			'uri' => $get,
			'json' => {
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},

		{
			'uri' => $get,
			'json' => {
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
					'description' => 'good3a' . $UNIQUE,
					'expression' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'good3a' . $UNIQUE,
					'expression' => 'good3a' . $UNIQUE,
					'nodegroup' => 'good3a' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

];
