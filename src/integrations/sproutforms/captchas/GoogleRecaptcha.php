<?php
/**
 * Sprout Google reCAPTCHA plugin for Craft CMS 3.x
 *
 * Google reCAPTCHA solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutformsgooglerecaptcha\integrations\sproutforms\captchas;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnBeforeValidateEntryEvent;
use Craft;
use craft\web\View;
use ReflectionException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidValueException;

/**
 * Google reCAPTCHA v2 class
 *
 * @property array  $languageOptions
 * @property string $script
 */
class GoogleRecaptcha extends Captcha
{
    /**
     * Supported themes
     *
     * @var array
     * @see https://developers.google.com/recaptcha/docs/display#config
     */
    protected static $themes = ['light', 'dark'];

    /**
     * @var string
     */
    public $siteKey;

    /**
     * @var string
     */
    public $secretKey;

    /**
     * @var bool
     */
    public $disableCss = false;

    /**
     * Captcha theme. Default : light
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/display#config
     */
    protected $theme = 'light';

    /**
     * Captcha language. Default : auto-detect
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/language
     */
    protected $language = 'en';

    /**
     * Captcha size. Default : normal
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/display#render_param
     */
    protected $size = 'normal';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Google reCAPTCHA';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms-google-recaptcha', 'reCAPTCHA protects you against spam and other types of automated abuse.');
    }

    /**
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getCaptchaSettingsHtml(): string
    {
        // We just use this here to indicate in the UI if the setting is overridden
        $config = Craft::$app->getConfig()->getConfigFromFile('sprout-forms-google-recaptcha');
        $settings = $this->getSettings();

        $languageOptions = $this->getLanguageOptions();

        return Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/settings', [
            'captcha' => $this,
            'config' => $config,
            'settings' => $settings,
            'languageOptions' => $languageOptions
        ]);
    }

    /**
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getCaptchaHtml(): string
    {
        $oldTemplateMode = Craft::$app->getView()->getTemplateMode();
        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);

        $settings = $this->getSettings();
        $settings['siteKey'] = Craft::parseEnv($settings['siteKey']);

        if (!$settings['siteKey']) {
            throw new InvalidValueException('reCAPTCHA SiteKey setting must be provided when reCAPTCHA is enabled');
        }

        $languageId = $this->getMatchedLanguageId() ?? $settings['language'];

        $html = Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/'.$settings['recaptchaType'], [
            'form' => $this->form,
            'settings' => $settings,
            'languageId' => $languageId
        ]);

        Craft::$app->getView()->setTemplateMode($oldTemplateMode);

        return $html;
    }

    public function getMatchedLanguageId() {

        $currentLanguageId = Craft::$app->locale->getLanguageID();

        // 700+ languages supported
        $allCraftLocales = Craft::$app->getI18n()->getAllLocales();
        $allCraftLanguageIds = array_column($allCraftLocales, 'id');

        // ~70 languages supported
        $allRecaptchaLanguages = $this->getLanguageOptions();
        $allRecaptchaLanguageIds = array_column($allRecaptchaLanguages, 'value');

        // 65 matched language IDs
        $matchedLanguageIds = array_intersect($allRecaptchaLanguageIds, $allCraftLanguageIds);

        // If our current request Language ID matches a reCAPTCHA language ID, use it
        if (in_array($currentLanguageId, $matchedLanguageIds, true)) {
            return $currentLanguageId;
        }

        // If our current language ID has a more generic match, use it
        if (strpos($currentLanguageId, '-') !== false) {
            $parts = explode('-', $currentLanguageId);
            $baseLanguageId = $parts['0'] ?? null;

            if (in_array($baseLanguageId, $matchedLanguageIds, true)) {
                return $baseLanguageId;
            }
        }

        return null;
    }

    /**
     * @param OnBeforeValidateEntryEvent $event
     *
     * @return bool
     */
    public function verifySubmission(OnBeforeValidateEntryEvent $event): bool
    {
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return true;
        }

        $settings = $this->getSettings();
        $this->secretKey = Craft::parseEnv($settings['secretKey']);

        $gRecaptchaResponse = $_POST['g-recaptcha-response'] ?? null;

        if (empty($gRecaptchaResponse)) {
            $errorMessage = Craft::t('sprout-forms-google-recaptcha', "Google reCAPTCHA can't be blank.");
            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);

            return false;
        }

        if ($this->secretKey === null) {
            $errorMessage = Craft::t('sprout-forms-google-recaptcha', 'Invalid secret key.');
            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);

            return false;
        }

        $siteVerifyResponse = $this->getResponse($gRecaptchaResponse);

        if (isset($siteVerifyResponse['error-codes'])) {
            foreach ($siteVerifyResponse['error-codes'] as $key => $errorCode) {
                $this->addError(self::CAPTCHA_ERRORS_KEY, $errorCode);
            }
        }

        return $siteVerifyResponse['success'] ?? false;
    }

    /**
     * Server side reCAPTCHA validation
     *
     * @param $gRecaptcha
     *
     * @return array|mixed
     */
    public function getResponse($gRecaptcha)
    {
        $responseObject = [];

        $params = [
            'secret' => Craft::parseEnv($this->secretKey),
            'response' => $gRecaptcha,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        try {
            $client = Craft::createGuzzleClient([
                'base_uri' => 'https://www.google.com/recaptcha/api/siteverify',
                'timeout' => 120,
                'connect_timeout' => 120
            ]);

            $response = $client->request('POST', 'siteverify', [
                'query' => $params
            ]);

            $responseObject = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            // Mock a response object with the error message
            $responseObject['success'] = false;
            $responseObject['error-codes'] = $e->getMessage();
            Craft::error($e->getMessage(), __METHOD__);
        }

        return $responseObject;
    }

    /**
     * List of language options for reCAPTCHA badge
     *
     * Manually update list
     * https://developers.google.com/recaptcha/docs/language
     */
    public function getLanguageOptions(): array
    {
        $languages = [
            'Arabic' => 'ar',
            'Afrikaans' => 'af',
            'Amharic' => 'am',
            'Armenian' => 'hy',
            'Azerbaijani' => 'az',
            'Basque' => 'eu',
            'Bengali' => 'bn',
            'Bulgarian' => 'bg',
            'Catalan' => 'ca',
            'Chinese (Hong Kong)' => 'zh-HK',
            'Chinese (Simplified)' => 'zh-CN',
            'Chinese (Traditional)' => 'zh-TW',
            'Croatian' => 'hr',
            'Czech' => 'cs',
            'Danish' => 'da',
            'Dutch' => 'nl',
            'English (UK)' => 'en-GB',
            'English (US)' => 'en',
            'Estonian' => 'et',
            'Filipino' => 'fil',
            'Finnish' => 'fi',
            'French' => 'fr',
            'French (Canadian)' => 'fr-CA',
            'Galician' => 'gl',
            'Georgian' => 'ka',
            'German' => 'de',
            'German (Austria)' => 'de-AT',
            'German (Switzerland)' => 'de-CH',
            'Greek' => 'el',
            'Gujarati' => 'gu',
            'Hebrew' => 'iw',
            'Hindi' => 'hi',
            'Hungarian' => 'hu',
            'Icelandic' => 'is',
            'Indonesian' => 'id',
            'Italian' => 'it',
            'Japanese' => 'ja',
            'Kannada' => 'kn',
            'Korean' => 'ko',
            'Laothian' => 'lo',
            'Latvian' => 'lv',
            'Lithuanian' => 'lt',
            'Malay' => 'ms',
            'Malayalam' => 'ml',
            'Marathi' => 'mr',
            'Mongolian' => 'mn',
            'Norwegian' => 'no',
            'Persian' => 'fa',
            'Polish' => 'pl',
            'Portuguese' => 'pt',
            'Portuguese (Brazil)' => 'pt-BR',
            'Portuguese (Portugal)' => 'pt-PT',
            'Romanian' => 'ro',
            'Russian' => 'ru',
            'Serbian' => 'sr',
            'Sinhalese' => 'si',
            'Slovak' => 'sk',
            'Slovenian' => 'sl',
            'Spanish' => 'es',
            'Spanish (Latin America)' => 'es-419',
            'Swahili' => 'sw',
            'Swedish' => 'sv',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Thai' => 'th',
            'Turkish' => 'tr',
            'Ukrainian' => 'uk',
            'Urdu' => 'ur',
            'Vietnamese' => 'vi',
            'Zulu' => 'zu'
        ];

        $languageOptions = [];
        foreach ($languages as $languageName => $languageCode) {
            $languageOptions[] = [
                'label' => $languageName,
                'value' => $languageCode
            ];
        }

        return $languageOptions;
    }
}