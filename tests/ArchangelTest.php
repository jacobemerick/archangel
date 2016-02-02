<?php

namespace Jacobemerick\Archangel;

use PHPUnit_Framework_TestCase;
use ReflectionClass;

class ArchangelTest extends PHPUnit_Framework_TestCase
{

    public function testIsInstanceOfArchangel()
    {
        $archangel = new Archangel();

        $this->assertInstanceOf('Jacobemerick\Archangel\Archangel', $archangel);
    }

    public function testIsLoggerAwareInterface()
    {
        $archangel = new Archangel();

        $this->assertInstanceOf('Psr\Log\LoggerAwareInterface', $archangel);
    }

    public function testConstructSetsDefaultMailer()
    {
        $archangel = new Archangel();
        $mailer = sprintf('PHP/%s', phpversion());
        $headers = array('X-Mailer' => $mailer);

        $this->assertAttributeEquals($headers, 'headers', $archangel);
    }

    public function testConstructOverridesMailer()
    {
        $archangel = new Archangel('AwesomeMailer');
        $headers = array('X-Mailer' => 'AwesomeMailer');

        $this->assertAttributeEquals($headers, 'headers', $archangel);
    }

    public function testConstructSetsNullLogger()
    {
        $archangel = new Archangel();

        $this->assertAttributeInstanceOf('Psr\Log\NullLogger', 'logger', $archangel);
    }

    public function testConstructSetsBoundaries()
    {
        $archangel = new Archangel();
        $expectedBoundaryMixed = sprintf('PHP-mixed-%s', uniqid());
        $expectedBoundaryAlternative = sprintf('PHP-alternative-%s', uniqid());

        $this->assertAttributeEquals($expectedBoundaryMixed, 'boundaryMixed', $archangel);
        $this->assertAttributeEquals($expectedBoundaryAlternative, 'boundaryAlternative', $archangel);
    }

    public function testSetLogger()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $archangel = new Archangel();
        $archangel->setLogger($logger);

