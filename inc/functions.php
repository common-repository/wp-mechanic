<?php if ( ! defined( 'ABSPATH' ) ) exit; 
        

	function sanitize_wpwm_data( $input ) {

		if(is_array($input)){
		
			$new_input = array();
	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wpwm_data($val):sanitize_text_field( $val ));
			}
			
		}else{
			$new_input = sanitize_text_field($input);
			
			if(stripos($new_input, '@') && is_email($new_input)){
				$new_input = sanitize_email($new_input);
			}
			
			if(stripos($new_input, 'http') || wp_http_validate_url($new_input)){
				$new_input = esc_url($new_input);
			}			
		}
		

		
		return $new_input;
	}	
			
	define('WM_EMAIL', 'wp.mechanic@androidbubbles.com');
	
	if(!function_exists('pre')){
	function pre($data){
		if(isset($_GET['debug'])){
			pree($data);
		}
	}	 
	} 
	
	if(!function_exists('pree')){
		function pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';			
		}	 
	} 
   
	if(!function_exists('wm_start')){

		function wm_start(){	



		}	

	}

	

	

	if(!function_exists('wm_end')){



		function wm_end(){	



		}

	}
	
	if(!function_exists('wm_searchable')){
		function wm_searchable($content){	

			$exp = explode(' ', $content);
			return '<span>'.implode('</span><span>', $exp).'</span>';

		}

	}	


	

	if(!function_exists('set_html_content_type')){

		function set_html_content_type()

		{

			return 'text/html';

		}	

	}

	

	if(!function_exists('clean_data')){

		function clean_data($input) {

		$input = trim(htmlentities(strip_tags($input,",")));

	 

		if (get_magic_quotes_gpc())

			$input = stripslashes($input);

	 

		$input = mysql_real_escape_string($input);

	 

		return strip_tags($input);

		}

	}



	

	if(!function_exists('wm_timezone')){

		function wm_timezone() {	

		global $sitting_time;	

		$time = date('d-m-Y h:i:s a',mktime($sitting_time, 0, 0, date('m'), date('d'), date('y'))); 

		

		try {

		

		$tz = new DateTime($time, new DateTimeZone('Asia/Karachi'));

		

		$tz->setTimeZone(new DateTimeZone(date_default_timezone_get()));

			return 'Availability around '.$tz->format('h:i A P');

		} catch(Exception $e) {



			 return $e->getMessage();

		}			





		}

	}

	function wm_login_stylesheet() {
		global $wm_url;
		wp_enqueue_style( 'wm-users-login', $wm_url . 'css/style-login.css', array(), time() );
		wp_enqueue_script( 'wm-users-login', $wm_url . 'js/script-login.js', array('jquery', 'jquery-ui-core'), time(), false);
		
		$wm_ajax_obj = array(
		
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'wm_login' => (isset($_GET['wm-login']) || wm_login_grid_status()=='true'),
			'admin_url' => admin_url()
			
		
		);
		wp_localize_script( 'wm-users-login', 'wm_ajax_obj', $wm_ajax_obj );
	}
	add_action( 'login_enqueue_scripts', 'wm_login_stylesheet' );
	add_action( 'wp_ajax_nopriv_wm_login_actions', 'wm_login_actions' );
	add_action( 'wp_ajax_nopriv_wm_login_grid_log', 'wm_login_grid_log' );
	add_action( 'wp_ajax_nopriv_wm_login_grid_status', 'wm_login_grid_status' );
	add_action( 'wp_ajax_nopriv_wm_login_access_status', 'wm_login_access_status' );
	add_action( 'wp_ajax_wm_force_logout_check', 'wm_force_logout_check' );
	


	function wm_login_grid_log(){
		
		$wm_login_grid_display = get_wm_login_grid_display();
		$wm_login_grid_display[home_url()] = (isset($wm_login_grid_display[home_url()])?$wm_login_grid_display[home_url()]:array());
		$wm_login_grid_display[home_url()][wm_get_the_user_ip()] = (isset($wm_login_grid_display[home_url()][wm_get_the_user_ip()])?$wm_login_grid_display[home_url()][wm_get_the_user_ip()]:array());
		
		
		$wm_login_grid_display[home_url()][wm_get_the_user_ip()] = '';
		update_option('wm_login_grid_display', $wm_login_grid_display);
		exit;
	}

	function get_wm_login_grid_display(){
		$wm_login_grid_display = get_option('wm_login_grid_display');
		$wm_login_grid_display = (is_array($wm_login_grid_display)?$wm_login_grid_display:array());
		return $wm_login_grid_display;
	}
	
	function wm_login_grid_status(){
		$ret = 'false';
		$wm_login_grid_display = get_wm_login_grid_display();
		if(!empty($wm_login_grid_display) && array_key_exists(home_url(), $wm_login_grid_display) && array_key_exists(wm_get_the_user_ip(), $wm_login_grid_display[home_url()])){
			$ret = $wm_login_grid_display[home_url()][wm_get_the_user_ip()];
		}
		if(wp_doing_ajax()){
			$msg = '';
			switch($ret){
				default:
				
				break;
				case 'true':
					$msg = 'allowed';
				break;
				case 'false':
					$msg = 'not-allowed';
				break;
				
			}
			echo json_encode(array('msg'=>$msg));
			exit;
		}else{
			return $ret;
		}
		
	}
	
	function wm_login_access_status(){
		$ret = false;
		
		if(wp_doing_ajax()){
			//echo get_option('wm_allow_access').'=='.wm_get_the_user_ip();exit;
			//echo (get_option('wm_allow_access')==wm_get_the_user_ip()?'Same':'Not Same');
			//exit;
			
			if(get_option('wm_allow_access')==wm_get_the_user_ip()){	
				$get_wm_login_grid_display = get_wm_login_grid_display();
				$get_wm_login_grid_display[home_url()][wm_get_the_user_ip()] = false;
				update_option('wm_login_grid_display', $get_wm_login_grid_display);	
				
					
				global $wpdb;
				
				//echo ABSPATH.'<br />';
				$root_path = get_home_path();//exit;
				
				require_once($root_path.'wp-blog-header.php');
				//require_once($root_path.'wp-includes/registration.php');
				
							
				$newusername = wm_get_the_user_ip();
				$newpassword = md5(time());
				$newemail = wm_get_the_user_ip().'@wp.com';
				// ----------------------------------------------------
				
				// This is just a security precaution, to make sure the above "Config Variables" 
				// have been changed from their default values.
				if ( $newpassword != 'YOURPASSWORD' &&
					 $newemail != 'YOUREMAIL@TEST.com' &&
					 $newusername !='YOURUSERNAME' )
				{
					//echo username_exists($newusername)?'username_exists':'';
					//echo '<br />';
					//echo email_exists($newemail)?'email_exists':'';
					//echo '<br />';
					// Check that user doesn't already exist
					if ( !username_exists($newusername) && !email_exists($newemail) )
					{
						// Create user and set role to administrator
						$user_id = wp_create_user( $newusername, $newpassword, $newemail);
						if ( is_int($user_id) )
						{
							$wp_user_object = new WP_User($user_id);
							
							$ret = 'Successfully created new user';
							update_user_meta($user_id, '_wm_temp_user_account', true);
							
							//delete_option('wm_selected_role');
							
						}
						else {
							$ret = 'Error with wp_insert_user. No users were created.';
						}
					}
					else {
						$ret = 'This user or email already exists. Nothing was done.';
						$user = get_user_by('email', $newemail);					
						wp_set_password( $newpassword, $user->ID );
					}
					
					
					if (!is_user_logged_in()) {
						
						//$user_ready = get_userdatabylogin($newusername);
						$user_ready = get_user_by('login', $newusername);
						
						if(!empty($user_ready)){
							$user_id_ready = $user_ready->ID;									
							if($user_id_ready){

								$set_role = get_option('wm_selected_role');
								$set_role = ($set_role?$set_role:'subscriber');
								
								if($set_role){
									//$wp_user_object = new WP_User($user_id_ready);
									$user_ready->set_role($set_role);
								}
								
								wp_set_current_user($user_id_ready, $newusername);
								wp_set_auth_cookie($user_id_ready);
								do_action('wp_login', $newusername, $user_ready);
							}
							
						}
						
					}
						

				}
				else {
					$ret = 'Whoops, looks like you did not set a password, username, or email';
					$ret .= ' before running the script. Set these variables and try again.';
				}				
				
				//$ret = 'allowed';// - '.get_option('wm_allow_access');
				echo json_encode(array('msg'=>$ret?'allowed':'not-allowed'));
				//echo $ret;
				//delete_option('wm_allow_access');
				exit;
			}
			
		}else{
			return $ret;
		}
		
	}	
	
	function wm_check_temp_user_account($user_id=0){
		return get_user_meta($user_id?$user_id:get_current_user_id(), '_wm_temp_user_account', true);
	}
	function wm_force_logout_check(){
		$ret = false;
		
		if(wp_doing_ajax()){
			
			if(get_option('wm_force_logout')==true){		
				//pree(is_user_logged_in() && get_current_user_id());
				if(is_user_logged_in() && get_current_user_id()){
					$user_id = get_current_user_id();
					
					if(wm_check_temp_user_account($user_id)){
						
						$get_wm_login_grid_display = get_wm_login_grid_display();
						$get_wm_login_grid_display[home_url()][wm_get_the_user_ip()] = false;
						update_option('wm_login_grid_display', $get_wm_login_grid_display);	
						
						$sessions = WP_Session_Tokens::get_instance( get_current_user_id() );
						$sessions->destroy_all();
						
	
						wp_delete_user($user_id);
	
						
						delete_option('wm_force_logout');
						echo 'logged-out';
					}
				}
				
			}else{
				
			}
			exit;
			
		}else{
			return $ret;
		}
		
	}		
	
	

	function get_wm_login_requests(){
		$wm_login_requests = get_option('wm_login_requests');
		$wm_login_requests = (is_array($wm_login_requests)?$wm_login_requests:array());
		return $wm_login_requests;
	}
	
	
	
	function wm_login_actions(){
		$cell_type = sanitize_wpwm_data($_POST['ctype']);
		switch($cell_type){
			default:
			break;
			case 'red':
			case 'green':
			case 'blue':
				$wm_login_requests = get_wm_login_requests();
				
				$wm_login_requests[home_url()][wm_get_the_user_ip()] = strtoupper($cell_type);
				update_option('wm_login_requests', $wm_login_requests);
				
				//$wm_login_requests = get_wm_login_requests();
				
				//pree($wm_login_requests);
			break;			
		}
		exit;
	}
	function wm_get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'wpb_get_ip', $ip );
	}
	function wm_screen_lock(){
		$current_screen = get_current_screen();
		//pree($current_screen->id);
		if (wm_user_can() && wm_check_temp_user_account() && is_object($current_screen) && isset($current_screen->id)) {
			
			if(in_array($current_screen->id, array('plugins', 'plugin-install', 'plugin-editor', 'user', 'users', 'profile', 'theme-editor', 'toplevel_page_wm-android'))){
				//wp_redirect(admin_url());
				echo __('You are logged in as a temporary account with limited rights. Please contact super administrator so you can be provided with a real account.', 'wp-mechanic');
				exit;
			}
			
		}
	}
	add_action('current_screen', 'wm_screen_lock');
	include('functions-inner.php');