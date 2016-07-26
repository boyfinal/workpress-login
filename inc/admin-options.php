<?php defined('ABSPATH') or die;

class Farost_Plugin_Admin_Options {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	function admin_menu() {
		add_submenu_page(
			'options-general.php',
	        __( 'Farost login', 'farost_login' ),
	        __( 'Farost login','farost_login' ),
	        'manage_options',
	        'farost-option-login',
	        array($this,'settings_page')
	    );
	}

	function register_settings() {
	  	register_setting( 'farost_login_options', 'farost_login' );
	}

	function settings_page() {
		include_once __DIR__ . '/views/admin-options.php';
	}
}
new Farost_Plugin_Admin_Options;