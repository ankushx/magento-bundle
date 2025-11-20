<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\Exception\NotRegisteredException;
use MiniOrange\MagentoSocialLogin\Helper\Exception\RequiredFieldsException;

/**
 * The base action class that is inherited by each of the action
 * class. It consists of certain common functions that needs to
 * be inherited by each of the action class. Extends the
 * \Magento\Framework\App\Action\Action class which is usually
 * extended by Controller class.
 */
abstract class BaseAction extends \Magento\Framework\App\Action\Action
{

    protected $socialUtility;
    protected $context;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->socialUtility = $socialUtility;
        parent::__construct($context);
    }


    /**
     * This function checks if the required fields passed to
     * this function are empty or not. If empty throw an exception.
     *
     * @param $array
     * @throws RequiredFieldsException
     */
    protected function checkIfRequiredFieldsEmpty($array)
    {
        foreach ($array as $key => $value) {

            if ((is_array($value) && ( !isset($value[$key]) || $this->socialUtility->isBlank($value[$key])) )
                || $this->socialUtility->isBlank($value)
              ) {
                throw new RequiredFieldsException();
            }
        }
    }


    /**
     * This function is used to send AuthorizeRequest as a request Parameter.
     * LogoutRequest & AuthRequest is sent in the request parameter if the binding is
     * set as HTTP Redirect. Http Redirect is the default way Authn Request
     * is sent. Function also generates the signature and appends it in the
     * parameter as well along with the relayState parameter
     * @param $samlRequest
     * @param $sendRelayState
     * @param $idpUrl
     */
    protected function sendHTTPRedirectRequest($socialLoginRequest, $authorizeUrl)
    {
        
        $socialLoginRequest = $authorizeUrl . $socialLoginRequest ;
        $this->socialUtility->log_debug("auth request: ", $socialLoginRequest);
        return $this->resultRedirectFactory->create()->setUrl($socialLoginRequest);
    }

    
    /** This function is abstract that needs to be implemented by each Action Class */
    abstract public function execute();


    /* ===================================================================================================
                THE FUNCTIONS BELOW ARE FREE PLUGIN SPECIFIC AND DIFFER IN THE PREMIUM VERSION
       ===================================================================================================
     */

    /**
     * This function checks if the user has registered himself
     * and throws an Exception if not registered. Checks the
     * if the admin key and api key are saved in the database.
     *
     * @throws NotRegisteredException
     */
    protected function checkIfValidPlugin()
    {
        
        if (!$this->socialUtility->micr()) {
            throw new NotRegisteredException;
        }
    }
}
