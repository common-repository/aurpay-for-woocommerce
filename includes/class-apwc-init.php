<?php
// Aurpay Init.

class APWC_Init {
	/* Aurapy Payment Gateway init. */

	private static $instance;

	/**
	 * Single ton
	 *
	 * @return APWC_Init
	 * @since 1.0
	 * @version 1.0
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * APWC_Init constructor.
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function __construct() {

		$this->validate();
	}

	/**
	 * Meets requirements
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function validate() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->init();
		} else {
			add_action( 'admin_notices', array( $this, 'missing_wc' ) );
		}
	}

	/**
	 * Shows Notice
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function missing_wc() {

		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php esc_html_e( 'In order to use Aurpay for WooCommerce, make sure WooCommerce is installed and active.', 'aurpay-wc' ); ?>
			</p>
		</div>
		<?php

	}

	/**
	 * Finally initialize the Plugin :)
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	private function init() {

		$this->includes();
	}

	/**
	 * Includes files
	 *
	 * @since 1.0
	 * @version 1.0
	 */
	public function includes() {

		// require 'class-apwc-gateway.php';
        require 'class-gateway.php';
	}
}
