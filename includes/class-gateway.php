<?php

class APWC_Gateway extends WC_Payment_Gateway {

	/**
	 * APWC_Gateway constructor.
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function __construct() {

		$this->id                 = 'aurpay';
		$this->title              = $this->get_option( 'title' );
		$this->icon               = apply_filters( 'wcap_icon', APWC_PLUGIN_URL . '/assets/images/icon.png' );
		$this->has_fields         = true;
		$this->method_title       = 'Aurpay';
		$this->description        = $this->get_option( 'description' );
		$this->public_key         = $this->get_option( 'public_key' );
		$this->has_fields         = false;
		$this->method_description = 'Allows customer to checkout with Aurpay.';
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_api_apwc_gateway', array( $this, 'ipn_callback' ) );

	}


	/**
	 * Admin form fields
	 *
	 * @since 1.0.1
	 * @version 1.0.1
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => 'Enabled/Disabled',
				'type'    => 'checkbox',
				'label'   => 'Enable Aurpay',
				'default' => 'no'
			),
			'title'      => array(
				'title'       => 'Aurpay Payment Title',
				'type'        => 'text',
				'default'     => 'Aurpay Crypto Payment',
				'desc_tip'    => true,
				'description' => 'Title displayed for Aurpay Payment on the order checkout page.',
			),
			'public_key' => array(
				'title'       => 'Public Key',
				'type'        => 'password',
				'description' => sprintf(
					'Get Aurpay Publickey: <a href=%s>Aurpay Dashboard</a>', esc_url( 'https://dashboard.aurpay.net/#/login?platform=WOOCOMMERCE' )
				),
			)
		);
	}

	/**
	 * Process Admin Settings | Validate
	 *
	 * @return bool|void
	 * @since 1.0
	 * @version 1.0
	 */
	public function process_admin_options() {

        if ( get_transient( 'apwc_gateway_notified' ) ) {
            return;
        }
        set_transient( 'apwc_gateway_notified', true, 3 );

		parent::process_admin_options();

		$api_key = isset( $_POST['woocommerce_aurpay_public_key'] ) ? sanitize_text_field( $_POST['woocommerce_aurpay_public_key'] ) : '';

		if ( empty( $api_key ) ) {
			WC_Admin_Settings::add_error( '[Aurpay WooCommerce] Please fill in Aurpay public key.' );
			return false;
		} else {
			if ( is_admin() ) {
				if ( is_wp_error( self::verify_api_key( $api_key ) ) ) {
					error_log( $api_key );
					return;
				}
			}
		}
	}


	/**
	 * Verify Apikey
	 *
	 * @return bool|void
	 * @since 1.0
	 * @version 1.0
	 */
	public function verify_api_key( $merchant_public_key ) {

		$key_verify_url = sprintf(
			'https://dashboard.aurpay.net/api/plugin/key/verify?name=WOOCOMMERCE&key=%s&url=%s',
			$merchant_public_key, site_url()
		);

		$verify_result  = wp_remote_get( $key_verify_url );
		$response_data  = json_decode( $verify_result['body'], true );

		if ( ! ( $response_data['data'] ) ) {

			add_action( 'admin_notices', array( $this, 'admin_notice_for_key' ) );

		} else {
            $this->notified = false;
        }
	}


	/**
	 * Send notification of backend validation failure.
	 *
	 * @return bool|void
	 * @since 1.0
	 * @version 1.0
	 */
	public function admin_notice_for_key() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php __( '[Aurpay WooCommerce] The Aurpay public key you entered is incorrect. Please check the video link for more information', 'aurpay-wc' ); ?>
				(<a href="https://youtu.be/UeNASEXFXlI" target="blank">Tutorial videos: https://youtu.be/UeNASEXFXlI</a>)
			</p>
		</div>
		<?php
	}


	public function httpPost( $url, $data, $api_key ) {
		$body    = $data;
		$headers = array(
			'Content-Type' => 'application/json; charset=utf-8',
			'API-KEY'      => $api_key,
		);
		$args    = array(
			'body'        => $body,
			'timeout'     => 5,
			'redirection' => 5,
			'headers'     => $headers,
			'sslverify'   => false,
		);

		$response = wp_remote_post( $url, $args );
		return $response;
	}


