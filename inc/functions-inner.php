<?php



	/* New Code */
	


include('phpqrcode.php');

class WMQR_Code_Settings 
{
	private $rest_api_url;
	private $wm_plugin_url;
	private $plugin_dir;
	

	function __construct($plugin_dir, $wm_plugin_url, $rest_api_url)
	{
		$this->rest_api_url = $rest_api_url;
		$this->plugin_url = $wm_plugin_url;
		$this->plugin_dir = $plugin_dir;		
		$this->execute_settings_api();
	}

	
	function get_wm_user_roles(){

		global $wp_roles;		
	
		$roles_arr = array();
		
		if(!empty($wp_roles)){
			foreach($wp_roles->roles as $role_key=>$role_details){
				$roles_arr[] = array('role_value'=>$role_key, 'role_title'=>$role_details['name']);
			}
		}
		
		return $roles_arr;
	}
	
	function get_wm_acrobatics($param){
		
		$login_key = $param['login_key'];
		$android_app = $param['android_app'];	
		$allow_grid = $param['allow_grid'];	
		$visitor_ip = $param['visitor_ip'];	
		$grid_color = $param['grid_color'];	
		$allow_access = $param['allow_access'];	
		$force_logout = $param['force_logout'];	
		$selected_role = $param['selected_role'];	
		$delete_grid_request = $param['delete_grid_request'];	
		$get_wm_login_grid_display = get_wm_login_grid_display();
		$wm_login_requests = get_wm_login_requests();
		$requested_color = '';
		$wm_allow_access_updated = false;


		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));
		
		if($login_key_status == true){
				
			if($allow_grid!='' && $visitor_ip!=''){			
				if(array_key_exists($visitor_ip, $get_wm_login_grid_display[home_url()])){
					$get_wm_login_grid_display[home_url()][$visitor_ip] = $allow_grid;
					update_option('wm_login_grid_display', $get_wm_login_grid_display);
					//pree($allow_access);
					if($allow_access=='true'){
						
						//pree($wm_login_requests);
						$requested_color = $wm_login_requests[home_url()][$visitor_ip];
						if($requested_color==strtoupper($grid_color)){
							
							update_option('wm_allow_access', $visitor_ip);
							
							
							
							$wm_allow_access_updated = true;
							
							$wm_login_requests[home_url()][$visitor_ip] = '';
							
							update_option('wm_login_requests', $wm_login_requests);
							
							
						}
					}
				}
			}
			
			if($force_logout=='true'){
				delete_option('wm_allow_access');			
				update_option('wm_force_logout', true);
			}elseif($force_logout=='false'){
				update_option('wm_force_logout', false);
			}
			
			if($delete_grid_request=='true'){
				if(array_key_exists($visitor_ip, $get_wm_login_grid_display[home_url()])){
					unset($get_wm_login_grid_display[home_url()][$visitor_ip]);
					update_option('wm_login_grid_display', $get_wm_login_grid_display);
					$get_wm_login_grid_display = get_wm_login_grid_display();
				}
			}
			
			if($selected_role != ''){			
				update_option('wm_selected_role', $selected_role);
			}
			
			
			$login_data = array(
				'allow_grid' => $allow_grid,
				'visitor_ip' => $visitor_ip,
				'grid_color' => $grid_color,
				'requested_color' => $requested_color,
				'allow_access' => $allow_access,
				'force_logout' => $force_logout,	
				'selected_role' => $selected_role,
				'availble_roles' => $this->get_wm_user_roles(),
				'allow_access_status' => get_option('wm_allow_access'),
				'wm_allow_access_updated' => $wm_allow_access_updated,
				'force_logout_status' => get_option('wm_force_logout'),
				'selected_role_status' => get_option('wm_selected_role'),
				
	
			);		
			if(!empty($get_wm_login_grid_display)){			
				foreach($get_wm_login_grid_display as $blog_url => $ip_addresses){
					
					if(!empty($ip_addresses)){
						foreach($ip_addresses as $ip=>$status){
							$login_data['level_1'][] = array('ip_addpress'=>$ip, 'allow_status'=>$status);
						}
					}
				}
			}
			
		}else{
			$login_data = array(
				'allow_grid' => 'login_key_step'
			);
		}
		
		$res = new WP_REST_Response($login_data);
		return $res;
		


	}
		
	function api_get_wordpress_users_by_page($param)
	{

		$login_key = $param['login_key'];
		$android_app = $param['android_app'];

		$number = $param['number'];
		$paged = $param['paged'];
		$search = $param['search'];
		$role = $param['role'];
		$offset = $param['offset'];
		$user_id = $param['ID'];

		$args = array(

			'number' => $number,
			'pages' => $paged,
			'offset' => $offset,
			'search' => "*" . $search . "*",
			'search_fields' => array('display_name', 'ID'),
			'role' => $role,
			'orderby' => 'display_name',
			'order' => 'ASC',			
		);

		if (!empty($user_id)) {
			$args['search'] = $user_id;
			$args['search_fields'] = array('ID');
		}


		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));

		// $login_key_status = true;


		// if($login_key == base64_decode('MTIz')){			
		if ($login_key_status == true) {

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				//plugin is activated
				$wm_is_woo_active = true;
			} else {
				$wm_is_woo_active = false;
			}



			$wordpress_users = get_users($args);
			// print_r(get_user_meta(26));exit;
			function user_map_function($wordpress_user)
			{
				global $wm_plugin_url, $wm_is_woo_active;

				$user_data_array = $wordpress_user->data;
				$user_id = $user_data_array->ID;

				// $user_data_array->profile_image = $wm_plugin_url."images/user.png";
				$user_data_array->profile_image = get_avatar_url($user_id, array('size' => 256));
				$user_data_array->roles = $wordpress_user->roles;
				$user_data_array->first_name = get_user_meta($user_id, 'first_name', 'true');
				$user_data_array->last_name = get_user_meta($user_id, 'last_name', 'true');
				$user_data_array->description = get_user_meta($user_id, 'description', 'true');
				$user_data_array->nickname = get_user_meta($user_id, 'nickname', 'true');
				$user_data_array->is_shipping = 'not_available';
				$user_data_array->is_billing = 'not_available';
				$role_array = array();
				foreach ($user_data_array->roles as $role_key => $role_value) {
					# code...
					$role_array[] = $role_value;
				}

				$user_data_array->roles = $role_array;

				if ($wm_is_woo_active) {

					$customer = new WC_Customer($user_id);
					$customer_shipping = $customer->get_shipping();
					$customer_billing = $customer->get_billing();



					if (!empty($customer_shipping['state'])) {


						$states_shipping = WC()->countries->get_states($customer_shipping['country']);

						if (!empty($states_billing)) {

							$customer_shipping['state'] = $states_shipping[$customer_shipping['state']];
						}
					}

					if (!empty($customer_billing['state'])) {

						$states_billing = WC()->countries->get_states($customer_billing['country']);

						if (!empty($states_billing)) {

							$customer_billing['state'] = $states_billing[$customer_billing['state']];
						}
					}


					if (!empty($customer_shipping['country'])) {
						$customer_shipping['country'] = WC()->countries->countries[$customer_shipping['country']];
					}

					if (!empty($customer_billing['country'])) {
						$customer_billing['country'] = WC()->countries->countries[$customer_billing['country']];
					}

					$shipping_check_array = array();
					foreach ($customer_shipping as $shipping_key => $shipping_value) {
						# code...
						if (empty($shipping_value)) {
							$shipping_check_array[] = 0;
						} else {
							$shipping_check_array[] = 1;
						}
					}

					if (!empty($customer_shipping) && in_array(1, $shipping_check_array)) {
						$user_data_array->shipping = $customer_shipping;
						$user_data_array->is_shipping = 'available';
					}


					$billing_check_array = array();
					foreach ($customer_billing as $billing_key => $billing_value) {
						# code...
						if (empty($billing_value)) {
							$billing_check_array[] = 0;

						} else {
							$billing_check_array[] = 1;
						}
					}


					if (!empty($customer_billing) && in_array(1, $billing_check_array)) {

						$user_data_array->billing = $customer_billing;
						$user_data_array->is_billing = 'available';
					}
				}



				return $user_data_array;
			}



			$users_array = array_map('user_map_function', $wordpress_users);

			global $wp_roles;
			$roles_array = json_decode(json_encode($wp_roles), true);
			$count_users = count_users();
			$avail_roles = $count_users['avail_roles'];
			$users_role = $roles_array['role_names'];
			$user_role_list = array();

			if (!empty($users_role)) {
				foreach ($users_role as $role_key => $role_value) {
					# code...
					if (array_key_exists($role_key, $avail_roles)) {
						$user_role_list[] = array(
							'role' => $role_key,
							'totalUsers' => $avail_roles[$role_key],
						);
					} else {
						$user_role_list[] = array(
							'role' => $role_key,
							'totalUsers' => 0,
						);
					}
				}
			}

			$users_data = array(
				'totalUsers' => $count_users['total_users'],
				'usersRoleList' => $user_role_list,
				'users' => $users_array,

			);


			$res = new WP_REST_Response($users_data);
			return $res;
		} else {
			$res = new WP_REST_Response(array('totalUsers' => -1));
			return $res;
		}
	}
	
	function api_get_wordpress_users($param){
		
		
		$login_key = $param['login_key'];	
		$android_app = $param['android_app'];	
		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));
		// $login_key_status = true;
		

		// if($login_key == base64_decode('MTIz')){			
			if($login_key_status == true){

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
				//plugin is activated
				$wm_is_woo_active = true;
			}else{
				$wm_is_woo_active = false;
			}

			$args = array();
			
			//'role__in'     => array('customer')
			
			$wordpress_users = get_users();
			function user_map_function($wordpress_users){
				global $wm_plugin_url, $wm_is_woo_active;


				
				$user_data_array = $wordpress_users->data;
				
				
				$user_data_array->roles = $wordpress_users->roles;
				$user_id = $user_data_array->ID;
				
				//$user_data_array->profile_image = $wm_plugin_url."images/user.png";;
				$user_data_array->profile_image = get_avatar_url($user_id, array('size'=>256));
				
				$user_data_array->first_name = get_user_meta($user_id, 'first_name' , 'true');
				$user_data_array->last_name = get_user_meta($user_id, 'last_name' , 'true');
				$user_data_array->description = get_user_meta($user_id, 'description' , 'true');
				$user_data_array->is_shipping = 'not_available';
				$user_data_array->is_billing = 'not_available';
				if($wm_is_woo_active){

					
					

					$customer = new WC_Customer($user_id);
					$customer_shipping = $customer->get_shipping();
					$customer_billing = $customer->get_billing();
					
					

					if(!empty($customer_shipping['state'])){
						$states_arr = WC()->countries->get_states($customer_shipping['country']);
						$customer_shipping['state'] = $states_arr[$customer_shipping['state']];

					}

					if(!empty($customer_billing['state'])){
						$states_arr = WC()->countries->get_states($customer_billing['country']);
						$customer_billing['state'] = $states_arr[$customer_billing['state']];

					}				


					if(!empty($customer_shipping['country'])){
						$customer_shipping['country'] = WC()->countries->countries[$customer_shipping['country']];

					}

					if(!empty($customer_billing['country'])){
						$customer_billing['country'] = WC()->countries->countries[$customer_billing['country']];

					}

					$shipping_check_array = array();
					foreach ($customer_shipping as $shipping_key => $shipping_value) {
						# code...
						if(empty($shipping_value)){
							$shipping_check_array[] = 0;
						}else{
							$shipping_check_array[] = 1;
						}
					}
					
					if(!empty($customer_shipping) && in_array(1, $shipping_check_array)){
						$user_data_array->shipping = $customer_shipping;
						$user_data_array->is_shipping = 'available';
					}


					$billing_check_array = array();
					foreach ($customer_billing as $billing_key => $billing_value) {
						# code...
						if(empty($billing_value)){
							$billing_check_array[] = 0;
						}else{
							$billing_check_array[] = 1;
						}
					}

					
					if(!empty($customer_billing) && in_array(1, $billing_check_array)){

						$user_data_array->billing = $customer_billing;
						$user_data_array->is_billing = 'available';

					}
					

				}

				

				return $user_data_array;
			}
			
			
			$users_array = array_map('user_map_function', $wordpress_users);

			
			
			
			$res = new WP_REST_Response($users_array);
			return $res;
		}
		

			



	}

	function register_api_read_settings(){		
		
		register_rest_route( $this->rest_api_url, '/get_wordpress_users', array(

		  'methods' => 'POST',

		  'callback' => array($this, 'api_get_wordpress_users'),

		));
	}
	
	

	

	public function get_login_key_option_name(){
		$login_key_option_name = strtolower(str_replace(array(" ", 'http://', 'https://', '/', '.'), array(''), home_url()));
		$login_key_option_name = $login_key_option_name."_login_key";
		return $login_key_option_name;
	}

	
	private function generate_random_login_key($android_app){

		$login_key_option_name = $this->get_login_key_option_name();
		$login_key_array = get_option($login_key_option_name);

		$rand_login_key = md5(rand());
		

		if(empty($login_key_array)){
			$new_login_key = array(
				$android_app => array($rand_login_key)
			);
			
			$rand_key_update_status =	update_option($login_key_option_name, $new_login_key);
		}else{
			$login_key_array[$android_app][] = $rand_login_key;
			$rand_key_update_status =	update_option($login_key_option_name, $login_key_array);
		}		

		if($rand_key_update_status == true){
			return	$rand_login_key;
		}else{
			return false;
		}
	}	
	

	public function validate_login_key($login_key, $android_app){

		$login_key_option_name = $this->get_login_key_option_name();
		$login_key_array = get_option($login_key_option_name);
		$login_key_array = (is_array($login_key_array)?$login_key_array:array());
		
		$login_key_array = (array_key_exists($android_app, $login_key_array)?$login_key_array[$android_app]:array());
		// $login_key_array = array("a7749324bf84d8e7ae248562832d24d5");
		//pree($login_key);
		//pree($login_key_array);
		$login_key_match_result = in_array($login_key, $login_key_array);
		//pree($login_key_match_result);exit;
		if($login_key_match_result){
			return true;
		}else{
			return false;
		}
	}
	
	function qrhash_authentication_settings($param){

		

		$result_array = array(

			"request_status" => "rejected",

			"login_key" => "null",

			"settings_name" => "null"

		);



		if(isset($param['qr_hash'])){				

			

			$qr_hash_call = $param['qr_hash'];
			$android_app = $param['android_app'];

			$qr_hash_option = get_option('wmqr_qrcode_hash');				

			$wmqr_qrcode_hash = $qr_hash_option['wmqr_qrcode_hash'];

			// $wmqr_qrcode_hash = "a";

			$wmqr_qrcode_hash_time = $qr_hash_option['wmqr_qrcode_hash_time'];

			

			if($wmqr_qrcode_hash == $qr_hash_call){

				$rand_login_key = $this->generate_random_login_key($android_app);			

				if($rand_login_key != false){
					$result_array["request_status"] = "active";
					$result_array["login_key"] = $rand_login_key;
					$result_array["settings_name"] = get_bloginfo();
				}

			}			

		

		}



		$res = new WP_REST_Response($result_array);			

		return $res;

	}

	function register_qrhash_authentication_settings() {		

		

		register_rest_route( $this->rest_api_url, '/authentication', array(

		  'methods' => 'POST',

		  'callback' => array($this,'qrhash_authentication_settings'),

		));
	}	
	
	public function get_hash_option_encoded($app_slug=''){
		$login_key_option_name = $this->get_login_key_option_name();
		$qr_hash_option = get_option($login_key_option_name);	
		
		if($app_slug!=''){
			if(is_array($qr_hash_option) && array_key_exists($app_slug, $qr_hash_option)){
				$qr_hash_option = $qr_hash_option[$app_slug];
				$qr_hash_option = base64_encode(serialize($qr_hash_option));
			}else{
				$qr_hash_option = '';
			}
		
		}else{
			
		}
		return $qr_hash_option;
		
	}
	
	public function wm_app_connect_status(){
		
		if(isset($_POST['hash_option'])){
			$app_slug = esc_attr($_POST['app_slug']);
			$hash_option_encoded = esc_attr($_POST['hash_option']);
			if($this->get_hash_option_encoded($app_slug)==$hash_option_encoded){
				echo 'no-change';
			}else{
				echo 'refresh-required';
			}
		}
		exit;
	}
	

	
	
	function wm_disconnect_app(){

		$android_app = esc_attr($_POST['android_app']);		
		$login_key_name = $this->get_login_key_option_name();
		$login_key_array = get_option($login_key_name);		
		
		if(array_key_exists($android_app, $login_key_array)){
			unset($login_key_array[$android_app]);
		}
		
		update_option($login_key_name, $login_key_array);		
					
		exit;
	}
		

	function wm_generate_qrcode() {

					
		$tempDir = $this->plugin_dir."barcode/";
		
		if(!file_exists($tempDir)){
			mkdir($tempDir);
		}
		$url = $this->plugin_url."barcode/";		
			
		
		$files = glob($tempDir.'*'); // get all file names		

		if(!empty($files)){

			foreach($files as $file){ // iterate files

			if(is_file($file))

				unlink($file); // delete file

			}

		}

		

		$wmqr_qrcode_hash_array = array();

		$codeContents = array();

		$rand_no = rand();

		$rand_no_qr = md5($rand_no);

		$codeContents['url'] = get_home_url()."/wp-json/".$this->rest_api_url."/";
		// $codeContents['url'] = "http://192.168.43.248:82/wp-json/".$this->rest_api_url."/";

		$codeContents['qr_hash'] = $rand_no_qr;

		$wmqr_qrcode_hash_array['wmqr_qrcode_hash_time'] = time()+30;

		$wmqr_qrcode_hash_array['wmqr_qrcode_hash'] = $codeContents['qr_hash'];

		update_option('wmqr_qrcode_hash', $wmqr_qrcode_hash_array);



		$qr_content = json_encode($codeContents);

		$fileName = 'barcode'.rand().'.png';

		$pngAbsoluteFilePath = $tempDir.$fileName;		

		WMQRcode::png($qr_content, $pngAbsoluteFilePath,QR_ECLEVEL_L,10);
		
		//echo '<img src="'.$url.$fileName.'" />';
		echo $url.$fileName;


		wp_die();



	}

	function register_api_users_by_page(){		
		
		register_rest_route($this->rest_api_url, '/get_wordpress_users_by_page', array(

			'methods' => 'POST',

			'callback' => array($this, 'api_get_wordpress_users_by_page'),

		));

		register_rest_route($this->rest_api_url, '/get_wordpress_users_by_id', array(

			'methods' => 'POST',

			'callback' => array($this, 'get_wordpress_users_by_id'),

		));



		register_rest_route($this->rest_api_url, '/wm_get_countries', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_api_get_countries'),

		));


		register_rest_route($this->rest_api_url, '/wm_api_update_user', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_api_update_user'),

		));
	}
	
	function register_api_acrobatics(){		
		
		register_rest_route( $this->rest_api_url, '/get_wm_acrobatics', array(

		  'methods' => 'POST',

		  'callback' => array($this, 'get_wm_acrobatics'),

		));
	}	

	private function execute_settings_api(){

		add_action( 'rest_api_init', array($this, 'register_api_read_settings'));
		add_action( 'rest_api_init', array($this, 'register_api_users_by_page'));
		add_action( 'rest_api_init', array($this, 'register_api_acrobatics'));
		add_action( 'rest_api_init', array($this, 'register_qrhash_authentication_settings'));
		add_action( 'rest_api_init', array($this, 'wm_register_api_wc_orders'));
		add_action('rest_api_init', array($this, 'wm_register_api_wc_products'));
		add_action( 'wp_ajax_wm_generate_qrcode', array($this, 'wm_generate_qrcode') );
		add_action( 'wp_ajax_wm_disconnect_app', array($this, 'wm_disconnect_app') );
		add_action( 'wp_ajax_wm_app_connect_status', array($this, 'wm_app_connect_status') );
	}
	

	public static function ab_io_display($wm_plugin_url){
		
	

		?>
	
		<div class="wm-qrcode-body">



			<div class="wm-qrcode-view">



				<img class="qr-sample" title="<?php _e('Click here to refresh QR Code!', 'wp-mechanic');?>" src="<?php echo $wm_plugin_url.'images/sample.png' ?>">

				

			</div>

			



			<div class="qr-modal">

				<span class="qr-modal-close">&times;</span>

				<!-- Modal content -->

				<div class="modal-content">

					

					<div class="wm-qrcode-img">

						<span class="qr-loading">Loading....</span>

					</div>

				</div>



			</div>



		</div>

		<?php		

	}




	function wm_api_get_countries($param)
	{


		$countries = WC()->countries->countries;
		$states = WC()->countries->get_states();

		$countries_arr = array();
		if (!empty($countries)) {
			foreach ($countries as $country_code => $country_name) {
				# code...
				$states_array = (isset($states[$country_code])) ? $states[$country_code] : array();
				$state_array_final = array();
				if (!empty($states_array)) {

					foreach ($states_array as $state_code => $state_name) {
						# code...
						$state_array_final[] = array(
							"stateCode" => $state_code,
							"stateName" => $state_name,
						);
					}
				}

				$countries_arr[] = array(
					"countryCode" => $country_code,
					"countryName" => $country_name,
					"states" => $state_array_final,
				);
			}
		}






		$res = new WP_REST_Response($countries_arr);
		return $res;
	}


	function wm_api_update_user($param)
	{

		$login_key = $param['login_key'];
		$android_app = $param['android_app'];

		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));

		// $login_key_status = true;


		// if($login_key == base64_decode('MTIz')){			
		if ($login_key_status == true) {

			$user_data = $param['updated_user_json'];
			$user_data = json_decode($user_data, true);
			$shipping = $user_data['shipping'];
			$billing = $user_data['billing'];
			$user_id = $user_data['ID'];


			unset($user_data['shipping']);
			unset($user_data['billing']);

			if (!empty($shipping)) {
				foreach ($shipping as $ship_key => $ship_value) {
					# code...				
					update_user_meta($user_id, "shipping_" . $ship_key, $ship_value);
				}
			}

			if (!empty($billing)) {
				foreach ($billing as $billing_key => $billing_value) {
					# code...				
					update_user_meta($user_id, "billing_" . $billing_key, $billing_value);
				}
			}


			$user = new WP_User($user_id);
			$user->set_role($user_data['roles'][0]);


			$update_user = wp_update_user($user_data);

			$return_array = array(
				"ID" => $update_user,
			);
		} else {
			$return_array = array(
				"ID" => -1,
			);
		}


		$res = new WP_REST_Response($return_array);
		return $res;
	}

	function get_country_code_by_name($country_name, $default_return = "")
	{

		$country_code = array_search($country_name, WC()->countries->countries);
		$country_code = (!empty($ship_country)) ? $country_code : $default_return;

		return $country_code;
	}

	function get_wordpress_users_by_id($param)
	{

		$user_data_json = $this->api_get_wordpress_users_by_page($param);
		$user_data = $user_data_json->data;
		if (array_key_exists("users", $user_data)) {
			$current_user = $user_data["users"][0];
		} else {
			$current_user = array("ID" => "-1");
		}
		$res = new WP_REST_Response($current_user);
		return $res;
	}
	function wm_register_api_wc_orders()
	{

		register_rest_route($this->rest_api_url, '/get_wc_orders_data', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_get_wc_orders_data'),

		));

		register_rest_route($this->rest_api_url, '/get_wc_order_data_by_id', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_get_wc_order_data_by_Id'),

		));
		
	}

	function wm_get_wc_orders_data($param)
	{

		
		$login_key = $param['login_key'];
		$limit = $param['number'];
		$paged = $param['paged'];
		$offset = $param['offset'];
		$search = $param['search'];
		$order_status = $param['status'];
		$android_app = $param['android_app'];

		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));


		if ($login_key_status == true) {

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				//plugin is activated
				$wm_is_woo_active = true;
			} else {
				$wm_is_woo_active = false;
			}


			if ($wm_is_woo_active == true) {
				// ** Start ** Get all order status and their order qty

				$statuses = array();

				$get_statuses = wc_get_order_statuses();

				if (!empty($get_statuses)) {

					$status_order_args = array(
						'limit' => -1,
						'return' => 'ids',
					);

					foreach ($get_statuses as $status_key => $status_value) {
						# code...
						$status_order_args['status'] = $status_key;
						$statuses[] = array(
							"status_key" => $status_key,
							"status_value" => $status_value,
							"total_orders" => sizeof(wc_get_orders($status_order_args)),
						);
					}
				}

				$search_array = array(
					array(
						'key' => '_billing_address_1',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_billing_address_2',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_billing_city',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_billing_company',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_billing_email',
						'compare' => '=',
					),

					array(
						'key' => '_billing_postcode',
						'compare' => '=',
					),

					array(
						'key' => '_billing_phone',
						'compare' => '=',
					),

					array(
						'key' => '_billing_first_name',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_billing_last_name',
						'compare' => 'LIKE',
					),

					array(
						'key' => '_customer_ip_address',
						'compare' => '=',
					),

					array(
						'key' => '_price',
						'compare' => '=',
					),

					array(
						'key' => '_order_total',
						'compare' => '=',
					),


				);

				// ** End ** Get all order status and their order qty

				$filter_orders = 0;
				if (isset($search) && !empty($search)) {

					

					$meta_query = array('relation' => 'OR');
					
					if(!empty($search_array)){
						foreach ($search_array as $search_meta) {
							# code...
							$search_meta['value'] = $search;
							$meta_query[] = $search_meta;
						}
					}

					

					$args   =   array(
						'numberposts'   => 1,
						'post_type'     => 'shop_order',
						"include" => array($search),
						'post_status' => 'any',
						

					);

					$orders_posts = get_posts($args);
					$filter_orders = 1;

					if(sizeof($orders_posts) != 1){
						
						$args   =   array(
							'numberposts'   => $limit,
							'page'   => $paged,
							'offset'   => $offset,
							'post_type'     => 'shop_order',
							'post_status' => 'any',
							'meta_query' => $meta_query,						
	
						);

						$filter_args = array(
							
							'numberposts'   => -1,
							'post_type'     => 'shop_order',
							'post_status' => 'any',
							'meta_query' => $meta_query,					
	
						);

						if (isset($order_status) && !empty($order_status)) {
							$args['post_status'] = $order_status;
							$filter_args['post_status'] = $order_status;
						}
	
						$orders_posts = get_posts($args);

						$filter_orders = sizeof(get_posts($filter_args));

					}

					
				}else{


				$args   =   array(
					'numberposts'   => $limit,
					'page'   => $paged,
					'offset'   => $offset,
					'post_type'     => 'shop_order',
					'post_status' => 'any',							

				);	

				$filter_args = array(
							
					'numberposts'   => -1,
					'post_type'     => 'shop_order',
					'post_status' => 'any',	
				);
				
				if (isset($order_status) && !empty($order_status)) {
					$args['post_status'] = $order_status;
					$filter_args['post_status'] = $order_status;
				}

				$filter_orders = sizeof(get_posts($filter_args));
				$orders_posts = get_posts($args);

			}

				// for getting order numbers and max page

				$args = array(
					'limit' => 1,					
					'paginate' => true,
					
				);			

				$orders =  wc_get_orders($args);

				$orders_list = array();

				if (!empty($orders_posts)) {
					foreach ($orders_posts as $order_key => $order_value) {
						# code...
						$id = $order_value->ID;
						$status = str_replace("wc-", "", $order_value->post_status) ;
						$date_created = json_decode(json_encode($order_value->post_date), true);
						// pree($single_order_data['date_created']->WC_DateTime['date']);exit;
						
						
						$orders_list[] = array(
							"id" => $id,
							"status" => $status,
							"date_created" => date("M d Y h:i A", strtotime($date_created)),
						);
					}
				}

			

				// pree($orders);exit;

				$orders_data = array(
					"total_orders" => $orders->total,
					"filter_orders" => $filter_orders,
					"max_num_pages" => $orders->max_num_pages,
					"order_statuses" => $statuses,
					"orders_list" => $orders_list,
				);
			} else {

				$orders_data = array(
					"filter_orders" => -2,
				);
			}
		} else {

			$orders_data = array(
				"filter_orders" => -1,
			);
		}

		$res = new WP_REST_Response($orders_data);
		return $res;
	}

	function wm_get_wc_order_data_by_Id($param)
	{
		$login_key = $param['login_key'];
		$order_id = $param['order_id'];
		$android_app = $param['android_app'];

		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));


		if ($login_key_status == true) {

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				//plugin is activated
				$wm_is_woo_active = true;
			} else {
				$wm_is_woo_active = false;
			}


			if ($wm_is_woo_active == true) {

				//code start from here

				$order = 	new WC_Order($order_id);
				$order_data = $order->get_data();
				$order_shipping = $order_data['shipping'];
				$order_billing = $order_data['billing'];

				$items = $order->get_items();
				$all_order_products = array();

				if (!empty($items)) {
					foreach ($items as $item_key => $item_value) {
						# code...
						$current_item = new WC_Order_Item_Product($item_key);
						$product = $current_item->get_data();

						$product_array = array();
						$product_array['id'] = $product['id'];
						$product_array['name'] = $product['name'];
						$product_array['order_id'] = $product['order_id'];
						$product_array['product_id'] = $product['product_id'];
						$product_array['quantity'] = $product['quantity'];
						$product_array['subtotal'] = $product['subtotal'];
						$product_array['total'] = $product['total'];
						$product_array['image'] = wp_get_attachment_image_src(get_post_thumbnail_id($product['product_id']), 'single-post-thumbnail')[0];
						$product_array['price'] = $product['subtotal'] / $product['quantity'];
						$all_order_products[] = $product_array;
					}
				}

				if (!empty($order_shipping['state'])) {


					$states_shipping = WC()->countries->get_states($order_shipping['country']);

					if (!empty($states_shipping)) {

						$order_shipping['state'] = $states_shipping[$order_shipping['state']];
					}
				}

				if (!empty($order_billing['state'])) {

					$states_billing = WC()->countries->get_states($order_billing['country']);

					if (!empty($states_billing)) {

						$order_billing['state'] = $states_billing[$order_billing['state']];
					}
				}


				if (!empty($order_shipping['country'])) {
					$order_shipping['country'] = WC()->countries->countries[$order_shipping['country']];
				}

				if (!empty($order_billing['country'])) {
					$order_billing['country'] = WC()->countries->countries[$order_billing['country']];
				}

				$order_data['shipping_method'] = $order->get_shipping_method();
				$order_data['shipping'] = $order_shipping;
				$order_data['billing'] = $order_billing;
				$order_data['products'] = $all_order_products;
				$order_data['currency_symbol'] = html_entity_decode(get_woocommerce_currency_symbol($order_data['currency']));
				$order_data['date_created'] = date("M d Y h:i A", strtotime(json_decode(json_encode($order_data['date_created']), true)['date']));
				$order_data['date_modified'] = date("M d Y h:i A", strtotime(json_decode(json_encode($order_data['date_modified']), true)['date']));
				$order_data['date_completed'] = date("M d Y h:i A", strtotime(json_decode(json_encode($order_data['date_completed']), true)['date']));
				$order_data['date_paid'] = date("M d Y h:i A", strtotime(json_decode(json_encode($order_data['date_paid']), true)['date']));

				unset($order_data['coupon_lines']);
				unset($order_data['fee_lines']);
				unset($order_data['shipping_lines']);
				unset($order_data['tax_lines']);
				unset($order_data['line_items']);
			} else {

				$order_data = array(
					"id" => -2,
				);
			}
		} else {

			$order_data = array(
				"id" => -1,
			);
		}

		$res = new WP_REST_Response($order_data);
		return $res;
	}
	
	function wm_register_api_wc_products()
	{

		
		register_rest_route($this->rest_api_url, '/wm_get_wc_products_data_by_id', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_get_wc_products_data_by_id'),

		));

		register_rest_route($this->rest_api_url, '/wm_get_wc_products_data', array(

			'methods' => 'POST',

			'callback' => array($this, 'wm_get_wc_products_data'),

		));
	}

	function wm_get_wc_products_data($param)
	{
		$login_key = $param['login_key'];
		$limit = $param['number'];
		$paged = $param['paged'];
		$offset = $param['offset'];
		$search = $param['search'];
		$stock_status = $param['status'];
		$android_app = $param['android_app'];
		$stock_status_option = wc_get_product_stock_status_options();

		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));


		if ($login_key_status == true) {

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				//plugin is activated
				$wm_is_woo_active = true;
			} else {
				$wm_is_woo_active = false;
			}

			if ($wm_is_woo_active == true) {

				// ** Start ** get products

				$search_array = array(
					
					array(
						'key' => '_sku',
						'compare' => 'LIKE',
					),					

					array(
						'key' => '_regular_price',
						'compare' => '=',
					),

					array(
						'key' => '_price',
						'compare' => 'LIKE',
					),

				);


				$filter_products = 0;

				$stock_status_meta = array(
					'key' => '_stock_status',
					'value' => sanitize_text_field($stock_status),
					'compare' => '=',
				);

				if (isset($search) && !empty($search)) {



					$meta_query = array('relation' => 'OR');

					if (!empty($search_array)) {
						foreach ($search_array as $search_meta) {
							# code...
							$search_meta['value'] = sanitize_text_field($search);
							$meta_query[] = $search_meta;
						}
					}

					$args   =   array(
						'numberposts'   => 1,
						'post_type'     => 'product',
						"include" => array(sanitize_text_field($search)),
						'post_status' => 'any',


					);

					$product_posts = get_posts($args);
					$filter_products = 1;

					if (sizeof($product_posts) != 1) {

						$args   =   array(
							'numberposts'   => $limit,
							'page'   => $paged,
							'offset'   => $offset,
							's' => sanitize_text_field($search),
							'post_type'     => 'product',
							'post_status' => 'any',
							'meta_query' => $meta_query,

						);

						$filter_args = array(

							'numberposts'   => -1,
							'post_type'     => 'product',
							'post_status' => 'any',
							's' => sanitize_text_field($search),
							'meta_query' => $meta_query,

						);

						

						if (isset($stock_status) && !empty($stock_status)) {
							$args['meta_query'][] = $stock_status_meta;
							$filter_args['meta_query'][] = $stock_status_meta;

						}

						$product_posts = get_posts($args);
						$filter_products = sizeof(get_posts($filter_args));
					}
				} else {


					$args   =   array(
						'numberposts'   => $limit,
						'page'   => $paged,
						'offset'   => $offset,
						'post_type'     => 'product',
						'post_status' => 'any',

					);

					$filter_args = array(

						'numberposts'   => -1,
						'post_type'     => 'product',
						'post_status' => 'any',
					);

					if (isset($stock_status) && !empty($stock_status)) {
						$args['meta_query'][] = $stock_status_meta;
						$filter_args['meta_query'][] = $stock_status_meta;						
					}

					$filter_products = sizeof(get_posts($filter_args));
					$product_posts = get_posts($args);
				}				

				$args = array(
					'limit' => 1,					
					'paginate' => true,
					
				);			

				$products_object =  wc_get_products($args);		


				$product_list = array();

				if (!empty($product_posts)) {

					foreach ($product_posts as $product_key => $product_value) {
						# code...

						$product_id = $product_value->ID;
						$category_list = "";
						foreach ((get_the_terms($product_id, 'product_cat')) as $category) {
							$category_list .=	$category->name . ', ';
						}
						$product_list[] = array(
							"id" => $product_id,
							"name" => $product_value->post_title,
							"sku" => get_post_meta($product_id, "_sku", true),
							"price" => get_post_meta($product_id, "_price", true),
							"sale_price" => get_post_meta($product_id, "_sale_price", true),
							"stock_status" => $stock_status_option[get_post_meta($product_id, "_stock_status", true)],
							"stock_quantity" => get_post_meta($product_id, "_stock", true),
							"product_image" => $this->wm_get_product_image($product_id),
							"category" => trim(trim($category_list), ","),

						);
					}
				}
				



				$stock_statuses = array();


				if (!empty($stock_status_option)) {
					foreach ($stock_status_option as $status_key => $status_value) {
						# code...
						$stock_args = array(

							'limit' => -1,
							'stock_status' => $status_key,

						);

						$stock_status_products = wc_get_products($stock_args);

						$stock_statuses[] = array(
							'status_key' => $status_key,
							'status_value' => $status_value,
							'total_count' => sizeof($stock_status_products),
						);
					}
				}


				// ** end ** get status



				$product_data = array(

					"total_products" => $filter_products,
					"filter_products" => sizeof(wc_get_products(array("limit" => -1))),
					"max_num_pages" => $products_object->max_num_pages,
					"currency_symbol" => $this->wm_get_currency_symbol(get_woocommerce_currency()),
					"products" => $product_list,
					"stock_statuses" => $stock_statuses,

				);
			} else {

				$product_data = array(
					"id" => -2,
				);
			}
		} else {

			$product_data = array(
				"id" => -1,
			);
		}

		$res = new WP_REST_Response($product_data);
		return $res;
	}

	function wm_get_wc_products_data_by_id($param)
	{
		$login_key = $param['login_key'];
		$android_app = $param['android_app'];
		$product_id = $param['product_id'];

		$stock_status_option = wc_get_product_stock_status_options();

		$login_key_status = $this->validate_login_key($login_key, $android_app);
		update_option('wm_android_app_log', array($login_key, $login_key_status));


		if ($login_key_status == true) {

			global $wm_plugin_url, $wm_is_woo_active;
			$wm_plugin_url = $this->plugin_url;
			if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
				//plugin is activated
				$wm_is_woo_active = true;
			} else {
				$wm_is_woo_active = false;
			}

			if ($wm_is_woo_active == true) {

				// ** Start ** get products




				$product_object = wc_get_product($product_id);
				$is_on_sale = $product_object->is_on_sale();
				$product_data = $product_object->get_data();
				$product_attr = $this->wc_get_product_attributes($product_object);


				$product_data['attributes'] = $product_attr;
				$product_data['upsell_products'] = $this->wc_get_up_cross_products($product_data['upsell_ids']);
				$product_data['cross_sell_products'] = $this->wc_get_up_cross_products($product_data['cross_sell_ids']);

				$product_data['product_images'][0] = (!empty($product_data['image_id'])) ? $this->wm_get_product_image($product_id) : wc_placeholder_img_src();
				if (!empty($product_data['gallery_image_ids'])) {
					foreach ($product_data['gallery_image_ids'] as $image_id) {
						# code...
						$product_thumbnail = wp_get_attachment_image_src($image_id, 'single-post-thumbnail')[0];

						if (empty($product_thumbnail) || is_null($product_thumbnail) || $product_thumbnail == null) {
							$product_thumbnail = wc_placeholder_img_src();
						}

						$product_data['product_images'][] = $product_thumbnail;
					}
				}



				$product_data['categories'] = $this->wc_get_categories_string($product_id, 'product_cat',  ', ');
				$product_data['tags'] = $this->wc_get_categories_string($product_id, 'product_tag',  ', ');
				$product_data['date_created'] = date("M d Y h:i A", strtotime(json_decode(json_encode($product_data['date_created']), true)['date']));
				$product_data['stock_status'] = $stock_status_option[$product_data['stock_status']];


				$product_data['is_on_sale'] = $is_on_sale;

				if ($is_on_sale && !$product_object->is_type('grouped')) {

					$product_data['sale_percentage'] = ceil(($product_data['regular_price'] - $product_data['sale_price']) / $product_data['regular_price'] * 100) . "%";
				} else {

					$product_data['sale_percentage'] = "0%";
				}




				$product_required_fields = array(
					'id',
					'name',
					'slug',
					'date_created',
					'featured',
					'short_description',
					'price',
					'regular_price',
					'sale_price',
					'total_sales',
					'manage_stock',
					'stock_quantity',
					'stock_status',
					'reviews_allowed',
					'purchase_note',
					'attributes',
					'review_count',
					'upsell_products',
					'cross_sell_products',
					'product_images',
					'categories',
					'tags',
					'is_on_sale',
					'sale_percentage',
				);

				$single_product = array();

				if (!empty($product_data) && !empty($product_required_fields)) {
					foreach ($product_required_fields as $key) {
						# code...
						$single_product[$key] = $product_data[$key];
					}
				}


				// pree(wc_get_product_category_list($product_id));exit;
				// pree(wp_get_post_tags( $product_id));exit;



				// ** End ** get products








			} else {

				$single_product = array(
					"id" => -2,
				);
			}
		} else {

			$single_product = array(
				"id" => -1,
			);
		}

		$res = new WP_REST_Response($single_product);
		return $res;
	}


	private function wm_get_product_image($product_id)
	{
		$product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'single-post-thumbnail')[0];

		if (empty($product_image) || is_null($product_image) || $product_image == null) {
			$product_image = wc_placeholder_img_src();
		}

		return $product_image;
	}

	private function wm_get_currency_symbol($code)
	{
		return html_entity_decode(get_woocommerce_currency_symbol($code));
	}

	private function wc_get_product_attributes($product)
	{
		$product_attributes = array();

		// Display weight and dimensions before attribute list.
		$display_dimensions = apply_filters('wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions());

		if ($display_dimensions && $product->has_weight()) {
			$product_attributes[] = array(
				'key' => 'weight',
				'label' => __('Weight', 'woocommerce'),
				'value' => wc_format_weight($product->get_weight()),
			);
		}

		if ($display_dimensions && $product->has_dimensions()) {
			$product_attributes[] = array(
				'key' => 'dimensions',
				'label' => __('Dimensions', 'woocommerce'),
				'value' => str_replace('&times;', "X", wc_format_dimensions($product->get_dimensions(false))),
			);
		}

		// Add product attributes to list.
		$attributes = array_filter($product->get_attributes(), 'wc_attributes_array_filter_visible');

		foreach ($attributes as $attribute) {
			$values = array();

			if ($attribute->is_taxonomy()) {
				$attribute_values   = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'all'));

				foreach ($attribute_values as $attribute_value) {
					$value_name = esc_html($attribute_value->name);
					$values[] = $value_name;
				}
			} else {

				$values = $attribute->get_options();
			}

			$product_attributes[] = array(
				'key' => 'attribute_' . sanitize_title_with_dashes($attribute->get_name()),
				'label' => wc_attribute_label($attribute->get_name()),
				'value' => wptexturize(implode(', ', $values)), //apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values ),
			);

			// pree($values);
			// pree($attribute);exit;
		}

		return $product_attributes;
	}

	private function wc_get_up_cross_products($product_ids)
	{
		$products = array();
		if (!empty($product_ids)) {
			foreach ($product_ids as $p_id) {
				# code...
				$product = get_product($p_id);
				$products[] = array(

					'id' => $product->get_id(),
					'product_url' => $product->get_permalink($p_id),
					'product_image' => $this->wm_get_product_image($p_id),
					'name' => $product->get_name() . " - " . $this->wm_get_currency_symbol(get_woocommerce_currency()) . $product->get_price(),

				);
			}
		}

		return $products;
	}

	private function wc_get_categories_string($product_id, $taxanomy, $separate)
	{
		$cats = get_the_terms($product_id, $taxanomy);
		$cats_array = array();
		if (!empty($cats)) {


			foreach ($cats as $key => $value) {
				# code...
				$cats_array[] = $value->name;
			}
		}

		return wptexturize(implode($separate, $cats_array));
	}
}

