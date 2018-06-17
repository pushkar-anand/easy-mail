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
    private $attachment;
    private $customHeaders;
    private $reply_to;
    private $encoding = "iso-8859-1";

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

            $this->from = null;
            $this->headers = "";
            $this->reply_to = null;
            $this->customHeaders = false;
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
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
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

    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;
    }

    /**
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
     *Final call to send the  mail
     * returns true on successful call, false otherwise.
     * @return bool
     * @throws Exception
     */
    public function sendMail(): bool
    {
        $eol = "\r\n";
        $separator = md5(time());
        $body = wordwrap($this->msg);

        if (!($this->customHeaders)) {
            $this->headers .= "X-Mail: EasyMail" . $eol;

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

            if ($this->from != null) {
                $this->headers .= "From: $this->from" . $eol;
            }

            if ($this->reply_to != null) {
                $this->headers .= "Reply-To: $this->reply_to" . $eol;
            }

            $content = null;

            if ($this->hasAttachment) {
                if (!file_exists($this->attachment)) {
                    throw new Exception("File does'nt exists");
                }
                $content = file_get_contents($this->attachment);
                $content = chunk_split(base64_encode($content));


                $filename = pathinfo($this->attachment, PATHINFO_BASENAME);;

                $this->headers .= "MIME-Version: 1.0" . $eol;
                $this->headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
                $this->headers .= "This is a MIME encoded message." . $eol;

                //for message
                if ($this->isHtml) {
                    $body = "--" . $separator . $eol;
                    $body .= "Content-Type: text/html;charset=$this->encoding" . $eol;
                    $body .= "Content-Transfer-Encoding: 7bit" . $eol;
                    $body .= $this->msg . $eol;

                } else {
                    $body = "--" . $separator . $eol;
                    $body .= "Content-Type: text/plain;charset=$this->encoding" . $eol;
                    $body .= "Content-Transfer-Encoding: 7bit" . $eol;
                    $body .= $this->msg . $eol;
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
        }

        return mail($this->send_to, $this->subject, $body, $this->headers);
    }

}