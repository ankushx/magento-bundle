<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use MiniOrange\MagentoSocialLogin\Helper\Exception\MissingAttributesException;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * This class handles checking of the SAML attributes and NameID
 * coming in the response and mapping it to the attribute mapping
 * done in the plugin settings by the admin to update the user.
 */
class CheckAttributeMappingAction extends BaseAction implements HttpPostActionInterface
{
    //const TEST_VALIDATE_RELAYSTATE = SocialConstants::TEST_RELAYSTATE;

    private $userInfoResponse;
    private $flattenedUserInfoResponse;
    private $relayState;
    private $userEmail;
    private $provider;
    private $emailAttribute;
    private $usernameAttribute;
    private $firstName;
    private $lastName;
    private $checkIfMatchBy;
    private $groupName;

    private $testAction;
    private $processUserAction;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\ShowTestResultsAction $testAction,
        \MiniOrange\MagentoSocialLogin\Controller\Actions\ProcessUserAction $processUserAction
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->emailAttribute = $socialUtility->getStoreConfig(SocialConstants::MAP_EMAIL);
        $this->emailAttribute = $socialUtility->isBlank($this->emailAttribute) ? SocialConstants::DEFAULT_MAP_EMAIL : $this->emailAttribute;
        $this->usernameAttribute = $socialUtility->getStoreConfig(SocialConstants::MAP_USERNAME);
        $this->usernameAttribute = $socialUtility->isBlank($this->usernameAttribute) ? SocialConstants::DEFAULT_MAP_USERN : $this->usernameAttribute;
        $this->firstName = $socialUtility->getStoreConfig(SocialConstants::MAP_FIRSTNAME);
        $this->firstName = $socialUtility->isBlank($this->firstName) ? SocialConstants::DEFAULT_MAP_FN : $this->firstName;
        $this->lastName = $socialUtility->getStoreConfig(SocialConstants::MAP_LASTNAME);
        $this->checkIfMatchBy = $socialUtility->getStoreConfig(SocialConstants::MAP_MAP_BY);
        $this->testAction = $testAction;
        $this->processUserAction = $processUserAction;
        parent::__construct($context, $socialUtility);
    }

    /**
     * Execute function to execute the classes function.
     */
    public function execute()
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: execute");
        $attrs = $this->userInfoResponse;
        $flattenedAttrs =  $this->flattenedUserInfoResponse;
        $userEmail = $this->userEmail;
        $this->moSocialCheckMapping($attrs, $flattenedAttrs, $userEmail);
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }
    

    /**
     * This function checks the SAML Attribute Mapping done
     * in the plugin and matches it to update the user's
     * attributes.
     *
     * @param $attrs
     * @throws MissingAttributesException;
     */
    private function moSocialCheckMapping($attrs, $flattenedAttrs, $userEmail)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: moSocialCheckMapping");
        if (empty($attrs)) {
            throw new MissingAttributesException;
        }
        $this->checkIfMatchBy = SocialConstants::DEFAULT_MAP_BY;
        $this->processFirstName($flattenedAttrs);
        $this->processLastName($flattenedAttrs);
        $this->processUserName($flattenedAttrs);
        $this->processEmail($flattenedAttrs);
        //$this->processGroupName($flattenedAttrs);

        $this->processResult($attrs, $flattenedAttrs, $userEmail);
    }


    /**
     * Process the result to either show a Test result
     * screen or log/create user in Magento.
     *
     * @param $attrs
     */
    private function processResult($attrs, $flattenedattrs, $email)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: processResult");
        $isGoogleTest =  $this->socialUtility->getStoreConfig(SocialConstants::IS_GOOGLE_TEST);
        $isFacebookTest =  $this->socialUtility->getStoreConfig(SocialConstants::IS_FACEBOOK_TEST);
        $isLinkedinTest =  $this->socialUtility->getStoreConfig(SocialConstants::IS_LINKEDIN_TEST);
        $isTwitterTest =  $this->socialUtility->getStoreConfig(SocialConstants::IS_TWITTER_TEST);
        if ($isGoogleTest == true) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_GOOGLE_TEST, false);
            $this->socialUtility->flushCache();
            $this->testAction->setAttrs($flattenedattrs)->setUserEmail($email)->execute();
        } elseif ($isFacebookTest == true) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_FACEBOOK_TEST, false);
            $this->socialUtility->flushCache();
            $this->testAction->setAttrs($flattenedattrs)->setUserEmail($email)->execute();
        } elseif ($isLinkedinTest == true) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_LINKEDIN_TEST, false);
            $this->socialUtility->flushCache();
            $this->testAction->setAttrs($flattenedattrs)->setUserEmail($email)->execute();
        } elseif ($isTwitterTest == true) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_TWITTER_TEST, false);
            $this->socialUtility->flushCache();
            $this->testAction->setAttrs($flattenedattrs)->setUserEmail($email)->execute();
        } else {
            $this->processUserAction->setFlattenedAttrs($flattenedattrs)->setAttrs($attrs)->setUserEmail($email)->execute();
        }
    }

    /**
     * Check if the attribute list has a FirstName. If
     * no firstName is found then NameID is considered as
     * the firstName. This is done because Magento needs
     * a firstName for creating a new user.
     *
     * @param $attrs
     */
    private function processFirstName(&$attrs)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: processFirstName");
        if (!isset($attrs[$this->firstName])) {
            $parts  = explode("@", $this->userEmail);
            $name = $parts[0];
            $this->socialUtility->log_debug("CheckAttributeMappingAction: processFirstName: ".$name);
            $attrs[$this->firstName] = $name;
        }
    }

    private function processLastName(&$attrs)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: processLastName");
        if (!isset($attrs[$this->lastName])) {
            $parts  = explode("@", $this->userEmail);
            $name = $parts[1];
            $this->socialUtility->log_debug("CheckAttributeMappingAction: processLastName: ".$name);
            $attrs[$this->lastName] = $name;
        }
    }


    /**
     * Check if the attribute list has a UserName. If
     * no UserName is found then NameID is considered as
     * the UserName. This is done because Magento needs
     * a UserName for creating a new user.
     *
     * @param $attrs
     */
    private function processUserName(&$attrs)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: procesUserName");
        if (!isset($attrs[$this->usernameAttribute])) {
            $attrs[$this->usernameAttribute] = $this->userEmail;
        }
    }


    /**
     * Check if the attribute list has a Email. If
     * no Email is found then NameID is considered as
     * the Email. This is done because Magento needs
     * a Email for creating a new user.
     *
     * @param $attrs
     */
    private function processEmail(&$attrs)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: processEmail");
        if (!isset($attrs[$this->emailAttribute])) {
            $attrs[$this->emailAttribute] = $this->userEmail;
        }
    }


    /**
     * Check if the attribute list has a Group/Role. If
     * no Group/Role is found then NameID is considered as
     * the Group/Role. This is done because Magento needs
     * a Group/Role for creating a new user.
     *
     * @param $attrs
     */
    private function processGroupName(&$attrs)
    {
        $this->socialUtility->log_debug("CheckAttributeMappingAction: processGroupName");
        if (!isset($attrs[$this->groupName])) {
            $this->groupName = [];
        }
    }


    /** Setter for the OAuth Response Parameter */
    public function setUserInfoResponse($userInfoResponse)
    {
        $this->userInfoResponse = $userInfoResponse;
        return $this;
    }

    /** Setter for the OAuth Response Parameter */
    public function setFlattenedUserInfoResponse($flattenedUserInfoResponse)
    {
        $this->flattenedUserInfoResponse = $flattenedUserInfoResponse;
        return $this;
    }

    /** Setter for the user email Parameter */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /** Setter for the RelayState Parameter */
    public function setRelayState($relayState)
    {
        $this->relayState = $relayState;
        return $this;
    }
}
