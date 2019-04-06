<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
include_once(WOOCONNECTOR_ABSPATH.'templates/error.php');
if(isset($_GET["data_key"])){			
	$key_data = $_GET["data_key"];	
	global $wpdb;
	$woocomerce = ($GLOBALS["woocommerce"]) ? $GLOBALS["woocommerce"] : WC();
	$table_name = $wpdb->prefix . "wooconnector_data";
	$key_data = esc_sql($key_data);
	$datas = $wpdb->get_results(
		"
		SELECT * 
		FROM $table_name
		WHERE data_key = '$key_data'
		"
	);		
	if(!empty($datas)){
		foreach($datas as $values){
			$val = $values->data;
			$orderid = $values->order_id;
			$data = (array) unserialize($val);			
		}			
		$order = new WC_Order($orderid);	
		$orderstatus = $order->get_status();
		$keycheckuser = false;
		if(empty($orderid) || $orderid == null || $orderstatus == 'pending'){
			if(is_plugin_active('woocommerce-product-addons/woocommerce-product-addons.php')){
				require_once(WOOCONNECTOR_ABSPATH.'hooks/class-wooconnector-addons.php');
			}
			$key_session = $data["session_key"];	
			$user = get_user_by( 'id', $key_session ); 			
			if( $user ) {
				if(!is_user_logged_in()){
					wp_set_current_user( $key_session, $user->user_login );
					wp_set_auth_cookie( $key_session , true );
					do_action( 'wp_login', $user->user_login, $user );
					$page = $_SERVER["REQUEST_URI"];
					header("Refresh: 0; url=$page");
				}else{
					$keycheckuser = true;
				}
			}			
			$pro = $data["products"];
			$products = json_decode($pro);
			$woocomerce->session->set( 'refresh_totals', true );
			$woocomerce->cart->empty_cart();
			$attrs = array();
			foreach($products as $product){			
				$product_id = absint($product->product_id);			
				$quantity = $product->quantity;									
				$variation_id = isset($product->variation_id) ? absint($product->variation_id) : 0;	
				$attributes = isset($product->attributes) ? $product->attributes : array();	
				$addons = isset($product->addons) ? $product->addons : array();		
				if(!empty($variation_id) || $variation_id != '' || $variation_id != null || $variation_id !== 0){				
					if(!empty($attributes)){
						foreach($attributes as $key => $val){
							$attrs[$key] = $val;
						}
						$woocomerce->cart->add_to_cart($product_id,$quantity,$variation_id,$attrs);
					}else{
						$wcvp = new WC_Product_Variable($product_id);
						$gavs = $wcvp->get_available_variations();
						foreach($gavs as $gav => $value){							
							if($value['variation_id'] == $variation_id){
								$attrs = $value['attributes'];						
								$woocomerce->cart->add_to_cart($product_id,$quantity,$variation_id,$attrs);							
							}
						}
					}
				}				
				else{		
					$woocomerce->cart->add_to_cart($product_id,$quantity,$variation_id,$attributes);						
				}	
			}
			
			if(!empty($data['coupons'])){
				$cou = $data['coupons'];
				$coupons = json_decode($cou);					
				foreach($coupons as $coupon){
					$woocomerce->cart->add_discount($coupon);
				}	
			}
			$bfn = $data['billing_first_name'];
			$bln = $data['billing_last_name'];
			isset($data['billing_company']) ? $bcp = $data['billing_company'] : $bcp = '';
			$bct = $data['billing_country'];
			$ba1 = $data['billing_address_1'];
			isset($data['billing_address_2']) ? $ba2 = $data['billing_address_2'] : $ba2 = ' ';
			isset($data['billing_state']) ? $bs = $data['billing_state'] : $bs = '';
			$bc = $data['billing_city'];
			$bp = $data['billing_phone'];
			$be = $data['billing_email'];
			$bpc = $data['billing_postcode'];;
			$sm = $data['shipping_method'];
			$pm = $data['payment_method'];	
			isset($data['shipping_first_name']) ? $sfn = $data['shipping_first_name'] : $sfn = '';
			isset($data['shipping_last_name']) ? $sln = $data['shipping_last_name'] : $sln = '';
			isset($data['shipping_company']) ? $scp = $data['shipping_company'] : $scp = '';
			isset($data['shipping_country']) ? $sct = $data['shipping_country'] : $sct = '';
			isset($data['shipping_address_1']) ? $sa1 = $data['shipping_address_1'] : $sa1 = '';
			isset($data['shipping_address_2']) ? $sa2 = $data['shipping_address_2'] : $sa2 = '';
			isset($data['shipping_city']) ? $sc = $data['shipping_city'] : $sc = '';
			isset($data['shipping_state']) ? $ss = $data['shipping_state'] : $ss = '';	
			isset($data['shipping_postcode']) ? $sp = $data['shipping_postcode'] : $sp = '';
			isset($data['order_comments']) ? $oc = $data['order_comments'] : $oc = '';	
			isset($data['onesignal_player_id']) ? $onesignal_player_id = $data['onesignal_player_id'] : $onesignal_player_id = '';						
			$stripecheckout = $data['stripecheckout'];
			$finalsm = str_replace( ':', '', $sm );	
			$checkpayment = false;
			$use_form = isset($data['use_form']) ? $data['use_form'] : false;
			if($pm == 'stripe' && $stripecheckout != 'yes'){
				$checkpayment = true;
			}
			if(!$checkpayment){
				echo "<div style='position: absolute; top:0px; width:100%;text-align:center;'>Please Wait..</div>";
			}		
?> 
<!DOCTYPE html>
<html <?php language_attributes(); ?> >
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">	
<?php wp_head(); ?>
<style type="text/css">
	.apple-pay-button-wrapper, .apple-pay-button-checkout-separator,.woocommerce-info, #wpadminbar, #wp-live-chat{
		display:none !important;
	}
</style>
<?php
	if($checkpayment){
		echo '
			<style type="text/css">
				#order_review{
					float:left !important;
				}
				.wc_payment_method,.wc_payment_method > label{
					display:none !important;
				}
				.payment_method_'.$pm.'{
					display:block !important;
				}
			</style>
		';
	}
?>
</head>
<body <?php body_class(); ?> >

<div id="page" class="site" <?php if($checkpayment){echo 'style="display:block !important;"';}else{echo 'style="display:none !important;"';} ?>>      

	<div class="site-content-contain">
		<div id="content" class="site-content">

			<div class="wrap">
				<div id="primary" class="content-area">
					<main id="main" class="site-main" role="main">

						<article id="post-6" class="post-6 page type-page status-publish hentry">                               
							<div class="entry-content">
								<div class="woocommerce">
									<?php
										wc_print_notices();
									?>   
									<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo get_bloginfo('url') ?>/checkout/" enctype="multipart/form-data">
										<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
										<?php if(is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php')) && $use_form == 1){ ?>
										<div class="col2-set" id="customer_details" <?php if($checkpayment){echo 'style="display:none !important;"';} ?>>
											<div class="col-1">
												<div class="woocommerce-billing-fields">

													<h3>Billing details</h3>
													
													<div class="woocommerce-billing-fields__field-wrapper">
														<?php 	
															foreach($data as $keyb => $valb){ 																
																if(strpos($keyb,'billing_') !== false){
																	if(!empty($valb['class'])){
																		$class = implode(' ',$valb['class']);
																		if(isset($valb['required']) && $valb['required'] == true){
																			$class .= ' validate-required';
																		}
																	}
														?>
														<p class="form-row <?php echo $class; ?>" id="<?php echo $keyb;?>_field" data-priority="<?php echo $valb['priority'];?>">
															<label for="<?php echo $keyb;?>" class=""><?php if(isset($valb['label'])){echo $valb['label'];} ?><?php if($valb['required'] == true){ echo '<abbr class="required" title="required">*</abbr>'; } ?>
															</label>
															<input type="text" class="input-text" name="<?php echo $keyb; ?>" id="<?php echo $keyb; ?>" placeholder="" value="<?php if(isset($valb['value'])){ echo $valb['value'];} ?>"  />
														</p>		
														<?php }} ?>																			
													</div>

												</div>

											</div>

											<div class="col-2">
												<div class="woocommerce-shipping-fields">

													<h3 id="ship-to-different-address">
														<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
															<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"  type="checkbox" name="ship_to_different_address" value="1" /> <span>Ship to a different address?</span>
														</label>
													</h3>

													<div class="shipping_address">


														<div class="woocommerce-shipping-fields__field-wrapper">
															<?php 	foreach($data as $keys => $vals){ 
																if(strpos($keys,'shipping_') !== false && $keys !== 'shipping_method'){
																	$classs = '';
																	if(!empty($vals['class'])){
																		$classs = implode(' ',$vals['class']);
																		if(isset($vals['required']) && $vals['required'] == true){
																			$classs .= ' validate-required';
																		}
																	}
															?>
															<p class="form-row <?php echo $classs; ?>" id="<?php echo $keys;?>_field" data-priority="<?php echo $vals['priority'];?>">
																<label for="<?php echo $keys;?>" class=""><?php if(isset($vals['label'])){ echo $vals['label'];} ?><?php if($vals['required'] == true){ echo '<abbr class="required" title="required">*</abbr>'; } ?>
																</label>
																<input type="text" class="input-text" name="<?php echo $keys; ?>" id="<?php echo $keys; ?>" placeholder="" value="<?php if(isset($vals['value'])){echo $vals['value'];} ?>"  />
															</p>		
															<?php }} ?>		
														</div>
													</div>

												</div>
												<?php do_action( 'woocommerce_before_order_notes'); ?>
												<div class="woocommerce-additional-fields">
													<div class="woocommerce-additional-fields__field-wrapper">
														<p class="form-row notes" id="order_comments_field" data-priority="">
															<label for="order_comments" class="">Order notes</label>
															<textarea name="order_comments" class="input-text " id="order_comments" placeholder="Notes about your order, e.g. special notes for delivery." rows="2" cols="5"><?php echo $oc; ?></textarea>
														</p>
													</div>
												</div>
												<?php do_action( 'woocommerce_after_order_notes' ); ?>
											</div>
										</div>
										<?php }else{ ?>
											<div class="col2-set" id="customer_details" <?php if($checkpayment){echo 'style="display:none !important;"';} ?>>
											<div class="col-1">
												<div class="woocommerce-billing-fields">

													<h3>Billing details</h3>
													
													<div class="woocommerce-billing-fields__field-wrapper">
														<p class="form-row form-row-first validate-required" id="billing_first_name_field" data-priority="10">
															<label for="billing_first_name" class="">First name <abbr class="required" title="required">*</abbr>
															</label>
															<input type="hidden" class="input-text " name="billing_first_name" id="billing_first_name" placeholder="" value="<?php echo $bfn; ?>"  />
														</p>
														<p class="form-row form-row-last validate-required" id="billing_last_name_field" data-priority="20">
															<label for="billing_last_name" class="">Last name <abbr class="required" title="required">*</abbr>
															</label>
															<input type="hidden" class="input-text " name="billing_last_name" id="billing_last_name" placeholder="" value="<?php echo $bln; ?>"  />
														</p>
														<p class="form-row form-row-wide" id="billing_company_field" data-priority="30">
															<label for="billing_company" class="">Company name</label>
															<input type="hidden" class="input-text " name="billing_company" id="billing_company" placeholder="" value="<?php echo $bcp; ?>"  />
														</p>
														<p class="form-row form-row-wide address-field update_totals_on_change validate-required" id="billing_country_field" data-priority="40">
															<label for="billing_country" class="">Country <abbr class="required" title="required">*</abbr>
															</label>
																<input type="hidden" class="input-text "name="billing_country" id="billing_country" placeholder="" value="<?php echo $bct; ?>" />                                                             
															
														</p>
														<p class="form-row form-row-wide address-field validate-required" id="billing_address_1_field" data-priority="50">
															<label for="billing_address_1" class="">Address <abbr class="required" title="required">*</abbr>
															</label>
															<input type="hidden" class="input-text " name="billing_address_1" id="billing_address_1" placeholder="Street address" value="<?php echo $ba1; ?>"  />
														</p>
														<p class="form-row form-row-wide address-field" id="billing_address_2_field" data-priority="60">
															<input type="hidden" class="input-text " name="billing_address_2" id="billing_address_2" placeholder="Apartment, suite, unit etc. (optional)" value="<?php echo $ba2; ?>"  />
														</p>
														<p class="form-row form-row-wide address-field validate-required" id="billing_city_field" data-priority="70">
															<label for="billing_city" class="">Town / City <abbr class="required" title="required">*</abbr>
															</label>
															<input type="hidden" class="input-text " name="billing_city" id="billing_city" placeholder="" value="<?php echo $bc; ?>"  />
														</p>
														<p class="form-row form-row-wide address-field validate-state" id="billing_state_field" style="display: none">
															<label for="billing_state" class="">State / County</label>
															<input type="hidden" class="hidden" name="billing_state" id="billing_state" value="<?php echo $bs; ?>" />
														</p>
														<p class="form-row form-row-wide address-field validate-postcode" id="billing_postcode_field" data-priority="65">
															<label for="billing_postcode" class="">Postcode / ZIP</label>
															<input type="hidden" class="input-text " name="billing_postcode" id="billing_postcode" placeholder="" value="<?php echo $bpc; ?>"  />
														</p>
														<p class="form-row form-row-first validate-phone" id="billing_phone_field" data-priority="100">
															<label for="billing_phone" class="">Phone</label>
															<input type="hidden" class="input-text " name="billing_phone" id="billing_phone" placeholder="" value="<?php echo $bp; ?>"  />
														</p>
														<p class="form-row form-row-last validate-required validate-email" id="billing_email_field" data-priority="110">
															<label for="billing_email" class="">Email address <abbr class="required" title="required">*</abbr>
															</label>
															<input type="hidden" class="input-text " name="billing_email" id="billing_email" placeholder="" value="<?php echo $be; ?>"  />
														</p>																						
													</div>

												</div>

											</div>

											<div class="col-2">
												<div class="woocommerce-shipping-fields">

													<h3 id="ship-to-different-address">
														<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
															<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"  type="checkbox" name="ship_to_different_address" value="1" /> <span>Ship to a different address?</span>
														</label>
													</h3>

													<div class="shipping_address">


														<div class="woocommerce-shipping-fields__field-wrapper">
															<p class="form-row form-row-first validate-required" id="shipping_first_name_field" data-priority="10">
																<label for="shipping_first_name" class="">First name <abbr class="required" title="required">*</abbr>
																</label>
																<input type="hidden" class="input-text " name="shipping_first_name" id="shipping_first_name" placeholder="" value="<?php echo $sfn; ?>"  />
															</p>
															<p class="form-row form-row-last validate-required" id="shipping_last_name_field" data-priority="20">
																<label for="shipping_last_name" class="">Last name <abbr class="required" title="required">*</abbr>
																</label>
																<input type="hidden" class="input-text " name="shipping_last_name" id="shipping_last_name" placeholder="" value="<?php echo $sln; ?>"  />
															</p>
															<p class="form-row form-row-wide" id="shipping_company_field" data-priority="30">
																<label for="shipping_company" class="">Company name</label>
																<input type="hidden" class="input-text " name="shipping_company" id="shipping_company" placeholder="" value="<?php echo $scp; ?>"  />
															</p>
															<p class="form-row form-row-wide address-field update_totals_on_change validate-required" id="shipping_country_field" data-priority="40">
																<label for="shipping_country" class="">Country <abbr class="required" title="required">*</abbr>
																</label>
																	<input type="hidden" class="input-text " name="shipping_country" id="shipping_country" placeholder="" value="<?php echo $sct; ?>"  />                                                                    
															</p>
															<p class="form-row form-row-wide address-field validate-required" id="shipping_address_1_field" data-priority="50">
																<label for="shipping_address_1" class="">Address <abbr class="required" title="required">*</abbr>
																</label>
																<input type="hidden" class="input-text " name="shipping_address_1" id="shipping_address_1" placeholder="Street address" value="<?php echo $sa1; ?>"  />
															</p>
															<p class="form-row form-row-wide address-field" id="shipping_address_2_field" data-priority="60">
																<input type="hidden" class="input-text " name="shipping_address_2" id="shipping_address_2" placeholder="Apartment, suite, unit etc. (optional)" value="<?php echo $sa2; ?>"  />
															</p>
															<p class="form-row form-row-wide address-field validate-required" id="shipping_city_field" data-priority="70">
																<label for="shipping_city" class="">Town / City <abbr class="required" title="required">*</abbr>
																</label>
																<input type="hidden" class="input-text " name="shipping_city" id="shipping_city" placeholder="" value="<?php echo $sc; ?>"  />
															</p>
															<p class="form-row form-row-wide address-field validate-state" id="shipping_state_field" style="display: none">
																<label for="shipping_state" class="">State / County</label>
																<input type="hidden" class="hidden" name="shipping_state" id="shipping_state" value="<?php echo $ss; ?>"  placeholder="" />
															</p>
															<p class="form-row form-row-wide address-field validate-postcode" id="shipping_postcode_field" data-priority="65">
																<label for="shipping_postcode" class="">Postcode / ZIP</label>
																<input type="hidden" class="input-text " name="shipping_postcode" id="shipping_postcode" placeholder="" value="<?php echo $sp; ?>" />
															</p>
														</div>


													</div>

												</div>
												<?php do_action( 'woocommerce_before_order_notes'); ?>
												<div class="woocommerce-additional-fields">
													<div class="woocommerce-additional-fields__field-wrapper">
														<p class="form-row notes" id="order_comments_field" data-priority="">
															<label for="order_comments" class="">Order notes</label>
															<textarea name="order_comments" class="input-text " id="order_comments" placeholder="Notes about your order, e.g. special notes for delivery." rows="2" cols="5"><?php echo $oc; ?></textarea>
														</p>
													</div>
												</div>
												<?php do_action( 'woocommerce_after_order_notes' ); ?>
											</div>
										</div>
										<?php } ?>
										<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
										
										<h3 id="order_review_heading" <?php if($checkpayment){echo 'style="display:none !important;"';} ?>><?php _e( 'Your order', 'woocommerce' ); ?></h3>

										<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
										
										<input type="hidden" name="check_wooconnector" value="1"/>
										<input type="hidden" name="wooconnector_check_user_agent" value="<?php echo strtolower($_SERVER['HTTP_USER_AGENT']); ?>"/>
										
										<input type="hidden" name="wooconnector_key_order" value="<?php echo $key_data; ?>"/>	
										<input type="hidden" name="wooconnector_setting_customer" value="<?php echo $key_session; ?>"/>
										<input type="hidden" name="onesignal_player_id" placeholder="" value="<?php echo $onesignal_player_id; ?>"  />

										<div id="order_review" class="woocommerce-checkout-review-order">
											<table class="shop_table" <?php if($checkpayment){echo 'style="display:none !important;"';} ?>>
												<thead>
													<tr>
														<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
														<th class="product-total"><?php _e( 'Total', 'woocommerce' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
														foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
															$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

															if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
																?>
																<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
																	<td class="product-name">
																		<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
																		<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
																		<?php echo WC()->cart->get_item_data( $cart_item ); ?>
																	</td>
																	<td class="product-total">
																		<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
																	</td>
																</tr>
																<?php
															}
														}
													?>
												</tbody>
												<tfoot>
													<tr class="cart-subtotal">
														<th><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
														<td><?php wc_cart_totals_subtotal_html(); ?></td>
													</tr>

													<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
														<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
															<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
															<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
														</tr>
													<?php endforeach; ?>

													<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>

														<tr class="shipping">
															<th>Shipping</th>
															<td><input type="radio" checked="checked" class="shipping_method" name="shipping_method[]" id="shipping_method__<?php echo $finalsm?>" value="<?php echo $sm; ?>"/></td>
														</tr>
														
													<?php endif; ?>

													<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
														<tr class="fee">
															<th><?php echo esc_html( $fee->name ); ?></th>
															<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
														</tr>
													<?php endforeach; ?>

													<?php if ( wc_tax_enabled() && 'excl' === WC()->cart->tax_display_cart ) : ?>
														<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
															<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
																<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
																	<th><?php echo esc_html( $tax->label ); ?></th>
																	<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
																</tr>
															<?php endforeach; ?>
														<?php else : ?>
															<tr class="tax-total">
																<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
																<td><?php wc_cart_totals_taxes_total_html(); ?></td>
															</tr>
														<?php endif; ?>
													<?php endif; ?>

													<tr class="order-total">
														<th><?php _e( 'Total', 'woocommerce' ); ?></th>
														<td><?php wc_cart_totals_order_total_html(); ?></td>
													</tr>

												</tfoot>
											</table>
											<?php
												if ( ! is_ajax() ) {
													do_action( 'woocommerce_review_order_before_payment' );
												}
											?>
											<div id="payment" <?php if($checkpayment){ echo 'class="woocommerce-checkout-payment"'; } ?>>
											
													<input <?php if($checkpayment){echo 'style="display:none !important;"';} ?>  type="radio" name="payment_method" id="payment_method_<?php echo $pm; ?>" checked="checked" value="<?php echo $pm; ?>"/>
													
													<input <?php if($checkpayment){echo 'style="display:none !important;"';} ?> type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" checked="checked" name="terms" id="terms">														
													
													<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
													
													<?php if(!$checkpayment){ echo apply_filters( 'woocommerce_order_button_html', '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="" />' );} ?>
													
													<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

													<?php 
													/*if(WC_VERSION > '3.3'){
														wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' );
													}else{*/
														wp_nonce_field( 'woocommerce-process_checkout' );
													//}
													?>
											</div>
											<?php
												if ( ! is_ajax() ) {
													do_action( 'woocommerce_review_order_after_payment' );
												}
											?>
										</div>

										<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

									</form>
									
								</div>
							</div>
							<!-- .entry-content -->
						</article>
						<!-- #post-## -->

					</main>
					<!-- #main -->
				</div>
				<!-- #primary -->
			</div>
			<!-- .wrap -->


		</div>
		<!-- #content -->

	</div>
	<!-- .site-content-contain -->
