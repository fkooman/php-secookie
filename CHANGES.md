# ChangeLog

## 2.0.0 (TBD)
- ran [Psalm](https://getpsalm.org/) on the code base and fix issues
- no longer expose `Session::set()`, `Session::get()`, `Session::has()` and 
  `Session::delete()` but only methods to manage the session. Depending 
  applications MUST use `$_SESSION` directly

## 1.0.2 (2017-08-08)
- fix possible duplicate `Max-Age` when deleting cookie

## 1.0.1 (2017-08-07)
- `Cookie::delete` will set `Max-Age=0` telling the browser to delete the 
  cookie (#1)

## 1.0.0 (2017-06-30)
- initial release
