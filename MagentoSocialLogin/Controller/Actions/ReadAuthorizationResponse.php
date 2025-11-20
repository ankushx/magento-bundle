<?php

namespace MiniOrange\MagentoSocialLogin\Controller\Actions;

use Exception;
use Magento\Framework\App\Action\Context;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use MiniOrange\MagentoSocialLogin\Helper\MagentoSocialLogin\AccessTokenRequest;
use MiniOrange\MagentoSocialLogin\Helper\MagentoSocialLogin\AccessTokenRequestBody;
use MiniOrange\MagentoSocialLogin\Helper\Curl;
use miniorange\MagentoSocialLogin\Controller\Actions\MathBigInteger;
use MiniOrange\MagentoSocialLogin\Helper\SocialUtility;
use MiniOrange\MagentoSocialLogin\Controller\Actions\BaseAction;
/**
 * Handles reading of Responses from the IDP. Read the SAML Response
 * from the IDP and process it to detect if it's a valid response from the IDP.
 * Generate a SAML Response Object and log the user in. Update existing user
 * attributes and groups if necessary.
 */
class ReadAuthorizationResponse extends BaseAction
{
    private $REQUEST;
    private $POST;
    private $processResponseAction;

    public function __construct(
        Context $context,
        SocialUtility $socialUtility,
        ProcessResponseAction $processResponseAction
    ) {
        //You can use dependency injection to get any class this observer may need.
        $this->processResponseAction = $processResponseAction;
        parent::__construct($context, $socialUtility);
    }


/**
 * Execute function to execute the classes function.
 * @throws Exception
 */
    public function execute()
    {
        $this->socialUtility->log_debug("ReadAuthorizationResponse: execute");
        // read the response
        $params = $this->getRequest()->getParams();
        $provider = $params['provider'];
        if ($provider == 'twitter') {
            $oauth_verifier = $params['oauth_verifier'];
            $twitter_oauth_token = $params['oauth_token'];
            
            $clientID = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_ID);
            $clientSecret = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_SECRET);
            $this->socialUtility->setidsecret($clientID, $clientSecret, $provider);
            
            $oauth_token = $this->socialUtility->mo_twitter_get_access_token($oauth_verifier, $twitter_oauth_token);
            $oauth_token_array = explode('&', $oauth_token);
            $oauth_access_token = isset($oauth_token_array[0]) ? $oauth_token_array[0] : null;
            $oauth_access_token = explode('=', $oauth_access_token);
            $oauth_token_secret = isset($oauth_token_array[1]) ? $oauth_token_array[1] : null;
            $oauth_token_secret = explode('=', $oauth_token_secret);
            $screen_name = isset($oauth_token_array[3]) ? $oauth_token_array[3] : null;
            $screen_name = explode('=', $screen_name);
            $oauth_access_token1 = isset($oauth_access_token[1]) ? $oauth_access_token[1] : '';
            $oauth_token_secret1 = isset($oauth_token_secret[1]) ? $oauth_token_secret[1] : '';
            $screen_name1    =   isset($screen_name[1]) ? $screen_name[1] : '';

