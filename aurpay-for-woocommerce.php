<?php

/**
 * Plugin Name: Bitcoin—Aurpay crypto payment for WooCommerce,help you earn more $2/ order than others
 * Plugin URI: https://dashboard.aurpay.net/#/login?platform=WOOCOMMERCE
 * Description: Earn more $2/order compare to others，Let your customer pay with ETH, USDC, USDT, DAI, 50+ cryptos. Lowest fees, Non-Custodail & No Fraud/Chargeback. Invoice, Payment Link, Payment Button.
 * Version: 1.0.14
 * Author: Aurpay
 * Author URI: https://aurpay.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * @author Aurpay
 * @url https://www.linkedin.com/company/aurpay/
 */


 defined( 'ABSPATH' ) || exit;

 if ( ! defined( 'APWC_PLUGIN_FILE' ) ) {
     define( 'APWC_PLUGIN_FILE', __FILE__ );
 }

 if ( ! defined( 'APWC_PLUGIN_BASE' ) ) {
     define( 'APWC_PLUGIN_BASE', plugin_basename( APWC_PLUGIN_FILE ) );
 }

 if ( ! defined( 'APWC_VERSION' ) ) {
     define( 'APWC_VERSION', '1.0' );
 }

 if ( ! defined( 'APWC_ID' ) ) {
     define( 'APWC_ID', 'aurpay' );
 }

 if ( ! defined( 'APWC_PLUGIN_URL' ) ) {
     define( 'APWC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
 }

 if ( ! defined( 'AURPAY_PLUGIN_FILE' ) ) {
     define( 'AURPAY_PLUGIN_FILE', __FILE__ );
 }

 if ( ! defined( 'AURPAY_PLUGIN_URL' ) ) {
     define( 'AURPAY_PLUGIN_URL', plugins_url( '/', AURPAY_PLUGIN_FILE ) );
 }


 require dirname( APWC_PLUGIN_FILE ) . '/includes/class-apwc-init.php';

 add_action( 'plugins_loaded', 'load_apwc' );
 add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'apwc_plugin_edit_link' );
 add_filter( 'plugin_row_meta', 'apwc_plugin_row_meta', 10, 5 );

 /**
  * Loads Plugin
  *
  * @since 1.0
  * @version 1.0
  */
 function load_apwc() {
     APWC_Init::get_instance();

     require_once dirname( APWC_PLUGIN_FILE ) . '/includes/class-gateway.php';

     $appgwc = new APWC_Gateway();
     if ( isset( $appgwc->public_key ) && '' !== $appgwc->public_key ) {
         return false;
     } else {
         wp_enqueue_style( 'aurpay-notice-banner-style', APWC_PLUGIN_URL . 'assets/css/aurpay-usage-notice.css', array(), rand( 111, 9999 ) );
         wp_enqueue_style( 'aurpay', AURPAY_PLUGIN_URL . 'assets/css/style.css', array(), rand( 111, 9999 ));

         add_action( 'admin_notices', 'apwc_render_usage_notice' );
     }
 }


 /**
  * Renders the usage notice.  Only shown once and on plugin activation.
  */
 if ( ! function_exists( 'apwc_render_usage_notice' ) ) {
     function apwc_render_usage_notice() {
         global $pagenow;
         $admin_pages = array( 'index.php', 'plugins.php' );
         if ( in_array( $pagenow, $admin_pages ) ) {
             ?>
             <div class="ap-connection-banner aurpay-usage-notice">

                 <div class="ap-connection-banner__container-top-text">
                     <span class="notice-dismiss aurpay-usage-notice__dismiss" title="Dismiss this notice"></span>
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                         <rect x="0" fill="none" width="24" height="24" />
                         <g>
                             <path
                                 d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm1 15h-2v-2h2v2zm0-4h-2l-.5-6h3l-.5 6z" />
                         </g>
                     </svg>
                     <span>You're almost done. Setup Aurpay to enable Crypto Payment for you WooCommerce site.</span>
                 </div>
                 <div class="ap-connection-banner__inner">
                     <div class="ap-connection-banner__content">
                         <div class="ap-connection-banner__logo">
                             <img src="<?php echo esc_url( plugins_url( 'assets/images/logo_aurpay.svg', APWC_PLUGIN_FILE ) ); ?>"
                                 alt="logo">
                         </div>
                         <h2 class="ap-connection-banner__title">Empower Your Business with Aurpay Crypto Payment</h2>
                         <div class="ap-connection-banner__columns">
                             <div class="ap-connection-banner__text">⭐ Get listed on our online directory to attract <span
                                     style="color: #007AFF">300 millions</span> of crypto owners. </div>
                             <div class="ap-connection-banner__text">⭐ Earn up to <span style="color: #007AFF">150,000 satoshi</span>
                                 rewards for merchants who finished all settings and more. </div>
                         </div>
                         <div class="ap-connection-banner__rows">
                             <div class="ap-connection-banner__text ap-connection-banner__step">By setting up Aurpay, get a merchant
                                 account and save your "<span style="color: #007AFF">Public Key</span>" in WooCommerce Payment
                                 settings. </div>
                             <a id="ap-connect-button--alt" rel="external" target="_blank"
                                 href="https://dashboard.aurpay.net/#/integration?platform=WOOCOMMERCE"
                                 class="ap-banner-cta-button ap_step_1">Setup Aurpay</a>
                         </div>
                         <div class="ap-connection-banner__rows" style="display: none;">
                             <div class="ap-connection-banner__text ap-connection-banner__step">Save your PublicKey in WooCommerce
                                 Payment settings.</div>
                             <a id="ap-connect-button--alt" target="_self"
                                 href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . APWC_ID ) ); ?>"
                                 class="ap-banner-cta-button ap_step_2">Settings</a>
                         </div>
                     </div>
                     <div class="ap-connection-banner__image-container">
                         <picture>
                             <source type="image/webp"
                                 srcset="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.webp', APWC_PLUGIN_FILE ) ); ?> 1x, <?php echo esc_url( plugins_url( 'assets/images/img_aurpay-2x.webp', APWC_PLUGIN_FILE ) ); ?> 2x">
                             <img class="ap-connection-banner__image"
                                 srcset="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.png', APWC_PLUGIN_FILE ) ); ?> 1x, <?php echo esc_url( plugins_url( 'assets/images/img_aurpay-2x.png', APWC_PLUGIN_FILE ) ); ?> 2x"
                                 src="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.png', APWC_PLUGIN_FILE ) ); ?>"
                                 alt="">
                         </picture>
                         <img class="ap-connection-banner__image-background"
                             src="<?php echo esc_url( plugins_url( 'assets/images/background.svg', APWC_PLUGIN_FILE ) ); ?>" />
                     </div>
                 </div>
             </div>

             <?php

             wp_enqueue_script(
                 'aurpay-notice-banner-js',
                 APWC_PLUGIN_URL . 'assets/js/aurpay-usage-notice.js',
                 array( 'jquery' ),
                 rand( 111, 9999 )
             );
         }
     }
 }

 /**
  * Plugin action links.
  * Adds action links to the plugin list table
  *
  * @since 1.0.1
  */
 if ( ! function_exists( 'apwc_plugin_edit_link' ) ) {
     function apwc_plugin_edit_link( $links ) {
         return array_merge(
             array(
                 'aurpay'   => '<a target="_blank" href="https://dashboard.aurpay.net/#/login?platform=WOOCOMMERCE" style="color: #39b54a; font-weight: bold;";>' . __( 'Get Aurpay', 'aurpay-wc' ) . '</a>',
                 'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . APWC_ID ) . '">' . __( 'Settings', 'aurpay-wc' ) . '</a>',
             ),
             $links
         );
     }
 }

 /**
  * Plugin row meta.
  * Adds row meta links to the plugin list table
  *
  * @since 1.0
  */
 if ( ! function_exists( 'apwc_plugin_row_meta' ) ) {
     function apwc_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
         if ( 'aurpay-for-woocommerce' === $plugin_data['slug'] ) {
             $row_meta = array(
                 'dome'  => '<a style="color: #39b54a;" target="_blank"; href="https://example-wp.aurpay.net/shop/" aria-label="' . esc_attr( __( 'View Aurpay Demo', 'aurpay-wc' ) ) . '">' . __( 'Demo', 'aurpay-wc' ) . '</a>',
                 'video' => '<a style="color: #39b54a;" target="_blank"; href="https://youtu.be/UeNASEXFXlI" aria-label="' . esc_attr( __( 'View Aurpay Video Tutorials', 'aurpay-wc' ) ) . '">' . __( 'Video Tutorials', 'aurpay-wc' ) . '</a>',
             );

             $plugin_meta = array_merge( $plugin_meta, $row_meta );
         }
         return $plugin_meta;
     }
 }
