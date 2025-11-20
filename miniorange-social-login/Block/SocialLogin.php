<?php
namespace MiniOrange\MagentoSocialLogin\Block;

use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use Magento\Framework\Data\Form\FormKey;

/**
 * This class is used to denote our admin block for all our
 * backend templates. This class has certain commmon
 * functions which can be called from our admin template pages.
 */
class SocialLogin extends \Magento\Framework\View\Element\Template
{


    private $socialUtility;
    private $adminRoleModel;
    private $userGroupModel;
    private $formKey;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility,
        \Magento\Authorization\Model\ResourceModel\Role\Collection $adminRoleModel,
        \Magento\Customer\Model\ResourceModel\Group\Collection $userGroupModel,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        FormKey $formKey,
        array $data = []
    ) {
        $this->socialUtility = $socialUtility;
        $this->adminRoleModel = $adminRoleModel;
        $this->userGroupModel = $userGroupModel;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * This function is a test function to check if the template
     * is being loaded properly in the frontend without any issues.
     */
    public function getHelloWorldTxt()
    {
        return 'Hello world!';
    }
    public function getWebsiteCollection()
    {
        $collection = $this->_websiteCollectionFactory->create();
        return $collection;
    }

    /**
     * This function retrieves the miniOrange customer Email
     * from the database. To be used on our template pages.
     */
    public function getCustomerEmail()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::CUSTOMER_EMAIL);
    }


    public function isHeader()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SEND_HEADER);
    }


    public function isBody()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SEND_BODY);
    }

    /**
     * This function checks if Social Login has been configured or not.
     */
    public function isSocialLoginConfigured()
    {
        return $this->socialUtility->isSocialLoginConfigured();
    }

    /**
     * This function fetches the Client ID saved by the admin
     */
    public function getClientID()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::CLIENT_ID);
    }

    /**
     * This function fetches the Client secret saved by the admin
     */
    public function getClientSecret()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::CLIENT_SECRET);
    }

    /**
     * This function fetches the Scope saved by the admin
     */
    public function getScope()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SCOPE);
    }

    /**
     * This function fetches the Authorize URL saved by the admin
     */
    public function getAuthorizeURL()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::AUTHORIZE_URL);
    }

    /**
     * This function fetches the AccessToken URL saved by the admin
     */
    public function getAccessTokenURL()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::ACCESSTOKEN_URL);
    }

    /**
     * This function fetches the GetUserInfo URL saved by the admin
     */
    public function getUserInfoURL()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::GETUSERINFO_URL);
    }


    public function getLogoutURL()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SOCIAL_LOGIN_LOGOUT_URL);
    }

    /**
     * This function gets the admin CSS URL to be appended to the
     * admin dashboard screen.
     */
    public function getAdminCssURL()
    {
        return $this->socialUtility->getAdminCssUrl('adminSettings.css');
    }

      /**
       * This function gets the current version of the plugin
       * admin dashboard screen.
       */
    public function getCurrentVersion()
    {
        return SocialConstants::VERSION;
    }


    /**
     * This function gets the admin JS URL to be appended to the
     * admin dashboard pages for plugin functionality
     */
    public function getAdminJSURL()
    {
        return $this->socialUtility->getAdminJSUrl('adminSettings.js');
    }


    /**
     * This function gets the IntelTelInput JS URL to be appended
     * to admin pages to show country code dropdown on phone number
     * fields.
     */
    public function getIntlTelInputJs()
    {
        return $this->socialUtility->getAdminJSUrl('intlTelInput.min.js');
    }


    /**
     * This function fetches/creates the TEST Configuration URL of the
     * Plugin.
     */
    public function getGoogleTestUrl()
    {
        return $this->getSPInitiatedUrlForGoogle(SocialConstants::GOOGLE_TEST_RELAYSTATE);
    }

     /**
      * This function fetches/creates the TEST Configuration URL of the
      * Plugin.
      */
    public function getFacebookTestUrl()
    {
        return $this->getSPInitiatedUrlForFacebook(SocialConstants::FACEBOOK_TEST_RELAYSTATE);
    }

     /**
      * This function fetches/creates the TEST Configuration URL of the
      * Plugin.
      */
    public function getLinkedinTestUrl()
    {
        return $this->getSPInitiatedUrlForLinkedin(SocialConstants::LINKEDIN_TEST_RELAYSTATE);
    }

     /**
      * This function fetches/creates the TEST Configuration URL of the
      * Plugin.
      */
    public function getTwitterTestUrl()
    {
        return $this->getSPInitiatedUrlForTwitter(SocialConstants::TWITTER_TEST_RELAYSTATE);
    }


    /**
     * Get/Create Base URL of the site
     */
    public function getBaseUrl()
    {
        // Get the current website's default store
        $website = $this->_storeManager->getWebsite();
        $store = $website->getDefaultStore();
        return $store->getBaseUrl();
    }

    /**
     * Get/Create Base URL of the site
     */
    public function getCallBackUrl()
    {
        return $this->getBaseUrl() . SocialConstants::CALLBACK_URL;
    }

    public function getGoogleClientId()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_CLIENT_ID);
    }

    public function getFacebookClientId()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_CLIENT_ID);
    }

    public function getLinkedinClientId()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_CLIENT_ID);
    }

    public function getTwitterClientId()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_ID);
    }

    public function getGoogleClientSecret()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_CLIENT_SECRET);
    }

    public function getFacebookClientSecret()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_CLIENT_SECRET);
    }

    public function getLinkedinClientSecret()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_CLIENT_SECRET);
    }

    public function getTwitterClientSecret()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_SECRET);
    }

    public function getGoogleEnable()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::ENABLE_GOOGLE);
    }

    public function getLinkedinEnable()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::ENABLE_LINKEDIN);
    }

    public function getFacebookEnable()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::ENABLE_FACEBOOK);
    }

    public function getTwitterEnable()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::ENABLE_TWITTER);
    }




    /**
     * Create the URL for one of the SAML SP plugin
     * sections to be shown as link on any of the
     * template files.
     */
    public function getExtensionPageUrl($page)
    {
        return $this->socialUtility->getAdminUrl('mosocial/'.$page.'/index');
    }


    /**
     * Reads the Tab and retrieves the current active tab
     * if any.
     */
    public function getCurrentActiveTab()
    {
        $page = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => false]);
              $start = strpos($page, '/mosocial')+10;
        $end = strpos($page, '/index/key');
        $tab = substr($page, $start, $end-$start);
       
        return $tab;
    }

        /**
         * Just check and return if the user has verified his
         * license key to activate the plugin. Mostly used
         * on the account page to show the verify license key
         * screen.
         */
    public function isVerified()
    {
        return $this->socialUtility->mclv();
    }


    /**
     * Is the option to show SSO link on the Admin login page enabled
     * by the admin.
     */
    public function showAdminLink()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SHOW_ADMIN_LINK);
    }


    /**
     * Is the option to show SSO link on the Customer login page enabled
     * by the admin.
     */
    public function showCustomerLink()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::SHOW_CUSTOMER_LINK);
    }


    /**
     * Create/Get the SP initiated URL for the site.
     */
    public function getSPInitiatedUrlForGoogle($relayState = null)
    {
        return $this->socialUtility->getSPInitiatedUrlForGoogle($relayState);
    }

    /**
     * Create/Get the SP initiated URL for the site.
     */
    public function getSPInitiatedUrlForFacebook($relayState = null)
    {
        return $this->socialUtility->getSPInitiatedUrlForFacebook($relayState);
    }

    /**
     * Create/Get the SP initiated URL for the site.
     */
    public function getSPInitiatedUrlForLinkedin($relayState = null)
    {
        return $this->socialUtility->getSPInitiatedUrlForLinkedin($relayState);
    }

    /**
     * Create/Get the SP initiated URL for the site.
     */
    public function getSPInitiatedUrlForTwitter($relayState = null)
    {
        return $this->socialUtility->getSPInitiatedUrlForTwitter($relayState);
    }


    /**
     * This fetches the setting saved by the admin which decides if the
     * account should be mapped to username or email in Magento.
     */
    public function getAccountMatcher()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::MAP_MAP_BY);
    }

    /**
     * This fetches the setting saved by the admin which doesn't allow
     * roles to be assigned to unlisted users.
     */
    public function getDisallowUnlistedUserRole()
    {
        $disallowUnlistedRole = $this->socialUtility->getStoreConfig(SocialConstants::UNLISTED_ROLE);
        return !$this->socialUtility->isBlank($disallowUnlistedRole) ?  $disallowUnlistedRole : '';
    }


    /**
     * This fetches the setting saved by the admin which doesn't allow
     * users to be created if roles are not mapped based on the admin settings.
     */
    public function getDisallowUserCreationIfRoleNotMapped()
    {
        $disallowUserCreationIfRoleNotMapped = $this->socialUtility->getStoreConfig(SocialConstants::CREATEIFNOTMAP);
        return !$this->socialUtility->isBlank($disallowUserCreationIfRoleNotMapped) ?  $disallowUserCreationIfRoleNotMapped : '';
    }


    /**
     * This fetches the setting saved by the admin which decides what
     * attribute in the SAML response should be mapped to the Magento
     * user's userName.
     */
    public function getUserNameMapping()
    {
        $amUserName = $this->socialUtility->getStoreConfig(SocialConstants::MAP_USERNAME);
        return !$this->socialUtility->isBlank($amUserName) ?  $amUserName : '';
    }


    public function getGroupMapping()
    {
        $amGroupName = $this->socialUtility->getStoreConfig(SocialConstants::MAP_GROUP);
        return !$this->socialUtility->isBlank($amGroupName) ?  $amGroupName : '';
    }

    /**
     * This fetches the setting saved by the admin which decides what
     * attribute in the SAML response should be mapped to the Magento
     * user's Email.
     */
    public function getUserEmailMapping()
    {
        $amEmail = $this->socialUtility->getStoreConfig(SocialConstants::MAP_EMAIL);
        return !$this->socialUtility->isBlank($amEmail) ?  $amEmail : '';
    }

    /**
     * This fetches the setting saved by the admin which decides what
     * attribute in the SAML response should be mapped to the Magento
     * user's firstName.
     */
    public function getFirstNameMapping()
    {
        $amFirstName = $this->socialUtility->getStoreConfig(SocialConstants::MAP_FIRSTNAME);
        return !$this->socialUtility->isBlank($amFirstName) ?  $amFirstName : '';
    }


    /**
     * This fetches the setting saved by the admin which decides what
     * attributein the SAML resposne should be mapped to the Magento
     * user's lastName
     */
    public function getLastNameMapping()
    {
        $amLastName = $this->socialUtility->getStoreConfig(SocialConstants::MAP_LASTNAME);
        return !$this->socialUtility->isBlank($amLastName) ?  $amLastName : '';
    }


    /**
     * Get all admin roles set by the admin on his site.
     */
    public function getAllRoles()
    {
        $rolesCollection = $this->adminRoleModel->addFieldToFilter('role_type', 'G');
        // Convert the filtered collection to an options array
        $rolesOptionsArray = $rolesCollection->toOptionArray();
        return $rolesOptionsArray;
    }

    /**
     * This function fetches the X509 cert saved by the admin for the IDP
     * in the plugin settings.
     */
    public function getX509Cert()
    {
        return $this->socialUtility->getStoreConfig(SocialConstants::X509CERT);
    }


    /**
     * Get all customer groups set by the admin on his site.
     */
    public function getAllGroups()
    {
        return $this->userGroupModel->toOptionArray();
    }


    /**
     * Get the default role to be set for the user if it
     * doesn't match any of the role/group mappings
     */
    public function getDefaultRole()
    {
        $defaultRole = $this->socialUtility->getStoreConfig(SocialConstants::MAP_DEFAULT_ROLE);
        return !$this->socialUtility->isBlank($defaultRole) ?  $defaultRole : SocialConstants::DEFAULT_ROLE;
    }

    /**
     * Get the Current Admin user from session
     */
    public function getCurrentAdminUser()
    {
        return $this->socialUtility->getCurrentAdminUser();
    }


    /**
     * Fetches/Creates the text of the button to be shown
     * for SP inititated login from the admin / customer
     * login pages.
     */
    public function getSSOButtonText()
    {
        $buttonText = $this->socialUtility->getStoreConfig(SocialConstants::BUTTON_TEXT);
        $idpName = $this->socialUtility->getStoreConfig(SocialConstants::APP_NAME);
        return !$this->socialUtility->isBlank($buttonText) ?  $buttonText : 'Login with ' . $idpName;
    }


     /**
      * Get base url of miniorange
      */
    public function getMiniOrangeUrl()
    {
        return $this->socialUtility->getMiniOrangeUrl();
    }


    /**
     * Get Admin Logout URL for the site
     */
    public function getAdminLogoutUrl()
    {
        return $this->socialUtility->getLogoutUrl();
    }

    /**
     * Is Test Configuration clicked?
     */
    public function getIsTestConfigurationClicked()
    {
        return $this->socialUtility->getIsTestConfigurationClicked();
    }

    /**
     * Get CSRF form key value
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }


    /* ===================================================================================================
                THE FUNCTIONS BELOW ARE FREE PLUGIN SPECIFIC AND DIFFER IN THE PREMIUM VERSION
       ===================================================================================================
     */


    /**
     * This function checks if the user has completed the registration
     * and verification process. Returns TRUE or FALSE.
     */
    public function isEnabled()
    {
        return $this->socialUtility->micr();
    }

    public function getProductVersion(){
        return  $this->socialUtility->getProductVersion(); 
    }

    public function getEdition(){
        return $this->socialUtility->getEdition();
    }

    public function getCurrentDate(){
        return $this->socialUtility->getCurrentDate();
    }

    public function dataAdded(){
        $this->socialUtility->setStoreConfig(SocialConstants::DATA_ADDED,1);
        $this->socialUtility->flushCache() ;
    }

    public function checkDataAdded(){
        return $this->socialUtility->getStoreConfig(SocialConstants::DATA_ADDED);
    }
    
    public function getTimeStamp(){
        if($this->socialUtility->getStoreConfig(SocialConstants::TIME_STAMP) == null){
            $this->socialUtility->setStoreConfig(SocialConstants::TIME_STAMP,time());
            $this->socialUtility->flushCache();
            return time();
        }
        return $this->socialUtility->getStoreConfig(SocialConstants::TIME_STAMP);
    }
}
