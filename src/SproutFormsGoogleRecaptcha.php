<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutformsgooglerecaptcha;

use barrelstrength\sproutforms\services\Forms;
use barrelstrength\sproutformsgooglerecaptcha\integrations\sproutforms\captchas\GoogleRecaptcha;
use craft\base\Plugin;
use yii\base\Event;

/**
 * Class SproutFormsGoogleRecaptcha
 *
 * @author    Barrel Strength
 * @package   SproutFormsGoogleRecaptcha
 * @since     1.0.0
 *
 * @property  RecaptchaService $recaptcha
 */
class SproutFormsGoogleRecaptcha extends Plugin
{
    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var bool
     */
    public $hasCpSection = false;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Event::on(Forms::class, Forms::EVENT_REGISTER_CAPTCHAS, function(Event $event) {
            $event->types[] = GoogleRecaptcha::class;
        });
    }

}
