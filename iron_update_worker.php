<? // iron_update_worker.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_worker.phar';
require_once 'phar://./iron-io/iron_cache.phar';
require_once 'phar://./iron-io/iron_mq.phar';

require_once('tictactoe_functions.php');
require_once('tictactoe_email.php');

// "iron.json" contains credentials
$worker = new IronWorker();
$queue = new IronMQ();
$cache = new IronCache();
$cache->setCacheName('tictactoe_board');

if ($debug) { echo "iron_update_worker\n\n"; }

$payload = getPayload();
$child = $payload->child;
$parent = $payload->parent;

$result = $cache->get($child);
$value = json_decode($result->value);
$child_winner = $value->winner;

$result = $cache->get($parent);
$value = json_decode($result->value);
$parent_winner = $value->winner;
$email = $value->email;
$pending = $value->pending;

if ($debug) { printf("update, child %s (%s) => parent %s (%s), pending = %d\n", $child, $child_winner, $parent, $parent_winner, $pending); }

--$pending;
if ($debug) { printf("update, parent pending becomes %d\n", $pending); }

$count = boardcount($parent);
if ($count['O'] < $count['X']) {
	$player = 'O';
	$otherplayer = 'X';
} else {
	$player = 'X';
	$otherplayer = 'O';
}

if (($child_winner == $player)
|| (($child_winner == '.') && ($parent_winner == $otherplayer || $parent_winner == '?'))
|| (($child_winner == $otherplayer) && ($parent_winner == '?'))) {
	$parent_winner = $child_winner;
	$change = true;
	if ($debug) { printf("update, parent winner changes to %s\n", $parent_winner); }
}

if ($debug) { printf("update, cache put %s (%s) pending = %s\n", $parent, $parent_winner, $pending); }
$cache->put($parent, json_encode(array('winner' => $parent_winner, 'pending' => $pending, 'email' => $email)));

if (! $pending) {
	if ($debug) { printf("update, parent is finished, so schedule a callback\n"); }
	$worker->postTask('iron_callback', array('board' => $parent));
	if (! empty($email)) {
		if ($debug) { printf("SEND RESULTS BY EMAIL TO: [%s]\n", $email); }
		tictactoe_email($email, $parent, $parent_winner);
	}
}

if ($debug) {
	$toc = microtime(TRUE);
	printf("iron_update_worker, elapsed time = %.6f sec\n", $toc - $tic);
}
