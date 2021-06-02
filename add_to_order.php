<?php
/*
Plugin Name: Add to order
Plugin URI: https://wordpress.org/plugins/add-to-order
Description: Change the action of the 'add to cart' button to submit an order for the product
Version: 1.0.0.1
Author: Mohammad Jafar Khajeh
Author URI: http://mjkhajeh.com
Text Domain: mjato
Domain Path: /languages
*/
namespace MJATO;

if (!defined('ABSPATH')) exit;

class Init {
	PRIVATE $DIR = '';
	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
		static $instance = null;
		if( $instance === null ) {
			$instance = new self;
		}
		return $instance;
	}
	
	private function __construct() {
		$this->DIR = trailingslashit( plugin_dir_path( __FILE__ ) );

		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 5 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_to_order' ) );
	}

	public function i18n() {
		// Load languages
		load_plugin_textdomain( 'mjato', false, "{$this->DIR}languages" );
	}

	public function includes() {
		if( is_admin() ) {
			include_once( "{$this->DIR}options.php" );
		}
	}
	
	public function add_to_order( $data ) {
		$product_id	= $data['product_id'];
		if( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$order = wc_create_order( array(
				'customer_id'	=> $user->ID
			) );
			$order->add_product( get_product( $product_id ), 1 );

			$first_name = get_user_meta( $user->ID, 'billing_first_name', true );
			if( empty( $first_name ) ) {
				$first_name = $user->first_name;
			}
			$last_name = get_user_meta( $user->ID, 'billing_last_name', true );
			if( empty( $last_name ) ) {
				$last_name = $user->last_name;
			}
			$email = get_user_meta( $user->ID, 'billing_email', true );
			if( empty( $email ) ) {
				$email = $user->user_email;
			}
			$country = get_user_meta( $user->ID, 'billing_country', true );
			if( empty( $country ) ) {
				if( get_option( 'woocommerce_default_customer_address' ) == 'base' ) {
					$country = WC()->countries->get_base_country();
				}
			}
			$order->set_address( array(
				'first_name'	=> $first_name,
				'last_name'		=> $last_name,
				'email'			=> $email,
				'phone'			=> get_user_meta( $user->ID, 'billing_phone', true ),
				'address_1'		=> get_user_meta( $user->ID, 'billing_address_1', true ),
				'address_2'		=> get_user_meta( $user->ID, 'billing_address_2', true ),
				'city'			=> get_user_meta( $user->ID, 'billing_city', true ),
				'state'			=> get_user_meta( $user->ID, 'billing_state', true ),
				'postcode'		=> get_user_meta( $user->ID, 'billing_postcode', true ),
				'country'		=> $country,
			), 'billing' );
			$order->update_status( get_option( 'mjato_default_status', 'pending' ) );
		}

		return;
	}
}
Init::get_instance();