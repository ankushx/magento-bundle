<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use MiniOrange\MagentoSocialLogin\Helper\MagentoSocialLogin\AuthorizationRequest;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use Magento\Framework\HTTP\Client\Curl;

/**
 * Handles generation and sending of AuthnRequest to the IDP
 * for authentication. AuthnRequest is generated and user is
 * redirected to the IDP for authentication.
 */
class sendAuthorizationRequest extends BaseAction
{
    protected $curl;
    protected $key;
    protected $secret;
    /**
     * Execute function to execute the classes function.
     * @throws \Exception
     */
    public function execute()
    {
        
        $curl = new Curl();
        $this->curl = $curl;
        $this->socialUtility->log_debug("SendAuthorizationRequest: execute");
        $params = $this->getRequest()->getParams();  //get params
        $this->socialUtility->log_debug("params in sendAuthorizationRequest: ".print_r($params, true));


        $relayState = isset($params['relayState']) ? $params['relayState'] : '/';

        if ($params['provider']=='google') {
            return $this->googleAuthRequest($relayState);
        } elseif ($params['provider']=='facebook') {
            return $this->facebookAuthRequest($relayState);
        } elseif ($params['provider']=='linkedin') {
            return $this->linkedinAuthRequest($relayState);
        } elseif ($params['provider']=='twitter') {
            return $this->twitterAuthRequest($relayState);
        }
    }

