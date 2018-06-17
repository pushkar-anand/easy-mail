<?php

use EasyMail\Mail;
use PHPUnit\Framework\TestCase;


class MailTest extends TestCase
{
    private $test_mail = "test@email.com";

    public static function setUpBeforeClass()
    {
        $_SERVER['SERVER_NAME'] = 'text.example.com';
        $_SERVER['SERVER_ADDR'] = '127.0.1.0';

        $file = fopen("test.txt", "w");
        fwrite($file, "Attachment");
        fclose($file);
    }

    public static function tearDownAfterClass()
    {
        unlink("test.txt");
    }

    public function testIsThereAnySyntaxError()
    {
        try {
            $object = new \EasyMail\Mail($this->test_mail);
            $this->assertTrue(is_object($object));
            unset($object);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testEmailSend()
    {
        try {
            $mail = new Mail($this->test_mail);
            $mail->setSubject("Test Subject");
            $mail->addCC("hey@mail.com");
            $mail->setMsg("email msg");
            $mail->isHtml(false);
            $mail->addReplyTo("no-reply@mail.com", "no-reply");
            $this->assertTrue($mail->sendMail());

            unset($mail);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
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


            $mail->addAttachment(__DIR__ . "/test.txt");
            $this->assertTrue($mail->sendMail());

        } catch (Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

}
