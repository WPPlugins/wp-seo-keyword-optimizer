<?php
function wsko_add_error_post_type()
{
	register_post_type(WSKO_POST_TYPE_ERROR,
		array(
		'labels' => array( //just for completeness
			'name' => 'WSKO Log Report',
			'singular_name' => 'WSKO Log Report',
			'add_new' => 'Add a New WSKO Log Report',
			'add_new_item' => 'Add a New WSKO Log Report',
			'edit_item' => 'Edit WSKO Log Report',
			'new_item' => 'New WSKO Log Report',
			'view_item' => 'View WSKO Log Report',
			'search_items' => 'Search WSKO Log Reports',
			'not_found' => 'Nothing Found',
			'not_found_in_trash' => 'Nothing found in Trash',
			'parent_item_colon' => ''
		),
		'description' => 'WSKO Log Reports',
		'public' => false,
		'exclude_from_search' => false,
		'publicly_queryable' => false,
		'show_ui' => false,
		'show_in_nav_menus' => false,
		'show_in_menu' => false,
		'show_in_admin_bar' => false,
		'has_archive' => false,
		'rewrite' => false, //array('slug' => 'p'),
		
		/*'query_var'          => true,
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'author')*/
	));
}

function wsko_check_user_permissions()
{
	global $wsko_data;
	
	$user = wp_get_current_user();
	if ($user && $user->ID)
	{
		if (current_user_can('manage_options'))
		{
			return true;
		}
		foreach($user->roles as $role)
		{
			if (isset($wsko_data['permission_roles']) && in_array($role, $wsko_data['permission_roles']))
			{
				return true;
			}
		}
	}
	
	return false;
}

function wsko_remove_caps()
{
	global $wp_roles;
	$roles = $wp_roles->get_names(); 
	foreach ($roles as $k => $role)
	{
		$r = get_role($k);
		if ($r)
			$r->remove_cap(WSKO_VIEW_CAP);
	}
}

function wsko_report_error($type, $title, $var, $additional = false)
{
	global $wsko_data;
	
	switch ($type)
	{
		case 'exception':
			$type = 'error';
			$c = 'An exception occurred:<br/><br/><pre>' . $var->getMessage() . '</pre>';
			if ($additional)
				$c .= $additional;
			break;
			
		case 'warning':
		case 'info':
		case 'error':
			$c = $var;
			if ($additional)
				$c .= '<br/><br/>' . $additional;
			break;
			
		default:
			return;
	}
	
	
	if ($wsko_data['activate_log'])
	{
		$args = array(
			'post_type' => WSKO_POST_TYPE_ERROR,
			'post_status' => $type,
			'post_title' => $title,
			'post_content' => $c,
			'post_author' => 0
		);
		wp_insert_post($args);
	}
}

function wsko_get_midnight($time)
{
	if (is_numeric($time))
		$time = date('Y-m-d H:i:s', $time);
	$date = new DateTime($time);
	$date->setTime(0,0,0);
	return $date->getTimestamp();
}

function wsko_require_google_lib($setfail = false)
{
	if (!class_exists('Google_Client'))
	{
		require_once(plugin_dir_path( __FILE__ ) . '/includes/google-api-php-client-2.1.0/vendor/autoload.php');
		require_once(plugin_dir_path( __FILE__ ) . '/token.php'); //Check token
	}
	else
	{
		if ($setfail)
		{
			define('WSKO_GOOGLE_INCLUDE_FAILED', true);
		}
	}
}

function wsko_get_effective_url($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);
	$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
	curl_close($ch);
	return $effective_url;
}

