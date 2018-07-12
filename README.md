# Introduction

Secure cookie and session library for PHP.

# Why

* PHP >= 5.4 supported (CentOS 7 default PHP is supported);
* Replace complicated `setcookie()` which is not secure by default (`HttpOnly`, 
  `Secure`, `SameSite` are not the default);
* [delight-im/cookie](https://github.com/delight-im/PHP-Cookie) and 
  [paragonie/cookie](https://github.com/paragonie/PHP-Cookie), in addition to 
  requiring PHP >= 5.6, parse cookies, which is best avoided for security
  reasons and code simplicity;
* Allow binding PHP sessions to "Domain" and "Path" (see below);
* Easy to use PHP session API;
* Uses a "Canary" to regularly refresh session ID;
* Expires the PHP session on the server;
* Implements `SameSite` attribute value;
* Unit tests with PHPUnit;

Many of the ideas came from the resources below,
[delight-im/cookie](https://github.com/delight-im/PHP-Cookie) and 
[Airship](https://github.com/paragonie/airship)'s implementation of (secure) 
sessions.

We do _NOT_ support the (deprecated) `Expires` attribute value as `Max-Age` is 
supported by all current, i.e. >= IE 11, browsers on desktop and mobile.

# Cookies

Setting a cookie, i.e. add the `Set-Cookie` header is straightforward:

```php
    $cookie = new Cookie();
    $cookie->set('foo', 'bar');
```

This will set the cookie using the `Secure`, `HttpOnly` and `SameSite=Strict` 
values. 

The following configuration options are supported:

* `Secure`: `bool` (default: `true`)
* `HttpOnly`: `bool` (default: `true`)
* `Path`: `string`|`null` (default: `null`)
* `Domain`: `string`|`null` (default: `null`)
* `Max-Age`: `int`|`null` (default: `null`)
* `SameSite`: `Strict`|`Lax`|`null` (default: `Strict`)

For example, to limit a browser to only send the cookie to the `/foo/` path and
use the `Lax` option for `SameSite`:

```php
    $cookie = new Cookie(
        [
            'Path' => '/foo/',
            'SameSite' => 'Lax',
        ]
    );
    $cookie->set('foo', 'bar');
```

You can delete a cookie, which sets the value to the empty string and 
`MaxAge=0`, this results in the browser deleting the cookie.

```php
    $cookie->delete('foo');
```

# Sessions

Sessions will use the same defaults as Cookies, so you'll get secure sessions
out of the box. 

```php
    $session = new Session();
    $session->set('foo', 'bar');
```

Note that the values here are stored _inside_ the session, and not sent to the
browser!

The following configuration options are supported:

* `DomainBinding`: `string`|`null` (default: `null`), see "Session Binding"
* `PathBinding`: `string`|`null` (default: `null`), see "Session Binding"
* `SessionExpiry`: `string` (default: `PT08H`, 8 hours), see "Session Expiry"
* `CanaryExpiry`: `string` (default: `PT01H`, 1 hour)
* `SessionName`: `string`|`null` (default: `null`)

The format for `SessionExpiry` and `CanaryExpiry` are any string that is 
accepted by PHP's `DateInterval` [class](https://secure.php.net/manual/en/class.dateinterval.php).

You can destroy a session, i.e. empty the `$_SESSION` variable and regenerate 
the session ID by calling the `destroy()` method:

```php
    $session->destroy();
```

You can regenerate the session ID:

```php
    $session->regenerate();
```

It accepts a boolean parameter to delete the session on the server.
 
In addition there are methods for `get()`, `set()`, `has()` and `delete()` as 
well. It is recommended to call `regenerate()` before storing important 
information in the session, for example after user authentication.

## Cookie Settings

If you want to override the cookie settings, you can provide the a `Cookie` 
instance as the second parameter to the `Session` constructor, e.g.:

```php
    $session = new Session(
        [],
        new Cookie(
            [
               'SameSite' => 'Lax'
            ]
        )
    );
```

## Session Binding

Session binding is implemented to avoid using a PHP session meant for one 
"Domain" or "Path" being used at another Domain or Path. This is important if 
you are hosting a "multi-site" application where the site is running at 
multiple domains, but shares the same PHP session storage.

This can be used like this:

```php
    $session = new Session(
        [
            'DomainBinding' => 'www.example.org',
            'PathBinding' => '/foo/',
        ]
    );
```

This does *not* restrict the `Domain` and `Path` options for the Cookie, to 
modify these you'd have to provide your own `Cookie` instance as parameter to 
the `Session` constructor. But _NOT_ specifying them may result in more secure 
cookies as they will be automatically bound to the "Path" and "Domain", see 
_The definitive guide to cookie domains and why a www-prefix makes your website safer_
linked in the resources below.

## Session Expiry

The PHP session typically lives as long as the user's user agent, i.e. browser, 
runs. On many platforms the browser is not closed anymore and remains open 
indefinitely, or as least until the device is restarted, e.g. on mobile.

For some use cases it may be necessary to expire sessions on the server, this 
can be done with the `SessionExpiry` configuration option. The default is 8 
hours.

```php
    $session = new Session(
        [
            'SessionExpiry' => 'PT08H',
        ]
    );
```

To disable session expiry, you can set the `SessionExpiry` to `null`.

# Security

It is **very** important that you update your PHP session settings in 
`php.ini` on your host. See _The Fast Track to Safe and Secure PHP Sessions_, 
linked below in the resources.

```ini
    ;
    ; session
    ;
    ; @see https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions
    ; @see https://secure.php.net/manual/en/session.configuration.php
    ;
    session.save_handler = files
    session.save_path = "/var/lib/php/session"
    ; On Debian
    ;session.save_path = "/var/lib/php/sessions"
    session.use_cookies = 1
    session.use_only_cookies = 1

    ; PHP < 7.1
    session.hash_function = sha256
    session.hash_bits_per_character = 5
    session.entropy_file = /dev/urandom
    session.entropy_length = 32

    ; PHP >= 5.5.2
    ;session.use_strict_mode = 1

    ; PHP >= 7.1
    ;session.sid_length = 52
    ;session.sid_bits_per_character = 5
```

# Contact

You can contact me with any questions or issues regarding this project. Drop
me a line at [fkooman@tuxed.net](mailto:fkooman@tuxed.net).

If you want to (responsibly) disclose a security issue you can also use the
PGP key with key ID `9C5EDD645A571EB2` and fingerprint
`6237 BAF1 418A 907D AA98  EAA7 9C5E DD64 5A57 1EB2`.

# License

[MIT](LICENSE).

# Resources

* [The Fast Track to Safe and Secure PHP Sessions](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions)
* [The definitive guide to cookie domains and why a www-prefix makes your website safer](http://erik.io/blog/2014/03/04/definitive-guide-to-cookie-domains/)
* [Same-Site Cookies](https://tools.ietf.org/html/draft-ietf-httpbis-cookie-same-site-00)

