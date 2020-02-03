# ChangeLog

## 4.0.0 (2020-02-03)
- when `CookieOptions::setSameSite('None')` is used, the same cookie is also 
  sent without `SameSite` attribute for browsers that interpret `None` as 
  `Strict`, most notably iOS 12 devices
- no longer have the option to disable setting `SameSite`, it has to be either
  `None`, `Lax` or `Strict`
- make `CookieOptions` and `SessionOptions` immutable and rename "setters" to
  "withers"
- introduce `CookieOptions::withSameSiteNone()`, 
  `CookieOptions::withSameSiteLax()` and `CookieOptions::withSameSiteStrict()`
  instead of string parameter for `CookieOptions::setSameSite()`
- have `CookieOptions::withInsecure()` instead of bool parameter to 
  `CookieOptions::setSecure()`

## 3.0.1 (2020-01-29)
- use `Hex::encode()` from `paragonie/constant_time_encoding` for constant time
  hex encoding of session cookies

## 3.0.0 (2020-01-27)
- completely rewrite the library to use custom session handling without relying
  on PHP's built-in session and cookie support

## 2.0.1 (2018-06-02)
- add support for newer versions of PHPUnit
- update (C)
- update source formatting

## 2.0.0 (2017-09-10)
- change `Session` constructor to take `Cookie` instance instead of 
  `HeaderInterface` to have a much cleaner implementation and separation of 
  `Cookie` and `Session`. See [README.md](README.md) for the updated
  documentation
- Fix issues found by [Psalm](https://getpsalm.org/)

## 1.0.2 (2017-08-08)
- fix possible duplicate `Max-Age` when deleting cookie

## 1.0.1 (2017-08-07)
- `Cookie::delete` will set `Max-Age=0` telling the browser to delete the 
  cookie (#1)

## 1.0.0 (2017-06-30)
- initial release
