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

# API

## Cookies

Create a cookie `foo` with the value `bar`:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie();

    if(null === $cookieValue = $myCookie->get('foo')) {
        // no value for cookie "foo" (yet)
        $myCookie->set('foo', 'bar');
    }

In order to modify cookie options, the `CookieOptions` class can be used:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie(
        fkooman\SeCookie\CookieOptions::init()->withSameSiteStrict()
    );
    $myCookie->set('foo', 'bar');

You can use the following methods on `CookieOptions`:

- `withPath(string)`
- `withMaxAge(int)`
- `withSameSiteNone()` - only use this if you need to allow third-party POST 
  responses, e.g. when implementing a SAML SP
- `withSameSiteLax()`
- `withSameSiteStrict()`
- `withoutSecure()` - omits the `Secure` flag from the cookie options, this 
  is *ONLY* meant for development!

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

    $mySession->remove('foo');
    
    // ...

    $mySession->regenerate();

    // ...

    $mySession->destroy();

    // ...

The `Session::set` only takes `string` as a second parameter. You MUST convert 
everything you want to store in your sessions to `string`, e.g. using PHP's 
built-in `serialize()` function.

If you want to modify the session cookie options, you can also provide a 
`CookieOptions` object to the `Session` constructor:

    <?php

    $mySession = new fkooman\SeCookie\Session(
        // default SessionOptions
        fkooman\SeCookie\SessionOptions::init()
            ->withName('SID')
            ->withExpiresIn(new DateInterval('PT30M')),
        fkooman\SeCookie\CookieOptions::init()->withSameSiteStrict()
    );

    $mySession->start();

    // ...

By default, session "garbage collection" is enabled, and run every 100th 
request. It will delete session cookies that expired according to the 
`SessionOptions::setExpiresIn()` value. To disable it you can use 
`SessionOptions::withoutGc()`

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
