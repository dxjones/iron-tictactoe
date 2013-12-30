<? // iron_callback_worker.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_worker.phar';
require_once 'phar://./iron-io/iron_cache.phar';
require_once 'phar://./iron-io/iron_mq.phar';

// "iron.json" contains credentials
$worker = new IronWorker();
$queue = new IronMQ();
$cache = new IronCache();
$cache->setCacheName('tictactoe_board');

if ($debug) { echo "Hello World, from iron_callback_worker\n"; }

$payload = getPayload();
$child = $payload->board;

$result = $cache->get($child);
if ($result === NULL) {
	printf("ERROR: child = %s, not found in cache\n", $child);
	die('FAIL');
}
$value = json_decode($result->value);
if ($value->pending) {
	printf("ERROR: child = %s, cache is still pending\n", $child);
	die('FAIL');
}

while (TRUE) {
	$message = $queue->getMessage($child);
	if ($message === NULL) {
		if ($debug) { printf("queue %s is empty\n", $child); }
		break;
	}
	$parent = $message->body;
	if ($debug) { printf("callback: child = %s => parent = %s\n", $child, $parent); }
	$worker->postTask('iron_update', array('child' => $child, 'parent' => $parent));
	$result = $queue->deleteMessage($child, $message->id);
}

if ($debug) {
	echo "Goodbye, from iron_callback_worker\n";
	$toc = microtime(TRUE);
	printf("elapsed time = %.6f sec\n", $toc - $tic);
}
