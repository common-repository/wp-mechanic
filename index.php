<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
Plugin Name: WP Mechanic
Plugin URI: http://androidbubble.com/blog/wordpress-mechanic
Description: WP Mechanic is a combination of WordPress and Android Playstore Applications. Experience a set of hybrid software applications.
Version: 1.6.8
Author: Fahad Mahmood 
Author URI: http://www.androidbubbles.com
Text Domain: wp-mechanic
Domain Path: /languages/
License: GPL2

Alphabetic Pagination is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Alphabetic Pagination is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Alphabetic Pagination. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/ 



	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	global $wm_data, $wm_pro, $wm_url, $wm_dir, $WMQR_Code_Settings, $wm_apps_arr;
	
	$wm_pro = false;
	$admin_email = get_bloginfo('admin_email');
	$wm_data = get_plugin_data(__FILE__);
	$wm_url = plugin_dir_url( __FILE__ );
	$wm_dir = plugin_dir_path( __FILE__ );
	
	$wm_apps_arr = array(
		'wordpress.users' => array(
			'title' => 'WordPress Users',
			'premium' => false
		),
		'wordpress.users.management' => array(
			'title' => 'WordPress Users Management',
			'premium' => true
		),
		'woocommerce.customers' => array(
			'title' => 'WooCommerce Customers',
			'premium' => false
		),
		'wordpress.acrobatics' => array(
			'title' => 'WordPress Acrobatics',
			'premium' => false
		)	,
		'woocommerce.orders' => array(
			'title' => 'WooCommerce Orders',
			'premium' => false
		),
		'woocommerce.products' => array(
			'title' => 'WooCommerce Products',
			'premium' => false
		),
		'wp.alphabetic.pagination' => array(
			'title' => 'Alphabetic Pagination',
			'premium' => false
		),
		'woo.coming.soon' => array(
			'title' => 'Woo Coming Soon',
			'premium' => false
		),		
		'wp.docs' => array(
			'title' => 'WP Docs',
			'premium' => false
		)			
	);
	ksort($wm_apps_arr);	
	
	
	include('inc/functions.php');
	
	if(class_exists('WMQR_Code_Settings')){
		$rest_api_url = 'wordpress-users/v1';
		$WMQR_Code_Settings = new WMQR_Code_Settings($wm_dir, $wm_url, $rest_api_url);
	}
	
	function wp_mechanic(){ 

		if ( !current_user_can( 'administrator' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		global $wpdb; 

	}	

        

    function register_wm_scripts() {
		
		global $WMQR_Code_Settings, $wm_apps_arr;
		
		if(is_admin()){
			
			wp_register_style( 'wm-style', plugins_url('css/style.css', __FILE__) );
			wp_enqueue_style('wm-style');
			
		}
		
		wp_enqueue_script( 'wm-script', plugins_url('js/wm_scripts.js', __FILE__), array('jquery', 'jquery-ui-accordion'), time(), true);
		
		$hash_option_encoded = $WMQR_Code_Settings->get_hash_option_encoded();
		$hash_option_encoded = is_array($hash_option_encoded)?$hash_option_encoded:array();
		//pree($hash_option_encoded);

		$wm_ajax_obj = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'wm_logged_in' => is_user_logged_in()?'true':'false',
			'wm_force_logout' => (get_option('wm_force_logout')?'true':'false'),
			'force_logout_msg' => __('You will be logged out in 60 seconds. Please save your work in clipboard if you want. ', 'wp-mechanic')
		);
		//pree($hash_option_encoded);
		//pree($wm_apps_arr);
		if(!empty($wm_apps_arr)){
			foreach($wm_apps_arr as $app_slug=>$app_data){				
				$app_slug_js = str_replace(array('.'), '_', $app_slug);
				$wm_ajax_obj['hash_option'][$app_slug_js] = '';
				if(array_key_exists($app_slug, $hash_option_encoded)){					
					$wm_ajax_obj['hash_option'][$app_slug_js] = base64_encode(serialize($hash_option_encoded[$app_slug]));
				}
			}
		}
		
		//pree($wm_ajax_obj);
		wp_localize_script( 'wm-script', 'wm_ajax_obj', $wm_ajax_obj );
                
	}	

	register_activation_hook(__FILE__, 'wm_start');
	register_deactivation_hook(__FILE__, 'wm_end' );
	add_action( 'admin_menu', 'wm_menu' );	
    add_action( 'admin_enqueue_scripts', 'register_wm_scripts' );
	add_action( 'wp_enqueue_scripts', 'register_wm_scripts' );
	
	function wm_user_can() {
		if (is_multisite())
			$GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] = "manage_network";
		elseif (!isset($GLOBALS["wm"]["tmp"]["settings_array"]["user_can"]) || $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] == "manage_network")
			$GLOBALS["wm"]["tmp"]["settings_array"]["user_can"] = "activate_plugins";
		if (current_user_can($GLOBALS["wm"]["tmp"]["settings_array"]["user_can"]))
			return true;
		else
			return false;
	}
	
	function wm_menu() {
		global $wm_data, $wm_url;
		$base_page = "wm-android";
		$base_function = "wm_android";
		$pluginTitle = $wm_data['Name'];
		$pageTitle = "$pluginTitle ";
		if (wm_user_can() && !wm_check_temp_user_account()) {
			$my_admin_page = add_menu_page($pageTitle, $pluginTitle, $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], $base_page, $base_function, $wm_url.'images/face.png');
			add_submenu_page($base_page, "$pluginTitle > Android Apps", 'Android Applications', $GLOBALS["wm"]["tmp"]["settings_array"]["user_can"], "wm-android", "wm_android");
		}
	}
	

	
	function wm_enqueue_scripts() {
		wp_enqueue_style('dashicons');
	}
	add_action('admin_enqueue_scripts', 'wm_enqueue_scripts');
	
	function wm_android() {
		include_once('inc/wm_android.php');
	}


	add_action("admin_menu", "wm_menu");
	add_action("network_admin_menu", "wm_menu");	
	
	