<?php
if (!defined('WP_UNINSTALL_PLUGIN'))
{
	exit; // Exit if accessed directly
}

$wsko_data = get_option('wsko_init');
if (isset($wsko_data['clean_uninstall']) && $wsko_data['clean_uninstall'])
{
	global $wpdb;

	delete_option('wsko_init');
	wp_clear_scheduled_hook('wsko_cache_keywords');
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'wsko_cache');
	$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'wsko_cache_rows');
	
	$msgs = get_posts(array('post_type' => 'wsko_log_report', 'post_status' => 'any', 'numberposts' => -1));
	foreach ($msgs as $p)
	{
		wp_delete_post($p->ID, true);
	}
	
	global $wp_roles;
	$roles = $wp_roles->get_names(); 
	foreach ($roles as $k => $role)
	{
		$r = get_role($k);
		if ($r)
			$r->remove_cap('wsko_can_view');
	}
}
?>