<?php

namespace MiniOrange\MagentoSocialLogin\Helper\Exception;

use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;

/**
 * Exception denotes that the SAML resquest or response has missing
 * ID attribute.
 */
class MissingAttributesException extends \Exception
{
    public function __construct()
    {
        $message     = SocialMessages::parse('MISSING_ATTRIBUTES_EXCEPTION');
        $code         = 125;
        parent::__construct($message, $code, null);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
