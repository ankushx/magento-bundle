<?php

namespace MiniOrange\MagentoSocialLogin\Helper\Exception;

use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;

/**
 * Exception denotes that there was a password mismatch
 */
class PasswordMismatchException extends \Exception
{
    public function __construct()
    {
        $message     = SocialMessages::parse('PASS_MISMATCH');
        $code         = 122;
        parent::__construct($message, $code, null);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
