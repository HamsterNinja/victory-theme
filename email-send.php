<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once ('include/PHPMailer/src/Exception.php');
require_once ('include/PHPMailer/src/PHPMailer.php');
require('../../../wp-load.php');

if ($_POST){
$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$description = $_POST['description'];

$bodytext = ""; 
if ($name) {
  $bodytext .= "Имя - ".$name;
}
if ($phone) {
  $bodytext .= "<br>Телефон - ".$phone;
}
if ($email) {
  $bodytext .= "<br>email - ".$email;
}
if ($description) {
  $bodytext .= "<br>Сообщение - ".$description;
}

$admin_email = get_option('admin_email');

$email = new PHPMailer(true);
try {
  
  $email->ClearAttachments(); 
  $email->ClearCustomHeaders(); 
  $email->ClearReplyTos(); 
  
  $email->SingleTo = true;
  $email->ContentType = 'text/html'; 
  $email->IsHTML( true );
  $email->CharSet = 'utf-8';
  $email->ClearAllRecipients();
  $email->From      = $admin_email;
  $email->FromName  = 'site';
  $email->Subject   = 'Заявка с сайта';
  $email->Body      = $bodytext;
  $email->addAddress($admin_email);  

 
  
  if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $email->AddAttachment($_FILES['file']['tmp_name'], $_FILES['file']['name']);
  }
  if (!$email->send()) { 
    $result = array('status'=>"error", 'message'=>"Mailer Error: ".$email->ErrorInfo);//
    echo json_encode($result);
  } else {
      $result = array('status'=>"success", 'message'=>"Message sent.");
      echo json_encode($result);
  }
 
  var_dump('Message has been sent');
}
catch (Exception $e) {
  var_dump('Message could not be sent. Mailer Error: ', $email->ErrorInfo);
}
}
?> 