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
use barrelstrength\sproutformsgooglerecaptcha\integrations\sproutforms\GoogleRecaptcha;
use barrelstrength\sproutformsgooglerecaptcha\services\App;
use barrelstrength\sproutformsgooglerecaptcha\services\Recaptcha as RecaptchaService;
use barrelstrength\sproutforms\services\Entries;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

use Craft;
use craft\base\Plugin;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;

use yii\base\Event;

/**
 * Class SproutFormsGoogleRecaptcha
 *
 * @author    Barrel Strength
 * @package   SproutGoogleRecaptcha
 * @since     1.0.0
 *
 * @property  RecaptchaService $recaptcha
 */
class SproutFormsGoogleRecaptcha extends Plugin
{
    /**
     * @var SproutFormsGoogleRecaptcha
     */
    public static $app;

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';


    public $hasCpSettings = true;

    public $hasCpSection = false;

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
