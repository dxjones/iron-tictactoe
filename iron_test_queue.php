<? // iron_test_queue.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_mq.phar';

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

function initQueue($name, $debug = FALSE) {
	$queue = new IronMQ();
	printf("Queue: %s\n", $name);

	$result = $queue->postMessage($name,'XOXO');
	if ($debug) { printResult('postMessage', $result); }

	$message = $queue->getMessage($name);
	if ($debug) { printResult('getMessage', $message); }

	$result = $queue->deleteMessage($name, $message->id);
	if ($debug) { printResult('deleteMessage', $result); }

	$result = $queue->getMessage($name);
	if ($debug) { printResult('getMessage', $result); }
}

initQueue('test_queue', $debug);

$toc = microtime(TRUE);
printf("elapsed time = %.6f sec\n", $toc - $tic);
