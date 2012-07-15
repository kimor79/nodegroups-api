my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/set_order.php';

$TESTS = [

{
	'description' => 'v2/w/set_order.php - Missing fields',
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
				'details' => ignore(),
				'message' => re('Missing'),
				'status' => 400,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Extra fields',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'test',
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
	'description' => 'v2/w/set_order.php - Missing few',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'nodegroup' => 'test',
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
	'description' => 'v2/w/set_order.php - Invalid nodegroup',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'test',
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
	'description' => 'v2/w/set_order.php - Invalid app (length)',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'a' x 1025,
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
	'description' => 'v2/w/set_order.php - Invalid order',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'test',
				'nodegroup' => 'expression1' . $UNIQUE,
				'order' => 'foo',
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
	'description' => 'v2/w/set_order.php - Good 1',
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
				'app' => 'app-good1' . $UNIQUE,
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
					'app' => 'app-good1' . $UNIQUE,
					'nodegroup' => 'good1' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good 2',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good2' . $UNIQUE,
				'expression' => 'good2' . $UNIQUE,
				'nodegroup' => 'good2' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good2' . $UNIQUE,
				'nodegroup' => 'good2' . $UNIQUE,
				'order' => 5000,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good2' . $UNIQUE,
					'expression' => 'good2' . $UNIQUE,
					'nodegroup' => 'good2' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good2' . $UNIQUE,
					'nodegroup' => 'good2' . $UNIQUE,
					'order' => 5000,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good 3',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good3' . $UNIQUE,
				'expression' => 'good3' . $UNIQUE,
				'nodegroup' => 'good3' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => '{"name":"good3' . $UNIQUE . '","id":1}',
				'nodegroup' => 'good3' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good3' . $UNIQUE,
					'expression' => 'good3' . $UNIQUE,
					'nodegroup' => 'good3' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => '{"name":"good3' . $UNIQUE . '","id":1}',
					'nodegroup' => 'good3' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good 4',
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
			'uri' => $mod,
			'json' => {
				'app' => 'app-good4' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good4' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
			},
		},
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
				'details' => {
					'app' => 'app-good4' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good4' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good 5',
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
			'uri' => $mod,
			'json' => {
				'app' => 'app-good5' . $UNIQUE,
				'nodegroup' => 'good5' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good5' . $UNIQUE,
				'nodegroup' => 'good5' . $UNIQUE,
				'order' => 1,
			},
		},
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
				'details' => {
					'app' => 'app-good5' . $UNIQUE,
					'nodegroup' => 'good5' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good5' . $UNIQUE,
					'nodegroup' => 'good5' . $UNIQUE,
					'order' => 1,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good 6',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good6' . $UNIQUE,
				'expression' => 'good6' . $UNIQUE,
				'nodegroup' => 'good6' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good6' . $UNIQUE,
				'nodegroup' => 'good6' . $UNIQUE,
				'order' => 1,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good6' . $UNIQUE,
				'nodegroup' => 'good6' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good6' . $UNIQUE,
					'expression' => 'good6' . $UNIQUE,
					'nodegroup' => 'good6' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good6' . $UNIQUE,
					'nodegroup' => 'good6' . $UNIQUE,
					'order' => 1,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good6' . $UNIQUE,
					'nodegroup' => 'good6' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - Good ',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good7' . $UNIQUE,
				'expression' => 'good7' . $UNIQUE,
				'nodegroup' => 'good7' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'a' x 1024,
				'nodegroup' => 'good7' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good7' . $UNIQUE,
					'expression' => 'good7' . $UNIQUE,
					'nodegroup' => 'good7' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'a' x 1024,
					'nodegroup' => 'good7' . $UNIQUE,
					'order' => 100,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/set_order.php - no-exist 1',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'test',
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
	'description' => 'v2/w/set_order.php - Multiple nodegroup 1',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => 'test',
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
	'description' => 'v2/w/set_order.php - No exist 2',
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
				'app' => 'test',
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
	'description' => 'v2/w/set_order.php - Empty app',
	'uri' => $mod,
	'requests' => [
		{
			'json' => {
				'app' => '',
				'nodegroup' => 'emptyapp1' . $UNIQUE,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => any({}, []),
				'message' => re('app'),
				'status' => 400,
			},
		},
	],
},

];
