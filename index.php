<? // index.php
session_start('sid');
$debug = false;
require_once 'phar://./iron-io/iron_worker.phar';
include('tictactoe_functions.php');
include('tictactoe_email.php');

if ($debug) {
	echo "<pre>\n";
	print_r($_SERVER);
	echo "</pre>\n";
}

function session_reset() {
	$list = array('submit', 'board', 'email', 'pending', 'reset');
	foreach ($list as $key) {
		unset($_SESSION[$key]);
	}
}

function display_reset_form() {
?>
<p>Please check your inbox at "<b><? echo $_SESSION['email']; ?></b>" to make sure it has completed before submitting another task.</p>
<p>When you are ready, click "Reset".</p>

<div style="padding:16px">
<form action="" method="post">
<input name="pending" type="hidden" value="0"/>
<input name="reset" type="hidden" value="1"/>
<input name="board" type="hidden" value=""/>
<input name="email" type="hidden" value=""/>
<input name="submit" type="submit" value="Reset"/>
</form>
</div>
<?
}

?>
<html>
<head>
<title>Iron TicTacToe</title>
</head>
<body>
<h1>Iron TicTacToe</h1>
<?
$state = 'empty';
if (array_key_exists('submit', $_POST)) {
	$state = 'submit';
	$list = array('submit', 'board', 'email', 'pending', 'reset');
	foreach ($list as $key) {
		$_SESSION[$key] = $_POST[$key];
		if ($debug) { printf("<p>%s = %s</p>\n", $key, $_SESSION[$key]); }
	}
	if ($_SESSION['reset']) {
		session_reset();
		$state = 'empty';
	}
} else if (array_key_exists('board',$_SESSION)) {
	$state = 'pending';
}
if ($debug) { printf("<p>state = %s</p>\n", $state); }

if ($state == 'submit') {
	$ok = true;
	$count = boardcount($_SESSION['board']);
	$diff = $count['X'] - $count['O'];
	if (strlen($_SESSION['board']) != 9
	|| $diff < 0 || $diff > 1) {
?>
<div style="border:1px solid">
<table>
<tr><td><font color="red"><b>Error:</b></font></td>
<td>The <b>Board</b> must have exactly 9 characters, and number of X's and O's must differ by at most 1.</td></tr>
<tr><td></td><td>Please try again.</td></tr>
</table>
</div>
<?
		$state = 'empty';
	}
	if (empty($_SESSION['email'])) {
?>
<div style="border:1px solid">
<table>
<tr><td><font color="red"><b>Error:</b></font></td>
<td><b>Email</b> must be a valid email address.  It cannot be empty.</td></tr>
<tr><td></td><td>Please try again.</td></tr>
</table>
</div>
<?
		$state = 'empty';
	} else if (strpos($_SESSION['email'],'@') === FALSE || strpos($_SESSION['email'],'.') === FALSE) {
?>
<div style="border:1px solid">
<table>
<tr><td><font color="red"><b>Error:</b></font></td>
<td><b>Email</b> address "<b><? echo $_SESSION['email']; ?></b>" does not look valid.</td></tr>
<tr><td></td><td>Please try again.</td></tr>
</table>
</div>
<?
		$state = 'empty';
	}
	if ($state == 'empty') {
		session_reset();
	} else {
		// submit payload to IronWorker ...
		// iron.json contains credentials
		$worker = new IronWorker;
		$task_id = $worker->postTask('iron_tictactoe', array('board' => $_SESSION['board'], 'email' => $_SESSION['email']));
		webform_email($_SESSION['board'], $_SESSION['email'], $task_id);
?>
<div style="border:1px solid">
<table width="auto">
<tr><td><font color="blue"><b>Submitted:</b></font></td>
	<td><b>Board</b></td><td><tt><? echo $_SESSION['board']; ?></tt></tr></tr>
<tr><td></td>
	<td><b>Email</b></td><td><tt><? echo $_SESSION['email']; ?></tt></tr></tr>
<tr><td></td>
	<td><b>TaskId</b></td><td><tt><? echo $task_id; ?></tt></tr></tr>
</table>
</div>
<br/>
<?
	display_reset_form();
	}
}

if ($state == 'empty') {
?>
<p>This simple form enables you to submit input to <b>Iron TicTacToe</b>.<br/>
For more details, please read
<a href="https://www.hackerleague.org/hackathons/the-iron-holiday-hack/hacks/iron-tictactoe" target="_new">an overview of this "Iron Holiday Hack" project</a>.
</p>
<p>The <b>Board</b> must be a 9 character string containing only ".", "X", "O".<br/>
The <b>Email</b> must be valid, so you can receive the results.
</p>

<div style="padding:16px">
<form action="" method="post">
<input name="pending" type="hidden" value="0"/>
<input name="reset" type="hidden" value="0"/>
<table>
<tr><td></td><td></td><td>&nbsp;</td><td><i>example</i></td></tr>
<tr><td><label for="board" align="right"><b>Board:</b></label></td>
<td><input name="board" type="text" value="XOOXOX..X"/>
<td></td><td><tt>XOOXOX...</tt></td></tr>
<tr>
<td><label for="email" align="right"><b>Email:</b></label></td>
<td><input name="email" type="email" value="dxjones@gmail.com"/></td>
<td></td><td><tt>you@example.com</td></td></tr>
<tr><td></td><td>&nbsp;</td><td></td></tr>
<tr><td></td>
<td align="center"><input name="submit" type="submit" value="Submit"/></td>
<td></td></tr>
</table>
</form>
</div>
<?
} else if ($state == 'pending') {
?>
<p>You recently submitted a task to "Iron TicTacToe", with <b>Board</b> = <tt><? echo $_SESSION['board']; ?></tt>.</p>
<?
	display_reset_form();
} // end if state
?>
<br/>
<br/>
<br/>
_____<br/>
<p><a href="https://www.hackerleague.org/hackathons/the-iron-holiday-hack/hacks/iron-tictactoe" target="_new"><i>Iron TicTacToe</i></a>
was created by David Jones (dxjones@gmail.com)
for a
<a href="https://www.hackerleague.org/" target="_new"><b>Hacker League</b></a>
<a href="https://www.hackerleague.org/hackathons/the-iron-holiday-hack" target="_new">Hackathon</a>
sponsored by
<a href="http://iron.io/"><b>Iron.io</b></a> and
<a href="http://sendgrid.com/" target="_new"><b>SendGrid</b></a>.</p>
</body>
</html>
