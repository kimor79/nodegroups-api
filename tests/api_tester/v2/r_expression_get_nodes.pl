my $add = $ENV{'BASE_URI'} . '/v2/w/create_nodegroup.php';
my $parse = $ENV{'BASE_URI'} . '/v2/r/expression/get_nodes.php';

$TESTS = [

{
	'description' => 'v2/r/expression/get_nodes.php - Missing fields',
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
				'records' => any({}, []),
				'message' => re('Missing'),
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
	'description' => 'v2/r/expression/get_nodes.php - Extra fields',
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
	'description' => 'v2/r/expression/get_nodes.php - Invalid expression',
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
	'description' => 'v2/r/expression/get_nodes.php - Good 1',
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
				'records' => [{
					'inherited' => 0,
					'node' => 'good1' . $UNIQUE,
					'nodegroup' => '',
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
	'description' => 'v2/r/expression/get_nodes.php - Dupe 1',
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
				'records' => [{
					'inherited' => 0,
					'node' => 'foo',
					'nodegroup' => '',
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
	'description' => 'v2/r/expression/get_nodes.php - Multiple expression 1',
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
				'records' => any({}, []),
				'message' => re('Multiple'),
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
	'description' => 'v2/r/expression/get_nodes.php - Good 2',
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
				'records' => [{
					'inherited' => 1,
					'node' => 'foo',
					'nodegroup' => '', # TODO
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
	'description' => 'v2/r/expression/get_nodes.php - No such child',
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
				'records' => any({}, []),
				'message' => re('noexist' . $UNIQUE),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'node',
				'startIndex' => 0,
				'status' => 424,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/expression/get_nodes.php - Good 4',
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
				'records' => [
					{
						'inherited' => 0,
						'node' => 'a',
						'nodegroup' => '',
					},

					{
						'inherited' => 0,
						'node' => 'b',
						'nodegroup' => '',
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

];
