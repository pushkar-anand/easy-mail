<?php

use PHPUnit\Framework\TestCase;


class MailTest extends TestCase
{
    private $test_mail = "test@email.com";

    public function testIsThereAnySyntaxError()
    {
        try {
            $object = new \EasyMail\Mail($this->test_mail);
            $this->assertTrue(is_object($object));
            unset($object);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function testEmailSend()
    {
        try {
            $mail = new \EasyMail\Mail($this->test_mail);
            $mail->setSubject("Test Subject");
            $mail->addCC("hey@mail.com");
            $mail->isHtml(false);
            $mail->addReplyTo("no-reply@mail.com", "no-reply");

            $this->assertTrue($mail->sendMail());
            unset($mail);
        } catch (Exception $e) {

        }
    }


    public function testIfEmailExceptionIsThrown()
    {
        try {
            $mail = new \EasyMail\Mail("abc");
        } catch (Exception $e) {
            $this->assertTrue($e->getMessage() == "Invalid Email.");
        }
    }

}
