# Changes

## 2.0.0 (2017-09-08)
- ran [Psalm](https://getpsalm.org/) on the code and fixed issues;
- [**API change**] no longer implement `Session::set()`, `Session::get()`, 
  `Session::has()` and `Session::delete()` but only methods to manage the 
  session. API consumers have to use `$_SESSION` directly now;
- [**API change**] `Session` no longer extends `Cookie`, but uses it. You can
  override the default `Cookie` instance by providing it as the second 
  parameter in the `Session` constructor. It is NOT possible any longer to 
  provide `Cookie` settings in the constructor of `Session` (see 
  [README.md](README.md))

## 1.0.2 (2017-08-08)
- fix possible duplicate `Max-Age` when deleting cookie

## 1.0.1 (2017-08-07)
- `Cookie::delete` will set `Max-Age=0` telling the browser to delete the 
  cookie (#1)

## 1.0.0 (2017-06-30)
- initial release
