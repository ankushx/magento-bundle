<?php

namespace MiniOrange\TwoFA\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Customer;
use MiniOrange\TwoFA\Helper\TwoFAConstants;
use MiniOrange\TwoFA\Helper\TwoFAUtility;
use MiniOrange\TwoFA\Helper\MiniOrangeUser;
use MiniOrange\TwoFA\Helper\Curl;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var Validator
     */
    private $formKeyValidator;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    protected $TwoFAUtility;
    protected $customerModel;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param Validator $formKeyValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    private $cookieManager;

    private $url;
    private $moduleManager;
    protected $resultFactory;
    protected $response;
    public $customerSession;
    protected $storeManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        ?Validator $formKeyValidator,
        TwoFAUtility $TwoFAUtility,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\UrlInterface $url,
        CustomerRepository $customerRepository,
        Customer $customerModel,
    ) {
        $this->customerSession = $customerSession;
        $this->TwoFAUtility = $TwoFAUtility;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->moduleManager = $moduleManager;
        $this->url = $url;
        $this->resultFactory = $resultFactory;
        $this->customerModel = $customerModel;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $regionDataFactory,
            $addressDataFactory,
            $customerDataFactory,
            $customerUrl,
            $registration,
            $escaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $customerRepository,
            $formKeyValidator ?:
            ObjectManager::getInstance()->get(Validator::class)
        );
    }

    public function execute()
    {
        //After registration of customer ,flow start's from here.
        $params = $this->getRequest()->getParams();
        $current_website_id = $this->storeManager->getStore()->getWebsiteId();

        $resultRedirect = $this->resultRedirectFactory->create();
        //check if 2fa at customer registration is on or off
        $customer_registration_twofa = $this->TwoFAUtility->getStoreConfig(
            TwoFAConstants::REGISTER_CHECKBOX
        );
        //Forcefully assign "general" role. If you want to add any code related to customer role during registration add here.
        $customer_role_name = "General";
        //check if inline is on or off
        //check active methods
        $active_method = $this->TwoFAUtility->getStoreConfig(TwoFAConstants::REGISTER_OTP_TYPE);
        $active_method_status = $active_method == "[]" || $active_method == null ? false : true;

        if ($customer_registration_twofa && $active_method_status) {

            $count = $this->TwoFAUtility->getStoreConfig(TwoFAConstants::CUSTOMER_COUNT);
            
            if ($count >= 10) {

                $subject = 'TwoFA user limit has been exceeded';
                $message = 'Trying to create frontend user using ' . $params["email"] . ' email';
                $isUserLimitEmailSent = $this->TwoFAUtility->getStoreConfig(TwoFAConstants::USER_LIMIT_EMAIL_SENT);
                $this->TwoFAUtility->flushCache();
                if ($isUserLimitEmailSent == null) {

                    $timeStamp = $this->TwoFAUtility->getStoreConfig(TwoFAConstants::TIME_STAMP);
                    if($timeStamp == null){
                        $timeStamp = time();
                        $this->TwoFAUtility->setStoreConfig(TwoFAConstants::TIME_STAMP,$timeStamp);
                        $this->TwoFAUtility->flushCache();
                    }

                    $domain = $this->TwoFAUtility->getBaseUrl();
                    $environmentName = $this->TwoFAUtility->getEdition();
                    $environmentVersion = $this->TwoFAUtility->getProductVersion();
                    $miniorangeAccountEmail= $this->TwoFAUtility->getCustomerEmail();
                    $frontendMethod = '';
                    $backendMethod = '';
                    $freeInstalledDate = $this->TwoFAUtility->getCurrentDate();

                    
                    Curl::submit_to_magento_team($timeStamp,
                    '',
                    $domain,
                    $miniorangeAccountEmail,
                    '',
                    $environmentName,
                    $environmentVersion,
                    $freeInstalledDate,
                    $backendMethod,
                    $frontendMethod,
                    '',
                    'Yes');

                    $this->TwoFAUtility->setStoreConfig(TwoFAConstants::USER_LIMIT_EMAIL_SENT, 1);
                }
                $this->TwoFAUtility->log_debug("CreatePost.php : execute: your user limit has been exceeded ");
                $this->messageManager->addError(__('Your user limit has been exceeded. Please contact Email: magentosupport@xecurify.com'));
                $this->TwoFAUtility->log_debug(
                    "Execute CreatePost: Default Account Creation flow"
                );
                parent::execute();
                $resultRedirect->setPath("customer/account");
                return $resultRedirect;

            }

            $this->customerModel->setWebsiteId($current_website_id);
            $customer = $this->customerModel->loadByEmail($params["email"]);

            //check if customer id is null or not
            if (!is_null($customer->getId())) {
                $resultRedirect = $this->resultRedirectFactory->create();
                //parent::execute() function will continue event without loading further code
                parent::execute();
                $resultRedirect->setPath("customer/account");
                return $resultRedirect;
            }
            // Initiate MFA flow

            $current_username = $params["email"];
            $register_page_parameter = json_encode($params, true);
            $this->TwoFAUtility->setSessionValue(
                "mo_customer_page_parameters",
                $register_page_parameter
            );
            $this->TwoFAUtility->setSessionValue(
                "mousername",
                $params["email"]
            );
            $this->TwoFAUtility->setSessionValue(
                "mocreate_customer_register",
                1
            );
            // Setting up in the cookie for printing

            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $publicCookieMetadata->setDurationOneYear();
            $publicCookieMetadata->setPath("/");
            $publicCookieMetadata->setHttpOnly(false);
            $this->cookieManager->setPublicCookie(
                "mousername",
                $current_username,
                $publicCookieMetadata
            );

            $redirectionUrl = "";

            $this->TwoFAUtility->log_debug(
                "Execute CreatePost: Customer going through Inline in createpost"
            );

            $number_of_activeMethod = $this->TwoFAUtility->getStoreConfig(
                TwoFAConstants::NUMBER_OF_CUSTOMER_METHOD_AT_REGISTRATION
            );
            //check for number of active method. If only one method is active then redirect to that method without showing method dropdown.
            if ($number_of_activeMethod == 1) {
                $customer_active_method = $this->TwoFAUtility->getStoreConfig(
                    TwoFAConstants::REGISTER_OTP_TYPE
                );
                
                $customer_active_method = trim($customer_active_method, '[""]');
                $params = [
                    "mopostoption" => "method",
                    "miniorangetfa_method" => $customer_active_method,
                    "inline_one_method" => "1",
                ];
                $resultRedirect->setPath("motwofa/mocustomer", $params);
            } elseif ($number_of_activeMethod > 1) {
                //If more than one methods are present then show dropdown to choose method
                $params = [
                    "mooption" => "invokeInline",
                    "step" => "ChooseMFAMethod",
                ];
                $resultRedirect->setPath("motwofa/mocustomer/index", $params);
            }

            return $resultRedirect;
        } else {
            //customer creation by default method
            $this->TwoFAUtility->log_debug(
                "Execute CreatePost: Default Account Creation flow"
            );
            parent::execute();
            $resultRedirect->setPath("customer/account");
            return $resultRedirect;
        }
    }
}