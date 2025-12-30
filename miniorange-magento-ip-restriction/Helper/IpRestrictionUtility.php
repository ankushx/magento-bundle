<?php

namespace MiniOrange\IpRestriction\Helper;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use MiniOrange\IpRestriction\Helper\IpRestrictionConstants;

/**
 * IP Restriction Utility Helper
 * Contains methods for IP restriction logic and GeoIP functionality
 */
class IpRestrictionUtility
{
    protected $logger;
    protected $remoteAddress;
    protected $dataHelper;
    protected $directoryList;

    public function __construct(
        LoggerInterface $logger,
        RemoteAddress $remoteAddress,
        Data $dataHelper,
        DirectoryList $directoryList
    ) {
        $this->logger = $logger;
        $this->remoteAddress = $remoteAddress;
        $this->dataHelper = $dataHelper;
        $this->directoryList = $directoryList;
    }

    /**
     * Get the real client IP address
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getRealClientIp(RequestInterface $request)
    {
        // First try Magento's built-in remote address helper
        $realIp = $request->getClientIp();
        
        // Fallback to request headers
        $headers = $request->getServer();
        
        // Check X-Forwarded-For header (most common)
        if (isset($headers['HTTP_X_FORWARDED_FOR'])) {
            $forwardedIps = explode(',', $headers['HTTP_X_FORWARDED_FOR']);
            $realIp = trim($forwardedIps[0]);
            if ($this->isValidIp($realIp)) {
                return $realIp;
            }
        }

        // Check CF-Connecting-IP (Cloudflare)
        if (isset($headers['HTTP_CF_CONNECTING_IP'])) {
            $realIp = trim($headers['HTTP_CF_CONNECTING_IP']);
            if ($this->isValidIp($realIp)) {
                return $realIp;
            }
        }

        // Last resort - use request's getClientIp()
        return $request->getClientIp();
    }

    /**
     * Validate if an IP address is valid
     *
     * @param string $ip
     * @return bool
     */
    private function isValidIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ||
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false ||
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }
    
    /**
     * Check if IP is in admin denylist
     * 
     * @param string $ipAddress
     * @param int|null $storeId 
     * @param string $context 
     * @return bool
     */
    public function isIpInDenylist($ipAddress, $storeId = null, $context = IpRestrictionConstants::CONTEXT_FRONTEND)
    {
        try {
            // Admin denylist always uses default scope
            $ipString = $this->dataHelper->getStoreConfig(
                IpRestrictionConstants::ADMIN_IP_BLACKLIST,
                'default',
                0
            );
            
            if (empty($ipString)) {
                return false;
            }
            
            // Parse semicolon-separated IPs
            $ips = explode(';', $ipString);
            
            $maxIps = IpRestrictionConstants::MAX_IP_LIMIT;
            $validIpsChecked = 0;
            
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (empty($ip)) {
                    continue;
                }
                
                // Validate IP format 
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    $this->logger->debug("IpRestriction: Skipping invalid IP format in denylist: {$ip}");
                    continue;
                }

                if ($validIpsChecked >= $maxIps) {
                    $this->logger->warning("IpRestriction: IP limit ({$maxIps}) exceeded. Only checking first {$maxIps} valid IPs from denylist.");
                    break;
                }
                
                $validIpsChecked++;
                
                // Check if IP matches
                if ($this->ipMatches($ipAddress, $ip)) {
                    $this->logger->info("IpRestriction: IP {$ipAddress} matches denylist pattern '{$ip}'");
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->error("IpRestriction: Error checking IP denylist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if IP matches pattern 
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatches($ip, $pattern)
    {
        $pattern = trim($pattern);
        
        // Reject IP ranges
        if (strpos($pattern, ' - ') !== false || (strpos($pattern, '-') !== false && strpos($pattern, '/') === false)) {
            $this->logger->warning("IpRestriction: IP range pattern rejected (not supported in free version): {$pattern}");
            return false;
        }

        // Reject CIDR notation
        if (strpos($pattern, '/') !== false) {
            $this->logger->warning("IpRestriction: CIDR pattern rejected (not supported in free version): {$pattern}");
            return false;
        }

        // Reject wildcard patterns
        if (strpos($pattern, '*') !== false) {
            $this->logger->warning("IpRestriction: Wildcard pattern rejected (not supported in free version): {$pattern}");
            return false;
        }

        if (!filter_var($pattern, FILTER_VALIDATE_IP)) {
            $this->logger->warning("IpRestriction: Invalid IP pattern format: {$pattern}");
            return false;
        }

        // Exact match only
        return $ip === $pattern;
    }

    /**
     * Check if IP matches wildcard pattern (e.g., 192.168.*.*, 10.0.*)
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatchesWildcard($ip, $pattern)
    {
        // Only support IPv4 wildcards for now
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Extract IP and pattern parts
        $ipParts = explode('.', $ip);
        $patternParts = explode('.', $pattern);
        
        // Both must have exactly 4 octets
        if (count($patternParts) !== 4 || count($ipParts) !== 4) {
            return false;
        }

        // Check each octet
        for ($i = 0; $i < 4; $i++) {
            $patternPart = trim($patternParts[$i]);
            $ipPart = (int)$ipParts[$i];
            
            // If pattern part is wildcard, match any value (0-255)
            if ($patternPart === '*') {
                continue;
            }
            
            // Pattern part must be numeric and match exactly
            if (!is_numeric($patternPart)) {
                return false;
            }
            
            $patternValue = (int)$patternPart;
            if ($patternValue < 0 || $patternValue > 255) {
                return false;
            }
            
            // Exact match required for non-wildcard parts
            if ($ipPart !== $patternValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if IP matches range pattern (e.g., 10.0.0.5 - 10.0.0.50)
     * Supports both exact IP ranges and wildcard ranges
     * @param string $ip
     * @param string $pattern
     * @return bool
     */
    private function ipMatchesRange($ip, $pattern)
    {
        // Only support IPv4 ranges 
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        // Parse range 
        $rangeParts = preg_split('/\s*-\s*/', $pattern, 2);
        if (count($rangeParts) !== 2) {
            return false;
        }

        $startPattern = trim($rangeParts[0]);
        $endPattern = trim($rangeParts[1]);

        // Check if range contains wildcards
        $hasWildcards = (strpos($startPattern, '*') !== false || strpos($endPattern, '*') !== false);

        if ($hasWildcards) {
            // Wildcard range matching
            return $this->ipMatchesWildcardRange($ip, $startPattern, $endPattern);
        } else {
            // Exact IP range matching
            if (!filter_var($startPattern, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
                !filter_var($endPattern, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return false;
            }

            $ipLong = ip2long($ip);
            $startLong = ip2long($startPattern);
            $endLong = ip2long($endPattern);

            // Ensure start <= end
            if ($startLong > $endLong) {
                list($startLong, $endLong) = [$endLong, $startLong];
            }

            return ($ipLong >= $startLong && $ipLong <= $endLong);
        }
    }

    /**
     * Check if IP matches wildcard range (e.g., 10.0.0.* - 123.0.0.*)
     * @param string $ip
     * @param string $startPattern
     * @param string $endPattern
     * @return bool
     */
    private function ipMatchesWildcardRange($ip, $startPattern, $endPattern)
    {
        $ipParts = explode('.', $ip);
        if (count($ipParts) !== 4) {
            return false;
        }

        $startParts = explode('.', $startPattern);
        $endParts = explode('.', $endPattern);

        if (count($startParts) !== 4 || count($endParts) !== 4) {
            return false;
        }

        // Check each octet
        for ($i = 0; $i < 4; $i++) {
            $ipOctet = (int)$ipParts[$i];
            $startOctet = trim($startParts[$i]);
            $endOctet = trim($endParts[$i]);

            // If start or end has wildcard, we need to handle it
            if ($startOctet === '*' && $endOctet === '*') {
                // Both wildcards - match any value (0-255)
                continue;
            } elseif ($startOctet === '*') {
                // Start is wildcard (0), end is specific
                if (!is_numeric($endOctet)) {
                    return false;
                }
                $endVal = (int)$endOctet;
                if ($endVal < 0 || $endVal > 255) {
                    return false;
                }
                if ($ipOctet > $endVal) {
                    return false;
                }
            } elseif ($endOctet === '*') {
                // End is wildcard (255), start is specific
                if (!is_numeric($startOctet)) {
                    return false;
                }
                $startVal = (int)$startOctet;
                if ($startVal < 0 || $startVal > 255) {
                    return false;
                }
                if ($ipOctet < $startVal) {
                    return false;
                }
            } else {
                // Both are specific values
                if (!is_numeric($startOctet) || !is_numeric($endOctet)) {
                    return false;
                }
                $startVal = (int)$startOctet;
                $endVal = (int)$endOctet;
                
                if ($startVal < 0 || $startVal > 255 || $endVal < 0 || $endVal > 255) {
                    return false;
                }
                
                // Ensure start <= end
                if ($startVal > $endVal) {
                    list($startVal, $endVal) = [$endVal, $startVal];
                }
                
                if ($ipOctet < $startVal || $ipOctet > $endVal) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check IPv6 match 
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private function ipv6Matches($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        $ipBin = inet_pton($ip);
        $subnetBin = inet_pton($subnet);
        
        if ($ipBin === false || $subnetBin === false) {
            return false;
        }
        
        $maskBytes = (int)$mask / 8;
        $maskBits = (int)$mask % 8;
        
        for ($i = 0; $i < $maskBytes; $i++) {
            if ($ipBin[$i] !== $subnetBin[$i]) {
                return false;
            }
        }
        
        if ($maskBits > 0) {
            $maskByte = 0xFF << (8 - $maskBits);
            if ((ord($ipBin[$maskBytes]) & $maskByte) !== (ord($subnetBin[$maskBytes]) & $maskByte)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get country code for IP address using GeoIP2 database
     * 
     * @param string $ipAddress
     * @return string|null Country code (ISO 3166-1 alpha-2) or null if not found
     */
    public function getCountryCode($ipAddress)
    {
        try {
            return $this->getCountryCodeFromGeoIP2($ipAddress);
        } catch (\Exception $e) {
            $this->logger->error("Error getting country code for IP {$ipAddress}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get country code from GeoIP2 database
     * 
     * @param string $ipAddress
     * @return string|null
     */
    private function getCountryCodeFromGeoIP2($ipAddress)
    {
        try {
            if (!class_exists(\GeoIp2\Database\Reader::class)) {
                $this->logger->warning("Please install GeoIP2 supporting file using following command: composer require geoip2/geoip2");
                return null;
            }
            
            $databasePath = $this->getGeoIP2DatabasePath();
            
            if (!$databasePath || !file_exists($databasePath)) {
                $this->logger->warning("GeoIP2 database not found. Please download GeoLite2-Country.mmdb and place it in var/geoip/ directory.");
                return null;
            }

            // Get file size for logging
            $fileSize = filesize($databasePath);
            $fileSizeMb = round($fileSize / 1024 / 1024, 2);
            
            $this->logger->debug("IpRestriction: Looking up country for IP {$ipAddress} using database: {$databasePath} (Size: {$fileSizeMb} MB)");
            
            $reader = new \GeoIp2\Database\Reader($databasePath);
            $record = $reader->country($ipAddress);
            $reader->close();
            
            $this->logger->debug("IpRestriction: GeoIP2 lookup result for IP {$ipAddress} - Record exists: " . ($record ? 'yes' : 'no'));
            
            if ($record) {
                // Try country first
                $isoCode = null;
                $countryName = 'Unknown';
                
                if ($record->country) {
                    $isoCode = $record->country->isoCode ?? null;
                    $countryName = $record->country->name ?? 'Unknown';
                    $this->logger->debug("IpRestriction: GeoIP2 result for IP {$ipAddress} - Country: {$countryName}, ISO Code: " . ($isoCode ?? 'null'));
                }
                
                // Fallback to registeredCountry if country doesn't have ISO code
                if (!$isoCode && isset($record->registeredCountry) && $record->registeredCountry) {
                    $isoCode = $record->registeredCountry->isoCode ?? null;
                    $registeredCountryName = $record->registeredCountry->name ?? 'Unknown';
                    $this->logger->debug("IpRestriction: GeoIP2 fallback to registeredCountry for IP {$ipAddress} - Country: {$registeredCountryName}, ISO Code: " . ($isoCode ?? 'null'));
                }
                
                // Fallback to representedCountry if still no ISO code
                if (!$isoCode && isset($record->representedCountry) && $record->representedCountry) {
                    $isoCode = $record->representedCountry->isoCode ?? null;
                    $representedCountryName = $record->representedCountry->name ?? 'Unknown';
                    $this->logger->debug("IpRestriction: GeoIP2 fallback to representedCountry for IP {$ipAddress} - Country: {$representedCountryName}, ISO Code: " . ($isoCode ?? 'null'));
                }
                
                if ($isoCode) {
                    return $isoCode;
                } else {
                    // Check if this is a known issue with the database
                    $lastModified = filemtime($databasePath);
                    $daysOld = round((time() - $lastModified) / 86400);
                    
                    if ($daysOld > 90) {
                        $this->logger->warning("IpRestriction: GeoIP2 database is {$daysOld} days old and returned null ISO code for IP {$ipAddress}. Consider updating the database.");
                    } else {
                        $this->logger->warning("IpRestriction: GeoIP2 returned record for IP {$ipAddress} but all country ISO codes are null. Country name: {$countryName}. This may indicate the database is incomplete or corrupted.");
                    }
                }
            } else {
                $this->logger->debug("IpRestriction: GeoIP2 returned null record for IP {$ipAddress}");
            }

            return null;
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            $this->logger->debug("IpRestriction: IP address {$ipAddress} not found in GeoIP2 database (may be private/reserved IP): " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            $this->logger->error("IpRestriction: GeoIP2 lookup failed for IP {$ipAddress}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get GeoIP2 database file path
     * Checks multiple possible locations
     * 
     * @return string|null Path to database file or null if not found
     */
    private function getGeoIP2DatabasePath()
    {
        $possiblePaths = [
            $this->directoryList->getRoot() . '/' . IpRestrictionConstants::GEOIP_DIRECTORY . '/GeoLite2-Country.mmdb',
            $this->directoryList->getRoot() . '/var/geoip2/GeoLite2-Country.mmdb',
            $this->directoryList->getRoot() . '/pub/geoip/GeoLite2-Country.mmdb',
            __DIR__ . '/../../../../' . IpRestrictionConstants::GEOIP_DIRECTORY . '/GeoLite2-Country.mmdb',
        ];

        foreach ($possiblePaths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}