	/**
	 * Process Payment
	 * @since 1.1
	 * @version 1.1
	 */
	public function process_payment( $order_id ) {


		$ap_generate_checkout_token = 'https://dashboard.aurpay.net/api/order/pay/token';
		$ap_checkout_url            = 'https://dashboard.aurpay.net/#/cashier/choose?token=';

		global $woocommerce;
		// $order = new WC_Order( $order_id );
		$order = wc_get_order( $order_id );

		// No payment is required to exit
		if ( ! $order || ! $order->needs_payment() ) {
			wp_redirect( $this->get_return_url( $order ) );
			exit;
		}

		$platform     = 'WOOCOMMERCE';
		$site_url     = site_url();
		$callback_url = "$site_url/?wc-api=APWC_Gateway&order_id=$order_id";
		$current_url  = $this->get_return_url( $order );
		var_dump( $current_url );
		$api_key  = $this->get_option( 'public_key' );
		$currency = $order->get_currency();
		$price    = $order->get_total();

		$origin = array(
			'id'           => $order_id,
			'price'        => $price,
			'currency'     => $currency,
			'callback_url' => $callback_url,
			'succeed_url'  => $current_url,
			'url'          => trim( get_site_url(), "/" ),
		);

		$data = array(
			'platform' => $platform,
			'origin'   => $origin,
		);

		$response      = self::httpPost( $ap_generate_checkout_token, json_encode( $data ), $api_key );
		$response_code = wp_remote_retrieve_response_code( $response );
		$response_data = json_decode( wp_remote_retrieve_body( $response ) ) ?: array();

		if ( $response_code == 200 && $response_data->code == 0 ) {

			$token        = $response_data->data->token;
			$redirect_url = $ap_checkout_url . $token;

			$order->update_status( 'on-hold', __( 'Awaiting cheque payment', 'woocommerce' ) );

			// Remove cart
			$woocommerce->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $redirect_url,
			);
		} else {
			if ( $response_code != 200 ) {
				wc_add_notice( sprintf( 'Aurpay payment request error. response code %d', $response_code ), 'error' );
			} elseif ( $response_data->code == 401 ) {
				if ( empty( $api_key ) ) {
					wc_add_notice( sprintf( 'Aurpay payment notify Error: %s', 'Please fill in the public key in admin ' ), 'error' );
				} else {
					wc_add_notice( sprintf( 'Aurpay payment notify Error: %s', 'Please check whether the public key is correct ' ), 'error' );
				}
			} else {
				wc_add_notice( sprintf( 'Aurpay payment notify Error[%d]: %s', $response_data->code, $response_data->message ), 'error' );
			}
		}
	}

	/**
	 * Webhook Catcher | action_hook callback
	 * @since 1.0
	 * @version 1.0
	 */
	public function ipn_callback() {


		$public_key = isset( $_GET['public_key'] ) ? sanitize_text_field( $_GET['public_key'] ) : '';
		$order_id   = isset( $_GET['order_id'] ) ? sanitize_text_field( $_GET['order_id'] ) : '';

		if ( $public_key != $this->public_key )
			wp_send_json( array( 'message' => 'Public Key Error: ' . $public_key ), 400 );
		try {
			$order = new WC_Order( $order_id );

			$order->update_status( 'processing', 'Aurpay finished IPN Call.' );

			delete_option( $order_id );

			wp_send_json_success( array(
				'a'        => $this->public_key,
				'order_id' => $order_id,
			), 200 );
		} catch (Exception $e) {
			wp_send_json( array( 'message' => 'No order associated with this ID.' . $order_id . $e ), 400 );
		}
	}
}

/**
 * Adds Gateway into WooCommerce
 *
 * @param $gateways
 * @return mixed
 * @since 1.0
 * @version 1.0
 */
if ( ! function_exists( 'add_aurpay_to_wc' ) ) :
	function add_aurpay_to_wc( $gateways ) {
		$gateways[] = 'APWC_Gateway';
		return $gateways;
	}
endif;

add_filter( 'woocommerce_payment_gateways', 'add_aurpay_to_wc' );