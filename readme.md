#Archangel
Another PHP mailing script, this time with method chaining
----------------------------------------------------------
Encapsulated utility for sending out emails with PHP. Will separate out plain-text, html, and attachments. Works across several tested email clients, including the picky Microsoft Outlook.

Why Archangel? Well, it's cool (like bow ties), and the technically an 'archangel' is a 'messenger'.


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