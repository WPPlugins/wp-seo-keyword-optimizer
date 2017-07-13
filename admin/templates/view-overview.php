<?php
if (!defined('ABSPATH')) exit;

global $wpdb;

$is_admin_view = isset($is_admin_view) && $is_admin_view;

$charts_width = 495;
$charts_height = 250;
$charts_area_width = 80;
$charts_area_height = 50;

$kw_dist = array();
$kw_dist_ref = array();
$kw_count = 0;
$kw_count_ref = 0;
$kw_clicks = 0;
$kw_clicks_ref = 0;
$kw_imp = 0;
$kw_imp_ref = 0;
$has_ref = false;

$kw_dist_count = 0;
if (!empty($kw_rows))
{
	foreach ($kw_rows as $row)
	{
		$kw_count++;
		
		if ($row->position < 100)
		{
			$pos = floor($row->position / 10) + 1;
			if (isset($kw_dist[(string)$pos]))
				$kw_dist[(string)$pos]++;
			else
				$kw_dist[(string)$pos] = 1;	
			
			if ($pos <= 1)
				$kw_dist_count++;
		}
	}
	ksort($kw_dist);
}

if (!empty($date_rows))
{
	foreach ($date_rows as $row)
	{
		$kw_clicks += $row->clicks;
		$kw_imp += $row->impressions;
	}
}

$kw_dist_count_ref = 0;
if (!empty($kw_rows_ref))
{
	if (!empty($date_rows_ref))
		$has_ref = true;
	
	foreach ($kw_rows_ref as $row)
	{
		$kw_count_ref++;
		
		if ($row->position < 100)
		{
			$pos = floor($row->position / 10) + 1;
			if (isset($kw_dist_ref[(string)$pos]))
				$kw_dist_ref[(string)$pos]++;
			else
				$kw_dist_ref[(string)$pos] = 1;	
			
			if ($pos <= 1)
			$kw_dist_count_ref++;
		}
	}
	ksort($kw_dist_ref);
}

if (!empty($date_rows_ref))
{
	foreach ($date_rows_ref as $row)
	{
		$kw_clicks_ref += $row->clicks;
		$kw_imp_ref += $row->impressions;
	}
}

