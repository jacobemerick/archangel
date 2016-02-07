# Archangel

[![Build Status](https://travis-ci.org/jacobemerick/archangel.svg?branch=master)](https://travis-ci.org/jacobemerick/archangel)
[![Code Climate](https://codeclimate.com/github/jacobemerick/archangel/badges/gpa.svg)](https://codeclimate.com/github/jacobemerick/archangel)
[![Test Coverage](https://codeclimate.com/github/jacobemerick/archangel/badges/coverage.svg)](https://codeclimate.com/github/jacobemerick/archangel/coverage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jacobemerick/archangel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jacobemerick/archangel/?branch=master)

Encapsulated utility for sending out emails with PHP. Will separate out plain-text, html, and attachments. Works across several tested email clients, including the picky Microsoft Outlook.

Why Archangel? Well, it's cool (like bow ties) and technically an 'archangel' is a 'messenger'.

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Archangel.

```bash
$ composer require jacobemerick/archangel "^2.0"
```

This will install Archangel and it's dependencies. It requires PHP 5.3.0 or newer and sendmail functionality.

## Usage

There are some minimum values to set before Archangel will attempt to send a mail. They are:
 - to address
 - subject
 - some type of message (plain text, html, or attachment(s))

Archangel can work with or without method chaining. In the below examples everything is chained, but you don't have to structure your calling this way - it is totally up to you.

### Basic structure

Basic structure of a chained vs unchained request.
```php
(new Jacobemerick\Archangel\Archangel())
  ->addTo('email@example.com')
  ->setSubject('Test Subject')
  ->setPlainMessage('This is a rather plain message.')
  ->send();

$archangel = new Jacobemerick\Archangel\Archangel();
$archangel->addTo('email@example.com');
$archangel->setSubject('Test Subject');
$archangel->setPlainMessage('This is a rather plain message.');
$archangel->send();
```

### Future Todos
 - add in more documentation

## License

Archangel is licensed under the MIT license. See [License File](LICENSE.md) for more information.
