<?php

use EasyMail\Mail;
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
            $mail = new Mail($this->test_mail);
            $mail->setSubject("Test Subject");
            $mail->addCC("hey@mail.com");
            $mail->isHtml(false);
            $mail->addReplyTo("no-reply@mail.com", "no-reply");

            $this->assertTrue($mail->sendMail());
            unset($mail);
        } catch (Exception $e) {

        }
    }

    public function testFileNotFoundException()
    {
        $this->expectOutputString("File does'nt exists");

        try {
            $mail = new Mail($this->test_mail);
            $mail->setSubject("Test Subject");
            $mail->addCC("hey@mail.com");
            $mail->isHtml(false);
            $mail->addReplyTo("no-reply@mail.com", "no-reply");
            $mail->addAttachment("/invalid/file.ext");
            $mail->sendMail();
            unset($mail);
        } catch (Exception $e) {
            echo $e->getMessage();
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

    public function testAttachedEmail()
    {
        try {
            $mail = new Mail("anandpushkar088@email.com");
            $mail->setSubject("PHP Unit test Mail");
            $mail->isHtml(true);
            $mail->setMsg("<html><head></head><body>HTML email with attachment.</body></html>");

            $file = fopen("test.txt", "w");
            fwrite($file, "Hii this is the attachment.");
            fclose($file);

            $mail->addAttachment(__DIR__ . "/test.txt");
            $this->assertTrue($mail->sendMail());
            unlink("test.txt");
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
    }

}
