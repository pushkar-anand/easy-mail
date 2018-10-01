<?php

namespace EasyMail;


use Exception;

/**
 * Class Mail
 * @package EasyMail
 */
class Mail
{
    private $from = null;
    private $send_to;
    private $subject;
    private $msg;
    private $headers;
    private $attachment;
    private $reply_to = null;
    private $encoding = "iso-8859-1";
    private $priority = 3;


    private $isHtml = false;
    private $hasAttachment = false;


    private $bcc = array();
    private $cc = array();


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

            $this->headers = "";
        } else {

            throw new Exception("Invalid Email.");
        }
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
     * Set the subject of the email
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set whether the email is html or not
     * @param bool $bool
     */
    public function isHtml(bool $bool)
    {
        $this->isHtml = $bool;
    }

    /**
     * Set the message of the email
     * @param string $msg
     */
    public function setMsg(string $msg)
    {
        $this->msg = $msg;
    }

    /**
     * Define your own mail headers
     * @param string $header
     */
    public function customHeader(string $header)
    {
        $this->headers .= $header;
    }

    /**
     * Add BCC (blind carbon copy) header
     * @param string $email
     * @throws Exception
     */
    public function addBcc(string $email)
    {
        if ($this->isValidEmail($email)) {
            if (!in_array($email, $this->bcc)) {
                array_push($this->bcc, $email);
            }
        } else {
            throw new Exception("Invalid Email.");
        }
    }

    /**
     * Add CC (carbon copy) header
     * @param string $email
     * @throws Exception
     */
    public function addCC(string $email)
    {
        if ($this->isValidEmail($email)) {
            if (!in_array($email, $this->cc)) {
                array_push($this->cc, $email);
            }
        } else {
            throw new Exception("Invalid Email.");
        }
    }

    /**
     * Set priority of email. 1 is the highest, 3 is normal, 5 is the lowest
     * @param int $priority
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    /**
     * Add Reply-To header
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
     * Set From header
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
     * Set the encoding of the email
     * @param string $encoding
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Add file attachment to the email
     * @param string $file_full_path
     * @throws Exception
     */
    public function addAttachment(string $file_full_path)
    {
        if (file_exists($file_full_path)) {
            $this->hasAttachment = true;
            $this->attachment = $file_full_path;
        } else {
            throw new Exception("File does'nt exists");
        }
    }

    /**
     * Final call to send the  mail
     * returns true on successful call, false otherwise.
     * @return bool
     * @throws Exception
     */
    public function sendMail(): bool
    {
        if ($this->send_to == null || $this->msg == null) {
            throw new Exception("Required Parameter missing. Please see if you are missing body or receiver's address.");
        }
        $eol = "\r\n";
        $body = $this->msg;

        $content = null;
        $separator = "BOUNDARY" . md5(time()) . "";

        if ($this->from != null) {
            $this->headers .= "From: $this->from" . $eol;
        }

        if ($this->reply_to != null) {
            $this->headers .= "Reply-To: $this->reply_to" . $eol;
        }

        if (!empty($this->cc)) {
            foreach ($this->cc as $email) {
                $this->headers .= "Cc: $email" . $eol;
            }
        }

        if (!empty($this->bcc)) {
            foreach ($this->bcc as $email) {
                $this->headers .= "Bcc: $email" . $eol;
            }
        }

        if ($this->hasAttachment) {
            if (!file_exists($this->attachment)) {
                throw new Exception("File does'nt exists");
            }
            $content = file_get_contents($this->attachment);
            $content = chunk_split(base64_encode($content));


            $filename = pathinfo($this->attachment, PATHINFO_BASENAME);

            $this->headers .= "MIME-Version: 1.0" . $eol;
            $this->headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
            $this->headers .= "This is a MIME encoded message." . $eol;

            //for message
            if ($this->isHtml) {
                $body = "--" . $separator . $eol;
                $body .= "Content-Type: text/html;charset=$this->encoding" . $eol;
                $body .= "Content-Transfer-Encoding: base64" . $eol;
                $body .= chunk_split(base64_encode($this->msg)) . $eol;

            } else {
                $body = "--" . $separator . $eol;
                $body .= "Content-Type: text/plain;charset=$this->encoding" . $eol;
                $body .= "Content-Transfer-Encoding: base64" . $eol;
                $body .= chunk_split(base64_encode($this->msg)) . $eol;
            }

            //for attachment
            $body .= "--" . $separator . $eol;
            $body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
            $body .= "Content-Transfer-Encoding: base64" . $eol;
            $body .= "Content-Disposition: attachment" . $eol;
            $body .= $content . $eol;
            $body .= "--" . $separator . "--";
        }

        if (!$this->hasAttachment && $this->isHtml) {
            $this->headers .= "MIME-Version: 1.0" . $eol;
            $this->headers .= "Content-Type: text/html;charset=$this->encoding" . $eol;
        }


        $this->headers .= "X-Mailer: EasyMail-Composer-Lib" . $eol;
        $message_id = "<" . time() . '-' . hash('sha1', $this->from . $this->send_to) . '@' . $_SERVER['SERVER_NAME'] . ">";
        $this->headers .= "Message-Id: $message_id" . $eol;
        $this->headers .= "X-Priority: $this->priority" . $eol;
        $this->headers .= "X-Originating-IP: " . $_SERVER['SERVER_ADDR'] . $eol;

        return mail($this->send_to, $this->subject, $body, $this->headers);
    }
}