function wsko_cache_keywords($force = false)
{
	set_time_limit(300);
	wsko_require_google_lib();
	require_once(plugin_dir_path( __FILE__ ) . '/token.php'); //Check token expired first
	//return;
	global $wpdb, $wsko_data;
	
	if (!$force && (!isset($wsko_data['activate_caching']) || !$wsko_data['activate_caching']))
		return;
	
	$token = isset($wsko_data['token']) ? $wsko_data['token'] : false;
	if ($token)
	{
		$client = wsko_get_ga_client();
		if ($client)
		{
			try
			{
				$client->setAccessToken($token);
				$token = $client->getAccessToken();
				
				$webmaster =  new Google_Service_Webmasters($client);
				$today = wsko_get_midnight(time());
				$q = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
				$q->setStartDate(date("Y-m-d", $today - (60 * 60 * 24)));
				$q->setEndDate(date("Y-m-d", $today));
				$q->setDimensions(array('query'));
				$q->setRowLimit('1');
				$q->setSearchType('web');
				
				$data = $webmaster->searchanalytics->query(wsko_get_host_base(), $q);
				$rows = $data->getRows();
				
				$start = wsko_get_midnight(time()) - (60 * 60 * 24 * 3);
				$timeout = 5;
				for ($i = 0; $i < 88; $i++)
				{
					$curr = $start - (60 * 60 * 24 * $i);
					$curr_d = date('Y-m-d 00:00:00', $curr);
					$curr_d2 = date('Y-m-d 23:59:59', $curr);
					$temp = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_TABLE . " WHERE time BETWEEN '" . $curr_d . "' AND '" . $curr_d2 . "'");
					$is_limit = false;
					
					$is_corrupted = false;
					if ($wpdb->num_rows == 1 && $temp)
					{
						$temp_query = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_ROWS_TABLE . " WHERE cache_id=" . $temp[0]->id . ' AND type="0"');
						$temp_page = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_ROWS_TABLE . " WHERE cache_id=" . $temp[0]->id . ' AND type="1"');
						$temp_date = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_ROWS_TABLE . " WHERE cache_id=" . $temp[0]->id . ' AND type="2"');
						
						$is_limit = ($temp[0]->is_limit && max(array(count($temp_query), count($temp_page))) < WSKO_KEYWORD_LIMIT);
						if(!$temp_date || empty($temp_date))
						{
							wsko_report_error('warning', 'Corrupted Data Set', 'A corrupted data set for ' . $curr_d . ' was found and will be deleted.');
							$is_corrupted = true;
						}
					}
					
					if ($is_corrupted || $wpdb->num_rows != 1 || $is_limit)
					{
						if ($is_corrupted || $wpdb->num_rows > 1 || $is_limit) //Clean and refetch these rows
						{
							foreach ($temp as $t)
							{
								$wpdb->delete(WSKO_CACHE_ROWS_TABLE, array('cache_id' => $t->id));
								$wpdb->delete(WSKO_CACHE_TABLE, array('id' => $t->id));
							}
						}
						
						//$curr2 = $curr - (60 * 60 * 24);
						$date_rows = wsko_get_ga_query_data($webmaster, $curr, $curr, 'date', WSKO_KEYWORD_LIMIT, false);
						$page_rows = wsko_get_ga_query_data($webmaster, $curr, $curr, 'page', WSKO_KEYWORD_LIMIT, false);
						$kw_rows = wsko_get_ga_query_data($webmaster, $curr, $curr, 'query', WSKO_KEYWORD_LIMIT, false);
						
						if ($date_rows === -1 || $page_rows === -1 || $kw_rows === -1 ||
							$date_rows === false || $page_rows === false || $kw_rows === false /*||
							empty($date_rows) || empty($page_rows) || empty($kw_rows)*/) //A Query failed
						{
							ob_start();
							?>
							Keyword Rows: <?=(is_array($kw_rows) ? (empty($kw_rows) ? 'Set (Empty)' : 'Set') : $kw_rows)?><br/>
							Page Rows: <?=(is_array($page_rows) ? (empty($page_rows) ? 'Set (Empty)' : 'Set') : $page_rows)?><br/>
							Date Rows: <?=(is_array($date_rows) ? (empty($data_rows) ? 'Set (Empty)' : 'Set') : $date_rows)?><br/>
							<?php
							$add = ob_get_clean();
							wsko_report_error('error', 'Query Error', 'The query for ' . $curr_d . ' failed during the cache update.', $add);
							
							$timeout--;
							
							if ($timeout == 0)
							{
								wsko_report_error('error', 'Timeout Error', 'A cache update has used all 5 timeouts and can fail for the last ' . $i . ' rows.');
							}
							else if ($timeout > 0)
								$i--;
							
							continue;
						}
						
						$wpdb->insert(WSKO_CACHE_TABLE, 
							array(
								'time' => $curr_d,
								//'query' => json_encode($kw_rows ? $kw_rows : array()),
								//'page' => json_encode($page_rows ? $page_rows : array()),
								//'date' => json_encode($date_rows ? $date_rows : array()),
								'is_limit' => (count($date_rows) == WSKO_KEYWORD_LIMIT ||
												count($page_rows) == WSKO_KEYWORD_LIMIT ||
												count($kw_rows) == WSKO_KEYWORD_LIMIT) ? 1 : 0
							)
						);
						$id = $wpdb->insert_id;
						
						if ($id)
						{
							foreach ($kw_rows as $r)
							{
								$wpdb->insert(WSKO_CACHE_ROWS_TABLE, 
									array(
										'type' => 0,
										'cache_id' => $id,
										'keyval' => $r->keys[0],
										'clicks' => $r->clicks,
										'impressions' => $r->impressions,
										'position' => $r->position,
									)
								);
							}
							foreach ($page_rows as $r)
							{
								$wpdb->insert(WSKO_CACHE_ROWS_TABLE, 
									array(
										'type' => 1,
										'cache_id' => $id,
										'keyval' => $r->keys[0],
										'clicks' => $r->clicks,
										'impressions' => $r->impressions,
										'position' => $r->position,
									)
								);
							}
							foreach ($date_rows as $r)
							{
								$wpdb->insert(WSKO_CACHE_ROWS_TABLE, 
									array(
										'type' => 2,
										'cache_id' => $id,
										'keyval' => $r->keys[0],
										'clicks' => $r->clicks,
										'impressions' => $r->impressions,
										'position' => $r->position,
									)
								);
							}
						}
					}
					
					sleep(1); //Sleep to not hit 5 QPS Limit
				}
				
				if (WSKO_CACHE_TIME_LIMIT)
				{
					$start2 = strtotime('today') - (60 * 60 * 24 * WSKO_CACHE_TIME_LIMIT);
					$rows = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_TABLE . " WHERE time<'" . date('Y-m-d H:i:s', $start2) . "'");
					foreach ($rows as $row)
					{
						$wpdb->delete(WSKO_CACHE_ROWS_TABLE, array('cache_id' => $row->id));
						$wpdb->delete(WSKO_CACHE_TABLE, array('id' => $row->id));
					}
				}
			}
			catch (Exception $error)
			{
				wsko_report_error('exception', 'Cache Error', $error);
			}
		}
	}
}

