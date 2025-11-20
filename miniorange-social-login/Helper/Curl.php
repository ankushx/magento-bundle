<?php

namespace MiniOrange\MagentoSocialLogin\Helper;

use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;

/**
 * This class denotes all the cURL related functions.
 */
class Curl
{
    public static function mo_send_access_token_request($postData, $url, $clientID, $clientSecret)
    {
        $authHeader = [
            "Content-Type: application/x-www-form-urlencoded",
            'Authorization: Basic '.base64_encode($clientID.":".$clientSecret)
        ];
        $response = self::callAPI($url, $postData, $authHeader);
        return $response;
    }

    public static function mo_send_user_info_request($url, $headers)
    {

        $response = self::callAPI($url, [], $headers);
        return $response;
    }

    public static function submit_contact_us(
        $q_email,
        $q_phone,
        $query

    ) {
        $url = SocialConstants::HOSTNAME . "/moas/rest/customer/contact-us";
        $query = '[' . SocialConstants::AREA_OF_INTEREST . ']: ' . $query;

        $fields = [

            'email' => $q_email,
            'phone' => $q_phone,
            'query' => $query,
            'ccEmail' => 'magentosupport@xecurify.com'
                ];

        $response = self::callAPI($url, $fields);

        return true;
    }

    private static function callAPI($url, $jsonData = [], $headers = ["Content-Type: application/json"])
    {
        // Custom functionality written to be in tune with Mangento2 coding standards.
        $curl = new MoCurl();
        $options = [
            'CURLOPT_FOLLOWLOCATION' => true,
            'CURLOPT_ENCODING' => "",
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_AUTOREFERER' => true,
            'CURLOPT_TIMEOUT' => 0,
            'CURLOPT_MAXREDIRS' => 10
        ];


        $data = in_array("Content-Type: application/x-www-form-urlencoded", $headers)
            ? (!empty($jsonData) ? http_build_query($jsonData) : "") : (!empty($jsonData) ? json_encode($jsonData) : "");

        $method = !empty($data) ? 'POST' : 'GET';
        $curl->setConfig($options);
        $curl->write($method, $url, '1.1', $headers, $data);
        $content = $curl->read();
        $curl->close();
        return $content;
    }

    //Tracking admin email,firstname and lastname.
    public static function submit_to_magento_team($data) {
        $url = SocialConstants::PLUGIN_PORTAL_HOSTNAME . "/api/tracking";
        
        $data['pluginName'] = SocialConstants::MODULE_TITLE;
        $data['pluginVersion'] = SocialConstants::VERSION;
        $data['IsFreeInstalled'] = 'Yes';
    
        $response = self::callAPI($url, $data);
        return true;
    }
}
