# Changelog

## 1.1.0 - 2020-01-17

### Added
- Added support for Invisible reCAPTCHA v2
- Added Invisible reCAPTCHA settings for Language, Theme, and Badge Type including a 'Inline Text' option to display a line of text terms instead of a badge
- Added Checkbox reCAPTCHA settings for Language, Theme, and Size ([#1], [#3], [#332][332-sproutforms])
- Added support for using reCAPTCHA when displaying multiple forms on a page 

### Changed
- Updated setting 'Display default required CSS' to 'Disable CSS'

[#1]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/1
[#3]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/3
[332-sproutforms]: https://github.com/barrelstrength/craft-sprout-forms/issues/332

## 1.0.6 - 2019-11-19

### Added
- Added setting to resources for HTML Required behavior
- Added support for environment variables in settings ([#9][#9pull])

### Updated
- Updated barrelstrength/sprout-base-forms requirement to v3.6.1

### Fixed
- Fixed bug where CP settings were enabled but didn't exist ([#6][#6pull])

[#6pull]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/pull/6
[#9pull]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/pull/9

## 1.0.5 - 2019-04-30

### Changed
- Updated icon

## 1.0.4 - 2019-04-23

### Changed
- Removed unnecessary CSS ([#272])

[#272]: https://github.com/barrelstrength/craft-sprout-forms/issues/272

## 1.0.3 - 2019-03-15

### Changed
- Updated `EVENT_REGISTER_CAPTCHAS` Event to `RegisterComponentTypesEvent`

### Fixed
- Added support for Return Types on Captcha class ([#5])

[#5]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/5

## 1.0.2 - 2018-11-26

### Added
- Added async and defer to the Google Recaptcha api.js

### Updated
- Updated JS to be rendered at the end of the document

## 1.0.1 - 2018-10-22

### Added
- Added new packagist hook

## 1.0.0 - 2018-10-17

### Added
- Initial release