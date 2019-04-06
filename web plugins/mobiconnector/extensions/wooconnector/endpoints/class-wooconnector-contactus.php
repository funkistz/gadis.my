<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class WooConnectorContactUs{
	private $rest_url = 'wooconnector/contactus';	
	public function __construct() {
		$this->register_routes();
	}

	public function register_routes() {		
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	
	public function register_api_hooks() {
		// check coupon
		register_rest_route( $this->rest_url, '/sendmail', array(
				array(
					'methods'         => 'POST',
					'callback'        => array( $this, 'sendmail' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args' => array(
						'email' => array(
							'required' => true							
						),
						'name' => array(
							'required' => true							
						),
						'subject' => array(
							'required' => true							
						),
						'message' => array(
							'required' => true							
						)
						
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
	public function get_items_permissions_check( $request ) {
		if(is_plugin_active('mobiconnector/mobiconnector.php')){
			$usekey = get_option('mobiconnector_settings-use-security-key');
			if ($usekey == 1 && ! bamobile_mobiconnector_rest_check_post_permissions( $request ) ) {
				return new WP_Error( 'mobiconnector_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'mobiconnector' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}
		return true;
	}

	public function sendmail( $request ) {
		$parameters = $request->get_params();
		$tomail  = get_option('wooconnector_settings-mail');	
		$email   = sanitize_email($parameters['email']);	
		$sendmail = strip_tags($email);		
		$name    = sanitize_text_field($parameters['name']);
		$subject = sanitize_text_field($parameters['subject']);
		$message = esc_textarea($parameters['message']);
		$sendmes = '<html><body>';
		$sendmes .= "<strong>Name</strong>: " . strip_tags($name) ."<br>";
		$sendmes .= "<strong>Email</strong>: " . strip_tags($email)."<br>";
		$sendmes .= "<strong>Message</strong>: ";
		$sendmes .= "<p>".nl2br($message)."</p>";
		$sendmes .= '</body></html>';
		$headers = "From:$name <$sendmail>" . "\r\n";
		$headers .= 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .=	'X-Mailer: PHP/' . phpversion();
		if(wp_mail($tomail, $subject.' - '.$email, $sendmes, $headers)){
			return array(
				'result' => __('success','wooconnector'),
				'message' => __('Your message has been sent!','wooconnector')
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
$WooConnectorContactUs = new WooConnectorContactUs();
?>