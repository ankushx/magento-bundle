<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Adminhtml\SocialLoginsettings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use MiniOrange\MagentoSocialLogin\Helper\MagentoSocialLogin\SAML2Utilities;
use MiniOrange\MagentoSocialLogin\Controller\Actions\BaseAdminAction;
use MiniOrange\MagentoSocialLogin\Helper\Curl;


/**
 * This class handles the action for endpoint: mosocial/socialloginsettings/Index
 * Extends the \Magento\Backend\App\Action for Admin Actions which
 * inturn extends the \Magento\Framework\App\Action\Action class necessary
 * for each Controller class
 */
 
class Index extends BaseAdminAction implements HttpPostActionInterface, HttpGetActionInterface
{
     /**
      * The first function to be called when a Controller class is invoked.
      * Usually, has all our controller logic. Returns a view/page/template
      * to be shown to the users.
      *
      * This function gets and prepares all our Social Login config data from the
      * database. It's called when you visis the mosocial/socialloginsettings/Index
      * URL. It prepares all the values required on the SP setting
      * page in the backend and returns the block to be displayed.
      *
      * @return \Magento\Framework\View\Result\Page
      */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams(); //get params
           
            // check if form options are being saved
            if ($this->isFormOptionBeingSaved($params)) {
                
                 // check if required values have been submitted
                $this->processValuesAndSaveData($params);
                $this->socialUtility->flushCache();
                $this->messageManager->addSuccessMessage(SocialMessages::SETTINGS_SAVED);
                $this->socialUtility->reinitConfig();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->debug($e->getMessage());
        }
        
        // generate page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(SocialConstants::MODULE_TITLE));
        return $resultPage;
    }
     
     /**
      * Process Values being submitted and save data in the database.
      */
    private function processValuesAndSaveData($params)
    {
        if ($params['option'] == 'google_configurations') {
            if (isset($params['enable_google']) && $params['enable_google'] == 'on') {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_GOOGLE, 1);
            } else {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_GOOGLE, 0);
            }
            $this->socialUtility->setStoreConfig(SocialConstants::GOOGLE_CLIENT_ID, trim($params['google_client_id']));
            $this->socialUtility->setStoreConfig(SocialConstants::GOOGLE_CLIENT_SECRET, trim($params['google_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::GOOGLE_SCOPE, 'email profile');
            $this->socialUtility->setStoreConfig(SocialConstants::APP_NAME, 'Google');

            $values = array('google_configurations','email profile',trim($params['google_client_id']), trim($params['google_client_secret']));

            $this->socialUtility->setStoreConfig(SocialConstants::SET_DATA, json_encode($values));
        } elseif ($params['option'] == 'facebook_configurations') {
            if (isset($params['enable_facebook']) && $params['enable_facebook'] == 'on') {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_FACEBOOK, 1);
            } else {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_FACEBOOK, 0);
            }
            $this->socialUtility->setStoreConfig(SocialConstants::FACEBOOK_CLIENT_ID, trim($params['facebook_client_id']));
            $this->socialUtility->setStoreConfig(SocialConstants::FACEBOOK_CLIENT_SECRET, trim($params['facebook_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::FACEBOOK_SCOPE, 'email public_profile');
            $this->socialUtility->setStoreConfig(SocialConstants::APP_NAME, 'Facebook');

            $values = array('facebook_configurations','email public_profile',trim($params['facebook_client_id']), trim($params['facebook_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::SET_DATA, json_encode($values));

        } elseif ($params['option'] == 'linkedin_configurations') {
            if (isset($params['enable_linkedin']) && $params['enable_linkedin'] == 'on') {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_LINKEDIN, 1);
            } else {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_LINKEDIN, 0);
            }
            $this->socialUtility->setStoreConfig(SocialConstants::LINKEDIN_CLIENT_ID, trim($params['linkedin_client_id']));
            $this->socialUtility->setStoreConfig(SocialConstants::LINKEDIN_CLIENT_SECRET, trim($params['linkedin_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::LINKEDIN_SCOPE, 'r_liteprofile r_emailaddress w_member_social');
            $this->socialUtility->setStoreConfig(SocialConstants::APP_NAME, 'LinkedIN');

            $values = array('linkedin_configurations','r_liteprofile r_emailaddress w_member_social',trim($params['linkedin_client_id']), trim($params['linkedin_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::SET_DATA, json_encode($values));
        } elseif ($params['option'] == 'twitter_configurations') {

            if (isset($params['enable_twitter']) && $params['enable_twitter'] == 'on') {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_TWITTER, 1);
            } else {
                $this->socialUtility->setStoreConfig(SocialConstants::ENABLE_TWITTER, 0);
            }
            $this->socialUtility->setStoreConfig(SocialConstants::TWITTER_CLIENT_ID, trim($params['twitter_client_id']));
            $this->socialUtility->setStoreConfig(SocialConstants::TWITTER_CLIENT_SECRET, trim($params['twitter_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::TWITTER_SCOPE, 'profile');
            $this->socialUtility->setStoreConfig(SocialConstants::APP_NAME, 'Twitter');
            $values = array('twitter_configurations','profile',trim($params['twitter_client_id']),trim($params['twitter_client_secret']));
            $this->socialUtility->setStoreConfig(SocialConstants::SET_DATA, json_encode($values));
        }

        $currentAdminUser =  $this->socialUtility->getCurrentAdminUser()->getData();  
        $userEmail = $currentAdminUser['email'];

     $this->socialUtility->setStoreConfig(SocialConstants::ADMINEMAIL,$userEmail);
    }
    
     /**
      * Is the user allowed to view the Service Provider settings.
      * This is based on the ACL set by the admin in the backend.
      * Works in conjugation with acl.xml
      *
      * @return bool
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(SocialConstants::MODULE_DIR.SocialConstants::MODULE_SOCIALSETTINGS);
    }
}