        $this->assertAttributeSame($logger, 'logger', $archangel);
    }

    public function testAddTo()
    {
        $archangel = new Archangel();
        $archangel->addTo('test@example.com');

        $this->assertAttributeContains('test@example.com', 'toAddresses', $archangel);
    }

    public function testAddToMultiple()
    {
        $archangel = new Archangel();
        $archangel->addTo('testOne@example.com');
        $archangel->addTo('testTwo@example.com');

        $this->assertAttributeContains('testOne@example.com', 'toAddresses', $archangel);
        $this->assertAttributeContains('testTwo@example.com', 'toAddresses', $archangel);
    }

    public function testAddToWithTitle()
    {
        $archangel = new Archangel();
        $archangel->addTo('test@example.com', 'Mr. Test Alot');

        $this->assertAttributeContains('"Mr. Test Alot" <test@example.com>', 'toAddresses', $archangel);
    }

    public function testAddCc()
    {
        $archangel = new Archangel();
        $archangel->addCc('test@example.com');

        $this->assertAttributeContains('test@example.com', 'ccAddresses', $archangel);
    }

    public function testAddCcMultiple()
    {
        $archangel = new Archangel();
        $archangel->addCc('testOne@example.com');
        $archangel->addCc('testTwo@example.com');

        $this->assertAttributeContains('testOne@example.com', 'ccAddresses', $archangel);
        $this->assertAttributeContains('testTwo@example.com', 'ccAddresses', $archangel);
    }

    public function testAddCcWithTitle()
    {
        $archangel = new Archangel();
        $archangel->addCc('test@example.com', 'Mr. Test Alot');

        $this->assertAttributeContains('"Mr. Test Alot" <test@example.com>', 'ccAddresses', $archangel);
    }

    public function testAddBcc()
    {
        $archangel = new Archangel();
        $archangel->addBcc('test@example.com');

        $this->assertAttributeContains('test@example.com', 'bccAddresses', $archangel);
    }

    public function testAddBccMultiple()
    {
        $archangel = new Archangel();
        $archangel->addBcc('testOne@example.com');
        $archangel->addBcc('testTwo@example.com');

        $this->assertAttributeContains('testOne@example.com', 'bccAddresses', $archangel);
        $this->assertAttributeContains('testTwo@example.com', 'bccAddresses', $archangel);
    }

    public function testAddBccWithTitle()
    {
        $archangel = new Archangel();
        $archangel->addBcc('test@example.com', 'Mr. Test Alot');

        $this->assertAttributeContains('"Mr. Test Alot" <test@example.com>', 'bccAddresses', $archangel);
    }

    public function testSetFrom()
    {
        $archangel = new Archangel();
        $archangel->setFrom('test@example.com');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('From' => 'test@example.com'), $headers);
    }

    public function testSetFromMultiple()
    {
        $archangel = new Archangel();
        $archangel->setFrom('testOne@example.com');
        $archangel->setFrom('testTwo@example.com');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('From' => 'testTwo@example.com'), $headers);
        $this->assertNotContains('testOne@example.com', $headers);
    }

    public function testSetFromWithTitle()
    {
        $archangel = new Archangel();
        $archangel->setFrom('test@example.com', 'Mr. Test Alot');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('From' => '"Mr. Test Alot" <test@example.com>'), $headers);
    }

    public function testSetReplyTo()
    {
        $archangel = new Archangel();
        $archangel->setReplyTo('test@example.com');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('Reply-To' => 'test@example.com'), $headers);
    }

    public function testSetReplyToMultiple()
    {
        $archangel = new Archangel();
        $archangel->setReplyTo('testOne@example.com');
        $archangel->setReplyTo('testTwo@example.com');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('Reply-To' => 'testTwo@example.com'), $headers);
        $this->assertNotContains('testOne@example.com', $headers);
    }

    public function testSetReplyToWithTitle()
    {
        $archangel = new Archangel();
        $archangel->setReplyTo('test@example.com', 'Mr. Test Alot');
        $headersProperty = $this->getProtectedProperty('headers');
        $headers = $headersProperty->getValue($archangel);

        $this->assertArraySubset(array('Reply-To' => '"Mr. Test Alot" <test@example.com>'), $headers);
    }

    public function testFormatEmailAddress()
    {
        $archangel = new Archangel();
        $formatMethod = $this->getProtectedMethod('formatEmailAddress');
        $formattedEmail = $formatMethod->invokeArgs($archangel, array('test@example.com', ''));

        $this->assertEquals('test@example.com', $formattedEmail);
    }

    public function testFormatEmailAddressWithTitle()
    {
        $archangel = new Archangel();
        $formatMethod = $this->getProtectedMethod('formatEmailAddress');
        $formattedEmail = $formatMethod->invokeArgs($archangel, array('test@example.com', 'Mr. Test Alot'));

        $this->assertEquals('"Mr. Test Alot" <test@example.com>', $formattedEmail);
    }

    public function testSetSubject()
    {
        $archangel = new Archangel();
        $archangel->setSubject('Test Subject');

        $this->assertAttributeEquals('Test Subject', 'subject', $archangel);
    }

    public function testSetPlainMessage()
    {
        $archangel = new Archangel();
        $archangel->setPlainMessage('Plain text message');

        $this->assertAttributeEquals('Plain text message', 'plainMessage', $archangel);
    }

    public function testSetHTMLMessage()
    {
        $archangel = new Archangel();
        $archangel->setHTMLMessage('<p>An HTML message.</p>');

        $this->assertAttributeEquals('<p>An HTML message.</p>', 'htmlMessage', $archangel);
    }

    public function testAddAttachment()
    {
        $archangel = new Archangel();
        $archangel->addAttachment('path', 'type');

        $this->assertAttributeContains(
            array('path' => 'path', 'type' => 'type', 'title' => ''),
            'attachments',
            $archangel
        );
    }

    public function testAddAttachmentMultiple()
    {
        $archangel = new Archangel();
        $archangel->addAttachment('pathOne', 'typeOne');
        $archangel->addAttachment('pathTwo', 'typeTwo');

        $this->assertAttributeContains(
            array('path' => 'pathOne', 'type' => 'typeOne', 'title' => ''),
            'attachments',
            $archangel
        );
        $this->assertAttributeContains(
            array('path' => 'pathTwo', 'type' => 'typeTwo', 'title' => ''),
            'attachments',
            $archangel
        );
    }

    public function testAddAttachmentWithTitle()
    {
        $archangel = new Archangel();
        $archangel->addAttachment('path', 'type', 'title');

        $this->assertAttributeContains(
            array('path' => 'path', 'type' => 'type', 'title' => 'title'),
            'attachments',
            $archangel
        );
    }

    public function testSend()
    {
        $archangel = new Archangel();
        $archangel->addTo('test@example.com');
        $archangel->setSubject('Test Subject');
        $archangel->setPlainMessage('Plain text message');
        $response = $archangel->send();

        $expectedResponse = array(
            'to' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Plain text message' . Archangel::LINE_BREAK,
            'headers' => 'X-Mailer: PHP/6.0.0' . Archangel::LINE_BREAK,
        );

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @dataProvider dataCheckRequiredFields
     */
    public function testCheckRequiredFields(
        $expectedResult,
        $toAddresses,
        $subject,
        $plainMessage,
        $htmlMessage,
        $attachments
    ) {
        $archangel = new Archangel();

        if (!empty($toAddresses)) {
            $toAddressesProperty = $this->getProtectedProperty('toAddresses');
            $toAddressesProperty->setValue($archangel, $toAddresses);
        }

        if (!empty($subject)) {
            $subjectProperty = $this->getProtectedProperty('subject');
            $subjectProperty->setValue($archangel, $subject);
        }

        if (!empty($plainMessage)) {
            $plainMessageProperty = $this->getProtectedProperty('plainMessage');
            $plainMessageProperty->setValue($archangel, $plainMessage);
        }

        if (!empty($htmlMessage)) {
            $htmlMessageProperty = $this->getProtectedProperty('htmlMessage');
            $htmlMessageProperty->setValue($archangel, $htmlMessage);
        }

        if (!empty($attachments)) {
            $attachmentsProperty = $this->getProtectedProperty('attachments');
            $attachmentsProperty->setValue($archangel, $attachments);
        }

        $checkMethod = $this->getProtectedMethod('checkRequiredFields');
        $isValid = $checkMethod->invoke($archangel);

        if ($expectedResult == true) {
            $this->assertTrue($isValid);
            return;
        }
        $this->assertNotTrue($isValid);
    }

    public function dataCheckRequiredFields()
    {
        return array(
            array(
                'expectedResult' => false,
                'toAddresses' => array(),
                'subject' => '',
                'plainMessage' => '',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => false,
                'toAddresses' => array('test@example.com'),
                'subject' => '',
                'plainMessage' => '',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => false,
                'toAddresses' => array('test@example.com'),
                'subject' => 'Test Subject',
                'plainMessage' => '',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => false,
                'toAddresses' => array(),
                'subject' => 'Test Subject',
                'plainMessage' => '',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => false,
                'toAddresses' => array(),
                'subject' => 'Test Subject',
                'plainMessage' => 'Plain text message',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => true,
                'toAddresses' => array('test@example.com'),
                'subject' => 'Test Subject',
                'plainMessage' => 'Plain text message',
                'htmlMessage' => '',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => true,
                'toAddresses' => array('test@example.com'),
                'subject' => 'Test Subject',
                'plainMessage' => '',
                'htmlMessage' => '<p>An HTML message.</p>',
                'attachments' => array(),
            ),
            array(
                'expectedResult' => true,
                'toAddresses' => array('test@example.com'),
                'subject' => 'Test Subject',
                'plainMessage' => '',
                'htmlMessage' => '',
                'attachments' => array(
                    array('path' => 'path', 'type' => 'type'),
                ),
            ),
            array(
                'expectedResult' => true,
                'toAddresses' => array('test@example.com'),
                'subject' => 'Test Subject',
                'plainMessage' => 'Plain text message',
                'htmlMessage' => '<p>An HTML message.</p>',
                'attachments' => array(
                    array('path' => 'path', 'type' => 'type'),
                ),
            ),
       );
    }

    public function testBuildTo()
    {
        $archangel = new Archangel();
        $addressesProperty = $this->getProtectedProperty('toAddresses');
        $addressesProperty->setValue($archangel, array('test@example.com'));
        $buildMethod = $this->getProtectedMethod('buildTo');
        $toAddresses = $buildMethod->invoke($archangel);

        $this->assertEquals('test@example.com', $toAddresses);
    }

    public function testBuildToMultiple()
    {
        $archangel = new Archangel();
        $addressesProperty = $this->getProtectedProperty('toAddresses');
        $addressesProperty->setValue($archangel, array('testOne@example.com', 'testTwo@example.com'));
        $buildMethod = $this->getProtectedMethod('buildTo');
        $toAddresses = $buildMethod->invoke($archangel);

        $this->assertEquals('testOne@example.com, testTwo@example.com', $toAddresses);
    }

    protected function getProtectedProperty($property)
    {
        $reflectedArchangel = new ReflectionClass('Jacobemerick\Archangel\Archangel');
        $reflectedProperty = $reflectedArchangel->getProperty($property);
        $reflectedProperty->setAccessible(true);

        return $reflectedProperty;
    }

    protected function getProtectedMethod($method)
    {
        $reflectedArchangel = new ReflectionClass('Jacobemerick\Archangel\Archangel');
        $reflectedMethod = $reflectedArchangel->getMethod($method);
        $reflectedMethod->setAccessible(true);

        return $reflectedMethod;
    }
}
