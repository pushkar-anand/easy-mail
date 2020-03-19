# easy-mail

A wrapper for php's mail function that allows you to easily construct and send mails.

- [Install](#install)
- [Usage](#usage)
- [License](#license)

## Install

With composer:
```bash
composer require pushkar/easy-mail
```

## Usage

```php
require_once "../../path/to/vendor/autoload.php";

use EasyMail\Mail;

//Example 1

$mail = new Mail("send_to@exapmle.com");
$mail->setSubject("Subject");
$mail->setMsg("Msg");
if($mail->sendMail()) {
    echo "Mail Sent.";
}

//Example 2

$mail = new Mail("send_to@exapmle.com", "Subject", "Msg');
if($mail->sendMail()) {
    echo "Mail Sent.";
}

//Some methods

//For a reply-to header
$mail->addReplyTo("email@email.com", Name);

//For html email
$mail->isHtml(true);

//For adding custom header
$mail->customHeaders($header);

//set From
$mail->setFrom($email, $name);

//All methods
isValidEmail(string);
isHtml(bool);
setSubject(string);
setMsg(string);
addCC(string);
addBcc(string);
customHeader(string);
addReplyTo(string, string|null);
setFrom(string, string|null);
setPriority(int);
addAttachment(string);
setEncoding(string);
sendMail();

```

## License

Copyright (c) 2018 [Pushkar Anand](https://pushkaranand.me/). Under MIT License.
