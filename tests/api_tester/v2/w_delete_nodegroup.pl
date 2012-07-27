my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $del = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/delete_nodegroup.php';

$TESTS = [

{
	'description' => 'v2/w/delete_nodegroup.php - Missing fields',
	'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - Extra fields',
	'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - Missing few',
	'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - Invalid nodegroup',
	'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - Good 1',
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
			'uri' => $del,
			'json' => {
				'nodegroup' => 'good1' . $UNIQUE,
			},
		},

		{
			'uri' => $add,
			'json' => {
				'description' => 'good1' . $UNIQUE,
				'expression' => 'good1' . $UNIQUE,
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
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
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
				'status' => 201,
			},
		},
	],
},

{
	'description' => 'v2/w/delete_nodegroup.php - no-exist 1',
	'uri' => $del,
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
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/w/delete_nodegroup.php - Multiple nodegroup 1',
	'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - No exist 2',
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
			'uri' => $del,
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
	'description' => 'v2/w/delete_nodegroup.php - is child',
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
			'uri' => $add,
			'json' => {
				'description' => 'foo',
				'expression' => '@childtest1' . $UNIQUE,
				'nodegroup' => 'childtest1b' . $UNIQUE,
			},
		},

		{
			'uri' => $del,
			'json' => {
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
				'details' => {
					'description' => 'foo',
					'expression' =>
						'@childtest1' . $UNIQUE,
					'nodegroup' => 'childtest1b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' =>
					supersetof('childtest1b' . $UNIQUE),
				'message' => re('use'),
				'status' => 409,
			},
		},
	],
},

{
	'description' => 'v2/w/delete_nodegroup.php - Good 3',
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
			'uri' => $del,
			'json' => {
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},

		{
			'uri' => $del,
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

];
