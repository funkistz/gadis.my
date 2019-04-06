<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'WP_REST_Controller' ) ) {
	require_once(ABSPATH.'wp-content/plugins/rest-api/lib/endpoints/class-wp-rest-controller.php');
}
class WooConnectorCoupon extends  WP_REST_Controller{
	private $rest_url = 'wooconnector/coupon';	
	public function __construct() {
		$this->register_routes();
	}
	public function register_routes() {
		add_action( 'rest_api_init', array( $this, 'register_api_hooks'));
	}
	public function register_api_hooks() {
		// check coupon
		register_rest_route( $this->rest_url, '(?P<code>\w[ \w\s\-]*)', array(
				array(
					'methods'         => 'GET',
					'callback'        => array( $this, 'get_coupon' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args' => array(
						'code' => array(
							'required' => true,
							'sanitize_callback' => 'esc_sql'
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

	public function get_coupon( $request ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return new WP_Error( 'woocommerce_deactive', __( 'Woocommerce Deactive','wooconnector' ), array( 'status' => 400 ) );
		}
		$parameters = $request->get_params();
		$coupon = new WC_Coupon( $parameters["code"] );	
		$data = $coupon->get_data();
		$format_decimal = array( 'amount', 'minimum_amount', 'maximum_amount' );
		$format_date    = array( 'date_created', 'date_modified', 'date_expires' );
		$format_null    = array( 'usage_limit', 'usage_limit_per_user', 'limit_usage_to_x_items' );

		// Format decimal values.
		foreach ( $format_decimal as $key ) {
			$data[ $key ] = wc_format_decimal( $data[ $key ], 2 );
		}

		// Format date values.
		foreach ( $format_date as $key ) {
			$datetime 	  			= $data[ $key ];
			$data[ $key ] 			= wc_rest_prepare_date_response( $datetime, false );
			$data[ $key . '_gmt' ] 	= wc_rest_prepare_date_response( $datetime );
		}

		// Format null values.
		foreach ( $format_null as $key ) {
			$data[ $key ] = $data[ $key ] ? $data[ $key ] : null;
		}

		return array(
			'id'                          => $coupon->get_id(),
			'code'                        => $data['code'],
			'amount'                      => $data['amount'],
			'date_created'                => $data['date_created'],
			'date_created_gmt'            => $data['date_created_gmt'],
			'date_modified'               => $data['date_modified'],
			'date_modified_gmt'           => $data['date_modified_gmt'],
			'discount_type'               => $data['discount_type'],
			'description'                 => $data['description'],
			'date_expires'                => $data['date_expires'],
			'date_expires_gmt'            => $data['date_expires_gmt'],
			'usage_count'                 => $data['usage_count'],
			'individual_use'              => $data['individual_use'],
			'product_ids'                 => $data['product_ids'],
			'excluded_product_ids'        => $data['excluded_product_ids'],
			'usage_limit'                 => $data['usage_limit'],
			'usage_limit_per_user'        => $data['usage_limit_per_user'],
			'limit_usage_to_x_items'      => $data['limit_usage_to_x_items'],
			'free_shipping'               => $data['free_shipping'],
			'product_categories'          => $data['product_categories'],
			'excluded_product_categories' => $data['excluded_product_categories'],
			'exclude_sale_items'          => $data['exclude_sale_items'],
			'minimum_amount'              => $data['minimum_amount'],
			'maximum_amount'              => $data['maximum_amount'],
			'email_restrictions'          => $data['email_restrictions'],
			'used_by'                     => $data['used_by'],
			'meta_data'                   => $data['meta_data'],
		);			
	}
}
$WooConnectorCoupon = new WooConnectorCoupon();
?>