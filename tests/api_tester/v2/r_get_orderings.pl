my $add = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/create_nodegroup.php';
my $mod = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/set_order.php';
my $rm = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/w/remove_order.php';
my $get = 'http://' . $ENV{'MY_VM'} .
	'/nodegroups/api/v2/r/get_orderings.php';

$TESTS = [

{
	'description' => 'v2/r/get_orderings.php - Missing fields',
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
	'description' => 'v2/r/get_orderings.php - Extra fields',
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
	'description' => 'v2/r/get_orderings.php - Missing few',
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
	'description' => 'v2/r/get_orderings.php - Invalid nodegroup',
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
	'description' => 'v2/r/get_orderings.php - Invalid app (length)',
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
	'description' => 'v2/r/get_orderings.php - Good 1',
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
			'uri' => $get,
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
				'records' => [{
					'app' => 'app-good1' . $UNIQUE,
					'nodegroup' => 'good1' . $UNIQUE,
					'order' => 100,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Good 2',
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
			'uri' => $get,
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
				'records' => [{
					'app' => 'app-good2' . $UNIQUE,
					'nodegroup' => 'good2' . $UNIQUE,
					'order' => 5000,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Good 3',
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

		{
			'uri' => $get,
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

		{
			'body' => {
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Never added',
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
			'uri' => $get,
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
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - add remove add remove',
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

		{
			'uri' => $get,
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

		{
			'body' => {
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Good 7',
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
			'uri' => $get,
			'json' => {
				'app' => 'a' x 1024,
				'nodegroup' => 'good7' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'a' x 1024,
				'nodegroup' => 'good7' . $UNIQUE,
				'order' => 2,
			},
		},

		{
			'uri' => $get,
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
				'records' => [{
					'app' => 'a' x 1024,
					'nodegroup' => 'good7' . $UNIQUE,
					'order' => 100,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'a' x 1024,
					'nodegroup' => 'good7' . $UNIQUE,
					'order' => 2,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => [{
					'app' => 'a' x 1024,
					'nodegroup' => 'good7' . $UNIQUE,
					'order' => 2,
				}],
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 1,
			},
		},

	],
},

{
	'description' => 'v2/r/get_orderings.php - no-exist 1',
	'uri' => $get,
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
				'records' => any({}, []),
				'message' => ignore(),
				'recordsReturned' => 0,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => 0,
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Good 4',
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
				'app' => 'app-good4a' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
				'order' => 5000,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good4b' . $UNIQUE,
				'nodegroup' => 'good4' . $UNIQUE,
				'order' => 5000,
			},
		},

		{
			'uri' => $get,
			'get' => {
				'numResults' => 1,
			},
			'json' => {
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
					'app' => 'app-good4a' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'order' => 5000,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good4b' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'order' => 5000,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => superbagof({
					'app' => 'app-good4a' . $UNIQUE,
					'nodegroup' => 'good4' . $UNIQUE,
					'order' => 5000,
				}),
				'message' => ignore(),
				'recordsReturned' => 1,
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

{
	'description' => 'v2/r/get_orderings.php - Good 8',
	'requests' => [
		{
			'uri' => $add,
			'json' => {
				'description' => 'good8' . $UNIQUE,
				'expression' => 'good8' . $UNIQUE,
				'nodegroup' => 'good8' . $UNIQUE,
			},
		},

		{
			'uri' => $mod,
			'json' => {
				'app' => 'app-good8' . $UNIQUE,
				'nodegroup' => 'good8' . $UNIQUE,
				'order' => 5,
			},
		},

		{
			'uri' => $get,
			'json' => {
				'order' => 5,
			},
		},
	],
	'responses' => [
		{
			'body' => {
				'details' => {
					'description' => 'good8' . $UNIQUE,
					'expression' => 'good8' . $UNIQUE,
					'nodegroup' => 'good8' . $UNIQUE,
				},
				'message' => ignore(),
				'status' => 201,
			},
		},

		{
			'body' => {
				'details' => {
					'app' => 'app-good8' . $UNIQUE,
					'nodegroup' => 'good8' . $UNIQUE,
					'order' => 5,
				},
				'message' => ignore(),
				'status' => 200,
			},
		},

		{
			'body' => {
				'records' => superbagof({
					'app' => 'app-good8' . $UNIQUE,
					'nodegroup' => 'good8' . $UNIQUE,
					'order' => 5,
				}),
				'message' => ignore(),
				'recordsReturned' => re('\d+'),
				'sortDir' => 'asc',
				'sortField' => 'app',
				'startIndex' => 0,
				'status' => 200,
				'totalRecords' => re('\d+'),
			},
		},
	],
},

];
