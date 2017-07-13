<?php
if (!defined('ABSPATH')) exit;

class WSKO_AdminMenu
{
	static $instance;
	
	public function __construct()
	{
		if (wsko_check_user_permissions())
		{
			add_action('admin_menu', [$this, 'menu_items']);
			add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
			add_action('admin_enqueue_scripts', [$this, 'load_scripts']);
			add_action('admin_footer', [$this, 'post_footer']);
		}
	}

	public function menu_items()
	{
		global $wsko_data;
		
		if (current_user_can('manage_options'))
		{
			$hook = add_menu_page(
				'WSKO - Dashboard',
				'WP SEO Keyword Optimizer',
				'manage_options',
				'wsko_main_view',
				[ $this, 'main_view' ]
			);
			
			$hook = add_submenu_page(
				'wsko_main_view',
				'WSKO - Settings',
				'Settings',
				'manage_options',
				'wsko_settings_view',
				[ $this, 'settings_view' ]
			);
			
			//if (isset($wsko_data['activate_log']) && $wsko_data['activate_log'])
			//{
				$hook = add_submenu_page(
					'wsko_main_view',
					'WSKO - Error Reporting',
					'Error Reporting',
					'manage_options',
					'wsko_reporting_view',
					[ $this, 'reporting_view' ]
				);
			//}
		}
		else
		{
			$hook = add_menu_page(
				'WSKO - Dashboard',
				'WP SEO Keyword Optimizer',
				WSKO_VIEW_CAP,
				'wsko_main_view',
				[ $this, 'main_view' ]
			);
		}
	}
	
