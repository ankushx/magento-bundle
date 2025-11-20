<?php

namespace MiniOrange\MagentoSocialLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use Magento\Framework\Event\Observer;
use MiniOrange\MagentoSocialLogin\Controller\Actions\ReadAuthorizationResponse;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * This is our main Observer class. Observer class are used as a callback
 * function for all of our events and hooks. This particular observer
 * class is being used to check if a SAML request or response was made
 * to the website. If so then read and process it. Every Observer class
 * needs to implement ObserverInterface.
 */
class SocialLogoutObserver implements ObserverInterface
{
    private $requestParams =  [
        'option'
    ];

    private $messageManager;
    private $logger;
    private $readAuthorizationResponse;
    private $socialUtility;
    private $adminLoginAction;
    private $testAction;

    private $currentControllerName;
    private $currentActionName;
    private $requestInterface;
    private $request;

    protected $_redirect;
    protected $_response;

    
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\ReadAuthorizationResponse $readAuthorizationResponse,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\AdminLoginAction $adminLoginAction,
        \Magento\Framework\App\Request\Http $httpRequest,
        \Magento\Framework\App\RequestInterface $request,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\ShowTestResultsAction $testAction,
        RedirectInterface $redirect,
        ResponseInterface $response
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->readAuthorizationResponse = $readAuthorizationResponse;
        $this->socialUtility = $socialUtility;
        $this->adminLoginAction = $adminLoginAction;
        $this->currentControllerName = $httpRequest->getControllerName();
        $this->currentActionName = $httpRequest->getActionName();
        $this->request = $request;
        $this->testAction = $testAction;
        $this->_redirect = $redirect;
        $this->_response = $response;
    }

    /**
     * This function is called as soon as the observer class is initialized.
     * Checks if the request parameter has any of the configured request
     * parameters and handles any exception that the system might throw.
     *
     * @param $observer
     * @return
     */
    public function execute(Observer $observer)
    {
        $logoutUrl = $this->socialUtility->getStoreConfig(SocialConstants::SOCIAL_LOGIN_LOGOUT_URL);
        if (!empty($logoutUrl)) {
            $temp =  '<script>window.location = "'.$logoutUrl.'";</script>';
            return $this->_response->setBody($temp);
        }
    }
}
