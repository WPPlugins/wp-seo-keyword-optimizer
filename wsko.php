<?php
/*
Plugin Name: 	WP SEO Keyword Optimizer
Plugin URI: 	http://www.bavoko.services/wordpress/
Description: 	Show Keyword Data from the Google Search Console right inside your Wordpress!
Version: 		1.2.10
Author: 		BAVOKO
Author URI: 	http://www.bavoko.services/
License:     	GPL2 or later
*/
/*
WP SEO Keyword Optimizer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WP SEO Keyword Optimizer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/
if (!defined('ABSPATH')) exit;

global $wpdb, $wsko_plugin_path, $wsko_test, $wsko_data;
$wsko_plugin_path = dirname(__FILE__) . '/';

$wsko_test = false;

$wsko_data = get_option('wsko_init');

//warning: any constant here is hardcoded in uninstall.php
define('WSKO_VERSION', '1.2.10');
define('WSKO_KEYWORD_LIMIT', $wsko_data && isset($wsko_data['keyword_limit_main']) && $wsko_data['keyword_limit_main'] ? intval($wsko_data['keyword_limit_main']) : 5000);
define('WSKO_KEYWORD_LIMIT_DASHBOARD',  $wsko_data && isset($wsko_data['keyword_limit_dashboard']) && $wsko_data['keyword_limit_dashboard'] ? intval($wsko_data['keyword_limit_dashboard']) : 5000);
define('WSKO_KEYWORD_LIMIT_POST',  $wsko_data && isset($wsko_data['keyword_limit_post']) && $wsko_data['keyword_limit_post'] ? intval($wsko_data['keyword_limit_post']) : 100);
define('WSKO_DEFAULT_INTERVAL_POST',  $wsko_data && isset($wsko_data['post_time']) && $wsko_data['post_time'] ? intval($wsko_data['post_time']) : 27);
define('WSKO_CACHE_TIME_LIMIT', $wsko_data && isset($wsko_data['cache_time_limit']) && $wsko_data['cache_time_limit'] ? intval($wsko_data['cache_time_limit']) : false);

define('WSKO_CACHE_TABLE', $wpdb->prefix . 'wsko_cache');
define('WSKO_CACHE_ROWS_TABLE', $wpdb->prefix . 'wsko_cache_rows');

define('WSKO_POST_TYPE_ERROR', 'wsko_log_report');

define('WSKO_FEEDBACK_MAIL', 'info@bavoko.com');
define('WSKO_VIEW_CAP', 'wsko_can_view');

require_once(plugin_dir_path( __FILE__ ) . '/functions.php');

add_action('wsko_cache_keywords', 'wsko_cache_keywords'); //Late bind, origin in functions.php

function wsko_install_plugin()
{
	if (!wp_next_scheduled('wsko_cache_keywords'))
	{
		wp_schedule_event(wsko_get_midnight(time()), 'daily', 'wsko_cache_keywords');
    }
	
	global $wpdb;
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE " . WSKO_CACHE_TABLE . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  is_limit tinyint(1) NOT NULL,
	  PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta($sql);
	
	$sql = "CREATE TABLE " . WSKO_CACHE_ROWS_TABLE . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  cache_id mediumint(9) NOT NULL,
	  keyval text NOT NULL,
	  clicks mediumint(6) NOT NULL,
	  position double NOT NULL,
	  impressions mediumint(6) NOT NULL,
	  type tinyint(2) NOT NULL,
	  PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta($sql);
}
register_activation_hook(__FILE__, 'wsko_install_plugin');

function wsko_deinstall_plugin()
{
	wp_clear_scheduled_hook('wsko_cache_keywords');
}
register_deactivation_hook(__FILE__, 'wsko_deinstall_plugin');

function wsko_init()
{
	if (is_admin())
	{
		wsko_add_error_post_type();
		
		global $wsko_data, $wp_version;
		
		$res = array();
		if (isset($wsko_data['activate_caching']) && $wsko_data['activate_caching'])
		{
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			
			if (is_plugin_active('all-in-one-event-calendar/all-in-one-event-calendar.php'))
			{
				$data = get_plugin_data(ABSPATH . 'wp-content/plugins/all-in-one-event-calendar/all-in-one-event-calendar.php');
				array_push($res, isset($data['Name']) && $data['Name'] ? $data['Name'] : '-');
			}
			
			if (!empty($res))
				define('WSKO_PLUGIN_INCOMP_CACHE', implode(',', $res));
		}
		if (version_compare(PHP_VERSION, '5.5', '<') || version_compare($wp_version, '4.0', '<'))
		{
			if (isset($wsko_data['token']) && $wsko_data['token'])
			{
				$wsko_data['token'] = false;
				update_option('wsko_init', $wsko_data);
			}
			
			define('WSKO_INCOMP_VERSION', true);
		}
		
		/*if (!defined('DOING_AJAX') || !DOING_AJAX) //Move to admin views
		{
		}
		else if (in_array($_POST['action'], array('wsko_get_tables', 'wsko_update_cache', 'wsko_get_post_metabox', 'wsko_get_keyword')))
		{
			wsko_require_google_lib();
		}*/
	}
}
add_action('init', 'wsko_init');

if (is_admin())
{
	if (!defined('DOING_AJAX') || !DOING_AJAX)
	{
		include_once(plugin_dir_path( __FILE__ ) . '/admin/admin.php');
	}
	
	include_once(plugin_dir_path( __FILE__ ) . '/admin/admin-ajax.php');
}
?>