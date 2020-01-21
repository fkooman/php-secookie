# Introduction

This is a simple and secure cookie and session library written in PHP. It does 
NOT use any of PHP's built-in cookie/session functions.

An extensive set of unit tests are included, testing all aspects of the 
library.

Many thanks to Jørn Åne de Jong (Uninett) for invaluable feedback during the
development.

# Why

The existing PHP way of using and configuring cookies and sessions is 
complicated and not secure by default. It has various configuration flags in 
`php.ini` that also vary in different versions of PHP. There are some 
libraries that improve this, but they usually require a PHP version >= 5.4. 
This library support all version of PHP >= 5.4, including all versions of 
PHP 7.

For our application we also require support of multiple parallel sessions, this
does not seem possible with PHP. With the introduction of the `SameSite` cookie 
value, this became even more important.

It turns out, the easiest way to proceed is to write a library that avoids
using PHP's built-in session support and use secure defaults (famous last 
words). The most difficult part is the 
[session locking](https://ma.ttias.be/php-session-locking-prevent-sessions-blocking-in-requests/). 
Also, [this](https://www.php.net/manual/en/features.session.security.management.php) 
is rather scary material to read. Please read it if your application uses 
sessions! If you prefer to go "the PHP way", please read 
[this](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions).

# How

## Cookies

Create a cookie `foo` with the value `bar`:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie();
    $myCookie->set('foo', 'bar');

In order to modify cookie options, the `CookieOptions` class can be used:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie(
        fkooman\SeCookie\CookieOptions::init()->setSameSite('Strict');
    );
    $myCookie->set('foo', 'bar');

The methods `setSecure(bool)`, `setPath(string)`, `setMaxAge(int)` and 
`setSameSite(string|null)` are available for `CookieOptions`.

## Sessions

Start a new session, store a key `foo` with value `bar`:

    <?php

    $mySession = new fkooman\SeCookie\Session();
    $mySession->start();

    // ... 

    $mySession->set('foo', 'bar');

    // ...

    echo $mySession->get('foo');

    // ...

    $mySession->regenerate();

    // ...

    $mySession->destroy();

    // ...

If you want to modify the session cookie options, you can provide a `Cookie` 
object to the `Session` constructor:

    <?php

    $mySession = new fkooman\SeCookie\Session(
        fkooman\SeCookie\SessionOptions::init()->setName('APP_SESSION'),
        new fkooman\SeCookie\Cookie(
            fkooman\SeCookie\CookieOptions::init()->setSameSite('Strict')
        )
    );
    $mySession->start();

    // ...

# Testing

[PHPUnit](https://phpunit.de/) tests are included. You can easily run them:

    $ vendor/bin/phpunit

## Additional Tests

One thing we cannot properly test from PHPUnit is session locking and racing 
conditions or deadlocks as they would require multiple "parallel" requests.

For that purpose we start multiple PHP web servers and run `curl` against it. 
Check the `additional_tests` directory. Run `run_tests.sh` from there. If all 
goes well the test prints `OK` at the end.

# License

[MIT](LICENSE).

# Contact

You can contact me with any questions or issues regarding this project. Drop
me a line at [fkooman@tuxed.net](mailto:fkooman@tuxed.net).

If you want to (responsibly) disclose a security issue you can also use the
PGP key with key ID `9C5EDD645A571EB2` and fingerprint
`6237 BAF1 418A 907D AA98  EAA7 9C5E DD64 5A57 1EB2`.
