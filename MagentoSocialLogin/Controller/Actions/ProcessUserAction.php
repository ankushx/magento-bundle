<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use Magento\Authorization\Model\ResourceModel\Role\Collection;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use MiniOrange\MagentoSocialLogin\Helper\Exception\MissingAttributesException;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\SocialUtility;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use MiniOrange\MagentoSocialLogin\Helper\Curl;
use Magento\Framework\Stdlib\DateTime\dateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This action class processes the user attributes coming in
 * the SAML response to either log the customer or admin in
 * to their respective dashboard or create a customer or admin
 * based on the default role set by the admin and log them in
 * automatically.
 */
class ProcessUserAction extends BaseAction
{
    private $attrs;
    private $flattenedattrs;
    private $userEmail;
    private $checkIfMatchBy;
    private $defaultRole;
    private $emailAttribute;
    private $usernameAttribute;
    private $firstNameKey;
    private $lastNameKey;
    private $storeManager;
    private $userGroupModel;
    private $adminRoleModel;
    private $adminUserModel;
    private $customerModel;
    private $customerLoginAction;
    private $responseFactory;
    private $customerFactory;
    private $userFactory;
    private $randomUtility;
    protected $dateTime;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        SocialUtility $socialUtility,
        \Magento\Customer\Model\ResourceModel\Group\Collection $userGroupModel,
        Collection $adminRoleModel,
        User $adminUserModel,
        Customer $customerModel,
        StoreManagerInterface $storeManager,
        ResponseFactory $responseFactory,
        CustomerLoginAction $customerLoginAction,
        CustomerFactory $customerFactory,
        UserFactory $userFactory,
        Random $randomUtility,
        dateTime $dateTime,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->emailAttribute = $socialUtility->getStoreConfig(SocialConstants::MAP_EMAIL);
        $this->emailAttribute = $socialUtility->isBlank($this->emailAttribute) ? SocialConstants::DEFAULT_MAP_EMAIL : $this->emailAttribute;
        $this->usernameAttribute = $socialUtility->getStoreConfig(SocialConstants::MAP_USERNAME);
        $this->usernameAttribute = $socialUtility->isBlank($this->usernameAttribute) ? SocialConstants::DEFAULT_MAP_USERN : $this->usernameAttribute;
        $this->firstNameKey = $socialUtility->getStoreConfig(SocialConstants::MAP_FIRSTNAME);
        $this->firstNameKey = $socialUtility->isBlank($this->firstNameKey) ? SocialConstants::DEFAULT_MAP_FN : $this->firstNameKey;
        $this->lastNameKey = $socialUtility->getStoreConfig(SocialConstants::MAP_LASTNAME);
        $this->defaultRole = $socialUtility->getStoreConfig(SocialConstants::MAP_DEFAULT_ROLE);
        $this->checkIfMatchBy = $socialUtility->getStoreConfig(SocialConstants::MAP_MAP_BY);
        $this->userGroupModel = $userGroupModel;
        $this->adminRoleModel = $adminRoleModel;
        $this->adminUserModel = $adminUserModel;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->checkIfMatchBy = $socialUtility->getStoreConfig(SocialConstants::MAP_MAP_BY);
        $this->responseFactory = $responseFactory;
        $this->customerLoginAction = $customerLoginAction;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->randomUtility = $randomUtility;
        $this->dateTime=$dateTime;
        $this->scopeConfig = $scopeConfig;
            parent::__construct($context, $socialUtility);
    }
    
    
    /**
     * Execute function to execute the classes function.
     *
     * @throws MissingAttributesException
     */
    public function execute()
    {
        // throw an exception if attributes are empty
        if (empty($this->attrs)) {
            throw new MissingAttributesException;
        }
        $firstName = isset($this->flattenedattrs[$this->firstNameKey]) ?
            $this->flattenedattrs[$this->firstNameKey]: null;
        $lastName = isset($this->flattenedattrs[$this->lastNameKey]) ? $this->flattenedattrs[$this->lastNameKey]: null;
        $userName = isset($this->flattenedattrs[$this->usernameAttribute]) ? $this->flattenedattrs[$this->usernameAttribute]: null;
        if ($this->socialUtility->isBlank($this->defaultRole)) {
            $this->defaultRole = SocialConstants::DEFAULT_ROLE;
        }

        // process the user
        $this->processUserAction($this->userEmail, $firstName, $lastName, $userName, $this->defaultRole);
    }


    /**
     * This function processes the user values to either create
     * a new user on the site and log him/her in or log an existing
     * user to the site. Mapping is done based on $checkIfMatchBy
     * variable. Either email or username.
     *
     * @param $user_email
     * @param $firstName
     * @param $lastName
     * @param $userName
     * @param $checkIfMatchBy
     * @param $defaultRole
     */
    private function processUserAction($user_email, $firstName, $lastName, $userName, $defaultRole)
    {
        $admin = false;

        // check if the a customer or admin user exists based on the email in OAuth response
        
        
       
        $user = $this->getCustomerFromAttributes($user_email);

        if (!$user) {
            $this->socialUtility->log_debug("ProcessUserAction: Inside autocreate user tab");
            
            
            $donotCreateUsers=$this->socialUtility->getStoreConfig(SocialConstants::MAGENTO_COUNTER);
            if (is_null($donotCreateUsers)) {
                 $this->socialUtility->setStoreConfig(SocialConstants::MAGENTO_COUNTER, 10);
                 $this->socialUtility->reinitConfig();
                 $donotCreateUsers=$this->socialUtility->getStoreConfig(SocialConstants::MAGENTO_COUNTER);
            }
            if ($donotCreateUsers<1) {
                $this->socialUtility->log_debug("Your Auto Create User Limit for the free Miniorange Magento Social Login plugin is exceeded. Please Upgrade to any of the Premium Plan to continue the service.")  ;
                $email = $this->scopeConfig->getValue('trans_email/ident_general/email',ScopeInterface::SCOPE_STORE);
                $site = $this->socialUtility->getBaseUrl();
                $magentoVersion = $this->socialUtility->getProductVersion();
                $this->socialUtility->reinitConfig();
                $previousDate = $this->socialUtility->getStoreConfig(SocialConstants::PREVIOUS_DATE);
                $timeStamp = $this->socialUtility->getStoreConfig(SocialConstants::TIME_STAMP);
                if($timeStamp == null){
                    $timeStamp = time();
                    $this->socialUtility->setStoreConfig(SocialConstants::TIME_STAMP,$timeStamp);
                    $this->socialUtility->flushCache();
                }
                $domain = $this->socialUtility->getBaseUrl();
                $freeInstalledDate = $this->socialUtility->getCurrentDate();
                $identityProvider = $this->socialUtility->getStoreConfig(SocialConstants::APP_NAME);
                $autoCreateLimit = 'Yes';
                $environmentName = $this->socialUtility->getEdition();
                $environmentVersion = $this->socialUtility->getProductVersion();
                $ssoProvider =  $this->socialUtility->getStoreConfig(SocialConstants::APP_NAME);
                $trackingDate = $this->socialUtility->getCurrentDate();
                if($previousDate == NULL){
                    $previousDate = $this->dateTime->gmtDate('Y-m-d H:i:s');
                    $this->socialUtility->setStoreConfig(SocialConstants::PREVIOUS_DATE,$previousDate);
                    $data = [
                    'timeStamp' => $timeStamp,
                    'domain' => $domain,
                    'environmentName' => $environmentName,
                    'environmentVersion' => $environmentVersion,
                    'FreeInstalledDate' => $freeInstalledDate,
                    'IdentityProvider' => $identityProvider,
                    'autoCreateLimit' => $autoCreateLimit
                ];
                Curl::submit_to_magento_team($data);
                }
                $currentDate = $this->dateTime->gmtDate('Y-m-d H:i:s');
                $previousDate=date_create($previousDate);
                $currentDate=date_create($currentDate);
                $diff=date_diff($previousDate,$currentDate);
                $diff = $diff->format("%R%a days");
                if($diff > 0){
                $this->socialUtility->setStoreConfig(SocialConstants::PREVIOUS_DATE, $currentDate);
                $data = [
                    'timeStamp' => $timeStamp,
                    'domain' => $domain,
                    'environmentName' => $environmentName,
                    'environmentVersion' => $environmentVersion,
                    'FreeInstalledDate' => $freeInstalledDate,
                    'IdentityProvider' => $identityProvider,
                    'autoCreateLimit' => $autoCreateLimit
                ];
                Curl::submit_to_magento_team($data);
            }
                $this->messageManager->addErrorMessage(SocialMessages::AUTO_CREATE_USER_LIMIT);
                 $url = $this->socialUtility->getCustomerLoginUrl();
                return $this->getResponse()->setRedirect($url)->sendResponse();
            }else {
                $count=$this->socialUtility->getStoreConfig(SocialConstants::MAGENTO_COUNTER);
                $this->socialUtility->setStoreConfig(SocialConstants::MAGENTO_COUNTER, $count-1);
                $this->socialUtility->reinitConfig();
                 $user = $this->createNewUser($user_email, $firstName, $lastName, $userName, $user, $admin);
                $this->socialUtility->log_debug("processUserAction: user created");
               
            }
        }

        
        // log the user in to it's respective dashboard
       
            $this->socialUtility->log_debug("processUserAction: redirecting customer");
            $this->customerLoginAction->setUser($user)->execute();
    }


    
    /**
     * Create a temporary email address based on the username
     * in the SAML response. Email Address is a required so we
     * need to generate a temp/fake email if no email comes from
     * the IDP in the SAML response.
     *
     * @param  $userName
     * @return string
     */
    private function generateEmail($userName)
    {
        $this->socialUtility->log_debug("processUserAction : generateEmail");
        $siteurl = $this->socialUtility->getBaseUrl();
        $siteurl = substr($siteurl, strpos($siteurl, '//'), strlen($siteurl)-1);
        return $userName .'@'.$siteurl;
    }

    /**
     * Create a new user based on the SAML response and attributes. Log the user in
     * to it's appropriate dashboard. This class handles generating both admin and
     * customer users.
     *
     * @param $user_email
     * @param $firstName
     * @param $lastName
     * @param $userName
     * @param $defaultRole
     * @param $user
     */
    private function createNewUser($user_email, $firstName, $lastName, $userName, $user, &$admin)
    {

        // generate random string to be inserted as a password
        $this->socialUtility->log_debug("processUserAction: createNewUser");
        $random_password = $this->randomUtility->getRandomString(8);
        $userName = !$this->socialUtility->isBlank($userName)? $userName : $user_email;
        $firstName = !$this->socialUtility->isBlank($firstName) ? $firstName : $userName;
        $lastName = !$this->socialUtility->isBlank($lastName) ? $lastName : $userName;

        // create admin or customer user based on the role
        $user = $this->createCustomer($userName, $user_email, $firstName, $lastName, $random_password);

        return $user;
    }


    /**
     * Create a new customer.
     *
     * @param $email
     * @param $userName
     * @param $random_password
     * @param $role_assigned
     */
    private function createCustomer($userName, $email, $firstName, $lastName, $random_password)
    {
        $this->socialUtility->log_debug("processUserAction: createCustomer");
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $store = $this->storeManager->getStore();
        $storeId = $store->getStoreId();
        return $this->customerFactory->create()
            ->setWebsiteId($websiteId)
            ->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setPassword($random_password)
            ->save();
    }

    /**
     * Get the Customer User from the Attributes in the SAML response
     * Return false if the customer doesn't exist. The customer is fetched
     * by email only. There are no usernames to set for a Magento Customer.
     *
     * @param $user_email
     * @param $userName
     */
    private function getCustomerFromAttributes($user_email)
    {
        $this->socialUtility->log_debug("processUserAction: getCustomerFromAttributes");
        $this->customerModel->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
        $customer = $this->customerModel->loadByEmail($user_email);
        return !is_null($customer->getId()) ? $customer : false;
    }


    /**
     * The setter function for the Attributes Parameter
     */
    public function setAttrs($attrs)
    {
        $this->attrs = $attrs;
        return $this;
    }

    /**
     * The setter function for the Attributes Parameter
     */
    public function setFlattenedAttrs($flattenedattrs)
    {
        $this->flattenedattrs = $flattenedattrs;
        return $this;
    }

    /**
     * Setter for the User Email Parameter
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
        return $this;
    }
}
