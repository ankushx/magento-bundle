<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Adminhtml\Attrsettings;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\SocialMessages;
use MiniOrange\MagentoSocialLogin\Controller\Actions\BaseAdminAction;
use MiniOrange\MagentoSocialLogin\Helper\SocialUtility;
use Psr\Log\LoggerInterface;
use MiniOrange\MagentoSocialLogin\Helper\Curl;


/**
 * This class handles the action for endpoint: mosocial/attrsettings/Index
 * Extends the \Magento\Backend\App\Action for Admin Actions which
 * inturn extends the \Magento\Framework\App\Action\Action class necessary
 * for each Controller class
 */
class Index extends BaseAdminAction implements HttpPostActionInterface, HttpGetActionInterface
{

    private $adminRoleModel;
    private $userGroupModel;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SocialUtility $socialUtility,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        \Magento\Authorization\Model\ResourceModel\Role\Collection $adminRoleModel,
        Collection $userGroupModel
    ) {
        //You can use dependency injection to get any class this observer may need.
        parent::__construct($context, $resultPageFactory, $socialUtility, $messageManager, $logger);
        $this->adminRoleModel = $adminRoleModel;
        $this->userGroupModel = $userGroupModel;
    }

    /**
     * The first function to be called when a Controller class is invoked.
     * Usually, has all our controller logic. Returns a view/page/template
     * to be shown to the users.
     *
     * This function gets and prepares all our SP config data from the
     * database. It's called when you visis the moasaml/attrsettings/Index
     * URL. It prepares all the values required on the SP setting
     * page in the backend and returns the block to be displayed.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams(); //get params
            
            if ($this->isFormOptionBeingSaved($params)) { // check if form options are being saved
                $this->checkIfRequiredFieldsEmpty(['sociallogin_am_username'=>$params,'sociallogin_am_email'=>$params]);
                $this->processValuesAndSaveData($params);
                $this->socialUtility->flushCache();
                $this->messageManager->addSuccessMessage(SocialMessages::SETTINGS_SAVED);
                $this->socialUtility->reinitConfig();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->debug($e->getMessage());
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(SocialConstants::MODULE_TITLE));
        return $resultPage;
    }


    /**
     * Process Values being submitted and save data in the database.
     * @param $param
     */
    private function processValuesAndSaveData($params)
    {
          $this->socialUtility->setStoreConfig(SocialConstants::MAP_USERNAME, $params['sociallogin_am_username']);
          $this->socialUtility->setStoreConfig(SocialConstants::MAP_EMAIL, $params['sociallogin_am_email']);

        if (isset($params['dont_create_user_if_role_not_mapped'])) {
            $this->socialUtility->setStoreConfig(SocialConstants::CREATEIFNOTMAP, $params['dont_create_user_if_role_not_mapped']);
        }

        if (isset($params['dont_allow_unlisted_user_role'])) {
            $this->socialUtility->setStoreConfig(SocialConstants::UNLISTED_ROLE, $params['dont_allow_unlisted_user_role']);
        }
    }

    /**
     * Is the user allowed to view the Attribute Mapping settings.
     * This is based on the ACL set by the admin in the backend.
     * Works in conjugation with acl.xml
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(SocialConstants::MODULE_DIR.SocialConstants::MODULE_ATTR);
    }
}
