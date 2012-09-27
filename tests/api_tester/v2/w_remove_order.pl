my $add = $ENV{'BASE_URI'} . '/v2/w/create_nodegroup.php';
my $mod = $ENV{'BASE_URI'} . '/v2/w/set_order.php';
my $rm = $ENV{'BASE_URI'} . '/v2/w/remove_order.php';

$TESTS = [

{
	'description' => 'v2/w/remove_order.php - Missing fields',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Extra fields',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - GET',
	'uri' => $rm,
	'requests' => [
		{
			'get' => {
				'app' => 'test',
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
	'description' => 'v2/w/remove_order.php - Missing few',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Invalid nodegroup',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Invalid app (length)',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Good 1',
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

		{
			'uri' => $rm,
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

		{
			'body' => {
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/remove_order.php - Good 2',
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

		{
			'uri' => $rm,
			'json' => {
				'app' => 'app-good2' . $UNIQUE,
				'nodegroup' => 'good2' . $UNIQUE,
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

		{
			'body' => {
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/remove_order.php - Good 3',
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

		{
			'uri' => $rm,
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

		{
			'body' => {
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/remove_order.php - Already removed',
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
			'uri' => $rm,
			'json' => {
				'app' => 'app-good4' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
			},
		},

		{
			'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Never added',
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
			'uri' => $rm,
			'json' => {
				'app' => 'app-good5' . $UNIQUE,
				'nodegroup' => 'good5' . $UNIQUE,
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
				'details' => any({}, []),
				'message' => re('such'),
				'status' => 404,
			},
		},
	],
},

{
	'description' => 'v2/w/remove_order.php - add remove add remove',
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
			'uri' => $rm,
			'json' => {
				'app' => 'app-good6' . $UNIQUE,
				'nodegroup' => 'good6' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good6' . $UNIQUE,
				'nodegroup' => 'good6' . $UNIQUE,
				'order' => 4,
			},
		},

		{
			'uri' => $rm,
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
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good6' . $UNIQUE,
					'nodegroup' => 'good6' . $UNIQUE,
					'order' => 4,
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
	],
},

{
	'description' => 'v2/w/remove_order.php - Good 7',
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

		{
			'uri' => $rm,
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

		{
			'body' => {
				'details' => any({}, []),
				'message' => ignore(),
				'status' => 200,
			},
		},
	],
},

{
	'description' => 'v2/w/remove_order.php - no-exist 1',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Multiple nodegroup 1',
	'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - No exist 2',
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
			'uri' => $rm,
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
	'description' => 'v2/w/remove_order.php - Empty app',
	'uri' => $rm,
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