	public function load_scripts($hook)
	{
		if ($hook != 'post.php' && (!isset($_GET['page']) || !in_array($_GET['page'], array('wsko_main_view', 'wsko_settings_view', 'wsko_reporting_view'))))
			return;
		
		global $wsko_data;
		
		//CDN
		if (isset($wsko_data['add_bootstrap']) && $wsko_data['add_bootstrap'])
		{
			wp_register_script('bootstrap_js', plugins_url('includes/bootstrap/js/bootstrap.min.js' , dirname(__FILE__)));
			wp_enqueue_script('bootstrap_js');
			
			wp_register_style('bootstrap_css', plugins_url('includes/bootstrap/css/bootstrap.min.css', dirname(__FILE__ )));
			wp_enqueue_style('bootstrap_css');
		}
		
		if (isset($wsko_data['add_moment']) && $wsko_data['add_moment'])
		{
			wp_register_script('moment', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js');
			wp_enqueue_script('moment');
		}
		
		if (isset($wsko_data['add_bootstrap_datepicker']) && $wsko_data['add_bootstrap_datepicker'])
		{
			wp_register_script('bootstrap_datepicker_js', '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js');
			wp_enqueue_script('bootstrap_datepicker_js');
			
			wp_register_style('bootstrap_datepicker_css', '//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css');
			wp_enqueue_style('bootstrap_datepicker_css'); 
		}

		if (isset($wsko_data['add_datatables']) && $wsko_data['add_datatables'])
		{
			wp_register_script('jquery_datatables', 'https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js');
			wp_enqueue_script('jquery_datatables');
			
			wp_register_script('bootstrap_datatables_js', 'https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js');
			wp_enqueue_script('bootstrap_datatables_js');
		
			wp_register_style('bootstrap_datatables_css', 'https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css');
			wp_enqueue_style('bootstrap_datatables_css');
		}
		
		if (isset($wsko_data['add_google_chart']) && $wsko_data['add_google_chart'])
		{
			wp_register_script('google_charts', 'https://www.gstatic.com/charts/loader.js');
			wp_enqueue_script('google_charts');
		}
		
		if (isset($wsko_data['add_fontawesome']) && $wsko_data['add_fontawesome'])
		{
			wp_register_style('font_awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
			wp_enqueue_style('font_awesome');
		}
		
		if (isset($wsko_data['add_icheck']) && $wsko_data['add_icheck'])
		{
			wp_register_style('icheck_css', plugins_url('includes/iCheck/line/blue.css' , dirname(__FILE__)));
			wp_enqueue_style('icheck_css');
			
			wp_register_script('icheck_js', plugins_url('includes/iCheck/icheck.min.js' , dirname(__FILE__)), array('jquery'));
			wp_enqueue_script('icheck_js');
		}
		
		//WSKO
		wp_enqueue_script('wsko_front_js', plugins_url('admin/js/front.js', dirname(__FILE__)), array(), WSKO_VERSION);
		
		if ($hook == 'post.php')
			wp_enqueue_script('wsko_post_js', plugins_url('admin/js/post.js', dirname(__FILE__)), array(), WSKO_VERSION);
		
		wp_enqueue_script('wsko_admin_js', plugins_url('admin/js/admin.js', dirname(__FILE__)), array(), WSKO_VERSION);
		
		wp_enqueue_script('wsko_icheck_js', plugins_url('admin/js/icheck.js', dirname(__FILE__)), array(), WSKO_VERSION);
			
		wp_register_style('wsko_admin_css', plugins_url('admin/css/admin.css', dirname(__FILE__ )), array(), WSKO_VERSION);
		wp_enqueue_style('wsko_admin_css');
		
		wp_enqueue_script('wsko_lazy_js', plugins_url('admin/js/lazy.js', dirname(__FILE__)), array(), WSKO_VERSION);
	}
	
	public function add_meta_boxes()
	{
		global $wsko_data;
		
		if (isset($wsko_data['activate_post_widget']) && $wsko_data['activate_post_widget'])
		{
			$wsko_post_types = get_post_types('', 'objects');
			
			foreach ($wsko_post_types as $key=>$type)
			{
				add_meta_box(
					'wsko_post_metabox',
					'WP SEO Keyword Optimizer',
					array( &$this, 'post_meta_box_view' ),
					$key,
					'side',
					'high'
				);
			}
		}
	}
	
    public function post_meta_box_view() 
    {
		global $post, $pagenow, $wsko_invalid, $wsko_data, $wsko_plugin_path;
		
		wsko_require_google_lib(true);
		include_once($wsko_plugin_path . 'upgrade.php');
		
		if ($pagenow != 'post.php')
		{
			?><p>WSKO is available after first save.</p><?php
			return;
		}
		
		$wsko_path = plugin_dir_path( __FILE__ );
		
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		$client = wsko_get_ga_client();
		if ($client)
		{
			$invalid = wsko_check_google_access($client, $token, true);
			
			if ($invalid)
			{
				$wsko_invalid = $invalid;
				echo $invalid;
			}
			else
			{
				?>
				<div style="text-align:center; margin-top:10px;" id="wsko_lazy_beacon" data-post="<?=$post->ID?>" data-nonce="<?=wp_create_nonce('wsko-get-post-box-' . $post->ID)?>">
					<i class="fa fa-spinner fa-2x fa-pulse"></i>
				</div>
				<?php
			}
		}
		else
		{
			$wsko_invalid = 'Critical Google API Error. Please check the <a href="' . admin_url('admin.php?page=wsko_main_view'). '">Plugin Dashboard</a> for more information.';
			echo $wsko_invalid;
		}
    }

	function post_footer()
	{
		global $wpdb, $post, $pagenow, $wsko_rows, $wsko_invalid, $wsko_data;
		if ($pagenow != 'post.php' || $wsko_invalid)
			return;
		
		$wsko_path = plugin_dir_path( __FILE__ );
		
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		//$client = wsko_get_ga_client();
		
		if ($token)
		{
			$rows = $wsko_rows;
			?>
			<div id="wsko_lazy_beacon_footer">
			</div>
			<?php
		}
	}
	
	public function main_view()
	{
		global $wpdb, $wsko_data, $wsko_plugin_path;
		$is_admin_view = true; //Lazy loading flag for templates
		wsko_require_google_lib(true);
		include_once($wsko_plugin_path . 'upgrade.php');
		
		$is_admin = current_user_can('manage_options');
		
		$wsko_path = plugin_dir_path( __FILE__ );
		
		$invalid = false;
		
		$token = (isset($wsko_data['token']) ? $wsko_data['token'] : false);
		if ($token)
		{
			if (isset($_POST['revoke']) || isset($_GET['revoke']))
			{
				$client = wsko_get_ga_client();
				if ($client)
					$client->revokeToken($token);
				$wsko_data['token'] = $token = false;
				update_option('wsko_init', $wsko_data);
			}
		}
		
		if (isset($_POST['code']))
		{
			$client = wsko_get_ga_client();
			if ($client)
			{
				$client->authenticate($_POST['code']);
				$token = $client->getAccessToken();
				if ($token)
				{
					$wsko_data['activate_post_widget'] = isset($_POST['activate_post_widget']);
					$wsko_data['activate_caching'] = isset($_POST['activate_caching']);
					$wsko_data['token'] = $token;
					update_option('wsko_init', $wsko_data);
				}
			}
		}
		
		$nextCron = wp_next_scheduled('wsko_cache_keywords');
		$today = wsko_get_midnight(time());
		
		$caching_active = false;
		$first_date = $today - (60 * 60 * 24 * 90);
		if (isset($wsko_data['activate_caching']) && $wsko_data['activate_caching'] && !defined('WSKO_PLUGIN_INCOMP_CACHE'))
		{
			$caching_active = true;
			$start = $today - (60 * 60 * 24 * 3);
			$end = $today - (60 * 60 * 24 * 90);
			
			$first_date = strtotime($wpdb->get_var('SELECT MIN(time) FROM ' . WSKO_CACHE_TABLE));

			$temp = $wpdb->get_results("SELECT * FROM " . WSKO_CACHE_TABLE . " WHERE (time BETWEEN '" . date('Y-m-d H:i:s', $end) . "' AND '" . date('Y-m-d H:i:s', $start) . "')");
			$num_cache_rows = $wpdb->num_rows;
			$limits = 0;
			$max_limit = 0;
			foreach ($temp as $r)
			{
				if ($r->is_limit)
				{
					$max = max(array(count(json_decode($r->query)),count(json_decode($r->page)),count(json_decode($r->date))));
					if ($max > $max_limit)
						$max_limit = $max;
					
					$limits++;
				}
			}
		}
		if (isset($_GET['start_time']) && isset($_GET['end_time']))
		{
			$time = intval(sanitize_text_field($_GET['start_time']));
			//$time = get_date_from_gmt(date('Y-m-d H:i:s', $time));
			$time2 = intval(sanitize_text_field($_GET['end_time']));
			//$time2 = get_date_from_gmt(date('Y-m-d H:i:s', $time2));
		}
		else
		{
			$time2 = $today - (60 * 60 * 24 * 3);
			$time = $today - (60 * 60 * 24 * 30);
		}
		$client = wsko_get_ga_client();
		if ($client && $token)
		{
			$invalid = wsko_check_google_access($client, $token, false);
		}
		?>
		<!-- FB Sharer-->
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/de_DE/sdk.js#xfbml=1&version=v2.8";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>

		<div class="wrap">
			<div class="col-sm-12">
			<div class="wsko_main_wrapper">
				<h2 style="line-height: 17px;"><img class="wsko_logo" src="<?=plugins_url('/img/wsko_signet.png', __FILE__)?>" /><?=__('WP SEO Keyword Optimizer', 'wsko-lang')?>
				
				<?php /*
				<div class="wsko_social_wrapper" style="    line-height: 14px; display: inline-block; background-color:#ddd; padding:15px; float:right; border-radius:4px;">
				<div style="vertical-align: text-top; margin-left: 5px;" class="fb-share-button" data-href="https://wordpress.org/plugins/wp-seo-keyword-optimizer/" data-layout="button" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwww.bavoko.services%2Fwordpress%2F&amp;src=sdkpreparse">Share</a></div>
				
				<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wordpress.org/plugins/wp-seo-keyword-optimizer/" data-text="WP SEO Keyword Optimizer - Wordpress SEO Analysis & Optimization" data-hashtags="WordPress,SEO,WSKO,">Tweet</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
				
				<script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US</script>
				<script type="IN/Share" data-url="https://wordpress.org/plugins/wp-seo-keyword-optimizer/"></script>
				
				</div> */ ?>
				<div class="wsko_social_wrapper" style="display: inline-block; padding:10px; float:right;">
				<ul class="share-buttons">
				  <li style="vertical-align: text-top;
								color: #909090;
								padding: 0px 5px;
								border-radius: 4px;
								font-size: 19px;">Share this Plugin</li>
				  <li><a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-seo-keyword-optimizer%2F&t=WP%20SEO%20Keyword%20Optimizer%20-%20WordPress%20SEO%20Plugin" title="Share on Facebook" target="_blank"><i class="fa fa-facebook-square wsko_fa_fb" aria-hidden="true"></i></a></li>
				  <li><a href="https://twitter.com/intent/tweet?source=https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-seo-keyword-optimizer%2F&text=WP%20SEO%20Keyword%20Optimizer%20-%20WordPress%20SEO%20Plugin:%20https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-seo-keyword-optimizer%2F" target="_blank" title="Tweet"><i class="fa fa-twitter-square wsko_fa_twitter" aria-hidden="true"></i></a></li>
				  <li><a href="http://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-seo-keyword-optimizer%2F&title=WP%20SEO%20Keyword%20Optimizer%20-%20WordPress%20SEO%20Plugin&summary=&source=https%3A%2F%2Fwordpress.org%2Fplugins%2Fwp-seo-keyword-optimizer%2F" target="_blank" title="Share on LinkedIn"><i class="fa fa-linkedin-square wsko_fa_linkedin" aria-hidden="true"></i></a></li>
				</ul>
				</div>
				
				</h2>
			</div>
			</div>
			
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-9">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable wsko_main_wrapper" style="padding:20px 0px">
							<?php
							?>
							<div>
								<?php
								if ($invalid)
								{
									?><div class="bs-callout wsko-notice wsko-notice-error"><?php
										echo $invalid;
									?></div>
									<div class="pull-right">
										<form method="POST">
											<input name="page" type="hidden" value="wsko_main_view">
											<input type="hidden" name="revoke" value="true">
											<input class="btn btn-danger" type="submit" value="Revoke Access/Logout">
										</form>
									</div><?php
								}
								else
								{
									if ($token)
									{
										$star = "<i class='fa fa-star'></i>";
										?><div id="wsko_lazy_admin_overview_beacon" data-nonce="<?=wp_create_nonce('wsko-lazy-admin')?>"></div>										
										<form id="wsko_reload_data_form" method="GET">
											<input name="page" type="hidden" value="wsko_main_view">
											<div class="pull-right" style="margin:15px 10px 0 0;">
												<a href="<?=$is_admin ? admin_url('admin.php?page=wsko_settings_view') : '#'?>" data-toggle="tooltip" title="<?=$caching_active ? 'Caching active' : ($is_admin ? 'Enable caching to <ul><li>'.$star.' Store your data more than 90 days</li><li>'.$star.' See more detailed statistics</li><li>'.$star.' Get everything done faster!</li></ul>' : 'Caching inactive (max. 90 days can be viewed)')?>" data-html="true"><div class="wsko_caching_info <?=$caching_active ? 'wsko_green' : 'wsko_gray'?>" style="<?=$caching_active ? 'color:#fff;' : 'color:#999;'?>">Caching: <i style="<?=$caching_active ? 'color:#fff;' : 'color:#999;'?>" class="fa fa-<?=$caching_active ? 'check' : 'times'?>"></i></div></a>
												<div id="wsko_time_scope" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc;" data-start="<?=$first_date?>">
													<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
													<span style="width:280px"></span> <b class="caret"></b>
													<input class="wsko-start-time" name="start_time" type="hidden" value="<?=$time?>">
													<input class="wsko-end-time" name="end_time" type="hidden" value="<?=$time2?>">
													<i class="fa fa-info-circle wsko_info" aria-hidden="true" data-toggle="tooltip" title="Due to Google limitations the data for the last two days (<?=date("d.m.y", time() - 60 * 60 * 24 * 2)?> and <?=date("d.m.y", time() - 60 * 60 * 24)?>) is currently not available. Each day will be available after 48 hours have passed."></i>
												</div>
											</div>
										</form>
										
										<ul class="nav nav-tabs wsko_main_nav wsko_important_nav">
											<li class="active"><a data-toggle="tab" href="#wsko_overview">Overview</a></li>
											<li><a id="wsko_open_keywords" data-toggle="tab" href="#wsko_keyword_overview">Keywords</a></li>
											<li><a id="wsko_open_pages" data-toggle="tab" href="#wsko_page_overview">Pages</a></li>
										</ul>

										<?php
										if (defined('WSKO_GOOGLE_INCLUDE_FAILED') && WSKO_GOOGLE_INCLUDE_FAILED)
										{
											$old_ver = Google_Client::LIBVER;
											
											if (!$client)
											{
												?>
												<div class="bs-callout wsko-notice wsko-notice-error">
													Google API 2.1.0 couldn't be loaded, because you are already loading another version (<?=$old_ver?>) of it. This has caused a failure with the client.
													<br/>
													<p class="wsko_callout_note"><strong>Note:</strong> This error is usually caused by a plugin or theme loading an old version of the Google Client API. Updating your plugins and themes may solve this error.</p>
												</div>
												<?php
											}
											else
											{
												?>
												<div class="bs-callout wsko-notice wsko-notice-warning">
													Google API 2.1.0 couldn't be loaded, because you are already loading another version (<?=$old_ver?>) of it. This may cause errors with deprecated methods, if your version is outdated.
													<br/>
													<p class="wsko_callout_note"><strong>Note:</strong> This error is usually caused by a plugin or theme loading an old version of the Google Client API. Updating your plugins and themes may solve this error.</p>
												</div>
												<?php
											}
										}
										
										if ($caching_active)
										{
											if ($num_cache_rows < 88)
											{
												$missing = (88 - $num_cache_rows);
												$missing_time = date('i:s', $missing * 2.35); //avg process time (1.35) + counter-query-limit-sleep between calls(1.0)
												?>
												<div class="bs-callout wsko-notice wsko-notice-error">
													There are <?=$missing?> missing data sets in your last 90 days cache. You can wait for the next cronjob (<?=$nextCron ? date('d/m/Y H:i:s', $nextCron) : 'disabled'?>) or <?php if ($is_admin) { ?><a class="wsko_update_cache_btn button wsko_notice_btn" href="#" data-nonce="<?=wp_create_nonce('wsko-update-cache')?>"><i class="fa fa-spinner fa-pulse" style="display:none;"></i> Update Manually</a> (This may take a while, estimated: <?=$missing_time?>)<?php }
													else { ?>you'll need to contact an administrator to update the cache manually.<?php } ?>
													<br/>
													<?php if ($is_admin) { ?>
														<p class="wsko_callout_note"><strong>Note:</strong> Please update your data by clicking on 'Update Manually'. Google only stores data from the last 90 days - a dataset older than that can't be updated anymore.</p>
													<?php } ?>
												</div>
												<?php
											}
											
											if ($limits)
											{
												?>
												<div class="bs-callout wsko-notice wsko-notice-<?=$limits < 45 ? 'warning' : 'error'?>">
													There are <?=$limits?> data sets in your last 90 days cache that may have hit the limit. <?php if ($is_admin) { ?>Please go to <a target="_blank" href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings</a> and increase your Dataset Limit (Cache) to a value higher than <b><?=$max_limit?></b>. This change requires a database update. You can wait for the next cronjob (<?=$nextCron ? date('d/m/Y H:i:s', $nextCron) : 'disabled'?>) or <a class="wsko_update_cache_btn button wsko_notice_btn" href="#" data-nonce="<?=wp_create_nonce('wsko-update-cache')?>"><i class="fa fa-spinner fa-pulse" style="display:none;"></i> Update Manually</a><?php }
																																														else { ?>Please contact an administrator to change the Dataset Limit (Cache). Note that this may be impossible due to performance reasons or API limitations.<?php } ?>
													<br/>
													<?php if ($is_admin) { ?>
														<p class="wsko_callout_note"><strong>Note:</strong> Please update your data by clicking on 'Update Manually'. Google only stores data from the last 90 days - a dataset older than that can't be updated anymore.</p>
													<?php } ?>
												</div>
												<?php
											}
										}
										
										if (defined('WSKO_PLUGIN_INCOMP_CACHE') && WSKO_PLUGIN_INCOMP_CACHE)
										{
											?>
											<div class="bs-callout wsko-notice wsko-notice-error">
												The following plugins are known to have conflicts with the caching mode of this plugin. Caching has been automatically disabled. Please deactivate these plugins to use caching.
												<br/>
												<br/>
												<?php
												$cs = explode(',', WSKO_PLUGIN_INCOMP_CACHE);
												foreach ($cs as $c)
												{
													?><b><?=$c?></b></br><?php
												}
												?>
											</div>
											<?php
										}
										?>
										
										<div id="wsko_lazy_notices"></div>
										
										<div class="tab-content wsko_dashboard_content" style="margin-bottom: 10px; border-bottom: solid 1px #ddd; padding-bottom: 10px;">
											<div id="wsko_overview" class="tab-pane fade in active">
												<?php include($wsko_path. 'templates/view-overview.php'); ?>
											</div>
											<div id="wsko_page_overview" class="tab-pane fade">
												<?php include($wsko_path . 'templates/view-pages.php'); ?>
											</div>
											<div id="wsko_keyword_overview" class="tab-pane fade">
												<?php include($wsko_path . 'templates/view-keywords.php'); ?>
											</div>
										</div>
										
										<a class="btn btn-link" target="_blank" href="https://www.bavoko.services/wordpress/wsko-wordpress-seo-plugin/"><i style="padding-right:5px;" class="fa fa-info-circle" aria-hidden="true"></i> WSKO Documentation</a>
										<a class="btn btn-link" target="_blank" href="https://wordpress.org/support/plugin/wp-seo-keyword-optimizer"><i style="padding-right:5px;" class="fa fa-user" aria-hidden="true"></i> Support</a>
										<a class="btn btn-link wsko-give-feedback" href="#"><i style="padding-right:5px;" class="fa fa-comments" aria-hidden="true"></i> Feedback</a>
										
										<div class="pull-right">
											<form method="POST">
												<input name="page" type="hidden" value="wsko_main_view">
												<input type="hidden" name="revoke" value="true">
												<input class="btn btn-danger" type="submit" value="Revoke Access/Logout">
											</form>
										</div>
										<?php
									}
									else
									{
										if ($client)
											$auth_url = $client->createAuthUrl();
										else
											$auth_url = false;
										
										$inc = (!$client ? true : false);
										
										if (defined('WSKO_INCOMP_VERSION'))
										{
											?>
											<div class="bs-callout wsko-notice wsko-notice-error">
												You're using an incompatible version of PHP or WordPress. You need to meet the following requirements:
												<ul>
													<li>PHP 5.5+</li>
													<li>WordPress 4.0+</li>
												</ul>
											</div>
											<?php
											$inc = true;
										}
										
										if (defined('WSKO_GOOGLE_INCLUDE_FAILED') && WSKO_GOOGLE_INCLUDE_FAILED)
										{
											$old_ver = Google_Client::LIBVER;
											
											if (!$client)
											{
												?>
												<div class="bs-callout wsko-notice wsko-notice-error">
													Google API 2.1.0 couldn't be loaded, because you are already loading another version (<?=$old_ver?>) of it. This has caused a failure with the client.
													<br/>
													<p class="wsko_callout_note"><strong>Note:</strong> This error is usually caused by a plugin or theme loading an old version of the Google Client API. Updating your plugins and themes may solve this error.</p>
												</div>
												<?php
												$inc = true;
											}
											else
											{
												?>
												<div class="bs-callout wsko-notice wsko-notice-warning">
													Google API 2.1.0 couldn't be loaded, because you are already loading another version (<?=$old_ver?>) of it. This may cause errors with deprecated methods, if your version is outdated.
													<br/>
													<p class="wsko_callout_note"><strong>Note:</strong> This error is usually caused by a plugin or theme loading an old version of the Google Client API. Updating your plugins and themes may solve this error.</p>
												</div>
												<?php
											}
										}
										
										if (defined('WSKO_PLUGIN_INCOMP_CACHE'))
										{
											?>
											<div class="bs-callout wsko-notice wsko-notice-error">
												The following plugins are known to have conflicts with the caching mode of this plugin. Caching has been automatically disabled. Please deactivate these plugins to use caching.
												<br/>
												<br/>
												<?php
												$cs = explode(',', WSKO_PLUGIN_INCOMP_CACHE);
												foreach ($cs as $c)
												{
													?><b><?=$c?></b></br><?php
												}
												?>
											</div>
											<?php
										}
										?>
										<div class="wsko_box_wrapper">
											<div style="text-align:center; width:550px; margin:0 auto; margin-bottom: 15px;">
													
												<div style="margin:15px; padding:15px;">
													<img style="margin-bottom:10px;" class="wsko_logo" src="<?=plugins_url('/img/wsko_signet.png', __FILE__)?>" /></br>
													<p>Authenticate with Google to get access to <strong>WP SEO Keyword Optimizer</strong></br>
													
													<div class="bs-callout bs-callout-primary" style="padding: 10px 15px; text-align: left;
														width: 420px;
														margin: 0 auto;">Your Google account needs to be the property owner of this domain in the Google Search Console</div>
													<?php
													if ($inc)
													{
														?>
														<a style="margin-top:15px;" class="wp-core-ui btn btn-danger" href="#">Access Token blocked!</a></p>
														<?php
													}
													else
													{
														?>
														<a style="margin-top:15px;" class="wp-core-ui button-primary" href="<?=$auth_url ? $auth_url : '#'?>" target="_blank">Get Access Token!</a></p>
														<?php
													} ?>
												</div>
												<hr />
												<form method="POST">
													<input name="page" type="hidden" value="wsko_main_view">
													
													<li style="background-color:#f2f2f2;    text-align: left;
																		list-style-type: none;
																		max-width:415px;
																		margin: 15px auto;
																		border-radius:3px;
																		padding: 15px 20px;">
													<p style="margin:0px;"><label>Options</label></p>
													<input class="form-control" type="checkbox" name="activate_post_widget" checked="checked"> Activate Content Optimizer in single posts/pages
													<hr style="margin: 10px 0px; border-top-color:#ddd;" />
													<input class="form-control" type="checkbox" name="activate_caching" <?=defined('WSKO_PLUGIN_INCOMP_CACHE') ? 'disabled' : 'checked="checked"'?>> Activate Caching (Recommended)
													</li>
											
													<input style="width:350px;display: inline-block;" placeholder="Insert Access Token" class="form-control" type="text" name="code" autocomplete="off">
													<input style="height:32px;" class="wp-core-ui button-primary" type="submit" value="Submit" <?=$inc ? 'disabled' : ''?>>
												</form>
												<hr />
												<p>Find out more about the <a target="_blank" href="https://www.google.com/intl/en/webmasters/">Google Search Console</a></p>
												<p><a target="_blank" href="https://wordpress.org/support/plugin/wp-seo-keyword-optimizer">Support</a></p>
											</div>	
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		include( $wsko_path. '/templates/modal-keyword-details.php');
		include( $wsko_path. '/templates/modal-feedback.php');
	}
	
	public function settings_view()
	{
		global $wsko_plugin_path;
		
		wsko_require_google_lib(true);
		include_once($wsko_plugin_path . 'upgrade.php');
		
		$wsko_path = plugin_dir_path( __FILE__ );
		$client = wsko_get_ga_client();
		
		if (isset($_GET["res"]))
		{
			if ($_GET["res"])
			{ ?>
				<div class="updated">
					<p><?=isset($_GET['msg']) ? $_GET['msg'] : 'Save success.'?></p>
				</div>
			<?php }
			else
			{ ?>
				<div class="updated">
					<p><?=isset($_GET['msg']) ? $_GET['msg'] : 'Save failed.'?></p>
				</div>
			<?php }
		} ?>
		<div class="wrap">
			<div class="col-sm-12">
				<div class="wsko_main_wrapper">
				<h2><img class="wsko_logo" src="<?=plugins_url('/img/wsko_signet.png', __FILE__)?>" />WP SEO Keyword Optimizer - Settings</h2>
				</div>
			</div>
			
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-9">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable wsko_main_wrapper" style="padding:20px 0px">
							<div id="wsko_view_settings" class="tab-pane fade in active">
								<?php include( $wsko_path. '/templates/view-settings.php'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		include( $wsko_path. '/templates/modal-feedback.php');
	}
	
	public function reporting_view()
	{
		global $wsko_plugin_path;
		
		wsko_require_google_lib(true);
		include_once($wsko_plugin_path . 'upgrade.php');
		
		$wsko_path = plugin_dir_path( __FILE__ );
		?>
		<div class="wrap">
			<div class="col-sm-12">
				<div class="wsko_main_wrapper">
				<h2><img class="wsko_logo" src="<?=plugins_url('/img/wsko_signet.png', __FILE__)?>" />WP SEO Keyword Optimizer - Error Reporting</h2>
				</div>
			</div>
			
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-9">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable wsko_main_wrapper" style="padding:20px 0px">
							<div id="wsko_view_settings" class="tab-pane fade in active">
								<?php include( $wsko_path. '/templates/view-reporting.php'); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		include( $wsko_path. '/templates/modal-feedback.php');
	}
	
	public static function get_instance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}
}

add_action('plugins_loaded', function()
{
	WSKO_AdminMenu::get_instance();
});

?>