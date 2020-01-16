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
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\events\OnBeforeValidateEntryEvent;
use Craft;
use craft\web\View;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig_Error_Loader;
use yii\base\Exception;

/**
 * Google reCAPTCHA v2 class
 *
 * @property array  $languageOptions
 * @property string $script
 */
class GoogleRecaptcha extends Captcha
{
    /**
     * @var string
     */
    private $siteKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var bool
     */
    private $disableCss;

    /**
     * Remote IP address
     *
     * @var string
     */
    private $remoteIp;

    /**
     * Supported themes
     *
     * @var array
     * @see https://developers.google.com/recaptcha/docs/display#config
     */
    protected static $themes = ['light', 'dark'];

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
     * Initialize site and secret keys
     *
     * @throws ReflectionException
     */
    public function init()
    {
        $settings = $this->getSettings();
        $this->siteKey = Craft::parseEnv($settings['siteKey']) ?? null;
        $this->secretKey = Craft::parseEnv($settings['secretKey']) ?? null;
        $this->disableCss = $settings['disableCss'] ?? false;
        $this->remoteIp = $_SERVER['REMOTE_ADDR'];

        parent::init();
    }

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
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getCaptchaSettingsHtml(): string
    {
        $settings = $this->getSettings();

        $languageOptions = $this->getLanguageOptions();

        return Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/settings', [
            'settings' => $settings,
            'languageOptions' => $languageOptions,
            'captchaId' => $this->getCaptchaId()
        ]);
    }

    /**
     * @param Form $form
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getCaptchaHtml(Form $form): string
    {
        $oldTemplateMode = Craft::$app->getView()->getTemplateMode();
        Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);

        $settings = $this->getSettings();

        $googleTermsText = Craft::t('site', "This site is protected by reCAPTCHA and the Google <a href='{privacyUrl}'>Privacy Policy</a> and <a href='{termsUrl}'>Terms of Service</a> apply.", [
            'privacyUrl' => 'https://policies.google.com/privacy',
            'termsUrl' => 'https://policies.google.com/terms'
        ]);

        $googleTermsText = '<p>'.$googleTermsText.'</p>';

        $html = Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/'.$settings['recaptchaType'], [
            'form' => $form,
            'settings' => $settings,
            'googleTermsText' => $googleTermsText
        ]);

        Craft::$app->getView()->setTemplateMode($oldTemplateMode);

        return $html;
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
            'secret' => $this->secretKey,
            'response' => $gRecaptcha,
            'remoteip' => $this->remoteIp,
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
            Craft::error('sprout-forms-google-recaptcha', $e->getMessage());
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