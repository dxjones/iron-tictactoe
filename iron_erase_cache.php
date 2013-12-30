<? // iron_erase_cache.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_cache.phar';


function clearCache($cache, $name, $debug = FALSE) {
	$cache->setCacheName($name);

	$result = $cache->put('alpha', 'hello world!');
	if ($debug) {
		printf("put =\n");
		print_r($result);
	}
	
	$result = $cache->clear();
	if ($debug) {
		printf("clear =\n");
		print_r($result);
	}
	
	$result = $cache->get('alpha');
	if ($debug) {
		printf("get =\n");
		print_r($result);
		if (NULL === $result) {
			printf("result === NULL\n");
		}
	}
}


// "iron.json" contains credentials
$cache = new IronCache();

clearCache($cache, 'tictactoe_board', $debug);


$toc = microtime(TRUE);
printf("elapsed time = %.6f sec\n", $toc - $tic);
