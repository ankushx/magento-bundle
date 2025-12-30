<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'MiniOrange_SecuritySuite',
    __DIR__
);

// Conditionally register child modules if they exist
$childModules = [
    'TwoFactorAuth' => __DIR__ . '/../TwoFactorAuth/registration.php',
    'IpRestriction' => __DIR__ . '/../IpRestriction/registration.php',
    'BruteForceProtection' => __DIR__ . '/../BruteForceProtection/registration.php',
    'AdminLogs' => __DIR__ . '/../AdminLogs/registration.php'
];

foreach ($childModules as $moduleName => $registrationPath) {
    if (file_exists($registrationPath)) {
        require_once $registrationPath;
        $fullModuleName = 'MiniOrange_' . $moduleName;
        \Magento\Framework\Component\ComponentRegistrar::register(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $fullModuleName,
            dirname($registrationPath)
        );
    }
}
