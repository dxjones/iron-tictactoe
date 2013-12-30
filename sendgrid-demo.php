<? // sendgrid-demo.php

require_once 'unirest-php/lib/Unirest.php';
require_once 'sendgrid-php/lib/SendGrid.php';
SendGrid::register_autoloader();

$sendgrid = new SendGrid('dxjones@gmail.com', 'send$grid');

$mail = new SendGrid\Email();
$mail->
  addTo('dxjones+sendgrid@gmail.com')->
  setFrom('dxjones+ironio@gmail.com')->
  setSubject('Sent from SendGrid')->
  setText("Hello World!\n... sent with SendGrid\n");
  // ->setHtml('<strong>Hello World!</strong>');
  
$sendgrid->web->send($mail);

