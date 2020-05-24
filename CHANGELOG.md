# Changelog

## 1.4.0 - 2020-04-27

### Added
- Added support for dynamically setting reCAPTCHA language based on current locale ([#19])

### Changed
- Updated reCAPTCHA 'Language' setting to 'Fallback Language'
- Updated polyfill rules when generating front-end resources
- Updated javascript resource loading order ([#21])
- Updated Checkbox reCAPTCHA javascript and removed `beforeSproutFormsSubmit` logic

### Fixed
- Fixed Checkbox reCAPTCHA throws an "Uncaught in Promise" error after checked ([#22])
- Fixed HTML5 invalid message when two forms exist on the same page

[#19]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/19
[#21]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/21
[#22]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/22

## 1.3.0 - 2020-04-27

> {note} If you are using config overrides in `config/sprout-forms-google-recpatcha.php` will need to migrate those settings to Sprout Forms `config/sprout-forms.php`. See the `captchaSettings` setting in [the docs](https://sprout.barrelstrengthdesign.com/docs/forms/plugin-settings.html) and an example config (`src/config.php`) in the plugins vendor folder.

### Changed
- Updated captcha settings to use class name
- Updated `barrelstrength/sprout-base-forms` requirement to v3.10.0

### Fixed
- Fixed error where settings assumed a file-based config existed ([#16], [#17], [#18-PR][#18-pull])
- Fixed error message syntax

### Removed
- Removed support for `sprout-forms-google-recpatcha.php` config in favor of the Captcha settings array in `sprout-forms.php` config

[#16]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/16
[#17]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/17
[#18-pull]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/pull/18

## 1.2.0 - 2020-04-09

### Added 
- Added support for config overrides via `config/sprout-forms-google-recaptcha.php`. See `src/config.php` for examples.

### Updated
- Added polyfill for front-end javascript resources to support additional browsers
- Updated Invisible reCAPTCHA to use `onSproutFormsSubmit` Event
- Updated Checkbox reCAPTCHA to use `beforeSproutFormsSubmit` Event
- Updated javascript to target submit button element directly, instead of wrapper element ([#12])
- Updated `barrelstrength/sprout-base-forms` requirement to v3.9.0 ([#15])

### Fixed
- Fixed issue where `onloadCallback` inline script could load after the script it depends on ([#14]) 
- Fixed bug parsing `.env` variables ([#11])

### Removed
- Removed `data-google-recaptcha-form-id` in favor of finding form element directly ([#13])

[#11]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/11
[#12]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/12
[#13]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/13
[#14]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/issues/14
[#15]: https://github.com/barrelstrength/craft-sprout-forms-google-recaptcha/pull/15

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
- Updated `barrelstrength/sprout-base-forms` requirement to v3.6.1

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