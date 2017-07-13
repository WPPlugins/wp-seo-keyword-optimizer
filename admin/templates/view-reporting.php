<?php
global $wsko_data;

/** /wsko_report_error('error', 'Test Error', 'Content.', 'Additional.');
wsko_report_error('exception', 'Test Exception', 'Content.', 'Additional.');
wsko_report_error('warning', 'Test Warning', 'Content.', 'Additional.');
wsko_report_error('info', 'Test Info', 'Content.', 'Additional.');/**/
?>
<div class="row">
	<div class="col-md-12">
	<div class="wsko_box_wrapper">
	<?php
		if ($wsko_data['activate_log'])
		{
			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => WSKO_POST_TYPE_ERROR,
				'post_status'      => 'any',
				'suppress_filters' => true 
			);
			
			$msgs = get_posts($args);
			if (!empty($msgs))
			{
				?>
				<button type="button" id="wsko_clear_log" class="button pull-right" data-nonce="<?=wp_create_nonce('wsko-delete-log-reports')?>"><i class="fa fa-pulse fa-spinner" style="display:none;"></i> Clear reports</button>
				<a class="btn btn-link wsko-give-feedback pull-right" href="#"><i style="padding-right:5px;" class="fa fa-comments" aria-hidden="true"></i> Feedback/Error Report</a>
				<p class="wsko_chart_label">Error Reporting</p>

				
				<div class="panel-group" id="tab_errors_group" style="margin-top: 40px;">
				<?php
					$first = true;
					foreach ($msgs as $msg)
					{
						$type = '';
						switch ($msg->post_status)
						{
							case 'error':
								$type = '<i class="fa fa-times-circle" style="color:red"></i>';
								break;
							
							case 'warning':
								$type = '<i class="fa fa-exclamation-triangle" style="color:Gold"></i>';
								break;
							
							case 'info':
								$type = '<i class="fa fa-info-circle" style="color:LightSkyBlue"></i>';
								break;
						}
						?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
									<a data-toggle="collapse" data-parent="#tab_errors_group" href="#log_report_<?=$msg->ID?>"><?=$type?> <b><?=$msg->post_title?></b> <p style="float:right"><?=get_the_date('d.m.Y H:i:s', $msg->ID)?></p></a>
								</h4>
							</div>
							<div id="log_report_<?=$msg->ID?>" class="panel-collapse collapse <?=$first ? 'in' : ''?>">
								<div class="panel-body">
									<?=$msg->post_content?>
								</div>
							</div>
						</div>
						<?php
						$first = false;
					}
				?>
				</div><?php
			}
			else
			{
				?><div style="text-align:center;height:100px;width:100%;vertical-align: middle;line-height: 100px;font-weight:bold;font-size:14px">No reports found. You are good to go!</div><?php
			}
		}
		else
		{
			?><div style="text-align:center;height:100px;width:100%;vertical-align: middle;line-height: 100px;font-weight:bold;font-size:14px">Error reporting is disabled. You can activate it on the <a target="_blank" href="<?=admin_url('admin.php?page=wsko_settings_view')?>">Settings Page</a> if you are facing any errors.</div><?php
		} ?>
	</div>
	</div>
</div>