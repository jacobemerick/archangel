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
        $setHeaders = $this->getProtectedValue($archangel, 'headers');

        $this->assertArraySubset(array('From' => 'test@example.com'), $setHeaders);
    }

    public function testSetFromMultiple()
    {
        $archangel = new Archangel();
        $archangel->setFrom('testOne@example.com');
        $archangel->setFrom('testTwo@example.com');
        $setHeaders = $this->getProtectedValue($archangel, 'headers');

        $this->assertArraySubset(array('From' => 'testTwo@example.com'), $setHeaders);
        $this->assertNotContains('testOne@example.com', $setHeaders);
    }

    public function testSetFromWithTitle()
    {
        $archangel = new Archangel();
        $archangel->setFrom('test@example.com', 'Mr. Test Alot');
        $setHeaders = $this->getProtectedValue($archangel, 'headers');

        $this->assertArraySubset(array('From' => '"Mr. Test Alot" <test@example.com>'), $setHeaders);
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

    protected function getProtectedValue($archangel, $property)
    {
        $reflectedArchangel = new ReflectionClass($archangel);
        $reflectedProperty = $reflectedArchangel->getProperty($property);
        $reflectedProperty->setAccessible(true);

        return $reflectedProperty->getValue($archangel);
    }

    protected function getProtectedMethod($method)
    {
        $reflectedArchangel = new ReflectionClass('Jacobemerick\Archangel\Archangel');
        $reflectedMethod = $reflectedArchangel->getMethod($method);
        $reflectedMethod->setAccessible(true);

        return $reflectedMethod;
    }
}
