<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\Exception\NotRegisteredException;
use MiniOrange\MagentoSocialLogin\Helper\Exception\RequiredFieldsException;
use MiniOrange\MagentoSocialLogin\Helper\Exception\SupportQueryRequiredFieldsException;

/**
 * The base action class that is inherited by each of the admin action
 * class. It consists of certain common functions that needs to
 * be inherited by each of the action class. Extends the
 * \Magento\Backend\App\Action class which is usually
 * extended by Admin Controller class.
 *
 * \Magento\Backend\App\Action is extended instead of
 * \Magento\Framework\App\Action\Action so that we can check Access Level
 * Permissions before calling the execute fucntion
 */
abstract class BaseAdminAction extends \Magento\Backend\App\Action
{

    protected $socialUtility;
    protected $context;
    protected $resultPageFactory;
    protected $messageManager;
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MiniOrange\MagentoSocialLogin\Helper\SocialUtility $socialUtility,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->socialUtility = $socialUtility;
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        parent::__construct($context);
    }


    /**
     * Check if form is being saved in the backend other just
     * show the page. Checks if the request parameter has
     * an option key. All our forms need to have a hidden option
     * key.
     *
     * @param params
     * @return bool
     */
    protected function isFormOptionBeingSaved($params)
    {
        return isset($params['option']);
    }

    /**
     * This function checks if the required fields passed to
     * this function are empty or not. If empty throw an exception.
     *
     * @param $array
     * @throws RequiredFieldsException
     */
    protected function checkIfRequiredFieldsEmpty($array)
    {
        
        foreach ($array as $key => $value) {
            if ((is_array($value) && ( !isset($value[$key]) || $this->socialUtility->isBlank($value[$key])) )
                    || $this->socialUtility->isBlank($value)
              ) {
                throw new RequiredFieldsException();
            }
        }
    }


    /**
     * Check if support query forms are empty. If empty throw
     * an exception. This is an extension of the requiredFields
     * function.
     *
     * @param $array
     * @throws SupportQueryRequiredFieldsException
     */
    public function checkIfSupportQueryFieldsEmpty($array)
    {
        
        try {
            $this->checkIfRequiredFieldsEmpty($array);
        } catch (RequiredFieldsException $e) {
            throw new SupportQueryRequiredFieldsException();
        }
    }
    
    /** This function is abstract that needs to be implemented by each Action Class */
    abstract public function execute();


    /* ===================================================================================================
                THE FUNCTIONS BELOW ARE FREE PLUGIN SPECIFIC AND DIFFER IN THE PREMIUM VERSION
       ===================================================================================================
     */

    /**
     * This function checks if the user has registered himself
     * and throws an Exception if not registered. Checks the
     * if the admin key and api key are saved in the database.
     *
     * @throws NotRegisteredException
     * @todo remove the comments
     */
    protected function checkIfValidPlugin()
    {
        
        if (!$this->socialUtility->micr()) {
            throw new NotRegisteredException;
        }
    }
}
