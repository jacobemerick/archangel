<?php

/**
 * This is the main class for Archangel mailer
 * For licensing and examples:
 *
 * @see https://github.com/jacobemerick/archangel
 *
 * @author jacobemerick (http://home.jacobemerick.com/)
 * @version 1.0 (2013-04-12)
 */

final class Archangel
{

	/**
	 * These variables are set with setter methods below
	 */
	private $subject;
	private $reply_to;
	private $plain_message;
	private $html_message;

	/**
	 * Holder for error handling and validation
	 */
	private $passed_validation = TRUE;
	private $validation_error = array();

	/**
	 * Holders for some of the more list-y variable handling
	 */
	private $to_array = array();
	private $cc_array = array();
	private $bcc_array = array();
	private $header_array = array();
	private $attachment_array = array();

	/**
	 * Static pieces that really don't need to change
	 */
	private static $MAILER = 'PHP/%s';
	private static $LINE_BREAK = "\r\n";
	private static $BOUNDARY_FORMAT = 'PHP-mixed-%s';
	private static $BOUNDARY_SALT = 'Boundary Salt';
	private static $ALTERNATIVE_BOUNDARY_FORMAT = 'PHP-alternative-%s';
	private static $ALTERNATIVE_BOUNDARY_SALT = 'Alternative Boundary Salt';

	/**
	 * Standard constructor, sets some of the base (unchanging) fields
	 */
	public function __construct()
	{
		$this->header_array['X-Mailer'] = sprintf(self::$MAILER, phpversion());
	}

	/**
	 * Setter method for adding recipients
	 * This class only sends a single email, so all recipients can see each other
	 *
	 * @param	string	$address	email address for the recipient
	 * @param	string	$title		name of the recipient (optional)
	 * @return	object	instantiated $this
	 */
	public function addTo($address, $title = '')
	{
		if($this->is_valid_email_address($address) && $this->is_valid_email_title($title))
			$this->to_array[] = ($title != '') ? "\"{$title}\" <{$address}>" : "{$address}";
		
		return $this;
	}

	/**
	 * Setter method for adding cc recipients
	 *
	 * @param	string	$address	email address for the cc recipient
	 * @param	string	$title		name of the cc recipient (optional)
	 * @return	object	instantiated $this
	 */
	public function addCC($address, $title = '')
	{
		if($this->is_valid_email_address($address) && $this->is_valid_email_title($title))
			$this->cc_array[] = ($title != '') ? "\"{$title}\" <{$address}>" : "{$address}";
		
		return $this;
	}

	/**
	 * Setter method for adding bcc recipients
	 *
	 * @param	string	$address	email address for the bcc recipient
	 * @param	string	$title		name of the bcc recipient (optional)
	 * @return	object	instantiated $this
	 */
	public function addBCC($address, $title = '')
	{
		if($this->is_valid_email_address($address) && $this->is_valid_email_title($title))
			$this->bcc_array[] = ($title != '') ? "\"{$title}\" <{$address}>" : "{$address}";
		
		return $this;
	}

	/**
	 * Setter method for setting the single 'from' field
	 *
	 * @param	string	$address	email address for the sender
	 * @param	string	$title		name of the sender (optional)
	 * @return	object	instantiated $this
	 */
	public function setFrom($address, $title = '')
	{
		if($this->is_valid_email_address($address) && $this->is_valid_email_title($title))
			$this->header_array['From'] = ($title != '') ? "\"{$title}\" <{$address}>" : "{$address}";
		
		return $this;
	}

	/**
	 * Setter method for setting the single 'reply-to' field
	 *
	 * @param	string	$address	email address for the reply-to
	 * @param	string	$title		name of the reply-to (optional)
	 * @return	object	instantiated $this
	 */
	public function setReplyTo($address, $title = '')
	{
		if($this->is_valid_email_address($address) && $this->is_valid_email_title($title))
			$this->header_array['Reply-To'] = ($title != '') ? "\"{$title}\" <{$address}>" : "{$address}";
		
		return $this;
	}

	/**
	 * Setter method for setting a subject
	 *
	 * @param	string	$subject	subject for the email
	 * @return	object	instantiated $this
	 */
	public function setSubject($subject)
	{
		if($this->is_valid_subject($subject))
			$this->subject = $subject;
		
		return $this;
	}

	/**
	 * Setter method for the plain text message
	 *
	 * @param	string	$message	the plain-text message
	 * @return	object	insantiated $this
	 */
	public function setPlainMessage($message)
	{
		$this->plain_message = $message;
		
		return $this;
	}

