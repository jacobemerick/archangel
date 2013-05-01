<?

include_once 'Archangel.class.php';

// simple example
$mail_sent = Archangel::instance()
	->addTo('to@test.com', 'Awesome Pants')
	->setSubject('Subject')
	->setPlainMessage('This is a plain message, yo.')
	->send();

// more detailed example
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