<?php
global $wsko_data, $wpdb;
?>
<div class="row">
	<div class="col-md-12">
	<div class="wsko_box_wrapper">
		<div class="pull-right">
			<a class="btn btn-link wsko-give-feedback" href="#"><i style="padding-right:5px;" class="fa fa-comments" aria-hidden="true"></i> Feedback/Error Report</a>
			<a class="btn btn-link" target="_blank" href="https://www.bavoko.services/wordpress/wsko-wordpress-seo-plugin/"><i style="padding-right:5px;" class="fa fa-info-circle" aria-hidden="true"></i> WSKO Documentation</a>
		</div>
	
		<ul class="nav nav-tabs">
			<li class="active"><a class="wsko_chart_label" href="#tab_settings" data-toggle="tab"><i style="padding-right:5px;" class="fa fa-cog fa-blue" aria-hidden="true"></i> Settings</a><li>
			<li><a class="wsko_chart_label" href="#tab_advanced" data-toggle="tab"><i style="padding-right:5px;" class="fa fa-flag fa-blue" aria-hidden="true"></i> Advanced</a><li>
			<li><a class="wsko_chart_label" href="#tab_permissions" data-toggle="tab"><i style="padding-right:5px;" class="fa fa-unlock-alt fa-blue" aria-hidden="true"></i> Permissions</a><li>
		</ul>
		
		<form id="wsko_settings_save_form" data-nonce="<?=wp_create_nonce('wsko-save-settings')?>" onkeypress="return event.keyCode != 13;">
			<div class="tab-content">
				<div id="tab_settings" class="tab-pane fade in active">
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
						<div style="width:100%;">
							<p>
							<div class="row">
							<div class="col-sm-4">
								<label>Activate Content Optimizer</label>
								<p>Show the Content Optimization Widget in each post.</p>
							</div>
							<div class="col-sm-8">
								<p>
								<input class="form-control" type="checkbox" name="activate_post_widget" <?=isset($wsko_data['activate_post_widget']) && $wsko_data['activate_post_widget'] ? 'checked="checked"' : ''?>>
								Activate Content Optimizer
								</p>
							</div>
							</div>
							</p>
						</div>
					</div>
					
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
					<div style="width:100%">
						<p>
							<div class="row">
							<div class="col-sm-4">
								<label>Days measured in the Content Optimizer *</label>
								<p>The Content Optimizer will consider Keywords within this Time Range.</p>
							</div>
							<div class="col-sm-8">
								<input class="form-control" type="number" name="post_time" value="<?=isset($wsko_data['post_time']) && $wsko_data['post_time'] ? $wsko_data['post_time'] : ''?>" placeholder="Default: 27">
							</div>
							</div>
						
						</p>
					</div>
					</div>				
					
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
					<div style="width:100%">
						<label>Amount of Data Rows queried from the Search Console (ordered descending by 'clicks') *</label>
						
						<p>
						<div class="row">
						<div class="col-sm-4">
							Data Rows to save in cache daily
						</div>
						<div class="col-sm-8">
							<input class="form-control" type="number" name="keyword_limit_main" value="<?=isset($wsko_data['keyword_limit_main']) && $wsko_data['keyword_limit_main'] ? $wsko_data['keyword_limit_main'] : ''?>" min="0" max="5000" placeholder="Default: 5000 (Max.: 5000)">
						</div>
						</div>
						</p>
						
						<p>
						<div class="row">
						<div class="col-sm-4">
							Keywords to display in dashboard
							<p class="font-unimportant">If Caching is not activated, the maximum Value will be internally limited to 5000 Keywords</p>
						</div>
						<div class="col-sm-8">
							<input class="form-control" type="number" name="keyword_limit_dashboard" value="<?=isset($wsko_data['keyword_limit_dashboard']) && $wsko_data['keyword_limit_dashboard'] ? $wsko_data['keyword_limit_dashboard'] : ''?>" min="0" placeholder="Default: 5000">
						</div>
						</div>
						</p>
						
						<p>
						<div class="row">
						<div class="col-sm-4">
							Keywords to consider in Content Optimizer
						</div>
						<div class="col-sm-8">	
							<input class="form-control" type="number" name="keyword_limit_post" value="<?=isset($wsko_data['keyword_limit_post']) && $wsko_data['keyword_limit_post'] ? $wsko_data['keyword_limit_post'] : ''?>" min="0" max="5000" placeholder="Default: 100 (Max.: 5000)">
						</div>
						</div>
						</p>
					</div>
					</div>
					
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
					<div style="width:100%">
						<label>Caching Control</label>
						
						<?php
							$c_days = $wpdb->get_var('SELECT COUNT(*) FROM ' . WSKO_CACHE_TABLE);
							$c_rows = $wpdb->get_var('SELECT COUNT(*) FROM ' . WSKO_CACHE_ROWS_TABLE);
							$c_size = $wpdb->get_var('SELECT SUM((data_length + index_length)) AS size
													  FROM information_schema.TABLES
													  WHERE table_schema="' . $wpdb->dbname .'" 
													  AND (table_name="' . WSKO_CACHE_TABLE . '" OR table_name="' . WSKO_CACHE_ROWS_TABLE . '")');
							
							if ($c_size > 1000)
							{
								$c_size = $c_size / 1000;
								
								if ($c_size > 1000)
								{
									$c_size = round($c_size / 1000, 2) . ' MB';
								}
								else
									$c_size = round($c_size, 2) . ' KB';
							}
							else
								$c_size = round($c_size, 2) . ' B';
						?>
						
						
						<p>
						<div class="row">
						<div class="col-sm-4">
							Activate Caching (Recommended)
						</div>
						<div class="col-sm-8">
								<input class="form-control" type="checkbox" name="activate_caching" <?=isset($wsko_data['activate_caching']) && $wsko_data['activate_caching'] ? 'checked="checked"' : ''?>>
						</div>
						</div>
						</p>
						
						<p>
						<div class="row">
						<div class="col-sm-4">
						Clear Cache
						</div>
						<div class="col-sm-6">
							<button type="button" style="margin-right: 5px;" id="wsko_delete_recent_cache_btn" class="button" data-nonce="<?=wp_create_nonce('wsko-delete-recent-cache')?>"><i class="fa fa-pulse fa-spinner" style="display:none;"></i> Delete Recent Cache (last 90 Days)</button>
							<button type="button" id="wsko_delete_cache_btn" class="button" data-nonce="<?=wp_create_nonce('wsko-delete-cache')?>"><i class="fa fa-pulse fa-spinner" style="display:none;"></i> Delete Cache</button>
							<button type="button" class="button<?=$client ? ' wsko_update_cache_btn' : ''?>" style="margin-left: 5px;" href="#" <?=$client ? '' : 'disabled'?>><i class="fa fa-spinner fa-pulse" style="display:none;"></i> Update Cache manually</a>
						</div>
						</div>
						</p>
						
						<p>
						<div class="row">
						<div class="col-sm-4">
							Cache Limit (Days to keep data rows in cache) *
						</div>
						<div class="col-sm-8">
							<input class="form-control" type="number" name="cache_time_limit" value="<?=isset($wsko_data['cache_time_limit']) && $wsko_data['cache_time_limit'] ? $wsko_data['cache_time_limit'] : ''?>" min="90" placeholder="Default: Infinite (Min.: 90)">
						</div>
						</div>
						</p>
						
						<p>
						<div class="row">
							<div class="col-sm-4">
							Caching Information
							</div>
							<div class="col-sm-6">
								<div class="wsko_cache_settings"><i style="margin-right:5px;" class="fa fa-calendar" aria-hidden="true"></i> Cached Days: <strong><?=$c_days?></strong></div>
								<div class="wsko_cache_settings"><i style="margin-right:5px;" class="fa fa-list-ul" aria-hidden="true"></i> Data Rows: <strong><?=$c_rows?></strong></div>
								<div class="wsko_cache_settings"><i style="margin-right:5px;" class="fa fa-code" aria-hidden="true"></i> Size: <strong><?=$c_size?></strong></div>
							</div>
						</div>
						</p>
					</div>
					</div>
				</div>
				
				<div id="tab_advanced" class="tab-pane fade">
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
						<div style="width:100%;">
							<p>
							<div class="row">
							<div class="col-sm-12">
							<label>Reporting</label>
							</div>
							<div class="col-sm-4">
								
								<p>If you are facing errors, please activate error reporting to get more detailed reports of what is going wrong.</p>
							</div>
							<div class="col-sm-8">
								<p>
								<input class="form-control" type="checkbox" name="activate_log" <?=isset($wsko_data['activate_log']) && $wsko_data['activate_log'] ? 'checked="checked"' : ''?>>
								Activate Error Reporting
								</p>
							</div>
							</div>
							</p>
						</div>
					</div>
					
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
					<div class="row">
						<div class="col-sm-4">
									<label>Deactivate 3rd Party Code in your Admin Panel</label>
									<p>Warning: Expirienced Users only!</p>
						</div>
						<div class="col-sm-8">
									<label>
										<input class="form-control" type="checkbox" name="add_bootstrap" <?=isset($wsko_data['add_bootstrap']) && $wsko_data['add_bootstrap'] ? 'checked="checked"' : ''?>>
										Load Bootstrap in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_fontawesome" <?=isset($wsko_data['add_fontawesome']) && $wsko_data['add_fontawesome'] ? 'checked="checked"' : ''?>>
										Load Font Awesome in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_moment" <?=isset($wsko_data['add_moment']) && $wsko_data['add_moment'] ? 'checked="checked"' : ''?>>
										Load Moment in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_bootstrap_datepicker" <?=isset($wsko_data['add_bootstrap_datepicker']) && $wsko_data['add_bootstrap_datepicker'] ? 'checked="checked"' : ''?>>
										Load Bootstrap Datepicker in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_datatables" <?=isset($wsko_data['add_datatables']) && $wsko_data['add_datatables'] ? 'checked="checked"' : ''?>>
										Load Datatables in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_google_chart" <?=isset($wsko_data['add_google_chart']) && $wsko_data['add_google_chart'] ? 'checked="checked"' : ''?>>
										Load Google Charts in Admin Panel
									</label>
									<br/>
									<label>
										<input class="form-control" type="checkbox" name="add_icheck" <?=isset($wsko_data['add_icheck']) && $wsko_data['add_icheck'] ? 'checked="checked"' : ''?>>
										Load iCheck in Admin Panel
									</label>
									<br/>
							</div>
							</div>
					</div>
					
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
						<div style="width:100%;">
							<p>
							<div class="row">
							<div class="col-sm-12">
							<label>Uninstall Settings</label>
							</div>
							<div class="col-sm-4">
								
								<p>Remove datatable trace and delete cache when uninstalling plugin.</p>
							</div>
							<div class="col-sm-8">
								<p>
								<input class="form-control" type="checkbox" name="clean_uninstall" <?=isset($wsko_data['clean_uninstall']) && $wsko_data['clean_uninstall'] ? 'checked="checked"' : ''?>>
								Clear Keyword Cache when uninstalling
								</p>
							</div>
							</div>
							</p>
						</div>
					</div>
				</div>
				
				<div id="tab_permissions" class="tab-pane fade">
					<div style="padding:15px; border-bottom:solid 1px #ddd;">
						<div style="width:100%;">
							<p>
							<div class="row">
							<div class="col-sm-12">
							<label>Additional Permissions</label>
							</div>
							<div class="col-sm-4">
								
								<p>Add user roles to gain access to the plugin functionality. Only Admins are able to edit the settings and interact with the cache.</p>
							</div>
							<div class="col-sm-8">
								<p>
								<div class="row" style="overflow-y:auto;max-height:100px;">
								<?php
								 global $wp_roles;
								 $roles = $wp_roles->get_names(); 
								 foreach ($roles as $role_key => $role)
								 { ?>
									<div class="col-md-3">
									<label><input class="form-control" type="checkbox" name="permission_roles[]" value="<?=$role_key?>" <?=$role_key == 'administrator' ? 'disabled' : (isset($wsko_data['permission_roles']) && in_array($role_key, $wsko_data['permission_roles']) ? 'checked="checked"' : '')?>>
									<?=$role?></label><?=$role_key == 'administrator' ? ' <i class="fa fa-exclamation-triangle" data-toggle="tooltip" data-placement="bottom" title="Administrators allways have full permissions"></i>' : ''?>
									</div>
								 <?php } ?>
								 </div>
								</p>
							</div>
							</div>
							</p>
						</div>
					</div>
				</div>
			</div>
			
			<button style="margin-top:15px;" class="button button-primary" type="submit"><i class="fa fa-spin fa-spinner" style="display:none;"></i> Save Changes</button> <p class="font-unimportant" style="margin-top:15px;float:right;">* Delete values and save to reset them to default</p>
		</form>
	</div>
	</div>
</div>