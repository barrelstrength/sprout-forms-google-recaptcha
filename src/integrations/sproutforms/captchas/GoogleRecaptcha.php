<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutformsgooglerecaptcha\integrations\sproutforms\captchas;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use Craft;

/**
 * Google reCAPTCHA v2 class
 *
 */
class GoogleRecaptcha extends Captcha
{
    /**
     * ReCAPTCHA URL verifying
     *
     * @var string
     */
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    const API_RUL = 'https://www.google.com/recaptcha/api.js';

    /**
     * @var string
     */
    private $siteKey = null;

    /**
     * @var string
     */
    private $secretKey = null;

    /**
     * Remote IP address
     *
     * @var string
     */
    private $remoteIp = null;

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
    protected $theme = null;

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
    protected $type = null;

    /**
     * Captcha language. Default : auto-detect
     *
     * @var string
     * @see https://developers.google.com/recaptcha/docs/language
     */
    protected $language = null;

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
    protected $size = null;

    /**
     * Initialize site and secret keys
     *
     * @return void
     */
    public function __construct()
    {
        $settings = $this->getSettings();
        $this->siteKey = $settings['googleRecaptchaSiteKey'] ?? null;
        $this->secretKey = $settings['googleRecaptchaSecretKey'] ?? null;
        $this->remoteIp = $_SERVER['REMOTE_ADDR'];
    }

    public function getName()
    {
        return 'Google Recaptcha';
    }

    public function getDescription()
    {
        return Craft::t('sprout-forms-google-recaptcha', 'Adds Google reCAPTCHA to Sprout Forms');
    }

    /**
     * @inheritdoc
     *
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getCaptchaSettingsHtml()
    {
        $settings = $this->getSettings();

        $html = Craft::$app->getView()->renderTemplate('sprout-forms-google-recaptcha/_integrations/sproutforms/captchas/GoogleRecaptcha/settings', [
            'settings' => $settings,
            'captchaId' => $this->getCaptchaId()
        ]);
        return $html;
    }

    public function getCaptchaHtml()
    {
        $googleRecaptchaFile = $this->getScript();

        Craft::$app->view->registerJsFile($googleRecaptchaFile, ['defer' => 'defer', 'async' => 'async']);

        Craft::$app->view->registerJs("window.onload = function() {
            var recaptcha = document.querySelector('#g-recaptcha-response');
            if(recaptcha) {
                recaptcha.setAttribute('required', '');
            }
        };");

        Craft::$app->view->registerCss('#g-recaptcha-response {
            display: block !important;
            position: absolute;
            margin: -78px 0 0 0 !important;
            width: 302px !important;
            height: 76px !important;
            z-index: -999999;
            opacity: 0;
           }');

        $html = '';

        if (!empty($this->siteKey)) {
            $data = 'data-sitekey="'.$this->siteKey.'"';

            if (!is_null($this->theme)) {
                $data .= ' data-theme="'.$this->theme.'"';
            }

            if (!is_null($this->type)) {
                $data .= ' data-type="'.$this->type.'"';
            }

            if (!is_null($this->size)) {
                $data .= ' data-size="'.$this->size.'"';
            }

            $html = '<div class="g-recaptcha" '.$data.'></div>';
        }

        return $html;
    }

    /**
     * @inheritdoc
     *
     * @throws \ReflectionException
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return true;
        }

        if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
            $event->isValid = false;
            $event->errors[$this->getCaptchaId()] = "Google recaptcha can't be blank";
            return false;
        }

        $gRecaptcha = $_POST['g-recaptcha-response'] ?? null;

        $googleResponse = $this->getResponse($gRecaptcha);

        if (isset($googleResponse['error-codes'])) {
            foreach ($googleResponse['error-codes'] as $key => $errorCode) {
                $event->errors[$this->getCaptchaId()] = $errorCode;
            }
        }

        $event->isValid = $googleResponse['success'];

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

        if (in_array($theme, self::$themes)) {
            $this->theme = $theme;
        }
    }

    /**
     * Set type
     *
     * @param  string $type (see https://developers.google.com/recaptcha/docs/display#config)
     *
     */
    public function setType($type = null)
    {
        $this->type = 'image';

        if (in_array($type, self::$types)) {
            $this->type = $type;
        }
    }

    /**
     * Set language
     *
     * @param  string $language (see https://developers.google.com/recaptcha/docs/language)
     *
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Set timeout
     *
     * @param  int $timeout
     *
     */
    public function setVerifyTimeout($timeout)
    {
        $this->verifyTimeout = $timeout;
    }

    /**
     * Set size
     *
     * @param  string $size (see https://developers.google.com/recaptcha/docs/display#render_param)
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
    public function getScript()
    {
        $data = [];
        if (!is_null($this->language)) {
            $data = ['hl' => $this->language];
        }

        return self::API_RUL.'?'.http_build_query($data);
    }

    /**
     * Checks the code given by the captcha
     *
     * @param string $gRecaptcha $_POST['g-recaptcha-response']
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getResponse($gRecaptcha)
    {
        $googleResponse = [];

        if (empty($gRecaptcha) || is_null($this->secretKey)) {
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
        } catch (\Throwable $e) {
            Craft::error('sprout-forms-google-recaptcha', $e->getMessage());
        }

        return $googleResponse;
    }
}
