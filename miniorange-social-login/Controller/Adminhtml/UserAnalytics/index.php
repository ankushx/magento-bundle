<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Adminhtml\UserAnalytics;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use MiniOrange\MagentoSocialLogin\Helper\MagentoSocialLogin\SAML2Utilities;
use MiniOrange\MagentoSocialLogin\Controller\Actions\BaseAdminAction;
use Magento\Framework\App\Action\HttpPostActionInterface;
use MiniOrange\MagentoSocialLogin\Helper\Curl;


/**
 * This class handles the action for endpoint: mosocial/UserAnalytics/Index
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
     * This function gets and prepares all our SP config data from the
     * database. It's called when you visis the moasaml/UserAnalytics/Index
     * URL. It prepares all the values required on the SP setting
     * page in the backend and returns the block to be displayed.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {   
        try {
            $params = $this->getRequest()->getParams(); //get params

            // check if form options are being saved
            if ($this->isFormOptionBeingSaved($params)) {
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
        $mo_social_login_show_customer_link = 1;
        $this->socialUtility->setStoreConfig(SocialConstants::SHOW_CUSTOMER_LINK, $mo_social_login_show_customer_link);
    }


    /**
     * Is the user allowed to view the Sign in Settings.
     * This is based on the ACL set by the admin in the backend.
     * Works in conjugation with acl.xml
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(SocialConstants::MODULE_DIR.SocialConstants::MODULE_SIGNIN);
    }
}
