<? // iron_tictactoe_worker.php
$tic = microtime(TRUE);
$debug = TRUE;

require_once 'phar://./iron-io/iron_worker.phar';
require_once 'phar://./iron-io/iron_cache.phar';
require_once 'phar://./iron-io/iron_mq.phar';

require_once('tictactoe_functions.php');

// "iron.json" contains credentials
$worker = new IronWorker();
$queue = new IronMQ();
$cache = new IronCache();
$cache->setCacheName('tictactoe_board');

if ($debug) {
	echo "iron_tictactoe_worker\n\n";
}

$payload = getPayload();
$parent = $payload->board;
$email = isset($payload->email) ? $payload->email : '';

$count = boardcount($parent);
if ($count['O'] < $count['X']) {
	$player = 'O';
	$otherplayer = 'X';
} else {
	$player = 'X';
	$otherplayer = 'O';
}
if ($debug) { printf("payload: board = %s, player = %s\n", $parent, $player); }

if ($debug) { printf("cache put %s (%s) pending = %d, email = [%s]\n", $parent, '?', $count['.'], $email); }
$cache->put($parent, json_encode(array('winner' => '?', 'pending' => $count['.'], 'email' => $email)));


for ($i = 0 ; $i < 9 ; ++$i) {
	if ($parent[$i] == '.') {
		$child = $parent;
		$child[$i] = $player;
		
		// array_push($callback, array('child' => $child, 'parent' => $parent, 'player' => $player));
		if ($debug) { printf("queue %s, parent = %s\n", $child, $parent); }
		$queue->postMessage($child, $parent);
		
		$result = $cache->get($child);
		if ($result) {
			if ($debug) { printf("cache get: hit %s\n", $child); }
			$value = json_decode($result->value);
			if (! $value->pending) {
				$worker->postTask('iron_callback', array('board' => $child));
			}
			continue;
		} else {
			if ($debug) { printf("cache get: miss %s\n", $child); }
		}
		
		$child_winner = winner($child);
		if ($child_winner == '?') {
			if ($debug) { printf("post task: %s (%s)\n", $child, $otherplayer[$player]); }
			$worker->postTask('iron_tictactoe', array('board' => $child));
		} else {
			if ($debug) {
				printf("Leaf %s = winner %s\n", $child, $child_winner);
			}
			if ($debug) { printf("cache put %s (%s)\n", $child, $child_winner); }
			$cache->put($child, json_encode(array('winner' => $child_winner, 'pending' => 0, 'email' => '')));
			if ($debug) { printf("schedule callback, child = %s\n", $child); }
			$worker->postTask('iron_callback', array('board' => $child));
		}
	}
}


if ($debug) {
	$toc = microtime(TRUE);
	printf("iron_tictactoe_worker, elapsed time = %.6f sec\n", $toc - $tic);
}
