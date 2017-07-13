<?php
if (!defined('ABSPATH')) exit;

$post_id = $post->ID;//$_GET['post'];
$tracked_kw = get_post_meta($post_id, 'wsko_keywords', true);
$groups_with_main = array();
foreach ($rows as $key => $row)
{
	$rows[$key]->isTracked = false;
	$rows[$key]->isFocused = false;
	$rows[$key]->hasGroup = false;
	$rows[$key]->isMainInGroup = false;
	if (isset($tracked_kw[$row->keys[0]]))
	{
		$rows[$key]->isTracked = true;
		$rows[$key]->isFocused = $tracked_kw[$row->keys[0]]['focus'];
		if (isset($tracked_kw[$row->keys[0]]['group']))
		{
			$rows[$key]->hasGroup = $tracked_kw[$row->keys[0]]['group'];
			if (isset($tracked_kw[$row->keys[0]]['group_main']))
			{
				$groups_with_main[$rows[$key]->hasGroup] = true;
				$rows[$key]->isMainInGroup = true;
			}
		}
		unset($tracked_kw[$row->keys[0]]);
	}
}

usort($rows, function ($a, $b)
{
	if ($a->isTracked != $b->isTracked)
	{
		if ($a->isTracked)
			return -1;
		else if ($b->isTracked)
			return 1;
	}
	else if ($a->hasGroup != $b->hasGroup)
	{
		if (!$a->hasGroup)
			return 1;
		else if (!$b->hasGroup)
			return -1;
		else
			return ($a->hasGroup < $b->hasGroup) ? -1 : 1;
	}
	else if ($a->hasGroup == $b->hasGroup)
	{
		if ($a->isMainInGroup)
			return -1;
		else if ($b->isMainInGroup)
			return 1;
		else if ($a->clicks != $b->clicks)
			return ($a->clicks > $b->clicks) ? -1 : 1;
		else
			return 0;
	}
	else
		return 0;
});

