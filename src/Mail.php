<?php

namespace EasyMail;


use Exception;

/**
 * Class Mail
 * @package EasyMail
 */
class Mail
{
    private $from;
    private $send_to;
    private $subject;
    private $msg;
    private $headers;

    private $isHtml;
    private $customHeaders;

    private $bcc = array();
    private $cc = array();
    private $reply_to;

    /**
     * SimplerMailer constructor.
     * @param string $send_to
     * @param string|null $subject
     * @param string|null $msg
     * @throws Exception
     */
    public function __construct(string $send_to, string $subject = null, string $msg = null)
    {
        if ($this->isValidEmail($send_to)) {
            $this->send_to = $send_to;
            $this->subject = $subject;
            $this->msg = $msg;

            $this->from = null;
            $this->headers = "";
            $this->reply_to = null;
            $this->customHeaders = false;
        } else {

            throw new Exception("Invalid Email.");
        }
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Checks whether an email is valid or not
     * Return true if valid, false otherwise
     * @param string $email
     * @return bool
     */
    private function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * set whether the email is html email or not
     * @param bool $bool
     */
    public function isHtml(bool $bool)
    {
        $this->isHtml = $bool;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg)
    {
        $this->msg = $msg;
    }

    /**
     * Define your own mail headers
     * @param string $headers
     */
    public function customHeaders(string $headers)
    {
        $this->customHeaders = true;
        $this->headers = $headers;
    }

    /**
     * @param string $email
     * @throws Exception
     */
    public function addBcc(string $email)
    {
        if ($this->isValidEmail($email)) {
            array_push($this->bcc, $email);
        } else {
            throw new Exception("Invalid Email.");
        }
    }

    /**
     * @param string $email
     * @throws Exception
     */
    public function addCC(string $email)
    {
        if ($this->isValidEmail($email)) {
            array_push($this->cc, $email);
        } else {
            throw new Exception("Invalid Email.");
        }
    }

    /**
     * @param string $email
     * @param string $name
     * @throws Exception
     */
    public function addReplyTo(string $email, string $name)
    {
        if ($this->isValidEmail($email)) {
            if ($name == null) {
                $this->reply_to = $email;
            } else {
                $this->reply_to = "$name<$email>";
            }

        } else {
            throw new Exception("Invalid Email.");
        }
    }

    /**
     * @param string $email
     * @param string|null $name
     * @throws Exception
     */
    public function setFrom(string $email, string $name = null)
    {
        if ($this->isValidEmail($email)) {
            if ($name == null) {
                $this->from = $email;
            } else {
                $this->from = "$name<$email>";
            }
        } else {
            throw new Exception("Invalid Email.");
        }

    }

    /**
     *
     */
    public function sendMail()
    {
        if (!($this->customHeaders)) {
            $this->headers .= "X-Mail: SimpleMailer" . "\r\n";

            if (!empty($this->cc)) {
                foreach ($this->cc as $email) {
                    $this->headers .= "Cc: $email" . "\r\n";
                }
            }

            if (!empty($this->bcc)) {
                foreach ($this->bcc as $email) {
                    $this->headers .= "Bcc: $email" . "\r\n";
                }
            }
            if ($this->isHtml) {
                $this->headers .= "'MIME-Version: 1.0" . "\r\n";
                $this->headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            }

            if ($this->from != null) {
                $this->headers .= "From: $this->from";
            }

            if ($this->reply_to != null) {
                $this->headers .= "Reply-To: $this->reply_to";
            }
        }

        return mail($this->send_to, $this->subject, $this->msg, $this->headers);
    }

}