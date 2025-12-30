<?php

namespace MiniOrange\IpRestriction\Controller\Error;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\State;
use MiniOrange\IpRestriction\Helper\IpRestrictionUtility;
use MiniOrange\IpRestriction\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends Action
{
    protected $resultPageFactory;
    protected $ipRestrictionUtility;
    protected $dataHelper;
    protected $cache;
    protected $layoutFactory;
    protected $assetRepo;
    protected $appState;
    protected $directoryList;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        IpRestrictionUtility $ipRestrictionUtility,
        Data $dataHelper,
        CacheInterface $cache,
        LayoutFactory $layoutFactory,
        Repository $assetRepo,
        State $appState,
        DirectoryList $directoryList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ipRestrictionUtility = $ipRestrictionUtility;
        $this->dataHelper = $dataHelper;
        $this->cache = $cache;
        $this->layoutFactory = $layoutFactory;
        $this->assetRepo = $assetRepo;
        $this->appState = $appState;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    public function execute()
    {
        // Set HTTP 403 Forbidden status (more appropriate for IP/country blocking)
        $response = $this->getResponse();
        if ($response instanceof Response) {
            $response->setHttpResponseCode(403);
        }
        
        // Get IP address
        $ipAddress = $this->ipRestrictionUtility->getRealClientIp($this->getRequest());
        
        // Set area code to 'adminhtml' to ensure template can be found
        try {
            $this->appState->setAreaCode('adminhtml');
        } catch (\Exception $e) {
            // Area code already set, continue
        }
        
        // Create layout and block to render template
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            \Magento\Framework\View\Element\Template::class,
            'ip_restriction_error',
            [
                'data' => [
                    'template' => 'MiniOrange_IpRestriction::restrict.phtml',
                    'ip_address' => $ipAddress
                ]
            ]
        );
        
        // Render the block to HTML
        $html = $block->toHtml();
        
        // Load CSS from file
        $css = $this->getErrorPageCss();
        
        // Wrap in standalone HTML structure
        $fullHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    ' . $css . '
</head>
<body>
    ' . $html . '
</body>
</html>';
        
        // Return raw HTML response
        $response->setBody($fullHtml);
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8', true);
        return $response;
    }

    /**
     * Get CSS content from file
     * 
     * @return string
     */
    private function getErrorPageCss()
    {
        try {
            $rootPath = $this->directoryList->getRoot();
            $cssPath = $rootPath . '/app/code/MiniOrange/IpRestriction/view/adminhtml/web/css/restrict.css';
            
            if (file_exists($cssPath) && is_readable($cssPath)) {
                $cssContent = file_get_contents($cssPath);
                return '<style>' . $cssContent . '</style>';
            }
        } catch (\Exception $e) {
            // Silently fail if CSS cannot be read
        }
        return '';
    }
}


