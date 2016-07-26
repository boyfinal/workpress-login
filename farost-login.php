<?php
/*
Plugin Name: Farost Login
Plugin URI: #
Description: This is plugin login to ajax
Version: 1.0.0
Author: Farost
Author URI: #
*/

# plugin path
session_start();
defined('ABSPATH') or exit;
define( 'FAROST_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
$farost_login_options = get_option('farost_login');

include_once trailingslashit(FAROST_LOGIN_DIR) . 'inc/functions.php';
if ( is_admin() ) {
	include_once trailingslashit(FAROST_LOGIN_DIR) . 'inc/admin-options.php';
}
include_once trailingslashit(FAROST_LOGIN_DIR) . 'inc/author.php';
include_once trailingslashit(FAROST_LOGIN_DIR) . 'inc/shortcode.php';