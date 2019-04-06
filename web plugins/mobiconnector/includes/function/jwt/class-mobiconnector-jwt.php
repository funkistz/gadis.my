<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class BAMobile_JWT_Auth{
    /**
     * Namespace of JWT api 
     */
    private $namespace = 'mobiconnector/jwt';

    /**
     * List error
     */
    private $jwt_error = null;

    /**
     * BAMobile JWT Auth construct
     */
    public function __construct(){
        $this->init_hooks();
    }
    
    /**
	 * Hook into actions and filters.
	 */
    public function init_hooks(){
        add_action('rest_api_init', array($this,'bamobile_add_api_routes'));
        add_filter('rest_api_init', array($this,'bamobile_add_cors_support'));
        add_filter('determine_current_user',array($this,'bamobile_determine_current_user'),10);
        add_filter('rest_pre_dispatch', array($this, 'bamobile_rest_pre_dispatch'), 10, 3);
    }

    /**
     * Create api or field
     */
    public function bamobile_add_api_routes() {
        register_rest_route($this->namespace, '/token', array(
            'methods' => 'POST',
            'callback' => array($this, 'bamobile_generate_token'),
            'args' => array(
                'username' => array(
                    'required' => true,
                    'sanitize_callback' => 'esc_sql'
                ),
                'password' => array(
                    'required' => true,
                    'sanitize_callback' => 'esc_sql'
                )
                
            ),
        ));

        register_rest_route($this->namespace, '/token/validate', array(
            'methods' => 'POST',
            'callback' => array($this, 'bamobile_validate_token'),
        ));
    }

    /**
     * Add CORs suppot to the request.
     */
    public function bamobile_add_cors_support()  {
        $enable_cors = defined('JWT_AUTH_CORS_ENABLE') ? JWT_AUTH_CORS_ENABLE : false;
        if ($enable_cors) {
            $headers = apply_filters('mobiconnector_jwt_auth_cors_allow_headers', 'Access-Control-Allow-Headers, Content-Type, Authorization');
            header(sprintf('Access-Control-Allow-Headers: %s', $headers));
        }
    }

    /**
     * Create token by username and password
     * 
     * @param WP_REST_Request  $request   current Request
     * 
     * @return array data of Token
     */
    public function bamobile_generate_token($request) {
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        $params = $request->get_params();
        $username = $params['username'];
        $password = $params['password'];

        /** First thing, check the secret key if not exist return a error*/
        if (!$secret_key) {
            return new WP_Error(
                'mobiconnector_jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'mobiconnector'),
                array(
                    'status' => 403,
                )
            );
        }

        if(!username_exists($username) && !email_exists($username)) {
			return new WP_Error( 'username_invalid', __('Username or password invalid','mobiconnector'), array( 'status' => 403 ) );
        }			
        	
        /** Try to authenticate the user with the passed credentials*/
        $user = wp_authenticate($username,$password); 

        /** If the authentication fails return a error*/
        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();
            return $user;
            if($error_code == 'incorrect_password'){
                return new WP_Error( 'username_invalid', __('Username or password invalid','mobiconnector'), array( 'status' => 403 ) );
            }
        }

        /** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters('mobiconnector_jwt_auth_not_before', $issuedAt, $issuedAt);
        $expire = apply_filters('mobiconnector_jwt_auth_expire', $issuedAt + (YEAR_IN_SECONDS * 10), $issuedAt);

        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => array(
                'user' => array(
                    'id' => $user->data->ID,
                ),
            ),
        );

        /** Let the user modify the token data before the sign. */
        $token = BAMobile_JWT::encode(apply_filters('mobiconnector_jwt_auth_token_before_sign', $token, $user), $secret_key);

        /** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
            'token' => $token,
            'user_email' => $user->data->user_email,
            'user_nicename' => $user->data->user_nicename,
            'user_display_name' => $user->data->display_name,
        );

        /** Let the user modify the data before send it back */
        return apply_filters('mobiconnector_jwt_auth_token_before_dispatch', $data, $user);
    }

    /**
     * Check user login
     * 
     * @param object  $user   user login
     * 
     * @return int User id
     */
    public function bamobile_determine_current_user($user) {
        /**
         * This hook only should run on the REST API requests to determine
         * if the user in the Token (if any) is valid, for any other
         * normal call ex. wp-admin/.* return the user.
         *
         * @since 1.2.3
         **/
        $rest_api_slug = rest_get_url_prefix();
        $valid_api_uri = strpos($_SERVER['REQUEST_URI'], $rest_api_slug);
        if(!$valid_api_uri){
            return $user;
        }

        /*
         * if the request URI is for validate the token don't do anything,
         * this avoid double calls to the validate_token function.
         */
        $validate_uri = strpos($_SERVER['REQUEST_URI'], 'token/validate');
        if ($validate_uri > 0) {
            return $user;
        }

        $token = $this->bamobile_validate_token(false);

        if (is_wp_error($token)) {
            if ($token->get_error_code() != 'jwt_auth_no_auth_header') {
                /** If there is a error, store it to show it after see rest_pre_dispatch */
                $this->jwt_error = $token;
                return $user;
            } else {
                return $user;
            }
        }
        /** Everything is ok, return the user ID stored in the token*/
        return $token->data->user->id;
    }

    /**
     * Try to get the Autentication headers and decoded.
     *
     * @param bool $output
     *
     * @return WP_Error | Object | Array
     */
    public function bamobile_validate_token($output = true) {
        /*
         * Looking for the HTTP_AUTHORIZATION header, if not present just
         * return the user.
         */
        $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ?  $_SERVER['HTTP_AUTHORIZATION'] : false;


        /* Double check for different auth header string (server dependent) */
        if (!$auth) {
            $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ?  $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
        }

        if (!$auth) {
            return new WP_Error(
                'jwt_auth_no_auth_header',
                __('Authorization header not found.', 'mobiconnector'),
                array(
                    'status' => 403,
                )
            );
        }

        /*
         * The HTTP_AUTHORIZATION is present verify the format
         * if the format is wrong return the user.
         */
        list($token) = sscanf($auth, 'Bearer %s');
        if (!$token) {
            return new WP_Error(
                'jwt_auth_bad_auth_header',
                __('Authorization header malformed.', 'mobiconnector'),
                array(
                    'status' => 403,
                )
            );
        }

        /** Get the Secret Key */
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
        if (!$secret_key) {
            return new WP_Error(
                'jwt_auth_bad_config',
                __('JWT is not configurated properly, please contact the admin', 'mobiconnector'),
                array(
                    'status' => 403,
                )
            );
        }

        /** Try to decode the token */
        try {
            $token = BAMobile_JWT::decode($token, $secret_key, array('HS256'));
            /** The Token is decoded now validate the iss */
            if ($token->iss != get_bloginfo('url')) {
                /** The iss do not match, return error */
                return new WP_Error(
                    'jwt_auth_bad_iss',
                    __('The iss do not match with this server', 'mobiconnector'),
                    array(
                        'status' => 403,
                    )
                );
            }
            /** So far so good, validate the user id in the token */
            if (!isset($token->data->user->id)) {
                /** No user id in the token, abort!! */
                return new WP_Error(
                    'jwt_auth_bad_request',
                    __('User ID not found in the token', 'mobiconnector'),
                    array(
                        'status' => 403,
                    )
                );
            }
            /** Everything looks good return the decoded token if the $output is false */
            if (!$output) {
                return $token;
            }
            /** If the output is true return an answer to the request to show it */
             return array(
                 'code' => 'jwt_auth_valid_token',
                 'data' => array(
                     'status' => 200,
                 ),
             );
         } catch (Exception $e) {
            /** Something is wrong trying to decode the token, send back the error */
             return new WP_Error(
                 'jwt_auth_invalid_token',
                 $e->getMessage(),
                 array(
                     'status' => 403,
                 )
             );
         }
    }

    /**
     * Filter to hook the rest_pre_dispatch, if the is an error in the request
     * @param mixed           $result     Response to replace the requested version with. 
     *                                    Can be anything a normal endpoint can return, or null to not hijack the request.
     * @param WP_REST_Server  $server     Server instance.
     * @param WP_REST_Request $request    Request used to generate the response.
     * 
     * @return mixed
     */
    public function bamobile_rest_pre_dispatch($result, $server, $request) {
        if (is_wp_error($this->jwt_error)) {
            return $this->jwt_error;
        }
        return $result;
    }
}
$BAMobile_JWT_Auth = new BAMobile_JWT_Auth();

/**
 * This Class will be removed after Jan 01, 2019
 */
class Mobiconnector_JWT_Auth extends BAMobile_JWT_Auth{

    /**
     * Create token by username and password
     * 
     * @param WP_REST_Request  $request   current Request
     * 
     * @return array data of Token
     */
    public function generate_token($request) {
        return $this->bamobile_generate_token($request);
    }

     /**
     * Check user login
     * 
     * @param object  $user   user login
     * 
     * @return int User id
     */
    public function determine_current_user($user) {
        return $this->bamobile_determine_current_user($user);
    }
}
$Mobiconnector_JWT_Auth  = new Mobiconnector_JWT_Auth();
?>