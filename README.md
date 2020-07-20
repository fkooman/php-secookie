# Introduction

This is a simple to use and secure cookie and session library written in PHP. 
It does not use any of PHP's built-in cookie/session functions.

An extensive set of unit tests are included, testing all aspects of the 
library including "integration" tests using the built-in PHP web server.

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

# API

## Cookies

Create a cookie `foo` with the value `bar`:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie();
    if(null === $cookieValue = $myCookie->get('foo')) {
        // no value for cookie "foo" (yet)
        $myCookie->set('foo', 'bar');
    }

## Sessions

Start a new session, store a key `foo` with value `bar`, get the session value
again, remove it and stop the session:

    <?php

    $mySession = new fkooman\SeCookie\Session();
    $mySession->start();
    $mySession->set('foo', 'bar');
    echo $mySession->get('foo');
    $mySession->remove('foo');
    $mySession->stop();

Note that stopping the session is also done automatically at the end of the 
script using the destructor, so only call this if you need to stop the session
and write the session data to storage.

You can also regenerate the session ID, typically you do this *before* storing
important information in the session data, e.g. whether or not the user is 
logged in:

    $mySession->regenerate();

You can also completely destroy the session and remove the session data:

    $mySession->destroy();

The `Session::set` method only takes `string` as a second parameter. You MUST 
convert everything you want to store in your sessions to `string`, e.g. using 
PHP's built-in `serialize()` function.

## Options

### Cookie

In order to modify cookie options, a `CookieOptions` object can be used as the
parameter to the `Cookie` constructor, e.g.:

    <?php

    $myCookie = new fkooman\SeCookie\Cookie(
        fkooman\SeCookie\CookieOptions::init()->withSameSiteStrict()
    );

You can use the following methods on `CookieOptions`:

- `withPath(string)` - restrict the cookie to the provided path. The default
  restricts the cookie to the URL path that issues the cookie;
- `withMaxAge(int)` - specify the maximum lifetime of the cookie in seconds;
- `withSameSiteNone()` - only use this if you need to allow cross domain POST 
  responses, e.g. when implementing a SAML SP
- `withSameSiteLax()` - only send the cookie for cross domain "top level 
  navigation", not for methods that can change state on the server, e.g. `POST` 
  requests;
- `withSameSiteStrict()` - do not send any cookie for any cross domain request
- `withoutSecure()` - omits the `Secure` flag from the cookie options, this 
  is *ONLY* meant for development!

**NOTE**: `CookieOptions` is immutable. This means that when you call `withX()` 
or `withoutX()` you get a copy of the current `CookieOptions` with the new 
value set. It will NOT modify the existing object!

### Session

In order to modify session options, a `SessionOptions` object can be used as 
the first parameter to the `Session` constructor. If you also want to modify 
the `CookieOptions`, specify a `CookieOptions` object as the second parameter,
e.g.:

    <?php

    $mySession = new fkooman\SeCookie\Session(
        fkooman\SeCookie\SessionOptions::init()->withName('MYSID'),
        fkooman\SeCookie\CookieOptions::init()->withSameSiteStrict()
    );

You can use the following methods on `SessionOptions`:

- `withName(string)` - specify the session name. The default is `SID`;
- `withExpiresIn(DateInterval)` - specify the time a session is valid, on the
  server,. The default is `new DateInterval('PT30M')`, which is 30 minutes;
- `withoutGc()` - disable session garbage collection every 100th request.

**NOTE**: `SessionOptions` is immutable. This means that when you call `withX()` 
or `withoutX()` you get a copy of the current `SessionOptions` with the new 
value set. It will NOT modify the existing object!

# Security

This library uses `bin2hex` to convert a binary random string to "hex". It will
use the `sodium_bin2hex` function if available. It is highly recommended you
install the `php-sodium` extension. It is a core extension since PHP 7.2.

# Testing

[PHPUnit](https://phpunit.de/) tests are included. You can easily run them:

    $ vendor/bin/phpunit

## Additional Tests

One thing we cannot properly test from PHPUnit is session locking and racing 
conditions or deadlocks as they would require multiple "parallel" requests.

For that purpose we start multiple PHP web servers and run `curl` against it. 
Check the `additional_tests` directory. Run `run_tests.sh` from there. If all 
goes well the test prints `OK` at the end.

# Required Reading

- [Session Locking](https://ma.ttias.be/php-session-locking-prevent-sessions-blocking-in-requests/)
- [PHP Session Security Management](https://www.php.net/manual/en/features.session.security.management.php) 
- [Safe and Secure PHP sessions](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions)
- [Cookies: HTTP State Management Mechanism](https://tools.ietf.org/html/draft-ietf-httpbis-rfc6265bis-04)

# License

[MIT](LICENSE).

# Contact

You can contact me with any questions or issues regarding this project. Drop
me a line at [fkooman@tuxed.net](mailto:fkooman@tuxed.net).

If you want to (responsibly) disclose a security issue you can also use the
PGP key with key ID `9C5EDD645A571EB2` and fingerprint
`6237 BAF1 418A 907D AA98  EAA7 9C5E DD64 5A57 1EB2`.
