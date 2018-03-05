<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutgooglerecaptcha\contracts;

use barrelstrength\sproutgooglerecaptcha\SproutGoogleRecaptcha;
use Craft;

/**
 * Google reCAPTCHA v2 class
 *
 */
class GoogleRecaptcha
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
    private  $remoteIp = null;

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
        $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();
        $this->siteKey = $sproutFormsSettings->googleRecaptchaSiteKey;
        $this->secretKey = $sproutFormsSettings->googleRecaptchaSecretKey;
        $this->remoteIp = $_SERVER['REMOTE_ADDR'];
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
     * Generate the HTML code block for the captcha
     *
     * @return string
     */
    public function getHtml()
    {
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

            return '<div class="g-recaptcha" '.$data.'></div>';
        }
    }

    /**
     * Checks the code given by the captcha
     *
     * @param string $gRecaptcha $_POST['g-recaptcha-response']
     *
     * @return array|null
     */
    public function getResponse($gRecaptcha)
    {
        $verifyResponse = null;
        $response = [
            'success' => false,
            'message' => ''
        ];

        if (empty($gRecaptcha) || is_null($this->secretKey)) {
            $response['message'] = "Can't be blank";
            return $response;
        }

        $params = [
            'secret' => $this->secretKey,
            'response' => $gRecaptcha,
            'remoteip' => $this->remoteIp,
        ];

        if (function_exists('curl_version')) {
            $curl = curl_init(self::VERIFY_URL);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->verifyTimeout);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

            // @todo test this on live server
            if (Craft::$app->getConfig()->getGeneral()->devMode){
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            }
            $verifyResponse = curl_exec($curl);
        } else {
            $url = self::VERIFY_URL.'?'.http_build_query($params);
            $verifyResponse = file_get_contents($url);
        }

        if (empty($verifyResponse) || is_null($verifyResponse)) {
            $response['message'] = "Something went wrong";
            return $response;
        }

        $json = json_decode($verifyResponse);

        return $json;
    }
}