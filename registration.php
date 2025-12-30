<?php
/**
 * MiniOrange Security Suite - Combined Module Registration
 * 
 * Registers all security modules:
 * - Admin Logs: Track admin user activity
 * - Two-Factor Authentication: Enhanced login security
 * - Brute Force Protection: Prevent brute force attacks
 * - IP Restriction: Control access by IP address
 */

use Magento\Framework\Component\ComponentRegistrar;

// Register Admin Logs Module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MiniOrange_AdminLogs',
    __DIR__ . '/adminlogs'
);

// Register Two-Factor Authentication Module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MiniOrange_TwoFA',
    __DIR__ . '/miniorange-2fa'
);

// Register Brute Force Protection Module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MiniOrange_BruteForceProtection',
    __DIR__ . '/miniorange-magento-brute-force-protection'
);

// Register IP Restriction Module
ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'MiniOrange_IpRestriction',
    __DIR__ . '/miniorange-magento-ip-restriction'
);
