<?php
namespace MiniOrange\SecuritySuite\Plugin;

class MenuBuilderPlugin
{
    protected $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function afterGetResult(\Magento\Backend\Model\Menu\Builder $subject, $menu)
    {
        // Only proceed if our Security Suite is enabled
        if (!$this->moduleManager->isEnabled('MiniOrange_SecuritySuite')) {
            return $menu;
        }

        // Hide MiniOrange_TwoFA menu items but keep functionality
        $menusToHide = [
            'MiniOrange_TwoFA::TwoFA',              
            'MiniOrange_BruteForce::BruteForce',
            'MiniOrange_AdminActivity::AdminActivity',
            'MiniOrange_RateLimiting::RateLimiting',
            'MiniOrange_SSO::SSO',
            'MiniOrange_AzureSSO::OAuth',
            'MiniOrange_MagentoSocialLogin::SocialLogin'
        ];

        foreach ($menusToHide as $menuId) {
            if ($menu->get($menuId)) {
                $menu->remove($menuId);
            }
        }

        return $menu;
    }
}
