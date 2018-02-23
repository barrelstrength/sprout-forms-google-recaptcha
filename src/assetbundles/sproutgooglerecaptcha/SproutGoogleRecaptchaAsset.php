<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutgooglerecaptcha\assetbundles\SproutGoogleRecaptcha;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Barrel Strength
 * @package   SproutGoogleRecaptcha
 * @since     1.0.0
 */
class SproutGoogleRecaptchaAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@barrelstrength/sproutgooglerecaptcha/assetbundles/sproutgooglerecaptcha/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/SproutGoogleRecaptcha.js',
        ];

        $this->css = [
            'css/SproutGoogleRecaptcha.css',
        ];

        parent::init();
    }
}
