<?php

/**
 * Sprout Forms Google reCAPTCHA config overrides for Sprout Forms
 *
 * This file exists only as an example for the Sprout Forms Google reCAPTCHA captcha settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy the contents to the 'craft/config/sprout-forms.php'
 * config file and make your changes there to override default settings.
 *
 * See the Sprout Forms example config `sprout-forms/src/config.php` for general captcha options.
 */

return [

    // ...

    'captchaSettings' => [

        // ...

        'sproutformsgooglerecaptcha-googlerecaptcha' => [

            // Enable the Captcha for use.
            'enabled' => true,

            // The type of reCAPTCHA to use when validating a form
            // Options: v2_checkbox, v2_invisible
            'recaptchaType' => 'v2_checkbox',

            // The siteKey from your reCAPTCHA settings: https://www.google.com/recaptcha/admin
            'siteKey' => '',

            // The secretKey from your reCAPTCHA settings: https://www.google.com/recaptcha/admin
            'secretKey' => '',

            // The language reCAPTCHA will use;use;use;use;. Default: 'en'.
            // For supported languages, see: https://developers.google.com/recaptcha/docs/language
            'language' => 'en',

            // The theme that reCAPTCHA will use
            // Options: light, dark
            'theme' => 'light',

            // The size the reCAPTCHA will use
            // Options: normal, compact
            // This setting only applies to the v2_checkbox captcha
            // See: https://developers.google.com/recaptcha/docs/display#render_param
            'size' => 'normal',

            // The position the reCAPTCHA will use
            // Options: bottomright, bottomleft, inline-badge, inline-text
            // This setting only applies to the v2_invisible captcha
            'badge' => 'bottomright',

            // Enable this setting if you wish to manage CSS manually
            'disableCss' => false
        ]

        // ...
    ]

    // ...
];
