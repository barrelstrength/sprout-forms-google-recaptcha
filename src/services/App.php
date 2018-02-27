<?php

namespace barrelstrength\sproutgooglerecaptcha\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var Recaptcha
     */
    public $recaptcha;

    public function init()
    {
        $this->recaptcha = new Recaptcha();
    }
}