	/**
	 * Setter method for the html message
	 *
	 * @param	string	$message	the html message
	 * @return	object	insantiated $this
	 */
	public function setHTMLMessage($message)
	{
		$this->html_message = $message;
		
		return $this;
	}

	/**
	 * Setter method for adding attachments
	 *
	 * @param	string	$path	the full path of the attachment
	 * @param	string	$type	mime type of the file
	 * @param	string	$title	the title of the attachment (optional)
	 * @return	object	insantiated $this
	 */
	public function addAttachment($path, $type, $title = '')
	{
		$this->attachment_array[] = (object) array(
			'path' => $path,
			'type' => $type,
			'title' => $title);
		
		return $this;
	}

	/**
	 * The executing step, the actual sending of the email
	 * First checks to make sure the minimum fields are set (returns false if they are not)
	 * Second it attempts to send the mail with php's mail() (returns false if it fails)
	 *
	 * return	boolean	whether or not the email was valid & sent
	 */
	public function send()
	{
		if($this->passed_validation === FALSE)
			return false;
		
		if(!$this->check_required_fields())
			return false;
		
		$to = $this->get_to();
		$subject = $this->subject;
		$message = $this->get_message();
		$additional_headers = $this->get_additional_headers();
		
		return mail($to, $subject, $message, $additional_headers);
	}

	/**
	 * Main instantiator for the class
	 *
	 * @return	object	instantiated $this
	 */
	public static function instance()
	{
		return new Archangel();
	}

	/**
	 * Private call to check the minimum required fields
	 *
	 * @return	boolean	whether or not the email meets the minimum required fields
	 */
	private function check_required_fields()
	{
		return (
			count($this->to_array) > 0 &&
			(isset($this->subject) && strlen($this->subject) > 0) &&
			(
				(isset($this->plain_message) && strlen($this->plain_message) > 0) ||
				(isset($this->html_message) && strlen($this->html_message) > 0) ||
				(isset($this->attachment_array) && count($this->attachment_array) > 0)));
	}

	/**
	 * Private function to collect the recipients from to_array
	 *
	 * @return	string	comma-separated lit of recipients
	 */
	private function get_to()
	{
		return implode(', ', $this->to_array);
	}

	/**
	 * Long, nasty creater of the actual message, with all the multipart logic you'd never want to see
	 *
	 * @return	string	email message
	 */
	private function get_message()
	{
		$message = '';
		
		if(isset($this->attachment_array) && count($this->attachment_array) > 0)
			$message .= "--{$this->get_boundary()}" . self::$LINE_BREAK;
		
		if(
			isset($this->plain_message) && strlen($this->plain_message) > 0 &&
			isset($this->html_message) && strlen($this->html_message) > 0)
		{
			if(isset($this->attachment_array) && count($this->attachment_array) > 0)
			{
				$message .= "Content-Type: multipart/alternative; boundary={$this->get_alternative_boundary()}" . self::$LINE_BREAK;
				$message .= self::$LINE_BREAK;
			}
			$message .= "--{$this->get_alternative_boundary()}" . self::$LINE_BREAK;
			$message .= 'Content-Type: text/plain; charset="iso-8859"' . self::$LINE_BREAK;
			$message .= 'Content-Transfer-Encoding: 7bit' . self::$LINE_BREAK;
			$message .= self::$LINE_BREAK;
			$message .= $this->plain_message;
			$message .= self::$LINE_BREAK;
			$message .= "--{$this->get_alternative_boundary()}" . self::$LINE_BREAK;
			$message .= 'Content-Type: text/html; charset="iso-8859-1"' . self::$LINE_BREAK;
			$message .= 'Content-Transfer-Encoding: 7bit' . self::$LINE_BREAK;
			$message .= self::$LINE_BREAK;
			$message .= $this->html_message;
			$message .= self::$LINE_BREAK;
			$message .= "--{$this->get_alternative_boundary()}--" . self::$LINE_BREAK;
			$message .= self::$LINE_BREAK;
		}
		else if(isset($this->plain_message) && strlen($this->plain_message))
		{
			if(isset($this->attachment_array) && count($this->attachment_array) > 0)
			{
				$message .= 'Content-Type: text/plain; charset="iso-8859"' . self::$LINE_BREAK;
				$message .= 'Content-Transfer-Encoding: 7bit' . self::$LINE_BREAK;
				$message .= self::$LINE_BREAK;
			}
			$message .= $this->plain_message;
			$message .= self::$LINE_BREAK;
		}
		else if(isset($this->html_message) && strlen($this->html_message))
		{
			if(isset($this->attachment_array) && count($this->attachment_array) > 0)
			{
				$message .= 'Content-Type: text/html; charset="iso-8859-1"' . self::$LINE_BREAK;
				$message .= 'Content-Transfer-Encoding: 7bit' . self::$LINE_BREAK;
				$message .= self::$LINE_BREAK;
			}
			$message .= $this->html_message;
			$message .= self::$LINE_BREAK;
		}
		if(isset($this->attachment_array) && count($this->attachment_array) > 0)
		{
			foreach($this->attachment_array as $attachment)
			{
				$message .= "--{$this->get_boundary()}" . self::$LINE_BREAK;
				$message .= "Content-Type: {$attachment->type}; name=\"{$attachment->title}\"" . self::$LINE_BREAK;
				$message .= 'Content-Transfer-Encoding: base64' . self::$LINE_BREAK;
				$message .= 'Content-Disposition: attachment' . self::$LINE_BREAK;
				$message .= self::$LINE_BREAK;
				$message .= $this->get_attachment_content($attachment);
				$message .= self::$LINE_BREAK;
			}
			$message .= "--{$this->get_boundary()}--" . self::$LINE_BREAK;
		}
		return $message;
	}

