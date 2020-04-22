<?php

namespace barrelstrength\sproutformsgooglerecaptcha\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 *
 * @property null|int $fakeFieldLayoutId
 */
class m191119_000000_set_default_recaptcha_css extends Migration
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

        $recaptchaSettings['disableCss'] = 0;
        $currentSettings['captchaSettings']['sproutformsgooglerecaptcha-googlerecaptcha'] = $recaptchaSettings;

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191119_000000_set_default_recaptcha_css cannot be reverted.\n";

        return false;
    }
}
