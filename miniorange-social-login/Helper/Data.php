<?php

namespace MiniOrange\MagentoSocialLogin\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use MiniOrange\MagentoSocialLogin\Helper\SocialConstants;
use Magento\Framework\HTTP\Client\Curl;

/**
 * This class contains functions to get and set the required data
 * from Magento database or session table/file or generate some
 * necessary values to be used in our module.
 */
class Data extends AbstractHelper
{

    protected $scopeConfig;
    protected $adminFactory;
    protected $customerFactory;
    protected $urlInterface;
    protected $configWriter;
    protected $assetRepo;
    protected $helperBackend;
    protected $frontendUrl;
    protected $key;
    protected $secret;
    protected $provider;
    protected $profile;
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\User\Model\UserFactory $adminFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Helper\Data $helperBackend,
        \Magento\Framework\Url $frontendUrl
    ) {
        $this->curl = new Curl();
        $this->scopeConfig = $scopeConfig;
        $this->adminFactory = $adminFactory;
        $this->customerFactory = $customerFactory;
        $this->urlInterface = $urlInterface;
        $this->configWriter = $configWriter;
        $this->assetRepo = $assetRepo;
        $this->helperBackend = $helperBackend;
        $this->frontendUrl = $frontendUrl;
    }


    /**
     * Get base url of miniorange
     */
    public function getMiniOrangeUrl()
    {
        return SocialConstants::HOSTNAME;
    }

    /**
     * Function to extract data stored in the store config table.
     *
     * @param $config
     */
    public function getStoreConfig($config)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('miniorange/sociallogin/' . $config, $storeScope);
    }


    /**
     * Function to store data stored in the store config table.
     *
     * @param $config
     * @param $value
     */
    public function setStoreConfig($config, $value)
    {
        $this->configWriter->save('miniorange/sociallogin/' . $config, $value);
    }
    

    /**
     * This function is used to save user attributes to the
     * database and save it. Mostly used in the SSO flow to
     * update user attributes. Decides which user to update.
     *
     * @param $url
     * @param $value
     * @param $id
     * @param $admin
     * @throws \Exception
     */
    public function saveConfig($url, $value, $id, $admin)
    {
        $admin ? $this->saveAdminStoreConfig($url, $value, $id) : $this->saveCustomerStoreConfig($url, $value, $id);
    }


    /**
     * Function to extract information stored in the admin user table.
     *
     * @param $config
     * @param $id
     */
    public function getAdminStoreConfig($config, $id)
    {
        return $this->adminFactory->create()->load($id)->getData($config);
    }


    /**
     * This function is used to save admin attributes to the
     * database and save it. Mostly used in the SSO flow to
     * update user attributes.
     *
     * @param $url
     * @param $value
     * @param $id
     * @throws \Exception
     */
    private function saveAdminStoreConfig($url, $value, $id)
    {
        $data = [$url=>$value];
        $model = $this->adminFactory->create()->load($id)->addData($data);
        $model->setId($id)->save();
    }
    

    /**
     * Function to extract information stored in the customer user table.
     *
     * @param $config
     * @param $id
     */
    public function getCustomerStoreConfig($config, $id)
    {
        return $this->customerFactory->create()->load($id)->getData($config);
    }


    /**
     * This function is used to save customer attributes to the
     * database and save it. Mostly used in the SSO flow to
     * update user attributes.
     *
     * @param $url
     * @param $value
     * @param $id
     * @throws \Exception
     */
    private function saveCustomerStoreConfig($url, $value, $id)
    {
        $data = [$url=>$value];
        $model = $this->customerFactory->create()->load($id)->addData($data);
        $model->setId($id)->save();
    }


    /**
     * Function to get the sites Base URL.
     */
    public function getBaseUrl()
    {
        return  $this->urlInterface->getBaseUrl();
    }

    /**
     * Function get the current url the user is on.
     */
    public function getCurrentUrl()
    {
        return  $this->urlInterface->getCurrentUrl();
    }


    /**
     * Function to get the url based on where the user is.
     *
     * @param $url
     */
    public function getUrl($url, $params = [])
    {
        return  $this->urlInterface->getUrl($url, ['_query'=>$params]);
    }


    /**
     * Function to get the sites frontend url.
     *
     * @param $url
     */
    public function getFrontendUrl($url, $params = [])
    {
        return  $this->frontendUrl->getUrl($url, ['_query'=>$params]);
    }


    /**
     * Function to get the sites Issuer URL.
     */
    public function getIssuerUrl()
    {
        return $this->getBaseUrl() . SocialConstants::ISSUER_URL_PATH;
    }


    /**
     * Function to get the Image URL of our module.
     *
     * @param $image
     */
    public function getImageUrl($image)
    {
        return $this->assetRepo->getUrl(SocialConstants::MODULE_DIR.SocialConstants::MODULE_IMAGES.$image);
    }


    /**
     * Get Admin CSS URL
     */
    public function getAdminCssUrl($css)
    {
        return $this->assetRepo->getUrl(SocialConstants::MODULE_DIR.SocialConstants::MODULE_CSS.$css, ['area'=>'adminhtml']);
    }


    /**
     * Get Admin JS URL
     */
    public function getAdminJSUrl($js)
    {
        return $this->assetRepo->getUrl(SocialConstants::MODULE_DIR.SocialConstants::MODULE_JS.$js, ['area'=>'adminhtml']);
    }


    /**
     * Get Admin Metadata Download URL
     */
    public function getMetadataUrl()
    {
        return $this->assetRepo->getUrl(SocialConstants::MODULE_DIR.SocialConstants::MODULE_METADATA, ['area'=>'adminhtml']);
    }


    /**
     * Get Admin Metadata File Path
     */
    public function getMetadataFilePath()
    {
        return $this->assetRepo->createAsset(SocialConstants::MODULE_DIR.SocialConstants::MODULE_METADATA, ['area'=>'adminhtml'])
                    ->getSourceFile();
    }


    /**
     * Function to get the resource as a path instead of the URL.
     *
     * @param $key
     */
    public function getResourcePath($key)
    {
        return $this->assetRepo
                    ->createAsset(SocialConstants::MODULE_DIR.SocialConstants::MODULE_CERTS.$key, ['area'=>'adminhtml'])
                    ->getSourceFile();
    }


    /**
     * Get admin Base url for the site.
     */
    public function getAdminBaseUrl()
    {
        return $this->helperBackend->getHomePageUrl();
    }

    /**
     * Get the Admin url for the site based on the path passed,
     * Append the query parameters to the URL if necessary.
     *
     * @param $url
     * @param $params
     */
    public function getAdminUrl($url, $params = [])
    {
        return $this->helperBackend->getUrl($url, ['_query'=>$params]);
    }


    /**
     * Get the Admin secure url for the site based on the path passed,
     * Append the query parameters to the URL if necessary.
     *
     * @param $url
     * @param $params
     */
    public function getAdminSecureUrl($url, $params = [])
    {
        return $this->helperBackend->getUrl($url, ['_secure'=>true,'_query'=>$params]);
    }

     /**
      * Get the SP InitiatedURL
      *
      * @param $relayState
      */
    public function getSPInitiatedUrlForGoogle($relayState = null)
    {
        $relayState = is_null($relayState) ?$this->getCurrentUrl() : $relayState;
        return $this->getFrontendUrl(
            SocialConstants::SOCIALLOGIN_LOGIN_URL,
            ["provider"=>"google","relayState"=>$relayState]
        );
    }


    /**
     * Get the SP InitiatedURL
     *
     * @param $relayState
     */
    public function getSPInitiatedUrlForFacebook($relayState = null)
    {
        $relayState = is_null($relayState) ?$this->getCurrentUrl() : $relayState;
        return $this->getFrontendUrl(
            SocialConstants::SOCIALLOGIN_LOGIN_URL,
            ["relayState"=>$relayState, "provider"=>"facebook"]
        );
    }

    /**
     * Get the SP InitiatedURL
     *
     * @param $relayState
     */
    public function getSPInitiatedUrlForLinkedin($relayState = null)
    {
        $relayState = is_null($relayState) ?$this->getCurrentUrl() : $relayState;
        return $this->getFrontendUrl(
            SocialConstants::SOCIALLOGIN_LOGIN_URL,
            ["relayState"=>$relayState, "provider"=>"linkedin"]
        );
    }

    /**
     * Get the SP InitiatedURL
     *
     * @param $relayState
     */
    public function getSPInitiatedUrlForTwitter($relayState = null)
    {
        $relayState = is_null($relayState) ?$this->getCurrentUrl() : $relayState;
        return $this->getFrontendUrl(
            SocialConstants::SOCIALLOGIN_LOGIN_URL,
            ["relayState"=>$relayState, "provider"=>"twitter"]
        );
    }

    function mo_twitter_get_access_token($oauth_verifier, $twitter_oauth_token)
    {
        $args = [
            "oauth_verifier" => $oauth_verifier,
            "oauth_token" => $twitter_oauth_token
        ];
        $this->curl->post('https://api.twitter.com/oauth/access_token?', $args);
        $post_response = $this->curl->getBody();
        return $post_response;
    }

    function mo_twitter_get_profile_signature($oauth_token, $oauth_token_secret, $screen_name)
    {
        $this->profile = "https://api.twitter.com/1.1/account/verify_credentials.json";
        $params = [
            "oauth_version" => "1.0",
            "oauth_nonce" => time(),
            "oauth_timestamp" => time(),
            "oauth_consumer_key" => $this->key,
            "oauth_token" => $oauth_token,
            "oauth_signature_method" => "HMAC-SHA1",
            "screen_name" => $screen_name,
            "include_email" => "true"
        ];

        $keys = $this->mo_twitter_url_encode_rfc3986(array_keys($params));
        $values = $this->mo_twitter_url_encode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);
        uksort($params, 'strcmp');

        foreach ($params as $k => $v) {
            $pairs[] = $this->mo_twitter_url_encode_rfc3986($k).'='.$this->mo_twitter_url_encode_rfc3986($v);
        }
        $concatenatedParams = implode('&', $pairs);

        $baseString= "GET&".$this->mo_twitter_url_encode_rfc3986($this->profile)."&".$this->mo_twitter_url_encode_rfc3986($concatenatedParams);

        $secret = $this->mo_twitter_url_encode_rfc3986($this->secret)."&". $this->mo_twitter_url_encode_rfc3986($oauth_token_secret);
        $params['oauth_signature'] = $this->mo_twitter_url_encode_rfc3986(base64_encode(hash_hmac('sha1', $baseString, $secret, true)));

        uksort($params, 'strcmp');
        foreach ($params as $k => $v) {
            $urlPairs[] = $k."=".$v;
        }
        $concatenatedUrlParams = implode('&', $urlPairs);
        $url = $this->profile."?".$concatenatedUrlParams;
       //    echo $url;exit;
        $args = [];

        $this->curl->get($url, $args);
        $get_response = $this->curl->getBody();
        
        $profile_json_output = json_decode($get_response, true);
        //echo print_r($profile_json_output,true);exit;
        return  $profile_json_output;
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

    // function mo_twitter_http($url, $post_data = null)
    // {

    //     if(isset($post_data))
    //     {

    //         $args = array(
    //             'method' => 'POST',
    //             'body' => $post_data,
    //             'timeout' => '5',
    //             'redirection' => '5',
    //             'httpversion' => '1.0',
    //             'blocking' => true
    //         );
    //         //$redirectURL = $this->getCallBackUrl().'?provider='.$this->provider;
    //         //$accessTokenRequest = (new AccessTokenRequestBody($grantType, $redirectURL, $authorizationCode))->build();
    //       //  echo $this->curl->post($url,$args);exit;
    //         $post_response = $this->curl->getBody();
    //         //echo print_r($post_response,true);exit;
    //         return $post_response['body'];

    //     }
    //     $args = array();

    //     //$get_response = wp_remote_get($url,$args);
    //     $this->curl->get($url,$args);
    //     $response = $this->curl->getBody();
    //     //echo "response".print_r($response,true);exit;
    //     //$response = json_decode($response, true);
    //     //echo "response".print_r($response,true);exit;
    //     //$response =  $get_response['body'];
    //     if( !session_id() ) {
    //         session_start();
    //     }
    //    // $response = implode($response);
    //     $dirs = explode('&', $response);
    //     $dirs1 = explode('=', $dirs[0]);
    //     return $dirs1[1];

    // }

    /** Setter for the request Parameter */
    public function setRequestParam($request)
    {
        $this->REQUEST = $request;
        return $this;
    }


    /** Setter for the post Parameter */
    public function setPostParam($post)
    {
        $this->POST = $post;
        return $this;
    }
    
    
    public function verifySign($JWTComponents, $jwkeys)
    {
        $this->log_debug("ReadAuthorizationResponse: verifySign");
        $rsa = new CryptRSA();
        $rsa->loadKey([
                'n' => new MathBigInteger($this->get_base64_from_url($jwkeys->n), 256),
                'e' => new MathBigInteger($this->get_base64_from_url($jwkeys->e), 256)
        ]);
        $rsa->setHash('sha256');
        $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        return $rsa->verify($JWTComponents['data'], $JWTComponents['sign']) ? true : false;
    }

    public function setidsecret($id, $secret, $provider)
    {
        $this->key = $id;
        $this->secret = $secret;
        $this->provider = $provider;
    }

    public function get_base64_from_url($b64url)
    {
        return base64_decode(str_replace(['-','_'], ['+','/'], $b64url));
    }

    public function decodeJWT($JWT)
    {
        $pieces = explode(".", $JWT);
        $header = json_decode($this->get_base64_from_url($pieces[0]));
        $payload = json_decode($this->get_base64_from_url($pieces[1]));
        ;
        $sign = $this->get_base64_from_url($pieces[2]);
        
        return [
            'header' => $header,
            'payload' => $payload,
            'sign' => $sign,
            'data' => $pieces[0].".".$pieces[1],
        ];
    }
}