function wsko_get_ga_client($clear = false)
{
	try
	{
		$client = new Google_Client();
		if ($clear == false)
		{
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			$client->setApplicationName('WP SEO Analytics');
			$client->setAuthConfig(__DIR__ . '/includes/cred.json');
			$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob'); //'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');
		}
		
		$client->addScope("https://www.googleapis.com/auth/webmasters.readonly"); //Google_Service_Analytics::ANALYTICS_READONLY);
		return $client;
	}
	catch (Exception $error)
	{
		return false;
	}
}

function wsko_check_google_access($client, $token, $post)
{
	$wsko_path = plugin_dir_path( __FILE__ );
	
	$res = false;
	try
	{
		$client->setAccessToken($token);
		$token = $client->getAccessToken();
		
		$webmaster = new Google_Service_Webmasters($client);
		
		$q = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
		$q->setStartDate(date("Y-m-d", time() - (60 * 60 * 24)));
		$q->setEndDate(date("Y-m-d", time()));
		$q->setDimensions(array('query'));
		$q->setRowLimit('1');
		$q->setSearchType('web');
		
		$data = $webmaster->searchanalytics->query(wsko_get_host_base(), $q);
		$rows = $data->getRows();
		//return $rows;
	}
	catch (Exception $error)
	{
		ob_start();
		//var_dump($e);
		if ($post)
		{
			include($wsko_path. '/admin/templates/view-gafail-post.php');
		}
		else
		{
			include($wsko_path. '/admin/templates/view-gafail-main.php');
		}
		$res = ob_get_clean();
	}
	
	return $res;
}