$kw_count_ref_perc = $kw_count > 0 ? round((1 - $kw_count_ref / $kw_count) * 100, 2) : 0;
$kw_dist_count_ref_perc = $kw_dist_count > 0 ? round((1 - $kw_dist_count_ref / $kw_dist_count) * 100, 2) : 0;
$kw_clicks_ref_perc = $kw_clicks > 0 ? round((1 - $kw_clicks_ref / $kw_clicks) * 100, 2) : 0;
$kw_imp_ref_perc = $kw_imp > 0 ? round((1 - $kw_imp_ref / $kw_imp) * 100, 2) : 0;
?>
	<div class="row">
		<div class="col-md-12">
			<div class="wsko_box_wrapper" style="margin-top:0px;">
			<p class="wsko_chart_label">Overview</p>
				<div class="row" style="margin-top:20px;">
					<div class="col-sm-3 wsko_overview_wrapper">
					<div class="wsko_overview_background">
						<p class="wsko_chart_label wsko_overview_label">Total Keywords</p>
						<?php if ($is_admin_view)
						{ ?>
							<i class="fa fa-spin fa-spinner fa-2x"></i>
							<?php
						}
						else 
						{ ?>
							<span class="wsko_overview_data"><?=$kw_count == WSKO_KEYWORD_LIMIT_DASHBOARD ? '>' : ''?><?=$kw_count?> <?=$kw_count == WSKO_KEYWORD_LIMIT_DASHBOARD ? '<a target="_blank" href="' . admin_url('admin.php?page=wsko_settings_view') . '" title="Keyword limit is reached. Click to increase your limit in the plugin settings." data-toggle="tooltip"><i class="fa fa-caret-square-o-up"></i></a>': ''?></span>
							<a href="#" data-toggle="tooltip" title="<?=$has_ref ? $kw_count_ref . ' Keywords from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_weekly_progress <?=$has_ref ? ($kw_count_ref_perc < 0 ? 'wsko_red' : 'wsko_green') : 'wsko_gray'?>"><?=($has_ref && ($kw_count_ref_perc > 0)) ? '+' : ''?><?=$has_ref ? $kw_count_ref_perc : '-' ?> %</span></a>
						<?php } ?>
					</div>	
					</div>
					
					<div class="col-sm-3 wsko_overview_wrapper">
					<div class="wsko_overview_background">
						<p class="wsko_chart_label wsko_overview_label">Keywords in Top 10</p>
						<?php if ($is_admin_view)
						{ ?>
							<i class="fa fa-spin fa-spinner fa-2x"></i>
							<?php
						}
						else 
						{ ?>
							<span class="wsko_overview_data"><?=$kw_dist_count?></span>	
							<a href="#" data-toggle="tooltip" title="<?=$has_ref ? $kw_dist_count_ref . ' Keywords from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_weekly_progress <?=$has_ref ? ($kw_dist_count_ref_perc < 0 ? 'wsko_red' : 'wsko_green') : 'wsko_gray'?>"><?=($has_ref && ($kw_dist_count_ref_perc > 0)) ? '+' : ''?><?=$has_ref ? $kw_dist_count_ref_perc : '-' ?> %</span></a>
						<?php } ?>
					</div>		
					</div>
					
					<div class="col-sm-3 wsko_overview_wrapper">
					<div class="wsko_overview_background">
						<p class="wsko_chart_label wsko_overview_label">Clicks <a href="#" data-toggle="tooltip" title="Clicks during the selected time range"><i class="fa fa-info-circle wsko_info" aria-hidden="true"></i></a></p>
						<?php if ($is_admin_view)
						{ ?>
							<i class="fa fa-spin fa-spinner fa-2x"></i>
							<?php
						}
						else 
						{ ?>
							<span class="wsko_overview_data"><?=$kw_clicks?></span>
							<a href="#" data-toggle="tooltip" title="<?=$has_ref ? $kw_clicks_ref . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_weekly_progress <?=$has_ref ? ($kw_clicks_ref_perc < 0 ? 'wsko_red' : 'wsko_green') : 'wsko_gray'?>"><?=($has_ref && ($kw_clicks_ref_perc > 0)) ? '+' : ''?><?=$has_ref ? $kw_clicks_ref_perc : '-' ?> %</span></a>
						<?php } ?>
					</div>	
					</div>
					
					<div class="col-sm-3 wsko_overview_wrapper">
					<div class="wsko_overview_background">
						<p class="wsko_chart_label wsko_overview_label">Impressions <a href="#" data-toggle="tooltip" title="Impressions during the selected time range"><i class="fa fa-info-circle wsko_info" aria-hidden="true"></i></a></p>
						<?php if ($is_admin_view)
						{ ?>
							<i class="fa fa-spin fa-spinner fa-2x"></i>
							<?php
						}
						else 
						{ ?>
							<span class="wsko_overview_data"><?=$kw_imp?></span>
							<a href="#" data-toggle="tooltip" title="<?=$has_ref ? $kw_imp_ref . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_weekly_progress <?=$has_ref ? ($kw_imp_ref_perc < 0 ? 'wsko_red' : 'wsko_green') : 'wsko_gray'?>"><?=($has_ref && ($kw_imp_ref_perc > 0)) ? '+' : ''?><?=$has_ref ? $kw_imp_ref_perc : '-' ?> %</span></a>
						<?php } ?>
					</div>	
					</div>
				</div>
			</div>
		</div>
		
		<?php /*<div class="col-md-6">
			<div id="history_keyword" style="margin:15px;"></div>
		</div>*/ ?>
		
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Ranking Keywords History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if ($caching_active)
			{
				if (isset($date_rows) && isset($kw_dist))
				{
					?>
					<div id="history_keywords" style="margin:15px;">
					</div>
					<?php
				}
				else
				{
					if ($client)
					{
						include($wsko_path . 'templates/template-no-cache.php');
					}
					else
					{
						include($wsko_path . 'templates/template-no-cache-gafail.php');
					}
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-cache-active.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Ranking Pages History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if ($caching_active)
			{
				if (isset($date_rows) && isset($kw_dist))
				{
					?>
					<div id="history_pages" style="margin:15px;">
					</div>
					<?php
				}
				else
				{
					if ($client)
					{
						include($wsko_path . 'templates/template-no-cache.php');
					}
					else
					{
						include($wsko_path . 'templates/template-no-cache-gafail.php');
					}
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-cache-active.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Click History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if (isset($date_rows) && isset($kw_dist))
			{
				?>
				<div id="history_clicks" style="margin:15px;">
				</div>
				<?php
			}
			else if ($caching_active)
			{
				if ($client)
				{
					include($wsko_path . 'templates/template-no-cache.php');
				}
				else
				{
					include($wsko_path . 'templates/template-no-cache-gafail.php');
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-data.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Position History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if (isset($date_rows) && isset($kw_dist))
			{
				?>
				<div id="history_position" style="margin:15px;">
				</div>
				<?php
			}
			else if ($caching_active)
			{
				if ($client)
				{
					include($wsko_path . 'templates/template-no-cache.php');
				}
				else
				{
					include($wsko_path . 'templates/template-no-cache-gafail.php');
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-data.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Impressions History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if (isset($date_rows) && isset($kw_dist))
			{
				?>
				<div id="history_impressions" style="margin:15px;">
				</div>
				<?php
			}
			else if ($caching_active)
			{
				if ($client)
				{
					include($wsko_path . 'templates/template-no-cache.php');
				}
				else
				{
					include($wsko_path . 'templates/template-no-cache-gafail.php');
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-data.php');
			}
			?>
			</div>
		</div>
		<div class="col-md-6">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Ranking Distribution</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if (isset($date_rows) && isset($kw_dist))
			{
				?>
				<div id="history_ctr" style="margin:15px;">
				</div>
				<?php
			}
			else if ($caching_active)
			{
				if ($client)
				{
					include($wsko_path . 'templates/template-no-cache.php');
				}
				else
				{
					include($wsko_path . 'templates/template-no-cache-gafail.php');
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-data.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-12">
			<div class="wsko_box_wrapper wsko_history_charts">
			<p class="wsko_chart_label">Ranking Distribution History</p>
			<?php
			if ($is_admin_view)
			{ ?>
				<i class="fa fa-spin fa-spinner fa-2x"></i>
				<?php
			}
			else if ($caching_active)
			{
				if (isset($date_rows) && isset($kw_dist))
				{
					?>
					<div id="history_rankings" style="margin:15px;">
					</div>
					<?php
				}
				else
				{
					if ($client)
					{
						include($wsko_path . 'templates/template-no-cache.php');
					}
					else
					{
						include($wsko_path . 'templates/template-no-cache-gafail.php');
					}
				}
			}
			else
			{
				include($wsko_path . 'templates/template-no-cache-active.php');
			}
			?>
			</div>
		</div>
		
		<div class="col-md-12">
		<div class="wsko_box_wrapper">
			<p class="wsko_chart_label" style="text-align:left;">Top 5 Keywords</p>
			<div class="wsko_table_simple">
				<?php
				if ($is_admin_view)
				{ ?>
					<i class="fa fa-spin fa-spinner fa-2x"></i>
					<?php
				}
				else 
				{ ?>
					<table class="table table-striped table-bordered wsko_tables table-condensed" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>Keyword</th>
								<th>Clicks</th>
								<th>Position</th>
								<th>Impressions</th>
								<th>CTR</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Keyword</th>
								<th>Clicks</th>
								<th>Position</th>
								<th>Impressions</th>
								<th>CTR</th>
							</tr>
						</tfoot>
						<tbody>	
					
						<?php
							$count = 0;
							if (isset($kw_rows))
							{
								foreach ($kw_rows as $key => $row)
								{
									$count++;
									if ($count > 5)
										break;
									$kw = $row->keys[0];
									
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
										$ref_position = $ref_row->position - $row->position;//$row->position != 0 ? -round((1 - $ref_row->position / $row->position) * 100, 2) : 0;
										$ref_impressions = $row->impressions != 0 ? round((1 - $ref_row->impressions / $row->impressions) * 100, 2) : 0;
										$ref_ctr = $row->ctr != 0 ? round((1 - $ref_row->ctr / $row->ctr) * 100, 2) : 0;
									}
									?><tr>
										<td class="wsko_table_col1"><?=$kw?></td>
										<?php /* <td><span style="min-width:30px; float:left;"><?=$row->clicks?> </span> (<?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %)</td> 
										<td><span style="min-width:30px; float:left;"><?=round($row->position, 2)?> </span> (<?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?> %)</td>
										<td><span style="min-width:30px; float:left;"><?=$row->impressions?> </span> (<?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %)</td>
										<td><span style="min-width:30px; float:left;"><?=round($row->ctr, 2)?> % </span> (<?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %)</td>		
										*/ ?>
									
										<td data-order="<?=$row->clicks?>"><span style="min-width:30px; float:left;"><?=$row->clicks?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_single_progress <?=$ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %</span></a></td>
										<td data-order="<?=$row->position?>"><span style="min-width:30px; float:left;"><?=round($row->position, 2)?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? 'Position ' . round($ref_row->position, 2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?></span></a></td>
										<td data-order="<?=$row->impressions?>"><span style="min-width:30px; float:left;"><?=$row->impressions?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %</span></a></td>
										<td data-order="<?=$row->ctr?>"><span style="min-width:30px; float:left;"><?=round($row->ctr, 2)?> % </span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ?  round($ref_row->ctr ,2) . ' % from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_ctr != 0 ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %</span></a></td>
											
									</tr><?php
								}
							}
							?>
						
						</tbody>
					</table>
				<?php }?>
			</div>	
		</div>
		</div>
		
		<div class="col-md-12">
		<div class="wsko_box_wrapper">
			<p class="wsko_chart_label" style="text-align:left;">Top 5 Pages</p>
			<div class="wsko_table_simple">
				<?php
				if ($is_admin_view)
				{ ?>
					<i class="fa fa-spin fa-spinner fa-2x"></i>
					<?php
				}
				else 
				{ ?>
					<table class="table table-striped table-bordered wsko_tables table-condensed" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th>URL</th>
								<th>Clicks</th>
								<th>Position</th>
								<th>Impressions</th>
								<th>CTR</th>
								<th></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>URL</th>
								<th>Clicks</th>
								<th>Position</th>
								<th>Impressions</th>
								<th>CTR</th>
								<th></th>
							</tr>
						</tfoot>
						<tbody>
					
				
						<?php
							$count = 0;
							if ($is_admin_view)
							{ ?>
								<i class="fa fa-spin fa-spinner fa-2x"></i>
								<?php
							}
							else if (isset($page_rows))
							{
								foreach ($page_rows as $row)
								{
									$count++;
									if ($count > 5)
										break;
									$url = $row->keys[0];
									$res = wsko_url_get_title($url);
									
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
										$ref_position = $ref_row->position - $row->position;//$row->position != 0 ? -round((1 - $ref_row->position / $row->position) * 100, 2) : 0;
										$ref_impressions = $row->impressions != 0 ? round((1 - $ref_row->impressions / $row->impressions) * 100, 2) : 0;
										$ref_ctr = $row->ctr != 0 ? round((1 - $ref_row->ctr / $row->ctr) * 100, 2) : 0;
									}
									?>
									
									<tr>
										
										<td class="wsko_table_col1"><div class="wsko_nowrap" title="<?=$url?>"><?=$res->title?></br><span class="font-unimportant"><?=$url?></span></div></td>
										
										<?php /* <td><?=$row->clicks?> (<?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %)</td>
										<td><?=round($row->position, 2)?> (<?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?> %)</td>
										<td><?=$row->impressions?> (<?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %)</td> 
										<td style="min-width:80px;"><?=round($row->ctr, 2)?> % (<?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %)</td>							
										*/ ?>
										
										<td data-order="<?=$row->clicks?>"><span style="min-width:30px; float:left;"><?=$row->clicks?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_clicks != 0 ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %</span></a></td>
										<td data-order="<?=$row->position?>"><span style="min-width:30px; float:left;"><?=round($row->position, 2)?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? 'Position ' . round($ref_row->position, 2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_position != 0 ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?></span></a></td>
										<td data-order="<?=$row->impressions?>"><span style="min-width:30px; float:left;"><?=$row->impressions?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_impressions != 0 ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %</span></a></td> 
										<td data-order="<?=$row->ctr?>" style="min-width:80px;"><span style="min-width:30px; float:left;"><?=round($row->ctr, 2)?> % </span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ?  round($ref_row->ctr ,2) . ' % from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row && $ref_ctr != 0 ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %</span></a></td>		
												
										<td style="min-width:80px; text-align:left; padding-right:10px;">
											<a class="wsko-show-keywords unloaded wsko_details_button" href="#" data-url="<?=$url?>" data-nonce="<?=wp_create_nonce('wsko-show-keywords')?>"><i class="fa fa-eye" title="Show Keywords" data-toggle="tooltip"></i></a>
											<a class="wsko_details_button" target="_blank" href="<?=$url?>"><i class="fa fa-link" title="View Page" data-toggle="tooltip"></i></a>
											<?php if ($res->type == 'post')
											{
												?><a class="wsko_details_button" target="_blank" href="<?=get_edit_post_link($res->post_id)?>"><i class="fa fa-pencil" title="Edit Post" data-toggle="tooltip"></i></a><?php
											} ?>
											<div class="wsko-kd-cache" style="display:none;"></div>
										</td>
									</tr>
									<?php
								}
							}
						?>
						</tbody>
					</table>
				<?php } ?>
			</div>	
		</div>
		</div>
	</div>
	<?php
	if (!$is_admin_view && isset($date_rows) && isset($kw_dist))
	{
		foreach ($date_rows as $row)
		{
			$row->t_query = $wpdb->get_results(
				"
					SELECT table_cr.position AS position
					FROM " . WSKO_CACHE_ROWS_TABLE . " AS table_cr
					INNER JOIN " . WSKO_CACHE_TABLE . " AS table_c
					ON table_c.id = table_cr.cache_id
					AND table_cr.type = 0
					WHERE date(table_c.time)='" . $row->keys[0] . "'
					GROUP BY table_cr.keyval
					LIMIT " . WSKO_KEYWORD_LIMIT_DASHBOARD);
			$row->t_page = $wpdb->get_results(
				"
					SELECT table_cr.position AS position
					FROM " . WSKO_CACHE_ROWS_TABLE . " AS table_cr
					INNER JOIN " . WSKO_CACHE_TABLE . " AS table_c
					ON table_c.id = table_cr.cache_id
					AND table_cr.type = 1
					WHERE date(table_c.time)='" . $row->keys[0] . "'
					GROUP BY table_cr.keyval
					LIMIT " . WSKO_KEYWORD_LIMIT_DASHBOARD);
		}
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($)
		{
			google.charts.load('current', {'packages':['corechart']});
			google.charts.setOnLoadCallback(wsko_drawCharts);
		});
		  function wsko_drawCharts() {
			
			var options2 = {
			  width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data2 = google.visualization.arrayToDataTable([
			  ['Date', 'Clicks'],
				<?php
				foreach ($date_rows as $row)
				{
					echo '["' . $row->keys[0] . '", '. $row->clicks . '], ';
				} ?>
			]);
			
			var options3 = {
			  width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data3 = google.visualization.arrayToDataTable([
			  ['Date', 'Position'],
				<?php
				foreach ($date_rows as $row)
				{
					echo '["' . $row->keys[0] . '", '. $row->position . '], ';
				} ?>
			]);
			
			var options4 = {
			  width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data4 = google.visualization.arrayToDataTable([
			  ['Date', 'Impressions'],
				<?php
				foreach ($date_rows as $row)
				{
					echo '["' . $row->keys[0] . '", '. $row->impressions . '], ';
				} ?>
			]);
			
			var options5 = {
				width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
				hAxis: {title: 'SERP',  titleTextStyle: {color: '#333'}},
				bar: {groupWidth: "95%"},
				legend: { position: "none" },
			};
			var data5 = google.visualization.arrayToDataTable([
			  ['Position', 'Count' ],
				<?php
				foreach ($kw_dist as $pos => $count)
				{
					echo '["' . $pos . '", '. $count . ' ], ';
				}
				?>
			]);
			
			var options6 = {
				width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data6 = google.visualization.arrayToDataTable([
			  ['Date', 'Keywords'],
				<?php
				foreach ($date_rows as $row)
				{
					$kw_c = count($row->t_query);
					echo '["' . $row->keys[0] . '", ' . $kw_c . '], ';
				} ?>
			]);
			
			var options7 = {
				width: <?=$charts_width?>,
				height: <?=$charts_height?>,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "<?=$charts_area_height?>%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data7 = google.visualization.arrayToDataTable([
			  ['Date', 'Pages'],
				<?php
				foreach ($date_rows as $row)
				{
					$kw_c = count($row->t_page);
					echo '["' . $row->keys[0] . '", ' . $kw_c . '], ';
				} ?>
			]);
			
			var options8 = {
				width: 1100,
				height: 500,
				chartArea: {  width: "<?=$charts_area_width?>%", height: "70%" },
			  hAxis: {title: 'Date',  titleTextStyle: {color: '#333'}},
			  vAxis: {minValue: 0},
			  legend: { position: 'top' }
			};
			var data8 = google.visualization.arrayToDataTable([
			  ['Date', 'SERP 1', 'SERP 2', 'SERP 3', 'SERP 4', 'SERP 5', 'SERP 6', 'SERP 7', 'SERP 8', 'SERP 9', 'SERP 10'],
				<?php
				foreach ($date_rows as $row)
				{
					//$kw_r = json_decode($wpdb->get_var('SELECT query FROM ' . $cache_table . ' WHERE date(time)="' . $row->keys[0] . '"'));
					$kw_d = array();
					foreach ($row->t_query as $row2)
					{
						$kw_count++;
						
						$pos = floor($row2->position / 10) + 1;
						if ($pos <= 10)
						{
							if (isset($kw_d[(string)$pos]))
								$kw_d[(string)$pos]++;
							else
								$kw_d[(string)$pos] = 1;	
						}
					}
					ksort($kw_d);

					echo '["' . $row->keys[0] . '", ' .
					(isset($kw_d['1']) ? $kw_d['1'] : '0') . ', ' . 
					(isset($kw_d['2']) ? $kw_d['2'] : '0') . ', ' .
					(isset($kw_d['3']) ? $kw_d['3'] : '0') . ', ' .
					(isset($kw_d['4']) ? $kw_d['4'] : '0') . ', ' .
					(isset($kw_d['5']) ? $kw_d['5'] : '0') . ', ' .
					(isset($kw_d['6']) ? $kw_d['6'] : '0') . ', ' .
					(isset($kw_d['7']) ? $kw_d['7'] : '0') . ', ' .
					(isset($kw_d['8']) ? $kw_d['8'] : '0') . ', ' .
					(isset($kw_d['9']) ? $kw_d['9'] : '0') . ', ' .
					(isset($kw_d['10']) ? $kw_d['10'] : '0') . '], ';
				} ?>
			]);

			
			var chart2 = new google.visualization.AreaChart(document.getElementById('history_clicks'));
			chart2.draw(data2, options2);
			
			var chart3 = new google.visualization.AreaChart(document.getElementById('history_position'));
			chart3.draw(data3, options3);
			
			var chart4 = new google.visualization.AreaChart(document.getElementById('history_impressions'));
			chart4.draw(data4, options4);
			
			var chart5 = new google.visualization.ColumnChart(document.getElementById("history_ctr"));
			chart5.draw(data5, options5);
			
			var chart6 = new google.visualization.AreaChart(document.getElementById("history_keywords"));
			chart6.draw(data6, options6);
			
			var chart7 = new google.visualization.AreaChart(document.getElementById("history_pages"));
			chart7.draw(data7, options7);
			
			var chart8 = new google.visualization.LineChart(document.getElementById("history_rankings"));
			chart8.draw(data8, options8);
			
		  }
		</script>
		<?php
	}
	?>
