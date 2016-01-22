<?php

namespace Jacobemerick\Archangel;

use PHPUnit_Framework_TestCase;

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
}