function wsko_check_keyword($post_id, $keyword, $focused, $content, $content_plain, $meta_title, $meta_desc)
{
	if (!is_array($keyword))
	{
		$keyword = array($keyword);
	}
	
	usort($keyword, function($a, $b)
	{
		$a = count(explode(' ', $a));
		$b = count(explode(' ', $b));
		if ($a == $b)
			return 0;
		
		return ($a < $b) ? 1 : -1;
	});
	
	foreach ($keyword as $key => $kw)
	{
		$keyword[$key] = strtolower($kw);
	}

	$content_plain = strtolower($content_plain);
	$meta_title = strtolower($meta_title);
	$meta_desc = strtolower($meta_desc);
	$full = 100;
	$res = new stdClass;
	$res->efficiency = 0;
	$res->density = 0;
	$res->notes = array();
	
	//$content = strip_tags(do_shortcode(get_post_field('post_content', $post_id)));
	
	$word_count = str_word_count($content_plain, 0, '1234567890');
	$con_temp = $content_plain;
	$keyword_count = 0;
	foreach ($keyword as $kw)
	{
		$keyword_count += substr_count($con_temp, $kw);
		$con_temp = str_replace($kw, '', $con_temp);
	}
	
	$res->keyword_count = $keyword_count;
	
	if ($word_count > 0)
		$res->density = $keyword_count / $word_count * 100;
	libxml_use_internal_errors(true);
	$dom = new DOMDocument;
	$dom->recover = true;
	$dom->loadXML('<div>' . $content . '</div>');
	
	$inTitle = false;
	foreach ($keyword as $kw)
	{
		if (strpos($meta_title, $kw) !== false)
		{
			$inTitle = true;
			break;
		}
	}
	$startsTitle = false;
	foreach ($keyword as $kw)
	{
		if (substr($meta_title, 0, strlen($kw)) === $kw)
		{
			$startsTitle = true;
			break;
		}
	}
	
	$inDescritpion = false;
	foreach ($keyword as $kw)
	{
		if (strpos($meta_desc, $kw) !== false)
		{
			$inDescritpion = true;
			break;
		}
	}
	
	$url = get_permalink($post_id);
	
	$inUrl = true;
	foreach ($keyword as $kw)
	{
		$kw_p = explode(" ", $kw);
		foreach ($kw_p as $kw2)
		{
			if (strpos($url, $kw2) === false)
			{
				$inUrl = false;
				break;
			}
		}
		if (!$inUrl)
			break;
	}
	
	$inH1 = false;
	$inH2 = false;
	$inH3 = false;
	$inH4 = false;
	$inH5 = false;
	$inH6 = false;
	$inBold = false;
	$inItalic = false;
	$h1s = $dom->getElementsByTagName('h1');
	foreach ($h1s as $h1)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h1->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH1 = true;
			break;
		}
	}
	$h2s = $dom->getElementsByTagName('h2');
	foreach ($h2s as $h2)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h2->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH2 = true;
			break;
		}
	}
	$h3s = $dom->getElementsByTagName('h3');
	foreach ($h3s as $h3)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h3->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH3 = true;
			break;
		}
	}
	$h4s = $dom->getElementsByTagName('h4');
	foreach ($h4s as $h4)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h4->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH4 = true;
			break;
		}
	}
	$h5s = $dom->getElementsByTagName('h5');
	foreach ($h5s as $h5)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h5->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH5 = true;
			break;
		}
	}
	$h6s = $dom->getElementsByTagName('h6');
	foreach ($h6s as $h6)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($h6->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inH6 = true;
			break;
		}
	}
	$bolds = $dom->getElementsByTagName('b');
	foreach ($bolds as $b)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($b->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inBold = true;
			break;
		}
	}
	$strongs = $dom->getElementsByTagName('strong');
	foreach ($strongs as $strong)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($strong->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inBold = true;
			break;
		}
	}
	$is = $dom->getElementsByTagName('i');
	foreach ($is as $i)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($i->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inItalic = true;
			break;
		}
	}
	$ems = $dom->getElementsByTagName('em');
	foreach ($ems as $em)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($em->nodeValue), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inItalic = true;
			break;
		}
	}
	$xpath = new DOMXPath($dom);
	$inAlt = false;
	$alts = $xpath->query("//*[@alt]");//(count($xpath->query("//*[contains(@alt,'" . $keyword . "')]")) > 0);
	foreach ($alts as $alt)
	{
		$match = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($alt->getAttribute('alt')), $kw) !== false)
			{
				$match = true;
				break;
			}
		}
		
		if ($match)
		{
			$inAlt = true;
			break;
		}
	}
	
	if ($focused)
	{
		$firstwords = implode(" ", array_slice(explode(" ", $content_plain, 101), 0, 100));
		$inFirst = false;
		foreach ($keyword as $kw)
		{
			if (strpos(strtolower($firstwords), $kw) !== false)
			{
				$inFirst = true;
				break;
			}
		}
		
		//Focused
		if (!$inDescritpion)
		{
			$full -= (5/100) * 100;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in the Meta-Description'));
		}
		
		if (!$inUrl)
		{
			$full -= (5/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in the URL'));
		}
		
		if (!$inTitle)
		{
			$full -= (20/100) * 100;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in Meta-Title!'));
		}
		
		if (!$inH1)
		{
			$full -= (15/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in a H1-Tag!'));
		}
		
		if (!$inH2)
		{
			$full -= (10/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in a H2-Tag!'));
		}
		
		if (!$inAlt)
		{
			$full -= (10/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword is not contained in an Alt-Tag!'));
		}
		
		if (!$startsTitle)
		{
			$full -= (10/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword should be at the beginning of the Meta-Title.'));
		}
		
		if (!$inFirst)
		{
			$full -= (5/100) * 100 ;
			array_push($res->notes, array('type' => '2', 'msg' => 'Keyword should be within the first 100 words.'));
		}
	}
	else
	{
		//Unfocused
		if (!$inDescritpion)
		{
			$full -= (10/100) * 100;
			array_push($res->notes, array('type' => '0', 'msg' => 'Keyword is not contained in the Meta-Description'));
		}
		
		if (!$inH1 && !$inH2 && !$inH3 && !$inH4 && !$inH5 && !$inH6)
		{
			$full -= (35/100) * 100;
			array_push($res->notes, array('type' => '1', 'msg' => 'Keyword is not contained in a Caption!'));
		}
		
		if (!$inBold && !$inItalic && !$inAlt)
		{
			$full -= (20/100) * 100;
			array_push($res->notes, array('type' => '1', 'msg' => 'Keyword is not contained in a Bold-, Italic- or Alt-Tag!'));
		}
	}
	
	//Both
	if ($res->density < 2)
	{
		$full -= ($focused ? ((20/100) * 100) : ((35/100) * 100));
		array_push($res->notes, array('type' => $focused ? '2' : '1', 'msg' => 'Keyword-Density is too low!'));
	}
	else if ($res->density > 5)
	{
		$full -= ($focused ? ((10/100) * 100) : ((25/100) * 100));
		array_push($res->notes, array('type' => $focused ? '2' : '1', 'msg' => 'Keyword-Density is too high!'));
	}
	else if ($res->density > 3)
	{
		array_push($res->notes, array('type' => $focused ? '2' : '1', 'warning' => true, 'msg' => 'Keyword-Density is a little too high.'));
	}
	
	$res->efficiency = $full / 100 * 100;
	
	return $res;
}

function wsko_get_ga_query_data($webmaster, $start_time, $end_time, $dimension, $limit, $for_url = false, $order = false, $search = false)
{
	$q = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
	$q->setStartDate(date("Y-m-d", $start_time));
	$q->setEndDate(date("Y-m-d", $end_time));
	$q->setDimensions(array($dimension));
	$q->setRowLimit($limit);
	//$q->setAggregationType('byPage');
	$q->setSearchType('web');
	//$q->setDimensions(array('page', 'query', 'country', 'device'));
	//$sites = $webmaster->sites->listSites();
	//var_dump(json_encode($sites->toSimpleObject()));
	
	if ($for_url || $search)
	{
		$filters = array();
		
		if ($for_url)
		{
			$filter = new Google_Service_Webmasters_ApiDimensionFilter;
			$filter->setDimension("page");
			$filter->setOperator("equals");
			$filter->setExpression($for_url);
			array_push($filters, $filter);
		}
		
		if ($search)
		{
			$filter = new Google_Service_Webmasters_ApiDimensionFilter;
			$filter->setDimension($dimension);
			$filter->setOperator("contains");
			$filter->setExpression($search);
			array_push($filters, $filter);
		}
		
		$filter_group = new Google_Service_Webmasters_ApiDimensionFilterGroup;
		$filter_group->setGroupType('and');
		$filter_group->setFilters($filters);

		$q->setDimensionFilterGroups(array($filter_group));
	}
	
	try
	{
		$data = $webmaster->searchanalytics->query(wsko_get_host_base(), $q);
		if ($data)
		{
			$rows = $data->getRows();
			foreach ($rows as $row)
			{
				$row->ctr = $row->ctr * 100;
			}
			if ($order)
			{
				switch ($order)
				{
					case 0: //Key DESC
						usort($rows, function($a, $b)
						{
							return strcmp($a->keys[0], $b->keys[0]);
						});
						$rows = array_reverse($rows);
						break;
					case 1: //Key ASC
						usort($rows, function($a, $b)
						{
							return strcmp($a->keys[0], $b->keys[0]);
						});
						break;
					/*case 2: //Clicks DESC is default
						break;*/
					case 3: //Clicks ASC is default
						$rows = array_reverse($rows);
						break;
					case 4: //Position DESC
						usort($rows, function($a, $b)
						{
							if ($a->position == $b->position) {
								return 0;
							}
							return ($a->position < $b->position) ? 1 : -1;
						});
						break;
					case 5: //Position ASC
						usort($rows, function($a, $b)
						{
							if ($a->position == $b->position) {
								return 0;
							}
							return ($a->position < $b->position) ? -1 : 1;
						});
						break;
					case 6: //Impression DESC
						usort($rows, function($a, $b)
						{
							if ($a->impressions == $b->impressions) {
								return 0;
							}
							return ($a->impressions < $b->impressions) ? 1 : -1;
						});
						break;
					case 7: //Impression ASC
						usort($rows, function($a, $b)
						{
							if ($a->impressions == $b->impressions) {
								return 0;
							}
							return ($a->impressions < $b->impressions) ? -1 : 1;
						});
						break;
					case 8: //Ctr DESC
						usort($rows, function($a, $b)
						{
							if ($a->ctr == $b->ctr) {
								return 0;
							}
							return ($a->ctr < $b->ctr) ? 1 : -1;
						});
						break;
					case 9: //Ctr ASC
						usort($rows, function($a, $b)
							{
								if ($a->ctr == $b->ctr) {
									return 0;
								}
								return ($a->ctr < $b->ctr) ? -1 : 1;
							});
							break;
					}
				}
				
				foreach ($rows as $key => $row)
				{
					$rows[$key]->position = round($row->position);
				}
				return $rows;
		}
	}
	catch (Exception $e)
	{
		wsko_report_error('exception', 'Query Error', $e, 'A google query failed for the range ' . $start_time . ' to ' . $end_time . '.');
		return -1;
		//var_dump($e);
	}
	
	wsko_report_error('error', 'Query Error', 'A google query failed unexpectedly for the range ' . $start_time . ' to ' . $end_time . '.');
	return false;
}

function wsko_get_cache_data($start_time, $end_time, $dimension, $limit, $order = false, $search = false)
{
	global $wpdb, $wsko_data;
	
	$type = -1;
	$orderby = "clicks DESC";
	switch ($dimension)
	{
		case 'query':
			$type = 0;
			break;
		case 'page':
			$type = 1;
			break;
		case 'date':
			$type = 2;
			$orderby = "time ASC";
			break;
	}
	
	if ($order)
		$orderby = $order;
	
	if (!empty($wpdb->charset) && $wpdb->charset == 'utf8mb4')
		$collate = 'COLLATE utf8mb4_bin';
	
	$cache_rows = $wpdb->get_results(
	"	SELECT table_cr.keyval AS keyval, SUM(table_cr.clicks) AS clicks, SUM(table_cr.impressions) AS impressions, ROUND(AVG(table_cr.position)) AS position
			FROM " . WSKO_CACHE_ROWS_TABLE . " AS table_cr
			INNER JOIN " . WSKO_CACHE_TABLE . " AS table_c
			ON table_c.id = table_cr.cache_id
			AND table_cr.type = " . $type . "
			WHERE table_c.time BETWEEN '" . date('Y-m-d H:i:s', $start_time) . "' AND '" . date('Y-m-d H:i:s', $end_time) . "'
			" . ($search ? "AND table_cr.keyval LIKE '%" . $search . "%'" : "" ) . "
			GROUP BY table_cr.keyval " . $collate . "
			ORDER BY " . $orderby ./* "
			LIMIT " . $limit .*/ "
	");
	$rows = array();
	foreach ($cache_rows as $row)
	{
		$row->keys = array($row->keyval);
		if ($row->impressions > 0) //recalc ctr
			$row->ctr = $row->clicks / $row->impressions * 100;
		else
			$row->ctr = 0;
		
		unset($row->keyval);
		$rows[$row->keys[0]] = $row;
	}
	
	return $rows;
}

function wsko_get_host_base()
{
	if (isset($_SERVER['HTTPS']))
	{
		$protocol = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != "off") ? "https" : "http";
	}
	else
	{
		$protocol = 'http';
	}
	return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function wsko_url_get_title($url, $inner = false)
{
	global $wsko_url_cache;
	
	if (!isset($wsko_url_cache))
		$wsko_url_cache = array(
				'post_types' => array(),
				'terms' => array(),
			);
		
	$res = new stdClass;
	$res->title = '';
	$res->type = 'unknown';
	//$res->post_id = $postid;
	
	$postid = wsko_url_to_postid($url);
	
	if ($postid)
	{
		$res->title = get_the_title($postid);
		$res->type = 'post';
		$res->post_id = $postid;
	}
	else 
	{
		$isType = false;
		$post_types = get_post_types();
		foreach ($post_types as $type)
		{
			if (!isset($wsko_url_cache['post_types'][$type]))
				$url_c = $wsko_url_cache['post_types'][$type] = get_post_type_archive_link($type);
			else
				$url_c = $wsko_url_cache['post_types'][$type];
			
			if ($url_c == $url)
			{
				$type = get_post_type_object($type);
				$res->title = 'Archive - ' . $type->label;
				$res->type = 'archive';
				$isType = true;
				break;
			}
		}
		
		if (!$isType)
		{
			$isTerm = false;
			$terms = get_terms();
			foreach ($terms as $term)
			{
				if (!isset($wsko_url_cache['terms'][(string)$term->term_id]))
					$url_c = $wsko_url_cache['terms'][(string)$term->term_id] = get_term_link($term->term_id, $term->taxonomy);
				else
					$url_c = $wsko_url_cache['terms'][(string)$term->term_id];
				
				if ($url_c == $url)
				{
					$res->title = 'Term Archive - ' . $term->name;
					$res->type = 'term';
					$isTerm = true;
					break;
				}
			}
		
			if (!$isTerm)
			{
				if ($inner)
					$res->title = 'No Wordpress-Post found.';
				else
					$res = wsko_url_get_title(wsko_get_effective_url($url), true);
				
			}
		}
	}
	
	return $res;
}

function wsko_url_to_postid($url)
{
	global $wp_rewrite;

	$pid = url_to_postid($url);
	if ($pid)
		return $pid;
	
	$url = apply_filters('url_to_postid', $url);

	// First, check to see if there is a 'p=N' or 'page_id=N' to match against
	if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
		$id = absint($values[2]);
		if ( $id )
			return $id;
	}

	// Check to see if we are using rewrite rules
	$rewrite = $wp_rewrite->wp_rewrite_rules();

	// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
	if ( empty($rewrite) )
		return 0;

	// Get rid of the #anchor
	$url_split = explode('#', $url);
	$url = $url_split[0];

	// Get rid of URL ?query=string
	$url_split = explode('?', $url);
	$url = $url_split[0];

	// Add 'www.' if it is absent and should be there
	if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
		$url = str_replace('://', '://www.', $url);

	// Strip 'www.' if it is present and shouldn't be
	if ( false === strpos(home_url(), '://www.') )
		$url = str_replace('://www.', '://', $url);

	// Strip 'index.php/' if we're not using path info permalinks
	if ( !$wp_rewrite->using_index_permalinks() )
		$url = str_replace('index.php/', '', $url);

	if ( false !== strpos($url, home_url()) ) {
		// Chop off http://domain.com
		$url = str_replace(home_url(), '', $url);
	} else {
		// Chop off /path/to/blog
		$home_path = parse_url(home_url());
		$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
		$url = str_replace($home_path, '', $url);
	}

	// Trim leading and lagging slashes
	$url = trim($url, '/');

	$request = $url;
	// Look for matches.
	$request_match = $request;
	foreach ( (array)$rewrite as $match => $query) {
		// If the requesting file is the anchor of the match, prepend it
		// to the path info.
		if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
			$request_match = $url . '/' . $request;

		if ( preg_match("!^$match!", $request_match, $matches) ) {
			// Got a match.
			// Trim the query of everything up to the '?'.
			$query = preg_replace("!^.+\?!", '', $query);

			// Substitute the substring matches into the query.
			$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

			// Filter out non-public query vars
			global $wp;
			parse_str($query, $query_vars);
			$query = array();
			foreach ( (array) $query_vars as $key => $value ) {
				if ( in_array($key, $wp->public_query_vars) )
					$query[$key] = $value;
			}

		// Taken from class-wp.php
		foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
			if ( $t->query_var )
				$post_type_query_vars[$t->query_var] = $post_type;

		foreach ( $wp->public_query_vars as $wpvar ) {
			if ( isset( $wp->extra_query_vars[$wpvar] ) )
				$query[$wpvar] = $wp->extra_query_vars[$wpvar];
			elseif ( isset( $_POST[$wpvar] ) )
				$query[$wpvar] = sanitize_text_field($_POST[$wpvar]);
			elseif ( isset( $_GET[$wpvar] ) )
				$query[$wpvar] = sanitize_text_field($_GET[$wpvar]);
			elseif ( isset( $query_vars[$wpvar] ) )
				$query[$wpvar] = $query_vars[$wpvar];

			if ( !empty( $query[$wpvar] ) ) {
				if ( ! is_array( $query[$wpvar] ) ) {
					$query[$wpvar] = (string) $query[$wpvar];
				} else {
					foreach ( $query[$wpvar] as $vkey => $v ) {
						if ( !is_object( $v ) ) {
							$query[$wpvar][$vkey] = (string) $v;
						}
					}
				}

				if ( isset($post_type_query_vars[$wpvar] ) ) {
					$query['post_type'] = $post_type_query_vars[$wpvar];
					$query['name'] = $query[$wpvar];
				}
			}
		}

			// Do the query
			$query = new WP_Query($query);
			if ( !empty($query->posts) && $query->is_singular )
				return $query->post->ID;
			else
				return 0;
		}
	}
	return 0;
}
?>