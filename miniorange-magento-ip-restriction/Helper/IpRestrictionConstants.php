<?php

namespace MiniOrange\IpRestriction\Helper;

/**
 * IP Restriction Constants
 * Contains all constants used throughout the extension
 */
class IpRestrictionConstants
{
    // Module Information
    const MODULE_NAME = 'IpRestriction';
    const MODULE_DIR = 'MiniOrange_IpRestriction::';
    const MODULE_TITLE = 'miniOrange IP Restriction';
    const SECURITY_SUITE_NAME = 'Security Suite';
    const MODULE_BASE = 'ipratelimit';
    const MODULE_VERSION = 'v1.0.0';

    // Config Path Prefix
    const CONFIG_PATH_PREFIX = 'miniorange/SecuritySuite/';

    // IP Denylist Config Paths
    const ADMIN_IP_BLACKLIST = 'ip_admin_blacklist';
    const IP_BLACKLIST_ENABLED = 'ip_blacklist_enabled';

    // Country Restriction Config Paths
    const COUNTRY_RESTRICTIONS_ENABLED = 'country_restrictions_enabled';
    const COUNTRY_DENYLIST = 'country_denylist';

    // GeoIP2 Config Paths
    const GEOIP2_LICENSE_KEY = 'geoip2/license_key';
    const GEOIP2_AUTO_UPDATE_ENABLED = 'geoip2/auto_update_enabled';

    // GeoIP2 Download Constants
    const GEOIP2_MAX_DOWNLOAD_SIZE = 50 * 1024 * 1024; // 50MB
    const GEOIP2_MIN_DOWNLOAD_SIZE = 1000000; // 1MB
    const GEOIP2_DOWNLOAD_TIMEOUT = 300; // 5 minutes
    const MAXMIND_DOWNLOAD_URL = 'https://download.maxmind.com/app/geoip_download';
    const MAXMIND_EDITION_ID = 'GeoLite2-Country';

    // Context Constants
    const CONTEXT_ADMIN = 'admin';
    const CONTEXT_FRONTEND = 'frontend';

    // Limit Type Constants
    const LIMIT_TYPE_IP = 'ip';
    const LIMIT_TYPE_COUNTRY = 'country';

    // Limit Values
    const MAX_IP_LIMIT = 5;
    const MAX_COUNTRY_LIMIT = 2;

    // File Paths
    const GEOIP_DIRECTORY = 'var/geoip';
    const ADMIN_PATH_PREFIX = '/admin';

    // Default Values
    const DEFAULT_UNKNOWN_IP = 'UNKNOWN';
    const DEFAULT_GEOIP_DATABASE_PATH = 'var/geoip2/GeoLite2-Country.mmdb';

    // Tracking Constants
    const PLUGIN_PORTAL_HOSTNAME = "https://magento.shanekatear.in/plugin-portal";
    const TIME_STAMP = 'time_stamp';
    const DATA_ADDED = 'data_added';

    // Support/Contact API Constants
    const HOSTNAME = "https://login.xecurify.com";
    const AREA_OF_INTEREST = 'Magento IP Restriction Plugin';
    const DEFAULT_CUSTOMER_KEY = "16555";
    const DEFAULT_API_KEY = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
}

