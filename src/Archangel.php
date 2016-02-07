<?php

namespace Jacobemerick\Archangel;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * This is the main class for Archangel mailer
 * For licensing and examples:
 * @see https://github.com/jacobemerick/archangel
 *
 * @author jacobemerick (http://home.jacobemerick.com/)
 */
class Archangel implements LoggerAwareInterface
{

    /** @var boolean $isTestMode */
    protected $isTestMode;

    /** @var string $subject */
    protected $subject;

    /** @var array $toAddresses */
    protected $toAddresses = array();

    /** @var array $headers */
    protected $headers = array();

    /** @var string $plainMessage */
    protected $plainMessage;

    /** @var string $htmlMessage */
    protected $htmlMessage;

    /** @var array $attachments */
    protected $attachments = array();

    /** @var string $boundaryMixed */
    protected $boundaryMixed;

    /** @var string $boundaryAlternative */
    protected $boundaryAlternative;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string LINE_BREAK */
    const LINE_BREAK = "\r\n";

    /**
     * @param string  $mailer
     * @param boolean $isTestMode
     */
    public function __construct($mailer = null, $isTestMode = false)
    {
        if (is_null($mailer)) {
            $mailer = sprintf('PHP/%s', phpversion());
        }
        $this->headers['X-Mailer'] = $mailer;
        $this->isTestMode = $isTestMode;

        $this->logger = new NullLogger();
        $this->boundaryMixed = sprintf('PHP-mixed-%s', uniqid());
        $this->boundaryAlternative = sprintf('PHP-alternative-%s', uniqid());
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return object instantiated $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
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
        array_push(
            $this->toAddresses,
            $this->formatEmailAddress($address, $title)
        );

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
        if (!isset($this->headers['CC'])) {
            $this->headers['CC'] = array();
        }

        array_push(
            $this->headers['CC'],
            $this->formatEmailAddress($address, $title)
        );

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
        if (!isset($this->headers['BCC'])) {
            $this->headers['BCC'] = array();
        }

        array_push(
            $this->headers['BCC'],
            $this->formatEmailAddress($address, $title)
        );

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
        $this->headers['From'] = $this->formatEmailAddress($address, $title);

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
        $this->headers['Reply-To'] = $this->formatEmailAddress($address, $title);

        return $this;
    }

    /**
     * @param string $address
     * @param string $title
     *
     * @return string
     */
    protected function formatEmailAddress($address, $title)
    {
        if (!empty($title)) {
            $address = sprintf('"%s" <%s>', $title, $address);
        }
        return $address;
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
     * @return boolean whether or not the email was valid & sent
     */
    public function send()
    {
        if (!$this->checkRequiredFields()) {
            $this->logger->error('Minimum required fields not filled out, cannot send Archangel mail.');
            return false;
        }

        $recipients = $this->buildTo();
        $subject = $this->subject;
        $message = (empty($this->attachments)) ? $this->buildMessage() : $this->buildMessageWithAttachments();
        $headers = $this->buildHeaders();

        $debugMessage = array(
            'Triggered send on Archangel mail.',
            "Recipients: {$recipients}",
            "Subject: {$subject}",
            "Message: {$message}",
            "Headers: {$headers}",
        );
        $this->logger->debug(implode(' || ', $debugMessage));

        if ($this->isTestMode) {
            return true;
        }
        return mail($recipients, $subject, $message, $headers);
    }

    /**
     * Call to check the minimum required fields
     *
     * @return boolean whether or not the email meets the minimum required fields
     */
    protected function checkRequiredFields()
    {
        if (empty($this->toAddresses)) {
            return false;
        }
        if (empty($this->subject)) {
            return false;
        }

        if (empty($this->plainMessage) && empty($this->htmlMessage) && empty($this->attachments)) {
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
        return implode(', ', $this->toAddresses);
    }

    /**
     * Returns a simple email message without attachments
     *
     * @return string email message
     */
    protected function buildMessage()
    {
        if (empty($this->plainMessage) && empty($this->htmlMessage)) {
            return '';
        }
        if (!empty($this->plainMessage) && empty($this->htmlMessage)) {
            return $this->plainMessage;
        }
        if (empty($this->plainMessage) && !empty($this->htmlMessage)) {
            return $this->htmlMessage;
        }

        $message = array();
        array_push($message, "--{$this->boundaryAlternative}");
        $message = array_merge($message, $this->buildPlainMessageHeader());
        array_push($message, $this->plainMessage);
        array_push($message, "--{$this->boundaryAlternative}");
        $message = array_merge($message, $this->buildHtmlMessageHeader());
        array_push($message, $this->htmlMessage);
        array_push($message, "--{$this->boundaryAlternative}--");

        return implode(self::LINE_BREAK, $message);
    }

    /**
     * Build multi-part message with attachments
     *
     * @return string email message
     */
    protected function buildMessageWithAttachments()
    {
        $message = array();

        if (!empty($this->plainMessage) || !empty($this->htmlMessage)) {
            array_push($message, "--{$this->boundaryMixed}");
        }

        if (!empty($this->plainMessage) && !empty($this->htmlMessage)) {
            array_push($message, "Content-Type: multipart/alternative; boundary={$this->boundaryAlternative}");
            array_push($message, '');
            array_push($message, "--{$this->boundaryAlternative}");
            $message = array_merge($message, $this->buildPlainMessageHeader());
            array_push($message, $this->plainMessage);
            array_push($message, "--{$this->boundaryAlternative}");
            $message = array_merge($message, $this->buildHtmlMessageHeader());
            array_push($message, $this->htmlMessage);
            array_push($message, "--{$this->boundaryAlternative}--");
            array_push($message, '');
        } elseif (!empty($this->plainMessage)) {
            $message = array_merge($message, $this->buildPlainMessageHeader());
            array_push($message, $this->plainMessage);
        } elseif (!empty($this->htmlMessage)) {
            $message = array_merge($message, $this->buildHtmlMessageHeader());
            array_push($message, $this->htmlMessage);
        }
        foreach ($this->attachments as $attachment) {
            array_push($message, "--{$this->boundaryMixed}");
            array_push($message, "Content-Type: {$attachment['type']}; name=\"{$attachment['title']}\"");
            array_push($message, 'Content-Transfer-Encoding: base64');
            array_push($message, 'Content-Disposition: attachment');
            array_push($message, '');
            array_push($message, $this->buildAttachmentContent($attachment['path']));
        }
        array_push($message, "--{$this->boundaryMixed}--");

        return implode(self::LINE_BREAK, $message);
    }


    /**
     * Shared holder for the plain message header
     *
     * @return array
     */
    protected function buildPlainMessageHeader()
    {
        return array(
            'Content-Type: text/plain; charset="iso-8859"',
            'Content-Transfer-Encoding: 7bit',
            '',
        );
    }

    /**
     * Shared holder for the html message header
     *
     * @return array
     */
    protected function buildHtmlMessageHeader()
    {
        return array(
            'Content-Type: text/html; charset="iso-8859-1"',
            'Content-Transfer-Encoding: 7bit',
            '',
        );
    }

    /**
     * Builder for the additional headers needed for multipart emails
     *
     * @return string headers needed for multipart
     */
    protected function buildHeaders()
    {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            if ($key == 'CC' || $key == 'BCC') {
                $value = implode(', ', $value);
            }
            array_push($headers, sprintf('%s: %s', $key, $value));
        }

        if (!empty($this->attachments)) {
            array_push(
                $headers,
                "Content-Type: multipart/mixed; boundary=\"{$this->boundaryMixed}\""
            );
        } elseif (!empty($this->plainMessage) && !empty($this->htmlMessage)) {
            array_push(
                $headers,
                "Content-Type: multipart/alternative; boundary=\"{$this->boundaryAlternative}\""
            );
        } elseif (!empty($this->htmlMessage)) {
            array_push(
                $headers,
                'Content-type: text/html; charset="iso-8859-1"'
            );
        }

        return implode(self::LINE_BREAK, $headers);
    }

    /**
     * File reader for attachments
     *
     * @param string $path filepath of the attachment
     *
     * @return string binary representation of file, base64'd
     */
    protected function buildAttachmentContent($path)
    {
        if (!file_exists($path)) {
            $this->logger->error("Could not find file {$path} for attaching to Archangel mail.");
            return '';
        }

        $handle = fopen($path, 'r');
        $contents = fread($handle, filesize($path));
        fclose($handle);

        $contents = base64_encode($contents);
        $contents = chunk_split($contents);
        return $contents;
    }
}
