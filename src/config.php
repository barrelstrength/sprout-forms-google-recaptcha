<?php

/**
 * Sprout Forms Google reCAPTCHA config.php
 *
 * This file exists only as a template for the Sprout Forms Google reCAPTCHA settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'sprout-forms-google-recaptcha.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    '*' => [
        // The type of reCAPTCHA to use when validating a form. Default: 'v2_checkbox'
        //'recaptchaType' => 'v2_checkbox|v2_invisible'

        // The siteKey from your reCAPTCHA settings: https://www.google.com/recaptcha/admin
        //'siteKey' => '',

        // The secretKey from your reCAPTCHA settings: https://www.google.com/recaptcha/admin
        //'secretKey' => '',

        // The language reCAPTCHA will use. Default: 'en'.
        // For supported languages, see: https://developers.google.com/recaptcha/docs/language
        //'language' => 'en',

        // The theme that reCAPTCHA will use. Default: 'light'.
        //'theme' => 'light|dark',

        // The size the reCAPTCHA will use. Default : normal
        // This setting only applies to the v2_checkbox captcha
        // See: https://developers.google.com/recaptcha/docs/display#render_param
        //'size' => 'normal|compact',

        // The position the reCAPTCHA will use. Default : bottomright
        // This setting only applies to the v2_invisible captcha
        //'badge' => 'bottomright|bottomleft|inline-badge|inline-text'

        // Enable this setting if you wish to manage CSS manually. Default: false
        //'disableCss' => true|false,
    ]
];
