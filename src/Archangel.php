<?php

namespace Jacobemerick\Archangel;

/**
 * This is the main class for Archangel mailer
 * For licensing and examples:
 * @see https://github.com/jacobemerick/archangel
 *
 * @author jacobemerick (http://home.jacobemerick.com/)
 */
class Archangel
{

    /** @var string $subject */
    protected $subject;

    /** @var array $to */
    protected $to = array();

    /** @var array $cc */
    protected $cc = array();

    /** @var array $bcc */
    protected $bcc = array();

    /** @var array $headers */
    protected $headers = array();

    /** @var string $plainMessage */
    protected $plainMessage;

    /** @var string $htmlMessage */
    protected $htmlMessage;

    /** @var array $attachments */
    protected $attachments = array();

    /** @var string $boundary */
    protected $boundary;

    /** @var string $alternativeBoundary */
    protected $alternativeBoundary;

    /** @var string LINE_BREAK */
    const LINE_BREAK = "\r\n";

    /**
     * @param string $mailer
     */
    public function __construct($mailer = null)
    {
        if (is_null($mailer)) {
            $mailer = sprintf('PHP/%s', phpversion());
        }
        $this->headers['X-Mailer'] = $mailer;
    }

    /**
     * Setter method for adding recipients
     *
     * @param string $address email address for the recipient
     * @param string $title   name of the recipient (optional)

     * @return object instantiated $this
     */
    public function addTo($address, $title = '')
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        array_push($this->to, $address);

