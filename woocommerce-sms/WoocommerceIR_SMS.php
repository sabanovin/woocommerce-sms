<?php
/*
Plugin Name: افزونه حرفه ای پیامک ووکامرس
Version: 3.6.8
Plugin URI: http://www.woocommerce.ir/
Description: این افزونه شما را قادر می سازد تا براحتی قابلیت ارسال پیامک را در سیستم ووکامرس پارسی فراهم کنید. تمامی حقوق این افزونه متعلق به تیم ووکامرس پارسی می باشد و هر گونه کپی برداری ،  فروش آن غیر مجاز می باشد.
Author URI: http://woocommerce.ir/
Author: ووکامرس فارسی
Contributors: hannanstd,Persianscript
WC tested up to: 3.2.5
*/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Location: http://woocommerce.ir' );
	exit;
}

if ( ! defined( 'PS_WOO_SMS_VERSION' ) ) {
	define( 'PS_WOO_SMS_VERSION', '3.6.7' );
}

if ( ! defined( 'PS_WOO_SMS_PLUGIN_PATH' ) ) {
	define( 'PS_WOO_SMS_PLUGIN_PATH', plugins_url( '', __FILE__ ) );
}

if ( ! defined( 'PS_WOO_SMS_PLUGIN_LIB_PATH' ) ) {
	define( 'PS_WOO_SMS_PLUGIN_LIB_PATH', dirname( __FILE__ ) . '/includes' );
}

function WoocommerceIR_SMS_Pro_un_install() {
	update_option( 'redirect_to_woo_sms_about_page', 'no' );
	update_option( 'redirect_to_woo_sms_about_page_check', 'no' );
}

register_activation_hook( __FILE__, 'WoocommerceIR_SMS_Pro_un_install' );
register_deactivation_hook( __FILE__, 'WoocommerceIR_SMS_Pro_un_install' );

function WoocommerceIR_SMS_Pro() {
	global $persianwoosms;
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.helper.php';
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.settings.api.php';
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.settings.php';
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.gateways.php';
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.bulk.send.php';
	require_once PS_WOO_SMS_PLUGIN_LIB_PATH . '/class.dashboard.php';
	$persianwoosms = WoocommerceIR_Settings_SMS::init();
}

add_action( 'plugins_loaded', 'WoocommerceIR_SMS_Pro' );