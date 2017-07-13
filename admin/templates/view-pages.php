<?php
if (!defined('ABSPATH')) exit;

if ($is_admin_view)
{ ?>
	<div class="wsko_box_wrapper">
		<i class="fa fa-spin fa-spinner fa-2x"></i>
	</div>
	<?php
}
else if ($has_data)
{
	?>
	<div class="wsko_box_wrapper">
	<p class="wsko_chart_label">Page Overview</p>
	<table id="wsko_tables_page" class="table table-striped table-bordered wsko_tables table-condensed wsko-table-custom" cellspacing="0" width="100%" data-nonce="<?=wp_create_nonce('wsko-get-pages')?>">
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
			/*$r_id = 0;
			if (isset($page_rows))
			{
				foreach ($page_rows as $row)
				{
					$r_id++;
					$ref_row = false;
					if (isset($page_rows_ref))
					{
						foreach ($page_rows_ref as $row2)
						{
							if ($row2->keys[0] == $row->keys[0])
							{
								$ref_row = $row2;
							}
						}
					}
					if ($ref_row)
					{
						$ref_clicks = $row->clicks != 0 ? round((1 - $ref_row->clicks / $row->clicks) * 100, 2) : 0;
						$ref_position = $row->position != 0 ? -round((1 - $ref_row->position / $row->position) * 100, 2) : 0;
						$ref_impressions = $row->impressions != 0 ? round((1 - $ref_row->impressions / $row->impressions) * 100, 2) : 0;
						$ref_ctr = $row->ctr != 0 ? round((1 - $ref_row->ctr / $row->ctr) * 100, 2) : 0;
					}
					$url = $row->keys[0];
					?>
					<tr class="row-item wsko-unloaded" data-id="<?=$r_id?>" data-url="<?=$url?>">
						<td class="wsko_table_col1"><div class="wsko_nowrap" title="<?=$url?>"><span class="wsko-post-title"><i class="fa fa-spinner fa-spin"></i> <?='Identifying...'?></span></br><span class="font-unimportant"><?=$url?></span></div></td>
						<td data-order="<?=$row->clicks?>"><span style="min-width:30px; float:left;"><?=$row->clicks?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %</span></a></td>
						<td data-order="<?=$row->position?>"><span style="min-width:30px; float:left;"><?=round($row->position, 2)?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? 'Position ' . round($ref_row->position, 2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?> %</span></a></td>
						<td data-order="<?=$row->impressions?>"><span style="min-width:30px; float:left;"><?=$row->impressions?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %</span></a></td> 
						<td data-order="<?=$row->ctr?>" style="min-width:80px;"><span style="min-width:30px; float:left;"><?=round($row->ctr, 2)?> % </span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ?  round($ref_row->ctr ,2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %</span></a></td>							
						<td style="min-width:80px; text-align:left; padding-right:10px;">
							<a class="wsko-show-keywords unloaded wsko_details_button" href="#" data-toggle="modal" data-target="#modal_keyword_details" data-url="<?=$url?>" data-nonce="<?=wp_create_nonce('wsko-show-keywords')?>"><i class="fa fa-eye" title="Show Keywords" data-toggle="tooltip"></i></a>
							<a class="wsko_details_button" target="_blank" href="<?=$url?>"><i class="fa fa-link" title="View Page" data-toggle="tooltip"></i></a>
							<a class="wsko-post-button wsko_details_button" target="_blank" href="#" style="display:none"><i class="fa fa-pencil" title="Edit Post" data-toggle="tooltip"></i></a>
							<div class="wsko-kd-cache" style="display:none;"></div>
						</td>
						
					 </tr>
					<?php
				}
			}*/
		?>
			
		</tbody>
	</table>
	</div>
<?php
}
else if ($caching_active)
{
	if ($client)
	{
		?><div class="wsko_box_wrapper"> <?php
		include($wsko_path . 'templates/template-no-cache.php');
		?></div> <?php
	}
	else
	{
		?><div class="wsko_box_wrapper"> <?php
		include($wsko_path . 'templates/template-no-cache-gafail.php');
		?></div> <?php
	}
}
else
{
	?><div class="wsko_box_wrapper"> <?php
	include($wsko_path . 'templates/template-no-data.php');
	?></div> <?php
} ?>