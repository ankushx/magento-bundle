<?php

namespace MiniOrange\MagentoSocialLogin\Helper\Exception;

use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;

/**
 * Exception denotes that there was an Invalid Operation
 */
class IncorrectUserInfoDataException extends \Exception
{
    public function __construct()
    {
        $message     = SocialMessages::parse('INVALID_USER_INFO');
        $code         = 119;
        parent::__construct($message, $code, null);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
