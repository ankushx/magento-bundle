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

        // Hide child module menu items but keep functionality (accessed via Security Suite)
        $menusToHide = [
            'MiniOrange_TwoFA::TwoFA',
            'MiniOrange_IpRestriction::IpRestriction',
            'MiniOrange_BruteForceProtection::BruteForceProtection',
            'MiniOrange_AdminLogs::AdminLogs'
        ];

        foreach ($menusToHide as $menuId) {
            if ($menu->get($menuId)) {
                $menu->remove($menuId);
            }
        }

        return $menu;
    }
}
