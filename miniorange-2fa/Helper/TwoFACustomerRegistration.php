<?php
namespace MiniOrange\TwoFA\Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;

class TwoFACustomerRegistration {

    protected $twofautility;
    protected $context;
    protected $customerFactory;
    protected $storeManager;
    public function __construct(
        Context $context,
        \MiniOrange\TwoFA\Helper\TwoFAUtility $twofautility,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->context=$context;
        $this->twofautility = $twofautility;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
    }

    public function execute(){

    }

    public function createNewCustomerAtRegistration(){
        $groupId = 1;
        $customer_registration_parameter= json_decode( $this->twofautility->getSessionValue( 'mo_customer_page_parameters'),true);
     $current_username=$customer_registration_parameter['email'];
         $firstname= $customer_registration_parameter['firstname'];
         $lastname= $customer_registration_parameter['lastname'];
         $password= $customer_registration_parameter['password'];
         $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
         $store = $this->storeManager->getStore();
        // $storeId = $store->getStoreId();
         $customer = $this->customerFactory->create()
             ->setWebsiteId($websiteId)
             ->setStore($store)
             ->setEmail($current_username)
             ->setFirstname($firstname)
             ->setLastname($lastname)
             ->setPassword($password)
             ->setGroupId($groupId)
             ->save();
    }
}