<?php
global $wsko_data;

if (!$wsko_data || !is_array($wsko_data))
{
	wsko_install_plugin();
	$wsko_data_t = array(
		'version' => WSKO_VERSION,
		'token' => false,
		'add_bootstrap' => true,
		'add_fontawesome' => true,
		'add_moment' => true,
		'add_bootstrap_datepicker' => true,
		'add_datatables' => true,
		'add_google_chart' => true,
		'activate_post_widget' => true,
		'activate_caching' => true,
		'activate_log' => false
	);
	
	if ($wsko_data)
		update_option('wsko_init', $wsko_data_t);
	else
		add_option('wsko_init', $wsko_data_t);
	
	$wsko_data = $wsko_data_t;
}
else
{
	if (version_compare($wsko_data['version'], WSKO_VERSION, '<'))
	{
		$update_needed = false;
		
		if (version_compare($wsko_data['version'], '1.1.3', '<'))
		{
			delete_option('wsko_init');
			
			delete_option('bv_add_bootstrap');
			delete_option('bv_add_fontawesome');
			delete_option('bv_add_moment');
			delete_option('bv_add_bootstrap_datepicker');
			delete_option('bv_add_datatables');
			delete_option('bv_add_google_chart');
			
			delete_option('wsko_activate_post_widget');
			
			delete_option('wsko_keyword_max_time');
			delete_option('wsko_keyword_limit_main');
			delete_option('wsko_keyword_limit_dashboard');
			delete_option('wsko_keyword_limit_post');
			$update_needed = true;
		}
		
		/*if (version_compare($wsko_data['version'], '1.2.4', '<=')) //Duplicate with new 1.2.8
		{
			wp_clear_scheduled_hook('wsko_cache_keywords');
			wp_schedule_event(wsko_get_midnight(time()), 'daily', 'wsko_cache_keywords');
		}*/
		
		if (version_compare($wsko_data['version'], '1.2.7', '<'))
		{
			global $wpdb;
			$rows_table = $wpdb->prefix . 'wsko_cache_rows';
			$wpdb->query( "ALTER TABLE " . $rows_table . " DROP COLUMN ctr" );
			$wpdb->query( "ALTER TABLE " . $rows_table . " MODIFY position DOUBLE" );
			
			wsko_cache_keywords(); //recache
			$update_needed = true;
		}
		
		if (version_compare($wsko_data['version'], '1.2.8', '<'))
		{
			$msgs = get_posts(array('post_type' => WSKO_POST_TYPE_ERROR, 'post_status' => 'any', 'numberposts' => -1));
			foreach ($msgs as $p)
			{
				wp_delete_post($p->ID, true);
			}
			
			/*wp_clear_scheduled_hook('wsko_cache_keywords'); //Duplicate with new 1.2.9
			wp_schedule_event(wsko_get_midnight(time()), 'daily', 'wsko_cache_keywords');*/
		}
		
		if (version_compare($wsko_data['version'], '1.2.9', '<'))
		{
			wp_clear_scheduled_hook('wsko_cache_keywords');
			wp_schedule_event(wsko_get_midnight(time()), 'daily', 'wsko_cache_keywords');
			$update_needed = true;
		}
		
		if ($update_needed)
		{
			$wsko_data = array(
				'version' => WSKO_VERSION,
				'token' => false,
				'add_bootstrap' => true,
				'add_fontawesome' => true,
				'add_moment' => true,
				'add_bootstrap_datepicker' => true,
				'add_datatables' => true,
				'add_google_chart' => true,
				'add_icheck' => true,
				'activate_post_widget' => true,
				'activate_caching' => true,
				'activate_log' => false
			);
		}
		else
		{
			$wsko_data['version'] = WSKO_VERSION;
		}
		
		if (get_option('wsko_init'))
			update_option('wsko_init', $wsko_data);
		else
			add_option('wsko_init', $wsko_data);
	}
}
?>