	/**
	 * Private holder for the boundry logic
	 * Not called/created unless it's needed
	 *
	 * @return	string	boundary
	 */
	private $boundary;
	private function get_boundary()
	{
		if(!isset($this->boundary))
			$this->boundary = sprintf(self::$BOUNDARY_FORMAT, md5(date('r', time()) . self::$BOUNDARY_SALT));
		return $this->boundary;
	}

	/**
	 * Private holder for the alternative boundry logic
	 * Not called/created unless it's needed
	 *
	 * @return	string	alternative boundary
	 */
	private $alternative_boundary;
	private function get_alternative_boundary()
	{
		if(!isset($this->alternative_boundary))
			$this->alternative_boundary = sprintf(self::$ALTERNATIVE_BOUNDARY_FORMAT, md5(date('r', time()) . self::$ALTERNATIVE_BOUNDARY_SALT));
		return $this->alternative_boundary;
	}

	/**
	 * Fetcher for the additional headers needed for multipart emails
	 *
	 * @return	string	headers needed for multipart
	 */
	private function get_additional_headers()
	{
		$headers = '';
		foreach($this->header_array as $key => $value)
		{
			$headers .= "{$key}: {$value}" . self::$LINE_BREAK;
		}
		
		if(count($this->cc_array) > 0)
			$headers .= 'CC: ' . implode(', ', $this->cc_array) . self::$LINE_BREAK;
		if(count($this->bcc_array) > 0)
			$headers .= 'BCC: ' . implode(', ', $this->bcc_array) . self::$LINE_BREAK;
		
		if(isset($this->attachment_array) && count($this->attachment_array) > 0)
			$headers .= "Content-Type: multipart/mixed; boundary=\"{$this->get_boundary()}\"";
		else if(
			isset($this->plain_message) && strlen($this->plain_message) > 0 &&
			isset($this->html_message) && strlen($this->html_message) > 0)
		{
			$headers .= "Content-Type: multipart/alternative; boundary=\"{$this->get_alternative_boundary()}\"";
		}
		else if(isset($this->html_message) && strlen($this->html_message) > 0)
			$headers .= 'Content-type: text/html; charset="iso-8859-1"';
		
		return $headers;
	}

	/**
	 * File reader for attachments
	 *
	 * @return	string	binary representation of file, base64'd
	 */
	private function get_attachment_content($attachment)
	{
		$handle = fopen($attachment->path, 'r');
		$contents = fread($handle, filesize($attachment->path));
		fclose($handle);
		
		$contents = base64_encode($contents);
		$contents = chunk_split($contents);
		return $contents;
	}

	/**
	 * stub for email address checking
	 */
	private function is_valid_email_address($string)
	{
		if(strlen($string) < 1)
			return $this->fail_validation("{$string} is an invalid email address!");
		
		return true;
	}

	/**
	 * stub for email title checking
	 */
	private function is_valid_email_title($string)
	{
		return true;
	}

	/**
	 * stub for subject checking
	 */
	private function is_valid_subject($string)
	{
		if(strlen($string) < 1)
			return $this->fail_validation("{$string} is an invalid email subject!");
		
		return true;
	}

	/**
	 * holder for all validation fails
	 */
	private function fail_validation($message)
	{
		$this->passed_validation = FALSE;
		$this->validation_error[] = $message;
		
		return false;
	}

	public function get_validation_errors()
	{
		return $this->validation_error();
	}

}