</div>
<!-- #page -->
<?php 			
wp_footer(); ?>
<script type="text/javascript">	
	<?php
		$stda = isset($data['ship_to_different_address']) ? $data['ship_to_different_address'] : false;
		if(is_plugin_active('woocommerce/woocommerce.php') && (is_plugin_active('ba-mobile-form/ba-mobile-form.php') || bamobile_is_extension_active('ba-mobile-form/ba-mobile-form.php'))){
			if(!empty($stda) && $stda > 0){
		?>
				document.getElementById('ship-to-different-address-checkbox').click();
		<?php
			}
		}else{
			if(!empty($stda) && $stda === 0){
		?>
				document.getElementById('ship-to-different-address-checkbox').click();
		<?php
			}
		}
		if((int)$key_session > 0 && $keycheckuser == true && !$checkpayment || (int)$key_session == 0 && !$checkpayment){
	?>
		setTimeout(function(){				
			document.getElementById('place_order').click();
		},1000);
	<?php } ?>
		
	setTimeout(function(){
		
		document.getElementById('overlay').style.display = 'none';
			
	},500000);
</script>
<div id="overlay" <?php if($checkpayment){echo 'style="display:none !important;"';} ?>></div>

</body>

</html>

<?php					
			}
			else{
				echo order401();	
				die;
			}
		}
		else{
			echo datakey401();
			die;
		}

	} else {
		echo error404();
		die;	
	}

?>