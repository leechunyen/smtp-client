<?php
// used https://github.com/PHPMailer/PHPMailer

require './functions.php';

require './PHPMailer-master/src/PHPMailer.php';
require './PHPMailer-master/src/SMTP.php';
require './PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$pass = true;$msg = array();$success = false;$auth = false;$encryption = 'none';

# server
if(!isset($_POST['host'])||empty($_POST['host'])){
    array_push($msg,'host is required');
    $pass = false;
}

if(!isset($_POST['port'])||empty($_POST['port'])){
    array_push($msg,'port is required');
    $pass = false;
}

if(isset($_POST['encryption'])&&!empty($_POST['encryption'])){
    if($_POST['encryption'] == 'none'){
        $encryption = 'none';
    }else if($_POST['encryption'] == 'tls'){
        $encryption = 'tls';
    }else if($_POST['encryption'] == 'ssl'){
        $encryption = 'ssl';
    }else{
        array_push($msg,'invalid encryption');
        $pass = false;
    }
}

if(isset($_POST['auth'])&&!empty($_POST['auth'])&&$_POST['auth'] == 1){
    $auth = true;

    if(!isset($_POST['username'])||empty($_POST['username'])){
        array_push($msg,'username is required');
        $pass = false;
    }

    if(!isset($_POST['password'])||empty($_POST['password'])){
        array_push($msg,'password is required');
        $pass = false;
    }
}

# address
if(!isset($_POST['send_from'])){
    array_push($msg,'send_from address is required');
    $pass = false;
}else if(!is_array($_POST['send_from'])){
    array_push($msg,'send_from must is an array');
    $pass = false;
}else if(!isset($_POST['send_from']['address'])||empty($_POST['send_from']['address'])){
    array_push($msg,'send_from address is required');
    $pass = false;
}else if(!isValidEmail($_POST['send_from']['address'])){
    array_push($msg,'send_from address \''.$_POST['send_from']['address'].'\' is invalid');
    $pass = false;
}

if(!isset($_POST['send_to'])){
    array_push($msg,'send_to must at least 1 email address');
    $pass = false;
}else if(!is_array($_POST['send_to'])){
    array_push($msg,'send_to must is an array');
    $pass = false;
}else{
    $emptySendTo = 0;
    foreach($_POST['send_to'] as $emailAddr){
        if(empty($emailAddr)){
            $emptySendTo++;
        }else if(!isValidEmail($emailAddr)){
            array_push($msg,'send_to address \''.$emailAddr.'\' is invalid');
            $pass = false;
        }
    }
    if(count($_POST['send_to']) == $emptySendTo){
        array_push($msg,'send_to must at least 1 email address');
        $pass = false;
    }
}

if(isset($_POST['reply_to'])){
    if(!is_array($_POST['reply_to'])){
        array_push($msg,'reply_to must is an array');
        $pass = false;
    }else if(!isset($_POST['reply_to']['address'])||empty($_POST['reply_to']['address'])){
        array_push($msg,'reply_to must have address');
        $pass = false;
    }else if(!isValidEmail($_POST['reply_to']['address'])){
        array_push($msg,'reply_to address \''.$_POST['reply_to']['address'].'\' is invalid');
        $pass = false;
    }
}

if(isset($_POST['cc'])){
    if(!is_array($_POST['cc'])){
        array_push($msg,'cc must is an array');
        $pass = false;
    }else{
        foreach($_POST['cc'] as $emailAddress){
            if(!isValidEmail($emailAddress)){
                array_push($msg,'cc address \''.$emailAddress.'\' is invalid');
                $pass = false;
            }
        }
    }
}

if(isset($_POST['bcc'])){
    if(!is_array($_POST['bcc'])){
        array_push($msg,'bcc must is an array');
        $pass = false;
    }else{
        foreach($_POST['bcc'] as $emailAddress){
            if(!isValidEmail($emailAddress)){
                array_push($msg,'bcc address \''.$emailAddress.'\' is invalid');
                $pass = false;
            }
        }
    }
}

# Content
if(isset($_POST['subject'])){
    if(strlen($_POST['subject']) > 255){
        array_push($msg,'subject only allow up to 255 character');
        $pass = false;
    }
}

if(!isset($_POST['content'])||empty($_POST['content'])){
    array_push($msg,'content is required');
    $pass = false;
}else if(strlen($_POST['content']) > 384000){
    array_push($msg,'content only allow up to 384000 character');
    $pass = false;
}

# Attachments
if(isset($_FILES['attachments'])){
    if(!is_array($_FILES['attachments']['name'])){
        array_push($msg,'attachments must is an array');
        $pass = false;
    }else{
        for($i = 0; $i < count($_FILES['attachments']['error']); $i++){
            if($_FILES['attachments']['error'][$i] > 0){
                if($_FILES['attachments']['error'][$i] == 4){
                    array_push($msg,'attachments ' . $i . ' not found');
                }else{
                    array_push($msg,'attachments ' . $i . ' ' . $_FILES['attachments']['name'][$i] . ' error');
                }
                $pass = false;
            }
        }
        $totalSize = 0;
        foreach($_FILES['attachments']['size'] as $size){
            $totalSize += $size;
        }
        if($totalSize > 31457280){
            array_push($msg,'total attachments size over 30MB');
            $pass = false;
        }
    }
}

if($pass){

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $_POST['host'];
        $mail->SMTPAuth   = $auth;
        if($auth){
            $mail->Username   = $_POST['username'];
            $mail->Password   = $_POST['password'];
        }
        if($encryption <> 'none'){$mail->SMTPSecure = $encryption;}
        $mail->Port       = $_POST['port'];

        //Recipients
        $senderName = null;
        if(isset($_POST['send_from']['name'])&&!empty($_POST['send_from']['name'])){
            $senderName = $_POST['send_from']['name'];
        }
        $mail->setFrom($_POST['send_from']['address'], $senderName);
        
        foreach($_POST['send_to'] as $emailAddr){
            if(!empty($emailAddr)){
                $mail->addAddress($emailAddr);
            }
        }
        
        if(isset($_POST['reply_to'])){
            $replyName = null;
            if(isset($_POST['reply_to']['name'])&&!empty($_POST['reply_to']['name'])){
                $replyName = $_POST['reply_to']['name'];
            }
            $mail->addReplyTo($_POST['reply_to']['address'], $replyName);
        }
        
        if(isset($_POST['cc'])){
            foreach($_POST['cc'] as $ccAddress){
                if(!empty($ccAddress)){
                    $mail->addCC($ccAddress);
                }
            }
        }
        
        if(isset($_POST['bcc'])){
            foreach($_POST['bcc'] as $bccAddress){
                if(!empty($bccAddress)){
                    $mail->addBCC($bccAddress);
                }
            }
        }
        
        //Content
        $mail->isHTML(true);
        if(isset($_POST['subject'])){
            $mail->Subject = $_POST['subject'];
        }
        $mail->Body    = $_POST['content'];

        //Attachments
        if(isset($_FILES['attachments'])){
            for($i = 0; $i < count($_FILES['attachments']['tmp_name']); $i++){
                $mail->addAttachment($_FILES['attachments']['tmp_name'][$i], $_FILES['attachments']['name'][$i]);
            }
        }

        $mail->send();
        $mail->ClearAddresses();
        $success = true;
        array_push($msg,'Email has been sent');
    } catch (Exception $e) {
        array_push($msg,'Email could not be sent. '.$mail->ErrorInfo);
    }
}

$output['success'] = $success;
$output['message'] = $msg;
echo json_encode($output);