    public function googleAuthRequest($relayState)
    {
        if ($relayState == SocialConstants::GOOGLE_TEST_RELAYSTATE) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_GOOGLE_TEST, true);
            $this->socialUtility->flushCache();
        }
        $googleClientID = $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_CLIENT_ID);
        $googleScope = $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_SCOPE);
        $googleAuthorizeURL = SocialConstants::GOOGLE_AUTHORIZE_URL;
        $responseType = SocialConstants::CODE;
        $redirectURL = $this->socialUtility->getCallBackUrl().'?provider=google';
        $authorizationRequest = (new AuthorizationRequest($googleClientID, $googleScope, $googleAuthorizeURL, $responseType, $redirectURL, $relayState))->build();
        return $this->sendHTTPRedirectRequest($authorizationRequest, $googleAuthorizeURL);
    }

    public function facebookAuthRequest($relayState)
    {
        if ($relayState == SocialConstants::FACEBOOK_TEST_RELAYSTATE) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_FACEBOOK_TEST, true);
            $this->socialUtility->flushCache();
        }
        $facebookClientID = $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_CLIENT_ID);
        $facebookScope = $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_SCOPE);
        $facebookAuthorizeURL = SocialConstants::FACEBOOK_AUTHORIZE_URL;
        $responseType = SocialConstants::CODE;
        $redirectURL = $this->socialUtility->getCallBackUrl().'?provider=facebook';
        $authorizationRequest = (new AuthorizationRequest($facebookClientID, $facebookScope, $facebookAuthorizeURL, $responseType, $redirectURL))->build();
        return $this->sendHTTPRedirectRequest($authorizationRequest, $facebookAuthorizeURL);
    }

    public function linkedinAuthRequest($relayState)
    {
        if ($relayState == SocialConstants::LINKEDIN_TEST_RELAYSTATE) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_LINKEDIN_TEST, true);
            $this->socialUtility->flushCache();
        }
        $linkedinClientID = $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_CLIENT_ID);
        $linkedinScope = $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_SCOPE);
        $linkedinAuthorizeURL = SocialConstants::LINKEDIN_AUTHORIZE_URL;
        $responseType = SocialConstants::CODE;
        $redirectURL = $this->socialUtility->getCallBackUrl().'?provider=linkedin';
        $authorizationRequest = (new AuthorizationRequest($linkedinClientID, $linkedinScope, $linkedinAuthorizeURL, $responseType, $redirectURL))->build();
        return $this->sendHTTPRedirectRequest($authorizationRequest, $linkedinAuthorizeURL);
    }

    public function twitterAuthRequest($relayState)
    {

        if ($relayState == SocialConstants::TWITTER_TEST_RELAYSTATE) {
            $this->socialUtility->setStoreConfig(SocialConstants::IS_TWITTER_TEST, true);
            $this->socialUtility->flushCache();
        }
        $twitterClientID = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_ID);
        $twitterClientSecret = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_SECRET);
        $this->key = $twitterClientID;
        $this->secret = $twitterClientSecret;
        $twitterScope = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_SCOPE);
        $responseType = SocialConstants::CODE;
        $redirectURL = $this->socialUtility->getCallBackUrl().'?provider=twitter';
        $oauth_token = $this->mo_twitter_get_request_token();
        $twitterAuthorizeURL = "https://api.twitter.com/oauth/authenticate?oauth_token=" . $oauth_token.'&response_type=code&client_id=' .$twitterClientID .'&scope='.$twitterScope.'&access_type=offline';
        header('Location:'. $twitterAuthorizeURL);

        $authorizationRequest = (new AuthorizationRequest($twitterClientID, $twitterScope, $twitterAuthorizeURL, $responseType, $redirectURL))->build();
        return $this->sendHTTPRedirectRequest($authorizationRequest, $twitterAuthorizeURL);

        // header('Location:'. $login_dialog_url);
    }

    function mo_twitter_get_request_token()
    {
        // Default params
        $params = [
            "oauth_version" => "1.0",
            "oauth_nonce" => time(),
            "oauth_timestamp" => time(),
            "oauth_consumer_key" => $this->key,
            "oauth_signature_method" => "HMAC-SHA1"
        ];

        // BUILD SIGNATURE
        // encode params keys, values, join and then sort.
        $keys = $this->mo_twitter_url_encode_rfc3986(array_keys($params));
        $values = $this->mo_twitter_url_encode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);
        uksort($params, 'strcmp');

        // convert params to string
        foreach ($params as $k => $v) {
            $pairs[] = $this->mo_twitter_url_encode_rfc3986($k).'='.$this->mo_twitter_url_encode_rfc3986($v);
        }
        $concatenatedParams = implode('&', $pairs);

        // form base string (first key)
        $baseString= "GET&".$this->mo_twitter_url_encode_rfc3986("https://twitter.com/oauth/request_token")."&".$this->mo_twitter_url_encode_rfc3986($concatenatedParams);
        // form secret (second key)
        $secret = $this->mo_twitter_url_encode_rfc3986($this->secret)."&";
        // make signature and append to params
        $params['oauth_signature'] = $this->mo_twitter_url_encode_rfc3986(base64_encode(hash_hmac('sha1', $baseString, $secret, true)));

        // BUILD URL
        // Resort
        uksort($params, 'strcmp');
        // convert params to string
        foreach ($params as $k => $v) {
            $urlPairs[] = $k."=".$v;
        }
        $concatenatedUrlParams = implode('&', $urlPairs);
        // form url
        $url = "https://twitter.com/oauth/request_token?".$concatenatedUrlParams;
        // Send to cURL
        return $this->mo_twitter_http($url);
    }

    function mo_twitter_url_encode_rfc3986($input)
    {
        if (is_array($input)) {
            
            $array_map = [];
            foreach ($input as $key => $var) {
                $array_map[$key] = $this->mo_twitter_url_encode_rfc3986($input[$key]);
            }
            $doubledValues = $array_map;
            return $doubledValues;
        } elseif (is_scalar($input)) {
            return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
        } else {
            return '';
        }
    }

    function mo_twitter_http($url, $post_data = null)
    {
        if (isset($post_data)) {

            $args = [
                'method' => 'POST',
                'body' => $post_data,
                'timeout' => '5',
                'redirection' => '5',
                'httpversion' => '1.0',
                'blocking' => true
            ];

            $post_response = $this->curl->post($url, $args);
            $response = $this->curl->getBody();
            return $post_response['body'];

        }
        $args = [];

        $this->curl->get($url, $args);
        $response = $this->curl->getBody();
        if (!session_id()) {
            session_start();
        }
        $dirs = explode('&', $response);

        $dirs1 = explode('=', $dirs[0]);

        return $dirs1[1];
    }
}
