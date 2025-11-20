<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use MiniOrange\MagentoSocialLogin\Helper\Exception\IncorrectUserInfoDataException;
use MiniOrange\MagentoSocialLogin\Helper\Exception\UserEmailNotFoundException;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;

/**
 * Handles processing of SAML Responses from the IDP. Process the SAML Response
 * from the IDP and detect if it's a valid response from the IDP. Validate the
 * certificates and the SAML attributes and Update existing user attributes
 * and groups if necessary. Log the user in.
 */
class ProcessResponseAction extends BaseAction
{
    private $userInfoResponse;
    private $attrMappingAction;
    private $testAction;
    private $processUserAction;
    private $provider;
    private $email;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\CheckAttributeMappingAction $attrMappingAction
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->attrMappingAction = $attrMappingAction;
        parent::__construct($context, $socialUtility);
    }

    /**
     * Execute function to execute the classes function.
     * @throws IncorrectUserInfoDataException
     */
    public function execute()
    {
        $this->socialUtility->log_debug("processResponseAction: execute");
        $this->validateUserInfoData();

        $userInfoResponse = $this->userInfoResponse;
        // flatten the nested OAuth response
        $flattenedUserInfoResponse = [];
        $flattenedUserInfoResponse = $this->getflattenedArray("", $userInfoResponse, $flattenedUserInfoResponse);
     
        if ($this->provider == 'linkedin') {
            $userEmail = $this->email;
        } else {
            $userEmail = $this->findUserEmail($userInfoResponse);
        }


        if (empty($userEmail)) {
            return $this->getResponse()->setBody("Email address not received. Please check attribute mapping.");
        }
        $this->attrMappingAction->setProvider($this->provider)->setUserInfoResponse($userInfoResponse)
                                ->setFlattenedUserInfoResponse($flattenedUserInfoResponse)
                                ->setUserEmail($userEmail)->execute();
    }

    public function setLinkedinEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    private function findUserEmail($arr)
    {
        $this->socialUtility->log_debug("processResponseAction: findUserEmail");
        if ($arr) {
            foreach ($arr as $value) {
                if (is_array($value)) {
                    $value = $this->findUserEmail($value);
                }
                if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $value;
                }
            }
            return "";
        }
    }


    private function getflattenedArray($keyprefix, $arr, &$flattenedattributesarray)
    {

        foreach ($arr as $key => $resource) {

            if (is_array($resource) || is_object($resource)) {
                if (!empty($keyprefix)) {
                    $keyprefix .= ".";
                }
                $this->getflattenedArray($keyprefix .$key, $resource, $flattenedattributesarray);
            } else {
                if (!empty($keyprefix)) {
                    $key = $keyprefix . "." . $key;
                }

                $flattenedattributesarray[$key] = $resource;
            }

        }
            return $flattenedattributesarray;
    }

    /**
     * Function checks if the
     * @throws IncorrectUserInfoDataException
     */
    private function validateUserInfoData()
    {
        $this->socialUtility->log_debug("processResponseAction: validateUserInfoData");
        $userInfo = $this->userInfoResponse;

        if (isset($userInfo['error'])) {
            throw new IncorrectUserInfoDataException();
        }
    }

    /** Setter for the UserInfo Parameter */
    public function setUserInfoResponse($userInfoResponse)
    {
        $this->socialUtility->log_debug("processResponseAction: setUserInfoResponse");
        $this->userInfoResponse = $userInfoResponse;
        return $this;
    }
}
