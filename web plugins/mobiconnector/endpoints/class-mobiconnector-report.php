<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Create Api report for user
 */
class BAMobileReport{

    /**
     * Url of API
     */
    private $rest_url = 'mobiconnector/report';	

    /**
     * MobiConnectorLanguage construct
     */
    public function __construct(){
        $this->register_routes();
    }

    /**
	 * Hook into actions and filters.
	 */
    public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
    }
    
    /**
	 * Create Api or add field
	 */
	public function register_api_hooks() {

		register_rest_route( $this->rest_url, '/send', array(
				array(
					'methods'         => 'POST',
                    'callback'        => array( $this, 'bamobile_sendreport' ),
                    'permission_callback' => array( $this, 'bamobile_get_items_permissions_check' ),				
					'args' => array(
						'reportid' => array(
                            'required' => true,
							'sanitize_callback' => 'absint'
                        ),
                        'flag_reason' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
                        ),
                        'type' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
                        ),
					),
				)
			) 
        );
    }

    /**
	 * Check if a given request has access to read items.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function bamobile_get_items_permissions_check( $request ) {
		$usekey = get_option('mobiconnector_settings-use-security-key');
		if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
			return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

    /**
     * Get message and infomation when user input and then send mail to email Administrator or email configured in admin
     * 
     * @param WP_REST_Request $request  current Request
     * 
     * @return mixed
     */
    public function bamobile_sendreport($request){
        global $wpdb;
        $params = $request->get_params();
        $type = sanitize_text_field($params['type']);
        $paramsid = sanitize_text_field($params['reportid']);
        if($type == 'post'){
            $post = get_post($paramsid);
            if(empty($post) || empty($post->ID) || $post->post_type != 'post' ){
                return new WP_Error( 'rest_post_error', __( 'Post is Invalid.','wooconnector' ), array( 'status' => 401 ) );
            }else{
                $post_or_comment = sanitize_text_field($post->post_title);
                $link = sanitize_text_field($post->guid);
            }
        }elseif($type == 'comment'){
            $comment = get_comment($paramsid);
            if(empty($comment) || empty($comment->comment_ID)){
                return new WP_Error( 'rest_comment_error', __( 'Comment is Invalid.','wooconnector' ), array( 'status' => 401 ) );
            }else{
                $commentcontent = $comment->comment_content;
                $idpost = $comment->comment_post_ID;
                $post = get_post($idpost);
                $link = $post->guid;
                $post_or_comment = sanitize_text_field($commentcontent);
                $link = sanitize_text_field($link);
            }
        }
        $flag_reason = esc_textarea($params['flag_reason']);
        $toemail = strip_tags(get_option('mobiconnector_settings-mail'));
        $subject = __('An user has flagged content on mobile application','mobiconnector');
        $sql = "SELECT * FROM ".$wpdb->prefix."users AS u INNER JOIN ".$wpdb->prefix."usermeta AS um ON u.ID = um.user_id WHERE um.meta_value LIKE '%administrator%' ORDER BY u.ID ASC LIMIT 1";
        $user = $wpdb->get_results($sql,ARRAY_A);
        $user = $user[0]['user_email'];
        $fromemail = $user;
        $sendmes = '<html><body>';
        if($type == 'post'){
            $sendmes .= "<strong>Post Title</strong>: " . strip_tags($post_or_comment) ."<br>";
            $sendmes .= "<strong>Post link</strong>: " . strip_tags($link)."<br>";
        }elseif($type == 'comment'){
            $sendmes .= "<strong>Comment</strong>: " . strip_tags($post_or_comment) ."<br>";
            $sendmes .= "<strong>Comment link</strong>: " . strip_tags($link)."<br>";
        }
		$sendmes .= "<strong>Flag reason</strong>: ";
        $sendmes .= "<p>".nl2br($flag_reason)."</p>";
        $sendmes .= '</body></html>';
		$headers = "From: <$fromemail>" . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .=	'X-Mailer: PHP/' . phpversion();
        if(wp_mail($toemail, $subject, $sendmes, $headers)){
			return array(
				'result' => __('success','wooconnector'),
				'message' => __('You has just flagged this item.','wooconnector')
			);
		}
		else{
			return array(
				'result' => __('fail','wooconnector'),
				'message' => __('Something went wrong, go back and try again!','wooconnector')
			);
		}
    }

}
$BAMobileReport = new BAMobileReport();
?>