<?php 
/*
 * Plugin Name: Woo Estato - Addon for Real Estate Manager
 * Plugin URI: https://webcodingplace.com/real-estate-manager-wordpress-plugin/
 * Description: An addon for Real Estate Manager to manage premium subscriptions of agents.
 * Version: 1.0
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-estato
 * Domain Path: /languages
*/
 if( ! defined('ABSPATH' ) ){
	exit;
}

define( 'WOO_ESTATO_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define( 'WOO_ESTATO_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );

require_once('plugin.class.php');

/**
 * Iniliatizing main class object for setting up listing system
 */
if( class_exists('REM_WOO_ESTATO')){
    $woo_estato = new REM_WOO_ESTATO;
}
?>