            $userInfoResponseData = $this->socialUtility->mo_twitter_get_profile_signature($oauth_access_token1, $oauth_token_secret1, $screen_name1);
            $this->processResponseAction->setProvider($provider)->setUserInfoResponse($userInfoResponseData)->execute();
        } else {
            if (!isset($params['code'])) {
                if (isset($params['error'])) {
                    return $this->sendHTTPRedirectRequest('?error='.urlencode($params['error']), $this->socialUtility->getBaseUrl());
                }
                return $this->sendHTTPRedirectRequest('?error=code+not+received', $this->socialUtility->getBaseUrl());
            }
        
            $authorizationCode = $params['code'];

        

        //get required values from the database
        
            $grantType = SocialConstants::GRANT_TYPE;
            if ($provider == 'google') {
                $clientID = $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_CLIENT_ID);
                $clientSecret = $this->socialUtility->getStoreConfig(SocialConstants::GOOGLE_CLIENT_SECRET);
                $accessTokenURL = SocialConstants::GOOGLE_ACCESSTOKEN_URL;
                $userInfoURL = SocialConstants::GOOGLE_GETUSERINFO_URL;
            } elseif ($provider == 'facebook') {
                $clientID = $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_CLIENT_ID);
                $clientSecret = $this->socialUtility->getStoreConfig(SocialConstants::FACEBOOK_CLIENT_SECRET);
                $accessTokenURL = SocialConstants::FACEBOOK_ACCESSTOKEN_URL;
                $userInfoURL = SocialConstants::FACEBOOK_GETUSERINFO_URL;
            } elseif ($provider == 'linkedin') {
                $clientID = $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_CLIENT_ID);
                $clientSecret = $this->socialUtility->getStoreConfig(SocialConstants::LINKEDIN_CLIENT_SECRET);
                $accessTokenURL = SocialConstants::LINKEDIN_ACCESSTOKEN_URL;
                $userEmailUrl = SocialConstants::LINKEDIN_USER_EMAIL_URL;
                $userInfoURL = SocialConstants::LINKEDIN_GETUSERINFO_URL;
            } elseif ($provider == 'twitter') {
                $clientID = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_ID);
                $clientSecret = $this->socialUtility->getStoreConfig(SocialConstants::TWITTER_CLIENT_SECRET);
                $accessTokenURL = SocialConstants::TWITTER_ACCESSTOKEN_URL;
                $userInfoURL = SocialConstants::TWITTER_GETUSERINFO_URL;
            }
        
            $redirectURL = $this->socialUtility->getCallBackUrl().'?provider='.$provider;

            $header = $this->socialUtility->getStoreConfig(SocialConstants::SEND_HEADER);
            $body = $this->socialUtility->getStoreConfig(SocialConstants::SEND_BODY);

            $this->socialUtility->log_debug("Readauthorizetionresponse: generating accessTokenRequest: ");
            
            $accessTokenRequest = (new AccessTokenRequest($clientID,$clientSecret,$grantType, $redirectURL, $authorizationCode))->build();
            $this->socialUtility->log_debug("Readauthorizetionresponse: ReadaccessTokenRequest: ".print_r($accessTokenRequest, true));
        //send the accessToken request
            $accessTokenResponse = Curl::mo_send_access_token_request($accessTokenRequest, $accessTokenURL, $clientID, $clientSecret);
            $this->socialUtility->log_debug("Readauthorizetionresponse: accessTokenresponse: ".print_r($accessTokenResponse, true));
       
        // if access token endpoint returned a success response
            $accessTokenResponseData = json_decode($accessTokenResponse, 'true');
            if (isset($accessTokenResponseData['access_token'])) {
                $accessToken = $accessTokenResponseData['access_token'];

                $header = "Bearer " . $accessToken;
                $authHeader =  [
                "Authorization: $header"
                ];
                if ($provider== 'linkedin') {
                    $userEmail = Curl::mo_send_user_info_request($userEmailUrl, $authHeader);
                    $profile_json_output_email = json_decode($userEmail, true);
                    $email = isset($profile_json_output_email['elements']['0']['handle~']['emailAddress']) ?  $profile_json_output_email['elements']['0']['handle~']['emailAddress'] : '';
                }
           
                $userInfoResponse = Curl::mo_send_user_info_request($userInfoURL, $authHeader);
                $userInfoResponseData = json_decode($userInfoResponse, 'true');
            
                $this->socialUtility->log_debug("Readauthorizetionresponse: userinforesponse: ".print_r($userInfoResponseData, true));
            } elseif (isset($accessTokenResponseData['id_token'])) {
                $idToken = $accessTokenResponseData['id_token'];
                if (!empty($idToken)) {
                   // $x509_cert = $this->socialUtility->getStoreConfig(SocialConstants::X509CERT);
                    $idTokenArray = explode(".", $idToken);
                    if (sizeof($idTokenArray)>2) {
                        $userInfoResponseData = $idTokenArray[1];
                        $userInfoResponseData = json_decode(base64_decode($userInfoResponseData));
                    } else {
                        return $this->getResponse()->setBody("Invalid response. Please try again.");
                    }
                }
            } else {

                return $this->getResponse()->setBody("Invalid response. Please try again.");
            }
            
            if (empty($userInfoResponseData)) {
                return $this->getResponse()->setBody("Invalid response. Please try again.");
            }
            if ($provider == 'linkedin') {
                $this->processResponseAction->setProvider($provider)->setLinkedinEmail($email)->setUserInfoResponse($userInfoResponseData)->execute();
            }

        
            $this->processResponseAction->setProvider($provider)->setUserInfoResponse($userInfoResponseData)->execute();
        }
    }
}
