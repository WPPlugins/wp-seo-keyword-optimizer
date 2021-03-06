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
	<p class="wsko_chart_label">Keyword Overview</p>
	<table id="wsko_table_keywords" class="table table-striped table-bordered wsko_tables table-condensed wsko-table-custom" cellspacing="0" width="100%">
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
		/*if (isset($kw_rows))
		{
			foreach ($kw_rows as $row)
			{
				$ref_row = false;
				if (isset($kw_rows_ref))
				{
					foreach ($kw_rows_ref as $row2)
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
				$kw = $row->keys[0];
				?><tr>
					<td class="wsko_table_col1">
						<strong><?=$kw?><strong></br>
					</td>
					<td data-order="<?=$row->clicks?>"><span style="min-width:30px; float:left;"><?=$row->clicks?> </span><a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->clicks . ' Clicks from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"><span class="wsko_single_progress <?=$ref_row ? ($ref_clicks < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_clicks > 0)) ? '+' : ''?><?=$ref_row ? $ref_clicks : '-'?> %</span></a></td>
					<td data-order="<?=$row->position?>"><span style="min-width:30px; float:left;"><?=round($row->position, 2)?></span><a href="#" data-toggle="tooltip" title="<?=$ref_row ? 'Position ' . round($ref_row->position, 2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_position < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_position > 0)) ? '+' : ''?><?=$ref_row ? $ref_position : '-'?> %</span></a></td>
					<td data-order="<?=$row->impressions?>"><span style="min-width:30px; float:left;"><?=$row->impressions?></span> <a href="#" data-toggle="tooltip" title="<?=$ref_row ? $ref_row->impressions . ' Impressions from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_impressions < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_impressions > 0)) ? '+' : ''?><?=$ref_row ? $ref_impressions : '-'?> %</span></a></td>
					<td data-order="<?=$row->ctr?>"><span style="min-width:30px; float:left;"><?=round($row->ctr, 2)?> % </span><a href="#" data-toggle="tooltip" title="<?=$ref_row ?  round($ref_row->ctr ,2) . ' from ' . date('d/m/Y', $time - $time_diff) . ' to ' . date('d/m/Y', $time) . '.' : ''?>"> <span class="wsko_single_progress <?=$ref_row ? ($ref_ctr < 0 ? 'wsko_red_font' : 'wsko_green_font') : 'wsko_gray_font'?>"><?=($ref_row && ($ref_ctr > 0)) ? '+' : ''?><?=$ref_row ? $ref_ctr : '-'?> %</span></a></td>
				</tr><?php
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
}
			