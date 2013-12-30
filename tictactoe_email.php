<? // tictactoe_email.php

require_once 'unirest-php/lib/Unirest.php';
require_once 'sendgrid-php/lib/SendGrid.php';
SendGrid::register_autoloader();

function tictactoe_email($email,$board,$winner) {
	$sendgrid = new SendGrid('XXX', 'YYY');
	$mail = new SendGrid\Email();
	$mail->
	  addTo($email)->
	  setFrom('dxjones+ironio@gmail.com')->
	  setSubject('Results from Iron.io TicTacToe')->
	  setText(sprintf("Hi,\n\nHere are results from your recently submitted Iron TicTacToe task:\n\n"
	  	. "Board = %s\nWinner = %s\n\nSincerely,\nDavid Jones, PhD\ndxjones@gmail.com\n", $board, $winner))->
	  setHtml(sprintf("<p>Hi,</p>"
	  . "<p>Here are results from your recently submitted Iron TicTacToe task:</p>"
	  . "<p><b>Board = %s<br/>Winner = %s</b></p>"
	  . "<p>Sincerely,<br/>David Jones<br/>dxjones@gmail.com</p>", $board, $winner));
	$sendgrid->web->send($mail);
}

function webform_email($board,$email,$task_id) {
	$remote_addr = array_key_exists('REMOTE_ADDR',$_SERVER) ? $_SERVER['REMOTE_ADDR'] : '';
	$user_agent = array_key_exists('HTTP_USER_AGENT',$_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$sendgrid = new SendGrid('XXX', 'YYY');
	$mail = new SendGrid\Email();
	$mail->
	  addTo('dxjones+webform@gmail.com')->
	  setFrom('dxjones+webform@gmail.com')->
	  setSubject('Web Submission for Iron TicTacToe')->
	  setText(sprintf("Hi,\n\nHere is a recently submitted Iron TicTacToe task:\n\n"
	  	. "Board = %s\nEmail = %s\nTaskId = %s\nRemoteAddr = %s\nHttpUserAgent = %s\n"
	  	. "Sincerely,\nDavid Jones\ndxjones@gmail.com\n", $board, $email, $task_id, $remote_addr, $user_agent))->
	  setHtml(sprintf("<p>Hi,</p>"
	  . "<p>Here is a recently submitted Iron TicTacToe task:</p>"
	  . "<p><b>Board = %s<br/>Email = %s<br/>TaskId = %s<br/>RemoteAddr = %s<br/>HttpUserAgent = %s</b></p>"
	  . "<p>Sincerely,<br/>David Jones<br/>dxjones@gmail.com</p>", $board, $email, $task_id, $remote_addr, htmlspecialchars($user_agent)));
	$sendgrid->web->send($mail);
}

