<?php

namespace barrelstrength\sproutformsgooglerecaptcha\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200117_000000_update_settings extends Migration
{
    /**
     * @return bool|void
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');
        $recaptchaSettings = $currentSettings['captchaSettings']['sproutformsgooglerecaptcha-googlerecaptcha'] ?? [];

        $recaptchaSettings['recaptchaType'] = $recaptchaSettings['googleRecaptchaType'] ?? 'v2_checkbox';
        $recaptchaSettings['siteKey'] = $recaptchaSettings['googleRecaptchaSiteKey'] ?? '';
        $recaptchaSettings['secretKey'] = $recaptchaSettings['googleRecaptchaSecretKey'] ?? '';
        $recaptchaSettings['disableCss'] = $recaptchaSettings['addRequiredHtml'] ?? '';
        $recaptchaSettings['language'] = 'en';
        $recaptchaSettings['theme'] = 'light';
        $recaptchaSettings['size'] = 'normal';
        $recaptchaSettings['badge'] = 'bottomright';

        unset(
            $recaptchaSettings['googleRecaptchaType'],
            $recaptchaSettings['googleRecaptchaSiteKey'],
            $recaptchaSettings['googleRecaptchaSecretKey'],
            $recaptchaSettings['addRequiredHtml']
        );

        $currentSettings['captchaSettings']['sproutformsgooglerecaptcha-googlerecaptcha'] = $recaptchaSettings;

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200117_000000_update_settings cannot be reverted.\n";

        return false;
    }
}
