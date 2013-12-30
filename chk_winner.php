<? // chk_winner.php

include('tictactoe_functions.php');

$board_list = array('XOOXOXOXX');

foreach ($board_list as $b) {
	$w = winner($b);
	printf("board = %s,  winner = %s\n", $b, $w);
}
