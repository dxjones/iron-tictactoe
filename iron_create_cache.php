<? // iron_create_cache.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_cache.phar';
// require_once './iron-io/IronCore.class.php';
// require_once './iron-io/IronCache.class.php';

function printResult($label, $result) {
	if ($result === NULL) {
		$msg = 'NULL';
	} else if (isset($result->value)) {
		$msg = $result->value;
	} else {
		$msg = $result->msg;
	}
	printf("%s = %s\n", $label, $msg);
	print_r($result);
}

function initCache($name, $debug = FALSE) {
	$cache = new IronCache();
	$cache->setCacheName($name);
	printf("Cache: %s\n", $name);

	$result = $cache->put('alpha', json_encode(array('board' => 'XOXO', 'status' => 'pending')));
	if ($debug) { printResult('put', $result); }

	$result = $cache->get('alpha');
	if ($debug) { printResult('get', $result); }

	$result = $cache->delete('alpha');
	if ($debug) { printResult('delete', $result); }

	$result = $cache->get('alpha');
	if ($debug) { printResult('get', $result); }
	
	$result = $cache->put('alpha', 5);
	if ($debug) { printResult('put', $result); }
	
	$result = $cache->increment('alpha',-1);
	if ($debug) { printResult('decrement', $result); }

	$result = $cache->get('alpha');
	if ($debug) { printResult('get', $result); }

	$result = $cache->delete('alpha');
	if ($debug) { printResult('delete', $result); }
}

initCache('tictactoe_board', $debug);

$toc = microtime(TRUE);
printf("elapsed time = %.6f sec\n", $toc - $tic);
