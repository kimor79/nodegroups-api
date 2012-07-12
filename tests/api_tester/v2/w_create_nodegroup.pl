my $uri = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';

$TESTS = [

{
	'description' => 'v1/w/create_nodegroup.php - Missing fields',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Extra fields',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => re('Extra'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Missing few',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Invalid nodegroup',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Invalid expression',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => re('Invalid'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Good 1',
	'uri' => $uri,
	'requests' => [
		{
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
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Dupe 1',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'dupe1',
				'expression' => 'dupe1',
				'nodegroup' => 'dupe1' . $UNIQUE,
			},
		},

		{
			'json' => {
				'description' => 'dupe1',
				'expression' => 'dupe1',
				'nodegroup' => 'dupe1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'dupe1',
					'expression' => 'dupe1',
					'nodegroup' => 'dupe1' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'description' => 'dupe1',
					'expression' => 'dupe1',
					'nodegroup' => 'dupe1' . $UNIQUE,
				},
				'message' => re('exists'),
				'status' => 409,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Multiple expression 1',
	'uri' => $uri,
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
				'details' => ignore(),
				'message' => 'Multiple "nodegroup" not allowed',
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Good 2',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'foobar',
				'expression' => '',
				'nodegroup' => 'good2' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foobar',
					'expression' => '',
					'nodegroup' => 'good2' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - No such child',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'foo',
				'expression' => '@noexist' . $UNIQUE,
				'nodegroup' => 'childtest1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => ignore(),
				'message' => re('exist'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Self child',
	'uri' => $uri,
	'requests' => [
		{
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
				'details' => ignore(),
				'message' => re('recursion'),
				'status' => 418,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Good 3',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'good3a' . $UNIQUE,
				'expression' => 'good3a' . $UNIQUE,
				'nodegroup' => 'good3a' . $UNIQUE,
			},
		},

		{
			'json' => {
				'description' => 'good3b' . $UNIQUE,
				'expression' => '@good3a' . $UNIQUE,
				'nodegroup' => 'good3b' . $UNIQUE,
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
					'expression' => '@good3a' . $UNIQUE,
					'nodegroup' => 'good3b' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Good 4',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'foobar',
				'expression' => 'a,b',
				'nodegroup' => 'good4' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foobar',
					'expression' => 'a,b',
					'nodegroup' => 'good4' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},
	],
},

{
	'description' => 'v1/w/create_nodegroup.php - Empty nodegroup',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => 'foobar',
				'expression' => 'foobar',
				'nodegroup' => '',
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
	'description' => 'v1/w/create_nodegroup.php - Empty description',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => '',
				'expression' => 'foobar',
				'nodegroup' => 'empty' . $UNIQUE,
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
	'description' => 'v1/w/create_nodegroup.php - Empty fields',
	'uri' => $uri,
	'requests' => [
		{
			'json' => {
				'description' => '',
				'expression' => 'foobar',
				'nodegroup' => '',
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

];