        return $this;
    }

    /**
     * Setter method for adding cc recipients
     *
     * @param string $address email address for the cc recipient
     * @param string $title   name of the cc recipient (optional)
     *
     * @return object instantiated $this
     */
    public function addCC($address, $title = '')
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        array_push($this->cc, $address);

        return $this;
    }

    /**
     * Setter method for adding bcc recipients
     *
     * @param string $address email address for the bcc recipient
     * @param string $title   name of the bcc recipient (optional)
     *
     * @return object instantiated $this
     */
    public function addBCC($address, $title = '')
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        array_push($this->bcc, $address);

        return $this;
    }

    /**
     * Setter method for setting the single 'from' field
     *
     * @param string $address email address for the sender
     * @param string $title   name of the sender (optional)
     *
     * @return object instantiated $this
     */
    public function setFrom($address, $title = '')
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        $this->headers['From'] = $address;

        return $this;
    }

    /**
     * Setter method for setting the single 'reply-to' field
     *
     * @param string $address email address for the reply-to
     * @param string $title   name of the reply-to (optional)
     *
     * @return object instantiated $this
     */
    public function setReplyTo($address, $title = '')
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        $this->headers['Reply-To'] = $address;

        return $this;
    }

    /**
     * Setter method for setting a subject
     *
     * @param string $subject subject for the email
     *
     * @return object instantiated $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Setter method for the plain text message
     *
     * @param string $message the plain-text message
     *
     * @return object instantiated $this
     */
    public function setPlainMessage($message)
    {
        $this->plainMessage = $message;

        return $this;
    }

    /**
     * Setter method for the html message
     *
     * @param string $message the html message
     *
     * @return object instantiated $this
     */
    public function setHTMLMessage($message)
    {
        $this->htmlMessage = $message;

        return $this;
    }

    /**
     * Setter method for adding attachments
     *
     * @param string $path  the full path of the attachment
     * @param string $type  mime type of the file
     * @param string $title the title of the attachment (optional)
     *
     * @return object instantiated $this
     */
    public function addAttachment($path, $type, $title = '')
    {
        array_push($this->attachments, array(
          'path' => $path,
          'type' => $type,
          'title' => $title,
        ));

        return $this;
    }

    /**
     * The executing step, the actual sending of the email
     * First checks to make sure the minimum fields are set (returns false if they are not)
     * Second it attempts to send the mail with php's mail() (returns false if it fails)
     *
     * return boolean whether or not the email was valid & sent
     */
    public function send()
    {
        if (!$this->checkRequiredFields()) {
            return false;
        }

        $to = $this->buildTo();
        $subject = $this->subject;
        $message = $this->buildMessage();
        $headers = $this->buildHeaders();

        return mail($to, $subject, $message, $headers);
    }

    /**
     * Call to check the minimum required fields
     *
     * @return boolean whether or not the email meets the minimum required fields
     */
    protected function checkRequiredFields()
    {
        if (empty($this->to)) {
            return false;
        }
        if (empty($this->subject)) {
            return false;
        }

        if (
            empty($this->plainMessage) &&
            empty($this->htmlMessage) &&
            empty($this->attachments)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Build the recipients from 'to'
     *
     * @return string comma-separated lit of recipients
     */
    protected function buildTo()
    {
       return implode(', ', $this->to);
    }

    /**
     * Long, nasty creater of the actual message, with all the multipart logic you'd never want to see
     *
     * @return string email message
     */
    protected function buildMessage()
    {
      $message = '';
      
      if(isset($this->attachments) && count($this->attachments) > 0)
        $message .= "--{$this->getBoundary()}" . self::LINE_BREAK;
      
      if(
        isset($this->plainMessage) && strlen($this->plainMessage) > 0 &&
        isset($this->htmlMessage) && strlen($this->htmlMessage) > 0)
      {
        if(isset($this->attachments) && count($this->attachments) > 0)
        {
          $message .= "Content-Type: multipart/alternative; boundary={$this->getAlternativeBoundary()}" . self::LINE_BREAK;
          $message .= self::LINE_BREAK;
        }
        $message .= "--{$this->getAlternativeBoundary()}" . self::LINE_BREAK;
        $message .= 'Content-Type: text/plain; charset="iso-8859"' . self::LINE_BREAK;
        $message .= 'Content-Transfer-Encoding: 7bit' . self::LINE_BREAK;
        $message .= self::LINE_BREAK;
        $message .= $this->plainMessage;
        $message .= self::LINE_BREAK;
        $message .= "--{$this->getAlternativeBoundary()}" . self::LINE_BREAK;
        $message .= 'Content-Type: text/html; charset="iso-8859-1"' . self::LINE_BREAK;
        $message .= 'Content-Transfer-Encoding: 7bit' . self::LINE_BREAK;
        $message .= self::LINE_BREAK;
        $message .= $this->htmlMessage;
        $message .= self::LINE_BREAK;
        $message .= "--{$this->getAlternativeBoundary()}--" . self::LINE_BREAK;
        $message .= self::LINE_BREAK;
      }
      else if(isset($this->plainMessage) && strlen($this->plainMessage))
      {
        if(isset($this->attachments) && count($this->attachments) > 0)
        {
          $message .= 'Content-Type: text/plain; charset="iso-8859"' . self::LINE_BREAK;
          $message .= 'Content-Transfer-Encoding: 7bit' . self::LINE_BREAK;
          $message .= self::LINE_BREAK;
        }
        $message .= $this->plainMessage;
        $message .= self::LINE_BREAK;
      }
      else if(isset($this->htmlMessage) && strlen($this->htmlMessage))
      {
        if(isset($this->attachments) && count($this->attachments) > 0)
        {
          $message .= 'Content-Type: text/html; charset="iso-8859-1"' . self::LINE_BREAK;
          $message .= 'Content-Transfer-Encoding: 7bit' . self::LINE_BREAK;
          $message .= self::LINE_BREAK;
        }
        $message .= $this->htmlMessage;
        $message .= self::LINE_BREAK;
      }
      if(isset($this->attachments) && count($this->attachments) > 0)
      {
        foreach($this->attachments as $attachment)
        {
          $message .= "--{$this->getBoundary()}" . self::LINE_BREAK;
          $message .= "Content-Type: {$attachment['type']}; name=\"{$attachment['title']}\"" . self::LINE_BREAK;
          $message .= 'Content-Transfer-Encoding: base64' . self::LINE_BREAK;
          $message .= 'Content-Disposition: attachment' . self::LINE_BREAK;
          $message .= self::LINE_BREAK;
          $message .= $this->buildAttachmentContent($attachment);
          $message .= self::LINE_BREAK;
        }
        $message .= "--{$this->getBoundary()}--" . self::LINE_BREAK;
      }
      return $message;
    }

    /**
     * Private holder for the boundry logic
     * Not called/created unless it's needed
     *
     * @return  string  boundary
     */
    protected function getBoundary()
    {
        if (!isset($this->boundary)) {
            $this->boundary = sprintf('PHP-mixed-%s', uniqid());
        }
        return $this->boundary;
    }

    /**
     * Holder to create the alternative boundry logic
     * Not called/created unless it's needed
     *
     * @return string alternative boundary
     */
    protected function getAlternativeBoundary()
    {
        if (!isset($this->alternativeBoundary)) {
            $this->alternativeBoundary = sprintf('PHP-alternative-%s', uniqid());
        }
        return $this->alternativeBoundary;
    }

    /**
     * Fetcher for the additional headers needed for multipart emails
     *
     * @return  string  headers needed for multipart
     */
    protected function buildHeaders()
    {
      $headers = '';
      foreach($this->headers as $key => $value)
      {
        $headers .= "{$key}: {$value}" . self::LINE_BREAK;
      }
      
      if(count($this->cc) > 0)
        $headers .= 'CC: ' . implode(', ', $this->cc) . self::LINE_BREAK;
      if(count($this->bcc) > 0)
        $headers .= 'BCC: ' . implode(', ', $this->bcc) . self::LINE_BREAK;
      
      if(isset($this->attachments) && count($this->attachments) > 0)
        $headers .= "Content-Type: multipart/mixed; boundary=\"{$this->getBoundary()}\"";
      else if(
        isset($this->plainMessage) && strlen($this->plainMessage) > 0 &&
        isset($this->htmlMessage) && strlen($this->htmlMessage) > 0)
      {
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$this->getAlternativeBoundary()}\"";
      }
      else if(isset($this->htmlMessage) && strlen($this->htmlMessage) > 0)
        $headers .= 'Content-type: text/html; charset="iso-8859-1"';
      
      return $headers;
    }

    /**
     * File reader for attachments
     *
     * @return string binary representation of file, base64'd
     */
    protected function buildAttachmentContent($attachment)
    {
        if (!file_exists($attachment['path'])) {
            return ''; // todo log error
        }

        $handle = fopen($attachment['path'], 'r');
        $contents = fread($handle, filesize($attachment['path']));
        fclose($handle);

        $contents = base64_encode($contents);
        $contents = chunk_split($contents);
        return $contents;
    }
}
