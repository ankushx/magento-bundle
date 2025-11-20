<?php
 
 /**
  * The code below is used to register the
  * Social Login extension/component with the Mangeto
  * core Module. It specifies the root directory
  * of the plugin.
  */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'MiniOrange_MagentoSocialLogin',
    __DIR__
);
