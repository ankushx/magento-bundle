<?php
namespace MiniOrange\SecuritySuite\Controller\Adminhtml\Redirect;

class Index extends \Magento\Backend\App\Action
{
    protected $moduleManager;
    protected $backendUrl;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        parent::__construct($context);
        $this->moduleManager = $moduleManager;
        $this->backendUrl = $backendUrl;
    }

    public function execute()
    {
        // Get the module parameter from the request
        $module = $this->getRequest()->getParam('module');
        
        switch ($module) {
            case 'twofa':
                return $this->redirectToTwoFA();
            case 'bruteforce':
                return $this->redirectToBruteForce();
            case 'ratelimiting':
                return $this->redirectToRateLimiting();
            case 'adminactivity':
                return $this->redirectToAdminActivity();
            case 'sso':
                return $this->redirectToSSO();
            case 'azure-sso':
                return $this->redirectToAzureSSO();
            case 'social-login':
                return $this->redirectToSocialLogin();
            default:
                $this->messageManager->addError(__('Invalid module specified.'));
                return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToSSO()
    {
        // Check if MiniOrange OAuth SSO module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_SSO')) {
            $miniorangeUrl = $this->backendUrl->getUrl('mooauth/account/index');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('SSO module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToSocialLogin()
    {
        // Check if MiniOrange Social Login module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_MagentoSocialLogin')) {
            $miniorangeUrl = $this->backendUrl->getUrl('mosocial/upgrade');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('Social Login module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToAzureSSO()
    {
        // Check if MiniOrange Azure SSO module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_AzureSSO')) {
            $miniorangeUrl = $this->backendUrl->getUrl('mooauth/account/index');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('Azure SSO module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToTwoFA()
    {
        // Check if MiniOrange TwoFA module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_TwoFA')) {
            $miniorangeUrl = $this->backendUrl->getUrl('motwofa/account/index');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('Two-Factor Authentication module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToBruteForce()
    {
        // Check if MiniOrange OAuth module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_BruteForce')) {
            $miniorangeUrl = $this->backendUrl->getUrl('mobruteforce/bruteforcesettings');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('OAuth SSO module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToRateLimiting()
    {
        // Check if MiniOrange Azure SSO module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_RateLimiting')) {
            $miniorangeUrl = $this->backendUrl->getUrl('moratelimiting/ratelimitingsettings');
            return $this->_redirect($miniorangeUrl);
        } else {
            $this->messageManager->addError(__('Azure SSO module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function redirectToAdminActivity()
    {
        // Check if MiniOrange Admin Activity module is installed and enabled
        if ($this->moduleManager->isEnabled('MiniOrange_AdminActivity')) {
            $miniorangeUrl = $this->backendUrl->getUrl('moadminactivity/adminactivitysettings');
            return $this->_redirect($miniorangeUrl);
        }else{
            $this->messageManager->addError(__('Admin Activity module is not installed.'));
            return $this->_redirect('adminhtml/dashboard/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MiniOrange_SecuritySuite::SecuritySuite');
    }
}
