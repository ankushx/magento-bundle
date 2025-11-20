<?php
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Vendor_ModuleOne',
    __DIR__ . '/ModuleOne'
);

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Vendor_ModuleTwo',
    __DIR__ . '/ModuleTwo'
);