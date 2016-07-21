<?php defined('ABSPATH') or die;

class Farost_Plugin_Admin_Options {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	function admin_menu() {
		add_menu_page(
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
		require_once 'login.php';
	}
}
new Farost_Plugin_Admin_Options;