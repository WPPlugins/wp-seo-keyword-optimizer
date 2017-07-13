<?php
if (!defined('ABSPATH')) exit;

$kw_data = $kw_obj['data'];
$focus = $kw_obj['focus'];

if ($group)
{
	$kws = $kw_obj['keywords'];
	
	$clicks = $kw_obj['clicks'];
	$position = round($kw_obj['position'], 2);
	$impressions = $kw_obj['impressions'];
	$ctr = $kw_obj['ctr'];
}
else
{
	$clicks = $kw_obj['ga_row'] ? $kw_obj['ga_row']->clicks : '-';
	$position = $kw_obj['ga_row'] ? round($kw_obj['ga_row']->position, 2) : '-';
	$impressions = $kw_obj['ga_row'] ? $kw_obj['ga_row']->impressions : '-';
	$ctr = $kw_obj['ga_row'] ? $kw_obj['ga_row']->ctr : '-';
}
?>
<li>
	<div class="row">
				<div class="col-md-12">	
				<div class="wsko_keyword_progress" style="box-shadow: 0 1px 1px rgba(0,0,0,0.2);">
								<div class="row" style="margin-bottom:5px;">
										<div class="col-sm-10"  data-toggle="tooltip" data-html="true" title="<?=$focus ? '<strong>Focused</strong>' : ''?> <?=($focus && $group) ? '<strong>,</strong>' : ''?> <?=$group ? '<strong>Grouped</strong>' : ''?> <strong>Keyword:</strong> </br> <?=$kw?>" style="background-color: #337ab7; color:#fff; left:5px; padding: 4px; white-space: nowrap; overflow: hidden; min-height: 28px;">
											<?php
											if ($focus)
											{
												?>
												<span data-toggle="tooltip" title="" class="fa-stack fa-2x">
												  <i style="transform: scale(0.9);" class="fa fa-circle fa-stack-1x"></i>
												  <strong style="color:#337ab7; font-size:13px;" class="fa-stack-1x">F</strong>
												</span>
												
												<?php
											}
											if ($group)
											{
												?>
												
												<span data-toggle="tooltip" title="" class="fa-stack fa-2x">
												  <i style="transform: scale(0.9);" class="fa fa-circle fa-stack-1x"></i>
												  <strong style="color:#337ab7; font-size:13px;" class="fa-stack-1x">G</strong>
												</span>
												<?php
											}
											?>
											<span style="font-weight:bold;"><?=$kw?></span>
										</div>
										<div class="col-sm-2" style="background-color: #337ab7; right:5px; padding: 5px; min-height: 28px;">
											<a class="wsko-toggle" style="float: right; padding-right: 5px; color: #fff;" href="#wsko_post_keywords_details_<?=$kwid?>" data-toggle="collapse"><i class="fa fa-chevron-down" aria-hidden="true"></i></a>
										</div>
									
								</div>	
							
					<div class="row">
							<div class="col-md-6">
									<span class="<?=round($kw_data->efficiency, 1) > 50 ? 'wsko_green_font' : 'wsko_red_font'?>">
										<strong class="wsko-lazy" data-target="data" data-obj="<?=$key?>" data-attr="eff"><?=round($kw_data->efficiency, 1)?>%</strong>
									</span>
									<span style="padding-left:5px;">
												<?=count($kw_data->notes)?> Issues
									</span>
							</div>
							<div class="col-md-6" style="text-align: right; background-color:#fff; right:5px; padding-left: 0px; padding-right: 8px;">
									<span data-toggle="tooltip" title="Keyword Density">KD: <strong><?=round($kw_data->density, 2)?>% (<?=$kw_data->keyword_count?>)</strong></span>

							</div>
					</div>
					<hr class="wsko_hr" style="margin: 8px 0px 3px 0px !important;">
					<div class="row" style="padding:0px 10px;">
							<div class="col-md-4" style="padding:5px;">
									<i class="fa fa-mouse-pointer wsko_icon" data-toggle="tooltip" title="Clicks" aria-hidden="true"></i> <?=$clicks?>
							</div>
							<div class="col-md-4" style="padding:5px;">
									<i class="fa fa-eye wsko_icon" data-toggle="tooltip" title="Impressions" aria-hidden="true"></i> <?=$impressions?>
							</div>
							<div class="col-md-4" style="padding:5px;">
									<i class="fa fa-list wsko_icon" data-toggle="tooltip" title="Position in SERP" aria-hidden="true"></i> <?=$position?>
							</div>
					</div>
				</div>	
				</div>
			
				<div class="col-md-12">
					<div id="wsko_post_keywords_details_<?=$kwid?>" class="collapse" data-parent="#wsko_post_keywords_details_list">
						<?php
						if ($group)
						{
							?>
							<div class="wsko_group_details">
								
							<label class="wsko_label">Grouped Keywords (<?=count($kws)?>)</label>
							<a class="wsko-toggle" data-toggle="collapse" data-target="#show_kw_<?=$kwid?>"><i style="float:right;color: #444;padding-top: 2px;" class="fa fa-chevron-down" aria-hidden="true"></i></a>

							<div id="show_kw_<?=$kwid?>" class="collapse">
							<ul>
								<?php
									foreach ($kws as $key => $kw)
									{
										$wsko_kw_clicks = $kw["ga_row"] ? $kw["ga_row"]->clicks : "-";
										$wsko_kw_impr = $kw["ga_row"] ? $kw["ga_row"]->impressions : "-";
										$wsko_kw_pos = $kw["ga_row"] ? $kw["ga_row"]->position : "-";
										?>
										
										<li>
											<div class="row details_grouped_keyword">
												<div class="col-sm-2">
														<span data-toggle="tooltip" data-html="true" title="Clicks: <?=$kw['ga_row'] ? $kw['ga_row']->clicks : '-';?> </br> Impressions: <?=$kw['ga_row'] ? $kw['ga_row']->impressions : '-'?> </br> Position: <?=round($kw['ga_row'] ? $kw['ga_row']->position : '-', 2)?>">
																<i class="fa fa-info-circle" aria-hidden="true"></i>
														</span>
												</div>
												<div class="col-sm-6" title="<?=$key?>" style="white-space:nowrap; overflow: hidden; padding-left:5px;">
														<span><?=$key?></span>
												</div>	
												<div class="col-sm-4">	
															<span data-toggle="tooltip" title="Keyword Density"><strong><?=round($kw['data']->density, 2)?>%</strong></span>
												</div>
												
											</div>	
										</li>
										<?php
									}
								?>
							</ul>
							</div>
							</div>
							<?php
						}
						?>
						<ul>
							<?php
							if (!empty($kw_data->notes))
							{
								foreach ($kw_data->notes as $note)
								{
									?><li class="wsko_criteria bs-callout bs-callout-<?=isset($note['warning']) && $note['warning'] ? 'warning' : 'danger'?>"><?php /* <?=$note['type'] == '2' ? '
																																						<span data-toggle="tooltip" title="" class="fa-stack fa-2x">
																																									  <i title="This Criteria is for focused Keywords" data-toggle="tooltip" style="background-color:color:#337ab7; transform: scale(0.8);" class="fa fa-circle fa-stack-1x"></i>
																																									  <strong style="color:#fff; font-size:13px;" class="fa-stack-1x">F</strong>
																																									</span>
																																						' : ''?> */ ?> <?=$note['msg']?></li><?php
								}
							}
							else
							{
								?><li class="bs-callout bs-callout-success">No Warnings/Errors. Nice.</li><?php
							}
							?>
						</ul>
					</div>
				</div>
	</div>
</li>