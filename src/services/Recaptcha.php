<?php
/**
 * Sprout Google Recaptcha plugin for Craft CMS 3.x
 *
 * Google Recaptcha solution for Sprout Forms
 *
 * @link      https://www.barrelstrengthdesign.com/
 * @copyright Copyright (c) 2018 Barrel Strength
 */

namespace barrelstrength\sproutgooglerecaptcha\services;

use barrelstrength\sproutgooglerecaptcha\contracts\GoogleRecaptcha;
use Craft;
use craft\base\Component;

/**
 * @author    Barrel Strength
 * @package   SproutGoogleRecaptcha
 * @since     1.0.0
 */
class Recaptcha extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @var GoogleRecaptcha
     */
    private $recaptcha = null;

    public function init()
    {
        if (is_null($this->recaptcha)) {
            $this->recaptcha = new GoogleRecaptcha();
        }

        parent::init();
    }

    /*
     * Validate Response from Google Recaptcha
     * @param string $response usually $_POST['g-recaptcha-response']
     * @return array|null
     */
    public function getResponse($response)
    {
        return $this->recaptcha->getResponse($response);
    }

    /**
     * Verify Submission
     *
     * @return boolean
     */
    public function verifySubmission() : array
    {
        $response = [
            'success' => false,
            'message' => ''
        ];
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return $response;
        }

        if (!isset($_POST['g-recaptcha-response'])){
            $response['message'] = "Google recaptcha can't be blank";
            return $response;
        }

        $gRecaptcha = $_POST['g-recaptcha-response'] ?? null;

        $googleResponse = $this->getResponse($gRecaptcha);

        return $googleResponse;
    }


    /*
     * HTML for Form
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->recaptcha->getHtml();
    }

    /*
     * JS script for Google Recaptcha
     *
     * @return string
     */
    public function getScript()
    {
        return $this->recaptcha->getScript();
    }
}
