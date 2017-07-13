<?php
if (!defined('ABSPATH')) exit;

function wsko_lazy_overview()
{
	if (wsko_check_user_permissions())
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-lazy-admin');
		
		wsko_require_google_lib();
		$today = wsko_get_midnight(time());
		
		global $wpdb, $wsko_data;
		$wsko_path = plugin_dir_path( __FILE__ );
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		
		$is_admin = current_user_can('manage_options');
		
		$caching_active = false;
		if (isset($wsko_data['activate_caching']) && $wsko_data['activate_caching'] && !defined('WSKO_PLUGIN_INCOMP_CACHE'))
		{
			$caching_active = true;
		}
		
		if (isset($_REQUEST['start_time']) && isset($_REQUEST['end_time']))
		{
			$time = intval(sanitize_text_field($_REQUEST['start_time']));
			//$time = get_date_from_gmt(date('Y-m-d H:i:s', $time));
			$time2 = intval(sanitize_text_field($_REQUEST['end_time']));
			//$time2 = get_date_from_gmt(date('Y-m-d H:i:s', $time2));
		}
		else
		{
			$time2 = $today - (60 * 60 * 24 * 3);
			$time = $today - (60 * 60 * 24 * 30);
		}
		
		$client = wsko_get_ga_client();
		$has_data = false;
		$has_ref = false;
		$invalid = false;
		$msg = 'API or Token missing';
		if ($client && $token)
		{
			$invalid = wsko_check_google_access($client, $token, false);
			$msg = 'API Access failed';
			if (!$invalid)
			{
				$time_diff = abs($time2 - $time);
				$day_diff = floor($time_diff / (60 * 60 * 24));
				$time_diff = $day_diff * (60 * 60 * 24);
				
				if ($caching_active)
				{
					$data_c = $wpdb->get_var("SELECT COUNT(*) FROM " . WSKO_CACHE_TABLE . " WHERE time BETWEEN '" . date('Y-m-d H:i:s', $time) . "' AND '" . date('Y-m-d H:i:s', $time2) . "'");
					
					if ($data_c >= $day_diff)
					{
						$has_data = true;
						$date_rows = wsko_get_cache_data($time, $time2, 'date', '5000');
						$page_rows = wsko_get_cache_data($time, $time2, 'page', '5000');
						$kw_rows = wsko_get_cache_data($time, $time2, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD);
						$time3 = $time - $time_diff;
						$data_c2 = $wpdb->get_var("SELECT COUNT(*) FROM " . WSKO_CACHE_TABLE . " WHERE time BETWEEN '" . date('Y-m-d H:i:s', $time3) . "' AND '" . date('Y-m-d H:i:s', $time) . "'");
						
						if ($data_c2 >= $day_diff)
						{
							$has_ref = true;
							$kw_rows_ref = wsko_get_cache_data($time3, $time, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD);
							$page_rows_ref = wsko_get_cache_data($time3, $time, 'page', '5000');
							$date_rows_ref = wsko_get_cache_data($time3, $time, 'date', '5000');
						}
					}
				}
				else
				{
					$webmaster = new Google_Service_Webmasters($client);
					for ($timeout = 0; $timeout < 5; $timeout++)
					{
						$date_rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'date', '5000');
						$page_rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'page', '5000');
						$kw_rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000');
						sleep(1); //5 QPS Limit
						if ($date_rows !== -1 && $page_rows !== -1 && $kw_rows !== -1 &&
							$date_rows !== false && $page_rows !== false && $kw_rows !== false)
						{
							$has_data = true;
							break;
						}
					}
					
					if ($has_data)
					{
						$time3 = $time - $time_diff;
						if ($day_diff <= 45)
						{
							for ($timeout = 0; $timeout < 5; $timeout++)
							{
								$kw_rows_ref = wsko_get_ga_query_data($webmaster, $time3, $time, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000');
								$page_rows_ref = wsko_get_ga_query_data($webmaster, $time3, $time, 'page', '5000');
								$date_rows_ref = wsko_get_ga_query_data($webmaster, $time3, $time, 'date', '5000');
								sleep(1); //5 QPS Limit
								if ($date_rows_ref != -1 && $page_rows_ref != -1 && $kw_rows_ref != -1&&
									$date_rows_ref !== false && $page_rows_ref !== false && $kw_rows_ref !== false)
								{
									$has_ref = true;
									break;
								}
							}
						}
					}
				}
				
				ob_start();
				include($wsko_path . '/templates/view-overview.php');
				$c = ob_get_clean();
				ob_start();
				include($wsko_path . '/templates/view-pages.php');
				$c2 = ob_get_clean();
				ob_start();
				include($wsko_path . '/templates/view-keywords.php');
				$c3 = ob_get_clean();
				ob_start();
				if ($has_data)
				{
					?><div id="wsko_lazy_admin_beacon" data-nonce="<?=wp_create_nonce('wsko-get-tables')?>" data-ref="<?=$has_ref ? 'true' : 'false'?>"></div><?php
					$no_data = 0;
					$no_data_ref = 0;
					
					$times = array();
					for($i = $time; $i <= $time2; $i+=(60*60*24))
					{
						$times[date('Y-m-d', $i)] = true;
					}
					if (!empty($date_rows))
					{
						foreach ($date_rows as $row)
						{
							unset($times[$row->keys[0]]);
						}
					}
					$no_data += count($times);
					
					$times_ref = array();
					for($i = $time3; $i <= $time; $i+=(60*60*24))
					{
						$times_ref[date('Y-m-d', $i)] = true;
					}
					if (!empty($date_rows_ref))
					{
						foreach ($date_rows_ref as $row)
						{
							unset($times_ref[$row->keys[0]]);
						}
					}
					$no_data_ref += count($times_ref);
					
					if ($no_data || $no_data_ref)
					{
						?>
						<div class="bs-callout wsko-notice wsko-notice-warning">
							The selected time range has <?=$no_data ? $no_data . ' empty dataset(s)' : ''?><?=$no_data && $no_data_ref ? ' and ' : ' '?><?=$no_data_ref ? $no_data_ref . ' empty dataset(s) in the mirrored timerange (for the progress calculation)' : ''?>. 
							<?php if ($is_admin) { ?>Please activate Error Reporting on the <a href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings page</a> and see if you get any errors over time.<?php }
								else { ?>Please contact an Administrator to check for errors.<?php } ?>
							<?php if ($is_admin) { ?>
								<p class="wsko_callout_note"><strong>Note:</strong> If you just launched your site you may have days without data. If you have recently switched to SSL (HTTPS), the previous data for HTTP is not accessible anymore and will also not show up in the view.</p>
							<?php } ?>
						</div>
						<?php
					}
					
					if (count($kw_rows) == WSKO_KEYWORD_LIMIT_DASHBOARD ||
						($has_ref && count($kw_rows_ref) == WSKO_KEYWORD_LIMIT_DASHBOARD))
					{
						?>
						<div class="bs-callout wsko-notice wsko-notice-warning">
							You've reached the limit of <b><?=WSKO_KEYWORD_LIMIT_DASHBOARD?></b> Keywords. 
							<?php if ($is_admin) { ?>Please go to <a target="_blank" href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings</a> and increase your Dataset Limit (Dashboard).<?php }
								else { ?>Please contact an Administrator to change the Dataset Limit (Dashboard). Note that this may be impossible due to performance reasons or API limitations.<?php } ?>
							<?php /* <br/>
							<p class="wsko_callout_note"><strong>Note:</strong> A higher Dataset Limit will increase the loading time of this page.</p> */ ?>
						</div>
						<?php
					}
					
					if (count($page_rows) == WSKO_KEYWORD_LIMIT_DASHBOARD ||
						($has_ref && count($page_rows_ref) == WSKO_KEYWORD_LIMIT_DASHBOARD))
					{
						?>
						<div class="bs-callout wsko-notice wsko-notice-warning">
							You've reached the limit of <b><?=WSKO_KEYWORD_LIMIT_DASHBOARD?></b> Pages. 
							<?php if ($is_admin) { ?>Please go to <a href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings</a> and increase your Dataset Limit (Dashboard).<?php }
								else { ?>Please contact an Administrator to change the Dataset Limit (Dashboard). Note that this may be impossible due to performance reasons or API limitations.<?php } ?>
							<?php /* <br/>
							<p class="wsko_callout_note"><strong>Note:</strong> A higher Dataset Limit will increase the loading time of this page.</p> */ ?>
						</div>
						<?php
					}
				}
				$c4 = ob_get_clean();
				wp_send_json(array(
						'success' => true,
						'overview' => $c,
						'pages_view' => $c2,
						'keywords_view' => $c3,
						'notices' => $c4
					));
				wp_die();
				return;
			}
		}
	}
	
	wp_send_json(array(
			'success' => false,
			'msg' => $msg
		));
	wp_die();
}
add_action('wp_ajax_wsko_lazy_overview', 'wsko_lazy_overview');

