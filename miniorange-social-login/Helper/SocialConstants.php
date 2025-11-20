<?php

namespace MiniOrange\MagentoSocialLogin\Helper;

/**
 * This class lists down constant values used all over our Module.
 */
class SocialConstants
{
    const MODULE_DIR         = 'MiniOrange_MagentoSocialLogin';
    const MODULE_TITLE         = 'Social Login';

    //ACL Settings
    const MODULE_BASE         = '::SocialLogin';
    const MODULE_SOCIALSETTINGS = '::sociallogin_settings';
    const MODULE_SIGNIN     = '::signin_settings';
    const MODULE_ATTR          = '::attr_settings';
    const MODULE_FAQ          = '::faq_settings';
    const MODULE_SUPPORT    = '::support';
    const MODULE_UPGRADE     = '::upgrade';

    const MODULE_IMAGES     = '::images/';
    const MODULE_CERTS         = '::certs/';
    const MODULE_CSS         = '::css/';
    const MODULE_JS         = '::js/';

    // request option parameter values
    const LOGIN_ADMIN_OPT    = 'oauthLoginAdminUser';
    const TEST_CONFIG_OPT     = 'testConfig';

    //database keys

    const APP_NAME          = 'appName';
    const CLIENT_ID         = 'clientID';
    const CLIENT_SECRET     = 'clientSecret';
    const SCOPE             = 'scope';
    const AUTHORIZE_URL     = 'authorizeURL';
    const ACCESSTOKEN_URL   = 'accessTokenURL';
    const GETUSERINFO_URL   = 'getUserInfoURL';
    const SOCIAL_LOGIN_LOGOUT_URL  = 'SocialLoginLogoutURL';
    const GOOGLE_TEST_RELAYSTATE     = 'google_testvalidate';
    const FACEBOOK_TEST_RELAYSTATE     = 'facebook_testvalidate';
    const LINKEDIN_TEST_RELAYSTATE     = 'linkedin_testvalidate';
    const TWITTER_TEST_RELAYSTATE     = 'twitter_testvalidate';
    const ENDPOINT_URL = 'endpoint_url';

    
    const MAP_MAP_BY         = 'amAccountMatcher';
    const DEFAULT_MAP_BY     = 'email';
    const DEFAULT_GROUP     = 'General';
    const SEND_HEADER   =   'header';
    const SEND_BODY    = 'body';

    const NAME_ID             = 'nameId';
    const IDP_NAME             = 'identityProviderName';
    const X509CERT             = 'certificate';
    const RESPONSE_SIGNED     = 'responseSigned';
    const ASSERTION_SIGNED     = 'assertionSigned';
    const ISSUER             = 'samlIssuer';
    const DB_FIRSTNAME         = 'firstname';
    const USER_NAME         = 'username';
    const DB_LASTNAME         = 'lastname';
    const CUSTOMER_EMAIL    = 'email';
    const CUSTOMER_PHONE    = 'phone';
    const CUSTOMER_NAME        = 'cname';
    const CUSTOMER_FNAME    = 'customerFirstName';
    const CUSTOMER_LNAME    = 'customerLastName';
    const SAMLSP_CKL         = 'ckl';
    const SAMLSP_LK         = 'lk';
    const SHOW_ADMIN_LINK     = 'showadminlink';
    const SHOW_CUSTOMER_LINK= 'showcustomerlink';
    const TOKEN             = 'token';
    const BUTTON_TEXT         = 'buttonText';
    const IS_GOOGLE_TEST           = 'isGoogleTest';
    const IS_FACEBOOK_TEST           = 'isFacebookTest';
    const IS_LINKEDIN_TEST           = 'isLinkedinTest';
    const IS_TWITTER_TEST           = 'isTwitterTest';

    // attribute mapping constants
    const MAP_EMAIL         = 'amEmail';
    const DEFAULT_MAP_EMAIL = 'email';
    const MAP_USERNAME        = 'amUsername';
    const DEFAULT_MAP_USERN = 'username';
    const MAP_FIRSTNAME     = 'amFirstName';
    const DEFAULT_MAP_FN     = 'firstName';
    const MAP_LASTNAME         = 'amLastName';
    const MAP_DEFAULT_ROLE     = 'defaultRole';
    const DEFAULT_ROLE         = 'General';
    const MAP_GROUP         = 'group';
    const UNLISTED_ROLE     = 'unlistedRole';
    const CREATEIFNOTMAP     = 'createUserIfRoleNotMapped';

    //URLs
    const SOCIALLOGIN_LOGIN_URL     = 'mosocial/actions/sendAuthorizationRequest';

