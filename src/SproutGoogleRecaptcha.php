<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutgooglerecaptcha;

use barrelstrength\sproutgooglerecaptcha\services\Recaptcha as RecaptchaService;
use barrelstrength\sproutgooglerecaptcha\variables\SproutGoogleRecaptchaVariable;
use barrelstrength\sproutgooglerecaptcha\models\Settings;
use barrelstrength\sproutforms\services\Entries;
use barrelstrength\sproutforms\elements\Entry as EntryElement;

use Craft;
use craft\base\Plugin;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

/**
 * Class SproutGoogleRecaptcha
 *
 * @author    Barrel Strength
 * @package   SproutGoogleRecaptcha
 * @since     1.0.0
 *
 * @property  RecaptchaService $recaptcha
 */
class SproutGoogleRecaptcha extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var SproutGoogleRecaptcha
     */
    public static $app;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$app = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('sproutGoogleRecaptcha', SproutGoogleRecaptchaVariable::class);
            }
        );

        Event::on(Entries::class, EntryElement::EVENT_BEFORE_SAVE, function(OnBeforeSaveEntryEvent $event) {
            $response = SproutGoogleRecaptcha::$app->recaptcha->verifySubmission();
            if (!$response->success){
                $event->entry->addError('googleRecaptcha', 'ups!');
            }
        });

        // Support for displayForm() GoogleRecaptcha output via Hook (if enabled)
        Craft::$app->view->hook('sproutForms.modifyForm', function(&$context) {
            $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();

            if ($sproutFormsSettings->enableCaptchas && $sproutFormsSettings->enableGoogleRecaptcha){
                $googleRecaptchaFile = SproutGoogleRecaptcha::$app->recaptcha->getScript();
                Craft::$app->view->registerJsFile($googleRecaptchaFile);
                return SproutGoogleRecaptcha::$app->recaptcha->getHtml();
            }

            return '';
        });

        Craft::info(
            Craft::t(
                'sprout-google-recaptcha',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'sprout-google-recaptcha/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
