# smtp-client
 this is a web base SMTP client you can send email using it.\
 just download and put it in to Web Server.\
 the 'index.html' is a ready Web UI to use.\
 you must prepare your own mail server.
 
 ## Requirement
  web server eg:nginx apache etc...\
  php support (minimum PHP version not sure [developing on php 8.1])
 
 ## Library
  Bootstrap\
  jQuery\
  PHP-Miller

 ## Limitation
  maximum subject character length is 255\
  maximum content character length is 384000\
  total of attached file size is allow upto 30MB

# API key for 'send-email.php'
 HTTP request type: POST

 ## Required field
  host - SMTP server host ip/domain\
  port - SMTP server port number\
  encryption - encryption type [ssl / tls / none]\
  send_from[address] - send email by using this email address / sender email address\
  send_to[] - send email to (this is required atless 1 email address)\
  content - email content

 ## Optional field
  send_from['name'] - sender name\
  reply_to[address] - add reply email address\
  reply_to[name] - receiver name of reply_to email\
  cc[] - cc email address\
  bcc[] - bcc email address\
  subject - email subject\
  attachments[] - attach file when send email
  
 ## Other field
  auth - authentication to the mail server [1 = yes, 0 = no]\
  (if auth is 1 below is required)\
  username - username to login in to mail server\
  password - password to login in to mail server