    //images
    const IMAGE_RIGHT         = 'right.png';
    const IMAGE_WRONG         = 'wrong.png';

    const CALLBACK_URL      = 'mosocial/actions/ReadAuthorizationResponse';
    const CODE              = 'code';
    const GRANT_TYPE        = 'authorization_code';

    //SOCIALLOGIN Constants
    const SOCIALLOGIN              = 'SOCIALLOGIN';
    const HTTP_REDIRECT     = 'HttpRedirect';

    //Registration Status
    const STATUS_VERIFY_LOGIN     = "MO_VERIFY_CUSTOMER";
    const STATUS_COMPLETE_LOGIN = "MO_VERIFIED";

    //plugin constants
    const SAMLSP_KEY         = 'customerKey';
    const VERSION = "v1.0.4";
    const HOSTNAME                = "https://login.xecurify.com";
    const PLUGIN_PORTAL_HOSTNAME  = "https://magento.shanekatear.in/plugin-portal";
    const AREA_OF_INTEREST         = 'Magento 2.0 Social Login Client Plugin';
    const MAGENTO_COUNTER          = "magento_count";

    //Google credentials
    const GOOGLE_CLIENT_ID         = 'google_client_id';
    const GOOGLE_CLIENT_SECRET     = 'google_client_secret';
    const GOOGLE_SCOPE             = 'google_scope';
    const GOOGLE_AUTHORIZE_URL     = 'https://accounts.google.com/o/oauth2/auth';
    const GOOGLE_ACCESSTOKEN_URL   = 'https://accounts.google.com/o/oauth2/token';
    const GOOGLE_GETUSERINFO_URL   = 'https://www.googleapis.com/oauth2/v1/userinfo';
    const ENABLE_GOOGLE            = 'enable_google';

    //Facebook credentials
    const FACEBOOK_CLIENT_ID         = 'facebook_client_id';
    const FACEBOOK_CLIENT_SECRET     = 'facebook_client_secret';
    const FACEBOOK_SCOPE             = 'facebook_scope';
    const FACEBOOK_AUTHORIZE_URL     = 'https://www.facebook.com/v3.2/dialog/oauth';
    const FACEBOOK_ACCESSTOKEN_URL   = 'https://graph.facebook.com/v3.2/oauth/access_token';
    const FACEBOOK_GETUSERINFO_URL   = 'https://graph.facebook.com/me?fields=id,name,about,link,email,first_name,last_name,picture.width(720).height(720)';
    const ENABLE_FACEBOOK            = 'enable_facebook';
    
    //linkedIn credentials
    const LINKEDIN_CLIENT_ID         = 'linkedin_client_id';
    const LINKEDIN_CLIENT_SECRET     = 'linkedin_client_secret';
    const LINKEDIN_SCOPE             = 'linkedin_scope';
    const LINKEDIN_AUTHORIZE_URL     = 'https://www.linkedin.com/oauth/v2/authorization';
    const LINKEDIN_ACCESSTOKEN_URL   = 'https://www.linkedin.com/oauth/v2/accessToken';
    const LINKEDIN_USER_EMAIL_URL    = 'https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))';
    const LINKEDIN_GETUSERINFO_URL   = 'https://api.linkedin.com/v2/me?projection=(id,firstName,lastName,emailAddress,profilePicture(displayImage~:playableStreams))';
    const ENABLE_LINKEDIN            = 'enable_linkedin';

    //Twitter credentials
    const TWITTER_CLIENT_ID        = 'twitter_client_id';
    const TWITTER_CLIENT_SECRET    = 'twitter_client_secret';
    const TWITTER_SCOPE            = 'twitter_scope';
    const TWITTER_AUTHORIZE_URL     = 'https://accounts.google.com/o/oauth2/auth';
    const TWITTER_ACCESSTOKEN_URL   = 'https://twitter.com/oauth/access_token';
    const TWITTER_GETUSERINFO_URL   = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    const ENABLE_TWITTER            = 'enable_twitter';


    const SEND_EMAIL ='send_email';
    const ADMINEMAIL = 'admin_email';
    const SET_DATA = 'setdata';
    const DATA = 'data';

    const SEND_EMAIL_DURING_TEST_CONFIGURATION ='send_email_at_test_configuration';
    const PREVIOUS_DATE = 'previous_date';
    const SEND_EMAIL_CORE_CONFIG_DATA = 'send_email_config_data';

    const DATA_ADDED = 'data_added';
    const TIME_STAMP = 'time_stamp';
}
