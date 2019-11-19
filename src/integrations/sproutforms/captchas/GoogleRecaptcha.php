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
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Throwable;
use Twig_Error_Loader;
use yii\base\Exception;

/**
 * Google reCAPTCHA v2 class
 *
 */
class GoogleRecaptcha extends Captcha
{
    /**
     * URL to use to verify reCAPTCHA
     *
     * @var string
     */
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    const API_URL = 'https://www.google.com/recaptcha/api.js';

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
    private $addRequiredHtml;

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
    protected $theme;

    /**
     * Supported types
     *
     * @var array
     * @see https://developers.google.com/recaptcha/docs/display#config
     */
    protected static $types = ['image', 'audio'];

    /**
     * Captcha type. Default : image
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/display#config
     */
    protected $type;

    /**
     * Captcha language. Default : auto-detect
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/language
     */
    protected $language;

    /**
     * CURL timeout (in seconds) to verify response
     *
     * @var int
     */
    private $verifyTimeout = 1;

    /**
     * Captcha size. Default : normal
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/display#render_param
     */
    protected $size;

    /**
     * Initialize site and secret keys
     *
     * @throws ReflectionException
     */
    public function __construct()
    {
        $settings = $this->getSettings();
        $this->siteKey = Craft::parseEnv($settings['googleRecaptchaSiteKey']) ?? null;
        $this->secretKey = Craft::parseEnv($settings['googleRecaptchaSecretKey']) ?? null;
        $this->addRequiredHtml = $settings['addRequiredHtml'] ?? true;
        $this->remoteIp = $_SERVER['REMOTE_ADDR'];
    }

    public function getName(): string
    {
        return 'Google reCAPTCHA';
    }

    public function getDescription(): string
    {
        return Craft::t('sprout-forms-google-recaptcha', 'reCAPTCHA protects you against spam and other types of automated abuse.');
    }

    /**
     * @inheritdoc
     *
     * @throws ReflectionException
     * @throws Twig_Error_Loader
     * @throws Exception
     */
    public function getCaptchaSettingsHtml(): string
    {
        $settings = $this->getSettings();

        $html = Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/settings', [
            'settings' => $settings,
            'captchaId' => $this->getCaptchaId()
        ]);
        return $html;
    }

    public function getCaptchaHtml(): string
    {
        $googleRecaptchaFile = $this->getScript();

        Craft::$app->view->registerJsFile($googleRecaptchaFile, ['defer' => 'defer', 'async' => 'async']);

        Craft::$app->view->registerJs("window.onload = function() {
            var recaptcha = document.querySelector('#g-recaptcha-response');
            if(recaptcha) {
                recaptcha.setAttribute('required', '');
            }
        };", View::POS_END);

        if ($this->addRequiredHtml){
            Craft::$app->view->registerCss('#g-recaptcha-response {
                display: block !important;
                position: absolute;
                margin: -78px 0 0 0 !important;
                width: 302px !important;
                height: 76px !important;
                z-index: -999999;
                opacity: 0;}
            ');
        }

        $html = '';

        if (!empty($this->siteKey)) {
            $data = 'data-sitekey="'.$this->siteKey.'"';

            if ($this->theme !== null) {
                $data .= ' data-theme="'.$this->theme.'"';
            }

            if ($this->type !== null) {
                $data .= ' data-type="'.$this->type.'"';
            }

            if ($this->size !== null) {
                $data .= ' data-size="'.$this->size.'"';
            }

            $html = '<div class="g-recaptcha" '.$data.'></div>';
        }

        return $html;
    }

    /**
     * @param OnBeforeValidateEntryEvent $event
     * @return bool
     * @throws GuzzleException
     */
    public function verifySubmission(OnBeforeValidateEntryEvent $event): bool
    {
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return true;
        }

        if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
            $errorMessage = "Google reCAPTCHA can't be blank";
            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);
            return false;
        }

        $gRecaptcha = $_POST['g-recaptcha-response'] ?? null;

        $googleResponse = $this->getResponse($gRecaptcha);

        if (isset($googleResponse['error-codes'])) {
            foreach ($googleResponse['error-codes'] as $key => $errorCode) {
                $this->addError(self::CAPTCHA_ERRORS_KEY, $errorCode);
            }
        }

        return $googleResponse['success'] ?? false;
    }

    /**
     * Set theme
     *
     * @param string $theme (see https://developers.google.com/recaptcha/docs/display#config)
     *
     */
    public function setTheme($theme = null)
    {
        $this->theme = 'light';

        if (in_array($theme, self::$themes, true)) {
            $this->theme = $theme;
        }
    }

    /**
     * Set type
     *
     * @param string $type (see https://developers.google.com/recaptcha/docs/display#config)
     *
     */
    public function setType($type = null)
    {
        $this->type = 'image';

        if (in_array($type, self::$types, true)) {
            $this->type = $type;
        }
    }

    /**
     * Set language
     *
     * @param string $language (see https://developers.google.com/recaptcha/docs/language)
     *
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Set timeout
     *
     * @param int $timeout
     *
     */
    public function setVerifyTimeout($timeout)
    {
        $this->verifyTimeout = $timeout;
    }

    /**
     * Set size
     *
     * @param string $size (see https://developers.google.com/recaptcha/docs/display#render_param)
     *
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Generate the JS code of the captcha
     *
     * @return string
     */
    public function getScript(): string
    {
        $data = [];
        if ($this->language !== null) {
            $data = ['hl' => $this->language];
        }

        return self::API_URL.'?'.http_build_query($data);
    }

    /**
     * Checks the code given by the captcha
     *
     * @param string $gRecaptcha $_POST['g-recaptcha-response']
     *
     * @return array|mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function getResponse($gRecaptcha)
    {
        $googleResponse = [];

        if (empty($gRecaptcha) || $this->secretKey === null) {
            $response['message'] = "Can't be blank";
            return $response;
        }

        $params = [
            'secret' => $this->secretKey,
            'response' => $gRecaptcha,
            'remoteip' => $this->remoteIp,
        ];

        try {
            $client = Craft::createGuzzleClient([
                'base_uri' => self::VERIFY_URL,
                'timeout' => 120,
                'connect_timeout' => 120
            ]);

            $response = $client->request('POST', 'siteverify', [
                'query' => $params
            ]);

            $googleResponse = json_decode($response->getBody()->getContents(), true);
        } catch (Throwable $e) {
            Craft::error('sprout-forms-google-recaptcha', $e->getMessage());
        }

        return $googleResponse;
    }
}