?>
<div class="modal fade wsko_modal" id="modal_track_keywords" style="overflow-y: auto;">
  <div class="modal-dialog">
    <div class="modal-content" style="width: 900px;">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">Track Keywords</h4>
      </div>
	  <form id="wsko_track_keywords_form">
		<?php wp_nonce_field('wsko-track-keywords-' . $post_id); ?>
		<input type="hidden" name="post" value="<?=$post_id?>">
		<div class="modal-body wsko_modal_table">
			
			<div class="wsko_modal_top">
			<div class="row">	
				<div class="col-sm-6">
					<label class="wsko_label">Target Keywords</label>
					<p>Define a set of keywords for this page/post and optimize your content.</p>
					
					

					<div id="wsko_add_keyword" style="margin-top:10px;">
						<div id="wsko_error_add_keyword" style="display:none;"></div>
							<div class="row">
								<div class="col-md-6"style="display:flex;">
									<input id="wsko_add_track_keyword_value" list="wsko_suggest_kw_data_post" autocomplete="off" style="display: inline-block; width: 400px; margin-right:3px;" class="form-control" placeholder="Insert Keyword">
									<div id="wsko_suggest_post_loading" style="display:none;">
										<i style="padding:10px" class="fa fa-spinner fa-pulse"></i>
									</div>
									<datalist id="wsko_suggest_kw_data_post"></datalist>
									<button id="wsko_add_track_keyword" style="height:33px; margin:0px 3px;" class="btn btn-primary button-large">Add Keyword</button>
								</div>
							</div>
					</div>
			
				</div>	
				<div class="col-md-6">
					<div style="background-color:#ddd; border-radius:4px; padding:15px; margin:0 auto;">
						<label class="wsko_label">Important Target Keywords Criteria:</label> 
						<p>Keyword Density, in an h-tag, bold / italic, alt-tag</p>
						<hr class="wsko_hr" style="border-top-color: #ccc !important;" />
						<label class="wsko_label">Important Focus Keywords Criteria:</label>
						<p>In Title, URL, H1 tag, H2 tag, alt tag, Keyword Density</p>
						<p><a target="_blank" href="https://www.bavoko.services/wordpress/wsko-optimize-your-pages-for-ranking-keywords/">More about content optimization</a></p>
					</div>	
				</div>
			</div>	
			</div>
			
			<ul class="nav nav-tabs wsko_main_nav wsko_nav" style="margin-top:15px;">
				<li class="active"><a data-toggle="tab" href="#wsko_keywords">Keywords</a></li>
				<li><a id="wsko_add_track_keyword_view" data-toggle="tab" href="#wsko_custom_keywords">Custom Keywords</a></li>
			</ul>
			<div class="tab-content">
				<div id="wsko_keywords" class="tab-pane fade in active">
				  <table id="wsko_tables_page" class="table table-striped table-bordered wsko_tables_modal_border wsko_modal_table wsko_tables_post_kw table-condensed" cellspacing="0" width="100%">
					<thead>
						<tr>
							<th>Target <i class="fa fa-info-circle" title="Select Keywords to target. Set a higher priority for certain Keywords by focusing them." data-toggle="tooltip"></i></th>
							<th>Group</th>
							<th>Keyword</th>
							<th>Clicks</th>
							<th>Position</th>
							<th>Impressions</th>
							<th>CTR</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th></th>
							<th></th>
							<th>Keyword</th>
							<th>Clicks</th>
							<th>Position</th>
							<th>Impressions</th>
							<th>CTR</th>
						</tr>
					</tfoot>
					<tbody>
					
					<?php
					foreach ($rows as $row)
					{
						?>
						<tr class="keyword_group_wrapper">
							<td style="width:140px;">
								<input class="wsko_checkbox_target wsko-cb-target" type="checkbox" name="keywords[<?=$row->keys[0]?>][value]" value="<?=$row->keys[0]?>" <?=$row->isTracked ? 'checked' : ''?> <?=$row->hasGroup && isset($groups_with_main[$row->hasGroup]) && !$row->isMainInGroup ? 'disabled' : ''?>>
								<label>Target</label>
														
								<input class="wsko_checkbox_focus wsko-cb-focus" type="checkbox" name="keywords[<?=$row->keys[0]?>][focus]" value="true" <?=$row->isFocused ? 'checked' : ''?> <?=$row->hasGroup && isset($groups_with_main[$row->hasGroup]) && !$row->isMainInGroup ? 'disabled' : ''?>>
								<label>Focus</label>
								
							</td>
							<td style="width:170px;">
								<select class="keyword_group_select keyword_group_<?=$row->hasGroup ? $row->hasGroup : '0'?>" name="keywords[<?=$row->keys[0]?>][group]" <?=!$row->isTracked ? 'disabled' : ''?>>
									<option <?=$row->hasGroup == false ? 'selected' : ''?> value="0">-</option>
									<?php for ($i = 1; $i <= 10; $i++)
									{
										?><option value="<?=$i?>" <?=$row->hasGroup && $row->hasGroup == $i ? 'selected' : ''?>><?=$i?></option><?php
									} ?>
								</select>
								<input class="wsko_radio_main_keyword keyword_group_set_main" type="checkbox" name="keywords[<?=$row->keys[0]?>][group_main]" value="true" data-check="<?=$row->clicks?>" data-group="<?=$row->hasGroup ? $row->hasGroup : '0'?>" <?=$row->isMainInGroup? 'checked' : ''?> <?=!$row->hasGroup ? 'disabled' : ''?>>
								<label>Main</label>
								<i class="wsko-default-main fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-original-title="This keyword has the most clicks in this group and is therefore recommended as main keyword. This might change depending on the clicks. Click 'Main Keyword' in order to set this as main keyword of this group." style="display:none;"></i>
							</td>
							<td class="wsko-keywords-anchor"><?=$row->keys[0]?></td>
							<td><?=$row->clicks?></td>
							<td><?=round($row->position, 2)?></td>
							<td><?=$row->impressions?></td>
							<td><?=round($row->ctr, 2)?> %</td>
						</tr><?php
					}
					?>
					</tbody>
					</table>
				
				</div>
				
				<div id="wsko_custom_keywords" class="tab-pane fade">
					<table id="wsko_kw_wrapper" class="table table-striped table-bordered wsko_tables table-condensed wsko_modal_table" cellspacing="0" width="100%">	
						<thead>
						<tr>
							<th>Target <i class="fa fa-info-circle" title="Select Keywords to target. Set a higher priority for certain Keywords by focusing them." data-toggle="tooltip"></i></th>
							<th>Group</th>
							<th>Keyword</th>
						</tr>
					</thead>
						<tr class="wsko-kw-template keyword_group_wrapper" style="display:none;">
							<td style="width:140px;">
								<input class="wsko_checkbox_target wsko-kw-value wsko-cb-target-custom" type="checkbox" name="temp" checked>
								<label>Target</label>
														
								<input class="wsko_checkbox_focus wsko-kw-focus wsko-cb-focus-custom" type="checkbox" name="temp" value="true">
								<label>Focus</label>
							</td>
							<td style="width:170px;">
								<select class="keyword_group_select keyword_group_<?=$row->hasGroup ? $row->hasGroup : '0'?>" name="temp">
									<option selected value="0">-</option>
									<?php for ($i = 1; $i <= 10; $i++)
									{
										?><option value="<?=$i?>"><?=$i?></option><?php
									} ?>
								</select>
								<input class="wsko_radio_main_keyword keyword_group_set_main" type="checkbox" name="temp" value="true" data-check="0" data-group="0" disabled>
								<label>Main</label>
								<i class="wsko-default-main fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-original-title="This keyword has the most clicks in this group and is therefore the chosen main keyword. This might change depending on the clicks. Click here in order to fix these settings." style="display:none;"></i>
							</td>
							<td class="wsko-keywords-anchor"><span class="wsko-kw-title wsko-keywords-anchor"><?=$kw?></span></td>
						</tr>
						<?php
						if (!empty($tracked_kw))
						{
							foreach($tracked_kw as $kw => $data)
							{
								?>
									<?php /* <li>
										<input class="wsko-kw-value" type="checkbox" name="keywords[<?=$kw?>][value]" value="<?=$kw?>" checked>
										<input class="wsko-kw-focus" type="checkbox" name="keywords[<?=$kw?>][focus]" value="true" <?=$focused ? 'checked' : ''?>>
									</li> */ ?>								
								<tr class="keyword_group_wrapper">
									<td style="width:140px;">
										<input class="wsko_checkbox_target wsko-kw-value wsko-cb-target-custom" type="checkbox" name="keywords[<?=$kw?>][value]" value="<?=$kw?>" checked <?=isset($data['group']) && $data['group'] != 0 && isset($groups_with_main[(string)$data['group']]) && !isset($data['group_main']) ? 'disabled' : ''?>>
										<label>Target</label>
																
										<input class="wsko_checkbox_focus wsko-kw-focus wsko-cb-focus-custom" type="checkbox" name="keywords[<?=$kw?>][focus]" value="true" <?=$data['focus'] ? 'checked' : ''?> <?=isset($data['group']) && $data['group'] != 0 && isset($groups_with_main[(string)$data['group']]) && !isset($data['group_main']) ? 'disabled' : ''?>>
										<label>Focus</label>
										
									</td>
									<td style="width:170px;">
										<select class="keyword_group_select keyword_group_<?=isset($data['group']) ? $data['group'] : '0'?>" name="keywords[<?=$kw?>][group]">
											<option <?=isset($data['group']) ? 'selected' : ''?> value="0">-</option>
											<?php for ($i = 1; $i <= 10; $i++)
											{
												?><option value="<?=$i?>" <?=isset($data['group']) && $data['group'] == $i ? 'selected' : ''?>><?=$i?></option><?php
											} ?>
										</select>
										<input class="wsko_radio_main_keyword keyword_group_set_main" type="checkbox" name="keywords[<?=$kw?>][group_main]" value="true" data-check="0" data-group="<?=isset($data['group']) ? $data['group'] : '0'?>" <?=isset($data['group']) && isset($data['group_main']) ? 'checked' : ''?> <?=!isset($data['group']) || $data['group'] == 0 ? 'disabled' : ''?>>
										<label>Main</label>
										<i class="wsko-default-main fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-original-title="This keyword has the most clicks in this group and is therefore the chosen main keyword. This might change depending on the clicks. Click here in order to fix these settings." style="display:none;"></i>
									</td>
									<td class="wsko-keywords-anchor"><span class="wsko-kw-title wsko-keywords-anchor"><?=$kw?></span></td>
								</tr>
								<?php
							}
						}
						else
						{
							?><tr class="wsko-empty-row"><td>No keywords found.</td><td></td><td></td></tr><?php
						}
						?>
						</table>
					 
				</div>
			</div>
		</div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<input class="btn btn-sumbit" type="submit" value="Save Changes">
		  </div>
	  </form>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal --> 