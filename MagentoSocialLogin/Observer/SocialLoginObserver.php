<?php

namespace MiniOrange\MagentoSocialLogin\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use MiniOrange\MagentoSocialLogin\Controller\Actions\AdminLoginAction;
use MiniOrange\MagentoSocialLogin\Controller\Actions\ShowTestResultsAction;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use Magento\Framework\Event\Observer;
use MiniOrange\MagentoSocialLogin\Controller\Actions\ReadAuthorizationResponse;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\SocialUtility;
use Psr\Log\LoggerInterface;

/**
 * This is our main Observer class. Observer class are used as a callback
 * function for all of our events and hooks. This particular observer
 * class is being used to check if a SAML request or response was made
 * to the website. If so then read and process it. Every Observer class
 * needs to implement ObserverInterface.
 */
class SocialLoginObserver implements ObserverInterface
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
//    private $requestInterface;
    private $request;

    public function __construct(
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        ReadAuthorizationResponse $readAuthorizationResponse,
        SocialUtility $socialUtility,
        AdminLoginAction $adminLoginAction,
        Http $httpRequest,
        RequestInterface $request,
        ShowTestResultsAction $testAction
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
    }

    /**
     * This function is called as soon as the observer class is initialized.
     * Checks if the request parameter has any of the configured request
     * parameters and handles any exception that the system might throw.
     *
     * @param $observer
     */
    public function execute(Observer $observer)
    {
        $keys             = array_keys($this->request->getParams());
        $operation         = array_intersect($keys, $this->requestParams);


        try {
            $params = $this->request->getParams(); // get params
            $postData = $this->request->getPost(); // get only post params
            $isGoogleTest = $this->socialUtility->getStoreConfig(SocialConstants::IS_GOOGLE_TEST);
            $isFacebookTest = $this->socialUtility->getStoreConfig(SocialConstants::IS_FACEBOOK_TEST);
            $isLinkedinTest = $this->socialUtility->getStoreConfig(SocialConstants::IS_LINKEDIN_TEST);
            $isTwitterTest = $this->socialUtility->getStoreConfig(SocialConstants::IS_TWITTER_TEST);

            // request has values then it takes priority over others
            if (count($operation) > 0) {
                $this->_route_data(array_values($operation)[0], $observer, $params, $postData);

            }
        } catch (\Exception $e) {
            if ($isGoogleTest || $isFacebookTest || $isLinkedinTest || $isTwitterTest) { // show a failed validation screen
                $this->testAction->setOAuthException($e)->setHasExceptionOccurred(true)->execute();
            }
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->debug($e->getMessage());
        }
    }


    /**
     * Route the request data to appropriate functions for processing.
     * Check for any kind of Exception that may occur during processing
     * of form post data. Call the appropriate action.
     *
     * @param $op //refers to operation to perform
     * @param $observer
     */
    private function _route_data($op, $observer, $params, $postData)
    {
        switch ($op) {
            case $this->requestParams[0]:
                if ($params['option']==SocialConstants::LOGIN_ADMIN_OPT) {
                    $this->adminLoginAction->execute();
                }
                break;
        }
    }
}