function wsko_feedback()
{
	if (wsko_check_user_permissions())
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-feedback');
		
		$data = $_REQUEST['form_data'];
		$params = array();
		parse_str($data, $params);
		
		$type = isset($params['type']) ? intval($params['type']) : false;
		$msg = isset($params['msg']) ? sanitize_text_field($params['msg']) : false;
		$title = isset($params['title']) ? sanitize_text_field($params['title']) : false;
		$email = isset($params['email']) ? sanitize_email($params['email']) : false;
		$name = isset($params['name']) ? sanitize_text_field($params['name']) : false;
		
		if ($msg && $title)
		{
			$t = $title;
			$title = 'WSKO|';
			
			switch ($type)
			{
				case 0: $title .= 'Feedback - '; break;
				case 1: $title .= 'Error Ticket - '; break;
				case 2: $title .= 'Question - '; break;
			}
			$msg .= '<br/>Von: ' . $name . ' (' . $email .')';
			$title .= $t;
			wp_mail(WSKO_FEEDBACK_MAIL, $title, $msg, array('Content-Type: text/html; charset=UTF-8'));
		}
		
		wp_send_json(array(
				'success' => true
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_feedback', 'wsko_feedback');

function wsko_delete_log_reports()
{
	if (wsko_check_user_permissions(true))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-delete-log-reports');
		
		$msgs = get_posts(array('post_type' => WSKO_POST_TYPE_ERROR, 'post_status' => 'any', 'numberposts' => -1));
		foreach ($msgs as $p)
		{
			wp_delete_post($p->ID, true);
		}
		wp_send_json(array(
				'success' => true,
				'msg' => 'Log cleared'
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_delete_log_reports', 'wsko_delete_log_reports');

function wsko_delete_cache()
{
	if (wsko_check_user_permissions(true))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-delete-cache');
		
		global $wpdb;
		
		$wpdb->query('TRUNCATE TABLE ' . WSKO_CACHE_TABLE);
		$wpdb->query('TRUNCATE TABLE ' . WSKO_CACHE_ROWS_TABLE);
		
		wp_send_json(array(
				'success' => true,
				'msg' => 'Cache deleted'
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_delete_cache', 'wsko_delete_cache');

function wsko_delete_recent_cache()
{
	if (wsko_check_user_permissions(true))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-delete-recent-cache');
		
		global $wpdb;
		$today = strtotime('today');
		$start = $today - (60 * 60 * 24 * 3);
		$end = $today - (60 * 60 * 24 * 90);
		
		$rows = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_TABLE . " WHERE (time BETWEEN '" . date('Y-m-d H:i:s', $end) . "' AND '" . date('Y-m-d H:i:s', $start) . "')");
		foreach ($rows as $row)
		{
			$wpdb->delete(WSKO_CACHE_ROWS_TABLE, array('cache_id' => $row->id));
			$wpdb->delete(WSKO_CACHE_TABLE, array('id' => $row->id));
		}
		
		wp_send_json(array(
				'success' => true,
				'msg' => 'Cache deleted'
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_delete_recent_cache', 'wsko_delete_recent_cache');

function wsko_get_tables()
{
	global $wsko_data;
	$res = false;
	
	if (wsko_check_user_permissions())
	{
		wp_verify_nonce($_GET['nonce'], 'wsko-get-tables');
		
		wsko_require_google_lib();
		
		$caching_active = (isset($wsko_data['activate_caching']) && $wsko_data['activate_caching'] && !defined('WSKO_PLUGIN_INCOMP_CACHE'));
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		
		$wsko_path = plugin_dir_path( __FILE__ );
		$today = wsko_get_midnight(time());
		if (isset($_GET['start_time']) && isset($_GET['end_time']))
		{
			$time = intval(sanitize_text_field($_GET['start_time']));
			$time2 = intval(sanitize_text_field($_GET['end_time']));
		}
		else
		{
			$time2 = $today - (60 * 60 * 24 * 3);
			$time = $today - (60 * 60 * 24 * 30);
		}
		$time_diff = abs($time2 - $time);
		$day_diff = floor($time_diff / (60 * 60 * 24));
		$time_diff = $day_diff * (60 * 60 * 24);
		$time3 = $time - $time_diff;
		
		$p_start = intval(sanitize_text_field($_GET['start']));
		$p_length = intval(sanitize_text_field($_GET['length']));
		$p_end = $p_start + $p_length;
		
		$tab = isset($_GET['table']) ? sanitize_text_field($_GET['table']) : '';
		
		$order = false;
		$orderga = false;
		
		if (isset($_GET['order']))
		{
			switch ($_GET['order'][0]['column'])
			{
				case 0:
					$orderga = 0;
					$order = 'keyval';
					break;
				case 1:
					$orderga = 2;
					$order = 'clicks';
					break;
				case 2:
					$orderga = 4;
					$order = 'position';
					break;
				case 3:
					$orderga = 6;
					$order = 'impressions';
					break;
				case 4:
					$orderga = 8;
					$order = 'ctr';
					break;
			}
		}
		
		if ($order !== false)
		{
			$o_dir = sanitize_text_field($_GET['order'][0]['dir']);
			$order .= ' ' . $o_dir;
			
			if (strtolower($o_dir) == 'asc')
				$orderga++;
		}
		
		$search = (isset($_GET['search']) && $_GET['search']['value']) ? sanitize_text_field($_GET['search']['value']) : false;
		
		switch ($tab)
		{
			case 'keywords':
				if ($caching_active)
				{
					$res2 = true;
					$kw_rows = wsko_get_cache_data($time, $time2, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD, $order, $search);
					if (isset($_GET['ref']) && $_GET['ref'] == 'true')
					{
						$kw_rows_ref = wsko_get_cache_data($time3, $time, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD, $order, $search);
					}
				}
				else if ($token)
				{
					$client = wsko_get_ga_client();
					$client->setAccessToken($token);
					$webmaster = new Google_Service_Webmasters($client);
					$res2 = false;
					for ($timeout = 0; $timeout <= 5; $timeout++)
					{
						$kw_rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000', false, $orderga, $search);
						if ($kw_rows != -1)
						{
							$res2 = true;
							break;
						}
					}
					if ($res2 && isset($_GET['ref']) && $_GET['ref'] == 'true')
					{
						$res2 = false;
						for ($timeout = 0; $timeout <= 5; $timeout++)
						{
							$kw_rows_ref = wsko_get_ga_query_data($webmaster, $time3, $time, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000', false, $orderga, $search);
							if ($kw_rows_ref != -1)
							{
								$res2 = true;
								break;
							}
						}
					}
				}
				if ($res2)
				{
					$data = array();
					$i = 0;
					if (isset($kw_rows) && !empty($kw_rows))
					{
						foreach ($kw_rows as $row)
						{
							if ($i >= $p_start && $i < $p_end)
							{
								$ref_row = false;
								if (isset($kw_rows_ref))
								{
									if ($caching_active)
									{
										if (isset($kw_rows_ref[$row->keys[0]]))
										{
											$ref_row = $kw_rows_ref[$row->keys[0]];
										}
									}
									else
									{
										foreach ($kw_rows_ref as $r_row)
										{
											if ($r_row->keys[0] == $row->keys[0])
											{
												$ref_row = $r_row;
												break;
											}
										}
									}
								}
								if ($ref_row)
								{
									$ref_clicks = $row->clicks != 0 ? round((1 - $ref_row->clicks / $row->clicks) * 100, 2) : 0;
									$ref_position = $ref_row->position - $row->position; //$row->position != 0 ? -round((1 - $ref_row->position / $row->position) * 100, 2) : 0;
									$ref_impressions = $row->impressions != 0 ? round((1 - $ref_row->impressions / $row->impressions) * 100, 2) : 0;
									$ref_ctr = $row->ctr != 0 ? round((1 - $ref_row->ctr / $row->ctr) * 100, 2) : 0;
								}
								$kw = $row->keys[0];
								array_push($data, array(
									'<strong>' . $kw . '<strong></br>',
									'<span style="min-width:30px; float:left;">' . $row->clicks . '</span><a href="#" data-toggle="tooltip" title="' . ($ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"><span class="wsko_single_progress ' . ($ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_clicks > 0)) ? '+' : '') . ($ref_row ? $ref_clicks : '-') . ' %</span></a>',
									'<span style="min-width:30px; float:left;">' . round($row->position, 2) . '</span><a href="#" data-toggle="tooltip" title="Position ' . ($ref_row ? round($ref_row->position, 2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"><span class="wsko_single_progress ' . ($ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_position > 0)) ? '+' : '') . ($ref_row ? $ref_position : '-') . '</span></a>',
									'<span style="min-width:30px; float:left;">' . $row->impressions . '</span><a href="#" data-toggle="tooltip" title="' . ($ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"><span class="wsko_single_progress ' . ($ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_impressions > 0)) ? '+' : '') . ($ref_row ? $ref_impressions : '-') . ' %</span></a>',
									'<span style="min-width:30px; float:left;">' . round($row->ctr, 2) . ' %</span><a href="#" data-toggle="tooltip" title="CTR ' . ($ref_row ? round($ref_row->ctr, 2) . ' % from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"><span class="wsko_single_progress ' . ($ref_row && $ref_ctr != 0 ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_ctr > 0)) ? '+' : '') . ($ref_row ? $ref_ctr : '-') . ' %</span></a>',
								));
							}
							$i++;
						}
						$count = count($kw_rows);
						wp_send_json(array(
							'draw' => isset($_GET['draw']) ? $_GET['draw'] : 0,
							'recordsTotal' => $count,
							'recordsFiltered' => $count,
							'data' => $data,
						));
						$res = true;
					}
				}
				break;
				
			case 'pages':
				if ($caching_active)
				{
					$res2 = true;
					$page_rows = wsko_get_cache_data($time, $time2, 'page', WSKO_KEYWORD_LIMIT_DASHBOARD, $order, $search);
					if (isset($_GET['ref']) && $_GET['ref'] == 'true')
					{
						$page_rows_ref = wsko_get_cache_data($time3, $time, 'page', WSKO_KEYWORD_LIMIT_DASHBOARD, $order, $search);
					}
				}
				else if ($token)
				{
					$client = wsko_get_ga_client();
					$client->setAccessToken($token);
					$webmaster = new Google_Service_Webmasters($client);
					$res2 = false;
					for ($timeout = 0; $timeout <= 5; $timeout++)
					{
						$page_rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'page', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000', false, $orderga, $search);
						if ($page_rows != -1)
						{
							$res2 = true;
							break;
						}
					}
					if ($res2 && isset($_GET['ref']) && $_GET['ref'] == 'true')
					{
						$res2 = false;
						for ($timeout = 0; $timeout <= 5; $timeout++)
						{
							$page_rows_ref = wsko_get_ga_query_data($webmaster, $time3, $time, 'page', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000', false, $orderga, $search);
							if ($page_rows_ref != -1)
							{
								$res2 = true;
								break;
							}
						}
					}
				}
				if ($res2)
				{
					$data = array();
					$i = 0;
					if (isset($page_rows) && !empty($page_rows))
					{
						foreach ($page_rows as $row)
						{
							if ($i >= $p_start && $i < $p_end)
							{
								$ref_row = false;
								if (isset($page_rows_ref))
								{
									if ($caching_active)
									{
										if (isset($page_rows_ref[$row->keys[0]]))
										{
											$ref_row = $page_rows_ref[$row->keys[0]];
										}
									}
									else
									{
										foreach ($page_rows_ref as $r_row)
										{
											if ($r_row->keys[0] == $row->keys[0])
											{
												$ref_row = $r_row;
												break;
											}
										}
									}
								}
								if ($ref_row)
								{
									$ref_clicks = $row->clicks != 0 ? round((1 - $ref_row->clicks / $row->clicks) * 100, 2) : 0;
									$ref_position = $ref_row->position - $row->position; //$row->position != 0 ? -round((1 - $ref_row->position / $row->position) * 100, 2) : 0;
									$ref_impressions = $row->impressions != 0 ? round((1 - $ref_row->impressions / $row->impressions) * 100, 2) : 0;
									$ref_ctr = $row->ctr != 0 ? round((1 - $ref_row->ctr / $row->ctr) * 100, 2) : 0;
								}
								$url = $row->keys[0];
								array_push($data, array(
									'<div class="wsko-unloaded" data-id="' . $i . '" data-url="' . $url . '" style="display:none;"></div><div class="wsko_nowrap" title="' . $url . '"><span class="wsko-post-title"><i class="fa fa-spinner fa-spin"></i> Identifying...</span></br><span class="font-unimportant">' . $url . '</span></div>',
									'<span style="min-width:30px; float:left;">' . $row->clicks . '</span> <a href="#" data-toggle="tooltip" title="' . ($ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"> <span class="wsko_single_progress ' . ($ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_clicks > 0)) ? '+' : '' ) . ($ref_row ? $ref_clicks : '-') . ' %</span></a>',
									'<span style="min-width:30px; float:left;">' . round($row->position, 2) . '</span> <a href="#" data-toggle="tooltip" title="' . ($ref_row ? round($ref_row->position, 2) . ' Position from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"> <span class="wsko_single_progress ' . ($ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_position > 0)) ? '+' : '' ) . ($ref_row ? $ref_position : '-') . '</span></a>',
									'<span style="min-width:30px; float:left;">' . $row->impressions . '</span> <a href="#" data-toggle="tooltip" title="' . ($ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"> <span class="wsko_single_progress ' . ($ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_impressions > 0)) ? '+' : '' ) . ($ref_row ? $ref_impressions : '-') . ' %</span></a>',
									'<span style="min-width:30px; float:left;">' . round($row->ctr, 2) . ' %</span> <a href="#" data-toggle="tooltip" title="CTR ' . ($ref_row ? round($ref_row->ctr, 2) . ' % from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : '') . '"> <span class="wsko_single_progress ' . ($ref_row && $ref_ctr != 0 ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font') . '">' . (($ref_row && ($ref_ctr > 0)) ? '+' : '' ) . ($ref_row ? $ref_ctr : '-') . ' %</span></a>',
									'<a class="wsko-show-keywords unloaded wsko_details_button" href="#" data-url="' . $url . '" data-nonce="' . wp_create_nonce('wsko-show-keywords') . '"><i class="fa fa-eye" title="Show Keywords" data-toggle="tooltip"></i></a>
										<a class="wsko_details_button" target="_blank" href="' . $url . '"><i class="fa fa-link" title="View Page" data-toggle="tooltip"></i></a>
										<a class="wsko-post-button wsko_details_button" target="_blank" href="#" style="display:none"><i class="fa fa-pencil" title="Edit Post" data-toggle="tooltip"></i></a>
										<div class="wsko-kd-cache" style="display:none;"></div>',
								));
							}
							$i++;
						}
						$count = count($page_rows);
						wp_send_json(array(
							'draw' => isset($_GET['draw']) ? $_GET['draw'] : 0,
							'recordsTotal' => $count,
							'recordsFiltered' => $count,
							'data' => $data,
						));
						$res = true;
					}
				}
				break;
		}
	}
	
	if (!$res)
	{
		wp_send_json(array(
			'draw' => isset($_GET['draw']) ? $_GET['draw'] : 0,
			'recordsTotal' => 0,
			'recordsFiltered' => 0,
			'data' => array(
			),
		));
	}
		
	wp_die();
}
add_action('wp_ajax_wsko_get_tables', 'wsko_get_tables');

function wsko_update_cache()
{
	if (wsko_check_user_permissions(true))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-update-cache');
		
		wsko_cache_keywords(true);
		
		wp_send_json(array(
				'success' => true
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_update_cache', 'wsko_update_cache');

function wsko_save_settings()
{
	if (wsko_check_user_permissions(true))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-save-settings');
		
		global $wsko_data;
		
		$data = $_REQUEST['form_data'];
		$params = array();
		parse_str($data, $params);

		if (isset($params['clean_uninstall']))
		{
			$wsko_data['clean_uninstall'] = true;
		}
		else
		{
			unset($wsko_data['clean_uninstall']);
		}
		
		if (isset($params['activate_post_widget']))
		{
			$wsko_data['activate_post_widget'] = true;
		}
		else
		{
			unset($wsko_data['activate_post_widget']);
		}
		
		if (isset($params['add_bootstrap']))
		{
			$wsko_data['add_bootstrap'] = true;
		}
		else
		{
			unset($wsko_data['add_bootstrap']);
		}
		
		if (isset($params['add_fontawesome']))
		{
			$wsko_data['add_fontawesome'] = true;
		}
		else
		{
			unset($wsko_data['add_fontawesome']);
		}
		
		if (isset($params['add_moment']))
		{
			$wsko_data['add_moment'] = true;
		}
		else
		{
			unset($wsko_data['add_moment']);
		}
		
		if (isset($params['add_bootstrap_datepicker']))
		{
			$wsko_data['add_bootstrap_datepicker'] = true;
		}
		else
		{
			unset($wsko_data['add_bootstrap_datepicker']);
		}
		
		if (isset($params['add_datatables']))
		{
			$wsko_data['add_datatables'] = true;
		}
		else
		{
			unset($wsko_data['add_datatables']);
		}
		
		if (isset($params['add_google_chart']))
		{
			$wsko_data['add_google_chart'] = true;
		}
		else
		{
			unset($wsko_data['add_google_chart']);
		}
		
		if (isset($params['add_icheck']))
		{
			$wsko_data['add_icheck'] = true;
		}
		else
		{
			unset($wsko_data['add_icheck']);
		}
		
		if (isset($params['activate_caching']))
		{
			$wsko_data['activate_caching'] = true;
		}
		else
		{
			unset($wsko_data['activate_caching']);
		}
		
		if (isset($params['cache_time_limit']))
		{
			$cache_time_limit = intval(sanitize_text_field($params['cache_time_limit']));
			if (is_numeric($cache_time_limit) && $cache_time_limit > 0)
			{
				$wsko_data['cache_time_limit'] = $cache_time_limit;
			}
			else
			{
				unset($wsko_data['cache_time_limit']);
			}
		}
		else
		{
			unset($wsko_data['cache_time_limit']);
		}
		
		if (isset($params['post_time']))
		{
			$post_time = intval(sanitize_text_field($params['post_time']));
			if (is_numeric($post_time) && $post_time > 0)
			{
				$wsko_data['post_time'] = $post_time;
			}
			else
			{
				unset($wsko_data['post_time']);
			}
		}
		else
		{
			unset($wsko_data['post_time']);
		}
		
		if (isset($params['keyword_limit_main']))
		{
			$keyword_limit_main = intval(sanitize_text_field($params['keyword_limit_main']));
			if (is_numeric($keyword_limit_main) && $keyword_limit_main > 0 && $keyword_limit_main <= 5000)
			{
				$wsko_data['keyword_limit_main'] = $keyword_limit_main;
			}
			else
			{
				unset($wsko_data['keyword_limit_main']);
			}
		}
		else
		{
			unset($wsko_data['keyword_limit_main']);
		}
		
		if (isset($params['keyword_limit_dashboard']))
		{
			$keyword_limit_dashboard = intval(sanitize_text_field($params['keyword_limit_dashboard']));
			if (is_numeric($keyword_limit_dashboard) && $keyword_limit_dashboard > 0)
			{
				$wsko_data['keyword_limit_dashboard'] = $keyword_limit_dashboard;
			}
			else
			{
				unset($wsko_data['keyword_limit_dashboard']);
			}
		}
		else
		{
			unset($wsko_data['keyword_limit_dashboard']);
		}
		
		if (isset($params['keyword_limit_post']))
		{
			$keyword_limit_post = intval(sanitize_text_field($params['keyword_limit_post']));
			if (is_numeric($keyword_limit_post) && $keyword_limit_post > 0 && $keyword_limit_post <= 5000)
			{
				$wsko_data['keyword_limit_post'] = $keyword_limit_post;
			}
			else
			{
				unset($wsko_data['keyword_limit_post']);
			}
		}
		else
		{
			unset($wsko_data['keyword_limit_post']);
		}
		
		if (isset($params['activate_log']))
		{
			$wsko_data['activate_log'] = true;
		}
		else
		{
			$wsko_data['activate_log'] = false;
		}
		
		wsko_remove_caps();
		
		if (isset($params['permission_roles']) && is_array($params['permission_roles']))
		{
			$roles = $params['permission_roles'];
			foreach($roles as $k => $role)
			{
				$role = sanitize_text_field($role);
				if ($role)
				{
					$role_o = get_role($role);
					if ($role_o)
					{
						$role_o->add_cap(WSKO_VIEW_CAP);
						$roles[$k] = $role;
					}
					else
					{
						unset($roles[$k]);
					}
				}
				else
				{
					unset($roles[$k]);
				}
			}
			if (!empty($roles))
			{
				$wsko_data['permission_roles'] = $roles;
			}
			else
			{
				unset($wsko_data['permission_roles']);
			}
		}
		else
		{
			unset($wsko_data['permission_roles']);
		}
		
		update_option('wsko_init', $wsko_data);
		
		wp_send_json(array(
				'success' => true,
				'msg' => 'Saved'
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	
	wp_die();
}
add_action('wp_ajax_wsko_save_settings', 'wsko_save_settings');

function wsko_get_pages()
{
	$data = $_POST['pages'];
	if (wsko_check_user_permissions())
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-get-pages');
		foreach ($data as $key => $page)
		{
			$data[$key]['res'] = $res = wsko_url_get_title(esc_url($page['url']));
			if ($res->type == 'post')
				$data[$key]['res']->link = get_edit_post_link($res->post_id);
		}
		wp_send_json(array(
				'success' => true,
				'result' => $data,
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_get_pages', 'wsko_get_pages');

function wsko_get_post_metabox()
{	
	global $wsko_data;
	$post = sanitize_text_field($_POST['post']);
	if (wsko_check_user_permissions() && $post && is_numeric($post))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-get-post-box-' . $post);
		
		wsko_require_google_lib();
		
		$post = get_post($post);
		$wsko_path = plugin_dir_path( __FILE__ );
		
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		
		try
		{
			$client = wsko_get_ga_client();
			$client->setAccessToken($token);
			$webmaster = new Google_Service_Webmasters($client);
			
			$time2 = strtotime('today') - (60 * 60 * 24 * 3);
			$time = $time2 - (60 * 60 * 24 * intval(WSKO_DEFAULT_INTERVAL_POST));
			$rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'query', WSKO_KEYWORD_LIMIT_POST, get_permalink($post->ID));
			$data = wsko_get_ga_query_data($webmaster, $time, $time2, 'date', WSKO_KEYWORD_LIMIT_POST, get_permalink($post->ID));
			$sum_clicks = 0;
			$sum_imp = 0;
			$sum_clicks_kw = 0;
			$sum_imp_kw = 0;
			if ($rows && !empty($rows))
			{
				foreach ($rows as $r)
				{
					$sum_clicks_kw += $r->clicks;
					$sum_imp_kw += $r->impressions;
				}
			}
			if ($data && !empty($data))
			{
				foreach ($data as $d)
				{
					$sum_clicks += $d->clicks;
					$sum_imp += $d->impressions;
				}
			}
			
			ob_start();
			include($wsko_path. '/templates/metabox-post.php');
			$c = ob_get_clean();
			
			ob_start();
			include($wsko_path. '/templates/modal-track-keywords.php');
			$c2 = ob_get_clean();
		}
		catch (Exception $e)
		{
			wsko_report_error('exception', 'AJAX Error - Post Wiget', $e, 'This exception occured while fetching the post widget.');
			//var_dump($e);
		}
		
		wp_send_json(array(
				'success' => true,
				'result' => $c,
				'result_f' => $c2
			));
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	wp_die();
}
add_action('wp_ajax_wsko_get_post_metabox', 'wsko_get_post_metabox');

function wsko_get_keyword_suggests()
{
	$loc = get_locale();
	if (!$loc)
		$loc = 'de';
	else
		$loc = reset(explode('_', $loc));
	$keyword = utf8_encode(urlencode(trim(sanitize_text_field($_POST['keyword']))));
	ob_start();
	$file = utf8_encode(file_get_contents('http://google.de/complete/search?output=toolbar&hl=' . $loc . '&q='.$keyword));
	$xml = simplexml_load_string($file); 
	foreach($xml->CompleteSuggestion as $value)
	{
		echo '<option value="'.$value->suggestion['data'].'" />';
	}
	$c = ob_get_clean();

	wp_send_json(array(
			'success' => true,
			'result' => $c
		));
	wp_die();
}
add_action('wp_ajax_wsko_get_keyword_suggests', 'wsko_get_keyword_suggests');

function wsko_get_keyword()
{
	if (wsko_check_user_permissions() && isset($_POST['nonce']))
	{
		wp_verify_nonce($_POST['nonce'], 'wsko-show-keywords');
		
		wsko_require_google_lib();
		
		global $wsko_data;
		
		if (isset($_POST['start_time']) && isset($_POST['end_time']))
		{
			$time = intval(sanitize_text_field($_POST['start_time']));
			$time2 = intval(sanitize_text_field($_POST['end_time']));
		}
		else
		{
			$time2 = strtotime('today') - (60 * 60 * 24 * 3);
			$time = $time2 - (60 * 60 * 24 * 27);
		}
		
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		$url = esc_url($_POST['url']);
		if ($token && $url)
		{
			ob_start();
			try
			{
				//$time2 = strtotime('today') - (60 * 60 * 24 * 3);
				//$time = $time2 - (60 * 60 * 24 * intval(WSKO_DEFAULT_INTERVAL_POST));
				
				$client = wsko_get_ga_client();
				$client->setAccessToken($token);
				$webmaster = new Google_Service_Webmasters($client);
				
				$rows = wsko_get_ga_query_data($webmaster, $time, $time2, 'query', WSKO_KEYWORD_LIMIT_DASHBOARD <= 5000 ? WSKO_KEYWORD_LIMIT_DASHBOARD : '5000', $url);
				include(dirname(__FILE__) . '/templates/ajax-keywords.php');
			}
			catch (Exception $e)
			{
				wsko_report_error('exception', 'AJAX Error - Keywords for URL', $e, 'This exception occured while fetching the keywords for the url "' . $url . '"');
			}
			
			$c = ob_get_clean();
			wp_send_json(array(
					'success' => true,
					'result' => $c
				));
		}
		else
		{
			wp_send_json(array(
					'success' => false
				));
		}
	}
	else
	{
		wp_send_json(array(
				'success' => false
			));
	}
	
	wp_die();
}
add_action('wp_ajax_wsko_get_keyword', 'wsko_get_keyword');

function wsko_track_keywords()
{
	$data = $_POST['form_data'];
	$params = array();
	parse_str($data, $params);
	
	if (wsko_check_user_permissions() && isset($params['post']) && $params['post'] && isset($params['_wpnonce']))
	{
		wp_verify_nonce($params['_wpnonce'], 'wsko-track-keywords-' . $params['post']);
	
		$keywords = array();
		
		if (isset($params['keywords']))
		{
			foreach ($params['keywords'] as $key => $word)
			{
				$key = sanitize_text_field($key);
				if (isset($word['value']))
				{
					$keywords[$key] = array('focus' => isset($word['focus']) && $word['focus'] ? true : false);
					if (isset($word['group']) && $word['group'] != 0 && is_numeric($word['group']))
						$keywords[$key]['group'] = $word['group'];
					
					if (isset($word['group_main']))
						$keywords[$key]['group_main'] = true;
				}
			}
		}
		
		if (isset($params['keywords_new']))
		{
			foreach ($params['keywords_new'] as $key => $word)
			{
				$key = sanitize_text_field($key);
				if (isset($word['value']))
				{
					$keywords[$key] = array('focus' => isset($word['focus']) && $word['focus'] ? true : false);
					if (isset($word['group']) && $word['group'] != 0 && is_numeric($word['group']))
						$keywords[$key]['group'] = $word['group'];
					
					if (isset($word['group_main']))
						$keywords[$key]['group_main'] = true;
				}
			}
		}
		
		update_post_meta($params['post'], 'wsko_keywords', $keywords);	
	}
	
	wp_send_json(array(
			'success' => true
		));
	wp_die();
}
add_action('wp_ajax_wsko_track_keywords', 'wsko_track_keywords');
?>