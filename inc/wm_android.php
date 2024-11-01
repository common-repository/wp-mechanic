<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
	global $wm_url, $wm_data, $WMQR_Code_Settings, $wm_pro, $wm_apps_arr;
	if(!empty($wm_apps_arr)){
			

	global $wm_url;
	$login_key_option_name = $WMQR_Code_Settings->get_login_key_option_name();
	//pree($login_key_option_name);
	$qr_hash_option = get_option($login_key_option_name);
	//pree($qr_hash_option);
	//pree($wm_data);
	//$wordpress_users = get_users();
	
	//pree($wordpress_users);
	/*foreach($wordpress_users as $udata){
		//pree($udata->ID);
		pree(get_avatar_url($udata->ID, array('size'=>256)));
	}*/
	//pree($WMQR_Code_Settings->validate_login_key($login_key_option_name));
	//pree(get_option('wm_android_app_log'));

?>
<div class="wm-apps-wrapper">

<h2><span class="dashicons dashicons-welcome-widgets-menus"></span>&nbsp;<?php echo $wm_data['Name'].' '.'('.$wm_data['Version'].($wm_pro?') '.__('Pro', 'wp-mechanic').'':')'); ?> - <?php _e('Settings', 'wp-mechanic'); ?></h2>
<p><?php echo $wm_data['Description']; ?></p>
<ul>
<?php		
		foreach($wm_apps_arr as $slug=>$data){
			$connected = (!empty($qr_hash_option) && array_key_exists($slug, $qr_hash_option) && !empty($qr_hash_option[$slug]));
			$app_slug_js = str_replace(array('.'), '_', $slug);
			
?>
<li class="<?php echo (!$connected?'disconnected':'connected'); ?>" data-slug="<?php echo $slug; ?>" data-slug-js="<?php echo $app_slug_js; ?>">
<div class="left-part">
<div class="upper-part">
<img src="<?php echo $wm_url; ?>images/android-applications/<?php echo str_replace(array('.'), '-', $slug); ?>.png" alt="<?php echo $data['title']; ?>" /><br />
<strong><?php echo $data['title']; ?></strong>
</div>
<div class="lower-part">
<a target="_blank" class="download_app" href="https://play.google.com/store/apps/details?id=<?php echo $slug; ?>"><?php echo ($data['premium']?__('Purcahse App', 'wp-mechanic'):__('Download App', 'wp-mechanic')); ?></a><a class="configure_app"><?php echo __('Configure', 'wp-mechanic').($connected?'d':''); ?></a>
</div>
</div>
<div class="right-part">
<?php WMQR_Code_Settings::ab_io_display($wm_url); ?>
<div class="lower-part">
<strong><?php echo ($connected?__('Connected', 'wp-mechanic'):__('Disconnected', 'wp-mechanic')); ?></strong>
<?php if(!$connected): ?>
<a class="connect_app" data-slug="<?php echo $slug; ?>"><?php _e('Scan to connect', 'wp-mechanic'); ?></a>
<?php else: ?>
<a class="disconnect_app" data-slug="<?php echo $slug; ?>"><?php _e('Disconnect', 'wp-mechanic'); ?></a>
<?php endif; ?>
</div>
</div>
</li>
<?php			
		}
?>
</ul>
</div>
<?php			
	}
?>