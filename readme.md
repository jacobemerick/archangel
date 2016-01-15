# Archangel

[![Build Status](https://travis-ci.org/jacobemerick/archangel.svg?branch=master)](https://travis-ci.org/jacobemerick/archangel)
[![Code Climate](https://codeclimate.com/github/jacobemerick/archangel/badges/gpa.svg)](https://codeclimate.com/github/jacobemerick/archangel)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jacobemerick/archangel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jacobemerick/archangel/?branch=master)

Another PHP mailing script, this time with method chaining
----------------------------------------------------------
Encapsulated utility for sending out emails with PHP. Will separate out plain-text, html, and attachments. Works across several tested email clients, including the picky Microsoft Outlook.

Why Archangel? Well, it's cool (like bow ties) and technically an 'archangel' is a 'messenger'.


Requirements
------------------
- PHP (version 5 or better)
- A server with sendmail functionality


Usage
------------------
There is no default variables that you need to set to run.
Minimum fields to set are:
 - To field (only email address is required)
 - Subject
 - A message (could be plain text, html, or attachment)


Examples
------------------
```php
$mail_sent = Archangel::instance()
	->addTo('to@test.com', 'Awesome Pants')
	->setSubject('Subject')
	->setPlainMessage('This is a plain message, yo.')
	->send();
```

And more complicated...
```php
$mail_sent = Archangel::instance()
	->addTo('to@test.com', 'Awesome Pants')
	->addTo('carlyraejepsen@haha.com', 'Carly Rae')
	->addCC('importantguy@producers.com')
	->setFrom('someone@thesubway.com')
	->setReplyTo('myagent@hometown.us')
	->setSubject('Hey I just met you')
	->setPlainMessage('And this is crazy')
	->setHTMLMessage("<p>But here's my <b>number</b><br />So call me <b>maybe</b></p>")
	->addAttachment('screenshot.jpg', 'image/jpeg', 'some-phone-number.jpg')
	->addAttachment('logo.png', 'image/png')
	->send();
```


Future Enhancements
------------------
 - CC/BCC
 - Multiple attachments
 - Inline attachments via CID
 - Error handling
 - Error triggering
 - Validation
 - SMTP, mayhaps?
 - Encoding options
 - SMTP authentication, why not?
 - Split up get_message() more elegantly. Because, seriously.


Changelog
------------------
v1.0 (2013-04-12)
 - initial release


------------------
 - Project at GitHub [jacobemerck/archangel](https://github.com/jacobemerick/archangel)
 - Jacob Emerick [@jpemeric](http://twitter.com/jpemeric) [jacobemerick.com](http://home.jacobemerick.com/)
