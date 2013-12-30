<? // tictactoe_functions.php

function boardcount($board) {
	$count = array('X' => 0, 'O' => 0, '.' => 0);
	if (strlen($board) == 9) {
		for ($i = 0 ; $i < 9 ; ++$i) {
			++$count[ $board[$i] ];
		}
	}
	return $count;
}
	
function winner($board) {
	if (iswinner($board,'X')) {
		return 'X';
	} else if (iswinner($board,'O')) {
		return 'O';
	} else if (strpos($board,'.') === false) {
		return '.';
	} else {
		return '?';
	}
}

function iswinner($board,$player) {
	if ($board[0] == $player) {
		if ($board[1] == $player
		&&  $board[2] == $player) {
			return true;
		}
		if ($board[3] == $player
		&&  $board[6] == $player) {
			return true;
		}
		if ($board[4] == $player
		&&  $board[8] == $player) {
			return true;
		}
	}
	if ($board[1] == $player
	&&  $board[4] == $player
	&&  $board[7] == $player) {
		return true;
	}
	if ($board[2] == $player) {
		if ($board[4] == $player
		&&  $board[6] == $player) {
			return true;
		}
		if ($board[5] == $player
		&&  $board[8] == $player) {
			return true;
		}
	}
	if ($board[3] == $player
	&&  $board[4] == $player
	&&  $board[5] == $player) {
		return true;
	}
	if ($board[6] == $player
	&&  $board[7] == $player
	&&  $board[8] == $player) {
		return true;
	}
	return false;
}

