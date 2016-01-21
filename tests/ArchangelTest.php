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
}
