# ChangeLog

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
