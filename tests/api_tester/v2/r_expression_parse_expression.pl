my $add = $ENV{'BASE_URI'} . '/v2/w/create_nodegroup.php';
my $parse = $ENV{'BASE_URI'} . '/v2/r/expression/parse_expression.php';

$TESTS = [

{
	'description' => 'v2/r/expression/parse_expression.php - Missing fields',
	'uri' => $parse,
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
	'description' => 'v2/r/expression/parse_expression.php - Extra fields',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => 'test',
				'foo' => 'test',
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
	'description' => 'v2/r/expression/parse_expression.php - Invalid expression',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => '&&',
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
	'description' => 'v2/r/expression/parse_expression.php - Good 1',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => 'good1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'inherited_nodegroups' => any({}, []),
					'inherited_nodes' => any({}, []),
					'nodegroups' => any({}, []),
					'nodes' => [ 'good1' . $UNIQUE ],
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/parse_expression.php - Dupe 1',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => 'foo, foo',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'inherited_nodegroups' => any({}, []),
					'inherited_nodes' => any({}, []),
					'nodegroups' => any({}, []),
					'nodes' => [ 'foo' ],
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/parse_expression.php - Multiple expression 1',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => [ 
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
				'message' => re('Multiple'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/parse_expression.php - Good 2',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'foobar',
				'expression' => 'foo',
				'nodegroup' => 'good2' . $UNIQUE,
			},
		},

		{
			'uri' => $parse,
			'json' => {
				'expression' => '@good2' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'foobar',
					'expression' => 'foo',
					'nodegroup' => 'good2' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'inherited_nodegroups' => any({}, []),
					'inherited_nodes' => [ 'foo' ],
					'nodegroups' => [
						'good2' . $UNIQUE,
					],
					'nodes' => [ 'foo' ],
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/parse_expression.php - No such child',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => '@noexist' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => [ 'noexist' . $UNIQUE ],
				'message' => re('exist'),
				'status' => 424,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/parse_expression.php - Good 4',
	'uri' => $parse,
	'requests' => [
		{
			'json' => {
				'expression' => 'a,b',
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'inherited_nodegroups' => any({}, []),
					'inherited_nodes' => any({}, []),
					'nodegroups' => any({}, []),
					'nodes' => [ 'a', 'b' ],
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

];
